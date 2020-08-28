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
use Laminas\Form\Annotation;

/**
 * @ORM\Entity(repositoryClass="Application\Repository\CategoryRepository")
 *
 * @author Bruno Spyckerelle
 *        
 */
class SwitchObjectCategory extends Category implements StateCategoryInterface
{

    /**
     * Ref to the field used to store the state of a radar
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $statefield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $switchobjectfield;

    /**
     * @ORM\ManyToMany(targetEntity="SwitchObject", inversedBy="categories")
     * @ORM\JoinTable(name="switchobjects_categories")
     * @Annotation\Required(false)
     * @Annotation\Type("Laminas\Form\Element\Select")
     */
    protected $switchObjects;

    public function __construct()
    {
        parent::__construct();
        $this->switchObjects = new ArrayCollection();
    }

    public function getSwitchObjects()
    {
        return $this->switchObjects;
    }

    public function addSwitchObject(SwitchObject $so)
    {
        $this->switchObjects->add($so);
    }

    public function addSwitchObjects(Collection $switchobjects)
    {
        foreach ($switchobjects as $s) {
            $this->switchObjects->add($s);
        }
    }

    public function removeSwitchObjects(Collection $switchobjects)
    {
        foreach ($switchobjects as $s){
            $this->switchObjects->removeElement($s);
        }
    }

    public function clearSwitchObjects()
    {
        $this->switchObjects->clear();
    }

    public function getStateField() : CustomField
    {
        return $this->statefield;
    }

    public function setStateField(CustomField $statefield) : void
    {
        $this->statefield = $statefield;
    }

    public function getSwitchObjectField()
    {
        return $this->switchobjectfield;
    }

    public function setSwitchObjectfield($switchobjectfield)
    {
        $this->switchobjectfield = $switchobjectfield;
    }
}