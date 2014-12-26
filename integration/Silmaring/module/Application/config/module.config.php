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
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            // The following is a route to simplify getting started creating
            // new controllers and actions without needing to create a new
            // module. Simply drop new controllers in, and you can access them
            // using the path /application/:controller/:action
            'application' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/application',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
            'error' => array(
                'type'    => 'Segment',
                'options' => array(
                    'route'    => '/error',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Index',
                        'action'        => 'error',
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
        'factories' => array(
            'Zend\Log\Logger' => 'Application\Factory\LoggerFactory',
            'Zend\Session\SessionManager' => 'Application\Factory\SessionManagerFactory',
            'userservice' => 'Application\Factory\UserServiceFactory',
            'amaIdentityProvider' => 'Application\Factory\AmaIdentityProviderFactory',
            'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
            'admin_navigation' => 'Application\Navigation\AdminNavigationFactory',
            'tab_navigation' => 'Application\Navigation\TabNavigationFactory',
            'school_navigation' => 'Application\Navigation\SchoolNavigationFactory',
            'single_school_navigation' => 'Application\Navigation\SingleSchoolNavigationFactory',
            'category_navigation' => 'Application\Navigation\CategoryNavigationFactory',
            'normal_navigation' => 'Application\Navigation\NormalNavigationFactory',
            'wp_navigation' => 'Application\Navigation\WpNavigationFactory',
            'encryptDecrypt' => 'Application\Navigation\EncryptDecryptFactory',
            'zcache' => 'Application\Service\ZCacheService',
            'wp_cache' => 'Application\Service\WPCacheService',
        ),
    ),
    'validators' => array(
        'invokables' => array(
            'personalCodeValidator' => 'Application\Validator\PersonalCodeValidator',
            'personalCodeExists' => 'Application\Validator\PersonalCodeExists',
        ),
    ),
    'translator' => array(
        'locale' => 'et_EE',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index' => 'Application\Controller\IndexController'
        ),
    ),
    'controller_plugins' => array(
        'invokables' => array(
            'formatMessage' => 'Application\Plugin\FormatMessage',
            'formatCategories' => 'Application\Plugin\FormatCategories',
            'cleanInputs' => 'Application\Plugin\CleanInputs',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'layout/frontend'         => __DIR__ . '/../view/layout/frontend.phtml',
            'layout/login'            => __DIR__ . '/../view/layout/login.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
            'layout/header'           => __DIR__ . '/../view/layout/header.phtml',
            'layout/footer'           => __DIR__ . '/../view/layout/footer.phtml',
            'layout/error'            => __DIR__ . '/../view/layout/error.phtml',
            'error/403'               => __DIR__ . '/../view/error/403.phtml',
            'partial/admin-menu-li'   => __DIR__ . '/../view/partial/admin-menu-li.phtml',
            'partial/admin-menu-li-sub' => __DIR__ . '/../view/partial/admin-menu-li-sub.phtml',
            'partial/school-menu-li'     => __DIR__ . '/../view/partial/school-menu-li.phtml',
            'partial/school-menu-li-sub'   => __DIR__ . '/../view/partial/school-menu-li-sub.phtml',
            'partial/user-bar'        => __DIR__ . '/../view/partial/user-bar.phtml',
            'partial/sidemenu'        => __DIR__ . '/../view/partial/sidemenu.phtml',
            'partial/category-menu-li'   => __DIR__ . '/../view/partial/category-menu-li.phtml',
            'partial/category-menu-li-sub' => __DIR__ . '/../view/partial/category-menu-li-sub.phtml',
            'partial/tab-menu-li'   => __DIR__ . '/../view/partial/tab-menu-li.phtml',
            'partial/paginator' => __DIR__ . '/../view/partial/paginator.phtml',
            'partial/header-alerts' => __DIR__ .'/../view/partial/header-alerts.phtml',
            'partial/search-form' => __DIR__ .'/../view/partial/search-form.phtml',
            'partial/wp-menu-li'   => __DIR__ . '/../view/partial/wp-menu-li.phtml',
            'partial/wp-menu-li-sub'   => __DIR__ . '/../view/partial/wp-menu-li-sub.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'getCategoryPath' => 'Application\Helper\GetCategoryPath',
            'getLastAlerts' => 'Application\Helper\GetLastAlerts',
            'getIsNew' => 'Application\Helper\GetIsNew',
            'getDbValue' => 'Application\Helper\GetExistingDbValue',
            'getInpDisabled' => 'Application\Helper\GetInputDisabled',
            'customFormElement' => 'Application\Helper\CustomFormElement',
            'customFormInput' => 'Application\Helper\CustomFormInput',
            'wpNavigation' => 'Application\Helper\WpNavigation',
            'getParam' => 'Application\Helper\Params'
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
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
);
