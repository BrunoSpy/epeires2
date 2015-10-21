<?php
/*
 *  This file is part of Epeires².
 *  Epeires² is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  Epeires² is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with Epeires².  If not, see <http://www.gnu.org/licenses/>.
 * 
 *  (c) Bruno Spyckerelle <bruno.spyckerelle@aviation-civile.gouv.fr>
 */

namespace Administration\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Form\Annotation\AnnotationBuilder;

use Core\Entity\Permission;
use Core\Entity\Role;

use Doctrine\Common\Collections\ArrayCollection;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Doctrine\Common\Collections\Criteria;

use Application\Controller\FormController;
/**
 * 
 * @author Bruno Spyckerelle
 */
class RolesController extends FormController
{
    public function indexAction()
    {
        $viewmodel = new ViewModel();

    	$this->layout()->title = "Utilisateurs > Roles";
    	 
    	$config = $this->serviceLocator->get('config');
    	
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

    	$roles = $objectManager->getRepository('Core\Entity\Role')->findAll();
    	
    	$return = array();
    	if($this->flashMessenger()->hasErrorMessages()){
    		$return['error'] =  $this->flashMessenger()->getErrorMessages();
    	}
    	 
    	if($this->flashMessenger()->hasSuccessMessages()){
    		$return['success'] =  $this->flashMessenger()->getSuccessMessages();
    	}
    	 
    	$this->flashMessenger()->clearMessages();
    	 
    	$viewmodel->setVariables(array('messages'=>$return,
                                    'config'=>$config['permissions'],
                                    'roles' => $roles));
        
        return $viewmodel;
    }
    
    public function addpermissionAction(){
    	$permission = $this->params()->fromQuery('permission', null);
    	$roleid = $this->params()->fromQuery('roleid', null);
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$messages = array();
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
                        try {
                            $objectManager->flush();
                            $messages['success'][] = "Permission ".$perm->getName()." ajoutée à ".$role->getName();
                        } catch (\Exception $ex) {
                            $messages['error'][] = $ex->getMessage();
                        }
    		} else {
                    $messages['error'][] = "Rôle introuvable";
                }
        } else {
            $messages['error'][] = "Requête invalide.";
        }
        return new JsonModel($messages);	
    }
    
    public function removepermissionAction(){
    	$permission = $this->params()->fromQuery('permission', null);
    	$roleid = $this->params()->fromQuery('roleid', null);
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$messages = array();
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
    			try {
                            $objectManager->flush();
                            $messages['success'][] = "Permission ".$perm->getName()." retirée à ".$role->getName();
                        } catch (\Exception $ex) {
                            $messages['error'][] = $ex->getMessage();
                        }
    		} else {
                    $messages['error'][] = "Rôle introuvable";
                }
    	} else {
            $messages['error'][] = "Requête invalide.";
        }
    	 
    	return new JsonModel($messages);
    }
	public function saveroleAction() {
		$messages = array ();
		$objectManager = $this->getServiceLocator ()->get ( 'Doctrine\ORM\EntityManager' );
		$guest = false;
		$admin = false;
		if ($this->getRequest ()->isPost ()) {
			$post = $this->getRequest ()->getPost ();
			$id = $post ['id'];
			$datas = $this->getForm ( $id );
			$form = $datas ['form'];
			
			$guest = $datas ['role']->getName () == 'guest';
			$admin = $datas ['role']->getName () == 'admin';
			$form->setPreferFormInputFilter ( true );
			$form->setData ( $post );
			$role = $datas ['role'];
			
			if ($form->isValid ()) {
				if ($guest && $post ['name'] != 'guest') {
					$role->setName ( 'guest' );
					$this->flashMessenger ()->addErrorMessage ( "Modification du nom du rôle 'guest' interdit." );
				}
				if ($admin && $post ['name'] != 'admin') {
					$role->setName ( 'admin' );
					$this->flashMessenger ()->addErrorMessage ( "Modification du nom du rôle 'admin' interdit." );
				}
                if ($role->getParent()->getName() === $post['name']) {
                    $role->setParent(null);
                    $this->flashMessenger()->addErrorMessage("Impossible d'être son parent : rôle parent laissé vide.");
                }
				$objectManager->persist ( $role );
				try {
					$objectManager->flush ();
					$this->flashMessenger ()->addSuccessMessage ( "Rôle " . $role->getName () . " correctement enregistré." );
				} catch ( \Exception $ex ) {
					$messages ['error'] [] = $ex->getMessage ();
				}
			} else {
				$this->processFormMessages ( $form->getMessages (), $messages );
			}
		}
		return new JsonModel ( $messages );
    }
    
    public function deleteroleAction(){
    	$id = $this->params()->fromQuery('id', null);
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$role = $objectManager->getRepository('Core\Entity\Role')->find($id);
    	if($role){
    		$objectManager->remove($role);
    		$objectManager->flush();
    	}
    	return new JsonModel();
    }
    
    public function formAction(){
    	$request = $this->getRequest();
    	$viewmodel = new ViewModel();
    	//disable layout if request by Ajax
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    	
    	$id = $this->params()->fromQuery('id', null);
    	
    	$getform = $this->getForm($id);
    	 
    	$viewmodel->setVariables(array('form' => $getform['form'],'id'=>$id));
    	return $viewmodel;
    }
    
    private function getForm($id = null){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$role = new Role();
    	$builder = new AnnotationBuilder();
    	$form = $builder->createForm($role);
    	$form->setHydrator(new DoctrineObject($objectManager))
    	->setObject($role);
        	 
    	$form->get('parent')->setValueOptions($objectManager->getRepository('Core\Entity\Role')->getAllAsArray());
        $form->get('readcategories')->setValueOptions($objectManager->getRepository('Application\Entity\Category')->getAllAsArray());
    	
    	if($id){
    	    $criteria = Criteria::create()
    	               ->where(Criteria::expr()->neq('id', $id));
    	    $form->get('parent')->setValueOptions(
    	        $objectManager->getRepository('Core\Entity\Role')->getAllAsArray($criteria));
    		$role = $objectManager->getRepository('Core\Entity\Role')->find($id);
    		if($role){
    			$form->bind($role);
    			$form->setData($role->getArrayCopy());
    		}
    	}
    
    	$form->add(array(
    			'name' => 'submit',
    			'attributes' => array(
    					'type' => 'submit',
    					'value' => 'Enregistrer',
    					'class' => 'btn btn-primary btn-small',
    			),
    	));
    	 
    	return array('form' => $form, 'role'=>$role);
    }
}
