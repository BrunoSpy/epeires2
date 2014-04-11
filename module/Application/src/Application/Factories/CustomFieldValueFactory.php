<?php
namespace Application\Factories;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CustomFieldValueFactory implements FactoryInterface{
		
	public function createService(ServiceLocatorInterface $serviceLocator){
    	$customHelper = new \Application\View\Helper\CustomFieldValue();
    	$customHelper->setServiceManager($serviceLocator->getServiceLocator());
    	return $customHelper;
	}
	
}