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
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/**
 * @ORM\Table(name="sectors")
 * @ORM\Entity(repositoryClass="Application\Repository\SectorRepository")
 *
 * @author Bruno Spyckerelle
 *        
 */
class Sector
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="SectorsGroupsRelation",
     *     mappedBy="sector",
     *     cascade={"persist", "remove"})
     */
    protected $sectorsgroupsrelations;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom :"})
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="QualificationZone", inversedBy="sectors")
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required(true)
     * @Annotation\Options({"label":"Zone de qualification :", "empty_option":"Choisir la zone de qualification"})
     */
    protected $zone;

    /**
     * @ORM\OneToOne(targetEntity="Frequency", mappedBy="defaultsector", cascade={"detach"})
     */
    protected $frequency;

    public function __construct()
    {
        $this->sectorsgroupsrelations = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
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

    public function getSectorsgroups()
    {
        $sectorsgroups = new ArrayCollection();
        foreach ($this->sectorsgroupsrelations as $relation) {
            $sectorsgroups->add($relation->getSectorGroup());
        }
        return $sectorsgroups;
    }
    
    public function setSectorsGroupsRelations(Collection $sectorsgroupsrelations) {
        $this->sectorsgroupsrelations = $sectorsgroupsrelations;
    }
    
    /**
     * @return ArrayCollection
     */
    public function getSectorsGroupsRelations()
    {
        return $this->sectorsgroupsrelations;
    }
    
    /**
     * @return Frequency
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    public function setFrequency(Frequency $frequency)
    {
        $this->frequency = $frequency;
    }

    public function getArrayCopy()
    {
        $object_vars = get_object_vars($this);
        $sectorsgroups = array();
        foreach ($this->getSectorsgroups() as $sectorsgroup) {
            $sectorsgroups[] = $sectorsgroup->getId();
        }
        $object_vars['sectorsgroups'] = $sectorsgroups;
        $object_vars['frequency'] = ($this->frequency ? $this->frequency->getId() : null);
        return $object_vars;
    }
}