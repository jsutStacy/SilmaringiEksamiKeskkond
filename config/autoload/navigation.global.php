<?php

 return array(
	'navigation' => array(
		'default' => array(
			array(
				'label' => 'Kodu',
				'route' => 'home',
				'resource' => 'IndexController',
				'privilege' => 'indexAction'
			),
			array(
				'label' => 'Õpetajad',
				'route' => 'admin/teachers',
				'resource' => 'AdminController',
				'privilege' => 'all'
			),
			array(
				'label' => 'Kursused',
				'route' => 'admin/courses',
				'resource' => 'AdminController',
				'privilege' => 'all'
			),
			array(
				'label' => 'Kõik kursused',
				'route' => 'student/all-courses',
				'resource' => 'StudentController',
				'privilege' => 'all',
			),
			array(
				'label' => 'Minu kursused',
				'route' => 'student/my-courses',
				'resource' => 'StudentController',
				'privilege' => 'all',
			),
			array(
				'label' => 'Logi välja',
				'route' => 'home/logout',
				'action' => 'logoutAction',
				'controller' => 'IndexController',
				'resource' => 'IndexController',
				'privilege' => 'logoutAction',
			),
			array(
				'label' => 'Logi Sisse',
				'route' => 'home/login',
				'action' => 'loginAction',
				'controller' => 'IndexController',
				'resource' => 'IndexController',
				'privilege' => 'loginAction',
			),
		)
	),
	'service_manager' => array(
		'factories' => array(
			'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
		),
	)
 );