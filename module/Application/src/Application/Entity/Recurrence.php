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
use Zend\Form\Annotation;

/**
 * 
 * @ORM\Entity
 * @ORM\Table(name="recurrences")
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
    
    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }
    
    public function getRecurrencePattern()
    {
        return $this->recurrencePattern;
    }

    public function setRecurrencePattern($recurrencePattern)
    {
        $this->recurrencePattern = $recurrencePattern;
    }

    public function getEvents()
    {
        return $this->events;
    }

    
    
//    private function computeEndRecurrence() {
//        $rule = 'DTSTART;TZID=Etc/GMT:' . $this->getStartdate()->format('Ymd\THis') . '
//                 RRULE:'.$this->getRecurrence();
//        try {
//            $rrule = new RRule($rule);
//            if($rrule->isInfinite()) {
//                return null;
//            } else {
//                $rset = new RSet();
//                $rset->addRRule($rrule);
//                return $rset[$rset->count() - 1];
//            }
//        } catch (\Exception $e) {
//            //impossible de positionner une récurrence
//            error_log($e->getMessage());
//            return null;
//        }
//    }
}