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
use LmcRbacMvc\Guard\GuardInterface;
use Laminas\Session;

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
            'lmcuser_entity' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'paths' => array(
                    __DIR__ . '/../src/Entity'
                )
            ),
            'orm_default' => array(
                'drivers' => array(
                    'Core\Entity' => 'lmcuser_entity'
                )
            )
        )
    ),
    'service_manager' => array(
        'factories' => array(
            'doctrine.loggable' => 'Core\Factory\LoggableListenerFactory',
            'nmb2b' => 'Core\Factory\NMB2BServiceFactory',
            'mattermostservice' => 'Core\Factory\MattermostServiceFactory',
            'notamweb' => 'Core\Factory\NOTAMWebServiceFactory',
            'mapd' => 'Core\Factory\MAPDServiceFactory',
            'emailservice' => 'Core\Factory\EmailServiceFactory',
            'efneservice' => 'Core\Factory\eFNEServiceFactory'

        ),
        'aliases' => array(
            'Laminas\Authentication\AuthenticationService' => 'lmcuser_auth_service'
        ),
        'abstract_factories' => array(
            'Laminas\Log\LoggerAbstractServiceFactory'
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
    'lmc_rbac' => array(
        'protection_policy' => GuardInterface::POLICY_ALLOW,
        'guest_role' => 'guest',
        'role_provider' => array(
            'LmcRbacMvc\Role\ObjectRepositoryRoleProvider' => array(
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
    'lmcuser' => array(
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
        'use_login_form_csrf' => false,
        'use_redirect_parameter_if_present' => true,
        'auth_identity_fields' => array(
            'username',
            'email'
        ),
        'enable_user_state' => true,
        'default_user_state' => 1,
        'allowed_login_states' => array(1)
    ),
    'view_manager' => array(
        'display_not_found_reason' => false,
        'display_exceptions' => false,
        'doctype' => 'HTML5',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
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
            'userMenu' => 'Core\Factory\UserMenuFactory',
            'navbartop' => 'Core\Factory\NavBarTopFactory',
            'navbar' => 'Core\Factory\NavBarFactory',
            'viewselector' => 'Core\Factory\ViewSelectorFactory'
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
    ),
    'log' => array(
        'EpeiresLogger' => array(
            'writers' => array(
                'stream' => [
                    'name' => 'stream',
                    'priority' => 1,
                    'options' => [
                        'stream' => __DIR__ . '/../../../logs/epeires2.log',
                        'formatter' => [
                            'name' => \Laminas\Log\Formatter\Simple::class,
                            'options' => [
                                'format' => '%timestamp% %priorityName% (%priority%): %message% %extra%',
                                'dateTimeFormat' => 'c',
                            ],
                        ],
                        'filters' => [
                            'priority' => [
                                'name' => 'priority',
                                'options' => [
                                    'operator' => '<=',
                                    'priority' => \Laminas\Log\Logger::INFO,
                                ],
                            ],
                        ],
                    ],
                ],
            ),
            'processors' => array(
                'requestid' => [
                    'name' => \Laminas\Log\Processor\RequestId::class,
                ],
            )
        )
    )
);
