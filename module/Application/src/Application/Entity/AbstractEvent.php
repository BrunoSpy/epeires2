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
use Zend\Form\Annotation;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity(repositoryClass="Application\Repository\ExtendedRepository")
 * @ORM\Table(name="events", indexes={@ORM\Index(name="search_idx", columns={"punctual"})})
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"generic" = "Event", "model" = "PredefinedEvent"})
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\Loggable(logEntryClass="Application\Entity\Log")
 *
 * @author Bruno Spyckerelle
 *        
 */
abstract class AbstractEvent
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Annotation\Type("Zend\Form\Element\Hidden")
     */
    protected $id;

    /**
     * @ORM\Column(type="boolean")
     * @Annotation\Type("Zend\Form\Element\Checkbox")
     * @Annotation\Options({"label":"Ponctuel"})
     * @Annotation\Attributes({"id":"punctual"})
     * @Gedmo\Versioned
     */
    protected $punctual;

    /**
     * @ORM\ManyToOne(targetEntity="AbstractEvent", inversedBy="children")
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required(false)
     * @Annotation\Options({"label":"Evènement parent", "empty_option":"Choisir l'evt parent"})
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="AbstractEvent", mappedBy="parent", cascade={"persist", "remove"})
     */
    protected $children;

    /**
     * Position for models and actions
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $place;

    /**
     * @ORM\ManyToOne(targetEntity="Impact")
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required(true)
     * @Annotation\Options({"label":"Impact"})
     * @Gedmo\Versioned
     */
    protected $impact;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required(true)
     * @Annotation\Options({"label":"Catégorie", "empty_option":"Choisir la catégorie"})
     */
    protected $category;

    /**
     * @ORM\OneToMany(targetEntity="CustomFieldValue", mappedBy="event", cascade={"persist", "remove"})
     */
    protected $custom_fields_values;

    /**
     * @ORM\ManyToOne(targetEntity="Organisation")
     * @ORM\JoinColumn(nullable=false)
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required(true)
     * @Annotation\Options({"label":"Organisation"})
     */
    protected $organisation;

    /**
     * @ORM\ManyToMany(targetEntity="QualificationZone")
     * @ORM\JoinTable(name="events_qualificationzones")
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required(false)
     * @Annotation\Attributes({"multiple":true})
     * @Annotation\Options({"label":"Visibilité"})
     */
    protected $zonefilters;

    /**
     * @ORM\ManyToMany(targetEntity="File", mappedBy="events", cascade={"persist"})
     */
    protected $files;

    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->zonefilters = new \Doctrine\Common\Collections\ArrayCollection();
        $this->custom_fields_values = new \Doctrine\Common\Collections\ArrayCollection();
        $this->files = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function getOrganisation()
    {
        return $this->organisation;
    }

    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;
    }

    public function setZonefilters($zonefilters)
    {
        $this->zonefilters = $zonefilters;
    }

    public function getZonefilters()
    {
        return $this->zonefilters;
    }

    public function addZonefilter(QualificationZone $zonefilter)
    {
        $this->zonefilters->add($zonefilter);
    }

    public function addZonefilters(Collection $zonefilters)
    {
        foreach ($zonefilters as $zonefilter) {
            $this->zonefilters->add($zonefilter);
        }
    }

    public function removeZonefilters(Collection $zonefilters)
    {
        foreach ($zonefilters as $zonefilter) {
            $this->zonefilters->removeElement($zonefilter);
        }
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($category)
    {
        $this->category = $category;
    }

    public function getPlace()
    {
        return $this->place;
    }

    /**
     * @param int $place
     */
    public function setPlace($place)
    {
        $this->place = $place;
    }
    
    public function getCustomFieldsValues()
    {
        return $this->custom_fields_values;
    }

    public function addCustomFieldValue($customfieldvalue)
    {
        $this->custom_fields_values->add($customfieldvalue);
    }

    /**
     * Return the CustomFieldValue corresponding to a given <code>$customfield</code>
     * Return null if event doesn't has a matching <code>$customfield</code>
     *
     * @param \Application\Entity\CustomField $customfield            
     * @return type
     */
    public function getCustomFieldValue(CustomField $customfield)
    {
        $cid = $customfield->getId();
        $fields = $this->custom_fields_values->filter(function ($c) use($cid) {
            return $c->getCustomField()
                ->getId() == $cid;
        });
        if (count($fields) == 1) {
            return $fields->first();
        } else {
            return null;
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function isPunctual()
    {
        return $this->punctual;
    }

    public function setPunctual($punctual)
    {
        $this->punctual = $punctual;
    }

    public function setImpact($impact)
    {
        $this->impact = $impact;
    }

    public function getImpact()
    {
        return $this->impact;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function addChild(Event $event)
    {
        $this->children->add($event);
    }

    public function getArrayCopy()
    {
        $object_vars = get_object_vars($this);
        $object_vars['category'] = ($this->category ? $this->category->getId() : null);
        $object_vars['impact'] = ($this->impact ? $this->impact->getId() : null);
        $object_vars['parent'] = ($this->parent ? $this->parent->getId() : null);
        $object_vars['organisation'] = ($this->organisation ? $this->organisation->getId() : null);
        $zonefilters = array();
        foreach ($this->zonefilters as $zonefilter) {
            $zonefilters[] = $zonefilter->getId();
        }
        $object_vars['zonefilters'] = $zonefilters;
        return $object_vars;
    }
}
