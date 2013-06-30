<?php
/**
 * Epeires 2
 *
 * @copyright Copyright (c) 2013 Bruno Spyckerelle
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Application\Form;

use Zend\Form\Form;

class EventForm extends Form {
	
	public function __construct(){
		parent::__construct('event');
		
		$this->setAttribute('action', '/create');
		$this->setAttribute('method', 'post');
		
		$this->add(array(
				'name' => 'name',
				'attributes' => array(
					'type' => 'text',
					'id' => 'name'
				),
				'options' => array(
					'label' => 'Event name'
				)
		));
		
		$this->add(array(
				'name' => 'submit',
				'attributes' => array(
					'type' => 'submit',
					'value' => 'Add'
				)
		));
	}
	
}