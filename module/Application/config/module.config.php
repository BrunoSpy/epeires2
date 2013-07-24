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
                    'route'    => '/[:controller[/:action]]',
                	'constraints' => array(
                			'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    		'controller' => '[a-zA-Z][a-zA-Z0-9-]*',
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
        	'logger' => function($sl){
        		$logger = new \Zend\Log\Logger();
        		$logger->addWriter('FirePhp');
        		$logger->addWriter('ChromePhp');
        		return $logger;
        	}
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
        ),
        'initializers' => array(
        	'logger' => function($instance, $serviceManager){
        		if($instance instanceof \Application\Controller\LoggerAware){
        			$instance->setLogger($serviceManager->getServiceLocator()->get('logger'));
        		}
        				}
        ),
    ),
    'view_helpers' => array(
    	'invokables' => array(
    		'accordionGroup' => 'Application\View\Helper\AccordionGroup',
    		'controlGroup' => 'Application\View\Helper\ControlGroup',
    	),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
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
);
