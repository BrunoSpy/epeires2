<?php
/**
 * Epeires 2
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace IPO\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction(){
    	$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	$reports = $em->getRepository('IPO\Entity\Report')->findBy(array(), array('created_on' => 'ASC'), 10, 0);
    	    	
    	return array('reports' => $reports);
    }
    
    public function getEventsAction(){
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        
        $events = array();
        

        return new JsonModel($events);
    }
    
    private function getEventJson($event){
        $eventservice = $this->getServiceLocator()->get('EventService');
    	$customfieldservice = $this->getServiceLocator()->get('CustomFieldService');
        
        $e = array();
        $e['id'] = $event->getId();
        $e['name'] = $eventservice->getName($event);
        $e['category'] = $event->getCategory()->getName();
        $e['isroot'] = $event->getParent() ? false : true;
        $e['status'] = $event->getStatus() ? $event->getStatus()->getName() : '';
        $e['start_date'] = ($event->getStartdate() ? $event->getStartdate()->format(DATE_RFC2822) : null);
    	$e['end_date'] = ($event->getEnddate() ? $event->getEnddate()->format(DATE_RFC2822) : null);
        if($event->isPunctual()) {
            $e['duration'] = 'Ponctuel';
        } else if($event->getEnddate()){
            $diff = \Core\DateTime\MyDateInterval::createFromDateInterval($event->getEnddate()->diff($event->getStartdate()));
            $e['duration'] = $diff->formatWithoutZeroes('%y année(s)', '%m mois', '%d jour(s)', '%h heure(s)', '%i minute(s)', '%s seconde(s)');
        } else {
            $e['duration'] = 'Non terminé';
        }
        
        return $e;
    }
}
