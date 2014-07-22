<?php
namespace Application\Factories;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UpdateAuthorFactory implements FactoryInterface{
		
	public function createService(ServiceLocatorInterface $serviceLocator){
    	$updateauthorHelper = new \Application\View\Helper\UpdateAuthorHelper();
    	$updateauthorHelper->setEventService($serviceLocator->getServiceLocator()->get('eventservice'));
    	return $updateauthorHelper;
	}
	
}