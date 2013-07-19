<?php
namespace Application\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
	
class CategoryFormFieldset extends Fieldset implements InputFilterProviderInterface {
	
	public function __construct($array){
		
		parent::__construct('categories');
		
		$this->add(array(
				'name' => 'root_categories',
				'type' => 'Zend\Form\Element\Select',
				'options' => array('label' => 'Catégorie', 'value_options' => $array),
				'attributes' => array(
						'class' => 'categories'
				)
		));
		
	}
	
	public function getInputFilterSpecification(){
		return array(
				'root_categories'=>array('required'=>false)
				
		);
	}
	
}