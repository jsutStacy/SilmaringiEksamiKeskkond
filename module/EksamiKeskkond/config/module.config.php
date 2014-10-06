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
					'teachers' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/teachers',
							'defaults' => array(
								'action' => 'teachers',
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