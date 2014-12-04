<?php

namespace EksamiKeskkond\Controller;

use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\ViewModel\JsonModel;

use EksamiKeskkond\Model\Course;
use EksamiKeskkond\Form\CourseForm;
use EksamiKeskkond\Filter\CourseFilter;

use EksamiKeskkond\Model\Subject;
use EksamiKeskkond\Form\SubjectForm;
use EksamiKeskkond\Filter\SubjectFilter;

use EksamiKeskkond\Model\Subsubject;
use EksamiKeskkond\Form\SubsubjectForm;
use EksamiKeskkond\Filter\SubsubjectFilter;

use EksamiKeskkond\Model\Lesson;
use EksamiKeskkond\Form\LessonForm;
use EksamiKeskkond\Filter\LessonFilter;

use EksamiKeskkond\Model\LessonFiles;

use EksamiKeskkond\Form\EmailForm;
use EksamiKeskkond\Filter\EmailFilter;

use EksamiKeskkond\Model\Homework;
use EksamiKeskkond\Form\HomeworkForm;
use EksamiKeskkond\Filter\HomeworkFilter;

use EksamiKeskkond\Service\EmailService;

class TeacherController extends AbstractActionController {

	protected $courseTable;

	protected $subjectTable;

	protected $subsubjectTable;

	protected $lessonTable;

	protected $userCourseTable;

	protected $userTable;

	protected $lessonFilesTable;

	protected $homeworkTable;

	public function indexAction() {
		$auth = new AuthenticationService();

		$user = $auth->getIdentity();
		$course = $this->getCourseTable()->getCourseByTeacherId($user->id);

		if (!empty($course)) {
			return $this->redirect()->toRoute('teacher/my-course');
		}
		return new ViewModel();
	}

	public function myCourseAction() {
		$auth = new AuthenticationService();
		$user = $auth->getIdentity();

		$request = $this->getRequest();

		$course = $this->getCourseTable()->getCourseByTeacherId($user->id);
		$subjects = $this->getSubjectTable()->getSubjectsByCourseId($course->id);

		if (!$course) {
			return $this->redirect()->toRoute('errors');
		}
		if ($course->teacher_id == $user->id) {
			$viewmodel = new ViewModel();
			
			$sidebarView = new ViewModel();
			$sidebarView->setTemplate('teacher/sidebar');

			$viewmodel->addChild($sidebarView, 'sidebar');

			$viewmodel->setVariables(array(
				'course' => $course,
				'subjects' => $subjects,
				'subsubjectTable' => $this->getSubsubjectTable(),
				'lessonTable' => $this->getLessonTable(),
			));
			return $viewmodel;
		}
		return $this->redirect()->toRoute('errors/no-permission');
	}

	public function studentsAction() {
		$auth = new AuthenticationService();

		$user = $auth->getIdentity();

		$studentsData = array();
		$course = $this->getCourseTable()->getCourseByTeacherId($user->id);

		if (!$course) {
			return $this->redirect()->toRoute('errors');
		}
		$students = $this->getUserCourseTable()->getCourseParticipants($course->id);

		foreach ($students as $key => $student) {
			$studentsData[$key]['data'] = $this->getUserTable()->getUser($student['id']);
			$studentsData[$key]['status'] = $student['status'];
		}
		if ($course->teacher_id == $user->id) {
			$viewmodel = new ViewModel();

			$sidebarView = new ViewModel();
			$sidebarView->setTemplate('teacher/sidebar');

			$viewmodel->addChild($sidebarView, 'sidebar');
			$viewmodel->setVariables(array(
				'students' => $studentsData,
				'courseId' => $course->id,
				'teacherId' => $course->teacher_id,
			));
			return $viewmodel;
		}
		return $this->redirect()->toRoute('errors/no-permission');
	}

