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
class BrouillageCategory extends Category
{

    /**
     * @ORM\Column(type="boolean")
     */
    protected $defaultbrouillagecategory = false;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $frequencyfield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $levelfield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $rnavfield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $distancefield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $azimutfield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $originfield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $typefield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $causebrouillagefield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $commentairebrouillagefield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $causeinterferencefield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $commentaireinterferencefield;

    public function isDefaultBrouillageCategory()
    {
        return $this->defaultbrouillagecategory;
    }

    public function setDefaultBrouillageCategory($default)
    {
        $this->defaultbrouillagecategory = $default;
    }

    public function getFrequencyField()
    {
        return $this->frequencyfield;
    }

    public function setFrequencyfield($frequencyfield)
    {
        $this->frequencyfield = $frequencyfield;
    }

    public function getLevelField()
    {
        return $this->levelfield;
    }

    public function setLevelField($levelfield)
    {
        $this->levelfield = $levelfield;
    }

    public function setRnavField($rnavfield)
    {
        $this->rnavfield = $rnavfield;
    }

    public function getRnavField()
    {
        return $this->rnavfield;
    }

    public function getDistanceField()
    {
        return $this->distancefield;
    }

    public function setDistanceField($distancefield)
    {
        $this->distancefield = $distancefield;
    }

    public function getAzimutField()
    {
        return $this->azimutfield;
    }

    public function setAzimutField($azimutfield)
    {
        $this->azimutfield = $azimutfield;
    }

    public function setOriginField($originfield)
    {
        $this->originfield = $originfield;
    }

    public function getOriginField()
    {
        return $this->originfield;
    }

    public function setTypeField($typefield)
    {
        $this->typefield = $typefield;
    }

    public function getTypeField()
    {
        return $this->typefield;
    }

    public function getCauseBrouillageField()
    {
        return $this->causebrouillagefield;
    }

    public function setCauseBrouillageField($causebrouillagefield)
    {
        $this->causebrouillagefield = $causebrouillagefield;
    }

    public function getCauseInterferenceField()
    {
        return $this->causeinterferencefield;
    }

    public function setCauseInterferenceField($causeinterferencefield)
    {
        $this->causeinterferencefield = $causeinterferencefield;
    }

    public function getCommentaireBrouillageField()
    {
        return $this->commentairebrouillagefield;
    }

    public function setCommentaireBrouillageField($commentairebrouillagefield)
    {
        $this->commentairebrouillagefield = $commentairebrouillagefield;
    }

    public function getCommentaireInterferenceField()
    {
        return $this->commentaireinterferencefield;
    }

    public function setCommentaireInterferenceField($commentaireinterferencefield)
    {
        $this->commentaireinterferencefield = $commentaireinterferencefield;
    }
}