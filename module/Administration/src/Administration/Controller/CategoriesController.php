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
use Application\Entity\Category;
use Application\Entity\CustomField;
use Zend\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Application\Controller\FormController;
use Zend\Form\Element\Select;
use Application\Entity\RadarCategory;
use Application\Entity\AntennaCategory;
use Application\Entity\FrequencyCategory;
use Application\Entity\BrouillageCategory;

class CategoriesController extends FormController{
    
	public function indexAction(){
   		
    	$viewmodel = new ViewModel();
    	 
    	$return = array();
    	 
    	if($this->flashMessenger()->hasErrorMessages()){
    		$return['error'] =  $this->flashMessenger()->getErrorMessages();
    	}
    	 
    	if($this->flashMessenger()->hasSuccessMessages()){
    		$return['success'] =  $this->flashMessenger()->getSuccessMessages();
    	}
    	 
    	$this->flashMessenger()->clearMessages();
    	     	
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	$criteria = Criteria::create()->andWhere(Criteria::expr()->isNull('parent'));
        $criteria->orderBy(array("place" => Criteria::ASC));
    	
    	$rootcategories = $objectManager->getRepository('Application\Entity\Category')->matching($criteria);
    	
    	$subcategories = array();
    	foreach ($rootcategories as $category){
    		$criteria = Criteria::create()->andWhere(Criteria::expr()->eq('parent', $category));
                $criteria->orderBy(array("place" => Criteria::ASC));
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
    			'messages' => $return,
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
    	$form->setHydrator(new DoctrineObject($objectManager))
    		->setObject($category);
    	
        $form->get('parent')->setValueOptions($objectManager->getRepository('Application\Entity\Category')->getRootsAsArray($id));
        
        $form->get('readroles')->setValueOptions($objectManager->getRepository('Core\Entity\Role')->getAllAsArray());
        
        $type = new Select('type');
        $type->setValueOptions(Category::getTypeValueOptions());
        $type->setLabel('Type : ');
        $form->add($type);
        
    	if($id){
    		//bind to the category
    		$category = $objectManager->getRepository('Application\Entity\Category')->find($id);
    		if($category){
    			if($category instanceof RadarCategory){
    				$form->get('type')->setValue('radar');
    			} else if($category instanceof AntennaCategory){
    				$form->get('type')->setValue('antenna');
    			} else if($category instanceof FrequencyCategory){
    				$form->get('type')->setValue('frequency');
    			} else if($category instanceof BrouillageCategory){
				$form->get('type')->setValue('brouillage');
    			}
    			
    			$form->get('type')->setAttribute('disabled', true);
    			
    			//select parent
    			if($category->getParent()){
    				$form->get('parent')->setAttribute('value', $category->getParent()->getId());
    			}
    			//fill title fields available
    			$customfields = array();
    			foreach($category->getCustomfields() as $field){
    				$customfields[$field->getId()] = $field->getName();
    			}
    			
    			$form->get('fieldname')->setValueOptions($customfields);
    			
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
    		$fieldname = null; 		
			if($post['id']){
				$category = $objectManager->getRepository('Application\Entity\Category')->find($post['id']);
			} else {
				if($post['type'] == 'radar'){
					$category = $this->getServiceLocator()->get('categoryfactory')->createRadarCategory();
				} else if($post['type'] == 'antenna') {
					$category = $this->getServiceLocator()->get('categoryfactory')->createAntennaCategory();
				} else if($post['type'] == 'frequency') {
					$category = $this->getServiceLocator()->get('categoryfactory')->createFrequencyCategory();
				} else if($post['type'] == 'brouillage') {
					$category = $this->getServiceLocator()->get('categoryfactory')->createBrouillageCategory();
				} else {
					$category = new Category();
					$fieldname = new CustomField();
					$fieldname->setCategory($category);
					$fieldname->setName('Nom');
					$fieldname->setType($objectManager->getRepository('Application\Entity\CustomFieldType')->findOneBy(array('type'=>'string')));
					$fieldname->setPlace(1);
                                        $fieldname->setTooltip("");
					$fieldname->setDefaultvalue("");
					$objectManager->persist($fieldname);
					$category->setFieldname($fieldname);
				}
				//force fieldname value
				$fieldname = $category->getFieldname();
			}
    		
			$builder = new AnnotationBuilder();
			$form = $builder->createForm($category);
			$form->setHydrator(new DoctrineObject($objectManager))
			->setObject($category);
			$form->get('parent')->setValueOptions($objectManager->getRepository('Application\Entity\Category')->getRootsAsArray($post['id']));
			$form->bind($category);
			$form->setData($post);
			$form->setPreferFormInputFilter(true);
			
			if($form->isValid()){
				if(!$post['id']){
					//if new cat, force fieldname
					$category->setFieldname($fieldname);
				}
				if(!(strpos($category->getColor(),"#") === 0)){
					$category->setColor("#".$category->getColor());
				}
				$objectManager->persist($category);
				$objectManager->flush();
				$this->flashMessenger()->addSuccessMessage("Catégorie modifiée");
			} else {
				$this->flashMessenger()->addErrorMessage("Impossible de modifier la catégorie.");
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
    		//delete fieldname to avoid loop
    		$category->setFieldname(null);
    		if($category instanceof RadarCategory){
    			$category->setRadarfield(null);
    			$category->setStatefield(null);
    		}
    		if($category instanceof FrequencyCategory){
    			$category->setCurrentAntennafield(null);
    			$category->setStatefield(null);
    			$category->setFrequencyfield(null);
    			$category->setOtherFrequencyfield(null);
    		}
    		if($category instanceof BrouillageCategory){
			$category->setFrequencyField(null);
			$category->setLevelField(null);
			$category->setRnavField(null);
			$category->setDistanceField(null);
			$category->setAzimutField(null);
			$category->setOriginField(null);
			$category->setTypeField(null);
			$category->setCauseBrouillageField(null);
			$category->setCauseInterferenceField(null);
			$category->setCommentaireBrouillageField(null);
			$category->setCommentaireInterferenceField(null);
    		}
    		$objectManager->persist($category);
    		$objectManager->flush();
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
    	
    	$fields = $objectManager->getRepository('Application\Entity\CustomField')->findBy(array('category' => $id), array('place' => 'asc'));
    	    	
    	$viewmodel->setVariables(array('fields' => $fields, 'categoryid' => $id));
    	return $viewmodel;
    }
    
    public function upcategoryAction() {
        $messages = array();
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        if ($id) {
            $cat = $objectManager->getRepository('Application\Entity\Category')->find($id);
            if ($cat) {
                $cat->setPlace($cat->getPlace() - 1);
                $objectManager->persist($cat);
                try {
                    $objectManager->flush();
                    $messages['success'][] = "Catégorie correctement modifiée.";
                } catch (\Exception $e) {
                    $messages['error'][] = $e->getMessage();
                }
            } else {
                $messages['error'][] = "Impossible de trouver la catégorie";
            }
        }
        return new JsonModel($messages);
    }
    
    public function downcategoryAction() {
        $messages = array();
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        if ($id) {
            $cat = $objectManager->getRepository('Application\Entity\Category')->find($id);
            if ($cat) {
                $cat->setPlace($cat->getPlace() + 1);
                $objectManager->persist($cat);
                try {
                    $objectManager->flush();
                    $messages['success'][] = "Catégorie correctement modifiée.";
                } catch (\Exception $e) {
                    $messages['error'][] = $e->getMessage();
                }
            } else {
                $messages['error'][] = "Impossible de trouver la catégorie";
            }
        }
        return new JsonModel($messages);
    }

    public function defaultindexAction(){
        
        $this->layout()->title = "Personnalisation > Catégories par défaut";
        
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $freqcategories = $objectManager->getRepository('Application\Entity\FrequencyCategory')->findAll();
        
        $radarcategories = $objectManager->getRepository('Application\Entity\RadarCategory')->findAll();

        $antennacategories = $objectManager->getRepository('Application\Entity\AntennaCategory')->findAll();
        
        $brouillagecategories = $objectManager->getRepository('Application\Entity\BrouillageCategory')->findAll();
  
        return array('freqcategories' => $freqcategories, 
                    'radarcategories' => $radarcategories, 
                    'antennacategories' => $antennacategories,
                    'brouillagecategories' => $brouillagecategories);
    }
    
    public function changedefaultfrequencyAction(){
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $messages = array();
        if($id){
            $freq = $objectManager->getRepository('Application\Entity\FrequencyCategory')->find($id);
            if($freq){
                foreach ($objectManager->getRepository('Application\Entity\FrequencyCategory')->findAll() as $freqcat){
                    $freqcat->setDefaultFrequencyCategory(($freqcat->getId() == $freq->getId()));
                    $objectManager->persist($freqcat);
                }
                try{
                    $objectManager->flush();
                    $messages['success'][] = "Catégorie fréquence par défaut modifiée";
                } catch (\Exception $ex) {
                    $messages['error'][] = $ex->getMessage();
                }
            }
        }
        return new JsonModel($messages);
    }
    
    public function changedefaultradarAction(){
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $messages = array();
        if($id){
            $radar = $objectManager->getRepository('Application\Entity\RadarCategory')->find($id);
            if($radar){
                foreach ($objectManager->getRepository('Application\Entity\RadarCategory')->findAll() as $radarcat){
                    $radarcat->setDefaultRadarCategory(($radarcat->getId() == $radar->getId()));
                    $objectManager->persist($radarcat);
                }
                try{
                    $objectManager->flush();
                    $messages['success'][] = "Catégorie radar par défaut modifiée";
                } catch (\Exception $ex) {
                    $messages['error'][] = $ex->getMessage();
                }
            }
        }
        return new JsonModel($messages);
    }
    
    public function changedefaultantennaAction(){
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $messages = array();
        if($id){
            $antenna = $objectManager->getRepository('Application\Entity\AntennaCategory')->find($id);
            if($antenna){
                foreach ($objectManager->getRepository('Application\Entity\AntennaCategory')->findAll() as $antennacat){
                    $antennacat->setDefaultAntennaCategory(($antennacat->getId() == $antenna->getId()));
                    $objectManager->persist($antennacat);
                }
                try{
                    $objectManager->flush();
                    $messages['success'][] = "Catégorie antenne par défaut modifiée";
                } catch (\Exception $ex) {
                    $messages['error'][] = $ex->getMessage();
                }
            }
        }
        return new JsonModel($messages);
    }
    
    public function changedefaultbrouillageAction(){
        $id = $this->params()->fromQuery('id', null);
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $messages = array();
        if($id){
            $brouillage = $objectManager->getRepository('Application\Entity\BrouillageCategory')->find($id);
            if($brouillage){
                foreach ($objectManager->getRepository('Application\Entity\BrouillageCategory')->findAll() as $brouillagecat){
                    $brouillagecat->setDefaultBrouillageCategory(($brouillagecat->getId() == $brouillage->getId()));
                    $objectManager->persist($brouillagecat);
                }
                try{
                    $objectManager->flush();
                    $messages['success'][] = "Catégorie brouillage par défaut modifiée";
                } catch (\Exception $ex) {
                    $messages['error'][] = $ex->getMessage();
                }
            }
        }
        return new JsonModel($messages);
    }
}
