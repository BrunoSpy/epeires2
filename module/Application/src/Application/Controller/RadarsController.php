<?php
/**
 * Epeires 2
 *
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class RadarsController extends AbstractActionController {
	
	
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
		
		$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		
		$radars = array();
		
		$now = new \DateTime('NOW');
		$now->setTimezone(new \DateTimeZone("UTC"));
		
		foreach ($em->getRepository('Application\Entity\Radar')->findAll() as $radar){
			$qbEvents = $em->createQueryBuilder();
			$qbEvents->select(array('e', 'v', 'c', 't'))
			->from('Application\Entity\Event', 'e')
			->innerJoin('e.custom_fields_values', 'v')
			->innerJoin('v.customfield', 'c')
			->innerJoin('c.type', 't')
			->andWhere($qbEvents->expr()->eq('t.type', '?4'))
			->andWhere($qbEvents->expr()->eq('v.value', '?1'))
			->andWhere($qbEvents->expr()->lte('e.startdate', '?2'))
			->andWhere($qbEvents->expr()->orX(
					$qbEvents->expr()->isNull('e.enddate'),
					$qbEvents->expr()->gte('e.enddate', '?3')))
			->andWhere($qbEvents->expr()->in('e.status', array(2,3)))
			->setParameters(array(1 => $radar->getId(),
								2 => $now->format('Y-m-d H:i:s'),
								3 => $now->format('Y-m-d H:i:s'),
								4 => 'radar'));
			
			$query = $qbEvents->getQuery();
						
			$results = $query->getResult();
			
			$radars[] = array('name' => $radar->getName(), 'status' => count($results));
			
		}
		
		$viewmodel->setVariable('radars', $radars);
		
		return $viewmodel;
		
	}
}