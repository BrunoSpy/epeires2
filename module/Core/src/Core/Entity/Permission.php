<?php

namespace Core\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use ZfcRbac\Permission\PermissionInterface;

/**
 * 
 * @ORM\Entity
 * @ORM\Table(name="permissions")
 */
class Permission implements PermissionInterface {

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
    
    public function __construct() {
    	$this->roles = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
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

    public function addRole($role){
    	$this->roles->add($role);
    }
    
    public function addRoles(Collection $roles){
    	$collection = new ArrayCollection();
    	$collection->add($this);
    	foreach ($roles as $role){
    		$role->addPermissions($collection);
    		$this->roles->add($role);
    	}
    }
    
    public function removeRoles(Collection $roles){
    	$collection = new ArrayCollection();
    	$collection->add($this);
    	foreach ($roles as $role){
    		$role->removePermissions($collection);
    		$this->roles->removeElement($role);
    	}
    }
    
    public function getRoles(){
    	return $this->roles;
    }
    
    public function __toString()
    {
        return $this->name;
    }

}