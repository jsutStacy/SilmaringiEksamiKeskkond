<?php

namespace EksamiKeskkond\Controller;

use Zend\View\Model\ViewModel;
use Zend\ViewModel\JsonModel;

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
	
	protected $subsubjectTable;
	
	protected $lessonTable;

	protected $lessonFilesTable;

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
			'subsubjectTable' => $this->getSubsubjectTable(),
			'lessonTable' => $this->getLessonTable(),
		));
	}

	public function changeLessonAction(){
		$request = $this->getRequest();
		$response = $this->getResponse();

		$lessonId = $this->params()->fromRoute('id');
		$lesson = $this->getLessonTable()->getLesson($lessonId);

		$lessonFiles = array();
		$lessonFiles = $this->getLessonFilesTable()->getLessonFilesByLessonId($lessonId);

		$html = "";
		//Create HTML for video lesson
		if($lesson->type == "video"){
			$urls = array();
			foreach ($lessonFiles as $lessonFile){
				array_push($urls, $lessonFile->url);
			}
			if(!empty($urls)){
				parse_str( parse_url( $urls[0], PHP_URL_QUERY ), $urlVars );
				if(array_key_exists('v', $urlVars)){
					$html =
									'<div class="row">
									<iframe width="420" height="315" src="//www.youtube.com/embed/'.$urlVars['v'].'" frameborder="0" allowfullscreen></iframe>
									</div>';
				}
			}
		}

		if ($request->isPost()) {
			$response->setContent(\Zend\Json\Json::encode(array(
				'response' => true,
				'content' => $lesson->content, 
				'type' => $lesson->type, 
				'html' => $html,
			)));
		}
		return $response;
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
		$courseId = $this->params()->fromRoute('id');
		$course = $this->getCourseTable()->getCourse($courseId);

		$auth = new AuthenticationService();
		$user = $auth->getIdentity();

		$bankName = $this->params()->fromRoute('bank');
		$bankLinkPreferences = $this->getServiceLocator()->get('Config')['bankLinkPreferences'][$bankName];
		$bankPreferences = $this->getServiceLocator()->get('Config')['bankPreferences'][$bankName];

		date_default_timezone_set ( "Europe/Tallinn" );
		$time=time();
		$timestamp = date("Y-m-d", $time) . 'T' . date("H:i:s", $time) .'+0200';

		$stamp = 500; //Random

		$fields = array(
			'VK_SERVICE' => '1011',
			'VK_VERSION' => '008',
			'VK_SND_ID' => $this->to_banklink_ch($bankLinkPreferences['my_id'],$bankPreferences['charset']),
			'VK_STAMP' => $this->to_banklink_ch($stamp, $bankPreferences['charset']),
			'VK_AMOUNT' => $this->to_banklink_ch($course->price, $bankPreferences['charset']),
			'VK_CURR' => $this->to_banklink_ch('EUR', $bankPreferences['charset']),
			'VK_ACC' => $this->to_banklink_ch($bankLinkPreferences['account_number'], $bankPreferences['charset']),
			'VK_NAME' => $this->to_banklink_ch($bankLinkPreferences['account_owner'], $bankPreferences['charset']),
			'VK_REF' => "1234561",
			'VK_LANG' => 'EST',
			'VK_MSG' => $this->to_banklink_ch('Kursuse ostmine', $bankPreferences['charset']),
			'VK_RETURN' => $this->to_banklink_ch('http://silmaring.eksamikeskkond.dev/student/buy-course/'.$courseId.'/'.$bankName, $bankPreferences['charset']),
			'VK_CANCEL' => $this->to_banklink_ch('http://silmaring.eksamikeskkond.dev/student/buy-course/'.$courseId.'/'.$bankName, $bankPreferences['charset']),
			'VK_DATETIME' => $timestamp,
			'VK_ENCODING' => $bankPreferences['charset'],
		);

		$VK_variableOrder = $this->getServiceLocator()->get('Config')['VK_variableOrder'];
		$macString = $this->generateMACString($fields, $bankPreferences['charset'], $bankLinkPreferences, $VK_variableOrder);

		$key = openssl_pkey_get_private(
			file_get_contents($bankLinkPreferences['my_private_key']),
			$bankLinkPreferences['my_private_key_password']
		);

		openssl_sign($macString, $signature, $key, OPENSSL_ALGO_SHA1);

		$fields['VK_MAC'] = base64_encode ($signature);

		$form = new BanklinkForm();
		$form->setAttribute('action', $bankPreferences['url']);
		$form->setAttribute('id', 'bankLinkForm');
		foreach ($fields as $key => $value) {
			$form->get($key)->setValue($value);
		}

		//If there is response from bank:
		if (array_key_exists("VK_SERVICE" , $_REQUEST)) {
			$macFields = array ();
			foreach ((array)$_REQUEST as $f => $v) {
				if (substr ($f, 0, 3) == 'VK_') {
					$macFields[$f] = $v;
				}
			}

			$p = $bankPreferences['charset_parameter'];
			$banklinkCharset = '';
			if ($p != '') {
				$banklinkCharset = $macFields[$p];
			}
			if ($banklinkCharset == '') {
				$banklinkCharset = 'iso-8859-1';
			}

			$key = openssl_pkey_get_public(file_get_contents($bankLinkPreferences['bank_certificate']));
			$macString = $this->generateMACString($macFields, $bankPreferences['charset'], $bankLinkPreferences, $VK_variableOrder);
			$response = "";
			if (!openssl_verify($macString, base64_decode($macFields['VK_MAC']), $key,  OPENSSL_ALGO_SHA1)) {
				$response = "Tekkis viga, proovige hiljem uuesti.";
			}
			else {
				if ($macFields['VK_SERVICE'] == '1911') {
					$response = "Makse sooritamine katkestati.";
				}
				else if ($macFields['VK_SERVICE'] == '1111') {
					$this->getUserCourseTable()->buyCourse($user->id, $course->id, true);
					$response = "success";
				}
				else {
					$response = "Tekkis tundmatu viga, vabandame.";
				}
			}

			return new ViewModel(array(
				'course' => $course,
				'form' => $form,
				'response' => $response,
			));
		}
		else {
			return new ViewModel(array(
					'course' => $course,
					'form' => $form,
					'response' => "redirect",
			));
		}
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

	private function generateMACString ($macFields, $banklinkCharset, $preferences, $VK_variableOrder) {
		$requestNum = $macFields['VK_SERVICE'];
		$data = '';

		foreach ((array)$VK_variableOrder[$requestNum] as $key) {
			$v = $macFields[$key];
			$l = mb_strlen($v, $banklinkCharset);
			$data .= str_pad($l, 3, '0', STR_PAD_LEFT) . $v;
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

	public function getSubsubjectTable() {
		if (!$this->subsubjectTable) {
			$sm = $this->getServiceLocator();
			$this->subsubjectTable = $sm->get('EksamiKeskkond\Model\SubsubjectTable');
		}
		return $this->subsubjectTable;
	}

	public function getLessonTable() {
		if (!$this->lessonTable) {
			$sm = $this->getServiceLocator();
			$this->lessonTable = $sm->get('EksamiKeskkond\Model\LessonTable');
		}
		return $this->lessonTable;
	}

	public function getLessonFilesTable() {
		if (!$this->lessonFilesTable) {
			$sm = $this->getServiceLocator();
			$this->lessonFilesTable = $sm->get('EksamiKeskkond\Model\LessonFilesTable');
		}
		return $this->lessonFilesTable;
	}

}