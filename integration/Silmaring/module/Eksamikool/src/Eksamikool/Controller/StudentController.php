<?php

namespace Eksamikool\Controller;

use Zend\View\Model\ViewModel;
use Zend\ViewModel\JsonModel;

use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;

use Banklink\Bank;
use Banklink\bankLink;
use Banklink\shoppingCart;
use Exception;
use Eksamikool\Form\BanklinkForm;

use Zend\Mail\Message;

use Eksamikool\Model\Course;
use Eksamikool\Form\CourseForm;
use Eksamikool\Filter\CourseFilter;

use Eksamikool\Model\Subject;
use Eksamikool\Form\SubjectForm;
use Eksamikool\Filter\SubjectFilter;

use Eksamikool\Model\Note;
use Eksamikool\Form\NoteForm;

use Eksamikool\Form\HomeworkAnswerForm;
use Eksamikool\Model\HomeworkAnswers;

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
		$user = $this->identity();
		$course = $this->getUserCourseTable()->getCourseByUserId($user->getId());

		if (!empty($course)) {
			return $this->redirect()->toRoute('student/course', array('id' => $course->course_id));
		}
		return $this->redirect()->toRoute('student/all-courses');
	}

	public function courseAction() {
		$user = $this->identity();

		$subsubjects = array();
		$lessons = array();
		$course = array();
		$courseData = array();

		$course = $this->getCourseTable()->getCourse($this->params()->fromRoute('id'));
		$hasBoughtCourse = $this->getUserCourseTable()->checkIfUserHasBoughtCourse($user->getId(), $course->id);
		$status = $this->getUserCourseTable()->checkIfUserHasAccessToCourse($user->getId(), $course->id);

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
						$userLesson = $this->getUserLessonTable()->getUserLesson($user->getId(), $lesson->id);

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
			'userId' => $user->getId(),
			'activated' => $status,
		));
	}

	public function lessonAction() {
		$user = $this->identity();

		$id = $this->params()->fromRoute('id');

		$request = $this->getRequest();

		$viewmodel = new ViewModel();
		$viewmodel->setTerminal($request->isXmlHttpRequest());
		$viewmodel->setVariables(array(
			'lesson' => $this->getLessonTable()->getLesson($id),
			'lessonFiles' => $this->getLessonFilesTable()->getLessonFilesByLessonId($id),
			'isLessonMarkedDone' => $this->getUserLessonTable()->getUserLesson($user->getId(), $id),
			'notes' => $this->getNoteTable()->getNotesByUserIdAndLessonId($user->getId(), $id),
		));
		return $viewmodel;
	}

	public function allCoursesAction() {
		$user = $this->identity();
		var_dump($user); die;
		$studentCoursesIds = $this->getUserCourseTable()->getAllCoursesByUserId($user->getId());

		$teachers = $this->getUserTable()->getAllTeachersForList();
		$courses = $this->getCourseTable()->fetchAll();
		$coursesData = array();

		foreach ($courses as $key => $course) {
			$coursesData[$key]['course'] = $course;
			$coursesData[$key]['hasBought'] = $this->getUserCourseTable()->checkIfUserHasBoughtCourse($user->getId(), $course->id);
			$coursesData[$key]['activated'] = $this->getUserCourseTable()->checkIfUserHasAccessToCourse($user->getId(), $course->id);
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

		$user = $this->identity();
		$studentCoursesIds = $this->getUserCourseTable()->getAllCoursesByUserId($user->getId());

		$myCourses = array();

		$courseTeachers = array();

		foreach ($studentCoursesIds as $key => $courseId) {
			$course = $this->getCourseTable()->getCourse($courseId);
			$myCourses[$key]['course'] = $course;
			$myCourses[$key]['activated'] = $this->getUserCourseTable()->checkIfUserHasAccessToCourse($user->getId(), $course->id);

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

		 
		$user = $this->identity();

		$bankName = $this->params()->fromRoute('bank');
		$bankLinkPreferences = $this->getServiceLocator()->get('Config')['bankLinkPreferences'][$bankName];
		$bankPreferences = $this->getServiceLocator()->get('Config')['bankPreferences'][$bankName];

		date_default_timezone_set ( "Europe/Tallinn" );
		$time=time();
		$timestamp = date("Y-m-d", $time) . 'T' . date("H:i:s", $time) .'+0200';

		$stamp = 500; //Random
		$returnUrl = $this->url()->fromRoute('student/buy-course',  array('id' => $courseId, 'bank' => $bankName), array('force_canonical' => true));

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
			'VK_RETURN' => $this->to_banklink_ch($returnUrl, $bankPreferences['charset']),
			'VK_CANCEL' => $this->to_banklink_ch($returnUrl, $bankPreferences['charset']),
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
					$this->getUserCourseTable()->buyCourse($user->getId(), $course->id, true);
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
		 
		$user = $this->identity();

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

		$this->getUserCourseTable()->buyCourse($user->getId(), $course->id, null, true);

		return $this->redirect()->toRoute('student/my-courses');
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
		 
		$user = $this->identity();

		$lessonId = $this->params()->fromRoute('lesson_id');

		$form = new NoteForm();
		$form->get('user_id')->setValue($user->getId());
		$form->get('lesson_id')->setValue($lessonId);

		$request = $this->getRequest();
		$response = $this->getResponse();

		if ($request->isPost()) {
			$note = new Note();
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$note->exchangeArray($form->getData());
				$noteId = $this->getNoteTable()->saveNote($note);

				$html = '<div class="alert alert-warning" id="note'.$noteId.'">
									<p>'. $note->content .'</p>
									<a class="btn btn-default btn-xs edit-note" href="'.$this->url()->fromRoute('student/edit-note',  array('id' => $noteId )).'">
										<span class="glyphicon glyphicon-pencil"></span>
									</a>
									<a class="btn btn-default btn-xs delete-note" href="'.$this->url()->fromRoute('student/delete-note',  array('id' => $noteId )).'">
										<span class="glyphicon glyphicon-trash"></span>
									</a>
								</div>';

				$response->setContent(\Zend\Json\Json::encode(array(
					'response' => true,
					'html' => $html,
				)));
				return $response;
			}
		}
		else{
			$viewmodel = new ViewModel();
			$viewmodel->setTerminal($request->isXmlHttpRequest());
			$viewmodel->setVariables(array(
				'form' => $form,
				'lessonId' => $lessonId,
				'userId' => $user->getId(),
			));
			return $viewmodel;
		}

	}
	
	public function editNoteAction() {
		
		 
		$user = $this->identity();

		$id = $this->params()->fromRoute('id');
		$note = $this->getNoteTable()->getNote($id);

		$request = $this->getRequest();
		$response = $this->getResponse();

		if ($request->isPost()) {
			$form = new NoteForm();
			$form->bind($note);
			$form->get('user_id')->setValue($user->getId());
			$form->get('lesson_id')->setValue($note->lesson_id);
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$this->getNoteTable()->saveNote($note);

				$response->setContent(\Zend\Json\Json::encode(array(
						'response' => true,
						'content' => $form->getData()->content,
						'noteId' => $note->id,
				)));
				return $response;
			}
		}
		else{
			$form = new NoteForm();
			$form->bind($note);
			$form->get('user_id')->setValue($user->getId());
			$form->get('lesson_id')->setValue($note->lesson_id);
			$form->get('submit')->setAttribute('value', 'Muuda');

			$viewmodel = new ViewModel();
			$viewmodel->setTerminal($request->isXmlHttpRequest());
			$viewmodel->setVariables(array(
				'form' => $form,
				'id' => $id,
			));
			return $viewmodel;
		}
		die;
	}
	
	public function deleteNoteAction() {
		$this->getNoteTable()->deleteNote($this->params()->fromRoute('id'));

		$response = $this->getResponse();
		$response->setContent(\Zend\Json\Json::encode(array(
				'response' => true,
		)));
		return $response;
	}

	public function allNotesAction() {
		 
		$user = $this->identity();

		$coursesData = array();
		$courseIds = $this->getUserCourseTable()->getAllCoursesByUserId($user->getId());

		foreach ($courseIds as $courseId) {
			$course = $this->getCourseTable()->getCourse($courseId);
			$subjects = $this->getSubjectTable()->getSubjectsByCourseId($courseId);

			$subjectsData = array();

			foreach ($subjects as $subject) {
				$subjectsData[$subject->id] = $subject;
				$subsubjects = $this->getSubsubjectTable()->getSubsubjectsBySubjectId($subject->id);

				$subsubjectsData = array();

				foreach ($subsubjects as $subsubject) {
					$subsubjectsData[$subsubject->id] = $subsubject;
					$lessons = $this->getLessonTable()->getLessonsBySubsubjectId($subsubject->id);

					$lessonsData = array();

					foreach ($lessons as $lesson) {
						$lessonsData[$lesson->id] = $lesson;
						$notes = $this->getNoteTable()->getNotesByUserIdAndLessonId($user->getId(), $lesson->id);

						$notesData = array();

						foreach ($notes as $note) {
							$notesData[$note->id] = $note;
						}
						$lessonsData[$lesson->id]->notes = $notesData;
					}
					$subsubjectsData[$subsubject->id]->lessons = $lessonsData;
				}
				$subjectsData[$subject->id]->subsubjects = $subsubjectsData;
			}
			$coursesData[$courseId] = $course;
			$coursesData[$courseId]->subjects = $subjectsData;
		}
		return new ViewModel(array(
			'courses' => $coursesData,
			'subjects' => $subjectsData,
			'subsubjects' => $subsubjectsData,
			'lessons' => $lessonsData,
			'notes' => $notesData,
		));
	}

	public function markLessonDoneAction() {
		 
		$user = $this->identity();

		$response = $this->getResponse();

		$this->getUserLessonTable()->markLessonDone($user->getId(), $this->params()->fromRoute('id'));

		$response->setContent(\Zend\Json\Json::encode(array(
			'response' => true,
		)));
		return $response;
	}

	public function homeworkAction() {
		 
		$user = $this->identity();

		$homeworkId = $this->params()->fromRoute('id');

		$request = $this->getRequest();

		$viewmodel = new ViewModel();
		$viewmodel->setTerminal($request->isXmlHttpRequest());
		$viewmodel->setVariables(array(
			'homework' => $this->getHomeworkTable()->getHomework($homeworkId),
			'homeworkAnswer' => $this->getHomeworkAnswersTable()->getHomeworkAnswerByUserIdAndHomeworkId($user->getId(), $homeworkId),
		));
		return $viewmodel;
	}

	public function addHomeworkAnswerAction() {
		 
		$user = $this->identity();

		$homeworkId = $this->params()->fromRoute('id');

		$form = new HomeworkAnswerForm();
		$form->get('homework_id')->setValue($homeworkId);
		$form->get('user_id')->setValue($user->getId());

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
			$this->courseTable = $sm->get('Eksamikool\Model\CourseTable');
		}
		return $this->courseTable;
	}

	public function getUserCourseTable() {
		if (!$this->userCourseTable) {
			$sm = $this->getServiceLocator();
			$this->userCourseTable = $sm->get('Eksamikool\Model\UserCourseTable');
		}
		return $this->userCourseTable;
	}

	public function getUserTable() {
		if (!$this->userTable) {
			$sm = $this->getServiceLocator();
			$this->userTable = $sm->get('Eksamikool\Model\UserTable');
		}
		return $this->userTable;
	}

	public function getSubjectTable() {
		if (!$this->subjectTable) {
			$sm = $this->getServiceLocator();
			$this->subjectTable = $sm->get('Eksamikool\Model\SubjectTable');
		}
		return $this->subjectTable;
	}

	public function getSubsubjectTable() {
		if (!$this->subsubjectTable) {
			$sm = $this->getServiceLocator();
			$this->subsubjectTable = $sm->get('Eksamikool\Model\SubsubjectTable');
		}
		return $this->subsubjectTable;
	}

	public function getLessonTable() {
		if (!$this->lessonTable) {
			$sm = $this->getServiceLocator();
			$this->lessonTable = $sm->get('Eksamikool\Model\LessonTable');
		}
		return $this->lessonTable;
	}

	public function getLessonFilesTable() {
		if (!$this->lessonFilesTable) {
			$sm = $this->getServiceLocator();
			$this->lessonFilesTable = $sm->get('Eksamikool\Model\LessonFilesTable');
		}
		return $this->lessonFilesTable;
	}

	public function getNoteTable() {
		if (!$this->noteTable) {
			$sm = $this->getServiceLocator();
			$this->noteTable = $sm->get('Eksamikool\Model\NoteTable');
		}
		return $this->noteTable;
	}

	public function getUserLessonTable() {
		if (!$this->userLessonTable) {
			$sm = $this->getServiceLocator();
			$this->userLessonTable = $sm->get('Eksamikool\Model\UserLessonTable');
		}
		return $this->userLessonTable;
	}

	public function getHomeworkTable() {
		if (!$this->homeworkTable) {
			$sm = $this->getServiceLocator();
			$this->homeworkTable = $sm->get('Eksamikool\Model\HomeworkTable');
		}
		return $this->homeworkTable;
	}

	public function getHomeworkAnswersTable() {
		if (!$this->homeworkAnswersTable) {
			$sm = $this->getServiceLocator();
			$this->homeworkAnswersTable = $sm->get('Eksamikool\Model\HomeworkAnswersTable');
		}
		return $this->homeworkAnswersTable;
	}
}