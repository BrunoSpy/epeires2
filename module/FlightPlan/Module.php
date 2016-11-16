<?php

namespace FlightPlan;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
use Zend\ModuleManager\Feature\ControllerPluginProviderInterface;

use Zend\Mvc\ModuleRouteListener;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;
use Doctrine\ORM\EntityManager;

class Module implements ConfigProviderInterface, 
                        AutoloaderProviderInterface, 
                        ViewHelperProviderInterface,
                        ControllerPluginProviderInterface

{
    
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function init(ModuleManager $moduleManager)
    {
        $sharedEvents = $moduleManager->getEventManager()->getSharedManager();
        $sharedEvents->attach(__NAMESPACE__, 'dispatch', function ($e) {
            $controller = $e->getTarget();
            $controller->layout('layout/layout');
            $controller->setEntityManager($controller->getServiceLocator()->get(EntityManager::class));
        }, 100);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getAutoloaderConfig()
    {
        return include __DIR__ . '/config/autoload.config.php';
    }

    public function getControllerConfig()
    {
        return include __DIR__ . '/config/controllers.config.php';
    }

    public function getViewHelperConfig() 
    {  
        return include __DIR__ . '/config/viewhelpers.config.php';
    }

    public function getControllerPluginConfig()
    {
        return include __DIR__ . '/config/controllers_plugin.config.php';
    }

}