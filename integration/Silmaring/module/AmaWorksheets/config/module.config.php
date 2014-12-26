<?php
namespace AmaWorksheets;

return array(
    'controllers' => array(
        'invokables' => array(
            __NAMESPACE__ . '\Controller\Index' => __NAMESPACE__ . '\Controller\IndexController',
            __NAMESPACE__ . '\Controller\Manage' => __NAMESPACE__ . '\Controller\ManageController',
            __NAMESPACE__ . '\Controller\Solve' => __NAMESPACE__ . '\Controller\SolveController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'worksheets' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/worksheets[/:action][/:id][/:page]',
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
            'manageWorksheets' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/manage-worksheets[/:action][/:cid][/:id]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'cid'     => '[0-9_-]+',
                        'id'     => '[0-9_-]+',
                    ),
                    'defaults' => array(
                        'controller' =>  __NAMESPACE__ . '\Controller\Manage',
                        'action' => 'index',
                    ),
                ),
            ),
            'solveWorksheets' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/solve-worksheets[/:action][/:id][/:fid]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'id'     => '[0-9_-]+',
                        'fid'     => '[0-9_-]+'
                    ),
                    'defaults' => array(
                        'controller' =>  __NAMESPACE__ . '\Controller\Solve',
                        'action' => 'index',
                    ),
                ),
            )
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'ama-worksheets/index/index' => __DIR__ . '/../view/ama-worksheets/index/index.phtml',
            'ama-worksheets/index/category' => __DIR__ . '/../view/ama-worksheets/index/category.phtml',
            'ama-worksheets/manage/index' => __DIR__ . '/../view/ama-worksheets/manage/index.phtml',
            'ama-worksheets/manage/add' => __DIR__ . '/../view/ama-worksheets/manage/add.phtml',
            'ama-worksheets/manage/edit' => __DIR__ . '/../view/ama-worksheets/manage/edit.phtml',
            'ama-worksheets/manage/statistics' => __DIR__ . '/../view/ama-worksheets/manage/statistics.phtml',
            'ama-worksheets/manage/valuation' => __DIR__ . '/../view/ama-worksheets/manage/valuation.phtml',
            'ama-worksheets/manage/statistics-answers' => __DIR__ . '/../view/ama-worksheets/manage/statistics-answers.phtml',
            'ama-worksheets/partial/add-question' => __DIR__ . '/../view/ama-worksheets/partial/add-question.phtml',
            'ama-worksheets/partial/edit-question' => __DIR__ . '/../view/ama-worksheets/partial/edit-question.phtml',
            'ama-worksheets/partial/image-thumb' => __DIR__ . '/../view/ama-worksheets/partial/image-thumb.phtml',
            'ama-worksheets/partial/no-edit' => __DIR__ . '/../view/ama-worksheets/partial/no-edit.phtml',
            'ama-worksheets/partial/edit-question-disabled' => __DIR__ . '/../view/ama-worksheets/partial/edit-question-disabled.phtml',
            'ama-worksheets/solve/index' => __DIR__ . '/../view/ama-worksheets/solve/index.phtml',
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