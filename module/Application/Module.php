<?php
/**
 * Epeires 2
 *
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\ModuleManager\ModuleManager;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;

class Module implements ConsoleUsageProviderInterface
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $this->bootstrapSession($e);
    }

    public function bootstrapSession($e)
    {
    	$session = $e->getApplication()
    	->getServiceManager()
    	->get('Zend\Session\SessionManager');
    	$session->start();
    
    	$container = new Container('initialized');
    	if (!isset($container->init)) {
    		$session->regenerateId(true);
    		$container->init = 1;
    	}
    }
    
    public function getServiceConfig()
    {
    	return array(
    			'factories' => array(
    					'Zend\Session\SessionManager' => function ($sm) {
    						$config = $sm->get('config');
    						if (isset($config['session'])) {
    							$session = $config['session'];
    
    							$sessionConfig = null;
    							if (isset($session['config'])) {
    								$class = isset($session['config']['class'])  ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
    								$options = isset($session['config']['options']) ? $session['config']['options'] : array();
    								$sessionConfig = new $class();
    								$sessionConfig->setOptions($options);
    							}
    
    							$sessionStorage = null;
    							if (isset($session['storage'])) {
    								$class = $session['storage'];
    								$sessionStorage = new $class();
    							}
    
    							$sessionSaveHandler = null;
    							if (isset($session['save_handler'])) {
    								// class should be fetched from service manager since it will require constructor arguments
    								$sessionSaveHandler = $sm->get($session['save_handler']);
    							}
    
    							$sessionManager = new SessionManager($sessionConfig, $sessionStorage, $sessionSaveHandler);
    
    							if (isset($session['validators'])) {
    								$chain = $sessionManager->getValidatorChain();
    								foreach ($session['validators'] as $validator) {
    									$validator = new $validator();
    									$chain->attach('session.validate', array($validator, 'isValid'));
    
    								}
    							}
    						} else {
    							$sessionManager = new SessionManager();
    						}
    						Container::setDefaultManager($sessionManager);
    						return $sessionManager;
    					},
    			),
    	);
    }
    
    
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    public function init(ModuleManager $moduleManager) {
    	$sharedEvents = $moduleManager->getEventManager()->getSharedManager();
    	$sharedEvents->attach(
    			__NAMESPACE__,
    			'dispatch',
    			function($e) {
    				$controller = $e->getTarget();
    				$controller->layout('layout/app-layout');
    			},
    			100
    	);
    }
    
    public function getConsoleUsage(Console $console){
        return array(
            'report [--email] [--delta=] orgshortname' => 'Generate a daily report for an organisation',
            array('--email', 'Send an email to IPO'),
            array('--delta', '(optional) Delta to add to the current day (-1=yesterday)'),
            array('orgshortname', 'Shortname of the organisation as configured in the database'),
            'import-nmb2b [--delta=] orgshortname username' => 'Import RSAs from NM B2B WS',
            array('--delta', '(optional) Delta to add to the current day (-1=yesterday)'),
            array('orgshortname', 'Shortname of the organisation as configured in the database'),
            array('username', 'User Name of the author of created events')
        );
    }
}
