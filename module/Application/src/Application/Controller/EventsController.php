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
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Form\Fieldset;
use Zend\Form\Element\File;
use Gedmo\Uploadable\UploadableListener;
use Gedmo\Uploadable\FileInfo\FileInfoArray;
use Zend\Form\Element\Text;

class EventsController extends FormController {
	
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
    	
     	$viewmodel->setVariables(array('messages'=>$return));
    	 
        return $viewmodel;
    }
    
 	/**
 	 * 
 	 * @return \Zend\View\Model\JsonModel Exception : if query param 'return' is true, redirect to route application. 
 	 */
    public function saveAction(){   
    	 	 
    	
    	$return = $this->params()->fromQuery('return', null);
    	
    	$messages = array();
    	
    	if($this->getRequest()->isPost()){
    		
    		$post = array_merge_recursive($this->getRequest()->getPost()->toArray(),
    									$this->getRequest()->getFiles()->toArray());
    		$id = $post['id'] ? $post['id'] : null;
    		
    		$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

    		$event = new Event();
    		if($id){
    			$event = $objectManager->getRepository('Application\Entity\Event')->find($id);
    		} 
    		$form = $this->getSkeletonForm($event);  		
    		
    		$form->setData($post);
    		     		  		
    		if($form->isValid()){
   			 
    			//TODO find why hydrator can't set a null value to a datetime
    			if(isset($post['enddate']) && empty($post['enddate'])){
    				$event->setEndDate(null);
    			}
    			
    			if(!$id){//categories disabled when modification
    				if(isset($post['categories']['subcategories'])
    						&& !empty($post['categories']['subcategories'])
    						&& $post['categories']['subcategories'] > 0){
    					$event->setCategory($objectManager->find('Application\Entity\Category', $post['categories']['subcategories']));
    				} else {
    					$event->setCategory($objectManager->find('Application\Entity\Category', $post['categories']['root_categories']));
    				}
    			}
    			//save optional datas
    			if(isset($post['custom_fields'])){
    				foreach ($post['custom_fields'] as $key => $value){
    					//génération des customvalues si un customfield dont le nom est $key est trouvé
    					$customfield = $objectManager->getRepository('Application\Entity\CustomField')->findOneBy(array('id'=>$key));
    					if($customfield){
    						$customvalue = $objectManager->getRepository('Application\Entity\CustomFieldValue')
    															->findOneBy(array('customfield'=>$customfield->getId(), 'event'=>$id));
    						if(!$customvalue){
    							$customvalue = new CustomFieldValue();
    							$customvalue->setEvent($event);
    							$customvalue->setCustomField($customfield);
    							$event->addCustomFieldValue($customvalue);
    						}
    						$customvalue->setValue($value);
    						$objectManager->persist($customvalue);
    					}
    				}
    			}
    			//create associated actions (only relevant if creation from a model
    			if(isset($post['modelid'])){
    				$parentID = $post['modelid'];
    				//get actions
    				foreach ($objectManager->getRepository('Application\Entity\PredefinedEvent')->findBy(array('parent'=>$parentID)) as $action){
    					$child = new Event();
    					$child->setParent($event);
    					$child->createFromPredefinedEvent($action);
    					$child->setStatus($objectManager->getRepository('Application\Entity\Status')->findOneBy(array('defaut'=>true, 'open'=> true)));
    					//customfields
    					foreach($action->getCustomFieldsValues() as $customvalue){
    						$newcustomvalue = new CustomFieldValue();
    						$newcustomvalue->setEvent($child);
    						$newcustomvalue->setCustomField($customvalue->getCustomField());
    						$newcustomvalue->setValue($customvalue->getValue());
    						$objectManager->persist($newcustomvalue);
    					}
    					$objectManager->persist($child);
    				}
    			}
    			
    			//fichiers
    			if(isset($post['fichiers']) && is_array($post['fichiers'])){
    				foreach ($post['fichiers'] as $f){
    					$file = new \Application\Entity\File($f);
    					$file->addEvent($event);
    					$objectManager->persist($file);
    				}
    			}
    			
    			
    			
    			$objectManager->persist($event);
    			$objectManager->flush();
    			if($return){
    				$this->flashMessenger()->addSuccessMessage("Evènement enregistré");
    			} else {
    				$messages['success'][0] = "Evènement enregistré";
    			}
    			if($return){
    				return $this->redirect()->toRoute('application');
    			} else {
    				return new JsonModel(array('events' => array($event->getId() => $this->getEventJson($event)), 'messages'=>$messages));
    			}
    		} else {
    			if($return){
    				$this->flashMessenger()->addErrorMessage("Impossible d'enregistrer l'évènement.");
    			} else {
    				$messages['error'][0] = "Impossible d'enregistrer l'évènement.";
    			}
    			//traitement des erreurs de validation
    			$this->processFormMessages($form->getMessages(), $messages);
    			if($return){
    				return $this->redirect()->toRoute('application');
    			} else {
    				return new JsonModel(array('messages'=>$messages));
    			}
    		}
    	}
    	
    	
    }
    
    public function subformAction(){
    	$part = $this->params()->fromQuery('part', null);
    	
    	$viewmodel = new ViewModel();
    	$request = $this->getRequest();
    	 
    	//disable layout if request by Ajax
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    	 
    	$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	 
    	$form = $this->getSkeletonForm();
    	
    	if($part){
    		switch ($part) {
    			case 'subcategories':
    				$id = $this->params()->fromQuery('id');
    				$viewmodel->setVariables(array(
    						'part' => $part,
    						'values' => $em->getRepository('Application\Entity\Category')->getChildsAsArray($id),
    				));
    				break;
    			case 'predefined_events':
    				$id = $this->params()->fromQuery('id');
    				$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    				$category = $em->getRepository('Application\Entity\Category')->find($id);
    				$viewmodel->setVariables(array(
    						'part' => $part,
    						'values' => $em->getRepository('Application\Entity\PredefinedEvent')->getEventsWithCategoryAsArray($category),
    				));
    				break;
    			case 'custom_fields':
    				$viewmodel->setVariables(array(
    				'part' => $part,));
    				$form->add(new CustomFieldset($this->getServiceLocator(), $this->params()->fromQuery('id')));
    				break;
    			default:
    				;
    				break;
    		}
    	}
    	$viewmodel->setVariables(array('form' => $form));
    	return $viewmodel;
    }
    
    /**
     * Create a new form
     * @return \Zend\View\Model\ViewModel
     */
    public function formAction(){
    	
    	$viewmodel = new ViewModel();
    	$request = $this->getRequest();
    	
    	//disable layout if request by Ajax    	
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    	  	
    	$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	//création du formulaire : identique en cas de modif ou création
    	$form = $this->getSkeletonForm();
    	 
    	$id = $this->params()->fromQuery('id', null);
    	if($id){ //modification, prefill form
    		try {
    			$event = $em->getRepository('Application\Entity\Event')->find($id);
    		}
    		catch (\Exception $ex) {
    			$viewmodel->setVariables(array('error' => "Impossible de modifier l'évènement."));
    			return $viewmodel;
    		}
    		if(!$event){
    			$viewmodel->setVariables(array('error' => "Impossible de trouver l'évènement demandé."));
    			return $viewmodel;
    		}
    		$cat = $event->getCategory();
    		if($cat && $cat->getParent()){
    			$form->get('categories')->get('subcategories')->setValueOptions(
    					$em->getRepository('Application\Entity\Category')->getChildsAsArray($cat->getParent()->getId()));
    			$form->get('categories')->get('root_categories')->setAttribute('value', $cat->getParent()->getId());
    			$form->get('categories')->get('subcategories')->setAttribute('value', $cat->getId());
    		} else {
    			$form->get('categories')->get('root_categories')->setAttribute('value', $cat->getId());
    		}
    		//custom fields
    		$form->add(new CustomFieldset($this->getServiceLocator(), $cat->getId()));
    		//custom fields values
    		foreach ($em->getRepository('Application\Entity\CustomField')->findBy(array('category'=>$cat->getId())) as $customfield){
    			$customfieldvalue = $em->getRepository('Application\Entity\CustomFieldValue')->findOneBy(array('event'=>$event->getId(), 'customfield'=>$customfield->getId()));
    			if($customfieldvalue){
    				$form->get('custom_fields')->get($customfield->getId())->setAttribute('value', $customfieldvalue->getValue());
    			}
    		}
    		
    		//other values
    		$form->bind($event);
    		$form->setData($event->getArrayCopy());
    		$viewmodel->setVariables(array('event'=>$event));
    	}
    	
    	$viewmodel->setVariables(array('form' => $form));
    	return $viewmodel;
    	 
    }
    
    private function getSkeletonForm($event = null){
    	$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	if(!$event){
    		$event = new Event();
    	}
    	
    	$builder = new AnnotationBuilder();
    	$form = $builder->createForm($event);
    	$form->setHydrator(new DoctrineObject($em, 'Application\Entity\Event'))
    		->setObject($event);    	
    	
    	$form->get('status')
    		->setValueOptions($em->getRepository('Application\Entity\Status')->getAllAsArray());
    	
    	$form->get('impact')
    		->setValueOptions($em->getRepository('Application\Entity\Impact')->getAllAsArray());

    	//add default fieldsets
    	$form->add(new CategoryFormFieldset($em->getRepository('Application\Entity\Category')->getRootsAsArray(null, true)));
 	    	
    	//files
    	$filesFieldset = new Fieldset('fichiers');
    	$file = new File('file1');
    	$file->setLabel(' ')
    	->setAttribute('id', 'file1');
    	
    	$name = new Text('name1');
    	$name->setLabel('Fichier 1 :')->setAttribute('id', 'name1');
    	$name->setAttribute('placeholder', 'Nom du fichier');
    	$filesFieldset->add($name);
    	
    	$form->add($filesFieldset);
    	$filesFieldset->add($file);
    	
    	$form->bind($event);
    	$form->setData($event->getArrayCopy());
    	
    	$form->add(array(
    			'name' => 'submit',
    			'attributes' => array(
    					'type' => 'submit',
    					'value' => 'Ajouter',
    					'class' => 'btn btn-primary',
    			),
    	));
    	
    	return $form;
    }
    
    public function getpredefinedvaluesAction(){
    	$predefinedId = $this->params()->fromQuery('id',null);
    	$json = array();
    	$defaultvalues = array();
    	$customvalues = array();
    	
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$entityService = $this->getServiceLocator()->get('EventService');
    	
    	$predefinedEvt = $objectManager->getRepository('Application\Entity\PredefinedEvent')->find($predefinedId);
    	
    	$defaultvalues['punctual'] = $predefinedEvt->isPunctual();
		//TODO Impact
    	$json['defaultvalues'] = $defaultvalues;
    	
    	foreach ($predefinedEvt->getCustomFieldsValues() as $customfieldvalue){
    		$customvalues[$customfieldvalue->getCustomField()->getId()] = $customfieldvalue->getValue();
    	}
    	
    	$json['customvalues'] = $customvalues;
    	
    	return new JsonModel($json);
    }
    
    public function getactionsAction(){
    	$parentId = $this->params()->fromQuery('id', null);
    	$json = array();
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	foreach ($objectManager->getRepository('Application\Entity\PredefinedEvent')->findBy(array('parent' => $parentId), array('place' => 'DESC')) as $action){
    		$json[$action->getId()] = array('name' =>  $this->getServiceLocator()->get('EventService')->getName($action),
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
    		$event->setStatus($objectManager->getRepository('Application\Entity\Status')->findOneBy(array('defaut'=>true, 
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
     *		'punctual' => boolean
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
    	
    	$lastmodified = $this->params()->fromQuery('lastmodified', null);
    	
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	$criteria = Criteria::create();
    	if($lastmodified){
    		$criteria->andWhere(Criteria::expr()->gte('last_modified_on', $lastmodified));
    	} else {
    		$now = new \DateTime('NOW');
    		$criteria->andWhere(Criteria::expr()->gte('startdate', $now->sub(new \DateInterval('P3D'))));
    	}
    	$events = $objectManager->getRepository('Application\Entity\Event')->matching($criteria);
    	
    	$json = array();
    	foreach ($events as $event){ 		
    		$json[$event->getId()] = $this->getEventJson($event);
    	}
    	
    	return new JsonModel($json);
    }
    
    private function getEventJson($event){
    	$eventservice = $this->getServiceLocator()->get('EventService');
    	$json = array('name' => $eventservice->getName($event),
    					'start_date' => $event->getStartdate()->format(DATE_RFC2822),
    					'end_date' => ($event->getEnddate() ? $event->getEnddate()->format(DATE_RFC2822) : null),
    					'punctual' => $event->isPunctual(),
    					'category_root' => ($event->getCategory()->getParent() ? $event->getCategory()->getParent()->getName() : $event->getCategory()->getName()),
    					'category_root_short' => ($event->getCategory()->getParent() ? $event->getCategory()->getParent()->getShortName() : $event->getCategory()->getShortName()),
    					'category' => $event->getCategory()->getName(),
    					'category_short' => $event->getCategory()->getShortName(),
    					'category_compact' => $event->getCategory()->isCompactMode(),
    					'status_name' => $event->getStatus()->getName(),
    					'impact_value' => $event->getImpact()->getValue(),
    					'impact_name' => $event->getImpact()->getName(),
    					'impact_style' => $event->getImpact()->getStyle(),
    	);
    	
    	$actions = array();
    	foreach ($event->getChilds() as $child){
    		$actions[$eventservice->getName($child)] = $child->getStatus()->isOpen();
    	}
    	$json['actions'] = $actions;
    	
    	return $json;
    }
    
    /**
     * Liste des catégories racines visibles timeline
     * Au format JSON
     */
    public function getcategoriesAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$json = array();
    	$criteria = Criteria::create()->andWhere(Criteria::expr()->isNull('parent'));
    	$criteria->andWhere(Criteria::expr()->eq('timeline', true));
    	$categories = $objectManager->getRepository('Application\Entity\Category')->matching($criteria);
    	foreach ($categories as $category){
    		$json[$category->getId()] = array(
    			'name' => $category->getName(),
    			'short_name' => $category->getShortName(),
    			'color' => $category->getColor(),
    		);
    	}
    	return new JsonModel($json);
    }
    
    /**
     * Liste des impacts au format JSON
     */
    public function getimpactsAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$json = array();
    	$impacts = $objectManager->getRepository('Application\Entity\Impact')->findAll();
    	foreach ($impacts as $impact){
    		$json[$impact->getId()] = array(
    				'name' => $impact->getName(),
    				'style' => $impact->getStyle(),
    				'value' => $impact->getValue(),
    		);
    	}
    	return new JsonModel($json);
    }
    
    public function gethistoryAction(){

    	$viewmodel = new ViewModel();
    	$request = $this->getRequest();
    	 
    	//disable layout if request by Ajax
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    	
    	$evtId = $this->params()->fromQuery('id', null);
    	
    	$eventservice = $this->getServiceLocator()->get('EventService');
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	$event = $objectManager->getRepository('Application\Entity\Event')->find($evtId);
    	
    	$history = null;
    	if($event){
    		$history = $eventservice->getHistory($event);
    	}
    	
    	$viewmodel->setVariable('history', $history);
    	
    	return $viewmodel;
    }
    
    /**
     * Usage :
     * $this->url('application', array('controller' => 'events'))+'/changefield?id=<id>&field=<field>&value=<newvalue>'
     * @return JSon with messages
     */
    public function changefieldAction(){
    	$id = $this->params()->fromQuery('id', 0);
    	$field = $this->params()->fromQuery('field', 0);
    	$value = $this->params()->fromQuery('value', 0);
    	$messages = array();
    	if($id){
    		$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    		$event = $objectManager->getRepository('Application\Entity\Event')->find($id);
    		if ($event) {
				switch ($field) {
					case 'enddate' :
						$event->setEndDate(new DateTime($value));
						$objectManager->flush();
						$objectManager->persist($event);
						$messages['success'][0] = "Date et heure de fin modifiées.";
						break;	
					default :
						;
						break;
				}
    		}
    	} else {
    		$messages['error'][0] = "Impossible de modifier l'évènement.";
    	}
    	return new JsonModel($messages);
    }
    
}
