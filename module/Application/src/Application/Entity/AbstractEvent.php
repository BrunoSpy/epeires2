<?php
/**
 * Epeires 2
 *
 * @copyright Copyright (c) 2013 Bruno Spyckerelle
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Zend\Form\Annotation;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity(repositoryClass="Application\Repository\ExtendedRepository")
 * @ORM\Table(name="events")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"generic" = "Event", "model" = "PredefinedEvent"})
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable(logEntryClass="Application\Entity\Log")
 **/
class AbstractEvent {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 * @Annotation\Type("Zend\Form\Element\Hidden")
	 */
	protected $id;

	/** 
	 * @ORM\Column(type="boolean")
	 * @Annotation\Type("Zend\Form\Element\Checkbox")
	 * @Annotation\Options({"label":"Ponctuel :"})
	 * @Annotation\Attributes({"id":"punctual"})
	 * @Gedmo\Versioned
	 */
	protected $punctual;
	
 	/** 
 	 * @ORM\ManyToOne(targetEntity="AbstractEvent", inversedBy="children")
 	 * @Annotation\Type("Zend\Form\Element\Select")
	 * @Annotation\Required(false)
	 * @Annotation\Options({"label":"Evènement parent :", "empty_option":"Choisir l'evt parent"})
 	 */
 	protected $parent;
	
 	/** @ORM\OneToMany(targetEntity="AbstractEvent", mappedBy="parent", cascade={"remove"}) */
 	protected $children;
 	
 	/** 
 	 * @ORM\ManyToOne(targetEntity="Impact")
  	 * @Annotation\Type("Zend\Form\Element\Select")
	 * @Annotation\Required(true)
	 * @Annotation\Options({"label":"Impact :"})
 	 * @Gedmo\Versioned
 	 */
 	protected $impact;
 	
 	/** 
 	 * @ORM\ManyToOne(targetEntity="Category", inversedBy="events")
 	 * @ORM\JoinColumn(nullable=false)
 	 * @Annotation\Type("Zend\Form\Element\Select")
 	 * @Annotation\Required(true)
 	 * @Annotation\Options({"label":"Catégorie :", "empty_option":"Choisir la catégorie"})
 	 */
 	protected $category;
 	
 	/**
 	 * @ORM\OneToMany(targetEntity="CustomFieldValue", mappedBy="event", cascade={"remove"})
 	 */
 	protected $custom_fields_values;
 	
 	/** 
 	 * @ORM\ManyToOne(targetEntity="Organisation")
 	 * @ORM\JoinColumn(nullable=false)
 	 * @Annotation\Type("Zend\Form\Element\Select")
 	 * @Annotation\Required(true)
 	 * @Annotation\Options({"label":"Organisation :"})
 	 */
 	protected $organisation;
 	
 	/** 
 	 * @ORM\ManyToMany(targetEntity="QualificationZone")
 	 * @ORM\JoinTable(name="events_qualificationzones")
 	 * @Annotation\Type("Zend\Form\Element\Select")
 	 * @Annotation\Required(false)
 	 * @Annotation\Attributes({"multiple":true})
 	 * @Annotation\Options({"label":"Visibilité :"})
 	 */
 	protected $zonefilters;
 	
 	public function __construct(){
 		$this->children = new \Doctrine\Common\Collections\ArrayCollection();
 		$this->zonefilters = new \Doctrine\Common\Collections\ArrayCollection();
 		$this->custom_fields_values = new \Doctrine\Common\Collections\ArrayCollection();
 	}
 	
 	public function getOrganisation(){
 		return $this->organisation;
 	}
 	
 	public function setOrganisation($organisation){
 		$this->organisation = $organisation;
 	}
 	
 	public function setZonefilters($zonefilters){
 		$this->zonefilters = $zonefilters;
 	}
 	
 	public function getZonefilters(){
 		return $this->zonefilters;
 	}
 	
 	public function addZonefilters(Collection $zonefilters){
 		foreach ($zonefilters as $zonefilter){
 			$this->zonefilters->add($zonefilter);
 		}
 	}
 	
 	public function removeZonefilters(Collection $zonefilters){
 		foreach ($zonefilters as $zonefilter){
 			$this->zonefilters->removeElement($zonefilter);
 		}
 	}
 	
 	public function getCategory(){
 		return $this->category;
 	}
 	
 	public function setCategory($category){
 		$this->category = $category;
 	}
 	
 	public function getCustomFieldsValues(){
 		return $this->custom_fields_values;
 	}
 	
 	public function addCustomFieldValue($customfieldvalue){
 		$this->custom_fields_values->add($customfieldvalue);
 	}
 	
 	public function getId(){
 		return $this->id;
 	}
 	 	
	public function isPunctual() {
		return $this->punctual;
	}
	
	public function setPunctual($punctual){
		$this->punctual = $punctual;
	}
	
	public function setImpact($impact){
		$this->impact = $impact;
	}
	
	public function getImpact(){
		return $this->impact;
	}
	
	public function setParent($parent){
		$this->parent = $parent;
	}
	
	public function getParent(){
		return $this->parent;
	}
	
	public function getChildren(){
		return $this->children;
	}
	
	
	public function getArrayCopy() {
		$object_vars = get_object_vars($this);
		$object_vars['category'] = ($this->category ? $this->category->getId() : null);
		$object_vars['impact'] = ($this->impact ? $this->impact->getId() : null);
		$object_vars['organisation'] = ($this->organisation ? $this->organisation->getId() : null);
		$zonefilters = array();
		foreach ($this->zonefilters as $zonefilter){
			$zonefilters[] = $zonefilter->getId();
		}
		$object_vars['zonefilters'] = $zonefilters;
		return $object_vars;
	}
}