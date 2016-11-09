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
 * @ORM\Entity(repositoryClass="Application\Repository\ExtendedRepository")
 * @ORM\Table(name="interplan")
 *
 * @author Loïc Perrin
 *        
 */
class InterrogationPlan
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Type :"})
     */
    protected $type;

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
     * @Annotation\Required({"required":"false"})
     * @Annotation\Options({"label":"FIR Source :"})
     */
    protected $firSource;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"false"})
     * @Annotation\Options({"label":"FIR Dest :"})
     */
    protected $firDest;

    /**
     * @ORM\OneToMany(targetEntity="Field", mappedBy="interrogationPlan")
     * @Annotation\Required({"required":"false"})
     */   
    protected $fields;

    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getFirSource()
    {
        return $this->firSource;
    }

    public function setFirSource($firSource)
    {
        $this->firSource = $firSource;
    }

    public function getFirDest()
    {
        return $this->firDest;
    }

    public function setFirDest($firDest)
    {
        $this->firDest = $firDest;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    // public function getArrayCopy()
    // {
    //     $object_vars = get_object_vars($this);
    //     $object_vars['organisation'] = $this->organisation->getId();
    //     return $object_vars;
    // }
}