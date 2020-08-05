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
 * @ORM\Entity(repositoryClass="Application\Repository\ExtendedRepository")
 * @ORM\HasLifecycleCallbacks
 * @author Bruno Spyckerelle
 *
 */
class SunriseSunset
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="date", unique=true)
     */
    protected $date;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $sunrise;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $sunset;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getSunrise() : \DateTime
    {
        return $this->sunrise;
    }

    /**
     * @param \DateTime $sunrise
     */
    public function setSunrise(\DateTime $sunrise): void
    {
        $this->sunrise = $sunrise;
    }

    /**
     * @return mixed
     */
    public function getSunset() : \DateTime
    {
        return $this->sunset;
    }

    /**
     * @param mixed $sunset
     */
    public function setSunset(\DateTime $sunset): void
    {
        $this->sunset = $sunset;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date): void
    {
        $this->date = $date;
    }

    public function getDate()
    {
        return $this->date;
    }

    /**
     * @ORM\PostLoad
     */
    public function doCorrectUTC()
    {
        if ($this->sunrise) {
            $offset = $this->sunrise->getTimezone()->getOffset($this->sunrise);
            $this->sunrise->setTimezone(new \DateTimeZone("UTC"));
            $this->sunrise->add(new \DateInterval("PT" . $offset . "S"));
        }
        if ($this->sunset) {
            $offset = $this->sunset->getTimezone()->getOffset($this->sunrise);
            $this->sunset->setTimezone(new \DateTimeZone("UTC"));
            $this->sunset->add(new \DateInterval("PT" . $offset . "S"));
        }
    }

}