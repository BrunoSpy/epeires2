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

    const NMB2B = "nmb2b";
    const MAPD = "mapd";

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Options({"label":"Zones associées :"})
     * @Annotation\Attributes({"placeholder":"Ex : /LFTSA43[AB]/"})
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
     * @ORM\Column(type="string", nullable=true)
     * @Annotation\Required(false)
     * @Annotation\Type("Laminas\Form\Element\Select")
     * @Annotation\Options({"label":"Actualiser avec :"})
     */
    protected $origin;

    /**
     * @ORM\OneToMany(targetEntity="MilCategoryLastUpdate", mappedBy="category")
     */
    protected $lastUpdates;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $upperLevelField;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $lowerLevelField;

    public function __construct(){
        parent::__construct();
        $this->lastUpdates = new ArrayCollection();
    }

    /**
     * @param mixed $origin
     */
    public function setOrigin($origin): void
    {
        $this->origin = $origin;
    }

    /**
     * @return mixed
     */
    public function getOrigin()
    {
        return $this->origin;
    }
/*
    public function addLastUpdate(\DateTime $date)
    {
        $day = $date->format('!Y-m-d');
        $lastUpdate = $this->lastUpdates->filter(function(MilCategoryLastUpdate $update) use ($day) {
            return strcmp($update->getDay(), $day) ==0 ;
        });
        if($lastUpdate->isEmpty()) {

        }
    }
*/

    public function addLastUpdates(Collection $lastUpdates)
    {
        foreach ($lastUpdates as $u){
            $this->lastUpdates->add($u);
        }
    }

    public function removeLastUpdates(Collection $lastUpdates)
    {
        foreach ($lastUpdates as $u)
        {
            $this->lastUpdates->removeElement($u);
        }
    }

    public function setLastUpdates($lastUpdates)
    {
        $this->lastUpdates = $lastUpdates;
    }

    public function getLastUpdates()
    {
        return $this->lastUpdates;
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