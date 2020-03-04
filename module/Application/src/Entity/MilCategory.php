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

use Laminas\Form\Annotation;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Application\Repository\CategoryRepository")
 *
 * @author Bruno Spyckerelle
 *        
 */
class MilCategory extends Category
{

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Options({"label":"Zones associées :"})
     * Displayed zones, must be included in <$filter>*
     */
    protected $zonesRegex;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Options({"label":"Filtre d'import :"})
     * Filter applied at import
     */
    protected $filter;

    /**
     * @ORM\Column(type="boolean")
     * @Annotation\Required(false)
     * @Annotation\Type("Laminas\Form\Element\Checkbox")
     * @Annotation\Options({"label":"Actualiser avec NM B2B :"})
     */
    protected $nmB2B = false;

    /**
     * @ORM\Column(type="boolean")
     * @Annotation\Required(false)
     * @Annotation\Type("Laminas\Form\Element\Checkbox")
     * @Annotation\Options({"label":"Actualiser avec SIA (uniquement possible pour ZTBA) :"})
     */
    protected $sia = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $lastUpdateDate;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $lastUpdateSequence;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $upperLevelField;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $lowerLevelField;

    public function setNMB2B($nmb2b)
    {
        $this->nmB2B = $nmb2b;
    }

    public function isNMB2B()
    {
        return $this->nmB2B;
    }

    public function setSia($sia)
    {
        $this->sia = $sia;
    }

    public function isSia()
    {
        return $this->sia;
    }

    public function setLastUpdateDate($update)
    {
        $this->lastUpdateDate = $update;
    }

    public function getLastUpdateDate()
    {
        return $this->lastUpdateDate;
    }

    public function setLastUpdateSequence($sequence)
    {
        $this->lastUpdateSequence = $sequence;
    }

    public function getLastUpdateSequence()
    {
        $this->lastUpdateSequence;
    }

    public function setZonesRegex($regex)
    {
        $this->zonesRegex = $regex;
    }

    public function getZonesRegex()
    {
        return $this->zonesRegex;
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function setUpperLevelField($upperlevelfield)
    {
        $this->upperLevelField = $upperlevelfield;
    }

    public function getUpperLevelField()
    {
        return $this->upperLevelField;
    }

    public function setLowerLevelField($lowerlevelfield)
    {
        $this->lowerLevelField = $lowerlevelfield;
    }

    public function getLowerLevelField()
    {
        return $this->lowerLevelField;
    }

    public function getArrayCopy()
    {
        $object_vars = array_merge(get_object_vars($this), parent::getArrayCopy());
        return $object_vars;
    }
}