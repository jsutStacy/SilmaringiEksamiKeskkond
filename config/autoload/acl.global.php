<?php

return array(
	'acl' => array(
		'roles' => array(
			'guest' => null,
			'student' => 'guest',
			'teacher' => 'student',
			'admin' => 'teacher',
		),
		'resources' => array(
			'allow' => array(
				'IndexController' => array(
					'all' => 'guest',
					'logoutAction' => 'student',
					'loginAction' => 'guest'
				),
				'StudentController' => array(
					'all' => 'student',
				),
				'TeacherController' => array(
					'all' => 'teacher',
				),
				'AdminController' => array(
					'all' => 'admin',
				),
				'OurErrorController' => array(
					'all' => 'guest',
				),
			),
			'deny' => array(
				'StudentController' => array(
					'all' => 'teacher',
				),
				'IndexController' => array(
					'indexAction' => 'student',
					'logoutAction' => 'guest',
					'loginAction' => 'student',
				),
			),
		),
	),
);