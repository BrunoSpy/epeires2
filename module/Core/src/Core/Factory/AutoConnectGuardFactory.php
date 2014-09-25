<?php

namespace Core\Factory;

use Core\Guard\AutoConnectGuard;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\MutableCreationOptionsInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AutoConnectGuardFactory implements FactoryInterface, MutableCreationOptionsInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * {@inheritDoc}
     */
    public function setCreationOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $autoconnectGuard = new AutoConnectGuard($this->options);
        $autoconnectGuard->setAuthService($serviceLocator->getServiceLocator()->get('zfcuser_auth_service'));
        
        return $autoconnectGuard;
    }
}

