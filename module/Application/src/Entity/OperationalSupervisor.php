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
use Laminas\Form\Annotation;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Application\Repository\ExtendedRepository")
 * @ORM\Table(name="opsups")
 * @Gedmo\Loggable(logEntryClass="Application\Entity\Log")
 *
 * @author Bruno Spyckerelle
 *        
 */
class OperationalSupervisor
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Annotation\Type("Laminas\Form\Element\Hidden")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom :"})
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="Organisation")
     * @Annotation\Type("Laminas\Form\Element\Select")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Organisation :", "empty_option":"Choisir l'organisation"})
     */
    protected $organisation;

    /**
     * @ORM\ManyToOne(targetEntity="OpSupType", inversedBy="opsups")
     * @Annotation\Type("Laminas\Form\Element\Select")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Type :", "empty_option":"Choisir le type"})
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="QualificationZone")
     * @Annotation\Type("Laminas\Form\Element\Select")
     * @Annotation\Required(true)
     * @Annotation\Options({"label":"Zone de qualification :", "empty_option":"Choisir la zone de qualification"})
     */
    protected $zone;

    /**
     * @ORM\Column(type="boolean")
     * @Gedmo\Versioned
     */
    protected $current = false;

    /**
     * @ORM\Column(type="boolean")
     * @Annotation\Type("Laminas\Form\Element\Checkbox")
     * @Annotation\Options({"label":"Archivé :"})
     * @Annotation\Attributes({"title":"En cochant cette option, tous les évènements liés ne seront plus modifiables."})
     */
    protected $archived = false;

    public function isArchived()
    {
        return $this->archived;
    }

    public function setArchived($d)
    {
        $this->archived = $d;
    }

    public function getId()
    {
        return $this->id;
    }

    public function isCurrent()
    {
        return $this->current;
    }

    public function setCurrent($current)
    {
        $this->current = $current;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getZone()
    {
        return $this->zone;
    }

    public function setZone($zone)
    {
        $this->zone = $zone;
    }

    public function getOrganisation()
    {
        return $this->organisation;
    }

    public function setOrganisation(Organisation $organisation)
    {
        $this->organisation = $organisation;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getArrayCopy()
    {
        $object_vars = get_object_vars($this);
        $object_vars['organisation'] = $this->organisation->getId();
        $object_vars['type'] = $this->type->getId();
        $object_vars['zone'] = $this->zone->getId();
        return $object_vars;
    }
}