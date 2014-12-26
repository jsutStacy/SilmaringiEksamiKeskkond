<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Eksamikool;

use Zend\View\Model\ViewModel;
use Zend\View\HelperPluginManager;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Zend\Session\Container;
use Zend\Validator\AbstractValidator;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

use Eksamikool\Model\User;
use Eksamikool\Model\Course;
use Eksamikool\Model\Subject;
use Eksamikool\Model\Subsubject;
use Eksamikool\Model\Lesson;
use Eksamikool\Model\LessonFiles;
use Eksamikool\Model\UserCourse;
use Eksamikool\Model\UserLesson;
use Eksamikool\Model\Homework;
use Eksamikool\Model\HomeworkAnswers;
use Eksamikool\Model\Note;
use Eksamikool\Model\UserTable;
use Eksamikool\Model\CourseTable;
use Eksamikool\Model\SubjectTable;
use Eksamikool\Model\SubsubjectTable;
use Eksamikool\Model\LessonTable;
use Eksamikool\Model\LessonFilesTable;
use Eksamikool\Model\UserCourseTable;
use Eksamikool\Model\UserLessonTable;
use Eksamikool\Model\HomeworkTable;
use Eksamikool\Model\HomeworkAnswersTable;
use Eksamikool\Model\NoteTable;

use Zend\ServiceManager\ServiceManager;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;

class Module {

	public function getConfig() {
		return include __DIR__ . '/config/module.config.php';
	}

	public function getAutoloaderConfig() {
		return array(
			'Zend\Loader\StandardAutoloader' => array(
				'namespaces' => array(
					__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
				),
			),
		);
	}

	public function getServiceConfig() {
		return array(
			'factories' => array(
				'Eksamikool\Model\UserTable' => function($sm) {
					$tableGateway = $sm->get('UserTableGateway');
					$table = new UserTable($tableGateway);

					return $table;
				},
				'Eksamikool\Model\CourseTable' => function($sm) {
					$tableGateway = $sm->get('CourseTableGateway');
					$table = new CourseTable($tableGateway);

					return $table;
				},
				'Eksamikool\Model\SubjectTable' => function($sm) {
					$tableGateway = $sm->get('SubjectTableGateway');
					$table = new SubjectTable($tableGateway);

					return $table;
				},
				'Eksamikool\Model\SubsubjectTable' => function($sm) {
					$tableGateway = $sm->get('SubsubjectTableGateway');
					$table = new SubsubjectTable($tableGateway);

					return $table;
				},
				'Eksamikool\Model\LessonTable' => function($sm) {
					$tableGateway = $sm->get('LessonTableGateway');
					$table = new LessonTable($tableGateway);

					return $table;
				},
				'Eksamikool\Model\LessonFilesTable' => function($sm) {
					$tableGateway = $sm->get('LessonFilesTableGateway');
					$table = new LessonFilesTable($tableGateway);

					return $table;
				},
				'Eksamikool\Model\UserCourseTable' => function($sm) {
					$tableGateway = $sm->get('UserCourseTableGateway');
					$table = new UserCourseTable($tableGateway);

					return $table;
				},
				'Eksamikool\Model\UserLessonTable' => function($sm) {
					$tableGateway = $sm->get('UserLessonTableGateway');
					$table = new UserLessonTable($tableGateway);

					return $table;
				},
				'Eksamikool\Model\HomeworkTable' => function($sm) {
					$tableGateway = $sm->get('HomeworkTableGateway');
					$table = new HomeworkTable($tableGateway);

					return $table;
				},
				'Eksamikool\Model\NoteTable' => function($sm) {
					$tableGateway = $sm->get('NoteTableGateway');
					$table = new NoteTable($tableGateway);

					return $table;
				},
				'Eksamikool\Model\HomeworkAnswersTable' => function($sm) {
					$tableGateway = $sm->get('HomeworkAnswersTableGateway');
					$table = new HomeworkAnswersTable($tableGateway);

					return $table;
				},
				'UserTableGateway' => function($sm) {
					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');

					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new User());

					return new TableGateway('user', $dbAdapter, null, $resultSetPrototype);
				},
				'CourseTableGateway' => function($sm) {
					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');

					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new Course());

					return new TableGateway('course', $dbAdapter, null, $resultSetPrototype);
				},
				'SubjectTableGateway' => function($sm) {
					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');

					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new Subject());

					return new TableGateway('subject', $dbAdapter, null, $resultSetPrototype);
				},
				'SubsubjectTableGateway' => function($sm) {
					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');

					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new Subsubject());

