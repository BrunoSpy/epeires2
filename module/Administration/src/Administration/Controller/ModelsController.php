<?php
/**
 * Epeires 2
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Administration\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Doctrine\Common\Collections\Criteria;
use Application\Entity\PredefinedEvent;
use Application\Entity\CustomFieldValue;
use Application\Form\CustomFieldset;
use Zend\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Application\Controller\FormController;

class ModelsController extends FormController
{
    public function indexAction()
    {
    	$viewmodel = new ViewModel();
    	$this->layout()->title = "Personnalisation > Modèles";
    	
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	 
    	$criteria = Criteria::create()->andWhere(Criteria::expr()->isNull('parent'));
    	$models = $objectManager->getRepository('Application\Entity\PredefinedEvent')->matching($criteria);
    	
    	$actions = array();
    	foreach ($models as $model){
    		$criteria = Criteria::create()->andWhere(Criteria::expr()->eq('parent', $model));
    		$actions[$model->getId()] = count($objectManager->getRepository('Application\Entity\PredefinedEvent')->matching($criteria));
    	}
    	
    	$viewmodel->setVariables(array('models'=> $models, 'actions'=>$actions));
    	
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
    
    public function deleteAction(){
    	$id = $this->params()->fromQuery('id', null);
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	if($id){
    		$pevent = $objectManager->getRepository('Application\Entity\PredefinedEvent')->find($id);
    		if($pevent){
    			$objectManager->remove($pevent);
    			$objectManager->flush();
    		}
    	}
    	$redirect = $this->params()->fromQuery('redirect', true);
    	if($redirect){
    		return $this->redirect()->toRoute('administration', array('controller'=>'models'));
    	} else {
    		return new JsonModel();
    	}
    }
    
    public function upAction(){
    	$messages = array();
    	$id = $this->params()->fromQuery('id', null);
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	if($id){
    		$pevent = $objectManager->getRepository('Application\Entity\PredefinedEvent')->find($id);
    		if($pevent){
    			$pevent->setPlace($pevent->getPlace() -1);
    			$objectManager->persist($pevent);
    			try {
    				$objectManager->flush();
    				$messages['success'][] = "Élément correctement modifié.";
    			} catch (\Exception $e) {
    				$messages['error'][] = $e->getMessage();
    			}
    		} else {
    			$messages['error'][] = "Impossible de trouver l'élément";
    		}
    	}
    	return new JsonModel($messages);
    }
    
    public function downAction(){
    	$messages = array();
    	$id = $this->params()->fromQuery('id', null);
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	if($id){
    		$pevent = $objectManager->getRepository('Application\Entity\PredefinedEvent')->find($id);
    		if($pevent){
    			$pevent->setPlace($pevent->getPlace() +1);
    			$objectManager->persist($pevent);
    			try {
    				$objectManager->flush();
    				$messages['success'][] = "Élément correctement modifié.";
    			} catch (\Exception $e) {
    				$messages['error'][] = $e->getMessage();
    			}
    		} else {
    			$messages['error'][] = "Impossible de trouver l'élément";
    		}
    	}
    	return new JsonModel($messages);
    }
    
    public function saveAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$messages = array();
    	
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		$id = $post['id'];
    		$catid = $this->params()->fromQuery('catid', null);
    		$datas = $this->getForm($id, null, $catid);
    		
    		$form = $datas['form'];
    		$pevent = $datas['pevent'];
    		$form->setData($post);
    
    		if($form->isValid()){
    			//category, may be disable
    			if($post['category']){
    				$category = $post['category'];
    				$pevent->setCategory($objectManager->getRepository('Application\Entity\Category')->find($post['category']));
    			} else if($pevent->getCategory()){
    				$category = $pevent->getCategory()->getId();
    			} else { //last chance cat id passed by query
    				$category = $catid;
    				$pevent->setCategory($objectManager->getRepository('Application\Entity\Category')->find($catid));
    			}
    			if(!$id){//if modification : link to parent and calculate position
    				//link to parent
    				if(isset($post['parent'])){
    					$pevent->setParent($objectManager->getRepository('Application\Entity\PredefinedEvent')->find($post['parent']));
	    				//calculate order (order by parent)
    					$qb = $objectManager->createQueryBuilder ();
    					$qb->select ( 'MAX(f.place)' )
    					->from ( 'Application\Entity\PredefinedEvent', 'f' )
    					->where ( 'f.parent = ' . $post ['parent'] );
    					$result = $qb->getQuery()->getResult();
    					if($result[0][1]){
    						$pevent->setPlace($result[0][1]+1);
    					} else {
    						$pevent->setPlace(1);
    					}
    				} else {
	    				//no parent => model => order by category
    					$qb = $objectManager->createQueryBuilder ();
    					$qb->select ( 'MAX(f.place)' )
    					->from ( 'Application\Entity\PredefinedEvent', 'f' )
    					->where ( 'f.category = ' . $category );
	    				$result = $qb->getQuery()->getResult();
    					if($result[0][1]){
	    					$pevent->setPlace($result[0][1]+1);
    					} else {
    						$pevent->setPlace(1);
    					}
    				}
    			}
    			$pevent->setImpact($objectManager->getRepository('Application\Entity\Impact')->find($post['impact']));
    			$objectManager->persist($pevent);
    			//predefined custom field values
    			if(isset($post['custom_fields'])){
    				foreach ($post['custom_fields'] as $key => $value){
    					$customfield = $objectManager->getRepository('Application\Entity\CustomField')->findOneBy(array('id'=>$key));
    					if($customfield){
    						$customfieldvalue = $objectManager->getRepository('Application\Entity\CustomFieldValue')->findOneBy(array('customfield'=>$customfield->getId(), 'event'=>$id));
    						if(!$customfieldvalue){
    							$customfieldvalue = new CustomFieldValue();
    							$customfieldvalue->setEvent($pevent);
    							$customfieldvalue->setCustomField($customfield);
    							$pevent->addCustomFieldValue($customfieldvalue);
    						}
    						$customfieldvalue->setValue($value);
    						$objectManager->persist($customfieldvalue);
    					}
    				}
    			}
    			$objectManager->flush();
    			$this->flashMessenger()->addSuccessMessage("Modèle ".$pevent->getName()." enregistré.");
    			$this->processFormMessages($form->getMessages());
    		} else {
    			//traitement des erreurs de validation
    			$pevent = null;
    			$this->processFormMessages($form->getMessages(),$messages);
    		}
    		
    	}
    	$json = array('messages' => $messages);
    	if($pevent){
    		$json['id'] = $pevent->getId();
    		$json['name'] = $this->getServiceLocator()->get('EventService')->getName($pevent);
    		$json['impactstyle'] = $pevent->getImpact()->getStyle();
    		$json['impactname'] = $pevent->getImpact()->getName();  			
    	}
    	
    	return new JsonModel($json);
    }
    
    public function formAction(){
    	
    	$request = $this->getRequest();
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$viewmodel = new ViewModel();
    	//disable layout if request by Ajax
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    	 
    	$id = $this->params()->fromQuery('id', null);
    	$action = $this->params()->fromQuery('action', false);
    	$parentid = $this->params()->fromQuery('parentid', null);
    	$catid = $this->params()->fromQuery('catid', null);

    	if($id){//fiche reflexe
    		$childs = $objectManager->getRepository('Application\Entity\PredefinedEvent')->findBy(array('parent'=>$id), array('place'=>'asc'));
    		$viewmodel->setVariables(array('childs' => $childs));
    	}
    	
    	$getform = $this->getForm($id, $parentid, $catid, $action);
    	$form = $getform['form'];
    	
    	$form->add(array(
    			'name' => 'submit',
    			'attributes' => array(
    					'type' => 'submit',
    					'value' => 'Enregistrer',
    					'class' => 'btn btn-primary',
    			),
    	));
    	    	
    	$viewmodel->setVariables(array('form' =>$form, 'action' => $action));
    	return $viewmodel;
    	
    }
    
    private function getForm($id = null, $parentid = null, $catid = null, $action = false){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$pevent = new PredefinedEvent();
    	$builder = new AnnotationBuilder();
    	$form = $builder->createForm($pevent);
    	$form->setHydrator(new DoctrineObject($objectManager, 'Application\Entity\PredefinedEvent'))
    	->setObject($pevent);
    	
    	$form->get('impact')->setValueOptions($objectManager->getRepository('Application\Entity\Impact')->getAllAsArray());
    	 
    	$form->get('category')->setValueOptions($objectManager->getRepository('Application\Entity\Category')->getChildsAsArray());
    	
    	$form->get('parent')->setValueOptions($objectManager->getRepository('Application\Entity\PredefinedEvent')->getRootsAsArray());

    	$form->get('organisation')->setValueOptions($objectManager->getRepository('Application\Entity\Organisation')->getAllAsArray());
    	
    	if($catid){
    		//set category
    		$form->get('category')->setAttribute('value', $catid);
    		//disable category modification
    		$form->get('category')->setAttribute('disabled', 'disabled');
    		//and change validator
    		$form->getInputFilter()->get('category')->setRequired(false);
    		//add custom fields input
    		$form->add(new CustomFieldset($this->getServiceLocator(), $catid));
    	}
    	
    	if($id){//modification d'un evt
    		$pevent = $objectManager->getRepository('Application\Entity\PredefinedEvent')->find($id);
    		if($pevent){
    			$form->get('zonefilters')->setValueOptions(
    					$objectManager->getRepository('Application\Entity\QualificationZone')->getAllAsArray($pevent->getOrganisation()));
    			$form->bind($pevent);
    			//disable category modification
    			$form->get('category')->setAttribute('disabled', 'disabled');
    			//and change validator
    			$form->getInputFilter()->get('category')->setRequired(false);
    			$form->setData($pevent->getArrayCopy());
    			//custom field values
    			$customfields = $objectManager->getRepository('Application\Entity\CustomField')->findBy(array('category'=>$pevent->getCategory()->getId()));
    			if(count($customfields) > 0 ){
    				$form->add(new CustomFieldset($this->getServiceLocator(), $pevent->getCategory()->getId()));
    				foreach ($customfields as $customfield){
    					$customfieldvalue = $objectManager->getRepository('Application\Entity\CustomFieldValue')
    					->findOneBy(array('event'=>$pevent->getId(), 'customfield'=>$customfield->getId()));
    					if($customfieldvalue){
    						$form->get('custom_fields')->get($customfield->getId())->setAttribute('value', $customfieldvalue->getValue());
    					}
    				}
    			}
    		}
    	}
    	
    	if($parentid){//action reflexe
    		$form->get('parent')->setAttribute('value', $parentid);
    		$parent = $objectManager->getRepository('Application\Entity\PredefinedEvent')->find($parentid);
    		if($parent){
    			$form->get('organisation')->setAttribute('value', $parent->getOrganisation()->getId());
    		} else {
    			//TODO throw exception
    		}
    	}
    	
    	return array('form'=>$form, 'pevent'=>$pevent);
    }
    
    public function listAction(){
    	$request = $this->getRequest();
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$id = $this->params()->fromQuery('id', null); //categoryid
    	$viewmodel = new ViewModel();
    	//disable layout if request by Ajax
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    	if($id){
    		$models = $objectManager->getRepository('Application\Entity\PredefinedEvent')->findBy(array('category'=>$id, 'parent' => null), array('place' => 'ASC'));
    		$viewmodel->setVariables(array('models'=>$models, 'catid'=>$id));
    	}
    	return $viewmodel;
    }
    
    public function customfieldsAction(){
     	$request = $this->getRequest();
     	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
     	$viewmodel = new ViewModel();
     	//disable layout if request by Ajax
     	$viewmodel->setTerminal($request->isXmlHttpRequest());
     	
     	$id = $this->params()->fromQuery('id', null); //categoryid
     	
     	$pevent = new PredefinedEvent();
     	$builder = new AnnotationBuilder();
     	$form = $builder->createForm($pevent);
     	$form->setHydrator(new DoctrineObject($objectManager, 'Application\Entity\PredefinedEvent'))
     	->setObject($pevent);
     	
     	$form->add(new CustomFieldset($this->getServiceLocator(), $id));
     	
     	$viewmodel->setVariables(array('form' =>$form));
     	return $viewmodel;
     	
     }
     
     public function getzonefiltersAction(){
     	$orgid = $this->params()->fromQuery('id', null);
     	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
     	$org = $objectManager->getRepository('Application\Entity\Organisation')->find($orgid);
     	$zonefilters = null;
     	if($org) {
     		$zonefilters = $objectManager->getRepository('Application\Entity\QualificationZone')->getAllAsArray($org);
     	} else {
     		//TODO
     	}
     	return new JsonModel($zonefilters);
     }
     
}
