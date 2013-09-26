<?php
/**
 * @author Bruno Spyckerelle
 *
 */
namespace Application\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class EventNameHelper extends AbstractHelper implements ServiceManagerAwareInterface {
	
	private $servicemanager;
	
	public function __invoke($event){

		return $this->servicemanager->get('EventService')->getName($event);
		
	}
	
	public function setServiceManager(\Zend\ServiceManager\ServiceManager $serviceLocator){
		$this->servicemanager = $serviceLocator;
	}
	
}