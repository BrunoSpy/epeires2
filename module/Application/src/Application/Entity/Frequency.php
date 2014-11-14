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
 * @ORM\Entity(repositoryClass="Application\Repository\CategoryRepository")
 * @ORM\Table(name="frequencies")
 **/
class Frequency {
	/** 
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;
	
	/** 
	 * @ORM\ManyToOne(targetEntity="Organisation")
	 * @Annotation\Type("Zend\Form\Element\Select")
	 * @Annotation\Required(true)
	 * @Annotation\Options({"label":"Organisation :", "empty_option":"Choisir l'organisation"})
	 */
	protected $organisation;
	
 	/** 
 	 * @ORM\ManyToOne(targetEntity="Antenna", inversedBy="mainfrequencies") 
 	 * @Annotation\Type("Zend\Form\Element\Select")
	 * @Annotation\Required(true)
	 * @Annotation\Options({"label":"Antenne principale :", "empty_option":"Choisir l'antenne principale"})
 	 */
	protected $mainantenna;
	
	/** 
	 * @ORM\ManyToOne(targetEntity="Antenna", inversedBy="backupfrequencies") 
	 * @Annotation\Type("Zend\Form\Element\Select")
	 * @Annotation\Required(true)
	 * @Annotation\Options({"label":"Antenne secours :", "empty_option":"Choisir l'antenne secours"})
	 */
	protected $backupantenna;
	
	/** 
	 * @ORM\ManyToOne(targetEntity="Antenna", inversedBy="mainfrequenciesclimax")
	 * @Annotation\Type("Zend\Form\Element\Select")
	 * @Annotation\Required(false)
	 * @Annotation\Options({"label":"Antenne principale climax :", "empty_option":"Choisir l'antenne"})
	 */
	protected $mainantennaclimax;
	
	/** 
	 * @ORM\ManyToOne(targetEntity="Antenna", inversedBy="backupfrequenciesclimax")
	 * @Annotation\Type("Zend\Form\Element\Select")
	 * @Annotation\Required(false)
	 * @Annotation\Options({"label":"Antenne secours climax :", "empty_option":"Choisir l'antenne"})
	 */
	protected $backupantennaclimax;
	
	/** 
	 * @ORM\OneToOne(targetEntity="Sector", inversedBy="frequency")
	 * @Annotation\Type("Zend\Form\Element\Select")
	 * @Annotation\Required(false)
	 * @Annotation\Options({"label":"Secteur par défaut :", "empty_option":"Choisir le secteur"})
	 */
	protected $defaultsector = null;
	
	/** 
	 * @ORM\Column(type="decimal", precision=6, scale=3)
	 * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Valeur :"})
	 */
	protected $value;
	
	/** 
	 * @ORM\Column(type="string")
	 * @Annotation\Type("Zend\Form\Element\Text")
	 * @Annotation\Required({"required":"true"})
	 * @Annotation\Options({"label":"Nom :"})
	 */
	protected $othername;
	
	public function getId(){
		return $this->id;
	}
	
	public function getValue(){
		return $this->value;
	}
	
	public function setValue($value){
		$this->value = $value;
	}
	
	public function getName(){
		if($this->getDefaultsector()) {
			return $this->getDefaultsector()->getName();
		} else {
			return $this->getOthername();
		}
	}
	
	public function getOrganisation(){
		return $this->organisation;
	}
	
	public function setOrganisation($organisation){
		$this->organisation = $organisation;
	}
	
	public function getOthername(){
		return $this->othername;
	}
	
	public function setOthername($othername){
		$this->othername = $othername;
	}
	
	public function getDefaultsector(){
		return $this->defaultsector;
	}
	
	public function setDefaultsector($defaultsector){
		$this->defaultsector = $defaultsector;
	}
	
	public function setMainantenna($mainantenna){
		$this->mainantenna = $mainantenna;
	}
	
	public function getMainantenna(){
		return $this->mainantenna;
	}
	
	public function setBackupantenna($backupantenna){
		$this->backupantenna = $backupantenna;
	}
	
	public function getBackupantenna(){
		return $this->backupantenna;
	}
	
	public function setMainantennaclimax($mainantennaclimax){
		$this->mainantennaclimax = $mainantennaclimax;
	}
	
	public function getMainantennaclimax(){
		return $this->mainantennaclimax;
	}
	
	public function setBackupantennaclimax($backupantennaclimax){
		$this->backupantennaclimax = $backupantennaclimax;
	}
	
	public function getBackupantennaclimax(){
		return $this->backupantennaclimax;
	}
	
	public function getArrayCopy() {
		$object_vars = get_object_vars($this);
		$object_vars['mainantenna'] = ($this->mainantenna ? $this->mainantenna->getId() : null);
		$object_vars['backupantenna'] = ($this->backupantenna ? $this->backupantenna->getId() : null);
		$object_vars['mainantennaclimax'] = ($this->mainantennaclimax ? $this->mainantennaclimax->getId() : null);
		$object_vars['backupantennaclimax'] = ($this->backupantennaclimax ? $this->backupantennaclimax->getId() : null);
		$object_vars['defaultsector'] = ($this->defaultsector ? $this->defaultsector->getId() : null);
		$object_vars['organisation'] = ($this->organisation ? $this->organisation->getId() : null);
		return $object_vars;
	}
}