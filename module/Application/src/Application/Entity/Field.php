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
 * @ORM\Table(name="field")
 *
 * @author Loïc Perrin
 *        
 */
class Field
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Textarea")
     * @Annotation\Required({"required":"false"})
     */
    protected $comment;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     */
    protected $intTime;
    /** 
     * @ORM\ManyToOne(targetEntity="InterrogationPlan")
     * @ORM\JoinColumn(name="interplan_id", referencedColumnName="id")
     */
    protected $interrogationPlan;

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

    public function getComment()
    {
        return $this->comment;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    public function getIntTime()
    {
        return $this->intTime;
    }

    public function setIntTime($intTime)
    {
        $this->intTime = $intTime;
    }  
    // public function getArrayCopy()
    // {
    //     $object_vars = get_object_vars($this);
    //     $object_vars['organisation'] = $this->organisation->getId();
    //     return $object_vars;
    // }
}