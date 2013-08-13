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
 * @ORM\Table(name="stacks")
 **/
class Stack {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;
	
 	/** @ORM\ManyToOne(targetEntity="QualificationZone") */
	protected $zone;
	
	/** @ORM\Column(type="string") */
	protected $name;
	
	/** @ORM\Column(type="string") */
	protected $short_name;
	
	/** @ORM\Column(type="string") */
	protected $location;
	
	public function setId($id){
		$this->id = $id;
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
	
}