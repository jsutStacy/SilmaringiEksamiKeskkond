<?php

namespace EksamiKeskkond\Controller;

use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;

use EksamiKeskkond\Model\Course;
use EksamiKeskkond\Form\CourseForm;
use EksamiKeskkond\Filter\CourseFilter;

use EksamiKeskkond\Model\Subject;
use EksamiKeskkond\Form\SubjectForm;
use EksamiKeskkond\Filter\SubjectFilter;

class TeacherController extends AbstractActionController {

	protected $courseTable;

	protected $subjectTable;

	protected $userCourseTable;

	protected $userTable;

	public function indexAction() {
		$auth = new AuthenticationService();

		$user = $auth->getIdentity();
		$course = $this->getCourseTable()->getCourseByTeacherId($user->id);

		if (!empty($course)) {
			return $this->redirect()->toRoute('teacher/course', array('id' => $course->id));
		}
		return new ViewModel();
	}

	public function courseAction() {
		$auth = new AuthenticationService();

		$user = $auth->getIdentity();
		$course =  $this->getCourseTable()->getCourse($this->params()->fromRoute('id'));
		$subjects = $this->getSubjectTable()->getSubjectsByCourseId($course);
		

		if (!$course) {
			return $this->redirect()->toRoute('errors');
		}
		$teacherId = $course->teacher_id;

		if ($teacherId == $user->id) {
			return new ViewModel(array(
				'course' => $course,
				'subjects' => $subjects,
			));
		}
		else {
			return $this->redirect()->toRoute('errors/no-permission');
		}
	}

	public function studentsAction() {
		$students = array();
		$studentIds = $this->getUserCourseTable()->getCourseParticipants($this->params()->fromRoute('id'));

		foreach ($studentIds as $id) {
			$students[] = $this->getUserTable()->getUser($id);
		}
		return new ViewModel(array(
			'students' => $students,
		));
	}

	public function addSubjectAction() {
		$courseId = $this->params()->fromRoute('id');
		$course = $this->getCourseTable()->getCourse($courseId);

		$form = new SubjectForm();
		$form->get('course_id')->setValue($courseId);
		$request = $this->getRequest();
	
		if ($request->isPost()) {
			$subject = new Subject();
	
			$form->setInputFilter(new SubjectFilter($this->getServiceLocator()));
			$form->setData($request->getPost());
	
			if ($form->isValid()) {
				$subject->exchangeArray($form->getData());
				$this->getSubjectTable()->saveSubject($subject);
	
				return $this->redirect()->toRoute('teacher/course', array('id' => $courseId));
			}
		}
		return array(
			'form' => $form,
			'courseId' => $courseId,
		);
	}
	
	public function editSubjectAction() {
		$id = $this->params()->fromRoute('id');
		$subject = $this->getSubjectTable()->getSubject($id);
		$course = $this->getCourseTable()->getCourse($subject->course_id);

		$form  = new SubjectForm();
		$form->bind($subject);
		$form->get('course_id')->setValue($course->id);
		$form->get('submit')->setAttribute('value', 'Muuda');

		$request = $this->getRequest();

		if ($request->isPost()) {
			$form->setInputFilter(new SubjectFilter($this->getServiceLocator()));
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$this->getSubjectTable()->saveSubject($form->getData());

				return $this->redirect()->toRoute('teacher/course', array('id' => $course->id));
			}
		}
		return array(
			'id' => $id,
			'form' => $form,
		);
	}
	
	public function deleteSubjectAction() {
		$id = $this->params()->fromRoute('id');
		$subject = $this->getSubjectTable()->getSubject($id);
		$course = $this->getCourseTable()->getCourse($subject->course_id);
		$this->getSubjectTable()->deleteSubject($this->params()->fromRoute('id'));
		
	
		return $this->redirect()->toRoute('teacher/course', array('id'=> $course->id));
	}

	public function courseSubjectsAction() {
		$id = $this->params()->fromRoute('id');
		$subjectIds = $this->getCourseSubjectTable()->getCourseSubjects($id);
		$subjects = $this->getSubjectTable()->getSubjectsByIds($subjectIds);
	
		return new ViewModel(array(
			'id' => $id,
			'subjects' => $subjects,
		));
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
}