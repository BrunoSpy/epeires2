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
 * @ORM\Table(name="organisations")
 *
 * @author Bruno Spyckerelle
 *        
 */
class Organisation
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", unique=true)
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom :"})
     */
    protected $name;

    /**
     * @ORM\Column(type="string", unique=true)
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom court :"})
     */
    protected $shortname;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Options({"label":"Nom complet :"})
     */
    protected $longname;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Laminas\Form\Element\Textarea")
     * @Annotation\Required(false)
     * @Annotation\Options({"label":"Adresse :"})
     */
    protected $address;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Options({"label":"Numéro tel IPO"})
     */
    protected $iponumber;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Options({"label":"Email IPO"})
     */
    protected $ipoemail;

    /**
     * @ORM\OneToMany(targetEntity="QualificationZone", mappedBy="organisation", cascade={"remove"})
     */
    protected $zones;

    /**
     * @ORM\OneToMany(targetEntity="Core\Entity\User", mappedBy="organisation")
     */
    protected $users;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Required(false)
     * @Annotation\Options({"label":"Emails : compte sortant"})
     */
    protected $emailAccount;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Annotation\Type("Laminas\Form\Element\Password")
     * @Annotation\Attributes({"data-rule-minlength":"6"})
     * @Annotation\Required(false)
     * @Annotation\Options({"label":"Emails : mot de passe du compte sortant"})
     */
    protected $emailPassword;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Annotation\Type("Laminas\Form\Element\Text")
     * @Annotation\Required(false)
     * @Annotation\Options({"label":"Emails : Adresse d'envoi"})
     * @Annotation\Attributes({"placeholder":"Si compte Amelia, doit être l'adresse email du compte sortant"})
     */
    protected $emailFrom;


    public function __construct()
    {
        $this->zones = new \Doctrine\Common\Collections\ArrayCollection();
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getShortname()
    {
        return $this->shortname;
    }

    public function getLongname()
    {
        return $this->longname;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setIpoNumber($ipo)
    {
        $this->iponumber = $ipo;
    }

    public function getIpoNumber()
    {
        return $this->iponumber;
    }

    public function setIpoEmail($ipoemail)
    {
        $this->ipoemail = $ipoemail;
    }

    public function getIpoEmail()
    {
        return $this->ipoemail;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setShortname($shortname)
    {
        $this->shortname = $shortname;
    }

    public function setLongname($longname)
    {
        $this->longname = $longname;
    }

    public function setAddress($adress)
    {
        $this->address = $adress;
    }

    public function getZones()
    {
        return $this->zones;
    }

    /**
     * @param mixed $emailPassword
     */
    public function setEmailPassword($emailPassword): void
    {
        $this->emailPassword = $emailPassword;
    }

    /**
     * @return mixed
     */
    public function getEmailPassword()
    {
        return $this->emailPassword;
    }

    /**
     * @return mixed
     */
    public function getEmailAccount()
    {
        return $this->emailAccount;
    }

    /**
     * @param mixed $emailAccount
     */
    public function setEmailAccount($emailAccount): void
    {
        $this->emailAccount = $emailAccount;
    }

    /**
     * @return mixed
     */
    public function getEmailFrom()
    {
        return $this->emailFrom;
    }

    /**
     * @param mixed $emailFrom
     */
    public function setEmailFrom($emailFrom): void
    {
        $this->emailFrom = $emailFrom;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}