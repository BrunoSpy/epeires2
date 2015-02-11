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
namespace Application\Services;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\FactoryInterface;
use Application\Entity\Event;
use ZfcRbac\Service\Rbac;
use Application\Entity\AbstractEvent;

/**
 * @author Bruno Spyckerelle
 */
class EventService implements ServiceManagerAwareInterface{
	/**
	 * Service Manager
	 */
	protected $sm;
	
	/**
	 * Entity Manager
	 */
	private $em;
	
	private $rbac;
	
	public function getRbac(){
		if(!$this->rbac){
			$this->rbac = $this->sm->get('ZfcRbac\Service\AuthorizationService');
		}
		return $this->rbac;
	}
	
	public function setEntityManager(\Doctrine\ORM\EntityManager $em){
		$this->em = $em;
	}
	
	public function setServiceManager(ServiceManager $serviceManager){
		$this->sm = $serviceManager;
	}
	
	/**
	 * An event is modifiable if the current user is the author of the event or if he has the 'events.write' permission
	 * @return boolean
	 */
	public function isModifiable(Event $event){
		$auth = $this->sm->get('zfcuser_auth_service');
		if($auth->hasIdentity()){
			if($this->getRbac()->isGranted('events.write') ||
				($event->getAuthor() && $event->getAuthor()->getId() === $auth->getIdentity()->getId() )){
				return true;
			}
		}
		return false;
	}
	
	
	/**
	 * Get the name of an event depending on the title field of the category.
	 * If no title field is set, returns the event's id
	 * @param $event
	 */
	public function getName(AbstractEvent $event){	
		
		if($event instanceof \Application\Entity\PredefinedEvent){
			if($event->getParent() == null && $event->getName()){
				return $event->getName();
			}
		}
		
		$name = $event->getId();
		
		$category = $event->getCategory();
		
                if($category instanceof \Application\Entity\FrequencyCategory){
                    $freqid = 0;
                    $otherfreqid = 0;
                    foreach($event->getCustomFieldsValues() as $value){
                        if($value->getCustomField()->getId() == $category->getFrequencyField()->getId()){
                            $freqid = $value->getValue();
                        }
                        if($value->getCustomField()->getId() == $category->getOtherFrequencyField()->getId()){
                            $otherfreqid = $value->getValue();
                        }
                    }
                    if($freqid != 0){
                        $freq = $this->em->getRepository('Application\Entity\Frequency')->find($freqid);
                        if($freq){
                            if($otherfreqid != 0){
                                $otherfreq = $this->em->getRepository('Application\Entity\Frequency')->find($otherfreqid);
                                if($otherfreq){
                                    $name = $freq->getName().' → '.$otherfreq->getName().' '.$otherfreq->getValue();
                                }
                            } else {
                                $name = $freq->getName().' '.$freq->getValue();
                            }
                        }
                    }
                } else if($category instanceof \Application\Entity\MilCategory) {
                    $namefield = $event->getCustomFieldValue($category->getFieldname());
                    $name = ($namefield ? $namefield->getValue() : "???"); //TODO $namefield ne peut jamais être vide !!
                    $plancherfield = $event->getCustomFieldValue($category->getLowerLevelField());
                    $plafondfield = $event->getCustomFieldValue($category->getUpperLevelField());
                    $name .= ' ('
                            . ($plancherfield !== null ? str_pad($plancherfield->getValue(), 3, '0', STR_PAD_LEFT) : '--')
                            . '/'
                            . ($plafondfield !== null ? str_pad($plafondfield->getValue(), 3, '0', STR_PAD_LEFT) : '--')
                            . ')';
                } else {
                    $titlefield = $category->getFieldname();
                    if($titlefield){
                            foreach($event->getCustomFieldsValues() as $fieldvalue){
                                    if($fieldvalue->getCustomField()->getId() == $titlefield->getId()){
                                            $tempname = $this->sm->get('CustomFieldService')->getFormattedValue($fieldvalue->getCustomField(), $fieldvalue->getValue());

                                            if($tempname){
                                                    $name = ($category->getParent() != null ? $category->getShortName() : '').' '.$tempname;
                                            }
                                    }
                            }
                    }
                }       
		return $name;
	}
		
	protected function sortbydate($a, $b){
		return \DateTime::createFromFormat(DATE_RFC2822, $a) > \DateTime::createFromFormat(DATE_RFC2822, $b);
	}
	
