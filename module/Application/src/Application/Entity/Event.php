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
	
// 	/** @ORM\ManyToOne(targetEntity="Event") */
// 	protected $parent;
	
// 	/** @ORM\ManyToOne(targetEntity="Impact") */
// 	protected $impact;
	
 	/** @ORM\Column(type="datetime") */
 	protected $start_date;
	
// 	/** @ORM\Column(type="datetime") */
// 	protected $end_date;
	
	/** @ORM\Column(type="datetime") */
	protected $created_on;
	
 	/** @ORM\Column(type="datetime") */
 	protected $last_modified_on;
	
// 	/** @ORM\ManyToOne(targetEntity="Category") */
// 	protected $category;
	
	public function isPunctual() {
		return $punctual;
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
	
	/*** Form Validation ****/
	private $inputFilter;
	
	public function populate($data){
		$this->id     = (isset($data['id']))     ? $data['id']     : null;
		$this->name = (isset($data['name'])) ? $data['name'] : null;
		$this->punctual = (isset($data['punctual'])) ? $data['punctual'] : null;
		$this->start_date = (isset($data['start_date'])) ? new \DateTime($data['start_date']) : null;
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
					'required' => true,
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
					'name'     => 'start_date',
					'required' => true,
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