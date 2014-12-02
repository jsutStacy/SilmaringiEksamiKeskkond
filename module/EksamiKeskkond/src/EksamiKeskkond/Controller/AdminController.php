<?php

namespace EksamiKeskkond\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use EksamiKeskkond\Form\CourseForm;
use EksamiKeskkond\Filter\CourseFilter;

use EksamiKeskkond\Model\Course;

class AdminController extends AbstractActionController {

	protected $userTable;

	protected $courseTable;

	protected $userCourseTable;

	public function indexAction() {
		return $this->redirect()->toRoute('admin/courses');
	}

	public function courseAction() {
		return new ViewModel(array(
			'course' => $this->getCourseTable()->getCourse($this->params()->fromRoute('id')),
		));
	}

	public function coursesAction() {
		return new ViewModel(array(
			'courses' => $this->getCourseTable()->fetchAll(),
		));
	}

	public function addCourseAction() {
		$teachers = $this->getUserTable()->getAllTeachersForSelect();

		$form = new CourseForm();
		$form->get('teacher_id')->setValueOptions($teachers);
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
		$teachers = $this->getUserTable()->getAllTeachersForSelect();

		$form  = new CourseForm();
		$form->bind($course);
		$form->get('teacher_id')->setValueOptions($teachers);
		$form->get('submit')->setAttribute('value', 'Muuda');

		$request = $this->getRequest();

		if ($request->isPost()) {
			$form->setInputFilter(new CourseFilter($this->getServiceLocator()));
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$this->getCourseTable()->saveCourse($form->getData());

				return $this->redirect()->toRoute('admin/courses');
			}
		}

		return array(
			'id' => $id,
			'form' => $form,
		);
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
		}
		return new ViewModel(array(
			'id' => $id,
			'participants' => $users,
		));
	}

	public function changeUserCourseStatusAction() {
		$courseId = $this->params()->fromRoute('course_id');
		$userId = $this->params()->fromRoute('user_id');
		$status = $this->params()->fromRoute('status');

		$this->getUserCourseTable()->changeStatus($userId, $courseId, $status);

		return $this->redirect()->toRoute('admin/students');
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
			$this->userTable = $sm->get('EksamiKeskkond\Model\UserTable');
		}
		return $this->userTable;
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
}