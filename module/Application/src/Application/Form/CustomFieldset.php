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
			$options = array('label' => $customfield->getName());
			switch ($customfield->getType()->getType()) {
				case 'string':
					$definition['type'] = 'Zend\Form\Element\Text';
					break;
				case 'text':
					$definition['type'] = 'Zend\Form\Element\Textarea';
					break;
				case 'sector':
					$definition['type'] = 'Zend\Form\Element\Select';
					$options['value_options'] = $om->getRepository('Application\Entity\Sector')->getAllAsArray();
				break;
				case 'antenna':
					$definition['type'] = 'Zend\Form\Element\Select';
					$options['value_options'] = $om->getRepository('Application\Entity\Antenna')->getAllAsArray();					
				break;
				default:
					;
				break;
			}
			$definition['options'] = $options;
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