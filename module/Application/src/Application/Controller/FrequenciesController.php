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
use Application\Entity\Frequency;
use Application\Entity\FrequencyCategory;
use Zend\Validator\File\Count;
use Doctrine\Common\Collections\Criteria;

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
		 				
		$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		$groups = $em->getRepository('Application\Entity\SectorGroup')->findBy(array('display' => true), array('position' => 'ASC'));
		
		$criteria = Criteria::create();
		$criteria->andWhere(Criteria::expr()->isNull('defaultsector'));
		$otherfrequencies = $em->getRepository('Application\Entity\Frequency')->matching($criteria);
		
		$viewmodel->setVariables(array('antennas' => $this->getAntennas(), 
										'messages' => $return,
										'groups' => $groups,
										'other' => $otherfrequencies));
		
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
				$events = $em->getRepository('Application\Entity\Antenna')->getCurrentEvents('Application\Entity\AntennaCategory');
				//on récupère les évènements de l'antenne
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
						//ferme evts fils de type frequencycategory
						foreach ($event->getChildren() as $child){
							if($child->getCategory() instanceof FrequencyCategory){
								$child->setEnddate($now);
								$child->setStatus($endstatus);
								$em->persist($child);
							}
						}
						$event->setEnddate($now);
						$em->persist($event);
						try {
							$em->flush();
							$messages['success'][] = "Evènement antenne correctement terminé.";
						} catch (\Exception $e) {
							$messages['error'][] = $e->getMessage();
						}
					} else {
						$messages['error'][] = "Impossible de déterminer l'évènement à terminer";
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
							//création des evts fils pour le passage en secours
							foreach ($antenna->getMainfrequencies() as $frequency){
								$this->switchCoverture($messages, $frequency, 1, $now, $event);
							}
							foreach ($antenna->getMainfrequenciesclimax() as $frequency){
								$this->switchCoverture($messages, $frequency, 1, $now, $event);
							}
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
			$messages['error'][] = 'Droits insuffisants pour modifier l\'état de l\'antenne.';
		}
		return new JsonModel($messages);
	}
	
	public function switchCovertureAction(){
		$messages = array();
		if($this->isGranted('events.write') && $this->zfcUserAuthentication()->hasIdentity()){
			$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
			$cov = $this->params()->fromQuery('cov', null);
			$frequencyid = $this->params()->fromQuery('frequencyid', null);
			
			if($cov != null && $frequencyid){
				$now = new \DateTime('NOW');
				$now->setTimezone(new \DateTimeZone("UTC"));
				$freq = $em->getRepository('Application\Entity\Frequency')->find($frequencyid);
				if($freq){
					$this->switchCoverture($messages, $freq, intval($cov), $now);
				} else {
					$messages['error'][] = "Impossible de trouver la fréquence demandée";
				}
			} else {
				$messages['error'][] = "Paramètres incorrects, impossible de créer l'évènement.";
			}
			
		} else {
			$messages['error'][] = "Droits insuffisants";
		}
		return new JsonModel($messages);
	}
	
	/**
	 * Create and persist a new frequency event
	 * @param unknown $cov 0=principale, 1 = secours
	 * @param Event $parent
	 */
	private function switchCoverture(&$messages, Frequency $frequency, $cov, $startdate, Event $parent = null){
		$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		if($cov == 0) {
			$frequencyevents = array();
			//on cloture l'evt "passage en couv secours"
			foreach ($em->getRepository('Application\Entity\Frequency')->getCurrentEvents('Application\Entity\FrequencyCategory') as $event){
				$frequencyfield = $event->getCategory()->getFrequencyfield()->getId();
				foreach ($event->getCustomFieldsValues() as $customvalue){
					if($customvalue->getCustomField()->getId() == $frequencyfield){
						if($customvalue->getValue() == $frequency->getId()){
							$frequencyevents[] = $event;
						}
					}
				}
			}
			if(count($frequencyevents) == 0 || count($frequencyevents) > 1) {
				$messages['error'][] = "Impossible de trouver l'évènement à cloturer";
			} else {
				$event = $frequencyevents[0];
				$endstatus = $em->getRepository('Application\Entity\Status')->find('3');
				$event->setStatus($endstatus);
				$event->setEnddate($startdate);
				$em->persist($event);
				try {
					$em->flush();
				} catch (\Exception $e) {
					$messages['error'][] = $e->getMessage();
				}
			}
		} else {
			//on crée l'evt "passage en couv secours"
			$event = new Event();
			if($parent){
				$event->setParent($parent);
			}
			$status = $em->getRepository('Application\Entity\Status')->find('2');
			$impact = $em->getRepository('Application\Entity\Impact')->find('3');
			$event->setImpact($impact);
			$event->setStatus($status);
			$event->setStartdate($startdate);
			$event->setPunctual(false);
			//fix horrible en attendant de gérer correctement les fréquences sans secteur
			if($frequency->getDefaultsector()) {
				$event->setOrganisation($frequency->getDefaultsector()->getZone()->getOrganisation());
				$event->addZonefilter($frequency->getDefaultsector()->getZone());
			} else {
				$event->setOrganisation($this->zfcUserAuthentication()->getIdentity()->getOrganisation());
			}
			$event->setAuthor($this->zfcUserAuthentication()->getIdentity());
			$categories = $em->getRepository('Application\Entity\FrequencyCategory')->findAll();
			//TODO paramétrer la catégorie au lieu de prendre la première
			if($categories){
				$cat = $categories[0];
				$event->setCategory($cat);
				$frequencyfieldvalue = new CustomFieldValue();
				$frequencyfieldvalue->setCustomField($cat->getFrequencyfield());
				$frequencyfieldvalue->setEvent($event);
				$frequencyfieldvalue->setValue($frequency->getId());
				$event->addCustomFieldValue($frequencyfieldvalue);
				$statusfield = new CustomFieldValue();
				$statusfield->setCustomField($cat->getStatefield());
				$statusfield->setEvent($event);
				$statusfield->setValue(true); //unavailable
				$event->addCustomFieldValue($statusfield);
				$covfield = new CustomFieldValue();
				$covfield->setCustomField($cat->getCurrentAntennafield());
				$covfield->setEvent($event);
				$covfield->setValue($cov);
				$event->addCustomFieldValue($covfield);
				$em->persist($frequencyfieldvalue);
				$em->persist($statusfield);
				$em->persist($covfield);
				$em->persist($event);
				try {
					$em->flush();
					$messages['success'][] = "Changement de couverture de la fréquence ".$frequency->getValue()." enregistré.";
				} catch(\Exception $e){
					$messages['error'][] = $e->getMessage();
				}
			} else {
				$messages['error'][] = "Impossible de passer les couvertures en secours : aucune catégorie trouvée.";
			}
		}
	}
	
	public function getAntennaStateAction(){
		return new JsonModel($this->getAntennas(true));
	}
	
	/**
	 * State of the frequencies
	 * @return \Zend\View\Model\JsonModel
	 */
	public function getFrequenciesStateAction(){
		return new JsonModel($this->getFrequencies(true));
	}
	
	private function getFrequencies($full = true){
		$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		
		$frequencies = array();
		$results = $em->getRepository('Application\Entity\Frequency')->findAll();
		
		//retrieve antennas state once and for all
		$antennas = $this->getAntennas(false);
		
		foreach ($results as $frequency){
			if($full){
				$frequencies[$frequency->getId()] = array();
				$frequencies[$frequency->getId()]['name'] = $frequency->getValue();
				$frequencies[$frequency->getId()]['status'] = true; 
				$frequencies[$frequency->getId()]['cov'] = 0;
				$frequencies[$frequency->getId()]['planned'] = false;
			} else {
				$frequencies[$frequency->getId()] = true;
			}
			
			if($full){
				$frequencies[$frequency->getId()]['status'] *= $antennas[$frequency->getMainAntenna()->getId()] * $antennas[$frequency->getBackupAntenna()->getId()];
			} else {
				$frequencies[$frequency->getId()] *= $antennas[$frequency->getMainAntenna()->getId()] * $antennas[$frequency->getBackupAntenna()->getId()];
			}
		}

		foreach ($em->getRepository('Application\Entity\Frequency')->getCurrentEvents('Application\Entity\FrequencyCategory') as $event){
			$statefield = $event->getCategory()->getStatefield()->getId();
			$frequencyfield = $event->getCategory()->getFrequencyfield()->getId();
			$covfield = $event->getCategory()->getCurrentAntennafield()->getId();
			$frequencyid = 0;
			$available = true;
			$cov = 0;
			foreach ($event->getCustomFieldsValues() as $customvalue){
				if($customvalue->getCustomField()->getId() == $statefield){
					$available = !$customvalue->getValue();
				} else if($customvalue->getCustomField()->getId() == $frequencyfield){
					$frequencyid = $customvalue->getValue();
				} else if($customvalue->getCustomField()->getId() == $covfield){
					$cov = $customvalue->getValue();
				}
			}
			if(array_key_exists($frequencyid, $frequencies)){ //peut être inexistant si la fréquence a été supprimée alors que des évènements existent
				if($full){
					$frequencies[$frequencyid]['status'] *= $available;
					$frequencies[$frequencyid]['cov'] = $cov;
				} else {
					$frequencies[$frequencyid] *= $available;
				}
			}
		}
		
		if($full){ //en format complet, on donne aussi les evènements dans les 12h
			foreach ($em->getRepository('Application\Entity\Frequency')->getPlannedEvents('Application\Entity\FrequencyCategory') as $event){
				$statefield = $event->getCategory()->getStatefield()->getId();
				$frequencyfield = $event->getCategory()->getFrequencyfield()->getId();
				$frequencyid = 0;
				$planned = false;
				foreach ($event->getCustomFieldsValues() as $customvalue){
					if($customvalue->getCustomField()->getId() == $statefield){
						$planned = $customvalue->getValue();
					} else if($customvalue->getCustomField()->getId() == $frequencyfield){
						$frequencyid = $customvalue->getValue();
					}
				}
				if(array_key_exists($frequencyid, $frequencies)){ //peut être inexistant si la fréquence a été supprimée alors que des évènements existent
					$frequencies[$frequencyid]['planned'] = $planned;
				}
			}
		}
		
		return $frequencies;
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
				$antennas[$antenna->getId()]['planned'] = false;
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
		
		if($full){
			foreach ($em->getRepository('Application\Entity\Antenna')->getPlannedEvents('Application\Entity\AntennaCategory') as $result){
				$statefield = $result->getCategory()->getStatefield()->getId();
				$antennafield = $result->getCategory()->getAntennafield()->getId();
				$antennaid = 0;
				$planned = false;
				foreach ($result->getCustomFieldsValues() as $customvalue){
					if($customvalue->getCustomField()->getId() == $statefield){
						$planned = $customvalue->getValue();
					} else if($customvalue->getCustomField()->getId() == $antennafield){
						$antennaid = $customvalue->getValue();
					}
				}
				$antennas[$antennaid]['planned'] = $planned;
			}
		}
		
		return $antennas;
	}
}