<?php

namespace Core\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class NMB2BServiceFactory implements FactoryInterface {
    
    public function createService(ServiceLocatorInterface $serviceLocator) {
        return new \Core\Service\NMB2BService($serviceLocator);
    }
}

