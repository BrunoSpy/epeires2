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
 */
class ActionCategory extends Category
{

    /**
     * Ref to the field used to store the state of a radar
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $namefield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $textfield;

    public function getNamefield()
    {
        return $this->namefield;
    }

    public function setNamefield($namefield)
    {
        $this->namefield = $namefield;
    }

    public function getTextfield()
    {
        return $this->textfield;
    }

    public function setTextfield($textfield)
    {
        $this->textfield = $textfield;
    }
}