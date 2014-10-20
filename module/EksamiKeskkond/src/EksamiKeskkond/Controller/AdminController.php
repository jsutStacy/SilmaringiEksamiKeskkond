<?php

namespace EksamiKeskkond\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use EksamiKeskkond\Form\CourseForm;
use EksamiKeskkond\Model\Course;

class AdminController extends AbstractActionController {

	protected $courseTable;

	public function indexAction() {
		return new ViewModel();
	}

	public function coursesAction() {
		return new ViewModel(array(
			'courses' => $this->getCourseTable()->fetchAll(),
		));
	}

	public function addCourseAction() {
		$form = new CourseForm();
		$request = $this->getRequest();

		if ($request->isPost()) {
			$course = new Course();

			$form->setInputFilter($course->getInputFilter());
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
		$id = (int) $this->params()->fromRoute('id', 0);
		$course = $this->getCourseTable()->getCourse($id);

		$form  = new CourseForm();
		$form->bind($course);
		$form->get('submit')->setAttribute('value', 'Muuda');

		$request = $this->getRequest();

		if ($request->isPost()) {
			$form->setInputFilter($course->getInputFilter());
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

	public function emptyCourseAction($id) {
		
	}

	public function changeCoursePriceAction($id) {
		
	}

	public function publishCourseAction($id) {
	
	}
	
	public function unpublishCourseAction($id) {
	
	}

	public function getCourseTable() {
		if (!$this->courseTable) {
			$sm = $this->getServiceLocator();
			$this->courseTable = $sm->get('EksamiKeskkond\Model\CourseTable');
		}
		return $this->courseTable;
	}
}