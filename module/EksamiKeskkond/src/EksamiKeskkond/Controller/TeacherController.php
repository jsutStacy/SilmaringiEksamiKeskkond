<?php

namespace EksamiKeskkond\Controller;

use Zend\View\Model\ViewModel;

use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;

class TeacherController extends AbstractActionController {

	protected $courseTable;

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
		return new ViewModel(array(
			'course' => $this->getCourseTable()->getCourse($this->params()->fromRoute('id')),
		));
	}

	public function getCourseTable() {
		if (!$this->courseTable) {
			$sm = $this->getServiceLocator();
			$this->courseTable = $sm->get('EksamiKeskkond\Model\CourseTable');
		}
		return $this->courseTable;
	}
}