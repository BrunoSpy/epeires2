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

/**
 * @ORM\Entity(repositoryClass="Application\Repository\ExtendedRepository")
 * @ORM\Table(name="briefings")
 * @ORM\HasLifecycleCallbacks
 *
 * @author Bruno Spyckerelle
 *
 */
class Briefing
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Organisation")
     * @Annotation\Type("Laminas\Form\Element\Select")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Organisation :", "empty_option":"Choisir l'organisation"})
     */
    protected $organisation;

    /**
     * @ORM\Column(type="text")
     */
    protected $content;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Annotation\Type("Laminas\Form\Element\DateTime")
     * @Annotation\Required(false)
     * @Annotation\Options({"label":"Fin", "format" : "d-m-Y H:i"})
     * @Annotation\Attributes({"class":"datetime"})
     */
    protected $validAfter = null;

    /**
     * @ORM\PostLoad
     */
    public function doCorrectUTC()
    {
        // les dates sont stockées sans information de timezone, on considère par convention qu'elles sont en UTC
        // mais à la création php les crée en temps local, il faut donc les corriger
        if ($this->validAfter) {
            $offset = $this->validAfter->getTimezone()->getOffset($this->validAfter);
            $this->validAfter->setTimezone(new \DateTimeZone("UTC"));
            $this->validAfter->add(new \DateInterval("PT" . $offset . "S"));
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;
    }

    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @param mixed $validAfter
     */
    public function setValidAfter($validAfter)
    {
        $this->validAfter = $validAfter;
    }

    /**
     * @return mixed
     */
    public function getValidAfter()
    {
        return $this->validAfter;
    }
}