<?php
/**
 * Epeires 2
 *
 *
 * @copyright Copyright (c) 2013 Bruno Spyckerelle
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;
/**
 * @ORM\Entity @ORM\Table(name="predefinedcustomfieldvalues")
 **/
class PredefinedCustomFieldValue {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 * @Annotation\Type("Zend\Form\Element\Hidden")
	 */
	protected $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="PredefinedEvent", inversedBy="custom_fields_values")
	 */
	protected $predefinedevent;
	
	/**
	 * @ORM\ManyToOne(targetEntity="CustomField", inversedBy="predefinedvalues")
	 */
	protected $customfield;
	
	/** 
	 * @ORM\Column(type="string")
 	 * @Annotation\Type("Zend\Form\Element\Text")
	 * @Annotation\Required({"required":"true"})
	 * @Annotation\Options({"label":"Valeur :"})
	 */
	protected $value;

	public function setPredefinedEvent($predefinedevent){
		$this->predefinedevent = $predefinedevent;
	}
	
	public function setCustomField($customfield){
		$this->customfield = $customfield;
	}
	
	public function getCustomField(){
		return $this->customfield;
	}
	
	public function setValue($value){
		$this->value = $value;
	}
	
	public function getValue(){
		return $this->value;
	}
}