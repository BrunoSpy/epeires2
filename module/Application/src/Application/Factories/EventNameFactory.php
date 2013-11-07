<?php
namespace Application\Factories;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EventNameFactory implements FactoryInterface{
		
	public function createService(ServiceLocatorInterface $serviceLocator){
    	$eventnameHelper = new \Application\View\Helper\EventNameHelper();
    	$eventnameHelper->setServiceManager($serviceLocator);
    	return $eventnameHelper;
	}
	
}