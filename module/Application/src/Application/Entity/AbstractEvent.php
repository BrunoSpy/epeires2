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
	 * @Annotation\Options({"label":"Impact :", "empty_option":"Choisir l'impact"})
 	 * @Gedmo\Versioned
 	 */
 	protected $impact;
 	
 	/**
 	 * @ORM\ManyToOne(targetEntity="Organisation")
 	 */
 	protected $organisation;
 	
 	/**
 	 * @ORM\ManyToMany(targetEntity="QualificationZone")
 	 */
 	protected $zonefilters;
 	
 	public function __construct(){
 		$this->children = new \Doctrine\Common\Collections\ArrayCollection();
 		$this->zonefilters = new \Doctrine\Common\Collections\ArrayCollection();
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
	
	
	public function getCategory(){
		return $this->category;
	}
	
	public function setCategory($category){
		$this->category = $category;
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
		$object_vars['impact'] = ($this->impact ? $this->impact->getId() : null);
		$object_vars['category'] = ($this->category ? $this->category->getId() : null);
		return $object_vars;
	}
}