					return new TableGateway('subsubject', $dbAdapter, null, $resultSetPrototype);
				},
				'LessonTableGateway' => function($sm) {
					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');

					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new Lesson());

					return new TableGateway('lesson', $dbAdapter, null, $resultSetPrototype);
				},
				'LessonFilesTableGateway' => function($sm) {
					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
				
					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new LessonFiles());
				
					return new TableGateway('lesson_files', $dbAdapter, null, $resultSetPrototype);
				},
				'UserCourseTableGateway' => function($sm) {
					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');

					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new UserCourse());

					return new TableGateway('user_course', $dbAdapter, null, $resultSetPrototype);
				},
				'UserLessonTableGateway' => function($sm) {
					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');

					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new UserLesson());

					return new TableGateway('user_lesson', $dbAdapter, null, $resultSetPrototype);
				},
				'HomeworkTableGateway' => function($sm) {
					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');

					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new Homework());

					return new TableGateway('homework', $dbAdapter, null, $resultSetPrototype);
				},
				'NoteTableGateway' => function($sm) {
					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');

					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new Note());

					return new TableGateway('note', $dbAdapter, null, $resultSetPrototype);
				},
				'HomeworkAnswersTableGateway' => function($sm) {
					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');

					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new HomeworkAnswers());

					return new TableGateway('homework_answers', $dbAdapter, null, $resultSetPrototype);
				},
				'mail.transport' => function($sm) {
					$config = $sm->get('Config');

					$transport = new Smtp();
					$transport->setOptions(new SmtpOptions($config['mail']['transport']['options']));

					return $transport;
				},
			),
		);
	}

	public function onBootstrap(MvcEvent $e) {
		$application = $e->getApplication();
		$eventManager        = $application->getEventManager();
		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);
		$sm = $application->getServiceManager();
		$sharedManager = $application->getEventManager()->getSharedManager();

		$translator = $sm->get('translator');
		AbstractValidator::setDefaultTranslator($translator);

		$sharedManager->attach('Zend\Mvc\Application', 'dispatch.error',
			function ($e) use ($sm) {
				if ($e->getParam('exception')) {
					$sm->get('Zend\Log\Logger')->crit($e->getParam('exception'));
				}
			}
		);
		$this->bootstrapSession($e);
	}

	public function checkLogin(MvcEvent $e) {
		$sm = $e->getApplication()->getServiceManager();
		$auth = $sm->get('doctrine.authenticationservice.orm_default');

		if ($auth->hasIdentity()) return;
		if ($e->getRouteMatch()->getMatchedRouteName() == 'home') return;

		$url = $e->getRouter()->assemble(array('controller' => 'Application\Controller\Index'), array('name' => 'home'));
		$response = $e->getResponse();
		$response->setHeaders($response->getHeaders()->addHeaderLine('Location', $url));
		$response->setStatusCode(302);
		$response->sendHeaders();

		return;
	}

	public function bootstrapSession($e) {
		$session = $e->getApplication()
			->getServiceManager()
			->get('Zend\Session\SessionManager');
		$session->start();

		$container = new Container('initialized');
		if (!isset($container->init)) {
			$session->regenerateId(true);
			$container->init = 1;
		}
	}
}