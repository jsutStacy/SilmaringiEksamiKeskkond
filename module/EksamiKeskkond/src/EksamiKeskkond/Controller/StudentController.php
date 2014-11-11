<?php

namespace EksamiKeskkond\Controller;

use Zend\View\Model\ViewModel;

use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;
use Banklink\Bank;
use Banklink\bankLink;
use Banklink\shoppingCart;
use Exception;
use EksamiKeskkond\Form\BanklinkForm;

use Zend\Mail\Message;

use EksamiKeskkond\Model\Course;
use EksamiKeskkond\Form\CourseForm;
use EksamiKeskkond\Filter\CourseFilter;

use EksamiKeskkond\Model\Subject;
use EksamiKeskkond\Form\SubjectForm;
use EksamiKeskkond\Filter\SubjectFilter;

class StudentController extends AbstractActionController {

	protected $courseTable;

	protected $userCourseTable;

	protected $userTable;

	protected $subjectTable;

	public function indexAction() {
		$auth = new AuthenticationService();

		$user = $auth->getIdentity();
		$course = $this->getUserCourseTable()->getCourseByUserId($user->id);

		if (!empty($course)) {
			return $this->redirect()->toRoute('student/course', array('id' => $course->course_id));
		}
		return $this->redirect()->toRoute('student/all-courses');
	}

	public function courseAction() {
		$auth = new AuthenticationService();

		$user = $auth->getIdentity();

		$subjects = array();
		$course = $this->getCourseTable()->getCourse($this->params()->fromRoute('id'));
		$hasBoughtCourse = $this->getUserCourseTable()->checkIfUserHasBoughtCourse($user->id, $course->id);

		if (!$course) {
			return $this->redirect()->toRoute('errors');
		}
		if ($hasBoughtCourse) {
			$subjects = $this->getSubjectTable()->getSubjectsByCourseId($course->id);
		}
		return new ViewModel(array(
			'course' => $course,
			'subjects' => $subjects,
			'hasBoughtCourse' => $hasBoughtCourse,
		));
	}

	public function allCoursesAction() {
		$auth = new AuthenticationService();

		$user = $auth->getIdentity();
		$studentCoursesIds = $this->getUserCourseTable()->getAllCoursesByUserId($user->id);

		return new ViewModel(array(
			'courses' => $this->getCourseTable()->fetchAll(),
			'studentCoursesIds' => $studentCoursesIds,
		));
	}

	public function myCoursesAction() {
		$auth = new AuthenticationService();

		$user = $auth->getIdentity();
		$studentCoursesIds = $this->getUserCourseTable()->getAllCoursesByUserId($user->id);

		$myCourses = array();

		foreach ($studentCoursesIds as $courseId) {
			$myCourses[] = $this->getCourseTable()->getCourse($courseId);
		}
		return new ViewModel(array(
			'myCourses' => $myCourses,
		));
	}

