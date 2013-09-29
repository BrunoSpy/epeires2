<?php
/** Epeires 2
*
* @copyright Copyright (c) 2013 Bruno Spyckerelle
* @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
*/
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity(repositoryClass="Application\Repository\ExtendedRepository")
 * @ORM\Table(name="frequencies")
 **/
class Frequency {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;
	
 	/** @ORM\ManyToOne(targetEntity="Antenna", inversedBy="mainfrequencies") */
	protected $mainantenna;
	
	/** @ORM\ManyToOne(targetEntity="Antenna", inversedBy="backupfrequencies") */
	protected $backupantenna;
	
	/** @ORM\OneToOne(targetEntity="Sector") */
	protected $defaultsector;
	
	/** @ORM\Column(type="decimal") */
	protected $value;
	
	public function getId(){
		return $this->id;
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function setName($name){
		$this->name = $name;
	}
	
}