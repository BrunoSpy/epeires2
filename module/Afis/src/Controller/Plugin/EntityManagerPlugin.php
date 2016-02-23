<?php

namespace Afis\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\ServiceManager\ServiceLocatorInterface as Locator;
use Doctrine\ORM\EntityManager;

class EntityManagerPlugin extends AbstractPlugin
{
    public function __invoke()
    {
        //return $locator->getServiceLocator()->get(EntityManager::class);
    }
}
