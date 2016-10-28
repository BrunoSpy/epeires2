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
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * @ORM\Table(name="sectorsgroupsrelations")
 * @ORM\Entity(repositoryClass="Application\Repository\SectorsGroupsRepository")
 */
class SectorsGroupsRelation {

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Sector", inversedBy="sectorsgroupsrelations")
     */
    protected $sector;

    /**
     * @ORM\ManyToOne(targetEntity="SectorGroup", inversedBy="sectorsgroupsrelations")
     */
    protected $sectorgroup;

    /**
     * Position of a sector in a sectorgroup
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $place;
    
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Sector $sector
     */
    public function setSector(Sector $sector)
    {
        $this->sector = $sector;
    }

    /**
     * @return SectorGroup
     */
    public function getSector()
    {
        return $this->sector;
    }

    /**
     * @param SectorGroup $sectorgroup
     */
    public function setSectorgroup(SectorGroup $sectorgroup)
    {
        $this->sectorgroup = $sectorgroup;
    }

    /**
     * @return SectorGroup
     */
    public function getSectorgroup()
    {
        return $this->sectorgroup;
    }

    /**
     * @return int
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * @param int $place
     */
    public function setPlace($place)
    {
        $this->place = $place;
    }
}