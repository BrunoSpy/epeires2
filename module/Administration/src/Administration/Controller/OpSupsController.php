<?php
/**
 * Epeires 2
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Administration\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Crypt\Password\Bcrypt;
use Doctrine\Common\Collections\Criteria;
use Administration\Form\ChangePassword;
use Administration\Form\ChangePasswordFilter;
use Application\Controller\FormController;
use Application\Entity\OperationalSupervisor;

class OpSupsController extends FormController
{
	
    public function indexAction()
    {
    	$this->layout()->title = "Utilisateurs > Op Sups";
    	 
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	$opsups = $objectManager->getRepository('Application\Entity\OperationalSupervisor')->findAll();
    	    	
    	$return = array();
    	
    	if($this->flashMessenger()->hasErrorMessages()){
    		$return['error'] =  $this->flashMessenger()->getErrorMessages();
    	}
    	
    	if($this->flashMessenger()->hasSuccessMessages()){
    		$return['success'] =  $this->flashMessenger()->getSuccessMessages();
    	}
    	
    	$this->flashMessenger()->clearMessages();
    	
    	$viewmodel = new ViewModel();
    	
    	$viewmodel->setVariables(array('messages'=>$return, 'opsups'=>$opsups));
    	
        return $viewmodel;
    }
    

    public function saveopsupAction(){
    	
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		$id = $post['id'];
    		$datas = $this->getForm($id);
    		$form = $datas['form'];
                $form->setPreferFormInputFilter(true);
    		$form->setData($post);
    		$opsup = $datas['opsup'];
    		
    		if($form->isValid()){
    			$objectManager->persist($opsup);
    			try {
    				$objectManager->flush();
    				$this->flashMessenger()->addSuccessMessage('Op Sup enregistré.');
    			} catch (\Exception $e){
    				$this->flashMessenger()->addErrorMessage($e->getMessage());
    			}
    		} else {
    			$this->processFormMessages($form->getMessages());
    		} 
    	}
    	return new JsonModel();
    }
    
    public function deleteopsupAction(){
    	$id = $this->params()->fromQuery('id', null);
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$opsup = $objectManager->getRepository('Application\Entity\OperationalSupervisor')->find($id);
    	if($opsup){
    		$objectManager->remove($opsup);
    		try{
    			$objectManager->flush();
    		} catch (\Exception $e){
    			$this->flashMessenger()->addErrorMessage($e->getMessage());
    		}
    	}
    	return new JsonModel();
    }
    
    public function formAction(){
    	$request = $this->getRequest();
    	$viewmodel = new ViewModel();
    	//disable layout if request by Ajax
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    	
    	$opsupid = $this->params()->fromQuery('opsupid', null);
    	
    	$getform = $this->getForm($opsupid);
    	 
    	$viewmodel->setVariables(array('form' => $getform['form'],'opsupid'=>$opsupid));
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
    
    private function getForm($opsupid = null){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$opsup = new OperationalSupervisor();
    	$builder = new AnnotationBuilder();
    	$form = $builder->createForm($opsup);
    	$form->setHydrator(new DoctrineObject($objectManager))
    	->setObject($opsup);
        	 
    	$form->get('organisation')->setValueOptions($objectManager->getRepository('Application\Entity\Organisation')->getAllAsArray());
    	
    	if($opsupid){
    		$opsup = $objectManager->getRepository('Application\Entity\OperationalSupervisor')->find($opsupid);
    		if($opsup){   			
    			$form->bind($opsup);
    			$form->setData($opsup->getArrayCopy());
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
    	 
    	return array('form' => $form, 'opsup'=>$opsup);
    }
}
