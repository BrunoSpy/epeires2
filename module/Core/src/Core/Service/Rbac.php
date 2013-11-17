<?php

namespace Core\Service;

use Closure;
use InvalidArgumentException;
use RecursiveIteratorIterator;
use Zend\Authentication\AuthenticationService;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\Permissions\Rbac\Rbac as ZendRbac;
use ZfcRbac\Assertion\AssertionInterface;
use ZfcRbac\Exception;
use ZfcRbac\Firewall\AbstractFirewall;
use ZfcRbac\Identity;
use ZfcRbac\Provider\Event;
use ZfcRbac\Provider\ProviderInterface;
use Core\Entity\Role;

class Rbac extends \ZfcRbac\Service\Rbac
{

    /**
     * Returns true if the user has the permission.
     *
     * @param string                          $permission
     * @param null|Closure|AssertionInterface $assert
     * @throws InvalidArgumentException
     * @return bool
     */
    public function isGranted($permission, $assert = null)
    { 
    	
        if (!is_string($permission)) {
            throw new InvalidArgumentException('isGranted() expects a string for permission');
        }

        $rbac = $this->getRbac();

        if ($assert) {
            if ($assert instanceof AssertionInterface) {
                if (!$assert->assert($this)) {
                    return false;
                }
            } elseif (is_callable($assert)) {
                if (!$assert($this)) {
                    return false;
                }
            } else {
                throw new InvalidArgumentException(
                    'Assertions must be a Callable or an instance of ZfcRbac\AssertionInterface'
                );
            }
        }

        foreach($this->getIdentity()->getRoles() as $role) {
            if ($role instanceof Role && !$this->hasRole($role->getName())) {
                continue;
            }

            $rolename = "";
            if($role instanceof Role){
            	$rolename = $role->getName();
            } else {
            	$rolename = $role;
            }
            
            $event = new Event;
            $event->setRole($rolename)
                  ->setPermission($permission)
                  ->setRbac($rbac);

            $this->getEventManager()->trigger(Event::EVENT_IS_GRANTED, $event);
            if ($rbac->isGranted($rolename, $permission)) {
                return true;
            }
        }
        return false;
    }

}
