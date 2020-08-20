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
use Core\Entity\TemporaryResource;
use Laminas\Form\Annotation;

/**
 * @ORM\Entity(repositoryClass="Application\Repository\ExtendedRepository")
 * @ORM\Table(name="switchobjects")
 *
 * @author Bruno Spyckerelle
 *        
 */
class SwitchObject extends TemporaryResource
{



    const RADAR = "radar";

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Organisation")
     * @ORM\JoinColumn(nullable=false)
     * @Annotation\Type("Laminas\Form\Element\Select")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Organisation :", "empty_option":"Choisir l'organisation"})
     */
    protected $organisation;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom :"})
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom abrégé :"})
     */
    protected $shortname;
    
    /**
     * @ORM\OneToOne(targetEntity="PredefinedEvent")
     */
    protected $model;

    /**
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="SwitchObject", inversedBy="children", cascade={"persist"})
     * @Annotation\Type("Laminas\Form\Element\Select")
     * @Annotation\Required(false)
     * @Annotation\Options({"label":"Evènement parent", "empty_option":"Choisir l'objet parent"})
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="SwitchObject", mappedBy="parent", cascade={"persist", "remove"})
     */
    protected $children;
    
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
    
    public function getModel()
    {
        return $this->model;
    }
    
    public function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    public function getArrayCopy()
    {
        $object_vars = get_object_vars($this);
        $object_vars['organisation'] = $this->organisation->getId();
        return $object_vars;
    }
}