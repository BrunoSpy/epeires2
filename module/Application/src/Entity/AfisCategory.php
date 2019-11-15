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
class AfisCategory extends Category
{

    /**
     * @ORM\Column(type="boolean")
     */
    protected $defaultafiscategory = false;

    /**
     * Ref to the field used to store the state of an afis
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $statefield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $afisfield;

    public function isDefaultAfisCategory()
    {
        return $this->defaultafiscategory;
    }

    public function setDefaultAfisCategory($default)
    {
        $this->defaultafiscategory = $default;
    }

    public function getStatefield()
    {
        return $this->statefield;
    }

    public function setStatefield($statefield)
    {
        $this->statefield = $statefield;
    }

    public function getAfisfield()
    {
        return $this->afisfield;
    }

    public function setAfisfield($afisfield)
    {
        $this->afisfield = $afisfield;
    }
}