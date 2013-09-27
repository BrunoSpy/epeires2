<?php
/**
 * Epeires 2
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Administration\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class CentreController extends AbstractActionController
{
    public function indexAction()
    {
   	
        $viewmodel = new ViewModel();
    	$this->layout()->title = "Centre > Général";
    	
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	 
    	$qualifzones = $objectManager->getRepository('Application\Entity\QualificationZone')->findAll();
    	
    	$sectors = $objectManager->getRepository('Application\Entity\Sector')->findAll();
    	
    	$centres = $objectManager->getRepository('Application\Entity\Organisation')->findAll();
    	
    	$viewmodel->setVariables(array('qualifzones'=> $qualifzones, 'sectors'=>$sectors, 'centres' => $centres));
    	
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
    
    
}
