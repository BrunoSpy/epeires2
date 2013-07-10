<?php
/**
 * Epeires 2
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\Event;
use Application\Form\EventForm;

class EventsController extends AbstractActionController
{
    public function indexAction()
    {
   	
        return array('form' => new EventForm());
    }
    
    public function createAction(){

    	
    	if($this->getRequest()->isPost()){
    		$event = new Event();
    		
    		$form = new EventForm();
    		$form->setInputFilter($event->getInputFilter());
    		$form->setData($this->getRequest()->getPost());
    		if($form->isValid()){
    			//save new event
				$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    			$event->populate($form->getData());

    			$objectManager->persist($event);
    			$objectManager->flush();
    		} else {
    			//warn user
    		}
    	} 
    	
    	return $this->redirect()->toRoute('application');
    	
    }
}
