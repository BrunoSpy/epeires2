<?php
/**
 * Epeires 2
 * Admin module configuration
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

return array(
    'router' => array(
        'routes' => array(
            'ipo' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/ipo[/:controller[/:action]]',
                	'constraints' => array(
                			'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    		'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
               		),
                    'defaults' => array(
                        '__NAMESPACE__' => 'IPO\Controller',
                        'controller'    => 'Index',
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
	'doctrine' => array(
			'driver' => array(
					'ipo_entities' => array(
							'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
							'cache' => 'array',
							'paths' => array(__DIR__ . '/../src/IPO/Entity')
					),
					'orm_default' => array(
							'drivers' => array(
									'IPO\Entity' => 'ipo_entities'
							)
					)
			)
	),
    'controllers' => array(
        'invokables' => array(
            'IPO\Controller\Index' => 'IPO\Controller\IndexController',
        	'IPO\Controller\Report' => 'IPO\Controller\ReportController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'ipo/layout'    => __DIR__ . '/../view/layout/ipolayout.phtml',
            'error/404'     => __DIR__ . '/../view/error/404.phtml',
            'error/index'   => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
            __DIR__ . '/../view/ipo',
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
    
    'permissions' => array(
    	'IPO' => array(
    		'ipo.read' =>  
    			array('name' => 'Accès', 'description' => ''),
    	),	
    ),
);
