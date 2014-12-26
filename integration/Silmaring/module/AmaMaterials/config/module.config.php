<?php
namespace AmaMaterials;

return array(
    'controllers' => array(
        'invokables' => array(
            __NAMESPACE__ . '\Controller\Index' => __NAMESPACE__ . '\Controller\IndexController',
            __NAMESPACE__ . '\Controller\Test' => __NAMESPACE__ . '\Controller\TestController',
            __NAMESPACE__ . '\Controller\Image' => __NAMESPACE__ . '\Controller\ImageController',
            __NAMESPACE__ . '\Controller\File' => __NAMESPACE__ . '\Controller\FileController',
            __NAMESPACE__ . '\Controller\Presentation' => __NAMESPACE__ . '\Controller\PresentationController',
            __NAMESPACE__ . '\Controller\Video' => __NAMESPACE__ . '\Controller\VideoController',
            __NAMESPACE__ . '\Controller\Worksheet' => __NAMESPACE__ . '\Controller\WorksheetController',
            __NAMESPACE__ . '\Controller\LessonPlan' => __NAMESPACE__ . '\Controller\LessonPlanController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'materials' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/materials[/:action][/:id][/:page]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'id'     => '[0-9_-]+',
                        'page'     => '[0-9_-]+',
                    ),
                    'defaults' => array(
                        'controller' =>  __NAMESPACE__ . '\Controller\Index',
                        'action' => 'index',
                    ),
                ),
            ),
            'images' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/images[/:action][/:cid][/:id]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'cid'     => '[0-9_-]+',
                        'id'     => '[0-9_-]+',
                    ),
                    'defaults' => array(
                        'controller' =>  __NAMESPACE__ . '\Controller\Image',
                        'action' => 'index',
                    ),
                ),
            ),
            'files' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/files[/:action][/:cid][/:id]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'cid'     => '[0-9_-]+',
                        'id'     => '[0-9_-]+',
                    ),
                    'defaults' => array(
                        'controller' =>  __NAMESPACE__ . '\Controller\File',
                        'action' => 'index',
                    ),
                ),
            ),
            'presentations' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/presentations[/:action][/:cid][/:id]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'cid'     => '[0-9_-]+',
                        'id'     => '[0-9_-]+',
                    ),
                    'defaults' => array(
                        'controller' =>  __NAMESPACE__ . '\Controller\Presentation',
                        'action' => 'index',
                    ),
                ),
            ),
            'videos' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/videos[/:action][/:cid][/:id]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'cid'     => '[0-9_-]+',
                        'id'     => '[0-9_-]+',
                    ),
                    'defaults' => array(
                        'controller' =>  __NAMESPACE__ . '\Controller\Video',
                        'action' => 'index',
                    ),
                ),
            ),
            'worksheets' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/worksheets[/:action][/:cid][/:id]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'cid'     => '[0-9_-]+',
                        'id'     => '[0-9_-]+',
                    ),
                    'defaults' => array(
                        'controller' =>  __NAMESPACE__ . '\Controller\Worksheet',
                        'action' => 'index',
                    ),
                ),
            ),
            'lessonPlans' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/lesson-plans[/:action][/:id][/:page]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'id'     => '[0-9_-]+',
                        'page'     => '[0-9_-]+',
                    ),
                    'defaults' => array(
                        'controller' =>  __NAMESPACE__ . '\Controller\LessonPlan',
                        'action' => 'index',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'ama-materials/index/index' => __DIR__ . '/../view/ama-materials/index/index.phtml',
            'ama-materials/index/category' => __DIR__ . '/../view/ama-materials/index/category.phtml',
            'ama-materials/partial/add-material-dropdown' => __DIR__ . '/../view/ama-materials/partial/add-material-dropdown.phtml',
            'ama-materials/partial/breadcrumbs' => __DIR__ . '/../view/ama-materials/partial/add-material-dropdown.phtml',
            'ama-materials/image/add' => __DIR__ . '/../view/ama-materials/image/add.phtml',
            'ama-materials/partial/category-paginator' => __DIR__ . '/../view/ama-materials/partial/category-paginator.phtml',
            'ama-materials/partial/materials-paginator' => __DIR__ . '/../view/ama-materials/partial/materials-paginator.phtml',
            'ama-materials/partial/all-materials' => __DIR__ . '/../view/ama-materials/partial/all-materials.phtml',
            'ama-materials/lesson-plan/index' => __DIR__ . '/../view/ama-materials/lesson-plan/index.phtml',
            'ama-materials/file/select' => __DIR__ . '/../view/ama-materials/file/select.phtml',
            'ama-materials/file/add' => __DIR__ . '/../view/ama-materials/file/add.phtml',
            'ama-materials/image/select' => __DIR__ . '/../view/ama-materials/image/select.phtml',
            'ama-materials/image/add' => __DIR__ . '/../view/ama-materials/image/add.phtml',
            'ama-materials/video/select' => __DIR__ . '/../view/ama-materials/video/select.phtml',
            'ama-materials/video/add' => __DIR__ . '/../view/ama-materials/video/add.phtml',
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