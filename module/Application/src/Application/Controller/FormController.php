<?php

/**
 * Epeires 2
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractController;

/**
 * 
 * @author Bruno Spyckerelle
 *
 */
abstract class FormController extends AbstractActionController {
	
	protected function processFormMessages($messages, &$json = null){
		foreach($messages as $key => $message){
			foreach($message as $mkey => $mvalue){//les messages sont de la forme 'type_message' => 'message'
				if(is_array($mvalue)){
					foreach ($mvalue as $nkey => $nvalue){//les fieldsets sont un niveau en dessous
						if($json){
							$n = isset($json['error']) ? count($json['error']) : 0;
							$json['error'][$n] = "Champ ".$mkey." incorrect : ".$nvalue;
						} else {
							$this->flashMessenger()->addErrorMessage(
									"Champ ".$mkey." incorrect : ".$nvalue);
						}
					}
				} else {
					if($json){
						$n = isset($json['error']) ? count($json['error']) : 0;
						$json['error'][$n] = "Champ ".$key." incorrect : ".$mvalue;
					} else {
						$this->flashMessenger()->addErrorMessage(
								"Champ ".$key." incorrect : ".$mvalue);
					}
				}
			}
		}
	}
	
}