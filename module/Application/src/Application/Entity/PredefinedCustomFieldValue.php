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
/**
 * @ORM\Entity @ORM\Table(name="predefinedcustomfieldvalues")
 **/
class PredefinedCustomFieldValue {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="PredefinedEvent", inversedBy="custom_fields_values")
	 */
	protected $event;
	
	/**
	 * @ORM\ManyToOne(targetEntity="CustomField")
	 */
	protected $customfield;
	
	/** @ORM\Column(type="string") */
	protected $value;

	public function setPredefinedEvent($event){
		$this->event = $event;
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