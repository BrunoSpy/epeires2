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
use Zend\Form\Annotation;

/**
 * @ORM\Entity(repositoryClass="Application\Repository\CategoryRepository")
 * @ORM\Table(name="antennas")
 */
class Antenna extends TemporaryResource
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Organisation")
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Organisation :", "empty_option":"Choisir l'organisation"})
     */
    protected $organisation;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom :"})
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom abrégé :"})
     */
    protected $shortname;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Localisation :"})
     */
    protected $location;

    /**
     * @ORM\OneToMany(targetEntity="Frequency", mappedBy="mainantenna", cascade={"detach"})
     */
    protected $mainfrequencies;

    /**
     * @ORM\OneToMany(targetEntity="Frequency", mappedBy="backupantenna", cascade={"detach"})
     */
    protected $backupfrequencies;

    /**
     * @ORM\OneToMany(targetEntity="Frequency", mappedBy="mainantennaclimax", cascade={"detach"})
     */
    protected $mainfrequenciesclimax;

    /**
     * @ORM\OneToMany(targetEntity="Frequency", mappedBy="backupantennaclimax", cascade={"detach"})
     */
    protected $backupfrequenciesclimax;

    /**
     * @ORM\OneToOne(targetEntity="PredefinedEvent")
     */
    protected $model;

    public function __construct()
    {
        $this->mainfrequencies = new \Doctrine\Common\Collections\ArrayCollection();
        $this->backupfrequencies = new \Doctrine\Common\Collections\ArrayCollection();
        $this->mainfrequenciesclimax = new \Doctrine\Common\Collections\ArrayCollection();
        $this->backupfrequenciesclimax = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function getShortname()
    {
        return $this->shortname;
    }

    public function setShortname($name)
    {
        $this->shortname = $name;
    }

    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;
    }

    public function getOrganisation()
    {
        return $this->organisation;
    }

    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation($location)
    {
        $this->location = $location;
    }

    public function getMainfrequencies()
    {
        return $this->mainfrequencies;
    }

    public function getBackupfrequencies()
    {
        return $this->backupfrequencies;
    }

    public function getMainfrequenciesclimax()
    {
        return $this->mainfrequenciesclimax;
    }

    public function getBackupfrequenciesclimax()
    {
        return $this->backupfrequenciesclimax;
    }
    
    /**
     * @return ArrayCollection All frequencies on this antenna
     */
    public function getAllFrequencies()
    {
        $allFreq = new ArrayCollection();
        foreach ($this->mainfrequencies as $f) {
            $allFreq->add($f);
        }
        foreach ($this->backupfrequencies as $f) {
            $allFreq->add($f);
        }
        foreach ($this->mainfrequenciesclimax as $f) {
            $allFreq->add($f);
        }
        foreach ($this->backupfrequenciesclimax as $f) {
            $allFreq->add($f);
        }
        return $allFreq;
    }
    
    public function getModel()
    {
        return $this->model;
    }

    public function setModel($model)
    {
        $this->model = $model;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}
