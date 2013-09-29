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

class RadioController extends AbstractActionController
{
    public function indexAction()
    {
    	$viewmodel = new ViewModel();
    	$this->layout()->title = "Paramètres";
    	 
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	$viewmodel->setVariables(array('antennas' => $objectManager->getRepository('Application\Entity\Antenna')->findAll(),
    									'frequencies' => $objectManager->getRepository('Application\Entity\Frequency')->findAll(),
    							));
    	
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
    
    
    	$form = $this->getFormAntenna($id)['form'];
    
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
    	$form->setHydrator(new DoctrineObject($objectManager, 'Application\Entity\Antenna'))
    	->setObject($antenna);
    
    	$form->get('organisation')->setValueOptions($objectManager->getRepository('Application\Entity\Organisation')->getAllAsArray());
    
    	if($id){
    		$antenna = $objectManager->getRepository('Application\Entity\Antenna')->find($id);
    		if($antenna){
    		//	$groups = array();
    		//	foreach ($objectManager->getRepository('Application\Entity\SectorGroup')->findBy(array('zone'=>$sector->getZone()->getId())) as $group){
    		//		$groups[$group->getId()] = $group->getName();
    		//	}
    		//	$form->get('sectorsgroups')->setValueOptions($groups);
    			 
    			$form->bind($antenna);
    			$form->setData($antenna->getArrayCopy());
    		}
    	}
    	return array('form'=>$form, 'antenna'=>$antenna);
    }
}
