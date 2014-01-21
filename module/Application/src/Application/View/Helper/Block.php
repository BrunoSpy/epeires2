<?php
/**
 * Bootstrap accordion group helper
 * @author Bruno Spyckerelle
 *
 */
namespace Application\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;

class Block extends AbstractHelper {
	
	public function __invoke($title, $body){

		$html = '<div class="block">';
		$html .= '<div class="navbar navbar-inner block-header">';
		$html .= '<div class="muted pull-left">'.$title.'</div>';
		$html .= '</div>';
		$html .= '<div class="block-content collapse in">';
		$html .= '<div class="span12">';
		$html .= $body;
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';	
		
		return $html;
		
	}
	
}