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

use Doctrine\ORM\PersistentCollection;
use Zend\Form\Annotation;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Rbac\Role\RoleInterface;
use Rbac\Role\HierarchicalRoleInterface;

/**
 * @ORM\Entity(repositoryClass="Application\Repository\ExtendedRepository")
 * @ORM\Table(name="roles")
 *
 * @author Bruno Spyckerelle
 */
class Role implements HierarchicalRoleInterface
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Annotation\Type("Zend\Form\Element\Hidden")
     *
     * @var int
     *
     */
    protected $id;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Role", inversedBy="children")
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required(false)
     * @Annotation\Attributes({"multiple":false})
     * @Annotation\Options({"label":"Parent :", "empty_option":"Rôle parent (facultatif)"})
     * 
     * @var Role
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Role", mappedBy="parent")
     */
    protected $children;

    /**
     *
     * @ORM\Column(type="string", length=32)
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom :"})
     * 
     * @var string
     */
    protected $name;

    /**
     *
     * @ORM\ManyToMany(targetEntity="Permission", inversedBy="roles")
     * @ORM\JoinTable(name="roles_permissions",
     *                joinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="permission_id", referencedColumnName="id")}
     * )
     * 
     * @var PersistentCollection
     */
    protected $permissions;

    /**
     * @ORM\ManyToMany(targetEntity="Application\Entity\Category", mappedBy="readroles")
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required(false)
     * @Annotation\Attributes({"multiple":"true"})
     * @Annotation\Options({"label":"Catégories accessibles :"})
     */
    protected $readcategories;

    /**
     * @ORM\ManyToMany(targetEntity="Application\Entity\Tab", mappedBy="readroles")
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required(false)
     * @Annotation\Attributes({"multiple":"true"})
     * @Annotation\Options({"label":"Onglets accessibles :"})
     */
    protected $readtabs;

    /**
     * @ORM\ManyToMany(targetEntity="Application\Entity\OpSupType", mappedBy="roles")
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required(false)
     * @Annotation\Attributes({"multiple":"true"})
     * @Annotation\Options({"label":"Types Opsup affichés :"})
     */
    protected $opsuptypes;

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="userroles", cascade={"detach"})
     */
    protected $users;

    public function __construct()
    {
        $this->permissions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->readcategories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->readtabs = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     *
     * @param int $id            
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
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

    /**
     *
     * @param Role $parent            
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     *
     * @return int|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function setChildren($children)
    {
        $this->children = $children;
    }

    public function addChildren($roles)
    {
        foreach ($roles as $role) {
            $this->children->add($role);
        }
    }

    public function removeChildren($roles)
    {
        foreach ($roles as $role) {
            $this->children->removeElement($role);
        }
    }

    public function hasChildren()
    {
        return ! empty($this->children);
    }

    /**
     *
     * @param PersistentCollection $permissions            
     * @return self
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
        return $this;
    }

    /**
     * Add permission to the role.
     *
     * @param
     *            $name
     * @return RoleInterface
     */
    public function addPermission($permission)
    {
        if (is_string($permission)) {
            $permission = new Permission($permission);
        }

        $this->permissions->add($permission);

        return $this;
    }

    public function addPermissions(Collection $permissions)
    {
        foreach ($permissions as $permission) {
            $this->permissions->add($permission);
        }
    }

    public function removePermissions(Collection $permissions)
    {
        foreach ($permissions as $permission) {
            $this->permissions->removeElement($permission);
        }
    }

    /**
     * Checks if a permission exists for this role or any child roles.
     *
     * @param string $name            
     * @return bool
     */
    public function hasPermission($name, $recursive = false)
    {
        foreach ($this->permissions as $permission) {
            if ($permission->getName() == $name) {
                return true;
            }
        }
        
        if ($recursive) {
            foreach ($this->getChildren() as $child) {
                if ($child->hasPermission($name)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function containsRole($rolename)
    {
        if ($this->getName() == $rolename) {
            return true;
        }
        foreach ($this->children as $child) {
            if ($child->containsRole($rolename)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add a child.
     *
     * @param RoleInterface|string $child            
     */
    public function addChild(RoleInterface $child)
    {
        $this->children->add($child);
    }

    public function getReadcategories()
    {
        return $this->readcategories;
    }

    public function addReadcategories(Collection $categories)
    {
        foreach ($categories as $cat) {
            $collection = new ArrayCollection();
            $collection->add($this);
            $cat->addReadroles($collection);
            $this->readcategories->add($cat);
        }
    }

    public function removeReadcategories(Collection $categories)
    {
        foreach ($categories as $cat) {
            $collection = new ArrayCollection();
            $collection->add($this);
            $cat->removeReadroles($collection);
            $this->readcategories->removeElement($cat);
        }
    }

    public function getReadtabs()
    {
        return $this->readtabs;
    }

    public function addReadtabs(Collection $tabs)
    {
        foreach ($tabs as $tab) {
            $collection = new ArrayCollection();
            $collection->add($this);
            $tab->addReadroles($collection);
            $this->readtabs->add($tab);
        }
    }

    public function removeReadtabs(Collection $tabs)
    {
        foreach ($tabs as $tab) {
            $collection = new ArrayCollection();
            $collection->add($this);
            $tab->removeReadroles($collection);
            $this->readtabs->removeElement($tab);
        }
    }

    public function getOpsuptypes()
    {
        return $this->opsuptypes;
    }

    public function addOpsuptypes(Collection $opsuptypes)
    {
        foreach($opsuptypes as $opsuptype){
            $collection = new ArrayCollection();
            $collection->add($this);
            $opsuptype->addRoles($collection);
            $this->opsuptypes->add($opsuptype);
        }
    }

    public function removeOpsuptypes(Collection $opsuptypes)
    {
        foreach($opsuptypes as $opsuptype){
            $collection = new ArrayCollection();
            $collection->add($this);
            $opsuptype->removeRoles($collection);
            $this->opsuptypes->removeElement($opsuptype);
        }
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getArrayCopy()
    {
        $object_vars = get_object_vars($this);
        if ($this->parent) {
            $object_vars['parent'] = $this->parent->getId();
        }
        $readcategories = array();
        foreach ($this->readcategories as $cat) {
            $readcategories[] = $cat->getId();
        }
        $object_vars['readcategories'] = $readcategories;
        $readtabs = array();
        foreach ($this->readtabs as $tab) {
            $readtabs[] = $tab->getId();
        }
        $object_vars['readtabs'] = $readtabs;
        return $object_vars;
    }
}
