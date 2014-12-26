<?php

namespace Eksamikool\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Eksamikool\Form\EmailForm;
use Eksamikool\Form\CourseForm;

use Eksamikool\Filter\EmailFilter;
use Eksamikool\Filter\CourseFilter;

use Eksamikool\Model\Course;
use Eksamikool\Model\UserTable;

use Eksamikool\Service\EmailService;

class AdminController extends AbstractActionController {

	protected $userTable;

	protected $courseTable;

	protected $userCourseTable;

	protected $subjectTable;

	protected $lessonTable;

	protected $subsubjectTable;

	public function indexAction() {
		return $this->redirect()->toRoute('admin/courses');
	}

	public function courseAction() {
		$subsubjects = array();
		$lessons = array();
		
		$course = $this->getCourseTable()->getCourse($this->params()->fromRoute('id'));
		$subjects = $this->getSubjectTable()->getSubjectsByCourseId($course->id);
		
		$courseData = array();
		$courseData['course'] = $course;
		
		if (!$course) {
			return $this->redirect()->toRoute('errors');
		}
		$subjects = $this->getSubjectTable()->getSubjectsByCourseId($course->id);
	
		foreach ($subjects as $subjectKey => $subject) {
			$subsubjects = $this->getSubsubjectTable()->getSubsubjectsBySubjectId($subject->id);
	
			foreach ($subsubjects as $subsubjectKey => $subsubject) {
				$lessons = $this->getLessonTable()->getLessonsBySubsubjectId($subsubject->id);
	
				$subsubjects[$subsubjectKey] = get_object_vars($subsubject);
				$subsubjects[$subsubjectKey]['lessons'] = $lessons;
			}
			$subjects[$subjectKey] = get_object_vars($subject);
			$subjects[$subjectKey]['subsubjects'] = $subsubjects;
		}
		$courseData['subjects'] = $subjects;

		$viewmodel = new ViewModel();

		$sidebarView = new ViewModel();
		$sidebarView->setTemplate('admin/sidebar');
		$sidebarView->setVariables(array(
			'course' => $this->getCourseTable()->getCourse($this->params()->fromRoute('id')),
		));
		$viewmodel->addChild($sidebarView, 'sidebar');

		$viewmodel->setVariables(array(
				'courseData' => $courseData,
				'subjects' => $subjects,
		));
		return $viewmodel;
	}

	public function coursesAction() {
		return new ViewModel(array(
			'courses' => $this->getCourseTable()->fetchAll(),
		));
	}

