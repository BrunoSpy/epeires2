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
 * @ORM\Table(name="stacks")
 **/
class Stack {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;
	
 	/** 
 	 * @ORM\ManyToOne(targetEntity="QualificationZone")
 	 * @ORM\JoinColumn(nullable=false)
	 * @Annotation\Type("Zend\Form\Element\Select")
	 * @Annotation\Required({"required":"true"})
	 * @Annotation\Options({"label":"Zone de qualification :", "empty_option":"Choisir la zone"})
 	 */
	protected $zone;
	
	/** 
	 * @ORM\Column(type="string")
	 * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom :"})
	 */
	protected $name;
	
	public function getId(){
		return $this->id;
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function setName($name){
		$this->name = $name;
	}
	
	public function getZone(){
		return $this->zone;
	}
	
	public function setZone(QualificationZone $zone){
		$this->zone = $zone;
	}
	
	public function getArrayCopy() {
		$object_vars = get_object_vars($this);
		$object_vars['zone'] = $this->zone->getId();
		return $object_vars;
	}
}