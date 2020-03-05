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
 * @ORM\Entity(repositoryClass="Application\Repository\PredefinedEventRepository")
 *
 * @author Bruno Spyckerelle
 *        
 */
class PredefinedEvent extends AbstractEvent
{

    /**
     * @ORM\Column(type="string", unique=true, nullable=true, options={"collation":"utf8_bin"})
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Required(false)
     * @Annotation\Options({"label":"Nom :"})
     */
    protected $name;

    /**
     * @ORM\Column(type="boolean")
     * @Annotation\Type("Laminas\Form\Element\Checkbox")
     * @Annotation\Options({"label":"Liste :"})
     */
    protected $listable;

    /**
     * @ORM\Column(type="boolean")
     * @Annotation\Type("Laminas\Form\Element\Checkbox")
     * @Annotation\Options({"label":"Recherche :"})
     */
    protected $searchable;

    /**
     * @ORM\Column(type="boolean")
     * @Annotation\Type("Laminas\Form\Element\Checkbox")
     * @Annotation\Options({"label":"Programmé par défaut :"})
     */
    protected $programmed = false;

    /**
     * @ORM\Column(type="boolean")
     * @Annotation\Type("Laminas\Form\Element\Checkbox")
     * @Annotation\Options({"label":"Forcer affichage dans catégorie parente :"})
     */
    protected $forceroot = false;

    /**
     * @ORM\Column(type="integer")
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Required(false)
     * @Annotation\Options({"label":"Durée :"})
     * @Annotation\Attributes({"placeholder":"En minutes (facultatif)."})
     */
    protected $duration = -1;

    /**
     * @ORM\Column(type="boolean")
     * @Annotation\Type("Laminas\Form\Element\Checkbox")
     * @Annotation\Options({"label":"Accès rapide :"})
     * @Annotation\Required(false)
     */
    protected $quickaccess = false;

    public function __construct()
    {
        parent::__construct();
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function isQuickaccess()
    {
        return $this->quickaccess;
    }

    /**
     * @param bool $quick
     */
    public function setQuickAccess($quick)
    {
        $this->quickaccess = $quick;
    }

    public function isListable()
    {
        return $this->listable;
    }

    public function setListable($listable)
    {
        $this->listable = $listable;
    }

    public function isSearchable()
    {
        return $this->searchable;
    }

    public function setSearchable($searchable)
    {
        $this->searchable = $searchable;
    }

    public function isProgrammed()
    {
        return $this->programmed;
    }

    public function setProgrammed($programmed)
    {
        $this->programmed = $programmed;
    }

    public function setForceroot($forceroot)
    {
        $this->forceroot = $forceroot;
    }

    /**
     * @return boolean
     */
    public function isForceroot()
    {
        return $this->forceroot;
    }

    public function getPlace()
    {
        return $this->place;
    }

    public function setPlace($place)
    {
        $this->place = $place;
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    public function getDuration()
    {
        return $this->duration;
    }
}