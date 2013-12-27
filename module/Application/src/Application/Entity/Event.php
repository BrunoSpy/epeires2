<?php
/**
 * Epeires 2
 *
 * @copyright Copyright (c) 2013 Bruno Spyckerelle
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Zend\Form\Annotation;

/**
 * @ORM\Entity(repositoryClass="Application\Repository\ExtendedRepository")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable(logEntryClass="Application\Entity\Log")
 **/
class Event extends AbstractEvent{
	
 	/** 
 	 * @ORM\ManyToOne(targetEntity="Status")
 	 * @Annotation\Type("Zend\Form\Element\Select")
	 * @Annotation\Required(true)
	 * @Annotation\Options({"label":"Statut :"})
 	 * @Gedmo\Versioned
 	 */
 	protected $status;
	
 	/** 
 	 * Actions need an empty start date at creation
 	 * @ORM\Column(type="datetime", nullable=true)
   	 * @Annotation\Type("Zend\Form\Element\DateTime")
	 * @Annotation\Required(false)
	 * @Annotation\Options({"label":"Début :", "format" : "d-m-Y H:i"})
	 * @Annotation\Attributes({"class":"datetime", "id":"dateDeb"})
 	 * @Gedmo\Versioned
 	 */
  	protected $startdate;
	
 	/** 
 	 * @ORM\Column(type="datetime", nullable=true)
     * @Annotation\Type("Zend\Form\Element\DateTime")
	 * @Annotation\Required(false)
	 * @Annotation\Options({"label":"Fin :", "format" : "d-m-Y H:i"})
	 * @Annotation\Attributes({"class":"datetime", "id":"dateFin"})
 	 * @Gedmo\Versioned
 	 */
 	protected $enddate = null;
	
	/** @ORM\Column(type="datetime") */
	protected $created_on;
	
 	/** @ORM\Column(type="datetime") */
 	protected $last_modified_on;
 	
 	/**
 	 * @ORM\Column(type="boolean")
 	 */
 	protected $star = false;
 	
 	/**
 	 * @ORM\ManyToOne(targetEntity="Core\Entity\User", inversedBy="events")
 	 */
 	protected $author;
 	
 	/**
 	 * @ORM\OneToMany(targetEntity="EventUpdate", mappedBy="event", cascade={"remove"})
 	 */
 	protected $updates;
 	
 	/**
 	 * @ORM\ManyToMany(targetEntity="File", mappedBy="events")
 	 */
 	protected $files;
 	
 	public function __construct(){
 		parent::__construct();
 		$this->updates = new \Doctrine\Common\Collections\ArrayCollection();
 		$this->files = new \Doctrine\Common\Collections\ArrayCollection();
 	}
 	
 	public function getAuthor(){
 		return $this->author;
 	}
 	
 	public function setAuthor($author){
 		$this->author = $author;
 	}
 	
 	public function getUpdates(){
 		return $this->updates;
 	}
	
	public function isStar(){
		return $this->star;
	}
	
	public function setStar($star){
		$this->star = $star;
	}
	
	/** @ORM\PrePersist */
	public function setCreatedOn(){
		$this->created_on = new \DateTime('NOW');
		$this->created_on->setTimeZone(new \DateTimeZone("UTC"));
	}
	
	/** 
	 * @ORM\PreUpdate
	 * @ORM\PrePersist 
	 */
	public function setLastModifiedOn(){
		$this->last_modified_on = new \DateTime('NOW');
		$this->last_modified_on->setTimeZone(new \DateTimeZone("UTC"));
	}

	public function setStatus($status){
		$this->status = $status;
	}
	
	public function getStatus(){
		return $this->status;
	}
	
  	public function setStartdate($startdate = null){
  		$this->startdate = $startdate;
  	}
	
 	public function getStartdate(){
 		return $this->startdate;
 	}
	
	public function setEnddate($enddate = null){
		$this->enddate = $enddate;
	}
	
	public function getEnddate(){
		return $this->enddate;
	}
	
	public function getFiles(){
		return $this->files;
	}
	
	/** 
	 * @ORM\PostLoad
	 */
	public function doCorrectUTC(){
		//les dates sont stockées sans information de timezone, on considère par convention qu'elles sont en UTC
		//mais à la création php les crée en temps local, il faut donc les corriger
		$offset = date("Z");
		if($this->enddate){
			$this->enddate->setTimezone(new \DateTimeZone("UTC"));
			$this->enddate->add(new \DateInterval("PT".$offset."S"));
		}
		if($this->startdate){
			$this->startdate->setTimezone(new \DateTimeZone("UTC"));
			$this->startdate->add(new \DateInterval("PT".$offset."S"));
		}
		if($this->created_on){
			$this->created_on->setTimezone(new \DateTimeZone("UTC"));
			$this->created_on->add(new \DateInterval("PT".$offset."S"));
		}
		if($this->last_modified_on){
			$this->last_modified_on->setTimezone(new \DateTimeZone("UTC"));
			$this->last_modified_on->add(new \DateInterval("PT".$offset."S"));
		}
	}
	
	
	public function createFromPredefinedEvent(\Application\Entity\PredefinedEvent $predefined){
		$this->setCategory($predefined->getCategory());
		$this->setImpact($predefined->getImpact());
		$this->setPunctual($predefined->isPunctual());
	}
	
	
	public function getArrayCopy() {
		$object_vars = array_merge(get_object_vars($this), parent::getArrayCopy());
		$object_vars['status'] = ($this->status ? $this->status->getId() : null);
		$object_vars['author'] = ($this->author ? $this->author->getId() : null);
		return $object_vars;
	}
}