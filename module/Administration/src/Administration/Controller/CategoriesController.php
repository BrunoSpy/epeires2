<?php
/**
 * Epeires 2
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Administration\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\Common\Collections\Criteria;
use Application\Entity\Category;
use Zend\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

class CategoriesController extends AbstractActionController{
    
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
    	
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	$criteria = Criteria::create()->andWhere(Criteria::expr()->isNull('parent'));
    	
    	$rootcategories = $objectManager->getRepository('Application\Entity\Category')->matching($criteria);
    	
    	$subcategories = array();
    	foreach ($rootcategories as $category){
    		$criteria = Criteria::create()->andWhere(Criteria::expr()->eq('parent', $category->getId()));
    		$subcategories[$category->getId()] = $objectManager->getRepository('Application\Entity\Category')->matching($criteria);
    	}
    	
    	$events = array();
    	$models = array();
    	$fields = array();
    	foreach ($objectManager->getRepository('Application\Entity\Category')->findAll() as $cat){
    		$events[$cat->getId()] = count($objectManager->getRepository('Application\Entity\Event')->findBy(array('category' => $cat->getId())));
    		$models[$cat->getId()] = count($objectManager->getRepository('Application\Entity\PredefinedEvent')->findBy(array('category' => $cat->getId(), 'parent'=> null)));
    		$fields[$cat->getId()] = count($objectManager->getRepository('Application\Entity\CustomField')->findBy(array('category' => $cat->getId())));
    	}
    	
    	$viewmodel->setVariables(array('categories' => $rootcategories, 
    			'subcategories' => $subcategories,
    			'events' => $events,
    			'models' => $models,
    			'fields' => $fields,
    	));
    	
    	$this->layout()->title = "Personnalisation > Catégories";
    	
    	return $viewmodel;
    }
    
    public function formAction(){
    	
    	$request = $this->getRequest();
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$viewmodel = new ViewModel();
    	//disable layout if request by Ajax
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    	
    	$id = $this->params()->fromQuery('id', null);
    	
    	$category = new Category();
    	$builder = new AnnotationBuilder();
    	$form = $builder->createForm($category);
    	$form->setHydrator(new DoctrineObject($objectManager, 'Application\Entity\Category'))
    		->setObject($category);
    	
        $form->get('parent')->setValueOptions($objectManager->getRepository('Application\Entity\Category')->getRootsAsArray($id));
        
    	if($id){
    		$category = $objectManager->getRepository('Application\Entity\Category')->find($id);
    		if($category){
    			$form->bind($category);
    			$form->setData($category->getArrayCopy());
    		}
    	}
    	
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
    
    public function saveAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		
			if($post['id']){
				$category = $objectManager->getRepository('Application\Entity\Category')->find($post['id']);
			} else {
				$category = new Category();
			}
    		
			$builder = new AnnotationBuilder();
			$form = $builder->createForm($category);
			$form->setHydrator(new DoctrineObject($objectManager, 'Application\Entity\Category'))
			->setObject($category);
			$form->get('parent')->setValueOptions($objectManager->getRepository('Application\Entity\Category')->getRootsAsArray($post['id']));
			$form->bind($category);
			$form->setData($post);
			
			if($form->isValid()){
				$category->setParent($objectManager->getRepository('Application\Entity\Category')->find($post['parent']));
				$objectManager->persist($category);
				$objectManager->flush();
				$this->flashMessenger()->addSuccessMessage("Evènement modifié");
			} else {
				$this->flashMessenger()->addErrorMessage("Impossible de modifier l'évènement.");
				//traitement des erreurs de validation
				$this->processFormMessages($form->getMessages());
			}
    	}
    	
    	return $this->redirect()->toRoute('administration', array('controller'=>'categories'));
    }
    
    public function deleteAction(){
    	$id = $this->params()->fromQuery('id', null);
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$category = $objectManager->getRepository('Application\Entity\Category')->find($id);
    	
    	if($category){
    		$childs = $objectManager->getRepository('Application\Entity\Category')->findBy(array('parent' => $id));
    		foreach ($childs as $child){ //delete category
    			$child->setParent(null);
    			$objectManager->persist($child);
    		}
    		//suppression des evts associés par cascade
    		$objectManager->remove($category);
    		$objectManager->flush();
    	}
    	
    	return $this->redirect()->toRoute('administration', array('controller'=>'categories'));
    }
    
    public function fieldsAction(){
    	$request = $this->getRequest();
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$viewmodel = new ViewModel();
    	//disable layout if request by Ajax
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    	 
    	$id = $this->params()->fromQuery('id', null);
    	
    	$fields = $objectManager->getRepository('Application\Entity\CustomField')->findBy(array('category' => $id));
    	    	
    	$viewmodel->setVariables(array('fields' => $fields, 'categoryid' => $id));
    	return $viewmodel;
    }
    
    //TODO factoriser
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
