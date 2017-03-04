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

/**
 * @ORM\Entity(repositoryClass="Application\Repository\CategoryRepository")
 *
 * @author Loïc Perrin
 *        
 */
class InterrogationPlanCategory extends Category
{ 
    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $typefield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $alertfield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    // protected $firsourcefield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    // protected $firdestfield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $latfield;
 
    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $longfield;


    // public function __construct()
    // {
    //     $this->fields = new ArrayCollection();
    // }

    // public function addFields(Collection $fields)
    // {
    //     foreach ($fields as $field) {
    //         $field->setInterrogationPlan($this);
    //         $this->fields->add($field);
    //     }
    // }

    // public function removeFields(Collection $fields)
    // {
    //     foreach ($fields as $field) {
    //         $field->setInterrogationPlan(null);
    //         $this->fields->removeElement($field);
    //     }
    // }

    // public function getArrayCopy()
    // {
    //     $object_vars = get_object_vars($this);
    //     $object_vars['fields'] = [];
    //     foreach($this->fields as $field) {
    //         $object_vars['fields'][] = $field->toArray();
    //     }
    //     return $object_vars;
    // }

    // public function getPdfFileName() {
    //     $date = $this->startTime->format('dmY');
    //     return '('.$this->id.')_PI_du_'.$date.'.pdf';
    // }

    // public function getPdfFilePath() {
    //     return 'data/interrogation-plans/'.$this->getPdfFileName();
    // }

    // public function isValid() {
    //     return true;
    // }


    // public function getFields()
    // {
    //     return $this->fields;
    // }

    // public function setStartTime($startTime)
    // {
    //     if(is_a($startTime, \DateTime::class)) {
    //         $this->startTime = $startTime;
    //     } else {
    //         $this->startTime = new \DateTime();
    //     }
    // }
      
    // public function getStartTime()
    // {
    //     return $this->startTime;
    // }

    // public function getId()
    // {
    //     return $this->id;
    // }

    public function getTypeField()
    {
        return $this->typefield;
    }

    public function setTypeField($typefield)
    {
        $this->typefield = $typefield;
    }

    // public function getFirsourcefield()
    // {
    //     return $this->firsourcefield;
    // }

    // public function setFirsourcefield($firsourcefield)
    // {
    //     $this->firsourcefield = $firsourcefield;
    // }

    // public function getFirdestfield()
    // {
    //     return $this->firdestfield;
    // }

    // public function setFirdestfield($firdestfield)
    // {
    //     $this->firdestfield = $firdestfield;
    // }

    public function getLatField()
    {
        return $this->latfield;
    }

    public function setLatField($latfield)
    {
        $this->latfield = $latfield;
    }

    public function getLongField()
    {
        return $this->longfield;
    }

    public function setLongField($longfield)
    {
        $this->longfield = $longfield;
    }

    public function getAlertField()
    {
        return $this->alertfield;
    }

    public function setAlertField($alert)
    {
        $this->alertfield = $alert;
    }
}