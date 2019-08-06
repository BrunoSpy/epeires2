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
 * @ORM\Table(name="recurrenceexdates")
 * @ORM\HasLifecycleCallbacks
 * @author Bruno Spyckerelle
 */
class RecurrenceExDate
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Recurrence", inversedBy="exdates")
     */
    protected $recurrence;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $date;
    
    
    public function __construct($date)
    {
        $this->date = $date;
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
        if ($this->date) {
            $offset = $this->date->getTimezone()->getOffset($this->date);
            $this->date->setTimezone(new \DateTimeZone("UTC"));
            $this->date->add(new \DateInterval("PT" . $offset . "S"));
        }
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }
    
    /**
     * @return Recurrence
     */
    public function getRecurrence()
    {
        return $this->recurrence;
    }

    /**
     * @param mixed Recurrence
     */
    public function setRecurrence($recurrence)
    {
        $this->recurrence = $recurrence;
    }
    
}