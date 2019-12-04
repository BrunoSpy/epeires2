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

/**
 * @ORM\Entity
 * @ORM\Table(name="eventupdates")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable(logEntryClass="Application\Entity\Log")
 *
 * @author Bruno Spyckerelle
 */
class EventUpdate
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="text")
     * @Gedmo\Versioned
     */
    protected $text;

    /**
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="updates")
     */
    protected $event;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_on;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $hidden = false;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedOn()
    {
        $this->created_on = new \DateTime('NOW');
        $this->created_on->setTimeZone(new \DateTimeZone("UTC"));
    }

    public function getCreatedOn()
    {
        return $this->created_on;
    }

    /**
     * @ORM\PostLoad
     */
    public function doCorrectUTC()
    {
        if ($this->created_on) {
            $offset = $this->created_on->getTimezone()->getOffset($this->created_on);
            $this->created_on->setTimezone(new \DateTimeZone("UTC"));
            $this->created_on->add(new \DateInterval("PT" . $offset . "S"));
        }
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setEvent($event)
    {
        $this->event = $event;
    }

    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return mixed
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * @param mixed $hidden
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }
}