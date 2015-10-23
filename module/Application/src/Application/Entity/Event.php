<?php
/*
 * This file is part of Epeires².
 * Epeires² is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Epeires² is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Epeires². If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Zend\Form\Annotation;

/**
 * @ORM\Entity(repositoryClass="Application\Repository\EventRepository")
 * @ORM\Table(indexes={@ORM\Index(name="search_idx", columns={"startdate", "enddate"}),
                       @ORM\Index(name="search_idx2", columns={"last_modified_on"})})
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable(logEntryClass="Application\Entity\Log")
 * 
 * @author Bruno Spyckerelle
 *        
 */
class Event extends AbstractEvent
{

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

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_on;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $last_modified_on;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $star = false;

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
     * @Annotation\Attributes({"id":"scheduled"})
     */
    protected $scheduled = false;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $readonly = false;

    public function __construct()
    {
        parent::__construct();
        $this->updates = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor($author)
    {
        $this->author = $author;
    }

    public function getUpdates()
    {
        return $this->updates;
    }

    public function isReadOnly()
    {
        return $this->readonly;
    }

    public function setReadOnly($readonly)
    {
        $this->readonly = $readonly;
    }

    public function isScheduled()
    {
        return $this->scheduled;
    }

    public function setScheduled($scheduled)
    {
        $this->scheduled = $scheduled;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedOn()
    {
        $this->created_on = new \DateTime('NOW');
        $this->created_on->setTimeZone(new \DateTimeZone("UTC"));
    }

    /**
     * @ORM\PreUpdate
     * @ORM\PrePersist
     */
    public function setLastModifiedOn()
    {
        $this->last_modified_on = new \DateTime('NOW');
        $this->last_modified_on->setTimeZone(new \DateTimeZone("UTC"));
    }

    public function getLastModifiedOn()
    {
        return $this->last_modified_on;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStar($star)
    {
        $this->star = $star;
    }

    public function isStar()
    {
        return $this->star;
    }

    /**
     *
     * @param \DateTime $startdate
     *            Warning : Timezone == UTC !
     * @param \DateTime $enddate
     *            Warning : Timezone == UTC !
     */
    public function setDates(\DateTime $startdate, \DateTime $enddate)
    {
        if ($startdate && $enddate) {
            if ($startdate <= $enddate) {
                $this->startdate = $startdate;
                $this->enddate = $enddate;
                return true;
            }
        }
        return false;
    }

    public function setStartdate($startdate = null)
    {
        if ($this->enddate == null || ($this->enddate != null && $this->enddate >= $startdate)) {
            $this->startdate = $startdate;
            return true;
        } else {
            return false;
        }
    }

    public function getStartdate()
    {
        return $this->startdate;
    }

    public function setEnddate($enddate = null)
    {
        if ($this->startdate == null) {
            // impossible de fixer la date de fin si aucune date de début
            return false;
        } else {
            if ($enddate == null || $this->startdate <= $enddate) {
                $this->enddate = $enddate;
            } else {
                return false;
            }
        }
        return true;
    }

    public function getEnddate()
    {
        return $this->enddate;
    }

    /**
     * @ORM\PostLoad
     */
    public function doCorrectUTC()
    {
        // les dates sont stockées sans information de timezone, on considère par convention qu'elles sont en UTC
        // mais à la création php les crée en temps local, il faut donc les corriger
        if ($this->enddate) {
            $offset = $this->enddate->getTimezone()->getOffset($this->enddate);
            $this->enddate->setTimezone(new \DateTimeZone("UTC"));
            $this->enddate->add(new \DateInterval("PT" . $offset . "S"));
        }
        if ($this->startdate) {
            $offset = $this->startdate->getTimezone()->getOffset($this->startdate);
            $this->startdate->setTimezone(new \DateTimeZone("UTC"));
            $this->startdate->add(new \DateInterval("PT" . $offset . "S"));
        }
        if ($this->created_on) {
            $offset = $this->created_on->getTimezone()->getOffset($this->created_on);
            $this->created_on->setTimezone(new \DateTimeZone("UTC"));
            $this->created_on->add(new \DateInterval("PT" . $offset . "S"));
        }
        if ($this->last_modified_on) {
            $offset = $this->last_modified_on->getTimezone()->getOffset($this->last_modified_on);
            $this->last_modified_on->setTimezone(new \DateTimeZone("UTC"));
            $this->last_modified_on->add(new \DateInterval("PT" . $offset . "S"));
        }
    }

    public function createFromPredefinedEvent(\Application\Entity\PredefinedEvent $predefined)
    {
        $this->setCategory($predefined->getCategory());
        $this->setImpact($predefined->getImpact());
        $this->setPunctual($predefined->isPunctual());
    }

    /**
     * Cloture l'évènement ains que l'ensemble de ses fils.
     * 
     * @param \Application\Entity\Status $status            
     * @param \DateTime $enddate            
     * @throws \RuntimeException
     */
    public function close(Status $status, \DateTime $enddate = null)
    {
        if ($enddate == null && ! $this->isPunctual()) {
            throw new \RuntimeException("Impossible de fermer un évènement non ponctuel sans date de fin.");
        }
        if ($status->getId() != 3) {
            throw new \RuntimeException("Statut \"Fin confirmée\" attendu, un autre statut a été fourni.");
        }
        
        if (! $this->isPunctual()) {
            $this->setEnddate($enddate);
        }
        $this->setStatus($status);
        foreach ($this->getChildren() as $child) {
            // cloturer tous les évènements sauf les alarmes et les actions
            if (! $child->getCategory() instanceof AlarmCategory && ! $child->getCategory() instanceof ActionCategory) {
                $child->close($status, $enddate);
            }
        }
    }

    /**
     * Annule l'évènement et tous ses enfants
     * Si pas d'heure de fin programmée, utilisation de l'heure actuelle
     * 
     * @param \Application\Entity\Status $status            
     * @throws \RuntimeException
     */
    public function cancelEvent(Status $status)
    {
        if ($status->getId() != 4) {
            throw new \RuntimeException("Statut annulé attendu, un autre statut a été fourni.");
        }
        $this->setStatus($status);
        if ($this->isPunctual() || (! $this->isPunctual() && $this->getEnddate() === null)) {
            $now = new \DateTime('now');
            $now->setTimezone(new \DateTimeZone('UTC'));
            $this->setEnddate($now);
        }
        foreach ($this->getChildren() as $child) {
            // annuler tous les évènement sauf les actions
            if (! $child instanceof ActionCategory) {
                $child->cancelEvent($status);
            }
        }
    }

    /**
     * Update alarms if case of modification of startdate and enddate
     */
    public function updateAlarms()
    {
        foreach ($this->getChildren() as $child) {
            if ($child->getCategory() instanceof AlarmCategory) {
                $child->updateAlarmDate();
            }
        }
    }

    /**
     * Update alarm date according to parent dates and deltas
     * Only available if category is instance of AlarmCategory
     * 
     * @param Event $alarm            
     */
    public function updateAlarmDate()
    {
        if (! ($this->getCategory() instanceof AlarmCategory)) {
            return;
        }
        $deltaend = "";
        $deltabegin = "";
        $previousstart = $this->getStartdate();
        foreach ($this->getCustomFieldsValues() as $value) {
            if ($value->getCustomField()->getId() == $this->getCategory()
                ->getDeltaBeginField()
                ->getId()) {
                $deltabegin = preg_replace('/\s+/', '', $value->getValue());
            }
            if ($value->getCustomField()->getId() == $this->getCategory()
                ->getDeltaEndField()
                ->getId()) {
                $deltaend = preg_replace('/\s+/', '', $value->getValue());
            }
        }
        $event = $this->getParent();
        if ($event != null) {
            if (strlen(trim($deltaend)) > 0) {
                $deltaend = intval($deltaend);
            } else {
                $deltaend = 0;
            }
            if (strlen(trim($deltabegin)) > 0) {
                $deltabegin = intval($deltabegin);
            } else {
                $deltabegin = null;
            }
            if ($deltaend !== 0 && $event->getEnddate() != null) {
                $startdatealarm = clone $event->getEnddate();
                if ($deltaend > 0) {
                    $startdatealarm->add(new \DateInterval("PT" . $deltaend . "M"));
                } else {
                    $invdiff = - $deltaend;
                    $interval = new \DateInterval('PT' . $invdiff . 'M');
                    $interval->invert = 1;
                    $startdatealarm->add($interval);
                }
                if ($startdatealarm != $previousstart) {
                    $this->setStartdate($startdatealarm);
                }
            } else {
                if ($deltabegin !== null) {
                    $startdatealarm = clone $event->getStartdate();
                    if ($deltabegin > 0) {
                        $startdatealarm->add(new \DateInterval("PT" . $deltabegin . "M"));
                    } else {
                        $invdiff = - $deltabegin;
                        $interval = new \DateInterval('PT' . $invdiff . 'M');
                        $interval->invert = 1;
                        $startdatealarm->add($interval);
                    }
                    if ($startdatealarm != $previousstart) {
                        $this->setStartdate($startdatealarm);
                    }
                }
            }
        }
    }

    public function getArrayCopy()
    {
        $object_vars = array_merge(get_object_vars($this), parent::getArrayCopy());
        $object_vars['status'] = ($this->status ? $this->status->getId() : null);
        $object_vars['author'] = ($this->author ? $this->author->getId() : null);
        return $object_vars;
    }
}
