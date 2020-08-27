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

use Laminas\Form\Annotation;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="tabs")
 *
 * @author Bruno Spyckerelle
 */
class Tab
{

    const TIMELINE = "timeline";
    const SWITCHLIST = "switchlist";

    const OPTIONS = array(self::TIMELINE, self::SWITCHLIST);

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", unique=true)
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom :"})
     */
    protected $name;

    /**
     * @ORM\Column(type="string", unique=true)
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom court :"})
     */
    protected $shortname;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Annotation\Type("Laminas\Form\Element\Number")
     * @Annotation\Attributes({"min":0})
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Place :"})
     * @Gedmo\SortablePosition
     */
    protected $place;

    /**
     * @ORM\ManyToMany(targetEntity="Core\Entity\Role", inversedBy="readtabs")
     * @ORM\JoinTable(name="roles_tabs_read")
     * @Annotation\Type("Laminas\Form\Element\Select")
     * @Annotation\Required(true)
     * @Annotation\Attributes({"multiple":true})
     * @Annotation\Options({"label":"Affiché pour :"})
     */
    protected $readroles;

    /**
     * Categories to be displayed
     * @ORM\ManyToMany(targetEntity="Category", inversedBy="tabs")
     * @Annotation\Type("Laminas\Form\Element\Select")
     * @Annotation\Required(true)
     * @Annotation\Attributes({"multiple":true})
     * @Annotation\Options({"label":"Catégories à afficher :"})
     */
    protected $categories;

    /**
     * Show only root categories
     * @ORM\Column(type="boolean")
     * @Annotation\Type("Laminas\Form\Element\Checkbox")
     * @Annotation\Options({"label":"Catégories racines seulement :"})
     */
    protected $onlyroot = false;
    
    /**
     * Default tab. Only one default tab allowed.
     * @ORM\Column(type="boolean")
     * @Annotation\Type("Laminas\Form\Element\Checkbox")
     * @Annotation\Options({"label":"Onglet principal :"})
     */
    protected $isDefault = false;

    /**
     * Type of tab : timeline or switchlist
     * @ORM\Column(type="string")
     * @Annotation\Type("Laminas\Form\Element\Select")
     * @Annotation\Required(true)
     * @Annotation\Options({"label":"Type d'onglet :"})
     */
    protected $type;

    /**
     * Used only if type=switchlist
     * @ORM\Column(type="boolean")
     * @Annotation\Required(false)
     * @Annotation\Type("Laminas\Form\Element\Checkbox")
     * @Annotation\Options({"label":"Affichage horizontal :"})
     */
    protected $horizontal = false;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->readroles = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function isHorizontal(): bool
    {
        return $this->horizontal;
    }

    /**
     * @param bool $horizontal
     */
    public function setHorizontal(bool $horizontal): void
    {
        $this->horizontal = $horizontal;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
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

    public function setShortName($name)
    {
        $this->shortname = $name;
    }

    public function setOnlyroot($only)
    {
        $this->onlyroot = $only;
    }

    public function isOnlyroot()
    {
        return $this->onlyroot;
    }

    public function isDefault() {
        return $this->isDefault;
    }
    
    public function setDefault($default) {
        $this->isDefault = $default;
    }
    
    public function getCategories()
    {
        return $this->categories;
    }

    public function addCategories(Collection $categories)
    {
        foreach ($categories as $cat) {
            $this->categories->add($cat);
        }
    }

    public function removeCategories(Collection $categories)
    {
        foreach ($categories as $cat) {
            $this->categories->removeElement($cat);
        }
    }

    public function getReadroles()
    {
        return $this->readroles;
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

    public function getReadRoleNames()
    {
        $readroles = $this->getReadroles();
        $names = array();
        foreach ($readroles as $role) {
            $names[] = $role->getName();
        }
        return $names;
    }

    /**
     * @return mixed
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * @param mixed $place
     */
    public function setPlace($place)
    {
        $this->place = $place;
    }

    public function getArrayCopy()
    {
        $object_vars = get_object_vars($this);
        $roles = array();
        foreach ($this->readroles as $role) {
            $roles[] = $role->getId();
        }
        $object_vars['readroles'] = $roles;
        $cats = array();
        foreach ($this->categories as $cat) {
            $cats[] = $cat->getId();
        }
        $object_vars['categories'] = $cats;
        return $object_vars;
    }
}