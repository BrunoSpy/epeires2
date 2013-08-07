<?php
/**
 * Epeires 2
 *
 *
 * @copyright Copyright (c) 2013 Bruno Spyckerelle
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */
namespace Application\Entity;

use Zend\Form\Annotation;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="customfields")
 **/
class CustomField {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 * @Annotation\Type("Zend\Form\Element\Hidden")
	 */
	protected $id;
	
	/** @ORM\Column(type="string", unique=true)
 	 * @Annotation\Type("Zend\Form\Element\Text")
	 * @Annotation\Required({"required":"true"})
	 * @Annotation\Options({"label":"Nom :"})
	 */
	protected $name;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Category", inversedBy="customfields")
	 * @Annotation\Type("Zend\Form\Element\Select")
	 * @Annotation\Required({"required":"true"})
	 * @Annotation\Options({"label":"Catégorie :"})
	 */
	protected $category;
	
	/**
	 * @ORM\ManyToOne(targetEntity="CustomFieldType")
	 * @Annotation\Type("Zend\Form\Element\Select")
	 * @Annotation\Required({"required":"true"})
	 * @Annotation\Options({"label":"Type :", "empty_option":"Choisir le type"})
	 */
	protected $type;
	
	/**
	 * @ORM\Column(type="integer")
	 */
	protected $place;
	
	/**
	 * @ORM\OneToMany(targetEntity="CustomFieldValue", mappedBy="customfield", cascade={"remove"})
	 */
	protected $values;
	
	/**
	 * @ORM\OneToMany(targetEntity="PredefinedCustomFieldValue", mappedBy="customfield", cascade={"remove"})
	 */
	protected $predefinedvalues;
	
	public function getId(){
		return $this->id;
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function setName($name){
		$this->name = $name;
	}
	
	public function getType(){
		return $this->type;
	}
	
	public function setCategory($category){
		$this->category = $category;
	}
	
	public function getCategory(){
		return $this->category;
	}
	
	
	public function setType($type){
		$this->type = $type;
	}
	
	public function getPlace(){
		return $this->place;
	}
	
	public function setPlace($place){
		$this->place = $place;
	}
	
	public function getArrayCopy() {
		return get_object_vars($this);
	}
}