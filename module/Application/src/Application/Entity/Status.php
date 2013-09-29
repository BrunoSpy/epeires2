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
 * @ORM\Table(name="status")
 **/
class Status {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;
	
	/** @ORM\Column(type="boolean") */
	protected $open;
	
	/** @ORM\Column(type="boolean") */
	protected $display;
	
	/** @ORM\Column(type="string") */
	protected $name;
	
	/** @ORM\Column(type="boolean") */
	protected $defaut;
	
	public function getId(){
		return $this->id;
	}
	
	public function isOpen(){
		return $this->open;
	}
	
	public function setOpen($open){
		$this->open = $open;
	}
	
	public function isDefault(){
		return $this->defaut;
	}
	
	public function setDefault($default){
		$this->defaut = $default;
	}
	
	public function isDisplay(){
		return $this->display;
	}
	
	public function setDisplay($display){
		$this->display = $display;
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function setName($name){
		$this->name = $name;
	}
	
	
	
}