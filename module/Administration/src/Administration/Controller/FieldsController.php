<?php
/*
 *  This file is part of Epeires².
 *  Epeires² is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  Epeires² is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with Epeires².  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Administration\Controller;

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

use Application\Controller\FormController;
use Application\Entity\CustomField;

use Zend\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

/**
 * 
 * @license https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 * @author Bruno Spyckerelle
 */
class FieldsController extends FormController {
  
    public function fieldupAction(){
    	$id = $this->params()->fromQuery('id', null);
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	if($id){
    		$customfield = $objectManager->getRepository('Application\Entity\CustomField')->find($id);
    		if($customfield){
    			$customfield->setPlace($customfield->getPlace() - 1);
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
    			$customfield->setPlace($customfield->getPlace() + 1);
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
                        if($id){
                            //modification d'un champ
                            //si changement de type de champ, il faut vérifier la cohérence pour éviter un crash futur
                            $fieldtype = $customfield->getType();
                            if($fieldtype->getId() != $post['type']){
                                
                            }
                        } else {
                            $customfield->setType($objectManager->getRepository('Application\Entity\CustomFieldType')->find($post['type']));
                        }
			$objectManager->persist($customfield);
                        try{
                            $objectManager->flush();
                            $this->flashMessenger()->addSuccessMessage("Champ enregistré");
                        } catch (\Exception $ex) {
                            $this->flashMessenger()->addErrorMessage($ex->getMessage());
                        }
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
                                                                        'help' => $customfield->getTooltip(),
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

}
