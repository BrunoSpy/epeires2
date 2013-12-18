<?php
namespace Application\Factories;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CategoryEntityFactoryFactory implements FactoryInterface{
		
	public function createService(ServiceLocatorInterface $serviceLocator){
		$catfactory = new \Application\Factories\CategoryEntityFactory();
        $catfactory->setServiceLocator($serviceLocator);
        return $catfactory;
	}
	
}