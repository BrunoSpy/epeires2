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
/**
 * @ORM\Entity @ORM\Table(name="organisations")
 **/
class Organisation {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;
	
	/**
	 * @ORM\Column(type="string")
	 */
	protected $name;
	
	/**
	 * @ORM\Column(type="string")
	 */
	protected $shortname;
	
	/**
	 * @ORM\Column(type="string")
	 */
	protected $address;
	
	public function getId(){
		return $this->id;
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function getShortname(){
		return $this->shortname;
	}
	
	public function getAdress(){
		return $this->address;
	}
	
}