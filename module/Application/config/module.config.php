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
return array(
    'router' => array(
        'routes' => array(
            'application' => array(
                'type' => 'segment',
                'may_terminate' => true,
                'options' => array(
                    'route' => '/[:controller[/:action[/:id]]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'controller' => '[a-zA-Z][a-zA-Z0-9-]*',
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Events',
                        'action' => 'index'
                    )
                )
            )
        )
    ),
    'console' => array(
        'router' => array(
            'routes' => array(
                'generate-report' => array(
                    'options' => array(
                        'route' => 'report [--email] [--delta=] <orgshortname>',
                        'defaults' => array(
                            'controller' => 'Application\Controller\Report',
                            'action' => 'report'
                        )
                    )
                ),
                'import-nmb2b' => array(
                    'options' => array(
                        'route' => 'import-nmb2b [--delta=] <orgshortname> <username>',
                        'defaults' => array(
                            'controller' => 'Application\Controller\Mil',
                            'action' => 'importNMB2B'
                        )
                    )
                )
            )
        )
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory'
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator'
        ),
        'factories' => array(
            'eventservice' => 'Application\Factories\EventServiceFactory',
            'customfieldservice' => 'Application\Factories\CustomfieldServiceFactory',
            'categoryfactory' => 'Application\Factories\CategoryEntityFactoryFactory'
        )
    ),
    'translator' => array(
        'locale' => 'fr_FR',
        'translation_file_patterns' => array(
            array(
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo'
            )
        )
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Events' => 'Application\Controller\EventsController',
            'Application\Controller\Frequencies' => 'Application\Controller\FrequenciesController',
            'Application\Controller\Radars' => 'Application\Controller\RadarsController',
            'Application\Controller\Report' => 'Application\Controller\ReportController',
            'Application\Controller\File' => 'Application\Controller\FileController',
            'Application\Controller\Alarm' => 'Application\Controller\AlarmController',
            'Application\Controller\Mil' => 'Application\Controller\MilController',
            'Application\Controller\Tabs' => 'Application\Controller\TabsController'
        )
    ),
    'view_helpers' => array(
        'invokables' => array(
            'accordionGroup' => 'Application\View\Helper\AccordionGroup',
            'impact' => 'Application\View\Helper\Impact',
            'eventfieldname' => 'Application\View\Helper\EventFieldName',
            'block' => 'Application\View\Helper\Block',
            'sector' => 'Application\View\Helper\Sector'
        ),
        'factories' => array(
            'eventName' => 'Application\Factories\EventNameFactory',
            'updateAuthor' => 'Application\Factories\UpdateAuthorFactory',
            'ipo' => 'Application\Factories\IPOFactory',
            'opsup' => 'Application\Factories\OpSupFactory',
            'customfieldvalue' => 'Application\Factories\CustomFieldValueFactory',
            'ismultipleallowed' => 'Application\Factories\CustomFieldMultipleAllowedFactory'
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
            __DIR__ . '/../view/application'
        ),
        'strategies' => array(
            'ViewJsonStrategy'
        )
    ),
    /**
     * Doctrine 2 Configuration
     */
    'doctrine' => array(
        'driver' => array(
            'application_entities' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(
                    __DIR__ . '/../src/Application/Entity'
                )
            ),
            'orm_default' => array(
                'drivers' => array(
                    'Application\Entity' => 'application_entities'
                )
            )
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
    
    'permissions' => array(
        'Evènements' => array(
            'events.create' => array(
                'name' => 'Création',
                'description' => 'Possibilité de créer de nouveaux évènements'
            ),
            'events.write' => array(
                'name' => 'Modification',
                'description' => 'Possibilité de modifier les évènements accessibles en lecture'
            ),
            'events.schedule' => array(
                'name' => 'Programmer',
                'description' => 'Affiche la case &#8243;Evènement programmé&#8243;.'
            ),
            'events.confirme' => array(
                'name' => 'Statut auto',
                'description' => 'Les évènements sont créés avec le statut &#8243;Confirmé&#8243;.'
            ),
            'events.mod-files' => array(
                'name' => 'Ajouter/Modifier fichiers',
                'description' => ''
            ),
            'events.mod-ipo' => array(
                'name' => 'Modifier IPO',
                'description' => ''
            ),
            'events.mod-opsup' => array(
                'name' => 'Modifier Chef Op',
                'description' => ''
            )
        ),
        'Fréquences' => array(
            'frequencies.read' => array(
                'name' => 'Lecture',
                'description' => 'Donne accès à l\'onglet Radio.'
            )
        ),
        'Radars' => array(
            'radars.read' => array(
                'name' => 'Lecture',
                'description' => ''
            )
        )
    ),
    
    'zfc_rbac' => array(
        'guards' => array(
            'ZfcRbac\Guard\ControllerPermissionsGuard' => array(
                array(
                    'controller' => 'Application\Controller\Frequencies',
                    'permissions' => [
                        'frequencies.read'
                    ]
                ),
                array(
                    'controller' => 'Application\Controller\Radars',
                    'permissions' => [
                        'radars.read'
                    ]
                )
            )
        )
    )
);
