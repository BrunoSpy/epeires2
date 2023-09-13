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
 * @ORM\Entity
 * @ORM\Table(name="milcategorylastupdates", uniqueConstraints={@ORM\UniqueConstraint(name="search_idx", columns={"category_id", "day"})})
 * @author Bruno Spyckerelle
 *
 * Class MilCategoryLastUpdate
 * @package Application\Entity
 */
class MilCategoryLastUpdate
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="MilCategory", inversedBy="lastUpdates")
     */
    private $category;

    /**
     * Stores last-modified
     * @ORM\Column(type="datetime")
     */
    private $lastUpdate;

    /**
     * Stores datetime of last call to server
     * @ORM\Column(type="datetime")
     */
    private $lastCall;

    /**
     * Stores Y-m-d day of data imported
     * Only one value per day : the last lastModified value received
     * @ORM\Column(type="string")
     */
    private $day;

    /**
     * @param \DateTime $lastUpdate
     * @param \DateTime $lastCall Last call to server
     * @param MilCategory $category
     * @param $day
     */
    public function __construct(\DateTime $lastUpdate, \DateTime $lastCall, MilCategory $category, $day)
    {
        $this->setLastCall($lastCall);
        $this->setLastUpdate($lastUpdate);
        $this->setCategory($category);
        $this->setDay($day);
    }

    /**
     * @ORM\PostLoad
     */
    public function doCorrectUTC()
    {
        if ($this->lastUpdate) {
            $offset = $this->lastUpdate->getTimezone()->getOffset($this->lastUpdate);
            $this->lastUpdate->setTimezone(new \DateTimeZone("UTC"));
            $this->lastUpdate->add(new \DateInterval("PT" . $offset . "S"));
        }
        if ($this->lastCall) {
            $offset = $this->lastCall->getTimezone()->getOffset($this->lastCall);
            $this->lastCall->setTimezone(new \DateTimeZone("UTC"));
            $this->lastCall->add(new \DateInterval("PT" . $offset . "S"));
        }
    }


    /**
     * @return MilCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory(MilCategory $category)
    {
        $this->category = $category;
    }

    public function getDay(){
        return $this->day;
    }

    public function setDay($day)
    {
        $this->day = $day;
    }

    /**
     * @param \DateTime $lastUpdate
     */
    public function setLastUpdate(\DateTime $lastUpdate): void
    {
        $this->lastUpdate = $lastUpdate;
    }

    /**
     * @return \DateTime
     */
    public function getLastUpdate(): \DateTime
    {
        return $this->lastUpdate;
    }

    /**
     * @return mixed
     */
    public function getLastCall()
    {
        return $this->lastCall;
    }

    /**
     * @param mixed $lastCall
     */
    public function setLastCall(\DateTime $lastCall): void
    {
        $this->lastCall = $lastCall;
    }
}
