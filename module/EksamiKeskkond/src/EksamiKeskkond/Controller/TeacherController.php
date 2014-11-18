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

use EksamiKeskkond\Model\Subsubject;
use EksamiKeskkond\Form\SubsubjectForm;
use EksamiKeskkond\Filter\SubsubjectFilter;

class TeacherController extends AbstractActionController {

	protected $courseTable;

	protected $subjectTable;
	
	protected $subsubjectTable;

	protected $userCourseTable;

	protected $userTable;

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
		$course = $this->getCourseTable()->getCourseByTeacherId($user->id);
		$subjects = $this->getSubjectTable()->getSubjectsByCourseId($course->id);

		if (!$course) {
			return $this->redirect()->toRoute('errors');
		}
		if ($course->teacher_id == $user->id) {
			return new ViewModel(array(
				'course' => $course,
				'subjects' => $subjects,
				'subsubjectTable' => $this->getSubsubjectTable(),
			));
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
			return new ViewModel(array(
				'students' => $studentsData,
			));
		}
		return $this->redirect()->toRoute('errors/no-permission');
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
	
				return $this->redirect()->toRoute('teacher/my-course');
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

				return $this->redirect()->toRoute('teacher/my-course');
			}
		}
		return array(
			'id' => $id,
			'form' => $form,
		);
	}

	public function deleteSubjectAction() {
		$this->getSubjectTable()->deleteSubject($this->params()->fromRoute('id'));

		return $this->redirect()->toRoute('teacher/my-course');
	}

	/*kommenteerin praegu valja, sest tundub, et seda pole vaja, aga kindel pole
	public function courseSubjectsAction() {
		$id = $this->params()->fromRoute('id');
		$subjectIds = $this->getCourseSubjectTable()->getCourseSubjects($id);
		$subjects = $this->getSubjectTable()->getSubjectsByIds($subjectIds);
	
		return new ViewModel(array(
			'id' => $id,
			'subjects' => $subjects,
		));
	}
	*/

	public function addSubsubjectAction() {
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
				$this->getSubsubjectTable()->saveSubsubject($subsubject);

				return $this->redirect()->toRoute('teacher/my-course');
			}
		}
		return array(
				'form' => $form,
				'subjectId' => $subjectId,
		);
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
}