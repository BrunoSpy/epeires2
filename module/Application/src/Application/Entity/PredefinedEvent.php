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
class PredefinedEvent {
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

 	/** @ORM\ManyToOne(targetEntity="PredefinedEvent", inversedBy="childs") */
 	protected $parent;
	
 	/**
 	 * @ORM\OneToMany(targetEntity="PredefinedEvent", mappedBy="parent", cascade={"remove"})
 	 */
 	protected $childs;
 	
 	/** @ORM\ManyToOne(targetEntity="Impact") */
 	protected $impact;
	
 	/** @ORM\ManyToOne(targetEntity="Category", inversedBy="predefinedevents") */
 	protected $category;
 	
 	/** @ORM\Column(type="boolean") */
 	protected $listable;
 	
 	/** @ORM\Column(type="boolean") */
 	protected $searchable;
	
 	/** @ORM\Column(type="integer") */
 	protected $order;
 	
 	/**
 	 * @ORM\OneToMany(targetEntity="PredefinedCustomFieldValue", mappedBy="predefinedevent", cascade={"remove"})
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
	
	public function setCategory($category){
		$this->category = $category;
	}

	public function getCategory(){
		return $this->category;
	}
	
	public function setImpact($impact){
		$this->impact = $impact;
	}
	
	public function getImpact(){
		return $this->impact;
	}
	
}