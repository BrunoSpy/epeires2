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
namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\Config\SessionConfig;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\ModuleManager\ModuleManager;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;

/**
 *
 * @author Bruno Spyckerelle
 *
 */
class Module implements ConsoleUsageProviderInterface
{

    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        $this->bootstrapSession($e);
    }

    public function bootstrapSession($e)
    {
        $session = $e->getApplication()
            ->getServiceManager()
            ->get(SessionManager::class);

    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Core\Service\NOTAMService' => function($sm) {
                        return new Core\Service\NOTAMService();
                }
            )
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function init(ModuleManager $moduleManager)
    {
        $sharedEvents = $moduleManager->getEventManager()->getSharedManager();
        $sharedEvents->attach(__NAMESPACE__, 'dispatch', function ($e) {
            $controller = $e->getTarget();
            $controller->layout('layout/app-layout');
        }, 100);
    }

    public function getConsoleUsage(Console $console)
    {
        return array(
            'report [--email] [--delta=] orgshortname' => 'Generate a daily report for an organisation',
            array(
                '--email',
                '(optional) Send an email to IPO'
            ),
            array(
                '--delta',
                '(optional) Delta to add to the current day (-1=yesterday)'
            ),
            array(
                'orgshortname',
                'Shortname of the organisation as configured in the database'
            ),
            'import-nmb2b [--delta=] [--email] [--verbose] orgshortname username' => 'Import RSAs from NM B2B WS',
            array(
                '--delta',
                '(optional) Delta to add to the current day (-1=yesterday)'
            ),
            array(
                '--email',
                '(optional) Send an email to IPO if error during import.'
            ),
            array(
                '--verbose',
                '(optional) Print requests and responses.'
            ),
            array(
                'orgshortname',
                'Shortname of the organisation as configured in the database'
            ),
            array(
                'username',
                'User Name of the author of created events'
            ),
            'import-regulations [--delta=] [--email] [--verbose] orgshortname username' => 'Import Regulations from NM B2B WS for the specidifed day and the day after',
            array(
                '--delta',
                '(optional) Delta to add to the current day (-1=yesterday)'
            ),
            array(
                '--email',
                '(optional) Send an email to IPO if error during import.'
            ),
            array(
                '--verbose',
                '(optional) Print requests and responses.'
            ),
            array(
                'orgshortname',
                'Shortname of the organisation as configured in the database'
            ),
            array(
                'username',
                'User Name of the author of created events'
            )
        );
    }
}
