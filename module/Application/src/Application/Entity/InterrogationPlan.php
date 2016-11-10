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
     * @Annotation\Options({"label":"Type :","value_options" : {"0":"PIO", "1":"PIA"}})
     */
    protected $type;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Type d'alerte :","value_options" : {"0":"INERFA", "1":"ALERTFA", "2":"DETRESSFA"}})
     */
    protected $typeAlerte;

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

     * @ORM\OneToMany(targetEntity="Field", mappedBy="interrogationPlan", cascade={"persist"})
     * @Annotation\Type("Zend\Form\Element\Collection")
     * @Annotation\Required({"required":"false"})
     */   
    protected $fields;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
    }

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

    public function getTypeAlerte()
    {
        return $this->typeAlerte;
    }

    public function setTypeAlerte($typeAlerte)
    {
        $this->typeAlerte = $typeAlerte;
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

    public function addFields(Collection $fields)
    {
        foreach ($fields as $field) {
            $field->setInterrogationPlan($this);
            $this->fields->add($field);
        }
    }

    public function removeFields(Collection $fields)
    {
        foreach ($fields as $field) {
            $field->setInterrogationPlan(null);
            $this->fields->removeElement($field);
        }
    }

    public function getFields()
    {
        return $this->fields;
    }

    // public function getArrayCopy()
    // {
    //     $object_vars = get_object_vars($this);
    //     $object_vars['organisation'] = $this->organisation->getId();
    //     return $object_vars;
    // }
}