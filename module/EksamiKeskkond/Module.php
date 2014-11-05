<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace EksamiKeskkond;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\View\HelperPluginManager;

use EksamiKeskkond\Model\User;
use EksamiKeskkond\Model\Course;
use EksamiKeskkond\Model\Subject;
use EksamiKeskkond\Model\UserCourse;
use EksamiKeskkond\Model\UserTable;
use EksamiKeskkond\Model\CourseTable;
use EksamiKeskkond\Model\SubjectTable;
use EksamiKeskkond\Model\UserCourseTable;
use EksamiKeskkond\Model\CourseSubject;
use EksamiKeskkond\Model\CourseSubjectTable;

use EksamiKeskkond\Acl\Acl;

use Zend\ServiceManager\ServiceManager;

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
				'EksamiKeskkond\Model\UserCourseTable' => function($sm) {
					$tableGateway = $sm->get('UserCourseTableGateway');
					$table = new UserCourseTable($tableGateway);
				
					return $table;
				},
				'EksamiKeskkond\Model\CourseSubjectTable' => function($sm) {
					$tableGateway = $sm->get('CourseSubjectTableGateway');
					$table = new CourseSubjectTable($tableGateway);
				
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
				'UserCourseTableGateway' => function($sm) {
					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
				
					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new UserCourse());
				
					return new TableGateway('user_course', $dbAdapter, null, $resultSetPrototype);
				},
				'CourseSubjectTableGateway' => function($sm) {
					$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
				
					$resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new CourseSubject());
				
					return new TableGateway('course_subject', $dbAdapter, null, $resultSetPrototype);
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
					$navigation->setAcl($acl)
					->setRole($role);
						
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
			$url = $e->getRouter()->assemble(array(), array('name' => 'home'));

			$response = $e->getResponse();
			$response->getHeaders()->addHeaderLine('Location', $url);

			// The HTTP response status code 302 Found is a common way of performing a redirection.
			// http://en.wikipedia.org/wiki/HTTP_302
			$response->setStatusCode(302);
			$response->sendHeaders();

			exit;
		}
	}
}