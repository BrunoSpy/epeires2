<?php
/** Epeires 2
*
* @copyright Copyright (c) 2013 Bruno Spyckerelle
* @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
*/
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;
/**
 * @ORM\Entity(repositoryClass="Application\Repository\ExtendedRepository")
 * @ORM\Table(name="radars")
 **/
class Radar extends HardwareResource {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;
	
 	/** 
 	 * @ORM\ManyToOne(targetEntity="Organisation")
 	 * @ORM\JoinColumn(nullable=false)
	 * @Annotation\Type("Zend\Form\Element\Select")
	 * @Annotation\Required({"required":"true"})
	 * @Annotation\Options({"label":"Organisation :", "empty_option":"Choisir l'organisation"})
 	 */
	protected $organisation;
	
	/** 
	 * @ORM\Column(type="string")
	 * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom :"})
	 */
	protected $name;
	
	/** 
	 * @ORM\Column(type="string")
	 * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom abrégé :"})
	 */
	protected $shortname;
	
	
	public function getId(){
		return $this->id;
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function setName($name){
		$this->name = $name;
	}
	
	public function getShortname(){
		return $this->shortname;
	}
	
	public function setShortname($name){
		$this->shortname = $name;
	}
	
	public function setOrganisation($organisation){
		$this->organisation = $organisation;
	}
	
	public function getOrganisation(){
		return $this->organisation;
	}
	
	public function getArrayCopy() {
		$object_vars = get_object_vars($this);
		$object_vars['organisation'] = $this->organisation->getId();
		return $object_vars;
	}
}