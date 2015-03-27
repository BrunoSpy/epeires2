<?php
/*
 *  This file is part of Epeires².
 *  Epeires² is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  Epeires² is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with Epeires².  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Entity\Event;
use Application\Entity\CustomFieldValue;

/**
 * @author Bruno Spyckerelle
 */
class RadarsController extends TabController {

    public function indexAction() {

        parent::indexAction();

        $viewmodel = new ViewModel();

        $return = array();

        if ($this->flashMessenger()->hasErrorMessages()) {
            $return['errorMessages'] = $this->flashMessenger()->getErrorMessages();
        }

        if ($this->flashMessenger()->hasSuccessMessages()) {
            $return['successMessages'] = $this->flashMessenger()->getSuccessMessages();
        }

        $this->flashMessenger()->clearMessages();

        $viewmodel->setVariables(array('messages' => $return));

        $viewmodel->setVariable('radars', $this->getRadars());


        return $viewmodel;
    }

    public function switchradarAction(){
		$messages = array();
		if($this->isGranted('events.write') && $this->zfcUserAuthentication()->hasIdentity()) {
			$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
			$state = $this->params()->fromQuery('state', null);
			$radarid = $this->params()->fromQuery('radarid', null);
			
			$now = new \DateTime('NOW');
			$now->setTimezone(new \DateTimeZone("UTC"));
					
			if($state != null && $radarid){
				$events = $this->getCurrentRadarEvents();			

				$radarevents = array();
				foreach ($events as $event){
					$radarfield = $event->getCategory()->getRadarfield();
					foreach ($event->getCustomFieldsValues() as $value){
						if($value->getCustomField()->getId() == $radarfield->getId()){
							if($value->getValue() == $radarid) {
								$radarevents[] = $event;
							}
						}
					}
				}
			
				if($state == 'true'){
					//passage d'un radar à l'état OPE -> recherche de l'evt à fermer
					if(count($radarevents) == 1) {
						$event = $radarevents[0];
						$endstatus = $em->getRepository('Application\Entity\Status')->find('3');
						$event->setStatus($endstatus);
						$event->setEnddate($now);
						$em->persist($event);
						try {
							$em->flush();
							$messages['success'][] = "Evènement radar correctement terminé.";
						} catch (\Exception $e) {
							$messages['error'][] = $e->getMessage();
						}
					} else {
						$messages['error'][] = "Impossible de déterminer l'évènement à terminer.";
					}
				} else {
					//passage d'un radar à l'état HS -> on vérifie qu'il n'y a pas d'evt en cours
					if(count($radarevents) > 0){
						$messages['error'][] = "Un évènement est déjà en cours pour ce radar, impossible d'en créer un nouveau";
					} else {
						//$messages['error'][] = "Création d'un nouvel évènement non implémenté : utilisez la page Evènements.";
						$event = new Event();
						$status = $em->getRepository('Application\Entity\Status')->find('2');
						$impact = $em->getRepository('Application\Entity\Impact')->find('3');
						$event->setStatus($status);
						$event->setStartdate($now);
						$event->setImpact($impact);
						$event->setPunctual(false);
						$radar = $em->getRepository('Application\Entity\Radar')->find($radarid);
						$event->setOrganisation($radar->getOrganisation());
						$event->setAuthor($this->zfcUserAuthentication()->getIdentity());
						$categories = $em->getRepository('Application\Entity\RadarCategory')->findBy(array('defaultradarcategory' => true));
						if($categories){
							$cat = $categories[0];
							$radarfieldvalue = new CustomFieldValue();
							$radarfieldvalue->setCustomField($cat->getRadarfield());
							$radarfieldvalue->setValue($radarid);
							$radarfieldvalue->setEvent($event);
							$event->addCustomFieldValue($radarfieldvalue);
							$statusvalue = new CustomFieldValue();
							$statusvalue->setCustomField($cat->getStatefield());
							$statusvalue->setValue(true);
							$statusvalue->setEvent($event);
							$event->addCustomFieldValue($statusvalue);
							$event->setCategory($categories[0]);
							$em->persist($radarfieldvalue);
							$em->persist($statusvalue);
							$em->persist($event);
							try {
								$em->flush();
								$messages['success'][] = "Nouvel évènement radar créé.";
							} catch (\Exception $e) {
								$messages['error'][] = $e->getMessage();
							}
						} else {
							$messages['error'][] = "Impossible de créer un nouvel évènement.";
						}
					}
				}
			} else {
				$messages['error'][] = "Requête incorrecte, impossible de trouver le radar correspondant.";
			}
		} else {
			$messages['error'][] = "Droits insuffisants pour modifier l'état du radar";
		}
		return new JsonModel($messages);
	}
	
	public function getRadarStateAction(){
		return new JsonModel($this->getRadars(false));
	}
	
	private function getRadars($full = true){
		$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		
		$radars = array();
		
		foreach ($em->getRepository('Application\Entity\Radar')->findBy(array('decommissionned' => false)) as $radar){
			//avalaible by default
			if($full){
				$radars[$radar->getId()] = array();
				$radars[$radar->getId()]['name'] = $radar->getName();
				$radars[$radar->getId()]['status'] = true;
			} else {
				$radars[$radar->getId()] = true;
			}
		}
					
		$results = $this->getCurrentRadarEvents();
					
		foreach ($results as $result){
			$statefield = $result->getCategory()->getStatefield()->getId();
			$radarfield = $result->getCategory()->getRadarfield()->getId();
			$radarid = 0;
			$available = true;
			foreach ($result->getCustomFieldsValues() as $customvalue){
				if($customvalue->getCustomField()->getId() == $statefield){
					$available = !$customvalue->getValue();
				} else if($customvalue->getCustomField()->getId() == $radarfield){
					$radarid = $customvalue->getValue();
				}
			}
			if(array_key_exists($radarid, $radars)) {
				if($full){
					$radars[$radarid]['status'] *= $available;
				} else {
					$radars[$radarid] *= $available;
				}
			}
		}
				
		return $radars;
		
	}

	private function getCurrentRadarEvents(){
		$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
		$now = new \DateTime('NOW');
		$now->setTimezone(new \DateTimeZone("UTC"));
		//évènements radars en cours
		$qb = $em->createQueryBuilder();
		$qb->select('e', 'cat')
		->from('Application\Entity\Event', 'e')
		->innerJoin('e.category', 'cat')
		->andWhere('cat INSTANCE OF Application\Entity\RadarCategory')
		->andWhere($qb->expr()->lte('e.startdate', '?1'))
		->andWhere($qb->expr()->orX(
				$qb->expr()->isNull('e.enddate'),
				$qb->expr()->gte('e.enddate', '?2')))
				->andWhere($qb->expr()->in('e.status', array(2,3)))
				->setParameters(array(1 => $now->format('Y-m-d H:i:s'),
						2 => $now->format('Y-m-d H:i:s')));
					
		$query = $qb->getQuery();
		return $query->getResult();
	}
	
}