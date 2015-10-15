<?php
namespace Application\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use Doctrine\Common\Collections\Criteria;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
	
/**
 * Fieldset for custom fields
 * @author Bruno Spyckerelle
 *
 */
class CustomFieldset extends Fieldset implements InputFilterProviderInterface, ServiceManagerAwareInterface {
	
	private $names; 
	
	private $sm;
	
	public function setServiceManager(ServiceManager $serviceManager){
		$this->sm = $serviceManager;
	}
	
	public function __construct(ServiceManager $sm, $categoryid, $model = false){
		
		parent::__construct('custom_fields');
		
		$this->setServiceManager($sm);
		$om = $sm->get('Doctrine\ORM\EntityManager');
		
		$this->names = array();
		
		
		
		$category = $om->getRepository('Application\Entity\Category')->find($categoryid);
		$customfields = $om->getRepository('Application\Entity\CustomField')
						->matching(Criteria::create()
									->where(Criteria::expr()->eq('category', $category))
									->orderBy(array("place" => Criteria::ASC))
								);
				
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
			$definition['name'] = $customfield->getId();
			$this->names[] = $customfield->getId();
			$options = array('label' => $customfield->getName()." :");
			
			$customfieldservice = $sm->get('CustomFieldService');
			
			$value_options = $customfieldservice->getFormValueOptions($customfield);
			if($value_options){
				$options['value_options'] = $value_options;
			}
                        $empty_option = $customfieldservice->getEmptyOption($customfield);
                        if($empty_option){
                            $options['empty_option'] = $empty_option;
                        }
                        
			$definition['type'] = $customfieldservice->getZendType($customfield->getType());
					
                        foreach($customfieldservice->getFormAttributes($customfield) as $key => $attribute){
                            $definition['attributes'][$key] = $attribute;
                        }
                        
			$definition['options'] = $options;
			
			if(!$model && $customfield->getId() == $category->getFieldname()->getId()){
				$definition['attributes']['required'] = 'required';
                                $definition['attributes']['maxlength'] = '48';
			}
            $definition['attributes']['title'] = $customfield->getTooltip();
                                                
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