	public function addSubjectAction() {
		$request = $this->getRequest();
		$response = $this->getResponse();

		$form = new SubjectForm();

		if ($request->isPost()) {
			$subject = new Subject();
			
			$form->setInputFilter(new SubjectFilter($this->getServiceLocator()));
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$subject->exchangeArray($form->getData());
				$data = $request->getPost();
				$subjectId = $this->getSubjectTable()->saveSubject($subject);
				$html =
					'<li class="list-group-item active" id="subjectId' . $subjectId . '">' . 
					'<p class="subjectName">' . $data->name . '</p>' .
						'<div class="btn-group" role="group" aria-label="...">'.
							'<a class="btn btn-default btn-xs editSubject" href="edit-subject/' . $subjectId . '">' .
								'<span class="glyphicon glyphicon-pencil"></span>' .
							'</a>' .
							'<a class="btn btn-default btn-xs" href="delete-subject/' . $subjectId . '">' .
								'<span class="glyphicon glyphicon-trash"></span>' .
							'</a>' .
							'</div>' .
							'<a class="btn btn-default btn-xs pull-right addSubsubject" href="'.$this->url()->fromRoute('teacher/add-subsubject',  array('id' => $subjectId )).'">' .
							'<span class="glyphicon glyphicon-plus"></span>Lisa uus alamteema</a>' .
					'</li>' .
					'<div class="panel-body">' .
						'<ul class="list-group subsubjects"></ul>'.
					'</div>';

				$response->setContent(\Zend\Json\Json::encode(array(
					'response' => true,
					'addedSubject' => $data,
					'html' => $html,
				)));
				return $response;
			}
		}
		else {
			$courseId = $this->params()->fromRoute('id');
			$course = $this->getCourseTable()->getCourse($courseId);
			$form->get('course_id')->setValue($courseId);

			$viewmodel = new ViewModel();
			$viewmodel->setTerminal($request->isXmlHttpRequest());
			$viewmodel->setVariables(array(
				'form' => $form,
				'courseId' => $courseId,
			));
			return $viewmodel;
		}
	}

	public function editDescriptionAction() {
		
		$request = $this->getRequest();
		$response = $this->getResponse();

		if ($request->isPost()) {

			$id = $request->getPost()->id;
			$course = $this->getCourseTable()->getCourse($id);
			
			$form  = new CourseForm();
			$form->bind($course);
			$form->setInputFilter(new CourseFilter($this->getServiceLocator())); //TODO: FORM IS NOT VALID

			if ($form->isValid()) {
				echo "jiu"; die;
				$this->getCourseTable()->saveCourse($form->getData());
				$response->setContent(\Zend\Json\Json::encode(array(
					'response' => true,
				)));
				
			}
			die;
			return $response;
		}
		else {
			$id = $this->params()->fromRoute('id');
			$course = $this->getCourseTable()->getCourse($id);

			$form  = new CourseForm();
			$form->bind($course);
			
			$viewmodel = new ViewModel();
			$viewmodel->setTerminal($request->isXmlHttpRequest());
			$viewmodel->setVariables(array(
				'form' => $form,
				'courseId' => $course->id,
				'id' => $id,
			));
			return $viewmodel;
		}
	}

	public function editSubjectAction() {
		$request = $this->getRequest();
		$response = $this->getResponse();

		if ($request->isPost()) {
			$id = $request->getPost()->id;
			$subject = $this->getSubjectTable()->getSubject($id);

			$form  = new SubjectForm();
			$form->bind($subject);
			$form->setInputFilter(new SubsubjectFilter($this->getServiceLocator()));
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$this->getSubjectTable()->saveSubject($form->getData());
				$response->setContent(\Zend\Json\Json::encode(array(
					'response' => true,
					'subjectId' => $id,
					'subjectName' => $form->getData()->name,
				)));
				return $response;
			}
		}
		else {
			$id = $this->params()->fromRoute('id');
			$subject = $this->getSubjectTable()->getSubject($id);
			$course = $this->getCourseTable()->getCourse($subject->course_id);

			$form  = new SubjectForm();
			$form->bind($subject);
			$form->get('course_id')->setValue($course->id);
			$form->get('id')->setValue($id);

			$viewmodel = new ViewModel();
			$viewmodel->setTerminal($request->isXmlHttpRequest());
			$viewmodel->setVariables(array(
				'form' => $form,
				'courseId' => $subject->course_id,
				'id' => $id,
			));
			return $viewmodel;
		}
	}

	public function deleteSubjectAction() {
		$this->getSubjectTable()->deleteSubject($this->params()->fromRoute('id'));

		return $this->redirect()->toRoute('teacher/my-course');
	}

	public function addSubsubjectAction() {
		$request = $this->getRequest();
		$response = $this->getResponse();

		$subjectId = $this->params()->fromRoute('id');
		$subject = $this->getSubjectTable()->getSubject($subjectId);

		$form = new SubsubjectForm();
		$form->get('subject_id')->setValue($subjectId);
		$request = $this->getRequest();

		if ($request->isPost()) {
			$subsubject = new Subsubject();

			$form->setInputFilter(new SubsubjectFilter($this->getServiceLocator()));
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$subsubject->exchangeArray($form->getData());
				$subsubjectId = $this->getSubsubjectTable()->saveSubsubject($subsubject);

				$html = 
					'<li class="list-group-item" id="subsubjectId'.$subsubjectId.'">
					<p class="subsubjectName pull-left">' .
						$form->getData()['name'] .
					'</p>
					<a class="btn btn-default btn-xs pull-left" href="delete-subsubject/'. $subsubjectId .'">
						<span class="glyphicon glyphicon-trash"></span>
					</a>
					<a class="btn btn-default btn-xs pull-left editSubsubject" href="edit-subsubject/'. $subsubjectId .'">
						<span class="glyphicon glyphicon-pencil"></span>
					</a>
					<div class="clearfix"></div>
					<ul class="list-group">
						<a class="row btn btn-default btn-xs pull-right" href="add-lesson/'. $subsubjectId .'">
						<span class="glyphicon glyphicon-plus"></span>Lisa uus tund</a>
					</ul>';

				$response->setContent(\Zend\Json\Json::encode(array(
					'response' => true,
					'subsubjectId' => $subsubjectId,
					'html' => $html,
				)));
				return $response;
			}
		}
		else {
			$viewmodel = new ViewModel();
			$viewmodel->setTerminal($request->isXmlHttpRequest());
			$viewmodel->setVariables(array(
				'form' => $form,
				'subjectId' => $subjectId,
			));
			return $viewmodel;
		}
	}
	
	public function editSubsubjectAction() {
		$request = $this->getRequest();
		$response = $this->getResponse();

		if ($request->isPost()) {
			$subsubject = $this->getSubsubjectTable()->getSubsubject($request->getPost()->id);

			$form = new SubsubjectForm();
			$form->bind($subsubject);
			$form->setInputFilter(new SubsubjectFilter($this->getServiceLocator()));
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$this->getSubsubjectTable()->saveSubsubject($form->getData());

				$response->setContent(\Zend\Json\Json::encode(array(
					'response' => true,
					'subsubjectId' => $form->getData()->id,
					'subsubjectName' => $form->getData()->name,
				)));
				return $response;
			}
		}
		else {
			$id = $this->params()->fromRoute('id');
			$subsubject = $this->getSubsubjectTable()->getSubsubject($id);

			$subject = $this->getSubjectTable()->getSubject($subsubject->subject_id);

			$form = new SubsubjectForm();
			$form->bind($subsubject);
			$form->get('subject_id')->setValue($subject->id);
			$form->get('submit')->setAttribute('value', 'Muuda');

			$viewmodel = new ViewModel();
			$viewmodel->setTerminal($request->isXmlHttpRequest());
			$viewmodel->setVariables(array(
				'form' => $form,
				'id' => $id,
			));
			return $viewmodel;
		}
	}

	public function deleteSubsubjectAction() {
		$this->getSubsubjectTable()->deleteSubsubject($this->params()->fromRoute('id'));

		return $this->redirect()->toRoute('teacher/my-course');
	}

	public function lessonAction() {
		return new ViewModel(array(
			'lesson' => $this->getLessonTable()->getLesson($this->params()->fromRoute('id')),
			'lessonFiles' => $this->getLessonFilesTable()->getLessonFilesByLessonId($this->params()->fromRoute('id')),
		));
	}

	public function addLessonAction() {
		$auth = new AuthenticationService();
		$user = $auth->getIdentity();

		$subsubjectId = $this->params()->fromRoute('id');
		$subsubject = $this->getSubsubjectTable()->getSubsubject($subsubjectId);

		$form = new LessonForm();
		$form->get('subsubject_id')->setValue($subsubjectId);
		$form->get('user_id')->setValue($user->id);

		$request = $this->getRequest();

		if ($request->isPost()) {
			$lesson = new Lesson();

			$form->setInputFilter(new LessonFilter($this->getServiceLocator()));
			$post = array_merge_recursive(
				$this->getRequest()->getPost()->toArray(),
				$this->getRequest()->getFiles()->toArray()
			);
			$form->setData($post);

			if ($form->isValid()) {
				$adapter = new \Zend\File\Transfer\Adapter\Http();
				$files = $adapter->getFileInfo();

				$filesUrls = array();
				$error = array();

				if ($post['type'] == 'images' || $post['type'] == 'audio' || $post['type'] == 'presentation') {
					foreach ($files as $key => $file) {
						if ($post['type'] == 'images') {
							$extension = new \Zend\Validator\File\Extension(array('extension' => array('jpg', 'jpeg', 'png')));
						}
						else if ($post['type'] == 'audio') {
							$extension = new \Zend\Validator\File\Extension(array('extension' => array('mp3', 'wav')));
							$size = new \Zend\Validator\File\Size(array(array('max' => 104857600)));
							$adapter->setValidators(array($size), $file['name']);
						}
						else if ($post['type'] == 'presentation') {
							$extension = new \Zend\Validator\File\Extension(array('extension' => array('pdf')));
						}
						else {
							$extension = new \Zend\Validator\File\Extension(array('extension' => array()));
						}
						$adapter->setValidators(array($extension), $file['name']);
					}
					if (!$adapter->isValid()) {
						foreach ($adapter->getMessages() as $key => $row) {
							$error[] = $row;
						}
						$form->setMessages(array('fileupload' => $error));
					}
					else {
						$adapter->setDestination($this->getServiceLocator()->get('Config')['upload_dir']);

						if ($adapter->receive()) {
							$event = $this->getEvent();
							$request = $event->getRequest();
							$router = $event->getRouter();
							$uri = $router->getRequestUri();
							$baseUrl = sprintf('%s://%s%s', $uri->getScheme(), $uri->getHost(), $request->getBaseUrl());

							$adapterFileName = $adapter->getFileName();

							if (is_array($adapterFileName)) {
								foreach ($adapterFileName as $name) {
									$fileName = preg_replace('/\.\/public\/uploads/', '', $name);
									$filesUrls[] = $baseUrl . '/uploads/' . substr($fileName, 1);
								}
							}
							else {
								$fileName = preg_replace('/\.\/public\/uploads/', '', $adapterFileName);
								$filesUrls[] = $baseUrl . '/uploads/' . substr($fileName, 1);
							}
						}
					}
				}
				else if ($post['type'] == 'video') {
					$filesUrls[] = $post['url'];
				}
				else if ($post['type'] == 'text') {
					$lesson->exchangeArray($post);
					$lessonId = $this->getLessonTable()->saveLesson($lesson);

					return $this->redirect()->toRoute('teacher/lesson', array('id' => $lessonId));
				}
				if (empty($error)) {
					$lesson->exchangeArray($post);
					$lessonId = $this->getLessonTable()->saveLesson($lesson);

					$post['lesson_id'] = $lessonId;

					foreach ($filesUrls as $fileUrl) {
						$lessonFiles = new LessonFiles();

						$post['url'] = $fileUrl;
						$lessonFiles->exchangeArray($post);

						$this->getLessonFilesTable()->saveLessonFiles($lessonFiles);
					}
					return $this->redirect()->toRoute('teacher/lesson', array('id' => $lessonId));
				}
			}
		}
		return array(
			'form' => $form,
			'subsubjectId' => $subsubjectId,
		);
	}

	public function editLessonAction() {
		$auth = new AuthenticationService();
		$user = $auth->getIdentity();

		$lessonId = $this->params()->fromRoute('id');
		$lesson = $this->getLessonTable()->getLesson($lessonId);
		$lessonFiles = $this->getLessonFilesTable()->getLessonFilesByLessonId($lessonId);

		$subsubject = $this->getSubsubjectTable()->getSubsubject($lesson->subsubject_id);

		$form = new LessonForm();

		$form->bind($lesson);
		$form->get('submit')->setAttribute('value', 'Muuda');
		$form->get('id')->setValue($lessonId);
		$form->get('user_id')->setValue($user->id);
		$form->get('subsubject_id')->setValue($subsubject->id);

		$request = $this->getRequest();

		if ($request->isPost()) {
			$form->setInputFilter(new LessonFilter($this->getServiceLocator()));
			$post = array_merge_recursive(
					$this->getRequest()->getPost()->toArray(),
					$this->getRequest()->getFiles()->toArray()
			);
			$form->setData($post);

			if ($form->isValid()) {
				$adapter = new \Zend\File\Transfer\Adapter\Http();
				$files = $adapter->getFileInfo();

				$filesUrls = array();
				$error = array();

				if ($post['type'] == 'images' || $post['type'] == 'audio' || $post['type'] == 'presentation') {
					foreach ($files as $key => $file) {
						if ($post['type'] == 'images') {
							$extension = new \Zend\Validator\File\Extension(array('extension' => array('jpg', 'jpeg', 'png')));
						}
						else if ($post['type'] == 'audio') {
							$extension = new \Zend\Validator\File\Extension(array('extension' => array('mp3', 'wav')));
							$size = new \Zend\Validator\File\Size(array(array('max' => 104857600)));
							$adapter->setValidators(array($size), $file['name']);
						}
						else if ($post['type'] == 'presentation') {
							$extension = new \Zend\Validator\File\Extension(array('extension' => array('pdf')));
						}
						else {
							$extension = new \Zend\Validator\File\Extension(array('extension' => array()));
						}
						$adapter->setValidators(array($extension), $file['name']);
					}
					if (!$adapter->isValid()) {
						foreach ($adapter->getMessages() as $key => $row) {
							$error[] = $row;
						}
						$form->setMessages(array('fileupload' => $error));
					}
					else {
						$adapter->setDestination($this->getServiceLocator()->get('Config')['upload_dir']);

						if ($adapter->receive()) {
							$event = $this->getEvent();
							$request = $event->getRequest();
							$router = $event->getRouter();
							$uri = $router->getRequestUri();
							$baseUrl = sprintf('%s://%s%s', $uri->getScheme(), $uri->getHost(), $request->getBaseUrl());

							$adapterFileName = $adapter->getFileName();

							if (is_array($adapterFileName)) {
								foreach ($adapterFileName as $name) {
									$fileName = preg_replace('/\.\/public\/uploads/', '', $name);
									$filesUrls[] = $baseUrl . '/uploads/' . substr($fileName, 1);
								}
							}
							else {
								$fileName = preg_replace('/\.\/public\/uploads/', '', $adapterFileName);
								$filesUrls[] = $baseUrl . '/uploads/' . substr($fileName, 1);
							}
						}
					}
				}
				else if ($post['type'] == 'video') {
					$filesUrls[] = $post['url'];
				}
				else if ($post['type'] == 'text') {
					$lesson->exchangeArray($post);
					$this->getLessonTable()->saveLesson($lesson);
					$this->getLessonFilesTable()->deleteLessonFilesByLessonId($lessonId);

					return $this->redirect()->toRoute('teacher/lesson', array('id' => $lessonId));
				}
				if (empty($error)) {
					if ($lesson->type != $post['type'] || $post['type'] == 'presentation' || $post['type'] == 'audio') {
						$this->getLessonFilesTable()->deleteLessonFilesByLessonId($lessonId);
					}
					$lesson->exchangeArray($post);
					$this->getLessonTable()->saveLesson($lesson);

					$post['lesson_id'] = $post['id'];
					$post['id'] = 0;

					foreach ($filesUrls as $fileUrl) {
						$lessonFiles = new LessonFiles();

						$post['url'] = $fileUrl;
						$lessonFiles->exchangeArray($post);

						$this->getLessonFilesTable()->saveLessonFiles($lessonFiles);
					}
					return $this->redirect()->toRoute('teacher/lesson', array('id' => $lessonId));
				}
			}
		}
		return array(
			'id' => $lessonId,
			'form' => $form,
			'lessonFiles' => $lessonFiles,
			'lessonType' => $lesson->type,
		);
	}

	public function deleteLessonAction() {
		$this->getLessonTable()->deleteLesson($this->params()->fromRoute('id'));

		return $this->redirect()->toRoute('teacher/my-course');
	}

	public function deleteLessonFileAction() {
		$response = $this->getResponse();

		$this->getLessonFilesTable()->deleteLessonFile($this->params()->fromRoute('id'));

		$response->setContent(\Zend\Json\Json::encode(array(
			'response' => true,
			'info' => 'Fail kustutatud',
		)));
		return $response;
	}

	public function sendEmailToUserAction() {
		$userId = $this->params()->fromRoute('user_id');
		$user = $this->getUserTable()->getUser($userId);

		$teacherId = $this->params()->fromRoute('teacher_id');
		$teacher = $this->getUserTable()->getUser($teacherId);

		$form = new EmailForm();
		$form->get('user_id')->setValue($userId);
		$request = $this->getRequest();

		if ($request->isPost()) {
			$emailService = $this->getServiceLocator()->get('emailservice');
			$transport = $this->getServiceLocator()->get('mail.transport');

			$form->setInputFilter(new EmailFilter($this->getServiceLocator()));
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$formData = $form->getData();
				$emailService->sendEmail($user->email, $teacher->email, $formData['subject'], $formData['body'], $transport);

				return $this->redirect()->toRoute('teacher/students');
			}
		}
		return array(
			'user_id' => $userId,
			'teacher_id' => $teacherId,
			'form' => $form,
		);
	}
	
	public function sendEmailToAllParticipantsAction() {
		$courseId = $this->params()->fromRoute('course_id');
		$course = $this->getCourseTable()->getCourse($courseId);

		$teacherId = $course->teacher_id;
		$teacher = $this->getUserTable()->getUser($teacherId);

		$form = new EmailForm();
		$form->get('course_id')->setValue($courseId);

		$request = $this->getRequest();

		if ($request->isPost()) {
			$emailService = $this->getServiceLocator()->get('emailservice');
			$transport = $this->getServiceLocator()->get('mail.transport');

			$form->setInputFilter(new EmailFilter($this->getServiceLocator()));
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$formData = $form->getData();

				$participants = $this->getUserCourseTable()->getCourseParticipants($formData['course_id']);
				$userIds = array();

				foreach ($participants as $participant) {
					if ($participant['status'] == true) {
						$userIds[] = $participant['id'];
					}
				}
				$users = $this->getUserTable()->getUsersByIds($userIds);
	
				foreach ($users as $user) {
					$emailService->sendEmail($user->email, $teacher->email, $formData['subject'], $formData['body'], $transport);
				}
				return $this->redirect()->toRoute('teacher/students');
			}
		}
		return array(
			'form' => $form,
			'teacher_id' => $teacherId,
			'course_id' => $courseId,
		);
	}

	public function homeworkAction() {
		return new ViewModel(array(
			'homework' => $this->getHomeworkTable()->getHomework($this->params()->fromRoute('id')),
		));
	}

	public function addHomeworkAction() {
		$auth = new AuthenticationService();
		$user = $auth->getIdentity();

		$subsubjectId = $this->params()->fromRoute('subsubject_id');

		$form = new HomeworkForm();
		$form->get('user_id')->setValue($user->id);
		$form->get('subsubject_id')->setValue($subsubjectId);

		$request = $this->getRequest();

		if ($request->isPost()) {
			$homework = new Homework();

			$form->setInputFilter(new HomeworkFilter($this->getServiceLocator()));
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

						$homework->exchangeArray($post);
						$this->getHomeworkTable()->saveHomework($homework);

						return $this->redirect()->toRoute('teacher/my-course');
					}
				}
			}
		}
		return array(
			'form' => $form,
			'subsubjectId' => $subsubjectId,
		);
	}

	public function editHomeworkAction() {
		$auth = new AuthenticationService();
		$user = $auth->getIdentity();

		$id = $this->params()->fromRoute('id');
		$homework = $this->getHomeworkTable()->getHomework($id);

		$form = new HomeworkForm();
		$form->bind($homework);

		$form->get('submit')->setAttribute('value', 'Muuda');
		$form->get('id')->setValue($id);
		$form->get('user_id')->setValue($user->id);
		$form->get('subsubject_id')->setValue($homework->subsubject_id);

		$request = $this->getRequest();

		if ($request->isPost()) {
			$form->setInputFilter(new HomeworkFilter($this->getServiceLocator()));
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

						$homework->exchangeArray($post);
						$this->getHomeworkTable()->saveHomework($homework);

						return $this->redirect()->toRoute('teacher/my-course');
					}
				}
			}
		}
		return array(
			'form' => $form,
			'id' => $id,
			'homework' => $homework,
		);
	}

	public function deleteHomeworkFileAction() {
		$response = $this->getResponse();

		$this->getHomeworkTable()->deleteHomeworkFile($this->params()->fromRoute('id'));

		$response->setContent(\Zend\Json\Json::encode(array(
			'response' => true,
			'info' => 'Fail kustutatud',
		)));
		return $response;
	}

	public function getCourseTable() {
		if (!$this->courseTable) {
			$sm = $this->getServiceLocator();
			$this->courseTable = $sm->get('EksamiKeskkond\Model\CourseTable');
		}
		return $this->courseTable;
	}

	public function getSubjectTable() {
		if (!$this->subjectTable) {
			$sm = $this->getServiceLocator();
			$this->subjectTable = $sm->get('EksamiKeskkond\Model\SubjectTable');
		}
		return $this->subjectTable;
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

	public function getSubsubjectTable() {
		if (!$this->subsubjectTable) {
			$sm = $this->getServiceLocator();
			$this->subsubjectTable = $sm->get('EksamiKeskkond\Model\SubsubjectTable');
		}
		return $this->subsubjectTable;
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

	public function getHomeworkTable() {
		if (!$this->homeworkTable) {
			$sm = $this->getServiceLocator();
			$this->homeworkTable = $sm->get('EksamiKeskkond\Model\HomeworkTable');
		}
		return $this->homeworkTable;
	}
}