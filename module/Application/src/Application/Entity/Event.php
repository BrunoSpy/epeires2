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
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable(logEntryClass="Application\Entity\Log")
 **/
class Event {
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
 	 * @ORM\ManyToOne(targetEntity="Status")
 	 * @Annotation\Type("Zend\Form\Element\Select")
	 * @Annotation\Required(true)
	 * @Annotation\Options({"label":"Statut :"})
 	 * @Gedmo\Versioned
 	 */
 	protected $status;
	
 	/** @ORM\ManyToOne(targetEntity="Event", inversedBy="childs") */
 	protected $parent;
	
 	/** @ORM\OneToMany(targetEntity="Event", mappedBy="parent", cascade={"remove"}) */
 	protected $childs;
 	
 	/** 
 	 * @ORM\ManyToOne(targetEntity="Impact")
  	 * @Annotation\Type("Zend\Form\Element\Select")
	 * @Annotation\Required(true)
	 * @Annotation\Options({"label":"Impact :"})
 	 * @Gedmo\Versioned
 	 */
 	protected $impact;
	
 	/** 
 	 * Actions need an empty start date at creation
 	 * @ORM\Column(type="datetime", nullable=true)
   	 * @Annotation\Type("Zend\Form\Element\DateTime")
	 * @Annotation\Required(false)
	 * @Annotation\Options({"label":"Début :", "format" : "d-m-Y H:i"})
	 * @Annotation\Attributes({"class":"datetime", "id":"dateDeb"})
 	 * @Gedmo\Versioned
 	 */
  	protected $startdate;
	
 	/** 
 	 * @ORM\Column(type="datetime", nullable=true)
     * @Annotation\Type("Zend\Form\Element\DateTime")
	 * @Annotation\Required(false)
	 * @Annotation\Options({"label":"Fin :", "format" : "d-m-Y H:i"})
	 * @Annotation\Attributes({"class":"datetime", "id":"dateFin"})
 	 * @Gedmo\Versioned
 	 */
 	protected $enddate = null;
	
	/** @ORM\Column(type="datetime") */
	protected $created_on;
	
 	/** @ORM\Column(type="datetime") */
 	protected $last_modified_on;
	
 	/** 
 	 * @ORM\ManyToOne(targetEntity="Category", inversedBy="events")
 	 * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=false)
 	 * @Annotation\Required(true)
 	 */
 	protected $category;
	
 	/**
 	 * @ORM\OneToMany(targetEntity="CustomFieldValue", mappedBy="event", cascade={"remove"})
 	 */
 	protected $custom_fields_values;
 	
 	/**
 	 * @ORM\Column(type="boolean")
 	 */
 	protected $star = false;
 	
 	/**
 	 * @ORM\ManyToOne(targetEntity="Core\Entity\User", inversedBy="events")
 	 */
 	protected $author;
 	
 	/**
 	 * @ORM\OneToMany(targetEntity="EventUpdate", mappedBy="event", cascade={"remove"})
 	 */
 	protected $updates;
 	
 	/**
 	 * @ORM\ManyToMany(targetEntity="File", mappedBy="events")
 	 */
 	protected $files;
 	
 	public function __construct(){
 		$this->custom_fields_values = new \Doctrine\Common\Collections\ArrayCollection();
 		$this->childs = new \Doctrine\Common\Collections\ArrayCollection();
 		$this->updates = new \Doctrine\Common\Collections\ArrayCollection();
 		$this->files = new \Doctrine\Common\Collections\ArrayCollection();
 	}
 	
 	public function getId(){
 		return $this->id;
 	}
 	
 	public function getAuthor(){
 		return $this->author;
 	}
 	
 	public function setAuthor($author){
 		$this->author = $author;
 	}
 	
 	public function getUpdates(){
 		return $this->updates;
 	}
 	
 	public function getCustomFieldsValues(){
 		return $this->custom_fields_values;
 	}
 	
 	public function addCustomFieldValue($customfieldvalue){
 		$this->custom_fields_values->add($customfieldvalue);
 	}
 	
	public function isPunctual() {
		return $this->punctual;
	}
	
	public function setPunctual($punctual){
		$this->punctual = $punctual;
	}
	
	public function isStar(){
		return $this->star;
	}
	
	public function setStar($star){
		$this->star = $star;
	}
	
	/** @ORM\PrePersist */
	public function setCreatedOn(){
		$this->created_on = new \DateTime('NOW');
		$this->created_on->setTimeZone(new \DateTimeZone("UTC"));
	}
	
	/** 
	 * @ORM\PreUpdate
	 * @ORM\PrePersist 
	 */
	public function setLastModifiedOn(){
		$this->last_modified_on = new \DateTime('NOW');
		$this->last_modified_on->setTimeZone(new \DateTimeZone("UTC"));
	}

	public function setStatus($status){
		$this->status = $status;
	}
	
	public function getStatus(){
		return $this->status;
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
	
	public function getChilds(){
		return $this->childs;
	}
	
  	public function setStartdate($startdate = null){
  		$this->startdate = $startdate;
  	}
	
 	public function getStartdate(){
 		return $this->startdate;
 	}
	
	public function setEnddate($enddate = null){
		$this->enddate = $enddate;
	}
	
	public function getEnddate(){
		return $this->enddate;
	}
	
	public function getFiles(){
		return $this->files;
	}
	
	/** 
	 * @ORM\PostLoad
	 */
	public function doCorrectUTC(){
		//les dates sont stockées sans information de timezone, on considère par convention qu'elles sont en UTC
		//mais à la création php les crée en temps local, il faut donc les corriger
		$offset = date("Z");
		if($this->enddate){
			$this->enddate->setTimezone(new \DateTimeZone("UTC"));
			$this->enddate->add(new \DateInterval("PT".$offset."S"));
		}
		if($this->startdate){
			$this->startdate->setTimezone(new \DateTimeZone("UTC"));
			$this->startdate->add(new \DateInterval("PT".$offset."S"));
		}
		if($this->created_on){
			$this->created_on->setTimezone(new \DateTimeZone("UTC"));
			$this->created_on->add(new \DateInterval("PT".$offset."S"));
		}
		if($this->last_modified_on){
			$this->last_modified_on->setTimezone(new \DateTimeZone("UTC"));
			$this->last_modified_on->add(new \DateInterval("PT".$offset."S"));
		}
	}
	
	
	public function createFromPredefinedEvent(\Application\Entity\PredefinedEvent $predefined){
		$this->setCategory($predefined->getCategory());
		$this->setImpact($predefined->getImpact());
		$this->setPunctual($predefined->isPunctual());
	}
	
	
	public function getArrayCopy() {
		$object_vars = get_object_vars($this);
		$object_vars['status'] = ($this->status ? $this->status->getId() : null);
		$object_vars['impact'] = ($this->impact ? $this->impact->getId() : null);
		$object_vars['category'] = ($this->category ? $this->category->getId() : null);
		$object_vars['author'] = ($this->author ? $this->author->getId() : null);
		return $object_vars;
	}
}