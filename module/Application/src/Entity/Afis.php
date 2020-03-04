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
use Laminas\Form\Annotation;
use Core\Entity\TemporaryResource;
use Application\Entity\Organisation;
/**
 * @ORM\Entity(repositoryClass="Application\Repository\ExtendedRepository")
 * @ORM\Table(name="afis")
 *
 * @author Loïc Perrin
 *
 */
class Afis extends TemporaryResource
{
    //const DEFAULT_STATE = 0;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Annotation\Type("Laminas\Form\Element\Hidden")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=4, unique=true, nullable=false)
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Options({"label":"Code OACI :"})
     * @Annotation\Required(True)
     * @Annotation\Validator({"name": "StringLength", "options": {"min": 4, "max": 4}})
     */
    protected $code;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Options({"label":"Nom Long :"})
     */
    protected $name;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Organisation")
     * @ORM\JoinColumn(nullable=false)
     * @Annotation\Type("Laminas\Form\Element\Select")
     * @Annotation\Required(True)
     * @Annotation\Options({"label":"Organisation :", "empty_option":"Choisir l'organisation"})
     */
    protected $organisation;

    /**
     * @ORM\Column(type="text")
     * @Annotation\Type("Laminas\Form\Element\Textarea")
     * @Annotation\Options({"label":"Horaires ouvertures :"})
     */
    protected $openedhours;

    /**
     * @ORM\Column(type="text")
     * @Annotation\Type("Laminas\Form\Element\Textarea")
     * @Annotation\Options({"label":"Contacts :"})
     */
    protected $contacts;

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        if (strlen($code) == 4)
        {
            $this->code = $code;
        }
    }

    public function setName($name)
    {
        $this->name = $name;
    }


    public function getOrganisation()
    {
        return $this->organisation;
    }

    public function setOrganisation(Organisation $organisation)
    {
        $this->organisation = $organisation;
    }

    public function getOpenedhours()
    {
        return $this->openedhours;
    }

    public function setOpenedhours($openedhours)
    {
        $this->openedhours = $openedhours;
    }

    public function getContacts()
    {
        return $this->contacts;
    }

    public function setContacts($contacts)
    {
        $this->contacts = $contacts;
    }

    public function getArrayCopy()
    {
        $object_vars = get_object_vars($this);
        $object_vars['organisation'] = ($this->organisation) ? $this->organisation->getId() : null;
        return $object_vars;
    }

    public function isValid()
    {
        $r = false;
        if (is_string($this->code) and strlen($this->code) == 4 and
            is_a($this->organisation, Organisation::class))
        {
            $r = true;
        }
        return $r;
    }

}
