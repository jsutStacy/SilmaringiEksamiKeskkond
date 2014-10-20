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
			),
			'deny' => array(
				'StudentController' => array(
					'all' => 'teacher',
				),
			),
		),
	),
);