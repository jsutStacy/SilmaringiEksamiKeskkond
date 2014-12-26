<?php
namespace AmaTests;

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
            'tests' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/tests[/:action][/:id][/:page]',
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
            'manageTests' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/manage-tests[/:action][/:cid][/:id]',
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
            'solveTests' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/solve-tests[/:action][/:id][/:fid]',
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
            'ama-tests/index/index' => __DIR__ . '/../view/ama-tests/index/index.phtml',
            'ama-tests/index/category' => __DIR__ . '/../view/ama-tests/index/category.phtml',
            'ama-tests/manage/index' => __DIR__ . '/../view/ama-tests/manage/index.phtml',
            'ama-tests/manage/add' => __DIR__ . '/../view/ama-tests/manage/add.phtml',
            'ama-tests/manage/edit' => __DIR__ . '/../view/ama-tests/manage/edit.phtml',
            'ama-tests/manage/statistics' => __DIR__ . '/../view/ama-tests/manage/statistics.phtml',
            'ama-tests/manage/valuation' => __DIR__ . '/../view/ama-tests/manage/valuation.phtml',
            'ama-tests/manage/statistics-answers' => __DIR__ . '/../view/ama-tests/manage/statistics-answers.phtml',
            'ama-tests/partial/add-question' => __DIR__ . '/../view/ama-tests/partial/add-question.phtml',
            'ama-tests/partial/edit-question' => __DIR__ . '/../view/ama-tests/partial/edit-question.phtml',
            'ama-tests/partial/image-thumb' => __DIR__ . '/../view/ama-tests/partial/image-thumb.phtml',
            'ama-tests/partial/no-edit' => __DIR__ . '/../view/ama-tests/partial/no-edit.phtml',
            'ama-tests/partial/edit-question-disabled' => __DIR__ . '/../view/ama-tests/partial/edit-question-disabled.phtml',
            'ama-tests/solve/index' => __DIR__ . '/../view/ama-tests/solve/index.phtml',
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