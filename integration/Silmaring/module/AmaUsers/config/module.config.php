<?php
namespace AmaUsers;

return array(
    'controllers' => array(
        'invokables' => array(
            'AmaUsers\Controller\Index' => __NAMESPACE__ . '\Controller\IndexController',
            'AmaUsers\Controller\Register' =>  __NAMESPACE__ . '\Controller\RegisterController',
            'AmaUsers\Controller\Dashboard' => __NAMESPACE__ . '\Controller\DashboardController',
            'AmaUsers\Controller\Settings' => __NAMESPACE__ . '\Controller\SettingsController',
            'AmaUsers\Controller\Admin' => __NAMESPACE__ . '\Controller\AdminController',
            'AmaUsers\Controller\School' => __NAMESPACE__ . '\Controller\SchoolController',
            'AmaUsers\Controller\Statistics' => __NAMESPACE__ . '\Controller\StatisticsController'
        ),
    ),
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' =>  __NAMESPACE__ . '\Controller\Index',
                        'action' => 'index',
                    ),
                ),
            ),
            'register' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/signup',
                    'defaults' => array(
                        'controller' =>  __NAMESPACE__ . '\Controller\Register',
                        'action' => 'index',
                    ),
                ),
            ),
            'forgottenPassword' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/forgotten-password',
                    'defaults' => array(
                        'controller' =>  __NAMESPACE__ . '\Controller\Register',
                        'action' => 'forgottenPassword',
                    ),
                ),
            ),
            'registerAjax' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/register-ajax',
                    'defaults' => array(
                        'controller' =>  __NAMESPACE__ . '\Controller\Register',
                        'action' => 'registerAjax',
                    ),
                ),
            ),
            'login' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/login',
                    'defaults' => array(
                        'controller' => __NAMESPACE__ . '\Controller\Index',
                        'action' => 'login',
                    ),
                ),
            ),
            'logout' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/logout',
                    'defaults' => array(
                        'controller' => __NAMESPACE__ . '\Controller\Index',
                        'action' => 'logout',
                    ),
                ),
            ),
            'dashboard' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/dashboard[/:action][/:id]',
                    'defaults' => array(
                        'controller' => __NAMESPACE__ . '\Controller\Dashboard',
                        'action' => 'index',
                    ),
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'id'     => '[0-9_-]+',
                    ),
                ),
            ),
            'checkAlerts' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/check-alerts',
                    'defaults' => array(
                        'controller' => __NAMESPACE__ . '\Controller\Dashboard',
                        'action' => 'checkAlerts',
                    ),
                ),
            ),
            'markAlertsRead' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/mark-alerts-read',
                    'defaults' => array(
                        'controller' => __NAMESPACE__ . '\Controller\Dashboard',
                        'action' => 'markAlertsRead',
                    ),
                ),
            ),
            'settings' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/settings',
                    'defaults' => array(
                        'controller' => __NAMESPACE__ . '\Controller\Settings',
                        'action' => 'index',
                    ),
                ),
            ),
            'forgottenPasswordAjax' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/forgotten-password-ajax',
                    'defaults' => array(
                        'controller' =>  __NAMESPACE__ . '\Controller\Register',
                        'action' => 'forgottenPasswordAjax',
                    ),
                ),
            ),
            'deleteProfileImage' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/delete-profile-image',
                    'defaults' => array(
                        'controller' => __NAMESPACE__ . '\Controller\Settings',
                        'action' => 'deleteProfileImage',
                    ),
                ),
            ),
            'inviteCustomers' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/invite-customers',
                    'defaults' => array(
                        'controller' => __NAMESPACE__ . '\Controller\Dashboard',
                        'action' => 'invite',
                    ),
                ),
            ),
            'facebookLogin' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/facebook-login',
                    'defaults' => array(
                        'controller' => __NAMESPACE__ . '\Controller\Index',
                        'action' => 'facebookLogin',
                    ),
                ),
            ),
            'googleLogin' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/google-login',
                    'defaults' => array(
                        'controller' => __NAMESPACE__ . '\Controller\Index',
                        'action' => 'googleLogin',
                    ),
                ),
            ),
            'fbTest' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/fb-test',
                    'defaults' => array(
                        'controller' => __NAMESPACE__ . '\Controller\Index',
                        'action' => 'testFbImage',
                    ),
                ),
            ),
            'users' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/users[/:action][/:id][/:rid]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'id'     => '[0-9_-]+',
                        'rid'     => '[0-9_-]+',
                    ),
                    'defaults' => array(
                        'controller' =>  __NAMESPACE__ . '\Controller\Admin',
                        'action' => 'index',
                    ),
                ),
            ),
            'mySchools' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/my-schools[/:action][/:id][/:tid]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'id'     => '[0-9_-]+',
                        'tid'     => '[0-9_-]+',
                    ),
                    'defaults' => array(
                        'controller' =>  __NAMESPACE__ . '\Controller\School',
                        'action' => 'index',
                    ),
                ),
            ),
            'search' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/search',
                    'defaults' => array(
                        'controller' => __NAMESPACE__ . '\Controller\Dashboard',
                        'action' => 'search',
                    ),
                ),
            ),
            'statistics' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/statistics[/:action][/:id]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'id'     => '[0-9_-]+',
                    ),
                    'defaults' => array(
                        'controller' =>  __NAMESPACE__ . '\Controller\Statistics',
                        'action' => 'index',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'ama-users/index/index' => __DIR__ . '/../view/ama-users/index/index.phtml',
            'ama-users/register/index' => __DIR__ . '/../view/ama-users/register/index.phtml',
            'ama-users/register/forgotten-password' => __DIR__ . '/../view/ama-users/register/forgotten-password.phtml',
            'ama-users/register/register-ajax' => __DIR__ . '/../view/ama-users/register/register-ajax.phtml',
            'ama-users/dashboard/index' => __DIR__ . '/../view/ama-users/dashboard/index.phtml',
            'ama-users/dashboard/student-index' => __DIR__ . '/../view/ama-users/dashboard/student-index.phtml',
            'ama-users/settings/index' => __DIR__ . '/../view/ama-users/settings/index.phtml',
            'ama-users/partial/user-bar' => __DIR__ . '/../view/ama-users/partial/user-bar.phtml',
            'ama-users/index/facebook-login' => __DIR__ . '/../view/ama-users/index/facebook-login.phtml',
            'ama-users/index/google-login' => __DIR__ . '/../view/ama-users/index/google-login.phtml',
            'ama-users/admin/index' => __DIR__ . '/../view/ama-users/admin/index.phtml',
            'ama-users/admin/edit' => __DIR__ . '/../view/ama-users/admin/edit.phtml',
            'ama-users/school/index' => __DIR__ . '/../view/ama-users/school/index.phtml',
            'ama-users/school/manage' => __DIR__ . '/../view/ama-users/school/manage.phtml',
            'ama-users/school/manage-teachers' => __DIR__ . '/../view/ama-users/school/manage-teachers.phtml',
            'ama-users/school/manage-students' => __DIR__ . '/../view/ama-users/school/manage-students.phtml',
            'ama-users/school/add-teacher' => __DIR__ . '/../view/ama-users/school/add-teacher.phtml',
            'ama-users/school/edit-teacher' => __DIR__ . '/../view/ama-users/school/edit-teacher.phtml',
            'ama-users/dashboard/search' => __DIR__ . '/../view/ama-users/dashboard/search.phtml',
            'ama-users/dashboard/search-student' => __DIR__ . '/../view/ama-users/dashboard/search-student.phtml',
            'ama-users/statistics/index' => __DIR__ . '/../view/ama-users/statistics/index.phtml',
            'ama-users/statistics/index-teacher' => __DIR__ . '/../view/ama-users/statistics/index-teacher.phtml',
            'ama-users/statistics/index-teacher-class' => __DIR__ . '/../view/ama-users/statistics/index-teacher-class.phtml',
            'ama-users/statistics/type-daily' => __DIR__ . '/../view/ama-users/statistics/type-daily.phtml',
            'ama-users/statistics/type-monthly' => __DIR__ . '/../view/ama-users/statistics/type-monthly.phtml',
            'ama-users/statistics/type-weekly' => __DIR__ . '/../view/ama-users/statistics/type-weekly.phtml',

    ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    'doctrine' => array(
        'driver' => array(
            // defines an annotation driver with two paths, and names it `my_annotation_driver`
            __NAMESPACE__ . '_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(
                    __DIR__ . '/../src/' . __NAMESPACE__ . '/Entity'
                ),
            ),
            // default metadata driver, aggregates all other drivers into a single one.
            // Override `orm_default` only if you know what you're doing
            'orm_default' => array(
                'drivers' => array(
                    // register `my_annotation_driver` for any entity under namespace `My\Namespace`
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                )
            )
        )
    )
);