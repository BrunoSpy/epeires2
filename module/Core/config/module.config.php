<?php
/*
 * This file is part of Epeires².
 * Epeires² is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Epeires² is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Epeires². If not, see <http://www.gnu.org/licenses/>.
 *
 */
use ZfcRbac\Guard\GuardInterface;

return array(
    'doctrine' => array(
        'eventmanager' => array(
            'orm_default' => array(
                'subscribers' => array(
                    'doctrine.loggable',
                    'Gedmo\Sortable\SortableListener'
                )
            )
        ),
        'driver' => array(
            // overriding zfc-user-doctrine-orm's config
            'zfcuser_entity' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'paths' => __DIR__ . '/../src/Core/Entity'
            ),
            'orm_default' => array(
                'drivers' => array(
                    'Core\Entity' => 'zfcuser_entity'
                )
            )
        )
    ),
    'service_manager' => array(
        'factories' => array(
            'doctrine.loggable' => 'Core\Factory\LoggableListenerFactory',
            'nmb2b' => 'Core\Factory\NMB2BServiceFactory'
        ),
        'aliases' => array(
            'Zend\Authentication\AuthenticationService' => 'zfcuser_auth_service'
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'Core\Controller\User' => 'Core\Controller\UserController'
        )
    ),
    'router' => array(
        'routes' => array(
            'coreuser' => array(
                'type' => 'Literal',
                'priority' => 1000,
                'options' => array(
                    'route' => '/user',
                    'defaults' => array(
                        'controller' => 'coreuser',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'login' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/login',
                            'defaults' => array(
                                'controller' => 'coreuser',
                                'action' => 'login'
                            )
                        )
                    )
                )
            )
        )
    ),
    'zfc_rbac' => array(
        'protection_policy' => GuardInterface::POLICY_ALLOW,
        'guest_role' => 'guest',
        'role_provider' => array(
            'ZfcRbac\Role\ObjectRepositoryRoleProvider' => array(
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'class_name' => 'Core\Entity\Role',
                'role_name_property' => 'name'
            )
        ),
        'unauthorized_strategy' => array(
            'template' => 'error/custom-403'
        ),
        'guard_manager' => array(
            'factories' => array(
                'Core\Guard\AutoConnectGuard' => 'Core\Factory\AutoConnectGuardFactory'
            )
        )
    ),
    'zfcuser' => array(
        // telling ZfcUser to use our own class
        'user_entity_class' => 'Core\Entity\User',
        // telling ZfcUserDoctrineORM to skip the entities it defines
        'enable_default_entities' => false,
        'enable_username' => true,
        'enable_display_name' => true,
        'enable_registration' => false,
        'login_after_registration' => false,
        'logout_redirect_route' => 'application',
        'login_redirect_route' => 'application',
        'use_redirect_parameter_if_present' => true,
        'auth_identity_fields' => array(
            'username',
            'email'
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view'
        )
    ),
    'view_helpers' => array(
        'invokables' => array(
            'modalwindow' => 'Core\View\Helper\ModalWindow',
            'notifications' => 'Core\View\Helper\Notifications',
            'controlGroup' => 'Core\View\Helper\ControlGroup'
        ),
        'factories' => array(
            'userMenu' => 'Core\Factory\UserMenuFactory'
        )
    ),
    'session' => array(
        'config' => array(
            'class' => 'Zend\Session\Config\SessionConfig',
            'options' => array(
                'name' => 'epeires2'
            )
        ),
        'storage' => 'Zend\Session\Storage\SessionArrayStorage',
        'validators' => array(
            'Zend\Session\Validator\RemoteAddr',
            'Zend\Session\Validator\HttpUserAgent'
        )
    ),
    /**
     * Automatically use module assets
     */
    'asset_manager' => array(
        'resolver_configs' => array(
            'paths' => array(
                __DIR__ . '/../public'
            )
        )
    )
);
