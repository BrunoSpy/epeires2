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
	
	public function __construct($statusList){
		parent::__construct('event');
		
		$this->setAttribute('method', 'post');
		
		$this->add(array(
				'name' => 'id',
				'attributes' => array(
						'type' => 'hidden',
						'id' => 'id'
				)
		));
		
		$this->add(array(
				'name' => 'name',
				'attributes' => array(
					'type' => 'text',
					'id' => 'name'
				),
				'options' => array(
					'label' => 'Titre'
				)
		));
		
		$this->add(array(
				'name' => 'punctual',
				'type' => 'Zend\Form\Element\Checkbox',
				'attributes' => array(
						'id' => 'punctual'
				),
				'options' => array(
						'label' => 'Ponctuel'
				)
		));
		
		$this->add(array(
				'name' => 'status',
				'type' => 'Zend\Form\Element\Select',
				'options' => array('label' => 'Etat', 'value_options' => $statusList),
				'attributes' => array(
						'class' => 'status'
				)
		));
		
		$this->add(array(
				'name' => 'submit',
				'attributes' => array(
					'type' => 'submit',
					'value' => 'Ajouter',
						'class' => 'subm'
				)
		));
	
	}
	
}