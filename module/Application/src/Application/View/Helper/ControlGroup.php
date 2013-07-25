<?php
/**
 * Bootstrap accordion group helper
 * @author Bruno Spyckerelle
 *
 */
namespace Application\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;

class ControlGroup extends AbstractHelper {
	
	public function __invoke($label, $control, $options = array()){

		$control_id = (isset($options['control_id'])) ? "id=\"".$options['control_id']."\"" : "" ;
		
		$result = "<div class=\"control-group\">";
		$result .= $label;
		$result .= "<div class=\"controls\" ".$control_id.">";
		$result .= $control;
		$result .= "</div></div>";		
		
		return $result;
		
	}
	
}