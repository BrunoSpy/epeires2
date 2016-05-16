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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
//use RRule\RRule;
use RRule\RRule;
use RRule\RSet;
use Zend\Form\Annotation;

/**
 * 
 * @ORM\Entity
 * @ORM\Table(name="recurrences")
 * @ORM\HasLifecycleCallbacks
 * @author Bruno Spyckerelle
 */
class Recurrence
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;
    
    /**
     * Recurrence pattern following RFC 2445 RRULE spec
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Text")
     */
    protected $recurrencePattern;
    
    
    /**
     * @ORM\OneToMany(targetEntity="Event", mappedBy="recurrence")
     */
    protected $events;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $startdate;

    /**
     * @ORM\OneToMany(targetEntity="RecurrenceExDate", mappedBy="recurrence", cascade={"persist", "remove"})
     */
    protected $exdates;

    private $rrule;

    public function __construct($dtstart, $pattern, $refEvent, $status)
    {
        $this->events = new ArrayCollection();
        $this->exdates = new ArrayCollection();
        $this->startdate = $dtstart;
        $this->recurrencePattern = $pattern;
        $this->createEvents($refEvent, $status);
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @ORM\PostLoad
     */
    public function doCorrectUTC()
    {
        // les dates sont stockées sans information de timezone, on considère par convention qu'elles sont en UTC
        // mais à la création php les crée en temps local, il faut donc les corriger
        if ($this->startdate) {
            $offset = $this->startdate->getTimezone()->getOffset($this->startdate);
            $this->startdate->setTimezone(new \DateTimeZone("UTC"));
            $this->startdate->add(new \DateInterval("PT" . $offset . "S"));
        }
    }

    /**
     * @return \DateTime
     */
    public function getStartdate()
    {
        return $this->startdate;
    }

    /**
     * @param \DateTime $startdate
     */
    public function setStartdate($startdate)
    {
        $this->startdate = $startdate;
        $this->rrule = null;
    }

    /**
     * @return string
     */
    public function getRecurrencePattern()
    {
        return $this->recurrencePattern;
    }

    /**
     * @param string $recurrencePattern
     */
    public function setRecurrencePattern($recurrencePattern)
    {
        if($this->recurrencePattern === $recurrencePattern) {
            //do nothing
        } else {
            $this->recurrencePattern = $recurrencePattern;
            $this->rrule = null;
            $this->updateEvents();
        }
    }

    public function getEvents()
    {
        return $this->events;
    }

    public function exclude(Event $event) {
        $exdate = new RecurrenceExDate($event->getStartdate());
        $this->addExDates($exdate);
        $this->events->removeElement($event);
    }

    /**
     * @return mixed
     */
    public function getExdates()
    {
        return $this->exdates;
    }

    /**
     * @param mixed $exdates
     */
    public function setExdates($exdates)
    {
        $this->exdates = $exdates;
    }

    public function addExDates($exdate) {
        $this->exdates->add($exdate);
    }

    public function removeExDates($exdate) {
        $this->exdates->removeElement($exdate);
    }

    private function getRRule() {
        if($this->rrule == null) {
            $rule = 'DTSTART;TZID=Etc/GMT:' . $this->getStartdate()->format('Ymd\THis') . '
                 RRULE:'.$this->getRecurrencePattern();
            $this->rrule = new RRule($rule);
        }
        return $this->rrule;
    }

    /**
     * Create events from a reference event
     * @param Event $refEvent
     */
    private function createEvents(Event $refEvent, $status) {
        $rset = new RSet();
        $rset->addRRule($this->getRRule());
        foreach ($this->getExdates() as $exdate) {
            $rset->addExDate($exdate);
        }
        if($this->getRRule()->isFinite()) {
            foreach ($rset as $occurence) {
                $event = Event::createFromEvent($refEvent, $occurence, $status);
                $event->setRecurrence($this);
                $this->events->add($event);
            }
        } else {
            //throw something
        }
    }

    /**
     * When a recurrence is updated, only future events are concerned,
     * Past events are excluded.
     * @param $recurrencepattern
     * @param $diff
     * @param $refEvent
     * @return ArrayCollection Events excluded to be persisted
     */
    public function update($recurrencepattern, $diff, $refEvent) {
        $now = new Date();
        $offset = $now->getTimezone()->getOffset($now);
        $now->setTimezone(new \DateTimeZone("UTC"));
        $now->add(new \DateInterval("PT" . $offset . "S"));
        $excluded = array();
        foreach ($this->getEvents() as $e) {
            if($e->getStartdate() < $now) {
                $e->setRecurrence(null);
                $excluded[] = $e;
            }
        }
        //delete future events ??
        
        return $excluded;
    }

}