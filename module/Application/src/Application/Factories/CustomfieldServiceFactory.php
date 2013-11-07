<?php
namespace Application\Factories;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CustomfieldServiceFactory implements FactoryInterface{
		
	public function createService(ServiceLocatorInterface $serviceLocator){
		$customfield = new \Application\Services\CustomFieldService();
        $customfield->setEntityManager($serviceLocator->get('Doctrine\ORM\EntityManager'));
        return $customfield;
	}
	
}