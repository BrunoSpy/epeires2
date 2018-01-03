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
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="customfields")
 * 
 * @author Bruno Spyckerelle
 * @license https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */
class CustomField
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Annotation\Type("Zend\Form\Element\Hidden")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom"})
     */
    protected $name;

    /**
     * Bidirectional - owning side
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="customfields")
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Catégorie"})
     * @Gedmo\SortableGroup
     */
    protected $category;

    /**
     * @ORM\ManyToOne(targetEntity="CustomFieldType")
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Type", "empty_option":"Choisir le type"})
     */
    protected $type;

    /**
     * @ORM\Column(type="integer")
     * @Gedmo\SortablePosition
     */
    protected $place;

    /**
     * @ORM\Column(type="text")
     * @Annotation\Type("Zend\Form\Element\Textarea")
     * @Annotation\Required(false)
     * @Annotation\Options({"label":"Valeur par défaut"})
     * Stores default value, for example for select customtype
     */
    protected $defaultvalue;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required(false)
     * @Annotation\Options({"label":"Popup d'aide"})
     */
    protected $tooltip;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Annotation\Type("Zend\Form\Element\Checkbox")
     * @Annotation\Required(false)
     * @Annotation\Options({"label":"Sélection multiple"})
     */
    protected $multiple = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Annotation\Type("Zend\Form\Element\Checkbox")
     * @Annotation\Required(false)
     * @Annotation\Options({"label":"Traçable"})
     */
    protected $traceable = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Annotation\Type("Zend\Form\Element\Checkbox")
     * @Annotation\Required(false)
     * @Annotation\Options({"label":"Jalon"})
     */
    protected $milestone = false;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Annotation\Type("Zend\Form\Element\Checkbox")
     * @Annotation\Required(false)
     * @Annotation\Options({"label":"Caché"})
     */
    protected $hidden = false;

    /**
     * @ORM\OneToMany(targetEntity="CustomFieldValue", mappedBy="customfield", cascade={"remove"})
     */
    protected $values;

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

    public function getType()
    {
        return $this->type;
    }

    public function setCategory($category)
    {
        $this->category = $category;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getPlace()
    {
        return $this->place;
    }

    public function setPlace($place)
    {
        $this->place = $place;
    }

    public function isMultiple()
    {
        return $this->multiple;
    }

    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;
    }

    public function isTraceable()
    {
        return $this->traceable;
    }

    public function setTraceable($traceable)
    {
        $this->traceable = $traceable;
    }
    
    public function setMilestone($milestone)
    {
        $this->milestone = $milestone;
    }

    public function isMilestone() {
        return $this->milestone;
    }

    public function getDefaultValue()
    {
        return $this->defaultvalue;
    }

    public function setDefaultValue($defaultvalue)
    {
        $this->defaultvalue = $defaultvalue;
    }

    public function getTooltip()
    {
        return $this->tooltip;
    }

    public function setTooltip($tooltip)
    {
        $this->tooltip = $tooltip;
    }

    public function isHidden() {
        return $this->hidden;
    }

    public function setHidden($hidden) {
        $this->hidden = $hidden;
    }

    public function getArrayCopy()
    {
        $object_vars = get_object_vars($this);
        $object_vars['category'] = ($this->category ? $this->category->getId() : null);
        return $object_vars;
    }
}