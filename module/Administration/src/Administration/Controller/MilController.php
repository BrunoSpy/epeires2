<?php
/**
 * Epeires 2
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Administration\Controller;

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;


class MilController extends \Application\Controller\FormController {
    
    
    public function configAction(){
    	$viewmodel = new ViewModel();
    	$this->layout()->title = "Personnalisation > Page Zones militaires";
    	
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	        
    	$viewmodel->setVariables(array(
                            'cats' => $objectManager->getRepository('Application\Entity\MilCategory')->findBy(array(), array('name' => 'ASC'))
                            )
                );
    	 
        //gestion des erreurs lors d'un reload
        $return = array();
    	if($this->flashMessenger()->hasErrorMessages()){
    		$return['error'] =  $this->flashMessenger()->getErrorMessages();
    	}
    	 
    	if($this->flashMessenger()->hasSuccessMessages()){
    		$return['success'] =  $this->flashMessenger()->getSuccessMessages();
    	}
    	 
    	$this->flashMessenger()->clearMessages();
    	 
    	$viewmodel->setVariables(array('messages'=>$return));
    	return $viewmodel;
    }
    
    public function saveAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$messages = array();
    	$json = array();
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		$id = $post['id'];
    		    	
    		$datas = $this->getForm($id);
    		$form = $datas['form'];
                                
    		$form->setData($post);
    		$form->setPreferFormInputFilter(true);
    		$milcat = $datas['milcat'];
    		
    		if($form->isValid()){
    			$objectManager->persist($milcat);	
    			try {
    				$objectManager->flush();
    				$this->flashMessenger()->addSuccessMessage("Catégorie modifée.");
    				$mil = array('id' => $milcat->getId(), 'name' => $milcat->getName(), 'regex' => $milcat->getZonesRegex());
    				$json['milcat'] = $milcat;
    				$json['success'] = true;
    			} catch (\Exception $e) {
                            error_log(print_r($e->getMessage(), true));
    				$messages['error'][] = $e->getMessage();
    			}
    			
    		} else {
                    error_log("marche pas");
    			$this->processFormMessages($form->getMessages());
    			$this->flashMessenger()->addErrorMessage("Impossible de modifier la catégorie.");
    		}
    	}
       	$json['messages'] = $messages;
    	return new JsonModel($json);
    }
    
    public function formAction(){
    	$request = $this->getRequest();
    	$viewmodel = new ViewModel();
    	//disable layout if request by Ajax
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    	
    	$id = $this->params()->fromQuery('id', null);
    	
    	$getform = $this->getForm($id);
    	$form = $getform['form'];
    	 
    	$form->add(array(
    			'name' => 'submit',
    			'attributes' => array(
    					'type' => 'submit',
    					'value' => 'Enregistrer',
    					'class' => 'btn btn-primary',
    			),
    	));
    	
    	$viewmodel->setVariables(array('form' =>$form));
    	return $viewmodel;
    }
    
    private function getForm($id){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$milcat = new \Application\Entity\MilCategory();
    	$builder = new AnnotationBuilder();
    	$form = $builder->createForm($milcat);
                
    	$form->setHydrator(new DoctrineObject($objectManager))
    		->setObject($milcat);
        
        $form->get('readroles')->setValueOptions($objectManager->getRepository('Core\Entity\Role')->getAllAsArray());
        
    	if($id){
    		$milcat = $objectManager->getRepository('Application\Entity\MilCategory')->find($id);
    		if($milcat){
    			$form->bind($milcat);
    			$form->setData($milcat->getArrayCopy());
    		}
    	}

    	return array('form'=>$form, 'milcat'=>$milcat);
    }
    
}
