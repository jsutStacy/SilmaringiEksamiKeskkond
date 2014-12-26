<?php

return array(
    'navigation' => array(
        'default' => array(
            array(
                'label' => '',
                'route' => 'home',
                'class' => '',
            ),
        ),
        'admin_navigation' => array(
            array(
                'label' => _('Schools'),
                'route' => 'schools',
                'pages' => array(
                        array(
                            'label' => _('Add school'),
                            'route' => 'schools',
                            'action'=> 'add'
                    )
                )
            ),
            array(
                'label' => _('Users'),
                'route' => 'users',
            )
        ),
        'school_navigation' => array(
            array(
                'label' => _('My schools'),
                'route' => 'mySchools',
            ),
        ),
        'single_school_navigation' => array(
        ),
        'category_navigation' => array(
        ),
        'wp_navigation' => array(
        ),
        'tab_navigation' => array(
            array(
                'label' => _('Materials'),
                'route' => 'materials',
            ),
            array(
                'label' => _('Tests'),
                'route' => 'tests',
            ),
            array(
                'label' => _('Worksheets'),
                'route' => 'worksheets',
            ),
            array(
                'label' => _('My lesson plans'),
                'route' => 'lessonPlans',
            ),
        ),
        'normal_navigation' => array(
            array(
                'label' => _('Settings'),
                'route' => 'settings',
                'class' => 'gear-ico'
            ),
            array(
                'label' => _('Statistics'),
                'route' => 'statistics',
                'class' => 'stats-ico'
            )
        ),
    )
);