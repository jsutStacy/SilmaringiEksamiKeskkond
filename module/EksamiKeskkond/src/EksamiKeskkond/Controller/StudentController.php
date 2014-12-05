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

use EksamiKeskkond\Model\Note;
use EksamiKeskkond\Form\NoteForm;

use EksamiKeskkond\Form\HomeworkAnswerForm;
use EksamiKeskkond\Model\HomeworkAnswers;

class StudentController extends AbstractActionController {

	protected $courseTable;

	protected $userCourseTable;

	protected $userTable;

	protected $subjectTable;

	protected $subsubjectTable;

	protected $lessonTable;

	protected $lessonFilesTable;

	protected $noteTable;

	protected $userLessonTable;

	protected $homeworkTable;

	protected $homeworkAnswersTable;


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

		$subsubjects = array();
		$lessons = array();
		$course = array();
		$courseData = array();

		$course = $this->getCourseTable()->getCourse($this->params()->fromRoute('id'));
		$hasBoughtCourse = $this->getUserCourseTable()->checkIfUserHasBoughtCourse($user->id, $course->id);
		$status = $this->getUserCourseTable()->checkIfUserHasAccessToCourse($user->id, $course->id);

		$courseData['course'] = $course;

		if (!$course) {
			return $this->redirect()->toRoute('errors');
		}
		if ($hasBoughtCourse && $status == true) {
			$subjects = $this->getSubjectTable()->getSubjectsByCourseId($course->id);

			foreach ($subjects as $subjectKey => $subject) {
				$subsubjects = $this->getSubsubjectTable()->getSubsubjectsBySubjectId($subject->id);

				foreach ($subsubjects as $subsubjectKey => $subsubject) {
					$lessons = $this->getLessonTable()->getLessonsBySubsubjectId($subsubject->id);
					$homeworks = $this->getHomeworkTable()->getHomeworkBySubsubjectId($subsubject->id);

					foreach ($lessons as $lesson) {
						$userLesson = $this->getUserLessonTable()->getUserLesson($user->id, $lesson->id);

						$lesson->done = !empty($userLesson) ? true : false;
					}
					$subsubjects[$subsubjectKey] = get_object_vars($subsubject);
					$subsubjects[$subsubjectKey]['lessons'] = $lessons;
					$subsubjects[$subsubjectKey]['homeworks'] = $homeworks;
				}
				$subjects[$subjectKey] = get_object_vars($subject);
				$subjects[$subjectKey]['subsubjects'] = $subsubjects;
			}
			$courseData['subjects'] = $subjects;
		}
		$courseHasEnded = false;
		$courseHasntStarted = false;

