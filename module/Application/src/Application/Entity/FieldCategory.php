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
class FieldCategory extends Category
{
    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $namefield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $codefield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $latfield;
 
    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $longfield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    // protected $intplanfield;


    public function getNameField()
    {
        return $this->namefield;
    }

    public function setNameField($namefield)
    {
        $this->namefield = $namefield;
    }

    public function getCodeField()
    {
        return $this->codefield;
    }

    public function setCodeField($codefield)
    {
        $this->codefield = $codefield;
    }

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

    // public function getIntPlanField()
    // {
    //     return $this->intplanfield;
    // }

    // public function setIntPlanField($intplanfield)
    // {
    //     $this->intplanfield = $intplanfield;
    // }

}