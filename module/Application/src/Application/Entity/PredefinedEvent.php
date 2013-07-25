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
 * @ORM\Table(name="predefined_events")
 * @ORM\Entity(repositoryClass="Application\Repository\PredefinedEventRepository")
**/
class PredefinedEvent implements InputFilterAwareInterface {
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

 	/** @ORM\ManyToOne(targetEntity="PredefinedEvent") */
 	protected $parent;
	
// 	/** @ORM\ManyToOne(targetEntity="Impact") */
// 	protected $impact;
	
 	/** @ORM\ManyToOne(targetEntity="Category") */
 	protected $category;
 	
 	/** @ORM\Column(type="boolean") */
 	protected $listable;
 	
 	/** @ORM\Column(type="boolean") */
 	protected $searchable;
	
 	/** @ORM\Column(type="integer") */
 	protected $order;
 	
 	/**
 	 * @ORM\OneToMany(targetEntity="PredefinedCustomFieldValue", mappedBy="event")
 	 */
 	protected $custom_fields_values;
 	
 	public function __construct(){
 		$this->custom_fields_values = new \Doctrine\Common\Collections\ArrayCollection();
 	}
 	
 	public function getCustomFieldsValues(){
 		return $this->custom_fields_values;
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
	
	public function setName($name){
		$this->name = $name;
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function setStatus($status){
		$this->status = $status;
	}
	
	public function setCategory($category){
		$this->category = $category;
	}
	/*** Form Validation ****/
	private $inputFilter;
	
	public function populate($data){
		$this->id     = (isset($data['id']))     ? $data['id']     : null;
		$this->name = (isset($data['name'])) ? $data['name'] : null;
		$this->punctual = (isset($data['punctual'])) ? $data['punctual'] : null;
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
					'name'     => 'status',
					'required' => true,
			)));
			
			$this->inputFilter = $inputFilter;
		}
	
		return $this->inputFilter;
	}
}