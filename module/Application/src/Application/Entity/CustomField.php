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
	
	/** 
	 * @ORM\Column(type="string")
 	 * @Annotation\Type("Zend\Form\Element\Text")
	 * @Annotation\Required({"required":"true"})
	 * @Annotation\Options({"label":"Nom :"})
	 */
	protected $name;
	
	/** 
	 * Bidirectional - owning side
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
	 * @ORM\Column(type="text")
	 * @Annotation\Type("Zend\Form\Element\Textarea")
	 * @Annotation\Required(false)
	 * @Annotation\Options({"label":"Valeur par défaut :"})
	 * Stores default value, for example for select customtype
	 */
	protected $defaultvalue;
	
        /** 
         * @ORM\Column(type="text", nullable=true)
         * @Annotation\Type("Zend\Form\Element\Text")
         * @Annotation\Required(false)
         * @Annotation\Options({"label":"Popup d'aide"})
         */
        protected $tooltip;
        
	/**
	 * @ORM\OneToMany(targetEntity="CustomFieldValue", mappedBy="customfield", cascade={"remove"})
	 */
	protected $values;
	
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
	
	public function getDefaultValue(){
		return $this->defaultvalue;
	}
	
	public function setDefaultValue($defaultvalue){
		$this->defaultvalue = $defaultvalue;
	}
        
        public function getTooltip(){
            return $this->tooltip;
        }
	
        public function setTooltip($tooltip){
            $this->tooltip = $tooltip;
        }
        
	public function getArrayCopy() {
		$object_vars = get_object_vars($this);
		$object_vars['category'] = ($this->category ? $this->category->getId() : null);
		return $object_vars;
	}
}