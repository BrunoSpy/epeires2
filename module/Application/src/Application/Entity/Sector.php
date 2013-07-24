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
/**
 * @ORM\Table(name="sectors")
 * @ORM\Entity(repositoryClass="Application\Repository\StatusRepository")
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
	 */
	protected $sectorGroups;
	
	/**
	 * @ORM\Column(type="string")
	 */
	protected $name;
	
	/**
	 * @ORM\ManyToOne(targetEntity="QualificationZone")
	 */
	protected $zone;
	
	public function __construct(){
		$this->sectorGroups = new Doctrine\Common\Collections\ArrayCollection();
	}
	
	public function getId(){
		return $this->id;
	}
	
	public function getName(){
		return $this->name;
	}
}