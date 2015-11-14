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
namespace Core;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\EventManager\EventInterface;
use Core\Controller\UserController;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{

    public function onBootstrap(EventInterface $e)
    {
        $t = $e->getTarget();
        
        $t->getEventManager()->attach($t->getServiceManager()
            ->get('ZfcRbac\View\Strategy\UnauthorizedStrategy'));
        
        $events = $e->getApplication()->getEventManager()->getSharedManager();
        $events->attach('ZfcUser\Form\Login','init', function($e) {
            $form = $e->getTarget();
            $form->get('identity')->setLabel("Nom d'utilisateur");
            $form->get('credential')->setLabel("Mot de passe");
            $form->get('submit')->setLabel("Se connecter");
        });
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
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                )
            )
        );
    }

    public function getControllerConfig()
    {
        return array(
            'factories' => array(
                'coreuser' => function ($controllerManager) {
                    /* @var ControllerManager $controllerManager*/
                    $serviceManager = $controllerManager->getServiceLocator();
                    
                    /* @var RedirectCallback $redirectCallback */
                    $redirectCallback = $serviceManager->get('zfcuser_redirect_callback');
                    
                    /* @var UserController $controller */
                    $controller = new UserController($redirectCallback);
                    
                    return $controller;
                }
            )
        );
    }
}
