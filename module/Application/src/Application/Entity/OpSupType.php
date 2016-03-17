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
 * @ORM\Table(name="opsuptypes")
 * @Gedmo\Loggable(logEntryClass="Application\Entity\Log")
 *
 * @author Bruno Spyckerelle
 *        
 */
class OpSupType
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Annotation\Type("Zend\Form\Element\Hidden")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom :"})
     */
    protected $name;

    /**
     * @ORM\Column(type="string")
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom court :"})
     */
    protected $shortname;

    /**
     * @ORM\ManyToMany(targetEntity="Core\Entity\Role", inversedBy="opsuptype")
     * @ORM\JoinTable(name="roles_opsuptypes")
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required(false)
     * @Annotation\Attributes({"multiple":true})
     * @Annotation\Options({"label":"Affiché pour :"})
     */
    protected $roles;

    /**
     * @ORM\OneToMany(targetEntity="OperationalSupervisor", mappedBy="type", cascade={"remove"})
     */
    protected $opsups;

    /**
     * OpSupType constructor.
     */
    public function __construct()
    {
        $this->roles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->opsups = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setShortname($shortname)
    {
        $this->shortname = $shortname;
    }

    public function getShortname()
    {
        return $this->shortname;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    public function addRoles(Collection $roles)
    {
        foreach ($roles as $role) {
            $this->roles->add($role);
        }
    }

    public function removeRoles(Collection $roles)
    {
        foreach ($roles as $role) {
            $this->roles->removeElement($role);
        }
    }

    public function getRoleNames()
    {
        $roles = $this->getRoles();
        $names = array();
        foreach ($roles as $role) {
            $names[] = $role->getName();
        }
        return $names;
    }

    public function getArrayCopy()
    {
        $object_vars = get_object_vars($this);
        $roles = array();
        foreach ($this->roles as $role) {
            $roles[] = $role->getId();
        }
        $object_vars['roles'] = $roles;
        return $object_vars;
    }
}