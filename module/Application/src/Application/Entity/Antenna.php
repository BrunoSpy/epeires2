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
 * @ORM\Table(name="antennas")
 **/
class Antenna extends HardwareResource {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 * @ORM\Column(type="integer")
	 */
	protected $id;
	
 	/** 
 	 * @ORM\ManyToOne(targetEntity="Organisation")
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
	
	/** 
	 * @ORM\Column(type="string")
	 * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Localisation :"})
	 */
	protected $location;
	
	/** 
	 * @ORM\OneToMany(targetEntity="Frequency", mappedBy="mainantenna", cascade={"detach"})
	 */
	protected $mainfrequencies;
	
	/**
	 * @ORM\OneToMany(targetEntity="Frequency", mappedBy="backupantenna", cascade={"detach"})
	 */
	protected $backupfrequencies;
	
	/**
	 * @ORM\OneToMany(targetEntity="Frequency", mappedBy="mainantennaclimax", cascade={"detach"})
	 */
	protected $mainfrequenciesclimax;
	
	/**
	 * @ORM\OneToMany(targetEntity="Frequency", mappedBy="backupantennaclimax", cascade={"detach"})
	 */
	protected $backupfrequenciesclimax;
	
        /**
         * @ORM\OneToOne(targetEntity="PredefinedEvent") 
         * 
         */
        protected $model;
	
	public function __construct(){
		$this->mainfrequencies = new \Doctrine\Common\Collections\ArrayCollection();
		$this->backupfrequencies = new \Doctrine\Common\Collections\ArrayCollection();
		$this->mainfrequenciesclimax = new \Doctrine\Common\Collections\ArrayCollection();
		$this->backupfrequenciesclimax = new \Doctrine\Common\Collections\ArrayCollection();
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
	
	public function getLocation(){
		return $this->location;
	}
	
	public function setLocation($location){
		$this->location = $location;
	}
	
	public function getMainfrequencies(){
		return $this->mainfrequencies;
	}
	
	public function getBackupfrequencies(){
		return $this->backupfrequencies;
	}
	
	public function getMainfrequenciesclimax(){
		return $this->mainfrequenciesclimax;
	}
	
	public function getBackupfrequenciesclimax(){
		return $this->backupfrequenciesclimax;
	}
	
        public function getModel(){
            return $this->model;
        }
        
        public function setModel(PredefinedEvent $model){
            $this->model = $model;
        }
        
	public function getArrayCopy() {
		return get_object_vars($this);
	}
}