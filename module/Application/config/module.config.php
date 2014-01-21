<?php
/**
 * Epeires 2
 * Application module configuration
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

return array(
    'router' => array(
        'routes' => array(
        	'application' => array(
                'type'    => 'segment',
            	'may_terminate' => true,
                'options' => array(
                    'route'    => '/[:controller[/:action[/:id]]]',
                	'constraints' => array(
                			'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    		'controller' => '[a-zA-Z][a-zA-Z0-9-]*',
                			'id' => '[0-9]+',
               		),
                    'defaults' => array(
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Events',
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
        'factories' => array(
        	'eventservice' => 'Application\Factories\EventServiceFactory',
        	'customfieldservice' => 'Application\Factories\CustomfieldServiceFactory',
        	'categoryfactory' => 'Application\Factories\CategoryEntityFactoryFactory',
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
            'Application\Controller\Events' => 'Application\Controller\EventsController',
            'Application\Controller\Frequencies' => 'Application\Controller\FrequenciesController',
            'Application\Controller\Radars' => 'Application\Controller\RadarsController',
    	),
    ),
    'view_helpers' => array(
    	'invokables' => array(
    		'accordionGroup' => 'Application\View\Helper\AccordionGroup',
    		'controlGroup' => 'Application\View\Helper\ControlGroup',
    		'customFieldGroup' => 'Application\View\Helper\CustomFieldGroup',
    		'impact' => 'Application\View\Helper\Impact',
    		'formDateTimeEnhanced' => 'Application\View\Helper\FormDateTimeEnhanced',
    		'customfieldvalue' => 'Application\View\Helper\CustomFieldValue',
    		'eventfieldname' => 'Application\View\Helper\EventFieldName',
    		'block' => 'Application\View\Helper\Block',
    	),
    	'factories' => array(
    		'eventName' => 'Application\Factories\EventNameFactory',
    		'ipo' => 'Application\Factories\IPOFactory',
    		'opsup' => 'Application\Factories\OpSupFactory'
    	),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
            __DIR__ . '/../view/application',
        ),
    	'strategies' => array(
    		'ViewJsonStrategy',	
    	),
    ),
    /**
     * Doctrine 2 Configuration
     */
    'doctrine' => array(
    	'driver' => array(
    		'application_entities' => array(
				'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
				'cache' => 'array',
				'paths' => array(__DIR__ . '/../src/Application/Entity')
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
    			__DIR__ . '/../public',
    		),
    	),
    ),
    
    'permissions' => array(
    	'Evènements' => array(
    		'events.create' => 'Création',
    		'events.write' => 'Modification',
    		'events.status' => 'Modification statut',
    		'events.mod-files' => 'Ajouter/Modifier fichiers',
    		'events.mod-ipo' => 'Modifier IPO',
    		'events.mod-opsup' => 'Modifier Chef Op',
    	),
    	'Fréquences' => array(
    		'frequencies.read' => 'Lecture',
    	),	
    ),
);
