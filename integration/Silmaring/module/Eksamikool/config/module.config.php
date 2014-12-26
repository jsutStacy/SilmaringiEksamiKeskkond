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
			'eksamikool' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/eksamikool',
					'defaults' => array(
						'controller' => 'Eksamikool\Controller\Index',
						'action' => 'index',
					),
				),
			),
			'admin' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/eksamikool/admin',
					'defaults' => array(
						'controller' => 'Eksamikool\Controller\Admin',
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
					'route' => '/eksamikool/teacher',
					'defaults' => array(
						'controller' => 'Eksamikool\Controller\Teacher',
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
					'homeworks' => array(
						'type' => 'Literal',
						'options' => array(
							'route' => '/homeworks',
							'defaults' => array(
								'action' => 'homeworks',
							),
						),
					),
					'add-feedback' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/add-feedback[/:homework_id][/:user_id]',
							'constraints' => array(
								'homework_id' => '[0-9]+',
								'user_id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'add-feedback',
							),
						),
					),
					'edit-feedback' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/edit-feedback[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'edit-feedback',
							),
						),
					),
					'delete-feedback' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/delete-feedback[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'delete-feedback',
							),
						),
					),
				),
			),
			'student' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/eksamikool/student',
					'defaults' => array(
						'controller' => 'Eksamikool\Controller\Student',
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
							'route' => '/add-note[/:lesson_id]',
							'constraints' => array(
								'lesson_id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'add-note',
							),
						),
					),
					'edit-note' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/edit-note[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'edit-note',
							),
						),
					),
					'delete-note' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/delete-note[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'delete-note',
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
					'add-homework-answer' => array(
						'type' => 'Segment',
						'options' => array(
							'route' => '/add-homework-answer[/:id]',
							'constraints' => array(
								'id' => '[0-9]+',
							),
							'defaults' => array(
								'action' => 'add-homework-answer',
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
						'controller' => 'Eksamikool\Controller\OurError',
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
			'Zend\Form\FormAbstractServiceFactory',
		),
		'aliases' => array(
			'translator' => 'MvcTranslator',
		),
		'invokables' => array(
		),
		'factories' => array(
			'emailservice' => 'Eksamikool\Factory\EmailServiceFactory',
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
			'Eksamikool\Controller\Index' => 'Eksamikool\Controller\IndexController',
			'Eksamikool\Controller\Admin' => 'Eksamikool\Controller\AdminController',
			'Eksamikool\Controller\Teacher' => 'Eksamikool\Controller\TeacherController',
			'Eksamikool\Controller\Student' => 'Eksamikool\Controller\StudentController',
			'Eksamikool\Controller\OurError' => 'Eksamikool\Controller\OurErrorController',
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
				'object_manager' => 'Doctrine\ORM\EntityManager',
				'identity_class' => 'AmaUsers\Entity\User',
				'identity_property' => 'email',
				'credential_property' => 'password',
				'credential_callable' => 'Application\Service\UserService::verifyUser',
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