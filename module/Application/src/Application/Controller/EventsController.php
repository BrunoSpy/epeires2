<?php
/**
 * Epeires 2
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\Event;
use Application\Form\EventForm;
use Application\Form\CategoryFormFieldset;
use Application\Form\CustomFieldset;
use Application\Entity\CustomFieldValue;
use Zend\View\Model\JsonModel;

class EventsController extends AbstractActionController implements LoggerAware
{
	
    public function indexAction(){    	
    	$return = array();
    	
    	if($this->flashMessenger()->hasErrorMessages()){
    		$return['errorMessages'] =  $this->flashMessenger()->getErrorMessages();
    	}
    	
    	if($this->flashMessenger()->hasSuccessMessages()){
    		$return['successMessages'] =  $this->flashMessenger()->getSuccessMessages();
    	}
    	
    	$this->flashMessenger()->clearMessages();
    	
        return $return;
    }
    
    /**
     * Create a new event
     */
    public function createAction(){    	 
    	if($this->getRequest()->isPost()){
    		
    		$post = $this->getRequest()->getPost();
    		
    		$event = new Event();
    		$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    		$form = new EventForm($objectManager->getRepository('Application\Entity\Status')->getAllAsArray());
    		$form->setInputFilter($event->getInputFilter());
    		    	
    		$categoryfieldset = new CategoryFormFieldset($objectManager->getRepository('Application\Entity\Category')->getRootsAsArray());
    		$form->add($categoryfieldset);
    		//fill form subcategories    		
    		$form->get('categories')
    			 ->get('subcategories')
    			 ->setValueOptions($objectManager->getRepository('Application\Entity\Category')->getChildsAsArray($this->getRequest()->getPost()['categories']['root_categories']));
  		
    		$form->setData($post);
    		
    		//fill optional form fieldsets
    		if(isset($post['custom_fields'])){
    			$form->add(new CustomFieldset($objectManager, $post['custom_fields']['category_id']));
    		}
    		
    		if(isset($post['custom_fields'])){
    			error_log(print_r($post['custom_fields'], true));
    		}
    		    		
    		if($form->isValid()){
    			//save new event
    			$event->populate($form->getData());	
    			$event->setStatus($objectManager->find('Application\Entity\Status', $form->getData()['status']));
    			$event->setCategory($objectManager->find('Application\Entity\Category', $form->getData()['categories']['root_categories']));
    			
    			//save optional datas
    			if(isset($post['custom_fields'])){
    				foreach ($post['custom_fields'] as $key => $value){
    					//génération des customvalues si un customfield dont le nom est $key est trouvé
						$customfield = $objectManager->getRepository('Application\Entity\CustomField')->findOneBy(array('name'=>$key));
						if($customfield){
    						$customvalue = new CustomFieldValue();
    						$customvalue->setEvent($event);
    						$customvalue->setCustomField($customfield);
    						$customvalue->setValue($value);
    						$objectManager->persist($customvalue);
    					}
    				}
    			}
    			
    			$objectManager->persist($event);
    			$objectManager->flush();
    			$this->logger->log(\Zend\Log\Logger::INFO, "event saved");
    			$this->flashMessenger()->addSuccessMessage("Evènement enregistré");
    		} else {
    			$this->logger->log(\Zend\Log\Logger::ALERT, "Formulaire non valide");
    			$this->flashMessenger()->addErrorMessage("Impossible d'enregistrer l'évènement.");
    			//traitement des erreurs de validation
    			foreach($form->getMessages() as $key => $message){
    				foreach($message as $mkey => $mvalue){//les messages sont de la forme 'type_message' => 'message'
    					if(is_array($mvalue)){
    						foreach ($mvalue as $nkey => $nvalue){//les fieldsets sont un niveau en dessous
    							$this->flashMessenger()->addErrorMessage(
    									"Champ ".$mkey." incorrect : ".$nvalue);
    						}
    					} else {
    						$this->flashMessenger()->addErrorMessage(
    								"Champ ".$key." incorrect : ".$mvalue);
    					}
    				}
    			}
    		}
    	} 
    	
    	return $this->redirect()->toRoute('application');
    	
    }
    
    /**
     * Create a new form or a part of it
     * @return \Zend\View\Model\ViewModel
     */
    public function createformAction(){
    	
    	$type = $this->params()->fromQuery('type',null);
    	$viewmodel = new ViewModel();
    	$request = $this->getRequest();
    	//disable layout if request by Ajax
    	
    	error_log("xmlhttp : "+$request->isXmlHttpRequest());
    	
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    	$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	$form = new EventForm($em->getRepository('Application\Entity\Status')->getAllAsArray());
    	//add default fieldsets
    	$form->add(new CategoryFormFieldset($em->getRepository('Application\Entity\Category')->getRootsAsArray()));
    	
    	if($type){
    		switch ($type) {
    			case 'subcategories':
					$id = $this->params()->fromQuery('id');
    				$viewmodel->setVariables(array(
    						'type' => $type,
    						'values' => $em->getRepository('Application\Entity\Category')->getChildsAsArray($id),
    				));
    				break;
    			case 'predefined_events':
    				$id = $this->params()->fromQuery('id');
    				$viewmodel->setVariables(array(
    					'type' => $type,
    					'values' => $em->getRepository('Application\Entity\PredefinedEvent')->getEventsWithCategoryAsArray($id),	
    				));
    				break;
    			case 'custom_fields':
    				$viewmodel->setVariables(array(
    						'type' => $type));
    				$form->add(new CustomFieldset($em, $this->params()->fromQuery('id')));
    				break;
    			default:
    				;
    			break;
    		}
    	}
    	$viewmodel->setVariables(array('form' => $form));
    	return $viewmodel;
    	 
    }

    public function getpredefinedvaluesAction(){
    	$predefinedId = $this->params()->fromQuery('id',null);
    	$json = array();
    	$defaultvalues = array();
    	$customvalues = array();
    	
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	$predefinedEvt = $objectManager->getRepository('Application\Entity\PredefinedEvent')->find($predefinedId);
    	
    	$defaultvalues['name'] = $predefinedEvt->getName();
    	$defaultvalues['punctual'] = $predefinedEvt->isPunctual();

    	$json['defaultvalues'] = $defaultvalues;
    	
    	foreach ($predefinedEvt->getCustomFieldsValues() as $customfieldvalue){
    		$customvalues[$customfieldvalue->getCustomField()->getName()] = $customfieldvalue->getValue();
    	}
    	
    	$json['customvalues'] = $customvalues;
    	
    	return new JsonModel($json);
    }
    
    public function modifyAction(){
    	
    }
    
    //Logger
    private $logger;
    
    public function setLogger(\Zend\Log\Logger $logger){
    	$this->logger = $logger;
    }
    
    public function getLogger(){
    	return $logger;
    }
}
