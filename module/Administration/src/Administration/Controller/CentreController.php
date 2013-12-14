<?php
/**
 * Epeires 2
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Administration\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Application\Entity\Organisation;
use Application\Entity\QualificationZone;
use Application\Entity\SectorGroup;
use Application\Entity\Sector;
use Application\Controller\FormController;

class CentreController extends FormController
{
    public function indexAction()
    {
   	
        $viewmodel = new ViewModel();
    	$this->layout()->title = "Centre > Général";
    	
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	 
    	$qualifzones = $objectManager->getRepository('Application\Entity\QualificationZone')->findAll();
    	
    	$sectors = $objectManager->getRepository('Application\Entity\Sector')->findAll();
    	
    	$centres = $objectManager->getRepository('Application\Entity\Organisation')->findAll();
    	
    	$groups = $objectManager->getRepository('Application\Entity\SectorGroup')->findAll();
    	    	
    	$viewmodel->setVariables(array('qualifzones'=> $qualifzones, 'sectors'=>$sectors, 'centres' => $centres, 'groups'=>$groups));
    	
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
    
    /* **************************** */
    /*         Organisations        */
    /* **************************** */
    public function formorganisationAction(){
    	$request = $this->getRequest();
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$viewmodel = new ViewModel();
    	//disable layout if request by Ajax
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    	
    	$id = $this->params()->fromQuery('id', null);
    	
    	$getform = $this->getFormOrganisation($id);
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
    
    public function saveorganisationAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		$id = $post['id'];
    		    	
    		$datas = $this->getFormOrganisation($id);
    		$form = $datas['form'];
    		$form->setData($post);
    		
    		$organisation = $datas['organisation'];
    		
    		if($form->isValid()){
    			$objectManager->persist($organisation);	
    			$objectManager->flush();
    			$this->flashMessenger()->addSuccessMessage("Organisation enregistrée.");
    		} else {
    			$this->processFormMessages($form->getMessages());
    			$this->flashMessenger()->addErrorMessage("Impossible d\'enregistrer l'organisation.");
    		}
    	}
    	 
    	$json = array('id' => $organisation->getId(), 'name' => $organisation->getName());
    	 
    	return new JsonModel($json);
    }
    
    public function deleteorganisationAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$id = $this->params()->fromQuery('id', null);
    	if($id){
    		$org = $objectManager->getRepository('Application\Entity\Organisation')->find($id);
    		if($org){
    			$objectManager->remove($org);
    			$objectManager->flush();
    		}
    	}
    	return new JsonModel();
    }
    
    private function getFormOrganisation($id){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$organisation = new Organisation();
    	$builder = new AnnotationBuilder();
    	$form = $builder->createForm($organisation);
    	$form->setHydrator(new DoctrineObject($objectManager, 'Application\Entity\Organisation'))
    		->setObject($organisation);
    	
    	if($id){
    		$organisation = $objectManager->getRepository('Application\Entity\Organisation')->find($id);
    		if($organisation){
    			$form->bind($organisation);
    			$form->setData($organisation->getArrayCopy());
    		}
    	}
    	return array('form'=>$form, 'organisation'=>$organisation);
    }
    
    /* **************************** */
    /*            Qualif            */
    /* **************************** */
    public function formqualifAction(){
    	$request = $this->getRequest();
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$viewmodel = new ViewModel();
    	//disable layout if request by Ajax
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    	 
    	$id = $this->params()->fromQuery('id', null);
    	 
    	$getform = $this->getFormQualif($id);
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
    
    public function savequalifAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		$id = $post['id'];
    			
    		$datas = $this->getFormQualif($id);
    		$form = $datas['form'];
    		$form->setData($post);
    
    		$qualif = $datas['qualif'];
    
    		if($form->isValid()){
    			$qualif->setOrganisation($objectManager->getRepository('Application\Entity\Organisation')->find($post['organisation']));
    			
    			$objectManager->persist($qualif);
    			$objectManager->flush();
    			$this->flashMessenger()->addSuccessMessage("Zone de qualification enregistrée.");
    		} else {
    			$this->processFormMessages($form->getMessages());
    			$this->flashMessenger()->addErrorMessage("Impossible d\'enregistrer la zone de qualification.");
    		}
    	}
    
    	$json = array('id' => $qualif->getId(), 'name' => $qualif->getName());
    
    	return new JsonModel($json);
    }
    
    public function deletequalifAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$id = $this->params()->fromQuery('id', null);
    	if($id){
    		$qualif = $objectManager->getRepository('Application\Entity\QualificationZone')->find($id);
    		if($qualif){
    			$objectManager->remove($qualif);
    			$objectManager->flush();
    		}
    	}
    	return new JsonModel();
    }
    
    private function getFormQualif($id){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$qualif = new QualificationZone();
    	$builder = new AnnotationBuilder();
    	$form = $builder->createForm($qualif);
    	$form->setHydrator(new DoctrineObject($objectManager, 'Application\Entity\QualificationZone'))
    	->setObject($qualif);
    	 
    	$form->get('organisation')->setValueOptions($objectManager->getRepository('Application\Entity\Organisation')->getAllAsArray());
    	
    	if($id){
    		$qualif = $objectManager->getRepository('Application\Entity\QualificationZone')->find($id);
    		if($qualif){
    			$form->bind($qualif);
    			$form->setData($qualif->getArrayCopy());
    		}
    	}
    	return array('form'=>$form, 'qualif'=>$qualif);
    }
    
    /* **************************** */
    /*       Groupes de secteurs     */
    /* **************************** */
    
    public function getgroupsAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$request = $this->getRequest();
    	$zone = $this->params()->fromQuery('zone', null);
    	$groups = array();
    	if($zone){
    		foreach ($objectManager->getRepository('Application\Entity\SectorGroup')->findBy(array('zone'=>$zone)) as $group){
    			$groups[$group->getId()] = $group->getName();
    		}
    	}
    	return new JsonModel($groups);
    }
    
    public function formgroupAction(){
    	$request = $this->getRequest();
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$viewmodel = new ViewModel();
    	//disable layout if request by Ajax
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    
    	$id = $this->params()->fromQuery('id', null);
    
    	$getform = $this->getFormGroup($id);
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
    
    public function savegroupAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		$id = $post['id'];
    		 
    		$datas = $this->getFormGroup($id);
    		$form = $datas['form'];
    		$form->setData($post);
    		    
    		$group = $datas['group'];
    
    		if($form->isValid()){   			
    			$group->setZone($objectManager->getRepository('Application\Entity\QualificationZone')->find($post['zone']));
    			 
    			$objectManager->persist($group);
    			$objectManager->flush();
    		}
    	}
    
    	$json = array('id' => $group->getId(), 'name' => $group->getName());
    
    	return new JsonModel($json);
    }
    
    public function deletegroupAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$id = $this->params()->fromQuery('id', null);
    	if($id){
    		$group = $objectManager->getRepository('Application\Entity\SectorGroup')->find($id);
    		if($group){
    			$objectManager->remove($group);
    			$objectManager->flush();
    		}
    	}
    	return new JsonModel();
    }
    
    private function getFormGroup($id){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$group = new SectorGroup();
    	$builder = new AnnotationBuilder();
    	$form = $builder->createForm($group);
    	$form->setHydrator(new DoctrineObject($objectManager, 'Application\Entity\SectorGroup'))
    	->setObject($group);
    
    	$form->get('zone')->setValueOptions($objectManager->getRepository('Application\Entity\QualificationZone')->getAllAsArray());
    	 
    	if($id){
    		$group = $objectManager->getRepository('Application\Entity\SectorGroup')->find($id);
    		if($group){
    			$sectors = array();
    			foreach ($objectManager->getRepository('Application\Entity\Sector')->findBy(array('zone'=>$group->getZone()->getId())) as $sector){
    				$sectors[$sector->getId()] = $sector->getName();
    			}
       			$form->get('sectors')->setValueOptions($sectors);
    			$form->bind($group);
    			$form->setData($group->getArrayCopy());
    		}
    	}
    	return array('form'=>$form, 'group'=>$group);
    }
    
    /* **************************** */
    /*            Secteurs          */
    /* **************************** */
    
    public function getsectorsAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$request = $this->getRequest();
    	$zone = $this->params()->fromQuery('zone', null);
    	$sectors = array();
    	if($zone){
    		foreach ($objectManager->getRepository('Application\Entity\Sector')->findBy(array('zone'=>$zone)) as $sector){
    			$sectors[$sector->getId()] = $sector->getName();
    		} 
    	}
    	return new JsonModel($sectors);
    }
    
    public function formsectorAction(){
    	$request = $this->getRequest();
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$viewmodel = new ViewModel();
    	//disable layout if request by Ajax
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    
    	$id = $this->params()->fromQuery('id', null);
    
    	$getform = $this->getFormSector($id);
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
    
    public function savesectorAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		$id = $post['id'];
    		 
    		$datas = $this->getFormSector($id);
    		$form = $datas['form'];
    		$form->setData($post);
    
    		$sector = $datas['sector'];
    
    		if($form->isValid()){
    			$sector->setZone($objectManager->getRepository('Application\Entity\QualificationZone')->find($post['zone']));
    
    			$objectManager->persist($sector);
    			$objectManager->flush();
    		}
    	}
    
    	$json = array('id' => $sector->getId(), 'name' => $sector->getName());
    
    	return new JsonModel($json);
    }
    
    public function deletesectorAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$id = $this->params()->fromQuery('id', null);
    	if($id){
    		$sector = $objectManager->getRepository('Application\Entity\Sector')->find($id);
    		if($sector){
    			//TODO use cascading instead
    			if($sector->getFrequency()){
    				$sector->getFrequency()->setDefaultsector(null);
    			}
    			$objectManager->remove($sector);
    			$objectManager->flush();
    		}
    	}
    	return new JsonModel();
    }
    
    private function getFormSector($id){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$sector = new Sector();
    	$builder = new AnnotationBuilder();
    	$form = $builder->createForm($sector);
    	$form->setHydrator(new DoctrineObject($objectManager, 'Application\Entity\Sector'))
    	->setObject($sector);
    
    	$form->get('zone')->setValueOptions($objectManager->getRepository('Application\Entity\QualificationZone')->getAllAsArray());
        
    	if($id){
    		$sector = $objectManager->getRepository('Application\Entity\Sector')->find($id);
    		if($sector){
    			$groups = array();
    			foreach ($objectManager->getRepository('Application\Entity\SectorGroup')->findBy(array('zone'=>$sector->getZone()->getId())) as $group){
    				$groups[$group->getId()] = $group->getName();
    			}
    			$form->get('sectorsgroups')->setValueOptions($groups);
    			
    			$form->bind($sector);
    			$form->setData($sector->getArrayCopy());
    		}
    	}
    	return array('form'=>$form, 'sector'=>$sector);
    }
}
