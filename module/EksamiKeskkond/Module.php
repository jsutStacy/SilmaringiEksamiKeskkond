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

use EksamiKeskkond\Model\User;
use EksamiKeskkond\Model\Course;
use EksamiKeskkond\Model\UserTable;
use EksamiKeskkond\Model\CourseTable;

use Zend\ServiceManager\ServiceManager;
use Zend\Mail\Transport\Smtp;
use Zend\Mail\Transport\SmtpOptions;

class Module {

	public function onBootstrap(MvcEvent $e) {
		$this->initAcl($e);

		$eventManager = $e->getApplication()->getEventManager();
		$eventManager->attach('route', array($this, 'checkAcl'));

		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);
	}

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
				'mail.transport' => function (ServiceManager $serviceManager) {
					$config = $serviceManager->get('Config');

					$transport = new Smtp();
					$transport->setOptions(new SmtpOptions($config['mail']['transport']['options']));

					return $transport;
				},
			),
		);
	}

	public function initAcl(MvcEvent $e) {
		$acl = new \Zend\Permissions\Acl\Acl();
		$roles = include __DIR__ . '/config/module.acl.roles.php';
		//$roles = $this->getDbRoles($e); - uncomment when we have roles in database

		$allResources = array();

		foreach ($roles as $role => $resources) {
			$role = new \Zend\Permissions\Acl\Role\GenericRole($role);
			$acl->addRole($role);

			//adding resources
			foreach ($resources as $resource) {
				// Edit 4
				if(!$acl->hasResource($resource))
					$acl->addResource(new \Zend\Permissions\Acl\Resource\GenericResource($resource));
			}

			//adding restrictions
			foreach ($resources as $resource) {
				$acl->allow($role, $resource);
			}
		}

		//setting to view
		$e->getViewModel()->acl = $acl;
	}

	public function checkAcl(MvcEvent $e) {
		$route = $e->getRouteMatch()->getMatchedRouteName();

		//you set your role
		$userRole = 'admin';

		if ($e->getViewModel()->acl->hasResource($route) && !$e->getViewModel()->acl->isAllowed($userRole, $route)) {
			/*
			$response = $e->getResponse();

			//location to page or what ever
			$response->getHeaders()->addHeaderLine('Location', $e->getRequest()->getBaseUrl() . '/404');
			$response->setStatusCode(404);
			*/
		}
	}

	public function getDbRoles(MvcEvent $e) {
		// I take it that your adapter is already configured
		$dbAdapter = $e->getApplication()->getServiceManager()->get('Zend\Db\Adapter\Adapter');
		$results = $dbAdapter->query('SELECT * FROM acl');

		// making the roles array
		$roles = array();

		foreach ($results as $result) {
			$roles[$result['user_role']][] = $result['resource'];
		}
		return $roles;
	}
}