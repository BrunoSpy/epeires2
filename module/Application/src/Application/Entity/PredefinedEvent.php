<?php
/**
 * Epeires 2
 *
 * @copyright Copyright (c) 2013 Bruno Spyckerelle
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */
namespace Application\Entity;

use Zend\Form\Annotation;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
**/
class PredefinedEvent extends AbstractEvent{

	/** @ORM\Column(type="string", nullable=true)
	 * @Annotation\Type("Zend\Form\Element\Text")
	 * @Annotation\Required(false)
	 * @Annotation\Options({"label":"Nom :"})
	 */
	protected $name;
	
 	
 	/** @ORM\Column(type="boolean")
	 * @Annotation\Type("Zend\Form\Element\Checkbox")
	 * @Annotation\Options({"label":"Liste :"})
	 */
 	protected $listable;
 	
 	/** @ORM\Column(type="boolean")
	 * @Annotation\Type("Zend\Form\Element\Checkbox")
	 * @Annotation\Options({"label":"Recherche :"})
	 */
 	protected $searchable;
	
 	/** @ORM\Column(type="integer", nullable=true) */
 	protected $place;
 	
 	/** @ORM\ManyToOne(targetEntity="Category", inversedBy="predefinedevents")
 	 * @Annotation\Type("Zend\Form\Element\Select")
 	 * @Annotation\Required({"required":"true"})
 	 * @Annotation\Options({"label":"Catégorie :", "empty_option":"Choisir la catégorie"})
 	 */
 	protected $category;
 	
 	/**
 	 * @ORM\OneToMany(targetEntity="PredefinedCustomFieldValue", mappedBy="predefinedevent", cascade={"remove"})
 	 */
 	protected $custom_fields_values;
 	
 	public function __construct(){
 		$this->custom_fields_values = new \Doctrine\Common\Collections\ArrayCollection();
 	}
 	
 	public function setCategory($category){
 		$this->category = $category;
 	}
 	
 	public function getCategory(){
 		return $this->category;
 	}
 	
 	public function addCustomFieldValue($customfieldvalue){
 		$this->custom_fields_values->add($customfieldvalue);
 	}
 	
 	public function getCustomFieldsValues(){
 		return $this->custom_fields_values;
 	}
 	
	public function setName($name){
		$this->name = $name;
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function isListable(){
		return $this->listable;
	}
	
	public function setListable($listable){
		$this->listable = $listable;
	}
	
	public function isSearchable(){
		return $this->searchable;
	}
	
	public function setSearchable($searchable){
		$this->searchable = $searchable;
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