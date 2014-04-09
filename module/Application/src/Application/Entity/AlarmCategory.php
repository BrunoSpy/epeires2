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

use Zend\Form\Annotation;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
/**
 * @ORM\Entity(repositoryClass="Application\Repository\CategoryRepository")
 **/
class AlarmCategory extends Category{
	
	/**
	 * Ref to the field used to store the state of a radar
	 * @ORM\OneToOne(targetEntity="CustomField")
	 */
	protected $namefield;
	
	/**
	 * @ORM\OneToOne(targetEntity="CustomField")
	 */
	protected $textfield;
	
	public function getNamefield(){
		return $this->namefield;
	}
	
	public function setNamefield($namefield){
		$this->namefield = $namefield;
	}
	
	public function getTextfield(){
		return $this->textfield;
	}
	
	public function setTextfield($textfield){
		$this->textfield = $textfield;
	}
	
}