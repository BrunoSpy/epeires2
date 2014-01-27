<?php
/**
 * Epeires 2
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Administration\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Core\Entity\User;
use Zend\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Crypt\Password\Bcrypt;
use Doctrine\Common\Collections\Criteria;
use Application\Entity\Radar;

class RadarsController extends AbstractActionController
{
	
	
    public function indexAction()
    {
    	$this->layout()->title = "Centres > Radars";
    	 
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	$radars = $objectManager->getRepository('Application\Entity\Radar')->findAll();
    	    	
        return array('radars'=>$radars);
    }
    

    public function saveAction(){
    	
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		$id = $post['id'];
    		$datas = $this->getForm($id);
    		$form = $datas['form'];
    		$form->setData($post);
    		$radar = $datas['radar'];
    		
    		if($form->isValid()){
    			
    			$objectManager->persist($radar);
    			$objectManager->flush();
    		}
    	}
    	return new JsonModel();
    }
    
    public function deleteAction(){
    	$id = $this->params()->fromQuery('id', null);
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$radar = $objectManager->getRepository('Application\Entity\Radar')->find($id);
    	if($radar){
    		$objectManager->remove($radar);
    		$objectManager->flush();
    	}
    	return new JsonModel();
    }
    
    public function formAction(){
    	$request = $this->getRequest();
    	$viewmodel = new ViewModel();
    	//disable layout if request by Ajax
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    	
    	$id = $this->params()->fromQuery('id', null);
    	
    	$getform = $this->getForm($id);
    	 
    	$viewmodel->setVariables(array('form' => $getform['form'],'id'=>$id));
    	return $viewmodel;
    }
    
    private function getForm($id = null){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$radar = new Radar();
    	$builder = new AnnotationBuilder();
    	$form = $builder->createForm($radar);
    	$form->setHydrator(new DoctrineObject($objectManager))
    	->setObject($radar);
     	 
    	$form->get('organisation')->setValueOptions($objectManager->getRepository('Application\Entity\Organisation')->getAllAsArray());
    	
    	if($id){
    		$radar = $objectManager->getRepository('Application\Entity\Radar')->find(id);
    		if($radar){
     			$form->bind($user);
    			$form->setData($user->getArrayCopy());
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
    	 
    	return array('form' => $form, 'radar'=>$radar);
    }
}
