<?php
namespace Application\Factories;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EventServiceFactory implements FactoryInterface{
		
	public function createService(ServiceLocatorInterface $serviceLocator){
		$eventservice = new \Application\Services\EventService();
		$eventservice->setEntityManager($serviceLocator->get('Doctrine\ORM\EntityManager'));
		return $eventservice;
	}
	
}