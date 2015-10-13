<?php
/**
 * @author Bruno Spyckerelle
 *
 */
namespace Core\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;

class Notifications extends AbstractHelper {
	
	/**
	 * 
	 * @param  $messages
	 * @return string
	 */
	public function __invoke($messages){
		$return = "";
		if($messages !== null) {
			if (isset($messages['success'])) {
				foreach ($messages['success'] as $message) {
					$return .= "var n = noty({text:".json_encode($message).","
							. "type:'success',"
							. "layout:'bottomRight'});";
				}
			}
			if (isset($messages['error'])) {
				foreach ($messages['error'] as $message) {
					$return .= "var n = noty({text:".json_encode($message).","
							. "type:'error',"
							. "layout:'bottomRight'});";
				}
			}
		}
		return $return;		
	}
	
}