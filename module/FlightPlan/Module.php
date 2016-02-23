<?php

namespace FlightPlan;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
use Zend\ModuleManager\Feature\RouteProviderInterface;
//use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ControllerProviderInterface;

use Zend\Mvc\ModuleRouteListener;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;

class Module implements ConfigProviderInterface, 
                        AutoloaderProviderInterface, 
                        ViewHelperProviderInterface

{
    
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
                
       /*$e->getApplication()
        ->getServiceManager()
        ->get('router')
        ->addRoutes($this->getRouteConfig());
        * 
        */
    }

    public function init(ModuleManager $moduleManager)
    {
        $sharedEvents = $moduleManager->getEventManager()->getSharedManager();
        $sharedEvents->attach(__NAMESPACE__, 'dispatch', function ($e) {
            $controller = $e->getTarget();
            $controller->layout('layout/layout');
        }, 100);
    }
    
/*    public function onBootstrap(EventInterface $e)
    {   
        
       $e->getApplication()
        ->getServiceManager()
        ->get('router')
        ->addRoutes($this->getRouteConfig());
    }
*/
    
    
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getAutoloaderConfig()
    {
        return include __DIR__ . '/config/autoload.config.php';
    }

    public function getRouteConfig()
    {
        //return include __DIR__ . '/config/router.config.php';
    }

    public function getControllerConfig()
    {
        return include __DIR__ . '/config/controllers.config.php';
    }

    public function getViewHelperConfig() 
    {  
        return include __DIR__ . '/config/viewhelpers.config.php';
    }

}