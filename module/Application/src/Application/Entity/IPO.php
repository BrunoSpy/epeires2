<?php
/** Epeires 2
*
* @copyright Copyright (c) 2013 Bruno Spyckerelle
* @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
*/
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Application\Repository\ExtendedRepository")
 * @ORM\Table(name="ipos")
 * @Gedmo\Loggable(logEntryClass="Application\Entity\Log")
 **/
class IPO {
	/** 
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 * @Annotation\Type("Zend\Form\Element\Hidden")
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
 	 * @ORM\ManyToOne(targetEntity="Organisation")
	 * @Annotation\Type("Zend\Form\Element\Select")
	 * @Annotation\Required({"required":"true"})
	 * @Annotation\Options({"label":"Organisation :", "empty_option":"Choisir l'organisation"})
 	 */
	protected $organisation;
	
	/**
	 * @ORM\Column(type="boolean")
	 * @Gedmo\Versioned
	 */
	protected $current = false;
	
	public function getId(){
		return $this->id;
	}
	
	public function isCurrent(){
		return $this->current;
	}
	
	public function setCurrent($current) {
		$this->current = $current;
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function setName($name){
		$this->name = $name;
	}
	
	public function getOrganisation(){
		return $this->organisation;
	}
	
	public function setOrganisation(Organisation $organisation){
		$this->organisation = $organisation;
	}
	
	public function getArrayCopy(){
		$object_vars = get_object_vars($this);
		$object_vars['organisation'] = $this->organisation->getId();
		return $object_vars;
	}
}