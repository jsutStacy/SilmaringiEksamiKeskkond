<?php
namespace AmaCategories;

return array(
    'controllers' => array(
        'invokables' => array(
            'AmaCategories\Controller\Index' => __NAMESPACE__ . '\Controller\IndexController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'categories' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/category[/:action][/:id]',
                    'constraints' => array(
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]+',
                        'id'     => '[0-9_-]+',
                    ),
                    'defaults' => array(
                        'controller' =>  __NAMESPACE__ . '\Controller\Index',
                        'action' => 'index',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'ama-categories/index/index' => __DIR__ . '/../view/ama-categories/index/index.phtml',
            'ama-categories/index/add' => __DIR__ . '/../view/ama-categories/index/add.phtml',
            'ama-categories/index/edit' => __DIR__ . '/../view/ama-categories/index/edit.phtml',
            'ama-categories/index/view' => __DIR__ . '/../view/ama-categories/index/view.phtml',
            'ama-categories/index/import' => __DIR__ . '/../view/ama-categories/index/import.phtml',
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