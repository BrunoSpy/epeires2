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
use Application\Entity\Antenna;
use Application\Entity\Frequency;
use Doctrine\Common\Collections\Criteria;

class RadioController extends AbstractActionController
{
    public function indexAction()
    {
    	$viewmodel = new ViewModel();
    	$this->layout()->title = "Centres > Radio";
    	
    	$return = array();
    	if($this->flashMessenger()->hasErrorMessages()){
    		$return['error'] =  $this->flashMessenger()->getErrorMessages();
    	}
    	
    	if($this->flashMessenger()->hasSuccessMessages()){
    		$return['success'] =  $this->flashMessenger()->getSuccessMessages();
    	}
    	$this->flashMessenger()->clearMessages();
    	
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	$viewmodel->setVariables(array('antennas' => $objectManager->getRepository('Application\Entity\Antenna')->findAll(),
    									'frequencies' => $objectManager->getRepository('Application\Entity\Frequency')->findAll(),
    									'messages' => $return));
    	
        return $viewmodel;
    }
    
    /* **************************** */
    /*            Antennes          */
    /* **************************** */
     
    public function formantennaAction(){
    	$request = $this->getRequest();
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$viewmodel = new ViewModel();
    	//disable layout if request by Ajax
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    
    	$id = $this->params()->fromQuery('id', null);
    
    	$getform = $this->getFormAntenna($id);
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
    
    public function saveantennaAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		$id = $post['id'];
    		 
    		$datas = $this->getFormAntenna($id);
    		$form = $datas['form'];
    		$form->setData($post);
    
    		$antenna = $datas['antenna'];
    
    		if($form->isValid()){
    			$antenna->setOrganisation($objectManager->getRepository('Application\Entity\Organisation')->find($post['organisation']));
    
    			$objectManager->persist($antenna);
    			$objectManager->flush();
    		}
    	}
    
    	$json = array('id' => $antenna->getId(), 'name' => $antenna->getName());
    
