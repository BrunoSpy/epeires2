<?php
namespace Application\Factories;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EventNameFactory implements FactoryInterface{
		
	public function createService(ServiceLocatorInterface $serviceLocator){
    	$eventnameHelper = new \Application\View\Helper\EventNameHelper();
    	$eventnameHelper->setEventService($serviceLocator->getServiceLocator()->get('eventservice'));
    	return $eventnameHelper;
	}
	
}