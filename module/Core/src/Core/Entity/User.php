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
namespace Core\Entity;

use Zend\Form\Annotation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ZfcUser\Entity\UserInterface;
use ZfcRbac\Identity\IdentityInterface;
use Doctrine\ORM\PersistentCollection;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="users")
 *
 * @author Bruno Spyckerelle
 */
class User implements UserInterface, IdentityInterface
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Annotation\Type("Zend\Form\Element\Hidden")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Utilisateur :"})
     */
    protected $username;

    /**
     * @ORM\Column(type="string", unique=true, length=255)
     * @Annotation\Type("Zend\Form\Element\Email")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Email :"})
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"false"})
     * @Annotation\Options({"label":"Nom complet :"})
     */
    protected $displayName;

    /**
     * @ORM\Column(type="string", length=128)
     * @Annotation\Type("Zend\Form\Element\Password")
     * @Annotation\Options({"label":"Mot de passe :"})
     * @Annotation\Attributes({"data-rule-minlength":"6"})
     * @Annotation\Required({"required":"true"})
     * @Annotation\Validator({"name":"StringLength", "options":{"min":"6"}})
     */
    protected $password;

    /**
     */
    protected $state;

    /**
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="users")
     * @ORM\JoinTable(name="users_roles",
     *                joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     * )
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required(false)
     * @Annotation\Attributes({"multiple":true})
     * @Annotation\Options({"label":"Rôles :"})
     */
    protected $userroles;

    /**
     * @ORM\OneToMany(targetEntity="Application\Entity\Event", mappedBy="author", cascade={"detach"})
     */
    protected $events;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\Organisation", inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required(true)
     * @Annotation\Options({"label":"Organisation :", "empty_option":"Choisir l'organisation"})
     */
    protected $organisation;

    /**
     * @ORM\ManyToOne(targetEntity="Application\Entity\QualificationZone")
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required(false)
     * @Annotation\Options({"label":"Zone de qualification :", "empty_option":"Facultatif"})
     */
    protected $zone;

    public function __construct()
    {
        $this->userroles = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id.
     *
     * @param int $id            
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = (int) $id;
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set username.
     *
     * @param string $username            
     *
     * @return void
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email.
     *
     * @param string $email            
     *
     * @return void
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get displayName.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * Set displayName.
     *
     * @param string $displayName            
     *
     * @return void
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set password.
     *
     * @param string $password            
     *
     * @return void
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Get state.
     *
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set state.
     *
     * @param int $state            
     *
     * @return void
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    public function getOrganisation()
    {
        return $this->organisation;
    }

    public function setOrganisation($organisation)
    {
        $this->organisation = $organisation;
    }

    public function getZone()
    {
        return $this->zone;
    }

    public function setZone($zone)
    {
        $this->zone = $zone;
    }

    /**
     * Get role.
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->userroles->getValues();
    }

    /**
     * Add a role to the user.
     *
     * @param Role $role            
     *
     * @return void
     */
    public function addRole($role)
    {
        $this->userroles[] = $role;
    }

    /**
     *
     * @param PersistentCollection $roles            
     * @return self
     */
    public function setRoles(PersistentCollection $roles)
    {
        $this->userroles = $roles;
        
        return $this;
    }

    public function addUserroles($roles)
    {
        foreach ($roles as $role) {
            $this->userroles->add($role);
        }
    }

    public function removeUserroles($roles)
    {
        foreach ($roles as $role) {
            $this->userroles->removeElement($role);
        }
    }

    public function setUserroles($roles)
    {
        $this->userroles = $roles;
    }

    public function getUserroles()
    {
        return $this->userroles;
    }

    public function hasRole($rolename)
    {
        foreach ($this->userroles as $role) {
            if ($role->containsRole($rolename)) {
                return true;
            }
        }
        return false;
    }

    public function getArrayCopy()
    {
        $object_vars = get_object_vars($this);
        $roles = array();
        foreach ($this->userroles as $role) {
            $roles[] = $role->getId();
        }
        $object_vars['userroles'] = $roles;
        $object_vars['organisation'] = $this->organisation->getId();
        $object_vars['zone'] = ($this->zone ? $this->zone->getId() : null);
        return $object_vars;
    }
}
