<?php
/**
 * Epeires 2
 *
 * @copyright Copyright (c) 2013 Bruno Spyckerelle
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;

/**
 * @ORM\Entity
 * @ORM\Table(name="events")
 * @ORM\HasLifecycleCallbacks
 **/
class Event implements InputFilterAwareInterface {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;

	/** @ORM\Column(type="string") */
	protected $name;
	
	/** @ORM\Column(type="boolean") */
	protected $punctual;

 	/** @ORM\ManyToOne(targetEntity="Status") */
 	protected $status;
	
 	/** @ORM\ManyToOne(targetEntity="Event", inversedBy="childs") */
 	protected $parent;
	
 	/** @ORM\OneToMany(targetEntity="Event", mappedBy="parent") */
 	protected $childs;
 	
 	/** @ORM\ManyToOne(targetEntity="Impact") */
 	protected $impact;
	
 	/** 
 	 * @ORM\Column(type="datetime", nullable=true)
 	 */
 	protected $start_date;
	
 	/** 
 	 * @ORM\Column(type="datetime", nullable=true)
 	 */
 	protected $end_date;
	
	/** @ORM\Column(type="datetime") */
	protected $created_on;
	
 	/** @ORM\Column(type="datetime") */
 	protected $last_modified_on;
	
 	/** @ORM\ManyToOne(targetEntity="Category") */
 	protected $category;
	
 	/**
 	 * @ORM\OneToMany(targetEntity="CustomFieldValue", mappedBy="event")
 	 */
 	protected $custom_fields_values;
 	
 	public function __construct(){
 		$this->custom_fields_values = new \Doctrine\Common\Collections\ArrayCollection();
 		$this->childs = new \Doctrine\Common\Collections\ArrayCollection();
 	}
 	
 	public function getId(){
 		return $this->id;
 	}
 	
 	public function getCustomFieldsValues(){
 		return $this->custom_fields_values;
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
	}
	
	/** 
	 * @ORM\PreUpdate
	 * @ORM\PrePersist 
	 */
	public function setLastModifiedOn(){
		$this->last_modified_on = new \DateTime('NOW');
	}
	
	public function setName($name){
		$this->name = $name;
	}
	
	public function getName(){
		return $this->name;
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
 		return $this->start_date;
 	}
	
	public function setEndDate($enddate = null){
		$this->end_date = $enddate;
	}
	
	public function getEndDate(){
		return $this->end_date;
	}
	
	public function createFromPredefinedEvent(\Application\Entity\PredefinedEvent $predefined){
		$this->setName($predefined->getName());
		$this->setCategory($predefined->getCategory());
		$this->setImpact($predefined->getImpact());
		$this->setPunctual($predefined->isPunctual());
	}
	
	/*** Form Validation ****/
	private $inputFilter;
	
	public function populate($data){
		$this->id     = (isset($data['id']))     ? $data['id']     : null;
		$this->name = (isset($data['name'])) ? $data['name'] : null;
		$this->punctual = (isset($data['punctual'])) ? $data['punctual'] : null;
		if(isset($data['start_date']) && !empty($data['start_date'])){
			$this->start_date = new \DateTime($data['start_date']);
		}
		if(isset($data['end_date']) && !empty($data['end_date'])){
			$this->end_date = new \DateTime($data['end_date']);
		}
	}
	
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
					'name'     => 'name',
					'required' => true,
					'filters'  => array(
							array('name' => 'StripTags'),
							array('name' => 'StringTrim'),
					),
					'validators' => array(
							array(
									'name'    => 'StringLength',
									'options' => array(
											'encoding' => 'UTF-8',
											'min'      => 1,
											'max'      => 100,
									),
							),
					),
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