<?php
namespace Application\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Doctrine\Common\Collections\Criteria;
	
/**
 * Fieldset for custom fields
 * Types available :
 * 		- int
 * 		- text
 * 		- boolean
 * 		- 
 * @author Bruno Spyckerelle
 *
 */
class CustomFieldset extends Fieldset implements InputFilterProviderInterface {
	
	private $names; 
	
	public function __construct(\Doctrine\ORM\EntityManager $om, $categoryid){
		
		parent::__construct('custom_fields');
		
		$this->names = array();
		
		$customfields = $om->getRepository('Application\Entity\CustomField')->matching(Criteria::create()->where(Criteria::expr()->eq('category', $categoryid)));
				
		//add category id to regenerate fieldset during creation process
		$this->add(array(
				'name' => 'category_id',
				'type' => '\Zend\Form\Element\Hidden',
				'attributes' => array(
					'value' => $categoryid,
				),
		));
		
		foreach($customfields as $customfield){
			$definition = array();
			$definition['name'] = $customfield->getName();
			$this->names[] = $customfield->getName();
			$definition['attributes'] = array('id' => $customfield->getName());
			switch ($customfield->getType()->getType()) {
				case 'sector':
					$definition['type'] = 'Zend\Form\Element\Select';
					$options = array(
							'label' => $customfield->getName(),
							'value_options' => $om->getRepository('Application\Entity\Sector')->getAllAsArray(),
					);
					$definition['options'] = $options;
				break;
				
				default:
					;
				break;
			}
			$this->add($definition);
		}
		
	}
	
	public function getInputFilterSpecification(){
		$specifications = array();
		foreach ($this->names as $name){
			$specifications[$name] = array('required' => false);
		}
			
		return $specifications;
	}
	
}