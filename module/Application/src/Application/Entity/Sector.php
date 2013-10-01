<?php

/** 
 * Epeires 2
 * 
 * Secteur de contrôle
 *
 * @copyright Copyright (c) 2013 Bruno Spyckerelle
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */
namespace Application\Entity;


use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Table(name="sectors")
 * @ORM\Entity(repositoryClass="Application\Repository\ExtendedRepository")
 **/
class Sector {
	
	/** 
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;
	
	/** 
	 * @ORM\ManyToMany(targetEntity="SectorGroup", inversedBy="sectors")
	 * @ORM\JoinTable(name="sectors_groups")
	 * @Annotation\Type("Zend\Form\Element\Select")
	 * @Annotation\Required(false)
	 * @Annotation\Options({"label":"Regroupements :"})
	 * @Annotation\Attributes({"multiple":true})
	 */
	protected $sectorsgroups;
	
	/** 
	 * @ORM\Column(type="string")
	 * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom :"})
	 */
	protected $name;
	
	/** 
	 * @ORM\ManyToOne(targetEntity="QualificationZone", inversedBy="sectors")
	 * @Annotation\Type("Zend\Form\Element\Select")
	 * @Annotation\Required(true)
	 * @Annotation\Options({"label":"Zone de qualification :", "empty_option":"Choisir la zone de qualification"})
	 */
	protected $zone;
	
	/** 
	 * @ORM\OneToOne(targetEntity="Frequency", mappedBy="defaultsector", cascade={"detach"})
	 */
	protected $frequency;
	
	public function __construct(){
		$this->sectorsgroups = new \Doctrine\Common\Collections\ArrayCollection();
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
	
	public function getZone(){
		return $this->zone;
	}
	
	public function setZone($zone){
		$this->zone = $zone;
	}
	
	public function getSectorsgroups(){
		return $this->sectorsgroups;
	}
	
	public function setSectorsgroups($sectorsgroups){
		$this->sectorsgroups = $sectorsgroups;
	}
	
	public function addSectorsgroups(Collection $sectorsgroups){
		foreach ($sectorsgroups as $sectorsgroup){
			$this->sectorsgroups->add($sectorsgroup);
		}
	}
	
	public function removeSectorsgroups(Collection $sectorsgroups){
		foreach ($sectorsgroups as $sectorsgroup){
			$this->sectorsgroups->removeElement($sectorsgroup);
		}
	}
	
	public function getFrequency(){
		return $this->frequency;
	}
	
	public function setFrequency($frequency){
		$this->frequency = $frequency;
	}
	
	public function getArrayCopy() {
		$object_vars = get_object_vars($this);
		$sectorsgroups = array();
		foreach ($this->sectorsgroups as $sectorsgroup){
			$sectorsgroups[] = $sectorsgroup->getId();
		}
		$object_vars['sectorsgroups'] = $sectorsgroups;
		$object_vars['frequency'] = ($this->frequency ? $this->frequency->getId() : null);
		return $object_vars;
	}
}