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
 * @ORM\Entity(repositoryClass="Application\Repository\CategoryRepository")
 *
 * @author Loïc Perrin
 *        
 */
class FlightPlanCategory extends Category
{
    CONST CLASS_ALERTS = [
        "",
        "info",
        "warning",
        "danger"
    ];
    /**
     * @ORM\Column(type="boolean")
     */
    protected $defaultflightplancategory = false;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $aircraftidfield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $typeavionfield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $startfield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $destinationfield;
    
    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $estimatedtimeofarrivalfield;

    public function isDefaultFlightPlanCategory()
    {
        return $this->defaultflightplancategory;
    }

    public function setDefaultFlightPlanCategory($default)
    {
        $this->defaultflightplancategory = $default;
    }

    public function getAircraftidfield()
    {
        return $this->aircraftidfield;
    }

    public function setAircraftidfield($aircraftid)
    {
        $this->aircraftidfield = $aircraftid;
    }

    public function getTypeavionfield()
    {
        return $this->typeavionfield;
    }

    public function setTypeavionfield($typeavion)
    {
        $this->typeavionfield = $typeavion;
    }

    public function getDestinationfield()
    {
        return $this->destinationfield;
    }

    public function setDestinationfield($destination)
    {
        $this->destinationfield = $destination;
    }

    public function getStartfield()
    {
        return $this->startfield;
    }

    public function setStartfield($start)
    {
        $this->startfield = $start;
    }

    public function getEstimatedtimeofarrivalfield()
    {
        return $this->estimatedtimeofarrivalfield;
    }

    public function setEstimatedtimeofarrivalfield($estimatedtimeofarrival)
    {
        $this->estimatedtimeofarrivalfield = $estimatedtimeofarrival;
    }  
}