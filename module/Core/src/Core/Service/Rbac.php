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
     * Returns true if the user has the role (can pass an array).
     *
     * @param string|array $roles
     * @return bool
     */
    public function hasRole($roles)
    {
    	error_log('hasrole');
    	
        if (!$this->getIdentity()) {
            return false;
        }

        if (!is_array($roles) && !($roles instanceof \Traversable)) {
            $roles = array($roles);
        }

        $rbac = $this->getRbac();

        // Have to iterate and load roles to verify that parents are loaded.
        // If it wasn't for inheritance we could just check the getIdentity()->getRoles() method.
        $userRoles = $this->getIdentity()->getRoles();
        if (is_string($userRoles)) {
            $userRoles = array($userRoles);
        }
        foreach($roles as $role) {
            foreach($userRoles as $userRole) {
                $event = new Event;
                $event->setRole($userRole)
                      ->setRbac($rbac);

                $this->getEventManager()->trigger(Event::EVENT_HAS_ROLE, $event);

                if (!$this->getRbac()->hasRole($role)) {
                    continue;
                }

                // Fastest - do they match directly?
                if ($userRole == $role) {
                    return true;
                }

                // Last resort - check children from rbac.
                $it = new RecursiveIteratorIterator($rbac->getRole($userRole), RecursiveIteratorIterator::CHILD_FIRST);
                foreach($it as $leaf) {
                    if ($leaf->getName() == $role) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

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
    	error_log('isgranted');
    	 
    	
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

            $event = new Event;
            $event->setRole($role->getName())
                  ->setPermission($permission)
                  ->setRbac($rbac);

            $this->getEventManager()->trigger(Event::EVENT_IS_GRANTED, $event);
            if ($rbac->isGranted($role->getName(), $permission)) {
                return true;
            }
        }
        return false;
    }

}
