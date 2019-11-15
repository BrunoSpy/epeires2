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
use Zend\Form\Annotation;

/**
 * @ORM\Entity(repositoryClass="Application\Repository\QualificationZoneRepository")
 * @ORM\Table(name="qualifzones")
 *
 * @author Bruno Spyckerelle
 *        
 */
class QualificationZone
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", unique=true)
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom :"})
     */
    protected $name;

    /**
     * @ORM\Column(type="string", unique=true)
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom court :"})
     */
    protected $shortname;

    /**
     * @ORM\ManyToOne(targetEntity="Organisation", inversedBy="zones")
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Organisation :", "empty_option":"Choisir l'organisation"})
     */
    protected $organisation;

    /**
     * @ORM\OneToMany(targetEntity="SectorGroup", mappedBy="zone", cascade={"remove"})
     */
    protected $sectorsgroups;

    /**
     * @ORM\OneToMany(targetEntity="Sector", mappedBy="zone", cascade={"remove"})
     */
    protected $sectors;

    public function __construct()
    {
        $this->sectorsgroups = new \Doctrine\Common\Collections\ArrayCollection();
        $this->sectors = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getShortname()
    {
        return $this->shortname;
    }

    public function getOrganisation()
    {
        return $this->organisation;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setShortname($shortname)
    {
        $this->shortname = $shortname;
    }

    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;
    }

    public function getArrayCopy()
    {
        $object_vars = get_object_vars($this);
        $object_vars["organisation"] = ($this->organisation ? $this->organisation->getId() : null);
        return $object_vars;
    }
}