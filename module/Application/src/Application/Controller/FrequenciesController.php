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
use Zend\View\Model\JsonModel;
use Application\Entity\Event;
use Application\Entity\CustomFieldValue;

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
		
		$viewmodel->setVariable('antennas', $this->getAntennas());
		
		return $viewmodel;
		
	}
	
	public function switchantennaAction(){
		$messages = array();
		if($this->isGranted('events.write') && $this->zfcUserAuthentication()->hasIdentity()){
			$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
			$state = $this->params()->fromQuery('state', null);
			$antennaid = $this->params()->fromQuery('antennaid', null);
			
			$now = new \DateTime('NOW');
			$now->setTimezone(new \DateTimeZone("UTC"));
			
			if($state != null && $antennaid){
				$events = $this->getCurrentAntennaEvents();
				//on récupère les évènemets de l'antenne
				$antennaEvents = array();
				foreach ($events as $event){
					$antennafield = $event->getCategory()->getAntennafield();
					foreach ($event->getCustomFieldsValues() as $value){
						if($value->getCustomField()->getId() == $antennafield->getId()){
							if($value->getValue() == $antennaid){
								$antennaEvents[] = $event;
							}
						}
					}
				}
				
				if($state == 'true'){
					//recherche de l'evt à fermer
					if(count($antennaEvents) == 1){
						$event = $antennaEvents[0];
						$endstatus = $em->getRepository('Application\Entity\Status')->find('3');
						$event->setStatus($endstatus);
						$event->setEnddate($now);
						$em->persist($event);
						try {
							$em->flush();
							$messages['success'][] = "Evènement antenne correctement terminé.";
						} catch (\Exception $e) {
							$messages['error'][] = $e->getMessage();
						}
					} else {
						$messages['error'] = 'Impossible de déterminer l\'évènement à terminer';
					}
				} else {
					if(count($antennaEvents) > 0){
						$messages['error'][] = "Un évènement est déjà en cours, impossible d'en créer un nouveau.";
					} else {
						$event = new Event();
						$status = $em->getRepository('Application\Entity\Status')->find('2');
						$impact = $em->getRepository('Application\Entity\Impact')->find('3');
						$event->setStatus($status);
						$event->setStartdate($now);
						$event->setImpact($impact);
						$event->setPunctual(false);
						$antenna = $em->getRepository('Application\Entity\Antenna')->find($antennaid);
						$event->setOrganisation($antenna->getOrganisation()); //TODO et si une antenne appartient à plusieurs orga ?
						$event->setAuthor($this->zfcUserAuthentication()->getIdentity());
						$categories = $em->getRepository('Application\Entity\AntennaCategory')->findAll();
						if($categories){
							$cat = $categories[0];
							$antennafieldvalue = new CustomFieldValue();
							$antennafieldvalue->setCustomField($cat->getAntennaField());
							$antennafieldvalue->setValue($antennaid);
							$antennafieldvalue->setEvent($event);
							$event->addCustomFieldValue($antennafieldvalue);
							$statusvalue = new CustomFieldValue();
							$statusvalue->setCustomField($cat->getStatefield());
							$statusvalue->setValue(true);
							$statusvalue->setEvent($event);
							$event->addCustomFieldValue($statusvalue);
							$event->setCategory($categories[0]);
							$em->persist($antennafieldvalue);
							$em->persist($statusvalue);
							$em->persist($event);
							try {
								$em->flush();
								$messages['success'][] = "Nouvel évènement antenne créé.";
							} catch (\Exception $e) {
								$messages['error'][] = $e->getMessage();
							}
						} else {
							$messages['error'][] = "Impossible de créer un nouvel évènement. Contactez l'administrateur.";
						}
					}
				}
				
			} else {
				$messages['error'][] = "Requête incorrecte, impossible de trouver l'antenne correspondante.";
			}
			
		} else {
			$messages['error'] = 'Droits insuffisants pour modifier l\'état de l\'antenne.';
		}
		return new JsonModel($messages);
	}
	
	public function getAntennaStateAction(){
		return new JsonModel($this->getAntennas(false));
	}
	
	private function getAntennas($full = true){
		$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		
		$antennas = array();
		
		foreach ($em->getRepository('Application\Entity\Antenna')->findAll() as $antenna){
			//avalaible by default
			if($full) {
				$antennas[$antenna->getId()] = array();
				$antennas[$antenna->getId()]['name'] = $antenna->getName();
				$antennas[$antenna->getId()]['status'] = true;
			} else {
				$antennas[$antenna->getId()] = true;
			}
		}
			
		foreach ($em->getRepository('Application\Entity\Antenna')->getCurrentEvents('Application\Entity\AntennaCategory') as $result){
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
			if($full){
				$antennas[$antennaid]['status'] *= $available;
			} else {
				$antennas[$antennaid] *= $available;
			}
		}
		
		return $antennas;
	}
}