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
 * @ORM\Entity(repositoryClass="Application\Repository\EventRepository")
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
	 * @Annotation\Attributes({"class":"datetime"})
 	 * @Gedmo\Versioned
 	 */
  	protected $startdate;
	
 	/** 
 	 * @ORM\Column(type="datetime", nullable=true)
	 * @Annotation\Type("Zend\Form\Element\DateTime")
	 * @Annotation\Required(false)
	 * @Annotation\Options({"label":"Fin :", "format" : "d-m-Y H:i"})
	 * @Annotation\Attributes({"class":"datetime"})
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
 	protected $archived = false;
 	
 	/**
 	 * @ORM\ManyToOne(targetEntity="Core\Entity\User", inversedBy="events")
         * @ORM\JoinColumn(nullable=false)
 	 */
 	protected $author;
 	
 	/**
 	 * @ORM\OneToMany(targetEntity="EventUpdate", mappedBy="event", cascade={"remove"})
 	 */
 	protected $updates;
 	
        /** 
         * @ORM\Column(type="boolean")
         * @Annotation\Type("Zend\Form\Element\Checkbox")
         * @Annotation\Options({"label":"Evènement programmé :"})
         */
        protected $scheduled;
        
 	public function __construct(){
 		parent::__construct();
 		$this->updates = new \Doctrine\Common\Collections\ArrayCollection();
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
	
	public function isArchived(){
		return $this->archived;
	}
	
	public function setArchived($archived){
		$this->archived = $archived;
	}
	
        public function isScheduled(){
            return $this->scheduled;
        }
        
        public function setScheduled($scheduled){
            $this->scheduled = $scheduled;
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
        
        public function getLastModifiedOn(){
            return $this->last_modified_on;
        }

	public function setStatus($status){
		$this->status = $status;
	}
	
	public function getStatus(){
		return $this->status;
	}
	
        /**
         * 
         * @param \DateTime $startdate Warning : Timezone == UTC !
         * @param \DateTime $enddate Warning : Timezone == UTC !
         */
        public function setDates(\DateTime $startdate,  \DateTime $enddate){
            if($startdate && $enddate){
                if($startdate <= $enddate){
                    $this->startdate = $startdate;
                    $this->enddate = $enddate;
                    return true;
                }
            }
            return false;
        }
        
  	public function setStartdate($startdate = null){
            if($this->enddate == null || ($this->enddate != null && $this->enddate >= $startdate) ) {
                $this->startdate = $startdate;
                return true;
            } else {
                return false;
            }
  	}
	
 	public function getStartdate(){
 		return $this->startdate;
 	}
	
	public function setEnddate($enddate = null){
            if($this->startdate == null) {
                //impossible de fixer la date de fin si aucune date de début
                return false;
            } else {
                if($enddate == null || $this->startdate <= $enddate) {
                    $this->enddate = $enddate;
                } else {
                    return false;
                }
            }
            return true;
	}
	
	public function getEnddate(){
		return $this->enddate;
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