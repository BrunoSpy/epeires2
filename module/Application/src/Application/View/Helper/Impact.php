<?php
/**
 * Bootstrap accordion group helper
 * @author Bruno Spyckerelle
 *
 */
namespace Application\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;

class Impact extends AbstractHelper {
	
	public function __invoke($impact){

		if($impact){
			return '<span class="label label-'.$impact->getStyle().'">'.$impact->getName().'</span>';
		} else {
			return "";
		}
		
	}
	
}