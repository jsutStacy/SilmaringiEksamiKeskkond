<?php

namespace EksamiKeskkond\Controller;

use Zend\View\Model\ViewModel;

use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;

use Zend\Mail\Message;

use EksamiKeskkond\Model\Course;
use EksamiKeskkond\Form\CourseForm;
use EksamiKeskkond\Filter\CourseFilter;

use EksamiKeskkond\Model\Subject;
use EksamiKeskkond\Form\SubjectForm;
use EksamiKeskkond\Filter\SubjectFilter;

class StudentController extends AbstractActionController {

	protected $courseTable;

	protected $userCourseTable;

	protected $userTable;

	protected $subjectTable;

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

		$course = $this->getCourseTable()->getCourse($this->params()->fromRoute('id'));
		$subjects = array();
		$hasBoughtCourse = $this->getUserCourseTable()->checkIfUserHasBoughtCourse($user->id, $course->id);
		
		if (!$course) {
			return $this->redirect()->toRoute('errors');
		}
		if ($hasBoughtCourse) {
			$subjects = $this->getSubjectTable()->getSubjectsByCourseId($course);
		}
		return new ViewModel(array(
			'course' => $course,
			'subjects' => $subjects,
			'hasBoughtCourse' => $hasBoughtCourse,
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

	public function myCoursesAction() {
		$auth = new AuthenticationService();

		$user = $auth->getIdentity();
		$studentCoursesIds = $this->getUserCourseTable()->getAllCoursesByUserId($user->id);

		$myCourses = array();

		foreach ($studentCoursesIds as $courseId) {
			$myCourses[] = $this->getCourseTable()->getCourse($courseId);
		}
		return new ViewModel(array(
			'myCourses' => $myCourses,
		));
	}

	public function buyCourseAction() {
		$auth = new AuthenticationService();
		$user = $auth->getIdentity();

		$courseId = $this->params()->fromRoute('id');
		$this->getUserCourseTable()->buyCourse($user->id, $courseId);

		return $this->redirect()->toRoute('student/course', array('id' => $courseId));
	}

	public function buyCourseWithBillAction() {
		$auth = new AuthenticationService();
		$user = $auth->getIdentity();

		$config = $this->getServiceLocator()->get('Config');
		$course = $this->getCourseTable()->getCourse($this->params()->fromRoute('id'));
		$transport = $this->getServiceLocator()->get('mail.transport');

		$messageToStudent = new Message();
		$messageToAdmin = new Message();

		$messageToStudent->setEncoding('UTF-8')
			->addTo(/*$user->email*/$config['admin_email'])
			->addFrom($config['admin_email'])
			->setSubject('Arve')
			->setBody(
				'Olete ostnud kursuse ' . $course->name . '. Palun tasuda arve summas ' . $course->price
					. '. Palun tehke ülekanne EE21412904821049 kontole, et saada ligipääs kursusele.'
			);

		$messageToAdmin->setEncoding('UTF-8')
			->addTo($config['admin_email'])
			->addFrom($config['admin_email'])
			->setSubject('Õpilane on ostnud kursuse')
			->setBody(
				'Õpilane ' . $user->firstname . ' ' . $user->lastname . ', e-mailiga ' . $user->email
					. ', ostis kursuse ' . $course->name . ', mis maksab ' . $course->price
					. '. Kontrollige, et arve on tasutud ja andke talle kursuse jaoks õigused.'
			);

		$transport->send($messageToStudent);
		$transport->send($messageToAdmin);

		$this->getUserCourseTable()->buyCourse($user->id, $course->id, null);

		return $this->redirect()->toRoute('student/all-courses');
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
	
	public function getSubjectTable() {
		if (!$this->subjectTable) {
			$sm = $this->getServiceLocator();
			$this->subjectTable = $sm->get('EksamiKeskkond\Model\SubjectTable');
		}
		return $this->subjectTable;
	}
}