<?php
/**
 * Epeires 2
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Administration\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Core\Entity\User;
use Zend\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Crypt\Password\Bcrypt;

class UsersController extends AbstractActionController
{
	private $options;
	
	
    public function indexAction()
    {
    	$this->layout()->title = "Utilisateurs > Administration";
    	 
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	$users = $objectManager->getRepository('Core\Entity\User')->findAll();
    	    	
        return array('users'=>$users);
    }
    

    public function saveuserAction(){
    	
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		$id = $post['id'];
    		$datas = $this->getForm($id);
    		$form = $datas['form'];
    		$form->setData($post);
    		$user = $datas['user'];
    		
    		if($form->isValid()){
    			if(isset($post['password'])){
    				error_log('password');
    				$bcrypt = new Bcrypt();
    				$bcrypt->setCost($this->getServiceLocator()->get('zfcuser_module_options')->getPasswordCost());
    				$user->setPassword($bcrypt->create($user->getPassword()));
    			}
    			$objectManager->persist($user);
    			$objectManager->flush();
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
    
    private function getForm($userid = null){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$user = new User();
    	$builder = new AnnotationBuilder();
    	$form = $builder->createForm($user);
    	$form->setHydrator(new DoctrineObject($objectManager, 'Core\Entity\User'))
    	->setObject($user);
    
    	$form->get('userroles')->setValueOptions($objectManager->getRepository('Core\Entity\Role')->getAllAsArray());
    	 
    	if($userid){
    		$user = $objectManager->getRepository('Core\Entity\User')->find($userid);
    		if($user){
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
