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
					'teachers' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/teachers',
							'defaults' => array(
								'action' => 'teachers',
							)
						)
					),
					'students' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/students',
							'defaults' => array(
								'action' => 'students',
							)
						)
					),
					'change-user-course-status' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/change-user-course-status[/:user_id][/:course_id][/:status]',
							'constraints' => array(
								'user_id' => '[0-9]+',
								'course_id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'change-user-course-status',
							),
						),
					),
					'send-email-to-user' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/send-email-to-user[/:user_id]',
							'constraints' => array(
								'user_id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'send-email-to-user',
							),
						),
					),
					'send-email-to-all-participants' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/send-email-to-all-participants[/:course_id]',
							'constraints' => array(
								'course_id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'send-email-to-all-participants',
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
					'my-course' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/my-course',
							'defaults' => array(
								'action' => 'my-course',
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
					'lesson' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/lesson[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'lesson',
							),
						),
					),
					'add-lesson' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/add-lesson[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'add-lesson',
							),
						),
					),
					'edit-lesson' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/edit-lesson[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'edit-lesson',
							),
						),
					),
					'delete-lesson' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/delete-lesson[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'delete-lesson',
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
					'add-subsubject' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/add-subsubject[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'add-subsubject',
							),
						),
					),
					'edit-subsubject' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/edit-subsubject[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'edit-subsubject',
							),
						),
					),
					'delete-subsubject' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/delete-subsubject[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'delete-subsubject',
							),
						),
					),
					'edit-description' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/edit-descption[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'edit-description',
							),
						),
					),
					'students' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/students',
							'defaults' => array(
								'action' => 'students',
							),
						),
					),
					'send-email-to-user' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/send-email-to-user[/:teacher_id][/:user_id]',
							'constraints' => array(
								'user_id' => '[0-9]+',
								'teacher_id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'send-email-to-user',
							),
						),
					),
					'send-email-to-all-participants' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/send-email-to-all-participants[/:course_id][/:teacher_id]',
							'constraints' => array(
								'course_id' => '[0-9]+',
								'teacher_id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'send-email-to-all-participants',
							),
						),
					),
					'delete-lesson-file' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/delete-lesson-file[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'delete-lesson-file',
							),
						),
					),
					'homework' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/homework[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'homework',
							),
						),
					),
					'add-homework' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/add-homework[/:subsubject_id]',
							'constraints' => array(
								'subsubject_id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'add-homework',
							),
						),
					),
					'edit-homework' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/edit-homework[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'edit-homework',
							),
						),
					),
					'delete-homework-file' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/delete-homework-file[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'delete-homework-file',
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
					'lesson' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/course/lesson[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'lesson',
							),
						),
					),
					'add-note' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/add-note[/:user_id][/:lesson_id]',
							'constraints' => array(
								'user_id' => '[0-9]+',
								'lesson_id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'add-note',
							),
						),
					),
					'all-notes' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/all-notes[/:user_id]',
							'constraints' => array(
								'user_id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'all-notes',
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
					'my-courses' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/my-courses',
							'defaults' => array(
								'action' => 'my-courses',
							),
						),
					),
					'buy-course' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/buy-course[/:id][/:bank]',
							'constraints' => array(
								'id' => '[0-9]+',
								'bank' => '[a-zA-Z0-9_-]+',
							),
							'defaults' => array(
								'action' => 'buy-course',
							),
						),
					),
					'buy-course-with-bill' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/buy-course-with-bill[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'buy-course-with-bill',
							),
						),
					),
					'mark-lesson-done' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/mark-lesson-done[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'mark-lesson-done',
							),
						),
					),
				),
			),
			'errors' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/errors',
					'defaults' => array(
						'controller' => 'OurErrorController',
						'action' => 'index',
					),
				),
				'may_terminate' => true,
				'child_routes' => array(
					'no-permission' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/no-permission',
							'defaults' => array(
								'action' => 'no-permission',
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
		'factories' => array(
			'emailservice' => 'EksamiKeskkond\Factory\EmailServiceFactory',
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
			'OurErrorController' => 'EksamiKeskkond\Controller\OurErrorController',
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
			'teacher/sidebar' => __DIR__ . '/../view/eksami-keskkond/teacher/sidebar.phtml',
			'admin/sidebar' => __DIR__ . '/../view/eksami-keskkond/admin/sidebar.phtml',
		),
		'template_path_stack' => array(
			__DIR__ . '/../view',
		),
		'strategies' => array (
			'ViewJsonStrategy'
		),
	),
	'doctrine' => array(
		'authentication' => array(
			'orm_default' => array(
				'credential_callable' => 'Application\Service\EmailService::sendEmail',
			),
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