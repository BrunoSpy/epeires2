<?php
/** Epeires 2
*
* @copyright Copyright (c) 2013 Bruno Spyckerelle
* @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
*/
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Application\Repository\ExtendedRepository")
 * @ORM\Table(name="opsups")
 * @Gedmo\Loggable(logEntryClass="Application\Entity\Log")
 **/
class OperationalSupervisor {
	/** 
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 * @Annotation\Type("Zend\Form\Element\Hidden")
	 */
	protected $id;
	
	/** 
	 * @ORM\Column(type="string")
	 * @Annotation\Type("Zend\Form\Element\Text")
        * @Annotation\Required({"required":"true"})
        * @Annotation\Options({"label":"Nom :"})
        */
	protected $name;
	
 	/** 
 	 * @ORM\ManyToOne(targetEntity="Organisation")
	 * @Annotation\Type("Zend\Form\Element\Select")
	 * @Annotation\Required({"required":"true"})
	 * @Annotation\Options({"label":"Organisation :", "empty_option":"Choisir l'organisation"})
 	 */
	protected $organisation;
	

	/** 
	 * @ORM\ManyToOne(targetEntity="QualificationZone")
	 * @Annotation\Type("Zend\Form\Element\Select")
	 * @Annotation\Required(true)
	 * @Annotation\Options({"label":"Zone de qualification :", "empty_option":"Choisir la zone de qualification"})
	 */
	protected $zone;
	
	/** 
	 * @ORM\Column(type="boolean")
	 * @Gedmo\Versioned
	 */
	protected $current = false;
	
	public function getId(){
		return $this->id;
	}
	
	public function isCurrent(){
		return $this->current;
	}
	
	public function setCurrent($current) {
		$this->current = $current;
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function setName($name){
		$this->name = $name;
	}
	
	public function getZone(){
		return $this->zone;
	}
	
	public function setZone($zone){
		$this->zone = $zone;
	}
	
	public function getOrganisation(){
		return $this->organisation;
	}
	
	public function setOrganisation(Organisation $organisation){
		$this->organisation = $organisation;
	}
	
	public function getArrayCopy(){
		$object_vars = get_object_vars($this);
		$object_vars['organisation'] = $this->organisation->getId();
		$object_vars['zone'] = $this->zone->getId();
		return $object_vars;
	}
}