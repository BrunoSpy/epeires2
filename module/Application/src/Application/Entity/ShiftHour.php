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

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Zend\Form\Annotation;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="Application\Repository\ExtendedRepository")
 * @ORM\Table(name="shifthours")
 * @Gedmo\Loggable(logEntryClass="Application\Entity\Log")
 * @ORM\HasLifecycleCallbacks
 * @author Bruno Spyckerelle
 *
 */
class ShiftHour
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Annotation\Type("Zend\Form\Element\Hidden")
     */
    protected $id;


    /** @ORM\ManyToOne(targetEntity="OpSupType")
     * @ORM\JoinColumn(nullable=false)
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required(true)
     * @Annotation\Options({"label":"Type de superviseur"})
     */
    protected $opsuptype;


    /** 
     * @ORM\ManyToOne(targetEntity="QualificationZone")
     * @ORM\JoinColumn(nullable=true)
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required(false)
     * @Annotation\Options({"label":"Zone de qualification","empty_option":"Facultatif"})
     */
    protected $qualificationzone;


    /**
     * @ORM\Column(type="time")
     * @Annotation\Type("Zend\Form\Element\Text") //type=time not supported by Firefox...
     * @Annotation\Required(true)
     * @Annotation\Options({"label":"Heure", "format" : "H:i"})
     * @Annotation\Attributes({"placeholder":"Heure locale Europe/Paris"})
     */
    protected $hour;

    public function getId()
    {
        return $this->id;
    }

    public function getOpsuptype()
    {
        return $this->opsuptype;
    }

    public function setOpsuptype($opsuptype)
    {
        $this->opsuptype = $opsuptype;
    }

    public function getQualificationzone()
    {
        return $this->qualificationzone;
    }

    public function setQualificationzone($qualificationzone)
    {
        $this->qualificationzone = $qualificationzone;
    }

    public function getHour()
    {
        $datetime = new \DateTime();
        $datetime->setTime($this->hour->format('H'), $this->hour->format('i'));
        return $datetime;
    }

    public function getFormattedHour() {
        $formatterHour = \IntlDateFormatter::create(
            \Locale::getDefault(),
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::FULL,
            new \DateTimeZone('Europe/Paris'),
            \IntlDateFormatter::GREGORIAN,
            'HH:mm'
        );
        return $formatterHour->format($this->getHour());
    }

    public function getFormattedHourUTC() {
        error_log(print_r($this->getHour(), true));
        $formatterHour = \IntlDateFormatter::create(
            \Locale::getDefault(),
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::FULL,
            'UTC',
            \IntlDateFormatter::GREGORIAN,
            'HH:mm'
        );
        return $formatterHour->format($this->getHour());
    }

    public function setHour($hour)
    {
        $this->hour = $hour;
    }

    /**
     * @ORM\PostLoad
     */
/*    public function doCorrectUTC()
    {
        // les dates sont stockées sans information de timezone, on considère par convention qu'elles sont en UTC
        // mais à la création php les crée en temps local, il faut donc les corriger
        if ($this->hour) {
            $offset = $this->hour->getTimezone()->getOffset($this->hour);
            $this->hour->setTimezone(new \DateTimeZone("UTC"));
            $this->hour->add(new \DateInterval("PT" . $offset . "S"));
        }
    }*/

    public function getArrayCopy()
    {
        $object_vars = get_object_vars($this);

        $object_vars['hour'] = $this->getFormattedHour();
        $object_vars['opsuptype'] = $this->opsuptype->getId();
        if($this->qualificationzone != null) {
            $object_vars['qualificationzone'] = $this->qualificationzone->getId();
        }
        return $object_vars;
    }
}