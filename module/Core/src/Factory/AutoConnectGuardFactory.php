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
namespace Core\Factory;

use Core\Guard\AutoConnectGuard;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\MutableCreationOptionsInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class AutoConnectGuardFactory implements FactoryInterface, MutableCreationOptionsInterface
{

    /**
     *
     * @var array
     */
    protected $options;

    /**
     *
     * {@inheritDoc}
     *
     */
    public function setCreationOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     *
     * {@inheritDoc}
     *
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $autoconnectGuard = new AutoConnectGuard($this->options);
        $autoconnectGuard->setAuthService($serviceLocator->getServiceLocator()
            ->get('zfcuser_auth_service'));
        
        return $autoconnectGuard;
    }
}
