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
    CONST TYPE_IP = [
        0 => "PIO",
        1 => "PIA"
    ];

    CONST TYPE_ALERT = [
        0 => "INERFA",
        1 => "ALERTFA",
        2 => "DETRESSFA"
    ];   

    CONST CLASS_ALERT = [
        0 => "info",
        1 => "warning",
        2 => "danger"
    ];   

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Annotation\Type("Zend\Form\Element\DateTime")
     * @Annotation\Required(false)
     * @Annotation\Attributes({"class":"datetime"})
     */
    protected $startTime;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required(true)
     * @Annotation\Options({"label":"Type :","value_options" : {"0":"PIO", "1":"PIA"}})
     */
    protected $type;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required(true)
     * @Annotation\Options({"label":"Type d'alerte :","value_options" : {"0":"INERFA", "1":"ALERTFA", "2":"DETRESSFA"}})
     */
    protected $typeAlerte;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Required(false)
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Options({"label":"FIR Source :"})
     */
    protected $firSource;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Required(false)  
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Options({"label":"FIR Dest :"})
     */
    protected $firDest;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Textarea")
     * @Annotation\Required(false)
     * @Annotation\Options({"label":"Commentaire"})
     */
    protected $comment; 

    /**
     * @ORM\Column(type="float")
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Attributes({"disabled":"disabled"})
     * @Annotation\Options({"label":"Latitude"})
     */
    protected $latitude;
 
     /**
     * @ORM\Column(type="float")
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Attributes({"disabled":"disabled"})
     * @Annotation\Options({"label":"Longitude"})
     */
    protected $longitude;

    /**
     * @ORM\OneToMany(targetEntity="Field", mappedBy="interrogationPlan", cascade={"persist"})
     * @Annotation\Required(true)  
     */   
    protected $fields;

    public function __construct()
    {
        $this->fields = new ArrayCollection();
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

    public function getArrayCopy()
    {
        $object_vars = get_object_vars($this);
        $object_vars['fields'] = [];
        foreach($this->fields as $field) {
            $object_vars['fields'][] = $field->toArray();
        }
        return $object_vars;
    }

    public function getPdfFileName() {
        $date = $this->startTime->format('dmY');
        return '('.$this->id.')_PI_du_'.$date.'.pdf';
    }

    public function getPdfFilePath() {
        return 'data/interrogation-plans/'.$this->getPdfFileName();
    }

    public function isValid() {
        return true;
    }


    public function getFields()
    {
        return $this->fields;
    }

    public function setStartTime($startTime)
    {
        if(is_a($startTime, \DateTime::class)) {
            $this->startTime = $startTime;
        } else {
            $this->startTime = new \DateTime();
        }
    }
      
    public function getStartTime()
    {
        return $this->startTime;
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

    public function getComment()
    {
        return $this->comment;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

}