    	return new JsonModel($json);
    }
    
    public function deleteantennaAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$id = $this->params()->fromQuery('id', null);
    	if($id){
    		$antenna = $objectManager->getRepository('Application\Entity\Antenna')->find($id);
    		if($antenna){
    			$objectManager->remove($antenna);
    			$objectManager->flush();
    		}
    	}
    	return new JsonModel();
    }
    
    private function getFormAntenna($id){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$antenna = new Antenna();
    	$builder = new AnnotationBuilder();
    	$form = $builder->createForm($antenna);
    	$form->setHydrator(new DoctrineObject($objectManager))
    	->setObject($antenna);
    
    	$form->get('organisation')->setValueOptions($objectManager->getRepository('Application\Entity\Organisation')->getAllAsArray());
    
    	if($id){
    		$antenna = $objectManager->getRepository('Application\Entity\Antenna')->find($id);
    		if($antenna){
    			 
    			$form->bind($antenna);
    			$form->setData($antenna->getArrayCopy());
    		}
    	}
    	return array('form'=>$form, 'antenna'=>$antenna);
    }

    /* **************************** */
    /*           Fréquences         */
    /* **************************** */
     
    public function formfrequencyAction(){
    	$request = $this->getRequest();
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$viewmodel = new ViewModel();
    	//disable layout if request by Ajax
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    
    	$id = $this->params()->fromQuery('id', null);
    
    	$getform = $this->getFormFrequency($id);
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
    
    public function savefrequencyAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		$id = $post['id'];
    		 
    		$datas = $this->getFormFrequency($id);
    		$form = $datas['form'];
    		$form->setData($post);
    
    		$frequency = $datas['frequency'];
    
    		if($form->isValid()){
    			//$antenna->setOrganisation($objectManager->getRepository('Application\Entity\Organisation')->find($post['organisation']));
    
    			$objectManager->persist($frequency);
    			$objectManager->flush();
    		}
    	}
    
    	$json = array('id' => $frequency->getId(), 'name' => $frequency->getValue());
    
    	return new JsonModel($json);
    }
    
    public function deletefrequencyAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$id = $this->params()->fromQuery('id', null);
    	$messages = array();
    	if($id){
    		$frequency = $objectManager->getRepository('Application\Entity\Frequency')->find($id);
    		if($frequency){
    			$objectManager->remove($frequency);
    			try {
    				$objectManager->flush();
    				$messages['success'][] = "Fréquence ".$frequency->getValue()." correctement supprimée";
    			} catch (\Exception $e){
    				$messages['error'][] = "Impossible de supprimer la fréquence";
    				$messages['error'][] = $e->getMessage();
    			}
    		} else {
    			$messages['error'][] = "Impossible de supprimer la fréquence";
    		}
    	}
    	return new JsonModel($messages);
    }
    
    private function getFormFrequency($id){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$frequency = new Frequency();
    	$builder = new AnnotationBuilder();
    	$form = $builder->createForm($frequency);
    	$form->setHydrator(new DoctrineObject($objectManager))
    	->setObject($frequency);
    
    	$form->get('mainantenna')->setValueOptions($objectManager->getRepository('Application\Entity\Antenna')->getAllAsArray());
    	$form->get('backupantenna')->setValueOptions($objectManager->getRepository('Application\Entity\Antenna')->getAllAsArray());   	
    	$form->get('mainantennaclimax')->setValueOptions($objectManager->getRepository('Application\Entity\Antenna')->getAllAsArray());
    	$form->get('backupantennaclimax')->setValueOptions($objectManager->getRepository('Application\Entity\Antenna')->getAllAsArray());
    	$form->get('organisation')->setValueOptions($objectManager->getRepository('Application\Entity\Organisation')->getAllAsArray());
    	
    	$unsetsectors = $objectManager->getRepository('Application\Entity\Sector')->getUnsetSectorsAsArray();
    	$form->get('defaultsector')->setValueOptions($unsetsectors);
    	
    	if($id){
    		$frequency = $objectManager->getRepository('Application\Entity\Frequency')->find($id);
    		if($frequency){
    
    			if($frequency->getDefaultsector()) {
    				$options = $unsetsectors;
    				$options[$frequency->getDefaultsector()->getId()] = $frequency->getDefaultsector()->getName();
    				$form->get('defaultsector')->setValueOptions($options);
    			}
    			
    			$form->bind($frequency);
    			$form->setData($frequency->getArrayCopy());
    		}
    	}
    	return array('form'=>$form, 'frequency'=>$frequency);
    }

    /* **************************** */
    /*        Page Fréquences       */
    /* **************************** */
    
    public function configAction(){
    	$viewmodel = new ViewModel();
    	$this->layout()->title = "Personnalisation > Page Fréquence";
    	
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	 
    	$viewmodel->setVariables(array('sectorsgroups' => $objectManager->getRepository('Application\Entity\SectorGroup')->findBy(array(), array('position' => 'ASC'))));
    	 
    	return $viewmodel;
    }
    
    public function groupdownAction(){
    	$messages = array();
    	$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$id = $this->params()->fromQuery('id', null);
    	if($id) {
    		$sectorgroup = $em->getRepository('Application\Entity\SectorGroup')->find($id);
    		if($sectorgroup){
    			$sectorgroup->setPosition($sectorgroup->getPosition() + 1);
    			$em->persist($sectorgroup);
    			try {
    				$em->flush();
    				$messages['success'][] = "Groupe correctement modifié.";
    			} catch (\Exception $e) {
    				$messages['error'][] = "Impossible d'enregistrer la modification";
    				$messages['error'][] = $e->getMessage();
    			}
    		} else {
    			$messages['error'][] = "Impossible de trouver l'élément.";
    		}
    	}
    	return new JsonModel($messages);
    }
    
    public function groupupAction(){
    	$messages = array();
    	$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$id = $this->params()->fromQuery('id', null);
    	if($id) {
    		$sectorgroup = $em->getRepository('Application\Entity\SectorGroup')->find($id);
    		if($sectorgroup){
    			$sectorgroup->setPosition($sectorgroup->getPosition() - 1);
    			$em->persist($sectorgroup);
    			try {
    				$em->flush();
    				$messages['success'][] = "Groupe correctement modifié.";
    			} catch (\Exception $e) {
    				$messages['error'][] = "Impossible d'enregistrer la modification";
    				$messages['error'][] = $e->getMessage();
    			}
    		} else {
    			$messages['error'][] = "Impossible de trouver l'élément.";
    		}
    	}
    	return new JsonModel($messages);
    }
    
    public function switchdisplayAction(){
    	$messages = array();
    	$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$id = $this->params()->fromQuery('id', null);
    	if($id){
    		$sectorgroup = $em->getRepository('Application\Entity\SectorGroup')->find($id);
    		if($sectorgroup){
    			$sectorgroup->setDisplay(!$sectorgroup->isDisplay());
    			$em->persist($sectorgroup);
    			try {
    				$em->flush();
    				$messages['success'][] = "Groupe correctement modifié.";
    			} catch (\Exception $e) {
    				$messages['error'][] = $e->getMessage();
    			}
    		} else {
    			$messages['error'][] = "Impossible de trouver l'élément.";
    		}
    	}
    	return new JsonModel($messages);
    }
    
}
