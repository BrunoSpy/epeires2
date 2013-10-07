<?php
/**
 * @author Bruno Spyckerelle
 *
 */
namespace Application\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceManagerAwareInterface;

class EventFieldName extends AbstractHelper{
	
	
	public function __invoke($eventfield){

		switch ($eventfield) {
			case 'end_date':
				return 'Date de fin';
			case 'start_date':
				return 'Date de début';
			case 'punctual':
				return 'Ponctuel';
			case 'impact':
				return 'Impact';
			case 'status':
				return 'Statut';
			default:
				return $eventfield;
			break;
		}
		
	}
	
	
}