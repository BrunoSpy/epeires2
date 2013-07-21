<?php
/**
 * Bootstrap accordion group helper
 * @author Bruno Spyckerelle
 *
 */
namespace Application\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;

class AccordionGroup extends AbstractHelper {
	
	public function __invoke($title, $content, $options = array()){
		
		
		$dataparent = (isset($options['data-parent'])) ? "#".$options['data-parent'] : "#parent";
		$bodyid = (isset($options['body-id'])) ? $options['body-id'] : "accordion-".preg_replace('/\s+/', '', $title);
		$innerbody = (isset($options['inner-id'])) ? $options['inner-id'] : "inner-".preg_replace('/\s+/', '', $title);
		$in = (isset($options['in']) && $options['in']) ? "in" : "";
		
		$result = "<div class=\"accordion-group\">";
		$result .= "<div class=\"accordion-heading\">";
		$result .= "<a class=\"accordion-toggle\" data-toggle=\"collapse\" data-parent=\"".$dataparent."\" href=\"#".$bodyid."\">";
		$result .= $title;
		$result .= "</a>";
		$result .= "</div>";
		$result .= "<div class=\"accordion-body collapse ".$in."\" id=\"".$bodyid."\">";
		$result .= "<div class=\"accordion-inner\" id=\"".$innerbody."\">";
		$result .= $content;
		$result .= "</div>";
		$result .= "</div>";
		$result .= "</div>";
		
		return $result;
		
	}
	
}