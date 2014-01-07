<?php
/**
 * Epeires 2
 *
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\Query\Expr\Join;

class FrequenciesController extends AbstractActionController {
	
	
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
		
		$antennas = array();
		
		$now = new \DateTime('NOW');
		$now->setTimezone(new \DateTimeZone("UTC"));
				
		foreach ($em->getRepository('Application\Entity\Antenna')->findAll() as $antenna){
			//avalaible by default
			$antennas[$antenna->getId()] = array();
			$antennas[$antenna->getId()]['name'] = $antenna->getName();
			$antennas[$antenna->getId()]['status'] = true;
		}
			
		$qbEvents = $em->createQueryBuilder();
		$qbEvents->select(array('e', 'cat'))
		->from('Application\Entity\Event', 'e')
		->innerJoin('e.category', 'cat')
		->andWhere('cat INSTANCE OF Application\Entity\AntennaCategory')
		->andWhere($qbEvents->expr()->lte('e.startdate', '?1'))
		->andWhere($qbEvents->expr()->orX(
				$qbEvents->expr()->isNull('e.enddate'),
				$qbEvents->expr()->gte('e.enddate', '?2')))
		->andWhere($qbEvents->expr()->in('e.status', array(2,3)))
		->setParameters(array(1 => $now->format('Y-m-d H:i:s'),
							2 => $now->format('Y-m-d H:i:s')));
			
		$query = $qbEvents->getQuery();
			
		$results = $query->getResult();
			
		foreach ($results as $result){
			$statefield = $result->getCategory()->getStatefield()->getId();
			$antennafield = $result->getCategory()->getAntennafield()->getId();
			$antennaid = 0;
			$available = true;
			foreach ($result->getCustomFieldsValues() as $customvalue){
				if($customvalue->getCustomField()->getId() == $statefield){
					$available = !$customvalue->getValue();
				} else if($customvalue->getCustomField()->getId() == $antennafield){
					$antennaid = $customvalue->getValue();
				}
			}
			$antennas[$antennaid]['status'] *= $available;				
		}		
		
		$viewmodel->setVariable('antennas', $antennas);
		
		return $viewmodel;
		
	}
}