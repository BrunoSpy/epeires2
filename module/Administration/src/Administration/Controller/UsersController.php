<?php
/**
 * Epeires 2
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Administration\Controller;

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Core\Entity\User;
use Zend\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Crypt\Password\Bcrypt;
use Doctrine\Common\Collections\Criteria;
use Administration\Form\ChangePassword;
use Administration\Form\ChangePasswordFilter;
use Application\Controller\FormController;

class UsersController extends FormController {
	
    public function indexAction()
    {
    	$this->layout()->title = "Utilisateurs > Administration";
    	 
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	$users = $objectManager->getRepository('Core\Entity\User')->findAll();
    	    	
    	$return = array();
    	
    	if($this->flashMessenger()->hasErrorMessages()){
    		$return['error'] =  $this->flashMessenger()->getErrorMessages();
    	}
    	
    	if($this->flashMessenger()->hasSuccessMessages()){
    		$return['success'] =  $this->flashMessenger()->getSuccessMessages();
    	}
    	
    	$this->flashMessenger()->clearMessages();
    	
    	$viewmodel = new ViewModel();
    	
    	$viewmodel->setVariables(array('messages'=>$return, 'users'=>$users));
    	
        return $viewmodel;
    }
    

    public function saveuserAction(){
    	
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		$id = $post['id'];
    		$datas = $this->getForm($id);
    		$form = $datas['form'];
    		$form->setData($post);
            $form->setPreferFormInputFilter(true);
    		$user = $datas['user'];
    		
    		if($form->isValid()){
    			if(isset($post['password'])){
    				$bcrypt = new Bcrypt();
    				$bcrypt->setCost($this->getServiceLocator()->get('zfcuser_module_options')->getPasswordCost());
    				$user->setPassword($bcrypt->create($user->getPassword()));
    			}
    			try {
    				$objectManager->persist($user);
    				$objectManager->flush();
    				$this->flashMessenger()->addSuccessMessage('Utilisateur enregistré.');
    			} catch (\Exception $e){
    				$this->flashMessenger()->addErrorMessage($e->getMessage());
    			}
    		} else {
    			$this->processFormMessages($form->getMessages());
    		} 
    	}
    	return new JsonModel();
    }
    
    public function deleteuserAction(){
    	$id = $this->params()->fromQuery('id', null);
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$user = $objectManager->getRepository('Core\Entity\User')->find($id);
    	if($user){
    		$objectManager->remove($user);
    		$objectManager->flush();
    	}
    	return new JsonModel();
    }
    
    public function formAction(){
    	$request = $this->getRequest();
    	$viewmodel = new ViewModel();
    	//disable layout if request by Ajax
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    	
    	$userid = $this->params()->fromQuery('userid', null);
    	
    	$getform = $this->getForm($userid);
    	 
    	$viewmodel->setVariables(array('form' => $getform['form'],'userid'=>$userid));
    	return $viewmodel;
    }
    
    public function changepasswordAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();

    		$form = new ChangePassword('changepassword');
    		$form->setInputFilter(new ChangePasswordFilter());
//    		$form->setPreferFormInputFilter(true);
    		$form->setData($post);
    	
    		if($form->isValid()){
    			$user = $objectManager->getRepository('Core\Entity\User')->find($post['id']);
    			if($user && isset($post['newCredential'])){
    				$bcrypt = new Bcrypt();
    				$bcrypt->setCost($this->getServiceLocator()->get('zfcuser_module_options')->getPasswordCost());
    				$user->setPassword($bcrypt->create($post['newCredential']));
    			}
    			$objectManager->persist($user);
    			try {
    				$objectManager->flush();
    				$this->flashMessenger()->addSuccessMessage('Mot de passe correctement modifié');
    			} catch (\Exception $e) {
    				$this->flashMessenger()->addErrorMessage($e->getMessage());
    			}
    		} else {
    			$this->processFormMessages($form->getMessages());
    		}
    	}
    	return new JsonModel();
    }
    
    public function changepasswordformAction(){
    	$request = $this->getRequest();
    	$viewmodel = new ViewModel();
    	//disable layout if request by Ajax
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    	
        $userid = $this->params()->fromQuery('id', null);
        
    	$form = new ChangePassword('changepassword');
    	$form->setInputFilter(new ChangePasswordFilter());
    	
        $form->get('id')->setValue($userid);
        
    	$viewmodel->setVariables(array('form' => $form));
    	return $viewmodel;
    }
    
    public function getqualifzoneAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$orgid = $this->params()->fromQuery('id', null);
    	$json = array();
    	if($orgid){
    		$organisation = $objectManager->getRepository('Application\Entity\Organisation')->find($orgid);
    		if($organisation){
    			$criteria = Criteria::create()->where(Criteria::expr()->eq('organisation', $organisation));
    			foreach ($objectManager->getRepository('Application\Entity\QualificationZone')->matching($criteria) as $zone){
    				$json[$zone->getId()] = $zone->getName();
    			}
    		}
    	}
    	return new JsonModel($json);
    }
    
    private function getForm($userid = null){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$user = new User();
    	$builder = new AnnotationBuilder();
    	$form = $builder->createForm($user);
    	$form->setHydrator(new DoctrineObject($objectManager))
    	->setObject($user);
    
    	$form->get('userroles')->setValueOptions($objectManager->getRepository('Core\Entity\Role')->getAllAsArray());
    	 
    	$form->get('organisation')->setValueOptions($objectManager->getRepository('Application\Entity\Organisation')->getAllAsArray());
    	
    	if($userid){
    		$user = $objectManager->getRepository('Core\Entity\User')->find($userid);
    		if($user){
    			$form->get('zone')->setValueOptions($objectManager->getRepository('Application\Entity\QualificationZone')->getAllAsArray($user->getOrganisation()));
    			
    			$form->remove('password');
    			$form->bind($user);
    			$form->setData($user->getArrayCopy());
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
    	 
    	return array('form' => $form, 'user'=>$user);
    }
}
