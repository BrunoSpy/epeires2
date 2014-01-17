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
use Application\Entity\IPO;

class IPOSController extends FormController
{
	private $options;
	
	
    public function indexAction()
    {
    	$this->layout()->title = "Utilisateurs > IPO";
    	 
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	$ipos = $objectManager->getRepository('Application\Entity\IPO')->findAll();
    	    	
    	$return = array();
    	
    	if($this->flashMessenger()->hasErrorMessages()){
    		$return['error'] =  $this->flashMessenger()->getErrorMessages();
    	}
    	
    	if($this->flashMessenger()->hasSuccessMessages()){
    		$return['success'] =  $this->flashMessenger()->getSuccessMessages();
    	}
    	
    	$this->flashMessenger()->clearMessages();
    	
    	$viewmodel = new ViewModel();
    	
    	$viewmodel->setVariables(array('messages'=>$return, 'ipos'=>$ipos));
    	
        return $viewmodel;
    }
    

    public function saveipoAction(){
    	
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		$id = $post['id'];
    		$datas = $this->getForm($id);
    		$form = $datas['form'];
    		$form->setData($post);
    		$ipo = $datas['ipo'];
    		
    		if($form->isValid()){
    			$objectManager->persist($ipo);
    			try {
    				$objectManager->flush();
    				$this->flashMessenger()->addSuccessMessage('IPO enregistré.');
    			} catch (\Exception $e){
    				$this->flashMessenger()->addErrorMessage($e->getMessage());
    			}
    		} else {
    			$this->processFormMessages($form->getMessages());
    		} 
    	}
    	return new JsonModel();
    }
    
    public function deleteipoAction(){
    	$id = $this->params()->fromQuery('id', null);
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$ipo = $objectManager->getRepository('Application\Entity\IPO')->find($id);
    	if($ipo){
    		$objectManager->remove($ipo);
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
    	
    	$ipoid = $this->params()->fromQuery('ipoid', null);
    	
    	$getform = $this->getForm($ipoid);
    	 
    	$viewmodel->setVariables(array('form' => $getform['form'],'ipoid'=>$ipoid));
    	return $viewmodel;
    }
    
    private function getForm($ipoid = null){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$ipo = new IPO();
    	$builder = new AnnotationBuilder();
    	$form = $builder->createForm($ipo);
    	$form->setHydrator(new DoctrineObject($objectManager, 'Application\Entity\IPO'))
    	->setObject($ipo);
        	 
    	$form->get('organisation')->setValueOptions($objectManager->getRepository('Application\Entity\Organisation')->getAllAsArray());
    	
    	if($ipoid){
    		$ipo = $objectManager->getRepository('Application\Entity\IPO')->find($ipoid);
    		if($ipo){   			
    			$form->bind($ipo);
    			$form->setData($ipo->getArrayCopy());
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
    	 
    	return array('form' => $form, 'ipo'=>$ipo);
    }
}
