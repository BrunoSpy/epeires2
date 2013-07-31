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
use Doctrine\Common\Collections\Criteria;

class EventsController extends AbstractActionController implements LoggerAware
{
	
    public function indexAction(){    	
    	
    	$viewmodel = new ViewModel();
    	
    	$return = array();
    	
    	if($this->flashMessenger()->hasErrorMessages()){
    		$return['errorMessages'] =  $this->flashMessenger()->getErrorMessages();
    	}
    	
    	if($this->flashMessenger()->hasSuccessMessages()){
    		$return['successMessages'] =  $this->flashMessenger()->getSuccessMessages();
    	}
    	
    	$this->flashMessenger()->clearMessages();
    	
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	$events = $this->getevents();// $objectManager->getRepository('Application\Entity\Event')->findBy(array('parent'=> null));
    	
    	$viewmodel->setVariables(array('messages'=>$return, 'events'=>$events));
    	
        return $viewmodel;
    }
    
    /**
     * Create a new event
     * TODO: don't redirect but return JSON with event info
     */
    public function createAction(){    	 
    	if($this->getRequest()->isPost()){
    		
    		$post = $this->getRequest()->getPost();

    		$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    		 
    		$event = new Event();
    		$form = $this->prepareForm($event, $post['categories']['root_categories']);
    		$form->setData($post);
    		 
    		if($form->isValid()){
    			//save new event
    			$event->populate($form->getData());
    			$event->setStatus($objectManager->find('Application\Entity\Status', $form->getData()['status']));
    			if(isset($form->getData()['categories']['subcategories'])
    					&& !empty($form->getData()['categories']['subcategories'])
    					&& $form->getData()['categories']['subcategories'] > 0){
    				$event->setCategory($objectManager->find('Application\Entity\Category', $form->getData()['categories']['subcategories']));
    			} else {
    				$event->setCategory($objectManager->find('Application\Entity\Category', $form->getData()['categories']['root_categories']));
    			}
    			$event->setImpact($objectManager->find('Application\Entity\Impact', $form->getData()['impact']));

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
    			//create associated actions
    			if(isset($post['modelid'])){
    				$parentID = $post['modelid'];
    				//get actions
    				foreach ($objectManager->getRepository('Application\Entity\PredefinedEvent')->findBy(array('parent'=>$parentID)) as $action){
    					$child = new Event();
    					$child->setParent($event);
    					$child->createFromPredefinedEvent($action);
    					$child->setStatus($objectManager->getRepository('Application\Entity\Status')->findOneBy(array('default'=>true, 'open'=> true)));
    					$objectManager->persist($child);
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
    			$this->processFormMessages($form->getMessages());
    		}
    	}
    	
    	return $this->redirect()->toRoute('application');
    	
    }
    

    /**
     * TODO: don't redirect but return JSON
     */
    public function modifyAction(){
    	$id = $this->params()->fromRoute('id', 0);
    	
    	if($id && $this->getRequest()->isPost()){
    		
    		$post = $this->getRequest()->getPost();
    	
    		$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    		
    		$event = $objectManager->getRepository('Application\Entity\Event')->find($id);
    		    		
    		$form = $this->prepareForm($event);
    		$form->bind($event);
    		$form->setData($post);
    		    		
    		if($form->isValid()){
    			//dates are changed via bind() and categories don't change
    			$event->setStatus($objectManager->find('Application\Entity\Status', $post['status']));
    			$event->setImpact($objectManager->find('Application\Entity\Impact', $post['impact']));
    			//update custom field values if needed
    			if(isset($post['custom_fields'])){
    				foreach ($post['custom_fields'] as $key => $value){
    					$customfield = $objectManager->getRepository('Application\Entity\CustomField')->findOneBy(array('name'=>$key));
    					if($customfield){
    						$customfieldvalue = $objectManager->getRepository('Application\Entity\CustomFieldValue')->findOneBy(array('customfield'=>$customfield->getId(), 'event'=>$id));
    						if($customfieldvalue){
    							$customfieldvalue->setValue($value);
    							$objectManager->persist($customfieldvalue);
    						}
    					}
    					//TODO : historique
    				}
    			}
    			$objectManager->persist($event);
    			$objectManager->flush();
    			$this->logger->log(\Zend\Log\Logger::INFO, "event modified");
    			$this->flashMessenger()->addSuccessMessage("Evènement modifié");
    		} else {
    			$this->logger->log(\Zend\Log\Logger::ALERT, "Formulaire non valide, modification impossible");
    			$this->flashMessenger()->addErrorMessage("Impossible de modifier l'évènement.");
    			//traitement des erreurs de validation
    			$this->processFormMessages($form->getMessages());
    		}
    		
    	}
    	
    	return $this->redirect()->toRoute('application');
    }
    
    private function processFormMessages($messages){
    	foreach($messages as $key => $message){
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
    
    private function prepareForm($event, $root_category = 0){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	$form = new EventForm($objectManager->getRepository('Application\Entity\Status')->getAllAsArray(),
    			$objectManager->getRepository('Application\Entity\Impact')->getAllAsArray());
    	$form->setInputFilter($event->getInputFilter());
    	 
    	if($root_category){ //pas de catégories pour la modification d'un evt
    		$categoryfieldset = new CategoryFormFieldset($objectManager->getRepository('Application\Entity\Category')->getRootsAsArray());
    		$form->add($categoryfieldset);
    		//fill form subcategories
    		$valueoptions = $objectManager->getRepository('Application\Entity\Category')->getChildsAsArray($root_category);
    		$valueoptions[-1] = "";
    		$form->get('categories')
    		->get('subcategories')
    		->setValueOptions($valueoptions);
    	}
    	//fill optional form fieldsets
    	if(isset($post['custom_fields'])){
    		$form->add(new CustomFieldset($objectManager, $post['custom_fields']['category_id']));
    	}
    	
    	return $form;
    }

    
    /**
     * Create a new form or a part of it
     * @return \Zend\View\Model\ViewModel
     */
    public function createformAction(){
    	
    	//query param to get a part of the form
    	$subform = $this->params()->fromQuery('subform',null);
    	
    	//typ of the form : creation or modification
    	$type = $this->params()->fromQuery('type', null);
    	
    	$viewmodel = new ViewModel();
    	$request = $this->getRequest();
    	
    	//disable layout if request by Ajax    	
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    	
    	$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	//création du formulaire : identique en cas de modif ou création
    	$form = new EventForm($em->getRepository('Application\Entity\Status')->getAllAsArray(),
    			$em->getRepository('Application\Entity\Impact')->getAllAsArray());
    	//add default fieldsets
    	$form->add(new CategoryFormFieldset($em->getRepository('Application\Entity\Category')->getRootsAsArray()));
    	 
    	
    	if($type){
    		switch ($type) {
    			case 'modification':
    				$id = $this->params()->fromQuery('id', null);
    				try {
    					$event = $em->getRepository('Application\Entity\Event')->find($id);
    				}
    				catch (\Exception $ex) {
    					$this->flashMessenger()->addErrorMessage("Impossible de modifier l'évènement.");
    					return $this->redirect()->toRoute('application');
    				}
    				$cat = $event->getCategory();
    				if($cat->getParent()){
    					$form->get('categories')->get('subcategories')->setValueOptions(
    							$em->getRepository('Application\Entity\Category')->getChildsAsArray($cat->getParent()->getId()));
    					$form->get('categories')->get('root_categories')->setAttribute('value', $cat->getParent()->getId());
    					$form->get('categories')->get('subcategories')->setAttribute('value', $cat->getId());
    				} else {
    					$form->get('categories')->get('root_categories')->setAttribute('value', $cat->getId());
    				}
    				//custom fields
    				$form->add(new CustomFieldset($em, $cat->getId()));
    				//custom fields values
    				foreach ($em->getRepository('Application\Entity\CustomField')->findBy(array('category'=>$cat->getId())) as $customfield){
    					$customfieldvalue = $em->getRepository('Application\Entity\CustomFieldValue')->findOneBy(array('event'=>$event->getId(), 'customfield'=>$customfield->getId()));
  						$form->get('custom_fields')->get($customfield->getName())->setAttribute('value', $customfieldvalue->getValue());
    				}
    				//status
    				$form->get('status')->setAttribute('value', $event->getStatus()->getId());
    				//impact
    				$form->get('impact')->setAttribute('value', $event->getImpact()->getId());
    				$form->bind($event);
    				$viewmodel->setVariables(array('event'=>$event));
    				break;
    			case 'creation':
    			break;
    			default:
    				;
    			break;
    		}
    		
    	}
    	
    	
    	if($subform){
    		switch ($subform) {
    			case 'subcategories':
					$id = $this->params()->fromQuery('id');
    				$viewmodel->setVariables(array(
    						'subform' => $subform,
    						'values' => $em->getRepository('Application\Entity\Category')->getChildsAsArray($id),
    				));
    				break;
    			case 'predefined_events':
    				$id = $this->params()->fromQuery('id');
    				$viewmodel->setVariables(array(
    					'subform' => $subform,
    					'values' => $em->getRepository('Application\Entity\PredefinedEvent')->getEventsWithCategoryAsArray($id),	
    				));
    				break;
    			case 'custom_fields':
    				$viewmodel->setVariables(array(
    						'subform' => $subform));
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
    
    public function getactionsAction(){
    	$parentId = $this->params()->fromQuery('id', null);
    	$json = array();
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	foreach ($objectManager->getRepository('Application\Entity\PredefinedEvent')->findBy(array('parent' => $parentId), array('order' => 'DESC')) as $action){
    		$json[$action->getId()] = array('name' => $action->getName(),
    										'impactname' => $action->getImpact()->getName(),
    										'impactstyle' => $action->getImpact()->getStyle());
    	}
    	
    	return new JsonModel($json);
    }
    
    /**
     * Return {'open' => '<true or false>'}
     * @return \Zend\View\Model\JsonModel
     */
    public function toggleficheAction(){
    	$evtId = $this->params()->fromQuery('id', null);
    	$json = array();
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	$event = $objectManager->getRepository('Application\Entity\Event')->find($evtId);
    	
    	if($event){
    		$event->setStatus($objectManager->getRepository('Application\Entity\Status')->findOneBy(array('default'=>true, 
    																									'open' => !$event->getStatus()->isOpen())));
    		$objectManager->persist($event);
    		$objectManager->flush();
    	}
    	
    	$json['open'] = $event->getStatus()->isOpen();
    	    	
    	return new JsonModel($json);
    }
    
    /**
     * {'evt_id_0' => {
     * 		'name' => evt_name,
     * 		'start_date' => evt_start_date,
     *		'end_date' => evt_end_date,
     *		'category' => evt_category_name,
     *		'category_short' => evt_category_short_name,
     *		'status_name' => evt_status_name,
     *		'actions' => {
     *			'action_name0' => open? (boolean),
     *			'action_name1' => open? (boolean),
     *			...
     *			}
     * 		},
     * 	'evt_id_1' => ...
     * }
     * @return \Zend\View\Model\JsonModel
     */
    public function geteventsAction(){
    	$now = new \DateTime('NOW');
    	
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	$criteria = Criteria::create();
    	$criteria->andWhere(Criteria::expr()->gte('start_date', $now->sub(new \DateInterval('P3D'))));
    	
    	$events = $objectManager->getRepository('Application\Entity\Event')->matching($criteria);
    	
    	$json = array();
    	foreach ($events as $event){
    		$evt = array('name' => $event->getName(),
    					'start_date' => $event->getStartDate(),
    					'end_date' => $event->getEndDate(),
    					'category' => $event->getCategory()->getName(),
    					'category_short' => $event->getCategory()->getShortName(),
    					'status_name' => $event->getStatus()->getName(),
    		);
    		
    		$actions = array();
    		foreach ($event->getChilds() as $child){
    			$actions[$child->getName] = $child->getStatus()->isOpen();
    		}
    		$evt['actions'] = $actions;
    		
    		$json[$event->getId()] = $evt;
    	}
    	
    	return new JsonModel($json);
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
