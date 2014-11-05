<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
return array(
	'router' => array(
		'routes' => array(
			'home' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/',
					'defaults' => array(
						'controller' => 'IndexController',
						'action' => 'index',
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'login' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => 'login',
							'defaults' => array(
								'action' => 'login',
							),
						),
					),
					'logout' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => 'logout',
							'defaults' => array(
								'action' => 'logout',
							),
						),
					),
				),
			),
			'admin' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/admin',
					'defaults' => array(
						'controller' => 'AdminController',
						'action' => 'index',
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'course' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/course[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'course',
							),
						),
					),
					'courses' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/courses',
							'defaults' => array(
								'action' => 'courses',
							),
						),
					),
					'add-course' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/add-course',
							'defaults' => array(
								'action' => 'add-course',
							),
						),
					),
					'edit-course' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/edit-course[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'edit-course',
							),
						),
					),
					'delete-course' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/delete-course[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'delete-course',
							),
						),
					),
					'change-course-visibility' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/change-course-visibility[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'change-course-visibility',
							),
						),
					),
					'empty-course' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/empty-course[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'empty-course',
							),
						),
					),
					'course-participants' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/course-participants[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'course-participants',
							),
						),
					),
				),
			),
			'teacher' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/teacher',
					'defaults' => array(
						'controller' => 'TeacherController',
						'action' => 'index',
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'course' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/course[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'course',
							),
						),
					),
					'subject' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/subject[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'subject',
							),
						),
					),
					'add-subject' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/add-subject[/:id]',
							'constraints' => array(
								'id' => '[0-9]+', 
							),
							'defaults' => array(
								'action' => 'add-subject',
							),
						),
					),
					'edit-subject' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/edit-subject[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'edit-subject',
							),
						),
					),
					'delete-subject' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/delete-subject[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'delete-subject',
							),
						),
					),
				),
			),
			'student' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/student',
					'defaults' => array(
						'controller' => 'StudentController',
						'action' => 'index',
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'course' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/course[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'course',
							),
						),
					),
					'all-courses' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/all-courses',
							'defaults' => array(
								'action' => 'all-courses',
							),
						),
					),
					'buy-course' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/buy-course[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'buy-course',
							),
						),
					),
				),
			),
		),
	),
	'service_manager' => array(
		'abstract_factories' => array(
			'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
			'Zend\Log\LoggerAbstractServiceFactory',
		),
		'aliases' => array(
			'translator' => 'MvcTranslator',
			'Zend\Authentication\AuthenticationService' => 'my_auth_service',
		),
		'invokables' => array(
			'my_auth_service' => 'Zend\Authentication\AuthenticationService',
		),
	),
	'translator' => array(
		'locale' => 'en_US',
		'translation_file_patterns' => array(
			array(
				'type' => 'gettext',
				'base_dir' => __DIR__ . '/../language',
				'pattern' => '%s.mo',
			),
		),
	),
	'controllers' => array(
		'invokables' => array(
			'IndexController' => 'EksamiKeskkond\Controller\IndexController',
			'AdminController' => 'EksamiKeskkond\Controller\AdminController',
			'TeacherController' => 'EksamiKeskkond\Controller\TeacherController',
			'StudentController' => 'EksamiKeskkond\Controller\StudentController',
		),
	),
	'view_manager' => array(
		'display_not_found_reason' => true,
		'display_exceptions' => true,
		'doctype' => 'HTML5',
		'not_found_template' => 'error/404',
		'exception_template' => 'error/index',
		'template_map' => array(
			'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
			'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
			'error/404' => __DIR__ . '/../view/error/404.phtml',
			'error/index' => __DIR__ . '/../view/error/index.phtml',
		),
		'template_path_stack' => array(
			__DIR__ . '/../view',
		),
	),
	// Placeholder for console routes
	'console' => array(
		'router' => array(
			'routes' => array(
			),
		),
	),
);