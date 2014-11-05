<?php

namespace EksamiKeskkond\Controller;

use Zend\View\Model\ViewModel;

use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;

class StudentController extends AbstractActionController {

	protected $courseTable;

	protected $userCourseTable;

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
		return new ViewModel(array(
			'course' => $this->getCourseTable()->getCourse($this->params()->fromRoute('id')),
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

	public function buyCourseAction() {
		$auth = new AuthenticationService();
		$user = $auth->getIdentity();

		$courseId = $this->params()->fromRoute('id');
		$this->getUserCourseTable()->buyCourse($user->id, $courseId);

		return $this->redirect()->toRoute('student/course', array('id' => $courseId));
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