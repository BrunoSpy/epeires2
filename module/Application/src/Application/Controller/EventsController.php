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

class EventsController extends AbstractActionController implements LoggerAware
{
	
    public function indexAction()
    {
    	$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        return array('form' => new EventForm($em->getRepository('Application\Entity\Status')->getAllAsArray()));
    }
    
    public function createAction(){
    	if($this->getRequest()->isPost()){
    		$event = new Event();
    		$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    		$form = new EventForm($objectManager->getRepository('Application\Entity\Status')->getAllAsArray());
    		$form->setInputFilter($event->getInputFilter());
    		$form->setData($this->getRequest()->getPost());
    		if($form->isValid()){
    			//save new event
    			$event->populate($form->getData());	
    			$event->setStatus($objectManager->find('Application\Entity\Status', $form->getData()['status']));
    			$objectManager->persist($event);
    			$objectManager->flush();
    			$this->logger->log(\Zend\Log\Logger::INFO, "event saved");
    		} else {
    			//warn user
    			$this->logger->log(\Zend\Log\Logger::ALERT, "Formulaire non valide");
    		}
    	} 
    	
    	return $this->redirect()->toRoute('application');
    	
    }
    
    //Logger
    private $logger;
    
    public function setLogger(\Zend\Log\Logger $logger){
    	$this->logger = $logger;
    }
    
    public function getLogger(){
    	return $logger;
    }
}
