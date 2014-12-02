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
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="customfieldvalues")
 * @Gedmo\Loggable(logEntryClass="Application\Entity\Log")
 **/
class CustomFieldValue {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="AbstractEvent", inversedBy="custom_fields_values")
 	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $event;
	
	/**
	 * @ORM\ManyToOne(targetEntity="CustomField", inversedBy="values")
	 */
	protected $customfield;
	
	/** 
	 * @ORM\Column(type="string")
	 * @Gedmo\Versioned
	 */
	protected $value;

	public function getId(){
		return $this->id;
	}
	
	public function setEvent($event){
		$this->event = $event;
	}
	
	public function getCustomField(){
		return $this->customfield;
	}
	
	public function setCustomField($customfield){
		$this->customfield = $customfield;
	}
	
	public function setValue($value){
		$this->value = $value;
	}
	
	public function getValue(){
		return $this->value;
	}
}