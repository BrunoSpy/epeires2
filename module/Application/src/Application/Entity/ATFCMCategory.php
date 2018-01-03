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

use Zend\Form\Annotation;
use Doctrine\ORM\Mapping as ORM;


/**
 * Class ATFCMCategory
 * @package Application\Entity
 * @license AGPL3
 * @author Bruno Spyckerelle
 *
 * @ORM\Entity(repositoryClass="Application\Repository\CategoryRepository")
 */
class ATFCMCategory extends Category
{

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Options({"label":"TVs (séparés par une virgule) :"})
     * TVs to fetch
     */
    protected $tvs;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $reasonField;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $internalId;

    public function setTvs($tvs) {
        $this->tvs = $tvs;
    }

    public function getTvs() {
        return $this->tvs;
    }

    public function setInternalId($id) {
        $this->internalId = $id;
    }

    public function getInternalId() {
        return $this->internalId;
    }

    public function getReasonField()
    {
        return $this->reasonField;
    }

    public function setReasonField($reason)
    {
        $this->reasonField = $reason;
    }

    public function getArrayCopy()
    {
        $object_vars = array_merge(get_object_vars($this), parent::getArrayCopy());
        return $object_vars;
    }

}