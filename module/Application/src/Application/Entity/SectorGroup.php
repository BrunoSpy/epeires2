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
/**
 * @ORM\Entity @ORM\Table(name="sectorgroups")
 **/
class SectorGroup {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;
	
	/** @ORM\Column(type="string") */
	protected $name;

	/**
	 * @ORM\ManyToMany(targetEntity="Sector", mappedBy="sectorGroups")
	 */
	protected $sectors;
	
	public function __construct(){
		$this->sectors = new \Doctrine\Common\Collections\ArrayCollection();
	}
}