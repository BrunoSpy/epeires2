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

use Laminas\ModuleManager\Feature\AutoloaderProviderInterface;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\EventManager\EventInterface;
use Core\Controller\UserController;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class Module implements ConfigProviderInterface
{

    public function onBootstrap(EventInterface $e)
    {

        $app = $e->getApplication();
        $sm = $app->getServiceManager();

        $e->getTarget()->getEventManager()->attach(
            $e::EVENT_DISPATCH_ERROR,
            function($e) use ($sm) {
                return $sm->get('LmcRbacMvc\View\Strategy\UnauthorizedStrategy')->onError($e);
            }
        );

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
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getControllerConfig()
    {
        return array(
            'factories' => array(
                'coreuser' => function ($container) {

                    
                    /* @var RedirectCallback $redirectCallback */
                    $redirectCallback = $container->get('zfcuser_redirect_callback');

                    /* @var UserController $controller */
                    $controller = new UserController($redirectCallback);
                    $controller->setServiceLocator($container);
                    
                    return $controller;
                }
            )
        );
    }
}
