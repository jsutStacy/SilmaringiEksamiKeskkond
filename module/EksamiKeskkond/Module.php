<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace EksamiKeskkond;

use Zend\View\Model\ViewModel;
use Zend\View\HelperPluginManager;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

use EksamiKeskkond\Model\User;
use EksamiKeskkond\Model\Course;
use EksamiKeskkond\Model\Subject;
use EksamiKeskkond\Model\Subsubject;
use EksamiKeskkond\Model\Lesson;
use EksamiKeskkond\Model\LessonFiles;
use EksamiKeskkond\Model\UserCourse;
use EksamiKeskkond\Model\UserTable;
use EksamiKeskkond\Model\CourseTable;
use EksamiKeskkond\Model\SubjectTable;
use EksamiKeskkond\Model\SubsubjectTable;
use EksamiKeskkond\Model\LessonTable;
use EksamiKeskkond\Model\LessonFilesTable;
use EksamiKeskkond\Model\UserCourseTable;

use EksamiKeskkond\Acl\Acl;

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

	public function onBootstrap(\Zend\EventManager\EventInterface $e) {
		$application = $e->getApplication();
		$em = $application->getEventManager();

		$em->attach('route', array($this, 'onRoute'), -100);
	}

	public function getServiceConfig() {
		return array(
			'factories' => array(
				'EksamiKeskkond\Model\UserTable' => function($sm) {
					$tableGateway = $sm->get('UserTableGateway');
					$table = new UserTable($tableGateway);

					return $table;
				},
				'EksamiKeskkond\Model\CourseTable' => function($sm) {
					$tableGateway = $sm->get('CourseTableGateway');
					$table = new CourseTable($tableGateway);

					return $table;
				},
				'EksamiKeskkond\Model\SubjectTable' => function($sm) {
					$tableGateway = $sm->get('SubjectTableGateway');
					$table = new SubjectTable($tableGateway);

					return $table;
				},
				'EksamiKeskkond\Model\SubsubjectTable' => function($sm) {
					$tableGateway = $sm->get('SubsubjectTableGateway');
					$table = new SubsubjectTable($tableGateway);

					return $table;
				},
				'EksamiKeskkond\Model\LessonTable' => function($sm) {
					$tableGateway = $sm->get('LessonTableGateway');
					$table = new LessonTable($tableGateway);

					return $table;
				},
				'EksamiKeskkond\Model\LessonFilesTable' => function($sm) {
					$tableGateway = $sm->get('LessonFilesTableGateway');
					$table = new LessonFilesTable($tableGateway);

					return $table;
				},
				'EksamiKeskkond\Model\UserCourseTable' => function($sm) {
					$tableGateway = $sm->get('UserCourseTableGateway');
					$table = new UserCourseTable($tableGateway);

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
				'mail.transport' => function($sm) {
					$config = $sm->get('Config');

					$transport = new Smtp();
					$transport->setOptions(new SmtpOptions($config['mail']['transport']['options']));

					return $transport;
				},
			),
		);
	}
	
	public function getViewHelperConfig() {
		return array(
			'factories' => array(
				'navigation' => function(HelperPluginManager $pm){
					$sm = $pm->getServiceLocator();
					$config = $sm->get('Config');

					$acl = new Acl($config);
					$auth = $sm->get('Zend\Authentication\AuthenticationService');
					$role = Acl::DEFAULT_ROLE;
						
					if ($auth->hasIdentity()) {
						$user = $auth->getIdentity();

						switch ($user->role_id) {
							case 1 :
								$role = Acl::ADMIN_ROLE;
								break;

							case 2 :
								$role = Acl::TEACHER_ROLE;
								break;

							case 3 :
								$role = Acl::STUDENT_ROLE;
								break;

							default :
								$role = Acl::DEFAULT_ROLE;
								break;
						}
					}

					$navigation = $pm->get('Zend\View\Helper\Navigation');
					$navigation->setAcl($acl)->setRole($role);

					return $navigation;
				},
			),
		);
	}

	public function onRoute(\Zend\EventManager\EventInterface $e) {
		$application = $e->getApplication();
		$routeMatch = $e->getRouteMatch();
		$sm = $application->getServiceManager();

		$auth = $sm->get('Zend\Authentication\AuthenticationService');
		$config = $sm->get('Config');
		$acl = new Acl($config);

		$role = Acl::DEFAULT_ROLE;

		if ($auth->hasIdentity()) {
			$user = $auth->getIdentity();

			switch ($user->role_id) {
				case 1 :
					$role = Acl::ADMIN_ROLE;
					break;

				case 2 :
					$role = Acl::TEACHER_ROLE;
					break;

				case 3 :
					$role = Acl::STUDENT_ROLE;
					break;

				default :
					$role = Acl::DEFAULT_ROLE;
					break;
			}
		}
		$controller = $routeMatch->getParam('controller');
		$action = $routeMatch->getParam('action');

		if (!$acl->hasResource($controller)) {
			throw new \Exception('Resource ' . $controller . ' not defined');
		}
		if (!$acl->isAllowed($role, $controller, $action)) {
			$url = $e->getRouter()->assemble(array(), array('name' => 'errors/no-permission'));

			$response = $e->getResponse();
			$response->getHeaders()->addHeaderLine('Location', $url);
			$response->setStatusCode(403);
			$response->sendHeaders();

			exit;
		}
	}
}