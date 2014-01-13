<?php
namespace Core\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserMenuFactory implements FactoryInterface{
		
	public function createService(ServiceLocatorInterface $serviceLocator){
    	$usermenu = new \Core\View\Helper\UserMenu();
    	$usermenu->setAuthService($serviceLocator->getServiceLocator()->get('zfcuser_auth_service'));
    	$usermenu->setServiceManager($serviceLocator->getServiceLocator());
    	return $usermenu;
	}
	
}