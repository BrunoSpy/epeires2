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
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity
 * @ORM\Table(name="files")
 * 
 * @author Bruno Spyckerelle
 */
class File
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(name="path", type="string", unique=true)
     */
    protected $path;

    /**
     * @ORM\Column(name="mime_type", type="string", nullable=true)
     */
    protected $mimetype;

    /**
     * @ORM\Column(name="size", type="decimal", nullable=true)
     */
    protected $size;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $reference;

    /**
     * @ORM\Column(type="string")
     */
    protected $filename;

    /**
     * @ORM\ManyToMany(targetEntity="AbstractEvent", inversedBy="files")
     * @ORM\JoinTable(name="file_event")
     */
    protected $events;

    public function __construct()
    {
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getEvents()
    {
        return $this->events;
    }

    public function addEvent(AbstractEvent $event)
    {
        $this->events->add($event);
    }

    public function addEvents(Collection $events)
    {
        foreach ($events as $event) {
            $this->events->add($event);
        }
    }

    public function removeEvents(Collection $events)
    {
        foreach ($events as $event) {
            $this->events->removeElement($event);
        }
    }

    public function removeEvent(AbstractEvent $event)
    {
        $this->events->removeElement($event);
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setSize($size)
    {
        $this->size = $size;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function setMimetype($mimetype)
    {
        $this->mimetype = $mimetype;
    }

    public function getMimetype()
    {
        return $this->mimetype;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    public function getReference()
    {
        return $this->reference;
    }
}