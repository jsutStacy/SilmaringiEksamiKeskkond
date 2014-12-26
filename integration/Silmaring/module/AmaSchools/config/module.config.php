<?php
namespace AmaSchools;

return array(
    'controllers' => array(
        'invokables' => array(
            'AmaSchools\Controller\Index' => __NAMESPACE__ . '\Controller\IndexController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'schools' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/schools[/:action][/:id]',
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
            'schoolPage' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/schools/pages',
                    'defaults' => array(
                        'controller' =>  __NAMESPACE__ . '\Controller\Index',
                        'action' => 'page',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'ama-schools/index/index' => __DIR__ . '/../view/ama-schools/index/index.phtml',
            'ama-schools/index/add' => __DIR__ . '/../view/ama-schools/index/add.phtml',
            'ama-schools/index/edit' => __DIR__ . '/../view/ama-schools/index/edit.phtml',
            'ama-schools/index/choose' => __DIR__ . '/../view/ama-schools/index/choose.phtml',
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