	public function addCourseAction() {
		$allTeachers = $this->getUserTable()->getAllTeachersForSelect();
		$availableTeachers = array();

		foreach ($allTeachers as $id => $teacher) {
			$teacherCourse = $this->getCourseTable()->getCourseByTeacherId($id);

			if (empty($teacherCourse)) {
				$availableTeachers[$id] = $teacher;
			}
		}
		$form = new CourseForm();
		$form->get('teacher_id')->setValueOptions($availableTeachers);
		$request = $this->getRequest();

		if ($request->isPost()) {
			$course = new Course();

			$form->setInputFilter(new CourseFilter($this->getServiceLocator()));
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$course->exchangeArray($form->getData());
				$this->getCourseTable()->saveCourse($course);

				return $this->redirect()->toRoute('admin/courses');
			}
		}
		return array('form' => $form);
	}

	public function editCourseAction() {
		$id = $this->params()->fromRoute('id');
		$course = $this->getCourseTable()->getCourse($id);
		$currentTeacherId = $course->teacher_id;
		$allTeachers = $this->getUserTable()->getAllTeachersForSelect();
		$availableTeachers = array();

		foreach ($allTeachers as $key => $teacher) {
			$teacherCourse = $this->getCourseTable()->getCourseByTeacherId($key);

			if (empty($teacherCourse) || $currentTeacherId == $key) {
				$availableTeachers[$key] = $teacher;
			}
		}
		$form  = new CourseForm();
		$form->bind($course);
		$form->get('teacher_id')->setValueOptions($availableTeachers);
		$form->get('submit')->setAttribute('value', 'Muuda');

		$request = $this->getRequest();

		if ($request->isPost()) {
			$form->setInputFilter(new CourseFilter($this->getServiceLocator()));
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$this->getCourseTable()->saveCourse($form->getData());
			}
		}
		$viewmodel = new ViewModel();

		$sidebarView = new ViewModel();
		$sidebarView->setTemplate('admin/sidebar');
		$sidebarView->setVariables(array(
			'course' => $this->getCourseTable()->getCourse($this->params()->fromRoute('id')),
		));

		$viewmodel->addChild($sidebarView, 'sidebar');
		$viewmodel->setVariables(array(
			'id' => $id,
			'form' => $form,
			'course' => $this->getCourseTable()->getCourse($this->params()->fromRoute('id')),
		));
		return $viewmodel;
	}

	public function deleteCourseAction() {
		$this->getCourseTable()->deleteCourse($this->params()->fromRoute('id'));

		return $this->redirect()->toRoute('admin/courses');
	}

	public function emptyCourseAction() {
		$id = $this->params()->fromRoute('id');
		$this->getUserCourseTable()->emptyCourse($id);

		return $this->redirect()->toRoute('admin/course', array('id' => $id));
	}

	public function changeCourseVisibilityAction() {
		$id = $this->params()->fromRoute('id');
		$this->getCourseTable()->changeCourseVisibility($id);

		return $this->redirect()->toRoute('admin/course', array('id' => $id));
	}

	public function courseParticipantsAction() {
		$id = $this->params()->fromRoute('id');
		$courseParticipants = $this->getUserCourseTable()->getCourseParticipants($id);

		$users = array();

		foreach ($courseParticipants as $key => $courseParticipant) {
			$users[$key]['user'] = $this->getUserTable()->getUser($courseParticipant['id']);
			$users[$key]['status'] = $courseParticipant['status'];
			$users[$key]['is_paid_by_bill'] = $courseParticipant['is_paid_by_bill'];
		}

		$viewmodel = new ViewModel();
		
		$sidebarView = new ViewModel();
		$sidebarView->setTemplate('admin/sidebar');
		$sidebarView->setVariables(array(
				'course' => $this->getCourseTable()->getCourse($this->params()->fromRoute('id')),
		));
		
		$viewmodel->addChild($sidebarView, 'sidebar');
		$viewmodel->setVariables(array(
			'id' => $id,
			'participants' => $users,
		));
		return $viewmodel;
	}

	public function changeUserCourseStatusAction() {
		$courseId = $this->params()->fromRoute('course_id');
		$userId = $this->params()->fromRoute('user_id');
		$status = $this->params()->fromRoute('status');

		$this->getUserCourseTable()->changeStatus($userId, $courseId, $status);

		return $this->redirect()->toRoute('admin/students');
	}

	public function changeIsPaidByBillAction() {
		$courseId = $this->params()->fromRoute('course_id');
		$userId = $this->params()->fromRoute('user_id');
		$status = $this->params()->fromRoute('status');

		$this->getUserCourseTable()->changeIsPaidByBill($userId, $courseId, $status, true);
	
		return $this->redirect()->toRoute('admin/students');
	}

	public function sendEmailToUserAction() {
		$userId = $this->params()->fromRoute('user_id');
		$user = $this->getUserTable()->getUser($userId);

		$form = new EmailForm();
		$form->get('user_id')->setValue($userId);

		$request = $this->getRequest();

		if ($request->isPost()) {
			$emailService = $this->getServiceLocator()->get('emailservice');
			$transport = $this->getServiceLocator()->get('mail.transport');

			$form->setInputFilter(new EmailFilter($this->getServiceLocator()));
			$form->setData($request->getPost());
		
			if ($form->isValid()) {
				$config = $this->getServiceLocator()->get('Config');
				$formData = $form->getData();

				$emailService->sendEmail($user->email, $config['admin_email'], $formData['subject'], $formData['body'], $transport);

				return $this->redirect()->toRoute('admin/students');
			}
		}
		return array(
			'user_id' => $userId,
			'form' => $form,
		);
	}
	
	public function sendEmailToAllParticipantsAction() {
		$courseId = $this->params()->fromRoute('course_id');

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
				$config = $this->getServiceLocator()->get('Config');

				$participants = $this->getUserCourseTable()->getCourseParticipants($formData['course_id']);
				$userIds = array();

				foreach ($participants as $participant) {
					if ($participant['status'] == true) {
						$userIds[] = $participant['id'];
					}
				}
				$users = $this->getUserTable()->getUsersByIds($userIds);

				foreach ($users as $user) {
					$emailService->sendEmail($user->email, $config['admin_email'], $formData['subject'], $formData['body'], $transport);
				}
				return $this->redirect()->toRoute('admin/students');
			}
		}
		return array('form' => $form);
	}

	public function teachersAction() {
		$teachers = $this->getUserTable()->getAllTeachersForList();

		return new ViewModel(array(
			'teachers' => $teachers,
		));
	}

	public function studentsAction() {
		$courses = $this->getCourseTable()->fetchAll();
		$coursesData = array();

		foreach ($courses as $course) {
			$courseParticipants = $this->getUserCourseTable()->getCourseParticipants($course->id);
			$studentsData = array();
			
			foreach ($courseParticipants as $key => $courseParticipant) {
				$student = $this->getUserTable()->getUser($courseParticipant['id']);

				$studentsData[$key]['student'] = $student;
				$studentsData[$key]['status'] = $courseParticipant['status'];
				$studentsData[$key]['is_paid_by_bill'] = $courseParticipant['is_paid_by_bill'];
			}
			$coursesData[$course->id]['students'] = $studentsData;
			$coursesData[$course->id]['course'] = $course;
		}
		return new ViewModel(array(
			'coursesData' => $coursesData,
		));
	}

	public function getUserTable() {
		if (!$this->userTable) {
			$sm = $this->getServiceLocator();
			$this->userTable = $sm->get('Eksamikool\Model\UserTable');
		}
		return $this->userTable;
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

}