<?php

use ZfcRbac\Guard\GuardInterface;

return array(
    'doctrine' => array(
        'eventmanager' => array(
            'orm_default' => array(
                'subscribers' => array(
                    'doctrine.loggable',
                    'Gedmo\Sortable\SortableListener'
                ),
            ),
        ),
        'driver' => array(
            // overriding zfc-user-doctrine-orm's config
            'zfcuser_entity' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'paths' => __DIR__ . '/../src/Core/Entity',
            ),
            'orm_default' => array(
                'drivers' => array(
                    'Core\Entity' => 'zfcuser_entity',
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'doctrine.loggable' => 'Core\Factory\LoggableListenerFactory',
        ),
        'aliases' => array(
            'Zend\Authentication\AuthenticationService' => 'zfcuser_auth_service',
        ),
    ),
    'zfc_rbac' => array(
        'protection_policy' => GuardInterface::POLICY_ALLOW,
        'guest_role' => 'guest',
        'role_provider' => array(
            'ZfcRbac\Role\ObjectRepositoryRoleProvider' => array(
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'class_name' => 'Core\Entity\Role',
                'role_name_property' => 'name',
            ),
        ),
        'unauthorized_strategy' => array(
            'template' => 'error/custom-403'
        ),
    ),
    'zfcuser' => array(
        // telling ZfcUser to use our own class
        'user_entity_class' => 'Core\Entity\User',
        // telling ZfcUserDoctrineORM to skip the entities it defines
        'enable_default_entities' => false,
        'enable_username' => true,
        'enable_display_name' => true,
        'enable_registration' => true,
        'login_after_registration' => false,
        'logout_redirect_route' => 'application',
        'use_redirect_parameter_if_present' => true,
        'auth_identity_fields' => array('username', 'email'),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'modalwindow' => 'Core\View\Helper\ModalWindow',
        ),
        'factories' => array(
            'userMenu' => 'Core\Factory\UserMenuFactory',
        ),
    ),
    'session' => array(
        'config' => array(
            'class' => 'Zend\Session\Config\SessionConfig',
            'options' => array(
                'name' => 'epeires2',
            ),
        ),
        'storage' => 'Zend\Session\Storage\SessionArrayStorage',
        'validators' => array(
            'Zend\Session\Validator\RemoteAddr',
            'Zend\Session\Validator\HttpUserAgent',
        ),
    ),
    /**
     * Automatically use module assets
     */
    'asset_manager' => array(
    	'resolver_configs' => array(
    		'paths' => array(
    			__DIR__ . '/../public',
    		),
    	),
    ),
);
