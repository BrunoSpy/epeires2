<?php
/**
 * Epeires 2
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Administration\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Core\Entity\Permission;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\View\Model\JsonModel;

class RolesController extends AbstractActionController
{
    public function indexAction()
    {
    	$this->layout()->title = "Utilisateurs > Roles";
    	 
    	$config = $this->serviceLocator->get('config');
    	
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

    	$roles = $objectManager->getRepository('Core\Entity\Role')->findAll();
    	
        return array('config'=>$config['permissions'], 'roles' => $roles);
    }
    
    public function addpermissionAction(){
    	$permission = $this->params()->fromQuery('permission', null);
    	$roleid = $this->params()->fromQuery('roleid', null);
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	if($permission && $roleid){
    		$perm = $objectManager->getRepository('Core\Entity\Permission')->findOneBy(array('name' => $permission));
    		if(!$perm){
    			//create new permission
    			$perm = new Permission();
    			$perm->setName($permission);
    		}
    		$role = $objectManager->getRepository('Core\Entity\Role')->find($roleid);
    		if($role){
    			$permissioncollection = new ArrayCollection();
    			$permissioncollection->add($perm);
    			$role->addPermissions($permissioncollection);
    			$objectManager->persist($perm);
    			$objectManager->persist($role);
    			$objectManager->flush();
    		}
    	}
    	
    	//TODO renvoyer true si tou s'est bien passé
    	return new JsonModel();
    	
    }
    
    public function removepermissionAction(){
    	$permission = $this->params()->fromQuery('permission', null);
    	$roleid = $this->params()->fromQuery('roleid', null);
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	 
    	if($permission && $roleid){
    		$perm = $objectManager->getRepository('Core\Entity\Permission')->findOneBy(array('name' => $permission));
    		if(!$perm){
    			//create new permission
    			$perm = new Permission();
    			$perm->setName($permission);
    		}
    		$role = $objectManager->getRepository('Core\Entity\Role')->find($roleid);
    		if($role){
    			$permissioncollection = new ArrayCollection();
    			$permissioncollection->add($perm);
    			$role->removePermissions($permissioncollection);
    			$objectManager->persist($perm);
    			$objectManager->persist($role);
    			$objectManager->flush();
    		}
    	}
    	 
    	//TODO renvoyer true si tou s'est bien passé
    	return new JsonModel();
    	 
    }
}
