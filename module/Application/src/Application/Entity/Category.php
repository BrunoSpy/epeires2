<?php
/** 
 * Epeires 2
*
* Catégorie d'évènements.
* Peut avoir une catégorie parente.
*
* @copyright Copyright (c) 2013 Bruno Spyckerelle
* @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
*/
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Table(name="categories")
 * @ORM\Entity(repositoryClass="Application\Repository\CategoryRepository")
 **/
class Category {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;
	
	/** @ORM\ManyToOne(targetEntity="Category") */
	protected $parent;
	
	/**
	 * @ORM\Column(type="string")
	 */
	protected $short_name;
	
	/**
	 * @ORM\Column(type="string")
	 * Color coded in hexa, ex: #FFFFFF
	 */
	protected $color;
	
	/** @ORM\Column(type="string") */
	protected $name;
	
	//TODO : ajouter couleur et short_name
	
	public function getParent(){
		return $this->parent;
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
	
	public function getShortName(){
		return $this->short_name;
	}
	
	public function setShortName($short_name){
		$this->short_name = $short_name;
	}
	
	public function getColor(){
		return $this->color;
	}
	
	public function setColor($color){
		$this->color = $color;
	}
}