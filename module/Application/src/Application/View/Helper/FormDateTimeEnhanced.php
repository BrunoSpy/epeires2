<?php
/**
 * Bootstrap accordion group helper
 * @author Bruno Spyckerelle
 *
 */
namespace Application\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;

class FormDateTimeEnhanced extends AbstractHelper {
	
	public function __invoke($id = null){

		$html = '<div class="timepicker-form"'.($id ? 'id="'.$id.'"' : '').'>'.
				'<table class="table">'.
				'<tbody>'.
				'<tr>'.
				'<td class="day">'.
				"<div class=\"input-prepend\">".
				"<span class=\"add-on\"><i class=\"icon-calendar\"></i></span>".
				'<input type="date"></input>'.
				"</div>".
				'</td>'.
				'<td class="hour">'.
				'<a class="next" href="#"><i class="icon-chevron-up"></i></a><br>'.
				'<input type="text" class="input-mini"><br>'.
				'<a class="previous" href="#"><i class="icon-chevron-down"></i></a>'.
				'</td>'.
				'<td class="separator">:</td>'.
				'<td class="minute">'.
				'<a class="next" href="#"><i class="icon-chevron-up"></i></a><br>'.
				'<input type="text" class="input-mini"><br>'.
				'<a class="previous" href="#"><i class="icon-chevron-down"></i></a>'.
				'</td>'.
				'</tr>'.
				'</tbody>'.
				'</table>'.
				'</div>';
	
		
		return $html;
		
	}
	
}