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
 * @ORM\Entity(repositoryClass="Application\Repository\BtivRepository")
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
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     */
    protected $code;

    /**
     * @ORM\Column(type="float")
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     */
    protected $latitude;
 
     /**
     * @ORM\Column(type="float")
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     */
    protected $longitude;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Textarea")
     * @Annotation\Required({"required":"false"})
     */
    protected $comment;  

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Annotation\Type("Zend\Form\Element\DateTime")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Attributes({"class":"datetime"})
     */
    protected $intTime;
    /** 
     * @ORM\ManyToOne(targetEntity="InterrogationPlan", inversedBy="fields")
     * @ORM\JoinColumn(name="interplan_id", referencedColumnName="id")
     */
    protected $interrogationPlan;

    public function __construct($p) {
        if(is_array($p)) {
            foreach($p as $prop => $value) {
                $this->{"set".ucfirst($prop)}($value);
            }
        }
    }

    public function isValid() {
        if($this->name && $this->code && $this->latitude && $this->longitude && $this->intTime) return true;
    }

    public function setIntTime($intTime)
    {
        if(is_a($intTime, \DateTime::class)) {
            $this->intTime = $intTime;
        } else {
            $this->intTime = (new \DateTime())->setTimeStamp((int) $intTime);
        }
    }

    public function getIntTime()
    {
        return $this->intTime;
    }

    public function setInterrogationPlan(InterrogationPlan $interrogationPlan = null)
    {
        $this->interrogationPlan = $interrogationPlan;
    }

    public function getInterrogationPlan()
    {
        return $this->interrogationPlan;
    }

    public function toArray()
    {
        return get_object_vars($this);
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

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function setLatitude($latitude)
    {
        $this->latitude = (float) $latitude;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    public function setLongitude($longitude)
    {
        $this->longitude = (float) $longitude;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;
    }

}