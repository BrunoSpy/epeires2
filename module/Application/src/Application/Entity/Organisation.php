<?php
/**
 * Epeires 2
 *
 * 
 *
 * @copyright Copyright (c) 2013 Bruno Spyckerelle
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;
/**
 * @ORM\Entity(repositoryClass="Application\Repository\ExtendedRepository")
 * @ORM\Table(name="organisations")
 **/
class Organisation {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;
	
	/** 
	 * @ORM\Column(type="string", unique=true)
	 * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom :"})
	 */
	protected $name;
	
	/**
	 * @ORM\Column(type="string", unique=true)
	 * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom court :"})
	 */
	protected $shortname;
	
	/** 
	 * @ORM\Column(type="string")
	 * @Annotation\Type("Zend\Form\Element\Textarea")
     * @Annotation\Required(false)
     * @Annotation\Options({"label":"Adresse :"})
	 */
	protected $address;
	
	/**
	 * @ORM\OneToMany(targetEntity="QualificationZone", mappedBy="organisation", cascade={"remove"})
	 */
	protected $zones;
	
	/**
	 * @ORM\OneToMany(targetEntity="Core\Entity\User", mappedBy="organisation")
	 */
	protected $users;
	
	public function __construct(){
		$this->zones = new \Doctrine\Common\Collections\ArrayCollection();
		$this->users = new \Doctrine\Common\Collections\ArrayCollection();
	}
	
	public function getId(){
		return $this->id;
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function getShortname(){
		return $this->shortname;
	}
	
	public function getAddress(){
		return $this->address;
	}
	
	public function setId($id){
		$this->id = $id;
	}
	
	public function setName($name){
		$this->name = $name;
	}
	
	public function setShortname($shortname){
		$this->shortname = $shortname;
	}
	
	public function setAddress($adress){
		$this->address = $adress;
	}
	
	public function getZones(){
		return $this->zones;
	}
	public function getArrayCopy() {
		return get_object_vars($this);
	}
	
}