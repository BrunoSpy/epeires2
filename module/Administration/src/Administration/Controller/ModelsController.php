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
use Application\Form\CustomFieldSet;
use Zend\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

class ModelsController extends AbstractActionController
{
    public function indexAction()
    {
    	$viewmodel = new ViewModel();
    	$this->layout()->title = "Personnalisation > Modèles";
    	
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	 
    	$criteria = Criteria::create()->andWhere(Criteria::expr()->isNull('parent'));
    	$models = $objectManager->getRepository('Application\Entity\PredefinedEvent')->matching($criteria);
    	
    	$viewmodel->setVariable('models', $models);
    	
        return $viewmodel;
    }
    
    public function saveAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		
    		$id = $post['id'];
    		
    		$datas = $this->getForm($id);
    		
    		$form = $datas['form'];
    		$pevent = $datas['pevent'];
    		$form->setData($post);
    		
    		if($form->isValid()){
    			//category, may be disable
    			if($post['category']){
    				$pevent->setCategory($objectManager->getRepository('Application\Entity\Category')->find($post['category']));
    			}
    			$pevent->setImpact($objectManager->getRepository('Application\Entity\Impact')->find($post['impact']));
    			//predefined custom field values
    			if(isset($post['custom_fields'])){
    				foreach ($post['custom_fields'] as $key => $value){
    					$customfield = $objectManager->getRepository('Application\Entity\CustomField')->findOneBy(array('name'=>$key));
    					if($customfield){
    						$customfieldvalue = $objectManager->getRepository('Application\Entity\PredefinedCustomFieldValue')->findOneBy(array('customfield'=>$customfield->getId(), 'event'=>$id));
    						if(!$customfieldvalue){
    							$customfieldvalue = new CustomFieldValue();
    							$customfieldvalue->setEvent($pevent);
    							$customfieldvalue->setCustomField($customfield);
    						}
    						$customfieldvalue->setValue($value);
    						$objectManager->persist($customfieldvalue);
    					}
    				}
    			}
    			$objectManager->persist($pevent);
    			$objectManager->flush();
    		} else {
    			$this->logger->log(\Zend\Log\Logger::ALERT, "Formulaire non valide.");
    			$this->flashMessenger()->addErrorMessage("Impossible de sauver l'évènement.");
    			//traitement des erreurs de validation
    			$this->processFormMessages($form->getMessages());
    		}
    		
    	}
    	
    	return new JsonModel();
    }
    
    public function formAction(){
    	
    	$request = $this->getRequest();
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$viewmodel = new ViewModel();
    	//disable layout if request by Ajax
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    	 
    	$id = $this->params()->fromQuery('id', null);
    	$action = $this->params()->fromQuery('action', false);

    	if($id){//fiche reflexe
    		$childs = $objectManager->getRepository('Application\Entity\PredefinedEvent')->findBy(array('parent'=>$id), array('place'=>'asc'));
    		$viewmodel->setVariables(array('childs' => $childs));
    	}
    	
    	$form = $this->getForm($id)['form'];
    	
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
    
    private function getForm($id = null){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$pevent = new PredefinedEvent();
    	$builder = new AnnotationBuilder();
    	$form = $builder->createForm($pevent);
    	$form->setHydrator(new DoctrineObject($objectManager, 'Application\Entity\PredefinedEvent'))
    	->setObject($pevent);
    	
    	$form->get('impact')->setValueOptions($objectManager->getRepository('Application\Entity\Impact')->getAllAsArray());
    	 
    	$form->get('category')->setValueOptions($objectManager->getRepository('Application\Entity\Category')->getChildsAsArray());
    	
    	if($id){//modification d'un evt
    		$pevent = $objectManager->getRepository('Application\Entity\PredefinedEvent')->find($id);
    		if($pevent){
    			$form->bind($pevent);
    			$form->setData($pevent->getArrayCopy());
    			//category must not be modified
    			$form->get('category')->setAttribute('disabled', true);
    			//custom field values
    			$customfields = $objectManager->getRepository('Application\Entity\CustomField')->findBy(array('category'=>$pevent->getCategory()->getId()));
    			if(count($customfields) > 1 ){
    				$form->add(new CustomFieldset($objectManager, $pevent->getCategory()->getId()));
    				foreach ($customfields as $customfield){
    					$customfieldvalue = $objectManager->getRepository('Application\Entity\PredefinedCustomFieldValue')
    					->findOneBy(array('predefinedevent'=>$pevent->getId(), 'customfield'=>$customfield->getId()));
    					if($customfieldvalue){
    						$form->get('custom_fields')->get($customfield->getName())->setAttribute('value', $customfieldvalue->getValue());
    					}
    				}
    			}
    		}
    	}
    	
    	return array('form'=>$form, 'pevent'=>$pevent);
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
     	
     	$form->add(new CustomFieldset($objectManager, $id));
     	
     	$viewmodel->setVariables(array('form' =>$form));
     	return $viewmodel;
     	
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
}