        public function getUpdateAuthor(\Application\Entity\EventUpdate $eventupdate){
            $repo = $this->em->getRepository('Application\Entity\Log');
            
            $logentries = $repo->getLogEntries($eventupdate);
            if(count($logentries) >= 1 && $logentries[count($logentries)-1]->getAction() == "create" ){
                return $logentries[count($logentries)-1]->getUsername();
            } else {
                return "Unknown";
            }
        }
        
        public function getLastUpdateAuthorName(\Application\Entity\Event $action){
            $repo = $this->em->getRepository('Application\Entity\Log');
            $logentries = $repo->getLogEntries($action);
            if(count($logentries) >= 1){
                return $logentries[0]->getUsername();
            } else {
                return $action->getAuthor()->getUserName();
            }
        }
        
	/**
	 * Returns an array :
	 * datetime => array('date' => datetime object,
         *                   'user' => user name,
	 *                   'changes' => array(array ('fieldname', 'oldvalue', 'newvalue'))
	 * 					)
	 * @param Application\Entity\Event $event
	 */
	public function getHistory($event){
            $history = array();

            $repo = $this->em->getRepository('Application\Entity\Log');
		
            $formatter = \IntlDateFormatter::create(
                            \Locale::getDefault(),
                            \IntlDateFormatter::FULL,
                            \IntlDateFormatter::FULL,
                            'UTC',
                            \IntlDateFormatter::GREGORIAN,
                            'dd LLL, HH:mm');


        //history of event
		$logentries = $repo->getLogEntries($event);	
		if(count($logentries) >= 1 && $logentries[count($logentries)-1]->getAction() == "create" ){
			$ref = null;
			foreach (array_reverse($logentries) as $logentry){
				if(!$ref){ //set up reference == "create" entry
                                    if(!array_key_exists($logentry->getLoggedAt()->format(DATE_RFC2822), $history)){
					$ref = $logentry->getData();
                                        $entry = array();
                                        $entry['date'] = $logentry->getLoggedAt();
                                        $entry['user'] = $logentry->getUsername();
                                        $history[$logentry->getLoggedAt()->format(DATE_RFC2822)] = $entry;
                                    }
                                    $historyentry = array();
                                    $historyentry['fieldname'] = 'create';
                                    $history[$logentry->getLoggedAt()->format(DATE_RFC2822)]['changes'][] = $historyentry;
				} else {
					foreach($logentry->getData() as $key => $value){
						//sometimes log stores values that didn't changed
						if($ref[$key] != $value){
							if(!array_key_exists($logentry->getLoggedAt()->format(DATE_RFC2822), $history)){
								$entry = array();
								$entry['date'] = $logentry->getLoggedAt();
								$entry['changes'] = array();
                                                                $entry['user'] = $logentry->getUsername();
								$history[$logentry->getLoggedAt()->format(DATE_RFC2822)] = $entry;
							}
							$historyentry = array();
							$historyentry['fieldname'] = $key;
							if($key== 'enddate' || $key == 'startdate' ){
								if($key == 'enddate') {
									$historyentry['fieldname'] = "Fin";
								} else if($key == 'startdate') {
									$historyentry['fieldname'] = "Début";
								}
								$historyentry['oldvalue'] = ($ref[$key] ? $formatter->format($ref[$key]) : '');
								$historyentry['newvalue'] = ($value ? $formatter->format($value) : null);
							} else if ($key == 'punctual') {
								$historyentry['oldvalue'] = ($ref[$key] ? "Oui" : "Non") ;
								$historyentry['newvalue'] = ($value ? "Oui" : "Non");
                                                        } else if($key == 'status') {
                                                                $old = $this->em->getRepository('Application\Entity\Status')->find($ref[$key]['id']);
                                                                $new = $this->em->getRepository('Application\Entity\Status')->find($value['id']);
                                                                $historyentry['oldvalue'] =  $old->getName();
								$historyentry['newvalue'] = $new->getName();
                                                        } else if($key == 'impact') {
                                                            $old = $this->em->getRepository('Application\Entity\Impact')->find($ref[$key]['id']);
                                                            $new = $this->em->getRepository('Application\Entity\Impact')->find($value['id']);
                                                            $historyentry['oldvalue'] =  $old->getName();
                                                            $historyentry['newvalue'] = $new->getName();
                                                        } else {
								$historyentry['oldvalue'] = $ref[$key];
								$historyentry['newvalue'] = $value;
							}
							
							$history[$logentry->getLoggedAt()->format(DATE_RFC2822)]['changes'][] = $historyentry;
							//update ref
							$ref[$key] = $value;
						}
					}
				}
			}
		}
		
		//history of customfields
		foreach($this->em->getRepository('Application\Entity\CustomFieldValue')->findBy(array('event'=>$event->getId())) as $customfieldvalue){
			$fieldlogentries = $repo->getLogEntries($customfieldvalue);
			if(count($fieldlogentries) > 1 && $fieldlogentries[count($fieldlogentries)-1]->getAction() == "create"){
				$ref = null;
				foreach(array_reverse($fieldlogentries) as $fieldlogentry){
					if(!$ref){
						$ref = $fieldlogentry->getData();
					} else {
						foreach ($fieldlogentry->getData() as $key => $value){
							if($ref[$key] != $value){
								if(!array_key_exists($fieldlogentry->getLoggedAt()->format(DATE_RFC2822), $history)){
									$entry = array();
									$entry['date'] = $fieldlogentry->getLoggedAt();
									$entry['changes'] = array();
                                                                        $entry['user'] = $fieldlogentry->getUsername();
									$history[$fieldlogentry->getLoggedAt()->format(DATE_RFC2822)] = $entry;
								}
								$historyentry = array();
								$historyentry['fieldname'] = $customfieldvalue->getCustomField()->getName();
								$historyentry['oldvalue'] = $this->sm->get('CustomFieldService')->getFormattedValue($customfieldvalue->getCustomField(),$ref[$key]);
								$historyentry['newvalue'] = $this->sm->get('CustomFieldService')->getFormattedValue($customfieldvalue->getCustomField(),$value);
								$history[$fieldlogentry->getLoggedAt()->format(DATE_RFC2822)]['changes'][] = $historyentry;
								//update ref
								$ref[$key] = $value;
							}
						}
					}
				}
			}
		}
		
		//updates
		foreach($event->getUpdates() as $update){
			if(!array_key_exists($update->getCreatedOn()->format(DATE_RFC2822), $history)){
				$entry = array();
				$entry['date'] = $update->getCreatedOn();
				$entry['changes'] = array();
                                $entry['user'] = $this->getUpdateAuthor($update);
				$history[$update->getCreatedOn()->format(DATE_RFC2822)] = $entry;
			}
			$historyentry = array();
			$historyentry['fieldname'] = 'note';
			$historyentry['oldvalue'] = '';
			$historyentry['newvalue'] = $update->getText();
			$history[$update->getCreatedOn()->format(DATE_RFC2822)]['changes'][] = $historyentry;
		}
                //fiche reflexe
                foreach($event->getChildren() as $child){
                    if(($child->getCategory() instanceof \Application\Entity\ActionCategory) && !$child->getStatus()->isOpen()){
                        if(!array_key_exists($child->getLastModifiedOn()->format(DATE_RFC2822), $history)){
                            $entry = array();
                            $entry['date'] = $child->getLastModifiedOn();
                            $entry['changes'] = array();
                            $entry['user'] = $this->getLastUpdateAuthorName($child);
                            $history[$child->getLastModifiedOn()->format(DATE_RFC2822)] = $entry;
                        }
                        $historyentry = array();
                        $historyentry['fieldname'] = 'action';
                        $historyentry['oldvalue'] = '';
                        $historyentry['newvalue'] = $this->getName($child);
                        $historyentry['status'] = $child->getStatus();
                        $history[$child->getLastModifiedOn()->format(DATE_RFC2822)]['changes'][] = $historyentry;
                    }
                }
                //alertes
                foreach($event->getChildren() as $child){
                    if(($child->getCategory() instanceof \Application\Entity\AlarmCategory) && !$child->getStatus()->isOpen()){
                        if(!array_key_exists($child->getLastModifiedOn()->format(DATE_RFC2822), $history)){
                            $entry = array();
                            $entry['date'] = $child->getLastModifiedOn();
                            $entry['changes'] = array();
                            $entry['user'] = $this->getLastUpdateAuthorName($child);
                            $history[$child->getLastModifiedOn()->format(DATE_RFC2822)] = $entry;
                        }
                        $historyentry = array();
                        $historyentry['fieldname'] = 'alarm';
                        $historyentry['oldvalue'] = '';
                        $historyentry['newvalue'] = $this->getName($child);
                        $historyentry['status'] = $child->getStatus();
                        $history[$child->getLastModifiedOn()->format(DATE_RFC2822)]['changes'][] = $historyentry;
                    }
                }
		uksort($history, array($this, "sortbydate"));
		return $history;
	}
	
}