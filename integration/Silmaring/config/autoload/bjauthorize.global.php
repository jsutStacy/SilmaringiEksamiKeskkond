<?php

return array(
    'zfcuser' => array(
        // telling ZfcUser to use our own class
        'user_entity_class'       => 'AmaUsers\Entity\User',
        // telling ZfcUserDoctrineORM to skip the entities it defines
        'enable_default_entities' => false,
    ),

    'bjyauthorize' => array(
        'template' => 'application/error/403',
        'authenticated_role' => 'v_student',

        // Using the authentication identity provider, which basically reads the roles from the auth service's identity
        'identity_provider' => 'amaIdentityProvider',

        'role_providers'        => array(
            // using an object repository (entity repository) to load all roles into our ACL
            'BjyAuthorize\Provider\Role\ObjectRepositoryProvider' => array(
                'object_manager'    => 'doctrine.entitymanager.orm_default',
                'role_entity_class' => 'AmaUsers\Entity\Role',
            ),
        ),

        'resource_providers' => array(
            'BjyAuthorize\Provider\Resource\Config' => array(
                //'School' => array('manage'),
            ),
        ),

        'rule_providers' => array(
            'BjyAuthorize\Provider\Rule\Config' => array(
                'allow' => array(
                    //array(array('school'), 'School', 'manage', 'ManageSchool'),
                    //array(array('school'), 'School', 'manage', 'ManageSchool')
                ),
                'deny' => array(
                ),
            ),
        ),

        'guards' => array(
            /* If this guard is specified here (i.e. it is enabled), it will block
             * access to all controllers and actions unless they are specified here.
             * You may omit the 'action' index to allow access to the entire controller
             */
            'BjyAuthorize\Guard\Controller' => array(
                array('controller' => 'index', 'action' => 'index', 'roles' => array('guest', 'v_student')),
                //array('controller' => 'index', 'action' => 'stuff', 'roles' => array('user', 'manager')),
                array('controller' => 'Application\Controller\Index', 'action' => 'error', 'roles' => array( 'v_student', 'admin', 'school', 'v_teacher', 'k_student', 'k_teacher')),
                // You can also specify an array of actions or an array of controllers (or both)
                // allow "guest" and "admin" to access actions "list" and "manage" on these "index",
                // "static" and "console" controllers
                array(
                    'controller' => array('index', 'static', 'console'),
                    'action' => array('list', 'manage'),
                    'roles' => array('guest', 'admin')
                ),
                array(
                    'controller' => array('search', 'administration'),
                    'roles' => array('admin')
                ),
                array('controller' => 'zfcuser', 'roles' => array()),

                // Below is the default index action used by the ZendSkeletonApplication
                array('controller' => 'ZFTool\Controller\Create', 'roles' => 'guest'),
                array('controller' => 'AmaUsers\Controller\Index', 'roles' => array('guest', 'v_student', 'admin', 'school', 'v_teacher', 'k_student', 'k_teacher')),
                array('controller' => 'AmaUsers\Controller\Register', 'roles' => array('guest', 'v_student', 'admin', 'school', 'v_teacher', 'k_student', 'k_teacher')),
                array('controller' => 'AmaUsers\Controller\Dashboard', 'roles' => array('v_student', 'admin', 'school', 'v_teacher', 'k_student', 'k_teacher')),
                array('controller' => 'AmaUsers\Controller\Settings', 'roles' => array('v_student', 'admin', 'school', 'v_teacher', 'k_student', 'k_teacher')),
                array('controller' => 'AmaUsers\Controller\Statistics', 'roles' => array('v_student', 'admin', 'school', 'v_teacher', 'k_student', 'k_teacher')),
                array('controller' => 'AmaSchools\Controller\Index', 'roles' => array('admin')),
                array('controller' => 'AmaUsers\Controller\Admin', 'roles' => array('admin')),
                array('controller' => 'AmaUsers\Controller\School', 'roles' => array('school')),
                array('controller' => 'AmaCategories\Controller\Index', 'roles' => array('v_student', 'admin', 'school', 'v_teacher', 'k_student', 'k_teacher'), 'action' => array('index','view')),
                array('controller' => 'AmaCategories\Controller\Index', 'roles' => array('admin'), 'action' => array('add', 'add-ajax','edit', 'edit-ajax', 'delete', 'import', 'import-ajax')),
                array('controller' => 'AmaMaterials\Controller\Index', 'roles' => array('v_student', 'admin', 'school', 'v_teacher', 'k_student', 'k_teacher')),
                array('controller' => 'AmaMaterials\Controller\Image', 'roles' => array('admin', 'school', 'k_teacher')),
                array('controller' => 'AmaMaterials\Controller\File', 'roles' => array('admin', 'school',  'k_teacher')),
                array('controller' => 'AmaMaterials\Controller\Presentation', 'roles' => array('admin', 'school',  'k_teacher')),
                array('controller' => 'AmaMaterials\Controller\Video', 'roles' => array('admin', 'school',  'k_teacher')),
                array('controller' => 'AmaTests\Controller\Index', 'roles' => array('admin', 'school', 'k_teacher')),
                array('controller' => 'AmaTests\Controller\Manage', 'roles' => array('admin', 'school', 'k_teacher')),
                array('controller' => 'AmaTests\Controller\Solve', 'roles' => array('admin', 'k_student')),
                array('controller' => 'AmaWorksheets\Controller\Index', 'roles' => array('admin', 'school', 'k_teacher')),
                array('controller' => 'AmaWorksheets\Controller\Manage', 'roles' => array('admin', 'school', 'k_teacher')),
                array('controller' => 'AmaWorksheets\Controller\Solve', 'roles' => array('admin', 'k_student')),
                array('controller' => 'AmaMaterials\Controller\LessonPlan', 'roles' => array('admin', 'school', 'k_teacher')),
                array('controller' => 'AmaMaterials\Controller\Worksheet', 'roles' => array('admin', 'school', 'k_student', 'k_teacher')),
                array('controller' => 'htimg', 'roles' => array('v_student', 'admin', 'school', 'v_teacher', 'k_student', 'k_teacher')),
                array('controller' => 'TpMinifyProxy', 'roles' => array('guest', 'v_student', 'admin', 'school', 'v_teacher', 'k_student', 'k_teacher')),
                array('controller' => 'Application\Controller\Scheduled', 'roles' => array('guest')),
                array('controller' => 'Application\Controller\Service', 'roles' => array('guest')),
                array('controller' => 'Eksamikool\Controller\Index', 'roles' => array('guest', 'v_student', 'admin', 'v_teacher', 'k_student', 'k_teacher')),
                array('controller' => 'Eksamikool\Controller\Admin', 'roles' => array('admin')),
                array('controller' => 'Eksamikool\Controller\Teacher', 'roles' => array('admin', 'v_teacher', 'k_teacher')),
                array('controller' => 'Eksamikool\Controller\Student', 'roles' => array('admin', 'v_student', 'k_student')),
                array('controller' => 'Eksamikool\Controller\OurError', 'roles' => array('guest', 'v_student', 'admin', 'v_teacher', 'k_student', 'k_teacher')),
            ),
        ),
    ),
);
