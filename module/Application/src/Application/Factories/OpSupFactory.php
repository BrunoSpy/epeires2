<?php
namespace Application\Factories;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class OpSupFactory implements FactoryInterface{
		
	public function createService(ServiceLocatorInterface $serviceLocator){
    	$opsupHelper = new \Application\View\Helper\OpSupHelper();
    	$opsupHelper->setServiceManager($serviceLocator->getServiceLocator());
    	return $opsupHelper;
	}
	
}