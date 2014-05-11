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
use Core\Entity\Role;
use Application\Controller\FormController;
use Zend\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

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
    		$return['errorMessages'] =  $this->flashMessenger()->getErrorMessages();
    	}
    	 
    	if($this->flashMessenger()->hasSuccessMessages()){
    		$return['successMessages'] =  $this->flashMessenger()->getSuccessMessages();
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
    
    public function saveroleAction(){
    	$messages = array();
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		$id = $post['id'];
    		$datas = $this->getForm($id);
    		$form = $datas['form'];
    		$form->setPreferFormInputFilter(true);
                $form->setData($post);
    		$role = $datas['role'];
    		
    		if($form->isValid()){
    			$objectManager->persist($role);
                        try {
                            $objectManager->flush();
                            $this->flashMessenger()->addSuccessMessage("Rôle ".$role->getName()." correctement enregistré.");
                        } catch (\Exception $ex) {
                            $messages['error'][] = $ex->getMessage();
                        }
    		} else {
                    $this->processFormMessages($form->getMessages(), $messages);
                }
    	}
    	return new JsonModel($messages);
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
