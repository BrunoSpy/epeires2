<?php

namespace Core\Entity;


use Doctrine\ORM\PersistentCollection;
use RecursiveIterator;
use IteratorIterator;
use Zend\Form\Annotation;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Rbac\Role\RoleInterface;
use Rbac\Role\HierarchicalRoleInterface;

/** 
 * @ORM\Entity(repositoryClass="Application\Repository\ExtendedRepository")
 * @ORM\Table(name="roles")
 */
class Role implements HierarchicalRoleInterface{

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Annotation\Type("Zend\Form\Element\Hidden")
     */
    protected $id;

    /**
     * @var Role
     * @ORM\ManyToOne(targetEntity="Role", inversedBy="children")
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required(false)
	 * @Annotation\Attributes({"multiple":false})
	 * @Annotation\Options({"label":"Parent :", "empty_option":"Rôle parent (facultatif)"})
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Role", mappedBy="parent")
     */
    protected $children;

    /**
     * @var string
     * @ORM\Column(type="string", length=32)
     * @Annotation\Type("Zend\Form\Element\Text")
     * @Annotation\Required({"required":"true"})
     * @Annotation\Options({"label":"Nom :"})
     */
    protected $name;

    /**
     * @var PersistentCollection
     * @ORM\ManyToMany(targetEntity="Permission", inversedBy="roles")
     * @ORM\JoinTable(name="roles_permissions",
     *      joinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="permission_id", referencedColumnName="id")}
     *      )
     */
    protected $permissions;

    /**
     * @ORM\ManyToMany(targetEntity="Application\Entity\Category", mappedBy="readroles")
     */
    protected $readcategories;
    
    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="userroles", cascade={"detach"})
     */
    protected $users;
    
    public function __construct(){
    	$this->permissions = new \Doctrine\Common\Collections\ArrayCollection();
    	$this->children = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * @param int $id
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Role $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
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
    
    public function setChildren($children){
    	$this->children = $children;
    }
    
    public function addChildren($roles){
    	foreach ($roles as $role){
    		$this->children->add($role);
    	}
    }
    
    public function removeChildren($roles){
    	foreach ($roles as $role){
    		$this->children->removeElement($role);
    	}
    }
    
    public function hasChildren(){
    	return !empty($this->children);
    }
    
    /**
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
     * @param $name
     * @return RoleInterface
     */
    public function addPermission($permission)
    {
    	if(is_string($permission)){
    		$permission = new Permission($permission);
    	} else {
    		$this->permissions->add($permission);
    	}
        return $this;
    }

    public function addPermissions(Collection $permissions){
    	foreach ($permissions as $permission){
    		$this->permissions->add($permission); 		
    	}
    }
    
    public function removePermissions(Collection $permissions){
    	foreach ($permissions as $permission){
    		$this->permissions->removeElement($permission);
    	}
    }
    
    /**
     * Checks if a permission exists for this role or any child roles.
     *
     * @param  string $name
     * @return bool
     */
    public function hasPermission($name, $recursive = false) {
    	foreach ($this->permissions as $permission){
    		if($permission->getName() == $name){
    			return true;
    		}
    	}
    	
    	if($recursive){
    		foreach ($this->getChildren() as $child) {
    			if($child->hasPermission($name)){
    				return true;
    			}
    		}
    	}
        return false;
    }

    public function containsRole($rolename){
    	if($this->getName() == $rolename){
    		return true;
    	}
    	foreach ($this->children as $child){
    		if($child->containsRole($rolename)){
    			return true;
    		}
    	}
    	return false;
    }

    /**
     * Add a child.
     *
     * @param  RoleInterface|string $child
     */
    public function addChild(RoleInterface $child)
    {
        $this->children->add($child);
    }
    
    public function __toString()
    {
        return $this->name;
    }
    
    public function getArrayCopy(){
    	$object_vars = get_object_vars($this);
    	if($this->parent){
    		$object_vars['parent'] = $this->parent->getId();
    	}
    	return $object_vars;
    }
}
