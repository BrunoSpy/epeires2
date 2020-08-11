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
            'ipo' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/ipo[/:controller[/:action]]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        '__NAMESPACE__' => 'IPO\Controller',
                        'controller' => 'Index',
                        'action' => 'index'
                    )
                )
            )
        )
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Laminas\Cache\Service\StorageCacheAbstractServiceFactory',
            'Laminas\Log\LoggerAbstractServiceFactory'
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator'
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
    'doctrine' => array(
        'driver' => array(
            'ipo_entities' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(
                    __DIR__ . '/../src/Entity'
                )
            ),
            'orm_default' => array(
                'drivers' => array(
                    'IPO\Entity' => 'ipo_entities'
                )
            )
        )
    ),
    'controllers' => array(
        'factories' => array(
            'IPO\Controller\Index' => 'IPO\Controller\Factory\IndexControllerFactory',
            'IPO\Controller\Report' => 'IPO\Controller\Factory\ReportControllerFactory',
            'IPO\Controller\Search' => 'IPO\Controller\Factory\SearchControllerFactory'
        )
    ),
    'view_manager' => array(
        'display_not_found_reason' => false,
        'display_exceptions' => false,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'ipo/layout' => __DIR__ . '/../view/layout/ipolayout.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
            __DIR__ . '/../view/ipo'
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
        'IPO' => array(
            'ipo.read' => array(
                'name' => 'Accès',
                'description' => ''
            )
        )
    ),
    'lmc_rbac' => array(
        'guards' => array(
            'LmcRbacMvc\Guard\RoutePermissionsGuard' => array(
                'ipo' => array(
                    'ipo.read'
                )
            ),
        )
    )
);
