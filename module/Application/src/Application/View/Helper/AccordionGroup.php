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
                $titleid = (isset($options['title_id'])) ? $options['title_id'] : preg_replace('/\s+/', '', $title)."id";
		$bodyid = (isset($options['body-id'])) ? $options['body-id'] : "accordion-".$titleid;
		$innerbody = (isset($options['inner-id'])) ? $options['inner-id'] : "inner-".$titleid;
		$in = (isset($options['in']) && $options['in']) ? "in" : "";
		$titleclass = (isset($options['title_class'])) ? $options['title_class'] : "";
		
		$result = "<div class=\"accordion-group\">";
		$result .= "<div class=\"accordion-heading\">";
		$result .= "<a class=\"accordion-toggle ".$titleclass."\" data-toggle=\"collapse\" data-parent=\"".$dataparent."\" href=\"#".$bodyid."\" id=\"$titleid\">";
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