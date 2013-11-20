<?php
namespace Core\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LoggableListenerFactory implements FactoryInterface{
		
	public function createService(ServiceLocatorInterface $serviceLocator){
		$loggablelistener = new \Core\Listener\LoggableListener();
		$loggablelistener->setServiceManager($serviceLocator);
		return $loggablelistener;
	}
	
}