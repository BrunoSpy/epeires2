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
namespace FlightPlan\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;

/**
 * @ORM\Entity(repositoryClass="Application\Repository\ExtendedRepository")
 * @ORM\Table(name="flightplan")
 *
 * @author Loïc Perrin
 *        
 */
class FlightPlan
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
     * @Annotation\Options({"label":"Indicatif :"})
     */
    protected $aircraftid;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Terrain de Destination :"})
     */
    protected $destinationterrain;
    
    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Options({"label":"Terrain de Départ :"})
     */
    protected $startterrain;

    /**
     * Actions need an empty start date at creation
     * @ORM\Column(type="datetime", nullable=true)
     * @Annotation\Type("Zend\Form\Element\DateTime")
     * @Annotation\Required(false)
     * @Annotation\Options({"label":"Heure d'arrivée :", "format" : "d-m-Y H:i"})
     * @Annotation\Attributes({"class":"datetime"})
     */
    /*@Gedmo\Versioned
     */
    protected $timeofarrival;
    
    /**
     * Actions need an empty start date at creation
     * @ORM\Column(type="datetime", nullable=true)
     * @Annotation\Type("Zend\Form\Element\DateTime")
     * @Annotation\Required(false)
     * @Annotation\Options({"label":"Heure estimée d'arrivée :", "format" : "d-m-Y H:i"})
     * @Annotation\Attributes({"class":"datetime"})
     */
    /*@Gedmo\Versioned
     */
    protected $estimatedtimeofarrival;
    
    public function getId() {
        return $this->id;
    }

    public function getAircraftid() {
        return $this->aircraftid;
    }

    public function getStartterrain() {
        return $this->startterrain;
    }

    public function getDestinationterrain() {
        return $this->destinationterrain;
    }

    public function getTimeofarrival() {
        return $this->timeofarrival;
    }

    public function getEstimatedtimeofarrival() {
        return $this->estimatedtimeofarrival;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setAircraftid($aircraftid) {
        $this->aircraftid = $aircraftid;
    }

    public function setStartterrain($startterrain) {
        $this->startterrain = $startterrain;
    }

    public function setDestinationterrain($destinationterrain) {
        $this->destinationterrain = $destinationterrain;
    }

    public function setTimeofarrival($timeofarrival) {
        $this->timeofarrival = $timeofarrival;
    }

    public function setEstimatedtimeofarrival($estimatedtimeofarrival) {
        $this->estimatedtimeofarrival = $estimatedtimeofarrival;
    }
    
    public function getArrayCopy()
    {
        $object_vars = get_object_vars($this);
        //$object_vars['organisation'] = $this->organisation->getId();
        return $object_vars;
    }

    public function isValid(){
        $r = false;
        if (is_int($this->id) and
            is_string($this->aircraftid) and
            is_string($this->startterrain) and
            is_string($this->destinationterrain)) $r = true;
        return $r;
    }
}