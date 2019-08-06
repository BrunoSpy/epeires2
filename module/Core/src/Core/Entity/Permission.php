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

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Rbac\Permission\PermissionInterface;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="permissions")
 *
 * @author Bruno Spyckerelle
 */
class Permission implements PermissionInterface
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    protected $name;

    /**
     * @ORM\ManyToMany(targetEntity="Role", mappedBy="permissions", cascade={"persist"})
     */
    protected $roles;

    public function __construct($name = "")
    {
        $this->roles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setName($name);
    }

    /**
     *
     * @param int $id            
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @param string $name            
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function addRole($role)
    {
        $this->roles->add($role);
    }

    public function addRoles(Collection $roles)
    {
        $collection = new ArrayCollection();
        $collection->add($this);
        foreach ($roles as $role) {
            $role->addPermissions($collection);
            $this->roles->add($role);
        }
    }

    public function removeRoles(Collection $roles)
    {
        $collection = new ArrayCollection();
        $collection->add($this);
        foreach ($roles as $role) {
            $role->removePermissions($collection);
            $this->roles->removeElement($role);
        }
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function __toString()
    {
        return $this->name;
    }
}