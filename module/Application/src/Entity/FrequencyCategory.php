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
 * @author Bruno Spyckerelle
 *        
 */
class FrequencyCategory extends Category
{

    /**
     * @ORM\Column(type="boolean")
     */
    protected $defaultfrequencycategory = false;

    /**
     * Ref to the field used to store the state of the frequency
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $statefield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $currentcovfield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $frequencyfield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $otherfrequencyfield;

    /** 
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $causefield;
    
    public function isDefaultFrequencyCategory()
    {
        return $this->defaultfrequencycategory;
    }

    public function setDefaultFrequencyCategory($default)
    {
        $this->defaultfrequencycategory = $default;
    }

    /**
     * True : unavailable
     * False : avalaible
     *
     * @return CustomField
     */
    public function getStateField()
    {
        return $this->statefield;
    }

    public function setStateField($statefield)
    {
        $this->statefield = $statefield;
    }

    public function getFrequencyField()
    {
        return $this->frequencyfield;
    }

    public function setFrequencyField($frequencyfield)
    {
        $this->frequencyfield = $frequencyfield;
    }

    /**
     * 0 : normale
     * 1 : secours
     */
    public function getCurrentAntennaField()
    {
        return $this->currentcovfield;
    }

    public function setCurrentAntennaField($currentcovfield)
    {
        $this->currentcovfield = $currentcovfield;
    }

    public function getOtherFrequencyField()
    {
        return $this->otherfrequencyfield;
    }

    public function setOtherFrequencyField($otherfrequencyfield)
    {
        $this->otherfrequencyfield = $otherfrequencyfield;
    }
    
    public function getCauseField()
    {
        return $this->causefield;
    }
    
    public function setCauseField($causefield)
    {
        $this->causefield = $causefield;
    }
}