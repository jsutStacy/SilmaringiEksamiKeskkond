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

use EksamiKeskkond\Model\Lesson;
use EksamiKeskkond\Form\LessonForm;
use EksamiKeskkond\Filter\LessonFilter;

use EksamiKeskkond\Model\LessonFiles;

class TeacherController extends AbstractActionController {

	protected $courseTable;

	protected $subjectTable;

	protected $subsubjectTable;

	protected $lessonTable;

	protected $userCourseTable;

	protected $userTable;
	
	protected $lessonFilesTable;

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
				'lessonTable' => $this->getLessonTable(),
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
	
	public function editSubsubjectAction() {
		$id = $this->params()->fromRoute('id');
		$subsubject = $this->getSubsubjectTable()->getSubsubject($id);
		$subject = $this->getSubjectTable()->getSubject($subsubject->subject_id);
	
		$form  = new SubsubjectForm();
		$form->bind($subsubject);
		$form->get('subject_id')->setValue($subject->id);
		$form->get('submit')->setAttribute('value', 'Muuda');
	
		$request = $this->getRequest();
	
		if ($request->isPost()) {
			$form->setInputFilter(new SubsubjectFilter($this->getServiceLocator()));
			$form->setData($request->getPost());
	
			if ($form->isValid()) {
				$this->getSubsubjectTable()->saveSubsubject($form->getData());
	
				return $this->redirect()->toRoute('teacher/my-course');
			}
		}
		return array(
				'id' => $id,
				'form' => $form,
		);
	}

	public function deleteSubsubjectAction() {
		$this->getSubsubjectTable()->deleteSubsubject($this->params()->fromRoute('id'));

		return $this->redirect()->toRoute('teacher/my-course');
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
			$lessonFiles = new LessonFiles();
			$form->setInputFilter(new LessonFilter($this->getServiceLocator()));
			$form->setData($request->getPost());

			if ($form->isValid()) {
				$data = $form->getData();
				$lesson->exchangeArray($data);
				$lessonId = $this->getLessonTable()->saveLesson($lesson);

				$data['id'] = $lessonId;
				$lessonFiles->exchangeArray($data);

				$this->getLessonFilesTable()->saveLessonFiles($lessonFiles);
				return $this->redirect()->toRoute('teacher/my-course');
			}
		}
		return array(
				'form' => $form,
				'subsubjectId' => $subsubjectId,
		);
	}
	
	public function editLessonAction() {
		$id = $this->params()->fromRoute('id');
		$lesson = $this->getLessonTable()->getLesson($id);
		$subsubject = $this->getSubsubjectTable()->getSubsubject($lesson->subsubject_id);
	
		$form = new LessonForm();
		$form->bind($lesson);
		$form->get('subsubject_id')->setValue($subsubject->id);
		$form->get('submit')->setAttribute('value', 'Muuda');
	
		$request = $this->getRequest();
	
		if ($request->isPost()) {
			$form->setInputFilter(new LessonFilter($this->getServiceLocator()));
			$form->setData($request->getPost());
	
			if ($form->isValid()) {
				$this->getLessonTable()->saveLesson($form->getData());
	
				return $this->redirect()->toRoute('teacher/my-course');
			}
		}
		return array(
				'id' => $id,
				'form' => $form,
		);
	}

	public function deleteLessonAction() {
		$this->getLessonTable()->deleteLesson($this->params()->fromRoute('id'));

		return $this->redirect()->toRoute('teacher/my-course');
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
}