<?php
/**
 * Epeires 2
 *
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;


class MilController extends AbstractActionController {
	
	
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
				
		$nmservice = $this->serviceLocator->get('nmb2b');
                
                //$viewmodel->setVariables(array('rsas' => $nmservice->getEAUPRSA(array('LFCB*'), new \DateTime('2014-10-28'))));
                
                $eaupChain = new \Core\NMB2B\EAUPChain($nmservice->getEAUPChain(new \DateTime('2014-10-28')));
                
                $viewmodel->setVariable('number', $eaupChain->getLastSequenceNumber());

        return $viewmodel;
		
	}
	
	
	
}