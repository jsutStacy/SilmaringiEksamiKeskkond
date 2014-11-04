<?php

 return array(
	'navigation' => array(
		'default' => array(
			array(
				'label' => 'Kodu',
				'route' => 'home',
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
			)
		)
	),
	'service_manager' => array(
		'factories' => array(
			'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
		),
	)
 );