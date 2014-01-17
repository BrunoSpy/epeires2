<?php
/**
 * Epeires 2
 * Admin module configuration
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

return array(
    'router' => array(
        'routes' => array(
            'administration' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/admin[/:controller[/:action]]',
                	'constraints' => array(
                			'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    		'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
               		),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Administration\Controller',
                        'controller'    => 'Home',
                        'action'        => 'index',
                    ),
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'fr_FR',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Administration\Controller\Home' => 'Administration\Controller\HomeController',
            'Administration\Controller\Categories' => 'Administration\Controller\CategoriesController',
            'Administration\Controller\Fields' => 'Administration\Controller\FieldsController',
            'Administration\Controller\Models' => 'Administration\Controller\ModelsController',
            'Administration\Controller\Config' => 'Administration\Controller\ConfigController',
            'Administration\Controller\Centre' => 'Administration\Controller\CentreController',
            'Administration\Controller\Maintenance' => 'Administration\Controller\MaintenanceController',
            'Administration\Controller\Radio' => 'Administration\Controller\RadioController',
            'Administration\Controller\Users' => 'Administration\Controller\UsersController',
            'Administration\Controller\Roles' => 'Administration\Controller\RolesController',
            'Administration\Controller\Radars' => 'Administration\Controller\RadarsController',
            'Administration\Controller\IPOS' => 'Administration\Controller\IPOSController'
    	),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
        	'admin/layout'			  => __DIR__ . '/../view/layout/adminlayout.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
            __DIR__ . '/../view/administration',
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
    'zfc_rbac' => array(
    	'guards' => array(
   			'ZfcRbac\Guard\RouteGuard' => array(
    			'administration' => array('admin'),
    		),
    	),
    ),
);
