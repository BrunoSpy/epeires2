<?php
/**
 * @author Bruno Spyckerelle
 *
 */
namespace Core\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;

class ModalWindow extends AbstractHelper {
	
	/**
	 * 
	 * @param  $id
	 * @param  $header
	 * @param  $headerstyle
	 * @param  $content content div, if not null $body and $footer will be ignored
	 * @param  $body
	 * @param  $footer
	 * @return string
	 */
	public function __invoke($id, $header, $headerstyle, $content, $body = null, $footer = null){

		$html = '<div class="modal hide fade" id="'.$id.'" '.$headerstyle.'>';
		$html .= '<div class="modal-header">';
		$html .= '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
		$html .= $header; 
		$html .= '</div>';
		
		if($content) {
			$html .= $content;
		} else {
			$html .= '<div class="modal-body">';
			$html .= $body;
			$html .= '</div>';
			$html .= '<div class="modal-footer">';
			$html .= $footer;
			$html .= '</div>';
		}
		$html .= '</div>';
		return $html;
		
	}
	
}