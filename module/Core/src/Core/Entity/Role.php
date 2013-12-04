<?php

namespace Core\Entity;


use Doctrine\ORM\PersistentCollection;
use RecursiveIterator;
use IteratorIterator;
use Zend\Form\Annotation;
use Zend\Permissions\Rbac\RoleInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/** 
 * @ORM\Entity(repositoryClass="Application\Repository\ExtendedRepository")
 * @ORM\Table(name="roles")
 */
class Role implements RoleInterface{

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
     * @ORM\ManyToOne(targetEntity="Role", inversedBy="childrenroles")
     * @Annotation\Type("Zend\Form\Element\Select")
     * @Annotation\Required(false)
	 * @Annotation\Attributes({"multiple":false})
	 * @Annotation\Options({"label":"Parent :", "empty_option":"Rôle parent (facultatif)"})
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Role", mappedBy="parent")
     */
    protected $childrenroles;

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
     * current position in the children array
     * @var int
     */
    protected $childrenPosition=0;

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
    	$this->childrenroles = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
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
     * @return self
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /* ***** Implements RecursiveIterator ***** */
    
    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Returns if an iterator can be created for the current entry.
     * @link http://php.net/manual/en/recursiveiterator.haschildren.php
     * @return bool true if the current entry can be iterated over, otherwise returns false.
     */
    public function hasChildren()
    {
    	return ($this->childrenroles->count() > 0);
    }
    
    /**
     * @return Role[]|RecursiveIterator[]
     */
    public function getChildren()
    {
        $children = $this->childrenroles->getValues();
        return $children[$this->childrenPosition];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
    	$children = $this->childrenroles->getValues();
    	return $children[$this->childrenPosition];
    }
    
    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
    	$this->childrenPosition++;
    }
    
    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
    	return $this->childrenPosition;
    }
    
    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     *       Returns true on success or false on failure.
     */
    public function valid()
    {
    	$children = $this->childrenroles->getValues();
    	return isset($children[$this->childrenPosition]);
    }
    
    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
    	$this->childrenPosition=0;
    }
    
    
    
    /* ******** End RecursiveIterator ******** */
    
    
    public function getChildrenroles()
    {
    	return $this->childrenroles;
    }
    
    public function setChildrenroles($children){
    	$this->childrenroles = $children;
    }
    
    public function addChildrenroles($roles){
    	foreach ($roles as $role){
    		$this->childrenroles->add($role);
    	}
    }
    
    public function removeChildrenroles($roles){
    	foreach ($roles as $role){
    		$this->childrenroles->removeElement($role);
    	}
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
     * @param bool $recursive when true child permissions of a role are returned as well
     * @return PersistentCollection|Permission[]
     */
    public function getPermissions($recursive=false)
    {
        if (!$recursive) {
            return $this->permissions;
        }
        $permissions =  $this->permissions->getValues();
        $it = new IteratorIterator($this);
        foreach ($it as $leaf) {
            $permissions = array_merge($permissions, $leaf->getPermissions(true));
        }
        return $permissions;
    }

    
    protected $permissionstring = array();
    /**
     * Add permission to the role.
     *
     * @param $name
     * @return RoleInterface
     */
    public function addPermission($permission)
    {
    	if(is_string($permission)){
    		$this->permissionstring[] = $permission;
    	} else {
    		$this->getPermissions()->add($permission);
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
    public function hasPermission($name, $recursive = true)
    {
    	if(in_array($name,$this->permissionstring)){
    		return true;
    	}

    	foreach ($this->permissions as $permission){
    		if($permission->getName() == $name){
    			return true;
    		}
    	}
    	
    	if($recursive){
    		$it = new IteratorIterator($this);
    		foreach ($it as $leaf) {
    			if($leaf->hasPermission($name)){
    				return true;
    			}
    		}
    	}

        return false;
    }


    /**
     * Add a child.
     *
     * @param  RoleInterface|string $child
     * @return RoleInterface
     */
    public function addChild($child)
    {
        $this->childrenroles->add($child);
        return $this;
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
