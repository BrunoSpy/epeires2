<?php
namespace Application\Factories;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class IPOFactory implements FactoryInterface{
		
	public function createService(ServiceLocatorInterface $serviceLocator){
    	$ipoHelper = new \Application\View\Helper\IPOHelper();
    	$ipoHelper->setServiceManager($serviceLocator->getServiceLocator());
    	return $ipoHelper;
	}
	
}