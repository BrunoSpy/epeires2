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
namespace Application\Controller\Factory;

use Application\Controller\FlightPlansController;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Session\SessionManager;

class FlightPlansControllerFactory  implements FactoryInterface 
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new FlightPlansController(
            $container->get('Doctrine\ORM\EntityManager'),
            $container->get('eventservice'),
            $container->get('customfieldservice'),
            $container->get('LmcRbacMvc\Options\ModuleOptions'),
            $container->get('config'),
            $container->get('mattermostservice'),
            $container->get('translator'),
            $container->get('mapd'),
            $container->get('EpeiresLogger'),
            $container->get('emailservice')
        );
    }
}