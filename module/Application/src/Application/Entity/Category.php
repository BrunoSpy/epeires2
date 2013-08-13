<?php
/** 
 * Epeires 2
*
* Catégorie d'évènements.
* Peut avoir une catégorie parente.
*
* @copyright Copyright (c) 2013 Bruno Spyckerelle
* @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
*/
namespace Application\Entity;

use Zend\Form\Annotation;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Table(name="categories")
 * @ORM\Entity(repositoryClass="Application\Repository\CategoryRepository")
 **/
class Category {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 * @Annotation\Type("Zend\Form\Element\Hidden")
	 */
	protected $id;
	
	/** 
	 * @ORM\ManyToOne(targetEntity="Category")
	 * @Annotation\Type("Zend\Form\Element\Select")
	 * @Annotation\Required(false)
	 * @Annotation\Options({"label":"Catégorie parente :", "empty_option":"Choisir la catégorie parente"})
	 */
	protected $parent;
	
	/**
	 * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom court :"})
	 * @ORM\Column(type="string")
	 */
	protected $shortname;
	
	/**
	 * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Couleur :"})
	 * @ORM\Column(type="string")
	 * Color coded in hexa, ex: #FFFFFF
	 */
	protected $color;
	
	/** @ORM\Column(type="boolean")
	 * @Annotation\Type("Zend\Form\Element\Checkbox")
	 * @Annotation\Options({"label":"Mode compact :"})
	 */
	protected $compactmode;
	
	/** 
	 * @ORM\Column(type="string")
	 * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom complet :"})
	 */
	protected $name;
	
	/**
	* @ORM\OneToMany(targetEntity="Event", mappedBy="category", cascade={"remove"})
	*/
	protected $events;
	
	/**
	 * @ORM\OneToMany(targetEntity="CustomField", mappedBy="category", cascade={"remove"})
	 */
	protected $customfields;
	
	/**
	 * @ORM\OneToMany(targetEntity="PredefinedEvent", mappedBy="category", cascade={"remove"})
	 */
	protected $predefinedevents;
	
	public function getParent(){
		return $this->parent;
	}
	
	public function setParent($parent){
		$this->parent = $parent;
	}
	
	public function getId(){
		return $this->id;
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function setName($name){
		$this->name = $name;
	}
	
	public function getShortName(){
		return $this->shortname;
	}
	
	public function setShortName($shortname){
		$this->shortname = $shortname;
	}
	
	public function getColor(){
		return $this->color;
	}
	
	public function setColor($color){
		$this->color = $color;
	}
	
	public function isCompactMode(){
		return $this->compactmode;
	}
	
	public function setCompactMode($compactmode){
		$this->compactmode = $compactmode;
	}
	
	public function getArrayCopy() {
		return get_object_vars($this);
	}
}