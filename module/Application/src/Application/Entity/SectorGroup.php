<?php

/** 
 * Epeires 2
 * 
 * Groupe de secteurs de contrôle.
 * 
 * @copyright Copyright (c) 2013 Bruno Spyckerelle
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */
namespace Application\Entity;


use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Gedmo\Sortable\Entity\Repository\SortableRepository")
 * @ORM\Table(name="sectorgroups")
 **/
class SectorGroup {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
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
	 * @ORM\ManyToOne(targetEntity="QualificationZone", inversedBy="sectorsgroups")
	 * @Annotation\Type("Zend\Form\Element\Select")
	 * @Annotation\Required(true)
	 * @Annotation\Options({"label":"Zone de qualification :", "empty_option":"Choisir la zone de qualification"})
	 */
	protected $zone;
	
	/** 
	 * @ORM\ManyToMany(targetEntity="Sector", mappedBy="sectorsgroups")
	 * @Annotation\Type("Zend\Form\Element\Select")
	 * @Annotation\Required(false)
	 * @Annotation\Options({"label":"Secteurs :"})
	 * @Annotation\Attributes({"multiple":true})
	 */
	protected $sectors;
	
	/**
	 * @ORM\Column(type="boolean")
	 */
	protected $display = false;
	
	/**
	 * @ORM\Column(type="integer")
	 * @Gedmo\SortablePosition
	 */
	protected $position;
	
	public function __construct(){
		$this->sectors = new \Doctrine\Common\Collections\ArrayCollection();
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
	
	public function setPosition($position)
	{
		$this->position = $position;
	}
	
	public function getPosition()
	{
		return $this->position;
	}
	
	public function setDisplay($display){
		$this->display = $display;
	}
	
	public function isDisplay(){
		return $this->display;
	}
	
	public function getZone(){
		return $this->zone;
	}
	
	public function setZone($zone){
		$this->zone = $zone;
	}
	
	public function getSectors(){
		return $this->sectors;
	}
	
	public function setSectors($sectors){
		$this->sectors = $sectors;
	}
	
	public function addSectors(Collection $sectors){
		foreach ($sectors as $sector){
			$collection = new ArrayCollection();
			$collection->add($this);
			$sector->addSectorsGroups($collection);
			$this->sectors->add($sector);
		}
	}
	
	public function removeSectors(Collection $sectors){
		foreach ($sectors as $sector){
			$collection = new ArrayCollection();
			$collection->add($this);
			$sector->removeSectorsGroups($collection);
			$this->sectors->removeElement($sector);
		}
	}
	
	public function getArrayCopy() {
		$object_vars = get_object_vars($this);
		$sectors = array();
		foreach ($this->sectors as $sector){
			$sectors[] = $sector->getId();
		}
		$object_vars['sectors'] = $sectors;
		return $object_vars;
	}
}