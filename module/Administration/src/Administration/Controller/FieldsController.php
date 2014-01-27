<?php
/**
 * Epeires 2
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Administration\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Entity\CustomField;
use Zend\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

class FieldsController extends AbstractActionController
{
    public function indexAction()
    {
   	
        return array();
    }
    
    public function fieldupAction(){
    	$id = $this->params()->fromQuery('id', null);
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	if($id){
    		$customfield = $objectManager->getRepository('Application\Entity\CustomField')->find($id);
    		if($customfield){
    			//get the field just before
    			$qb = $objectManager->createQueryBuilder();
    			$qb->select('f')
    				->from('Application\Entity\CustomField', 'f')
    				->andWhere('f.category = '.$customfield->getCategory()->getId())
    				->andWhere('f.place < '.$customfield->getPlace())
    				->orderBy('f.place','DESC')
    				->setMaxResults(1);
    			$result = $qb->getQuery()->getSingleResult();
    			//switch places
    			$temp = $result->getPlace();
    			$result->setPlace($customfield->getPlace());
    			$customfield->setPlace($temp);
    			$objectManager->persist($result);
    			$objectManager->persist($customfield);
    			$objectManager->flush();
    		}
    	}
    	
    	//on gruge en renvoyant un json vide
    	return new JsonModel();
    }
    
    public function fielddownAction(){
    	$id = $this->params()->fromQuery('id', null);
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	if($id){
    		$customfield = $objectManager->getRepository('Application\Entity\CustomField')->find($id);
    		if($customfield){
    			//get the field just after
    			$qb = $objectManager->createQueryBuilder();
    			$qb->select('f')
    			->from('Application\Entity\CustomField', 'f')
    			->andWhere('f.category = '.$customfield->getCategory()->getId())
    			->andWhere('f.place > '.$customfield->getPlace())
    			->orderBy('f.place','ASC')
    			->setMaxResults(1);
    			$result = $qb->getQuery()->getSingleResult();
    			//switch places
    			$temp = $result->getPlace();
    			$result->setPlace($customfield->getPlace());
    			$customfield->setPlace($temp);
    			$objectManager->persist($result);
    			$objectManager->persist($customfield);
    			$objectManager->flush();
    		}
    	}
    	 
    	//on gruge en renvoyant un json vide
    	return new JsonModel();
    }
    
    public function deleteAction(){
    	$id = $this->params()->fromQuery('id', null);
    	$returnRoute = $this->params()->fromQuery('return', null);
    	
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	 
    	$customfield = $objectManager->getRepository('Application\Entity\CustomField')->find($id);
    	
    	$messages = array();
    	
    	if($customfield){
    		$objectManager->remove($customfield);
    		try {
    			$objectManager->flush();
    			$messages['success'][] = "Champ correctement supprimé";
    		} catch (\Exception $e) {
				$messages['error'][] = "Impossible de supprimer le champ.";
				$messages['error'][] = $e->getMessage();    			
    		}	
    	}
    	
    	if($returnRoute){
    		return $this->redirect()->toRoute('administration', array('controller'=>$returnRoute));
    	} else {
    		return new JsonModel($messages);
    	}
    }
    
    public function saveAction(){
    	$returnRoute = $this->params()->fromQuery('return', null);
    	
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		$id = $post['id'];
 		    		
    		$datas = $this->getForm($id);
    		$form = $datas['form'];
    		$customfield = $datas['customfield'];
    		$form->setData($post);
    		
    		if($form->isValid()){
    			$customfield->setCategory($objectManager->getRepository('Application\Entity\Category')->find($post['category']));
    			$customfield->setType($objectManager->getRepository('Application\Entity\CustomFieldType')->find($post['type']));
				// calculate next order avalaible
				$qb = $objectManager->createQueryBuilder ();
				$qb->select ( 'MAX(f.place)' )
					->from ( 'Application\Entity\CustomField', 'f' )
					->where ( 'f.category = ' . $post ['category'] );
				
				if(!$id){ //new field : calculate last place
					$result = $qb->getQuery ()->getSingleResult ();
					$order = $result[1] + 1;    			
    				$customfield->setPlace($order);
				}
				
				$objectManager->persist($customfield);
    			$objectManager->flush();
    			$this->flashMessenger()->addSuccessMessage("Champ modifié");
    		} else {
    			$this->flashMessenger()->addErrorMessage("Impossible de modifier le champ.");
    			//traitement des erreurs de validation
    			$this->processFormMessages($form->getMessages());
    		}
    	}
    	if($returnRoute){
    		return $this->redirect()->toRoute('administration', array('controller'=>$returnRoute));
    	} else {
    		return new JsonModel(array('id'=>$customfield->getId(), 
    									'name' => $customfield->getName(), 
    									'type' => $customfield->getType()->getName(), 
    									'defaut'=>$customfield->getDefaultValue()));
    	}
    }
    
    public function formAction(){

    	$request = $this->getRequest();
    	$viewmodel = new ViewModel();
    	//disable layout if request by Ajax
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    	 
    	$id = $this->params()->fromQuery('id', null);
    //	$return = $this->params()->fromQuery('return', null);
    	$categoryid = $this->params()->fromQuery('categoryid', null);
     	
    	$getform = $this->getForm($id, $categoryid);
    	
    	$viewmodel->setVariables(array('form' => $getform['form'],'id'=>$id));
    	return $viewmodel;
    }
    
    
    
    private function getForm($id = null, $categoryid = null){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$customfield = new CustomField();
    	$builder = new AnnotationBuilder();
    	$form = $builder->createForm($customfield);
    	$form->setHydrator(new DoctrineObject($objectManager))
    	->setObject($customfield);
    	 
    	$form->get('category')->setValueOptions($objectManager->getRepository('Application\Entity\Category')->getAllAsArray());
    	if($categoryid){
    		$form->get('category')->setAttribute('value', $categoryid);
    	}
    	
    	$form->get('type')->setValueOptions($objectManager->getRepository('Application\Entity\CustomFieldType')->getAllAsArray());
    	
    	if($id){
    		$customfield = $objectManager->getRepository('Application\Entity\CustomField')->find($id);
    		if($customfield){
    			$form->bind($customfield);
    			$form->setData($customfield->getArrayCopy());
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
    	
    	return array('form' => $form, 'customfield'=>$customfield);
    }
    
    //TODO Factoriser
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
}
