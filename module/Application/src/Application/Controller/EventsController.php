<?php
/**
 * Epeires 2
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class EventsController extends AbstractActionController
{
    public function indexAction()
    {
   	
        return array('form' => new \Application\Form\EventForm());
    }
    
    public function createAction(){
    	if($this->getRequest()->isPost()){
    		//save new event
    	} 
    	
    	return $this->redirect()->toRoute('application');
    	
    }
}