		if ((time() - (60 * 60 * 24)) > strtotime($course->end_date)) {
			$courseHasEnded = true;
		}
		if ((time() - (60 * 60 * 24)) < strtotime($course->start_date)) {
			$courseHasntStarted = true;
		}
		return new ViewModel(array(
			'courseData' => $courseData,
			'hasBoughtCourse' => $hasBoughtCourse,
			'hasEnded' => $courseHasEnded,
			'hasntStarted' => $courseHasntStarted,
			'userId' => $user->id,
		));
	}

	public function lessonAction() {
		$auth = new AuthenticationService();
		$user = $auth->getIdentity();

		$id = $this->params()->fromRoute('id');

		$request = $this->getRequest();

		$viewmodel = new ViewModel();
		$viewmodel->setTerminal($request->isXmlHttpRequest());
		$viewmodel->setVariables(array(
			'lesson' => $this->getLessonTable()->getLesson($id),
			'lessonFiles' => $this->getLessonFilesTable()->getLessonFilesByLessonId($id),
			'isLessonMarkedDone' => $this->getUserLessonTable()->getUserLesson($user->id, $id),
		));
		return $viewmodel;
	}

	public function allCoursesAction() {
		$auth = new AuthenticationService();

		$user = $auth->getIdentity();
		$studentCoursesIds = $this->getUserCourseTable()->getAllCoursesByUserId($user->id);

		$teachers = $this->getUserTable()->getAllTeachersForList();
		$courses = $this->getCourseTable()->fetchAll();
		$coursesData = array();

		foreach ($courses as $key => $course) {
			$coursesData[$key]['course'] = $course;
			$coursesData[$key]['hasBought'] = $this->getUserCourseTable()->checkIfUserHasBoughtCourse($user->id, $course->id);

			if ($course->teacher_id) {
				$coursesData[$key]['teacher'] = $teachers[$course->teacher_id];
			}
			else {
				$coursesData[$key]['teacher'] = null;
			}
		}
		return new ViewModel(array(
			'courses' => $coursesData,
			'studentCoursesIds' => $studentCoursesIds,
		));
	}

	public function myCoursesAction() {
		$auth = new AuthenticationService();

		$user = $auth->getIdentity();
		$studentCoursesIds = $this->getUserCourseTable()->getAllCoursesByUserId($user->id);

		$myCourses = array();

		$courseTeachers = array();

		foreach ($studentCoursesIds as $courseId) {
			$course = $this->getCourseTable()->getCourse($courseId);
			$myCourses[] = $course;

			if ($course->teacher_id) {
				$teacher = $this->getUserTable()->getUser($course->teacher_id);
				$courseTeachers[$teacher->id] = $teacher;
			}
			else {
				$teacher = null;
				$courseTeachers[null] = $teacher;
			}
		}
		return new ViewModel(array(
			'myCourses' => $myCourses,
			'courseTeachers' => $courseTeachers,
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
			//'VK_RETURN' => $this->to_banklink_ch('http://eksamikeskkond.silmaring.dev/student/buy-course/'.$courseId.'/'.$bankName, $bankPreferences['charset']),
			//'VK_CANCEL' => $this->to_banklink_ch('http://eksamikeskkond.silmaring.dev/student/buy-course/'.$courseId.'/'.$bankName, $bankPreferences['charset']),
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
		$emailService = $this->getServiceLocator()->get('emailservice');
		$transport = $this->getServiceLocator()->get('mail.transport');

		$UserMessageSubject = 'Arve';
		$UserMessageBody = 'Olete ostnud kursuse ' . $course->name . '. Palun tasuda arve summas ' . $course->price
			. '. Palun tehke ülekanne EE21412904821049 kontole, et saada ligipääs kursusele.';

		$AdminMessageSubject = 'Õpilane on ostnud kursuse';
		$AdminMessageBody = 'Õpilane ' . $user->firstname . ' ' . $user->lastname . ', e-mailiga ' . $user->email
			. ', ostis kursuse ' . $course->name . ', mis maksab ' . $course->price
			. '. Kontrollige, et arve on tasutud ja andke talle kursuse jaoks õigused.';

		$emailService->sendEmail($user->email, $config['admin_email'], $UserMessageSubject, $UserMessageBody, $transport);
		$emailService->sendEmail($config['admin_email'], $config['admin_email'], $AdminMessageSubject, $AdminMessageBody, $transport);

		$this->getUserCourseTable()->buyCourse($user->id, $course->id, null, true);

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

	public function addNoteAction() {
		$auth = new AuthenticationService();
		$user = $auth->getIdentity();

		$lessonId = $this->params()->fromRoute('lesson_id');

		$form = new NoteForm();
		$form->get('user_id')->setValue($user->id);
		$form->get('lesson_id')->setValue($lessonId);

		$request = $this->getRequest();

		if ($request->isPost()) {
			$note = new Note();
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$note->exchangeArray($form->getData());
				$this->getNoteTable()->saveNote($note);

				return $this->redirect()->toRoute('student/all-notes');
			}
		}
		return array(
			'form' => $form,
			'lessonId' => $lessonId,
			'userId' => $user->id,
		);
	}
	
	public function editNoteAction() {
		$auth = new AuthenticationService();
		$user = $auth->getIdentity();
		
		$request = $this->getRequest();
		$response = $this->getResponse();

		if ($request->isPost()) {
			$id = $request->getPost()->id;
			$note = $this->getNoteTable()->getNote($id);

			$form  = new NoteForm();
			$form->bind($note);
			//$form->setInputFilter(new NoteFilter($this->getServiceLocator()));
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$this->getNoteTable()->saveNote($form->getData());
				$response->setContent(\Zend\Json\Json::encode(array(
					'response' => true,
					'noteId' => $id,
					'userId' => $user->id,
					'lessonId' => $form->getData()->lesson_id,
					'content' => $form->getData()->content,
				)));
				return $response;
			}
		}
		else {
			$id = $this->params()->fromRoute('id');
			$note = $this->getNoteTable()->getNote($id);
			$lesson = $this->getLessonTable()->getLesson($note->lesson_id);

			$form  = new NoteForm();
			$form->bind($note);
			$form->get('lesson_id')->setValue($lesson->id);
			$form->get('id')->setValue($id);

			$viewmodel = new ViewModel();
			$viewmodel->setTerminal($request->isXmlHttpRequest());
			$viewmodel->setVariables(array(
				'form' => $form,
				'lessonId' => $note->lesson_id,
				'userId' => $user->id,
				'id' => $id,
			));
			return $viewmodel;
		}
	}
	
	public function deleteNoteAction() {
		$this->getNoteTable()->deleteNote($this->params()->fromRoute('id'));
	
		return $this->redirect()->toRoute('student/all-notes');
	}

	public function allNotesAction() {
		$auth = new AuthenticationService();
		$user = $auth->getIdentity();
		$courseIds = $this->getUserCourseTable()->getAllCoursesByUserId($user->id);
		$courses = array();
		
		foreach ($courseIds as $courseId) {
			$course = $this->getCourseTable()->getCourse($courseId);
			$courses[] = $course;
		}
	
		return new ViewModel(array(
				'courses' => $courses,
				'subjectTable' => $this->getSubjectTable(),
				'subsubjectTable' => $this->getSubsubjectTable(),
				'lessonTable' => $this->getLessonTable(),
				'noteTable' => $this->getNoteTable(),
		));
	}

	public function markLessonDoneAction() {
		$auth = new AuthenticationService();
		$user = $auth->getIdentity();

		$response = $this->getResponse();

		$this->getUserLessonTable()->markLessonDone($user->id, $this->params()->fromRoute('id'));

		$response->setContent(\Zend\Json\Json::encode(array(
			'response' => true,
		)));
		return $response;
	}

	public function homeworkAction() {
		$auth = new AuthenticationService();
		$user = $auth->getIdentity();

		$homeworkId = $this->params()->fromRoute('id');

		$request = $this->getRequest();

		$viewmodel = new ViewModel();
		$viewmodel->setTerminal($request->isXmlHttpRequest());
		$viewmodel->setVariables(array(
			'homework' => $this->getHomeworkTable()->getHomework($homeworkId),
			'homeworkAnswer' => $this->getHomeworkAnswersTable()->getHomeworkAnswerByUserIdAndHomeworkId($user->id, $homeworkId),
		));
		return $viewmodel;
	}

	public function addHomeworkAnswerAction() {
		$auth = new AuthenticationService();
		$user = $auth->getIdentity();

		$homeworkId = $this->params()->fromRoute('id');

		$form = new HomeworkAnswerForm();
		$form->get('homework_id')->setValue($homeworkId);
		$form->get('user_id')->setValue($user->id);

		$request = $this->getRequest();

		if ($request->isPost()) {
			$homeworkAnswer = new HomeworkAnswers();

			$post = array_merge_recursive(
				$this->getRequest()->getPost()->toArray(),
				$this->getRequest()->getFiles()->toArray()
			);
			$form->setData($post);

			if ($form->isValid()) {
				$adapter = new \Zend\File\Transfer\Adapter\Http();

				if (!$adapter->isValid()) {
					$error = array();

					foreach ($adapter->getMessages() as $key => $row) {
						$error[] = $row;
					}
					$form->setMessages(array('fileupload' => $error));
				}
				else {
					$adapter->setDestination($this->getServiceLocator()->get('Config')['homework_dir']);

					if ($adapter->receive()) {
						$event = $this->getEvent();
						$request = $event->getRequest();
						$router = $event->getRouter();
						$uri = $router->getRequestUri();
						$baseUrl = sprintf('%s://%s%s', $uri->getScheme(), $uri->getHost(), $request->getBaseUrl());

						$adapterFileName = $adapter->getFileName();

						$filesName = $baseUrl . '/uploads/homework/' . substr(preg_replace('/\.\/public\/uploads\/homework/', '', $adapter->getFileName()), 1);

						$post['url'] = $filesName;

						$homeworkAnswer->exchangeArray($post);
						$this->getHomeworkAnswersTable()->saveHomeworkAnswer($homeworkAnswer);

						return $this->redirect()->toRoute('student/homework', array('id' => $homeworkId)); // tuleks midagi paremat välja mõelda
					}
				}
			}
		}
		return array(
			'form' => $form,
			'id' => $homeworkId,
		);
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

	public function getUserTable() {
		if (!$this->userTable) {
			$sm = $this->getServiceLocator();
			$this->userTable = $sm->get('EksamiKeskkond\Model\UserTable');
		}
		return $this->userTable;
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

	public function getNoteTable() {
		if (!$this->noteTable) {
			$sm = $this->getServiceLocator();
			$this->noteTable = $sm->get('EksamiKeskkond\Model\NoteTable');
		}
		return $this->noteTable;
	}

	public function getUserLessonTable() {
		if (!$this->userLessonTable) {
			$sm = $this->getServiceLocator();
			$this->userLessonTable = $sm->get('EksamiKeskkond\Model\UserLessonTable');
		}
		return $this->userLessonTable;
	}

	public function getHomeworkTable() {
		if (!$this->homeworkTable) {
			$sm = $this->getServiceLocator();
			$this->homeworkTable = $sm->get('EksamiKeskkond\Model\HomeworkTable');
		}
		return $this->homeworkTable;
	}

	public function getHomeworkAnswersTable() {
		if (!$this->homeworkAnswersTable) {
			$sm = $this->getServiceLocator();
			$this->homeworkAnswersTable = $sm->get('EksamiKeskkond\Model\HomeworkAnswersTable');
		}
		return $this->homeworkAnswersTable;
	}
}