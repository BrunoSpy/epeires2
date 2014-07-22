<?php
/**
 * @author Bruno Spyckerelle
 *
 */
namespace Application\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;

class UpdateAuthorHelper extends AbstractHelper {
	
	private $eventservice;
	
	public function __invoke(\Application\Entity\EventUpdate $eventupdate){
		
		return $this->eventservice->getUpdateAuthor($eventupdate);
		
	}
	
    public function setEventService($service){
    	$this->eventservice = $service;
    }
	
}