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
		
                $group_id = (isset($options['group_id'])) ? "id=\"".$options['group_id']."\"" : "" ;
                
		$class = (isset($options['class'])) ? " ".$options['class'] : "";
		
		$result = "<div class=\"control-group".$class."\" ".$group_id.">";
		$result .= $label;
		$result .= "<div class=\"controls\" ".$control_id.">";
		$result .= $control;
		$result .= "</div></div>";		
		
		return $result;
		
	}
	
}