	public function buyCourseAction() {
		$auth = new AuthenticationService();
		$user = $auth->getIdentity();

		$courseId = $this->params()->fromRoute('id');
		$course = $this->getCourseTable()->getCourse($courseId);

		$bankName = $this->params()->fromRoute('bank');
		$bankLinkPreferences = $this->getServiceLocator()->get('Config')['bankLinkPreferences'][$bankName];
		$bankPreferences = $this->getServiceLocator()->get('Config')['bankPreferences'][$bankName];

		$form = new BanklinkForm();
		$form->setAttribute('action', $bankPreferences['url']);
		$form->get('VK_SND_ID')->setValue($bankLinkPreferences['my_id']); 
		$form->get('VK_STAMP')->setValue(550);//ID generator is needed
		$form->get('VK_AMOUNT')->setValue($course->price);
		$form->get('VK_CURR')->setValue('EUR');
		$form->get('VK_ACC')->setValue($bankLinkPreferences['account_number']);
		$form->get('VK_NAME')->setValue($bankLinkPreferences['account_owner']);
		$form->get('VK_MSG')->setValue('Kursuse ostmine');
		$form->get('VK_RETURN')->setValue('http://silmaring.eksamikeskkond.dev/');
		
		$macFields = array(
			'VK_SERVICE' => '1011',
			'VK_VERSION' => '008',
			'VK_SND_ID' => $this->to_banklink_ch($bankLinkPreferences['my_id'],$bankPreferences['charset']),
			'VK_STAMP' => $this->to_banklink_ch('5505', $bankPreferences['charset']),
			'VK_AMOUNT' => $this->to_banklink_ch($course->price, $bankPreferences['charset']),
			'VK_CURR' => $this->to_banklink_ch('EUR', $bankPreferences['charset']),
			'VK_ACC' => $this->to_banklink_ch($bankLinkPreferences['account_number'], $bankPreferences['charset']),
			'VK_NAME' => $this->to_banklink_ch($bankLinkPreferences['account_owner'], $bankPreferences['charset']),
			'VK_REF' => "1234561",
			'VK_LANG' => 'EST',
			'VK_MSG' => $this->to_banklink_ch('Kursuse ostmine', $bankPreferences['charset']),
			'VK_RETURN' => $this->to_banklink_ch('http://silmaring.eksamikeskkond.dev/', $bankPreferences['charset']),
			'VK_CANCEL' => $this->to_banklink_ch('http://silmaring.eksamikeskkond.dev/', $bankPreferences['charset']),
			'VK_DATETIME' => "2014-11-06T21:49:12+0200",
			'VK_ENCODING' => "utf-8",
		);

		$key = openssl_pkey_get_private(
			file_get_contents($bankLinkPreferences['my_private_key']),
			$bankLinkPreferences['my_private_key_password']
		);

		if (!openssl_sign ($this->generateMACString($macFields, $bankPreferences['charset'], $bankLinkPreferences), $signature, $key)) {
			trigger_error ("Unable to generate signature", E_USER_ERROR);
		}

		$macFields['VK_MAC'] = base64_encode ($signature);
		$fieldsString='';

		foreach ($macFields as $key => $value) {
			$fieldsString .= $key . '=' . htmlspecialchars($value) . '&';
		}
		rtrim($fieldsString, '&');


		try{
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL,$bankPreferences['url']);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
			curl_setopt($ch,CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_POST, count($macFields));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldsString);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				"Content-Type: text/html; charset=" . $bankPreferences['charset'])
			);

			$result=curl_exec($ch);
			if (FALSE === $result) {
				throw new Exception(curl_error($ch), curl_errno($ch));
			}
		} catch(Exception $e) {
			trigger_error(sprintf(
				'Curl failed with error #%d: %s',
				$e->getCode(), $e->getMessage()),
				E_USER_ERROR);
		}
		curl_close($ch);
		
		$this->getUserCourseTable()->buyCourse($user->id, $courseId);
		return $this->redirect()->toRoute('student/course', array('id' => $courseId));
	}

	public function buyCourseWithBillAction() {
		$auth = new AuthenticationService();
		$user = $auth->getIdentity();

		$config = $this->getServiceLocator()->get('Config');
		$course = $this->getCourseTable()->getCourse($this->params()->fromRoute('id'));
		$transport = $this->getServiceLocator()->get('mail.transport');

		$messageToStudent = new Message();
		$messageToAdmin = new Message();

		$messageToStudent->setEncoding('UTF-8')
			->addTo(/*$user->email*/$config['admin_email'])
			->addFrom($config['admin_email'])
			->setSubject('Arve')
			->setBody(
				'Olete ostnud kursuse ' . $course->name . '. Palun tasuda arve summas ' . $course->price
					. '. Palun tehke ülekanne EE21412904821049 kontole, et saada ligipääs kursusele.'
			);

		$messageToAdmin->setEncoding('UTF-8')
			->addTo($config['admin_email'])
			->addFrom($config['admin_email'])
			->setSubject('Õpilane on ostnud kursuse')
			->setBody(
				'Õpilane ' . $user->firstname . ' ' . $user->lastname . ', e-mailiga ' . $user->email
					. ', ostis kursuse ' . $course->name . ', mis maksab ' . $course->price
					. '. Kontrollige, et arve on tasutud ja andke talle kursuse jaoks õigused.'
			);

		$transport->send($messageToStudent);
		$transport->send($messageToAdmin);

		$this->getUserCourseTable()->buyCourse($user->id, $course->id, null);

		return $this->redirect()->toRoute('student/all-courses');
	}
	
	private function to_banklink_ch ($v, $banklinkCharset) {
		return mb_convert_encoding ($v, $banklinkCharset, 'utf-8');
	}
	
	private function generateMACString ($macFields, $banklinkCharset, $preferences) {
		global  $VK_variableOrder;
		$requestNum = $macFields['VK_SERVICE'];
		$data = '';
	
		foreach ((array)$VK_variableOrder[$requestNum] as $key) {
			$v = $macFields[$key];
			$l = ($preferences['bankname'] == 'swedbank' ? mb_strlen ($v, $banklinkCharset) : strlen ($v));
			$data .= str_pad ($l, 3, '0', STR_PAD_LEFT) . $v;
		}
	
		return $data;
	}

	public function getCourseTable() {
		if (!$this->courseTable) {
			$sm = $this->getServiceLocator();
			$this->courseTable = $sm->get('EksamiKeskkond\Model\CourseTable');
		}
		return $this->courseTable;
	}
	
	public function getUserCourseTable() {
		if (!$this->userCourseTable) {
			$sm = $this->getServiceLocator();
			$this->userCourseTable = $sm->get('EksamiKeskkond\Model\UserCourseTable');
		}
		return $this->userCourseTable;
	}
	
	public function getSubjectTable() {
		if (!$this->subjectTable) {
			$sm = $this->getServiceLocator();
			$this->subjectTable = $sm->get('EksamiKeskkond\Model\SubjectTable');
		}
		return $this->subjectTable;
	}
}