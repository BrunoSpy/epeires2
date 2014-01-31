<?php
namespace Application\Services;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;

class CustomFieldService implements ServiceManagerAwareInterface {
	/**
	 * Service Manager
	 */
	protected $sm;
	
	/**
	 * Entity Manager
	 */
	private $em;
	
	public function setEntityManager(\Doctrine\ORM\EntityManager $em){
		$this->em = $em;
	}
	
	public function setServiceManager(ServiceManager $serviceManager){
		$this->sm = $serviceManager;
	}
	
	/**
	 * Get the name of a customfield, depends on the customfield type
	 * @param $customfield
	 */
	public function getFormattedValue(\Application\Entity\CustomField $customfield, $fieldvalue){	
		$name = null;
		switch ($customfield->getType()->getType()) {
			case 'string':
				$name = $fieldvalue;
				break;
			case 'text':
				$name = $fieldvalue;
				break;
			case 'sector':
				$sector = $this->em->getRepository('Application\Entity\Sector')->find($fieldvalue);
				if($sector){
					$name = $sector->getName();
				}
				break;
			case 'antenna':
				$antenna = $this->em->getRepository('Application\Entity\Antenna')->find($fieldvalue);
				if($antenna){
					$name = $antenna->getName();
				}
				break;
			case 'frequency':
				$frequency = $this->em->getRepository('Application\Entity\Frequency')->find($fieldvalue);
				if($frequency){
					$name = $frequency->getName();
				}
				break;
			case 'radar':
				$radar = $this->em->getRepository('Application\Entity\Radar')->find($fieldvalue);
				if($radar){
					$name = $radar->getName();
				}
				break;
			case 'select':
				$defaultvalue = $customfield->getDefaultValue();
				if($defaultvalue && $fieldvalue) {
					$values = explode(PHP_EOL, $defaultvalue);
					if(count($values) >= $fieldvalue){
						$name = $values[$fieldvalue];
					}
				}
				break;
			case 'stack':
				$stack = $this->em->getRepository('Application\Entity\Stack')->find($fieldvalue);
				if($stack){
					$name = $stack->getName();
				}
				break;
			case 'boolean':
				$name = ($fieldvalue ? "Vrai" : "Faux");
				break;
			default:
				;
				break;
		}
		
		return $name;
	}
	
	/**
	 * Returns the corresponding Zend Form Type
	 * @param \Application\Entity\CustomFieldType $customfieldtype
	 * @return Ambigous <NULL, string>
	 */
	public function getZendType(\Application\Entity\CustomFieldType $customfieldtype){
		$type = null;
		switch ($customfieldtype->getType()) {
			case 'string':
				$type = 'Zend\Form\Element\Text';
				break;
			case 'text':
				$type = 'Zend\Form\Element\Textarea';
				break;
			case 'frequency':
			case 'sector':
			case 'antenna':
			case 'select':
			case 'stack':
			case 'radar':
				$type = 'Zend\Form\Element\Select';
				break;
			case 'boolean':
				$type = 'Zend\Form\Element\Checkbox';
				break;
			default:
				;
				break;
		}
		return $type;
	}
	
	/**
	 * 
	 * @param \Application\Entity\CustomFieldType $customfieldtype
	 * @return array
	 */
	public function getFormValueOptions(\Application\Entity\CustomField $customfield){
		$value_options = null;
		$om = $this->sm->get('Doctrine\ORM\EntityManager');
		switch ($customfield->getType()->getType()) {
			case 'string':
				break;
			case 'text':
				break;
			case 'sector':
				$value_options = $om->getRepository('Application\Entity\Sector')->getAllAsArray();
				break;
			case 'antenna':
				$value_options = $om->getRepository('Application\Entity\Antenna')->getAllAsArray();
				break;
			case 'frequency':
				$value_options = $om->getRepository('Application\Entity\Frequency')->getAllAsArray();
				break;
			case 'radar':
				$value_options = $om->getRepository('Application\Entity\Radar')->getAllAsArray();
				break;
			case 'select':
				$value_options = explode(PHP_EOL, $customfield->getDefaultValue());
				break;
			case 'stack':
				$value_options = $om->getRepository('Application\Entity\Stack')->getAllAsArray();
				break;
			case 'boolean':
				break;
			default:
				;
				break;
		}
		return $value_options;
	}
	
}