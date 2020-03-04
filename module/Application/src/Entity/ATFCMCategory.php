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
 * Class ATFCMCategory
 * @package Application\Entity
 * @license AGPL3
 * @author Bruno Spyckerelle
 *
 * @ORM\Entity(repositoryClass="Application\Repository\CategoryRepository")
 */
class ATFCMCategory extends Category
{

    const APPLIED = "APPLIED";
    const APPLYING = "APPLYING";
    const CANCELLED = "CANCELLED";
    const CANCELLING = "CANCELLING";
    const TERMINATED = "TERMINATED";

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Options({"label":"TVs (séparés par une virgule) :"})
     * @Annotation\Attributes({"placeholder":"Par défaut : LF* si le filtre est vide"})
     * TVs to fetch
     */
    protected $tvs;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Options({"label":"Filtre sur l'id, par défaut : aucun"})
     */
    protected $regex;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $reasonField;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $internalId;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $descriptionfield;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $normalRateField;

    /**
     * @ORM\OneToOne(targetEntity="CustomField")
     */
    protected $regulationStateField;

    /**
     * @ORM\Column(type="boolean")
     * @Annotation\Required(false)
     * @Annotation\Type("Laminas\Form\Element\Checkbox")
     * @Annotation\Options({"label":"Actualiser avec NM B2B :"})
     */
    protected $nmB2B = false;

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

    public function getDescriptionField(){
        return $this->descriptionfield;
    }

    public function setDescriptionField($field){
        $this->descriptionfield = $field;
    }

    public function setNormalRateField($field)
    {
        $this->normalRateField = $field;
    }

    public function getNormalRateField()
    {
        return $this->normalRateField;
    }

    public function getRegulationStateField()
    {
        return $this->regulationStateField;
    }

    public function setRegulationStateField($field)
    {
        $this->regulationStateField = $field;
    }

    public function setNMB2B($nmb2b)
    {
        $this->nmB2B = $nmb2b;
    }

    public function isNMB2B()
    {
        return $this->nmB2B;
    }

    public function setRegex($regex)
    {
        $this->regex = $regex;
    }

    public function getRegex()
    {
        return $this->regex;
    }

    public function getArrayCopy()
    {
        $object_vars = array_merge(get_object_vars($this), parent::getArrayCopy());
        return $object_vars;
    }

}