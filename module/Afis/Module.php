<?php

namespace Afis;

use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
use Zend\ModuleManager\Feature\RouteProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\ModuleManager\Feature\ControllerPluginProviderInterface;
//use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ControllerProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\ModuleManager\Feature\FormElementProviderInterface;

use Zend\Mvc\ModuleRouteListener;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\MvcEvent;

use Zend\EventManager\EventInterface;
use Zend\Mvc\Router\Console\Simple;
use Zend\Mvc\Router\Http\Literal;
use Afis\Controller\AfisController;

class Module implements ConfigProviderInterface, 
                        AutoloaderProviderInterface,
                        ServiceProviderInterface,
                        ControllerProviderInterface,
                        ControllerPluginProviderInterface,
                        ConsoleUsageProviderInterface,
                        ViewHelperProviderInterface,
                        FormElementProviderInterface

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

    public function getServiceConfig() {
        return include __DIR__ . '/config/services.config.php';
    }

    public function getControllerConfig()
    {
        return include __DIR__ . '/config/controllers.config.php';
    }
    
    public function getConsoleUsage(Console $console)
    {
        return [
            'list' => 'tous les AFIS',
            'get' => 'GET GET GET'
        ];
    }

    public function getViewHelperConfig() 
    {  
        return include __DIR__ . '/config/viewhelpers.config.php';
    }

    public function getControllerPluginConfig() {
        return include __DIR__ . '/config/controllers_plugin.config.php';
    }

    public function getFormElementConfig() {
        return include __DIR__ . '/config/forms.config.php';
    }

}