<?php
/**
 * @author Bruno Spyckerelle
 *
 */
namespace Application\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EventNameHelper extends AbstractHelper {
	
	private $eventservice;
	
	public function __invoke($event){
		
		return $this->eventservice->getName($event);
		
	}
	
    public function setEventService($service){
    	$this->eventservice = $service;
    }
	
}