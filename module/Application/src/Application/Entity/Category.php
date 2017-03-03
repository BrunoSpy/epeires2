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

use Zend\Form\Annotation;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Table(name="categories", indexes={@ORM\Index(name="search_idx", columns={"timeline"}),
                                          @ORM\Index(name="discr_idx", columns={"discr"})
                                          })
 * @ORM\Entity(repositoryClass="Application\Repository\CategoryRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"generic" = "Category",
 * "radar" = "RadarCategory",
 * "antenna" = "AntennaCategory",
 * "frequency" = "FrequencyCategory",
 * "action" = "ActionCategory",
 * "alarm" = "AlarmCategory",
 * "brouillage" = "BrouillageCategory",
 * "mil" = "MilCategory",
 * "afis" = "AfisCategory",
 * "flightplan" = "FlightPlanCategory",
 * "alert" = "AlertCategory"})
 *
 * Catégorie d'évènements.
 * Peut avoir une catégorie parente.
 *
 * @author Bruno Spyckerelle
 * @license https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */
class Category
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Annotation\Type("Zend\Form\Element\Hidden")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="childs")
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required(false)
     * @Annotation\Options({"label":"Catégorie parente", "empty_option":"Choisir la catégorie parente"})
     * @Gedmo\SortableGroup
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Category", mappedBy="parent")
     */
    protected $childs;

    /**
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom court"})
     * @ORM\Column(type="string")
     */
    protected $shortname;

    /**
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Couleur"})
     * @ORM\Column(type="string")
     * Color coded in hexa, ex: #FFFFFF
     */
    protected $color;

    /**
     * @ORM\Column(type="boolean")
     * @Annotation\Type("Zend\Form\Element\Checkbox")
     * @Annotation\Options({"label":"Mode compact"})
     */
    protected $compactmode;

    /**
     * @ORM\Column(type="boolean")
     * @Annotation\Type("Zend\Form\Element\Checkbox")
     * @Annotation\Options({"label":"Timeline"})
     */
    protected $timeline;

    /**
     * @ORM\Column(type="boolean")
     * @Annotation\Type("Zend\Form\Element\Checkbox")
     * @Annotation\Options({"label":"Mode confirmé"})
     * @Annotation\Attributes({"id":"timelineconfirmed","title":"Disponible uniquement si Timeline est actif", "data-toggle":"tooltip"})
     */
    protected $timelineconfirmed;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom complet"})
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="AbstractEvent", mappedBy="category", cascade={"remove"})
     */
    protected $events;

    /**
     * Bidirectional - inverse side
     * @ORM\OneToMany(targetEntity="CustomField", mappedBy="category", cascade={"remove"})
     */
    protected $customfields;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required(false)
     * @Annotation\Options({"label":"Champ titre", "empty_option":"Choisir le champ titre"})
     */
    protected $fieldname;

    /**
     * @ORM\ManyToMany(targetEntity="Core\Entity\Role", inversedBy="readcategories")
     * @ORM\JoinTable(name="roles_categories_read")
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required(true)
     * @Annotation\Attributes({"multiple":true})
     * @Annotation\Options({"label":"Affichée pour"})
     */
    protected $readroles;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\SortablePosition
     */
    protected $place;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $system = false;

    /**
     * @ORM\ManyToMany(targetEntity="Tab", mappedBy="categories")
     */
    protected $tabs;

    /**
     * Exclude category from IPO report
     * @ORM\Column(type="boolean")
     * @Annotation\Type("Zend\Form\Element\Checkbox")
     * @Annotation\Options({"label":"Exclure IPO"})
     */
    protected $exclude = false;

    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->customfields = new ArrayCollection();
        $this->readroles = new ArrayCollection();
        $this->tabs = new ArrayCollection();
    }

    public static function getTypeValueOptions()
    {
        $type = array();
        $type['generic'] = "Générique";
        $type['radar'] = "Radar";
        $type['antenna'] = "Antenne";
        $type['frequency'] = "Fréquence";
        $type['brouillage'] = "Brouillage fréquence";
        $type['mil'] = "Zone militaire";
        $type['afis'] = "AFIS";
        $type['flightplan'] = "Plan de vol";
        $type['alert'] = "Alerte";
        return $type;
    }

    public function getCustomfields()
    {
        return $this->customfields;
    }

    public function setSystem($system)
    {
        $this->system = $system;
    }

    public function isSystem()
    {
        return $this->system;
    }

    public function setPlace($place)
    {
        $this->place = $place;
    }

    public function getPlace()
    {
        return $this->place;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getChildren()
    {
        return $this->childs;
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

    public function getShortName()
    {
        return $this->shortname;
    }

    public function setShortName($shortname)
    {
        $this->shortname = $shortname;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function setColor($color)
    {
        $this->color = $color;
    }

    public function isTimeline()
    {
        return $this->timeline;
    }

    public function isTimelineConfirmed()
    {
        return $this->timelineconfirmed;
    }

    public function setTimeline($timeline)
    {
        $this->timeline = $timeline;
    }

    public function setTimelineConfirmed($timelineconfirmed)
    {
        $this->timelineconfirmed = $timelineconfirmed;
    }

    public function isCompactMode()
    {
        return $this->compactmode;
    }

    public function setCompactMode($compactmode)
    {
        $this->compactmode = $compactmode;
    }

    public function isExclude() {
        return $this->exclude;
    }

    public function setExclude($exclude) {
        $this->exclude = $exclude;
    }

    public function getFieldname()
    {
        return $this->fieldname;
    }

    public function setFieldname($fieldname)
    {
        $this->fieldname = $fieldname;
    }

    public function getTabs()
    {
        return $this->tabs;
    }

    public function getReadroles($recursive = false)
    {
        if ($recursive) {
            $readroles = new ArrayCollection();
            foreach ($this->readroles as $readrole) {
                $readroles->add($readrole);
            }
            foreach ($this->childs as $child) {
                foreach ($child->getReadroles(true) as $readrole) {
                    $readroles->add($readrole);
                }
            }
            return $readroles;
        } else {
            return $this->readroles;
        }
    }

    public function setReadroles($readroles)
    {
        $this->readroles = $readroles;
    }

    public function addReadroles(Collection $roles)
    {
        foreach ($roles as $role) {
            $this->readroles->add($role);
        }
    }

    public function removeReadroles(Collection $roles)
    {
        foreach ($roles as $role) {
            $this->readroles->removeElement($role);
        }
    }

    public function getArrayCopy()
    {
        $object_vars = get_object_vars($this);
        $object_vars["parent"] = ($this->parent ? $this->parent->getId() : null);
        $object_vars["fieldname"] = ($this->fieldname ? $this->fieldname->getId() : null);
        $roles = array();
        foreach ($this->readroles as $role) {
            $roles[] = $role->getId();
        }
        $object_vars['readroles'] = $roles;
        return $object_vars;
    }
}