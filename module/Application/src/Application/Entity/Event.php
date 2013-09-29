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
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;

/**
 * @ORM\Entity(repositoryClass="Application\Repository\ExtendedRepository")
 * @ORM\Table(name="events")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable(logEntryClass="Application\Entity\Log")
 **/
class Event implements InputFilterAwareInterface {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;

	/** 
	 * @ORM\Column(type="boolean")
	 * @Gedmo\Versioned
	 */
	protected $punctual;

 	/** 
 	 * @ORM\ManyToOne(targetEntity="Status")
 	 * @Gedmo\Versioned
 	 */
 	protected $status;
	
 	/** @ORM\ManyToOne(targetEntity="Event", inversedBy="childs") */
 	protected $parent;
	
 	/** @ORM\OneToMany(targetEntity="Event", mappedBy="parent", cascade={"remove"}) */
 	protected $childs;
 	
 	/** 
 	 * @ORM\ManyToOne(targetEntity="Impact")
 	 * @Gedmo\Versioned
 	 */
 	protected $impact;
	
 	/** 
 	 * @ORM\Column(type="datetime", nullable=true)
 	 * @Gedmo\Versioned
 	 */
  	protected $start_date;
	
 	/** 
 	 * @ORM\Column(type="datetime", nullable=true)
 	 * @Gedmo\Versioned
 	 */
 	protected $end_date;
	
	/** @ORM\Column(type="datetime") */
	protected $created_on;
	
 	/** @ORM\Column(type="datetime") */
 	protected $last_modified_on;
	
 	/** @ORM\ManyToOne(targetEntity="Category", inversedBy="events") */
 	protected $category;
	
 	/**
 	 * @ORM\OneToMany(targetEntity="CustomFieldValue", mappedBy="event", cascade={"remove"})
 	 */
 	protected $custom_fields_values;
 	
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
 	}
 	
 	public function getId(){
 		return $this->id;
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
	
  	public function setStartDate($startdate = null){
  		$this->start_date = $startdate;
  	}
	
 	public function getStartDate(){
 		if($this->start_date){
 			//les dates sont stockées sans information de timezone, on considère par convention qu'elles sont en UTC
 			//mais à la création php les crée en temps local, il faut donc les corriger
 			$offset = date("Z");
 			$this->start_date->add(new \DateInterval("PT".$offset."S"));
 		}
 		return $this->start_date;
 	}
	
	public function setEndDate($enddate = null){
		$this->end_date = $enddate;
	}
	
	public function getEndDate(){
		if($this->end_date){//les dates sont stockées sans information de timezone, on considère par convention qu'elles sont en UTC
 			//mais à la création php les crée en temps local, il faut donc les corriger
 			$offset = date("Z");
 			$this->end_date->add(new \DateInterval("PT".$offset."S"));
		}
		return $this->end_date;
	}
	
	public function createFromPredefinedEvent(\Application\Entity\PredefinedEvent $predefined){
		$this->setCategory($predefined->getCategory());
		$this->setImpact($predefined->getImpact());
		$this->setPunctual($predefined->isPunctual());
	}
	
	/*** Form Validation ****/
	private $inputFilter;
	
	public function getArrayCopy() {
		return get_object_vars($this);
	}
	
	public function setInputFilter(InputFilterInterface $inputFilter){
		throw new \Exception("Not used");
	}
	
	public function getInputFilter(){
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();
			$factory     = new InputFactory();
	
			$inputFilter->add($factory->createInput(array(
					'name'     => 'id',
					'required' => false,
					'filters'  => array(
							array('name' => 'Int'),
					),
			)));
	
			$inputFilter->add($factory->createInput(array(
					'name'     => 'punctual',
					'required' => true,
			)));
	
			$inputFilter->add($factory->createInput(array(
					'name'     => 'impact',
					'required' => true,
			)));
			
			$inputFilter->add($factory->createInput(array(
					'name'     => 'parent',
					'required' => false,
			)));
			
			$inputFilter->add($factory->createInput(array(
					'name'     => 'start_date',
					'required' => true,
			)));
			
			$inputFilter->add($factory->createInput(array(
					'name'     => 'end_date',
					'required' => false,
			)));
			
			$inputFilter->add($factory->createInput(array(
					'name'     => 'status',
					'required' => true,
			)));
			
			$this->inputFilter = $inputFilter;
		}
	
		return $this->inputFilter;
	}
}