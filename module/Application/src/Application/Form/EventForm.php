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
	
	public function __construct($statusList = array(), $impact = array()){
		parent::__construct('eventform');
		
		$this->setAttribute('method', 'post');
		
		$this->add(array(
				'name' => 'id',
				'attributes' => array(
						'type' => 'hidden',
						'id' => 'id'
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
				'name' => 'impact',
				'type' => 'Zend\Form\Element\Select',
				'options' => array('label' => 'Impact', 'value_options' => $impact),
				'attributes' => array(
						'class' => 'impact'
				)
		));
		
		$this->add(array(
				'name' => 'start_date',
				'type' => 'Zend\Form\Element\DateTime',
				'options' => array(
					'label' => 'Début',
					'format' => 'd-m-Y H:i'
				),
				'attributes' => array(
					'class' => 'datetime',
					'id' => 'dateDeb'
				),			
		));
		
		$this->add(array(
				'name' => 'end_date',
				'type' => 'Zend\Form\Element\DateTime',
				'options' => array(
						'label' => 'Fin',
						'format' => 'd-m-Y H:i'
				),
				'attributes' => array(
						'class' => 'datetime',
						'id' => 'dateFin'
				),
		));
		
		$this->add(array(
				'name' => 'submit',
				'attributes' => array(
					'type' => 'submit',
					'value' => 'Ajouter',
					'class' => 'btn btn-primary'
				)
		));
	}
	
}