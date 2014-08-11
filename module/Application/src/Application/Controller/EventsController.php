<?php
/**
 * Epeires 2
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */

namespace Application\Controller;

use Zend\View\Model\ViewModel;
use Application\Entity\Event;
use Application\Form\CategoryFormFieldset;
use Application\Form\CustomFieldset;
use Application\Entity\CustomFieldValue;
use Zend\View\Model\JsonModel;
use Doctrine\Common\Collections\Criteria;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Form\Annotation\AnnotationBuilder;
use Application\Form\FileFieldset;
use Doctrine\ORM\Query\Expr\Join;
use Application\Entity\PredefinedEvent;
use Doctrine\ORM\QueryBuilder;
use Zend\Session\Container;
use Zend\Form\Element;
use ZfcRbac\Exception\UnauthorizedException;
use Application\Entity\FrequencyCategory;

class EventsController extends ZoneController {
	
    public function indexAction(){    	
    	
        parent::indexAction();
        
    	$viewmodel = new ViewModel();
    	
    	$return = array();
    	
    	if($this->flashMessenger()->hasErrorMessages()){
    		$return['errorMessages'] =  $this->flashMessenger()->getErrorMessages();
    	}
    	
    	if($this->flashMessenger()->hasSuccessMessages()){
    		$return['successMessages'] =  $this->flashMessenger()->getSuccessMessages();
    	}
    	
    	$this->flashMessenger()->clearMessages();
    	
    	$this->layout()->cds = "Nom chef de salle";
    	$this->layout()->ipo = "Nom IPO (téléphone)";
    	
        //initialisation de la session si utilisateur connecté
        $session = new Container('zone');
        if($session->zoneshortname == null){
            if($this->zfcUserAuthentication()->hasIdentity()){
                if($this->zfcUserAuthentication()->getIdentity()->getZone()){
                    $session->zoneshortname = $this->zfcUserAuthentication()->getIdentity()->getZone()->getShortname();
                } else {
                    $session->zoneshortname = $this->zfcUserAuthentication()->getIdentity()->getOrganisation()->getShortname();
                }
            }
        }
        
        
     	$viewmodel->setVariables(array('messages'=>$return));
    	 
        return $viewmodel;
    }
    
    public function saveipoAction(){
    	$messages = array();
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		$ipoid = $post['nameipo'];
    		$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    		$ipo = $em->getRepository('Application\Entity\IPO')->find($ipoid);
    		if($ipo) {
    			//un seul IPO par organisation
    			$ipos = $em->getRepository('Application\Entity\IPO')->findBy(array('organisation' => $ipo->getOrganisation()->getId()));
    			foreach ($ipos as $i){
    				$i->setCurrent(false);
    				$em->persist($i);
    			}
    			$ipo->setCurrent(true);
    			$em->persist($ipo);
    			try {
    				$em->flush();
    				$messages['success'][] = "IPO en fonction modifié";
    			} catch (\Exception $e) {
    				$messages['error'][] = $e->getMessage();
    			}
    		} else {
    			$messages['error'][] = "Impossible de modifier l'IPO";
    		}
    	}
    	return new JsonModel($messages);
    }
    
    public function saveopsupAction(){
    	$messages = array();
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		$opsupid = $post['nameopsup'];
    		$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    		$opsup = $em->getRepository('Application\Entity\OperationalSupervisor')->find($opsupid);
    		if($opsup) {
    			//un seul IPO par organisation et par zone
    			$opsups = $em->getRepository('Application\Entity\OperationalSupervisor')->findBy(array('organisation' => $opsup->getOrganisation()->getId(), 
    																									'zone' => $opsup->getZone()->getId()));
    			foreach ($opsups as $i){
    				$i->setCurrent(false);
    				$em->persist($i);
    			}
    			$opsup->setCurrent(true);
    			$em->persist($opsup);
    			try {
    				$em->flush();
    				$messages['success'][] = "Op Sup en fonction modifié";
    			} catch (\Exception $e) {
    				$messages['error'][] = $e->getMessage();
    			}
    		} else {
    			$messages['error'][] = "Impossible de modifier le chef OP";
    		}
    	}
    	return new JsonModel($messages);
    }
    
    public function savezoneAction(){
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		$zone = $post['zone'];
    		$session = new Container('zone');
    		$session->zoneshortname = $zone;
    	}
    	return new JsonModel();
    }
    
    /**
     * Returns a Json with all relevant events and models
     */
    public function searchAction(){
    	$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$results = array();
    	if($this->getRequest()->isPost()){
    		$post = $this->getRequest()->getPost();
    		$search = $post['search'];
    		if(strlen($search) >= 2){
    			
    			//search events
    			$results['events'] = array();
    			$qbEvents = $em->createQueryBuilder();
    			$qbEvents->select(array('e', 'v', 'c', 't'))
    			->from('Application\Entity\Event', 'e')
    			->leftJoin('e.custom_fields_values', 'v')
    			->leftJoin('v.customfield', 'c')
    			->leftJoin('c.type', 't')
                        ->andWhere($qbEvents->expr()->isNull('e.parent'))
    			->orderBy('e.startdate', 'DESC')
    			->setMaxResults( 10 );

    			//search models
    			$results['models'] = array();
    			$qbModels = $em->createQueryBuilder();
    			$qbModels->select(array('m', 'v', 'c', 't'))
    			->from('Application\Entity\PredefinedEvent', 'm')
    			->innerJoin('m.custom_fields_values', 'v')
    			->innerJoin('v.customfield', 'c')
    			->innerJoin('c.type', 't')
                        ->andWhere($qbModels->expr()->isNull('m.parent'))
    			->andWhere($qbModels->expr()->eq('m.searchable', true));
    			
    			$this->addCustomFieldsSearch($qbEvents, $qbModels, $search);
    			
    			$query = $qbEvents->getQuery();
    			$events = $query->getResult();
    			//events are loaded partially during query
    			//as a consequence, we need to reload them
    			$eventsid = array();
    			foreach ($events as $event){
				$eventsid[] = $event->getId();
    			}
    			
    			$query = $qbModels->getQuery();
    			$models = $query->getResult();
    			$modelsid = array();
    			foreach ($models as $model){
    				$modelsid[] = $model->getId();
    			}
    			
    			$em->clear();
    			foreach($eventsid as $id){
				$results['events'][$id] = $this->getEventJson($em->getRepository('Application\Entity\Event')->find($id));
    			}
    			foreach($modelsid as $id){
				$results['models'][$id] = $this->getModelJson($em->getRepository('Application\Entity\PredefinedEvent')->find($id));
    			}
    		}
    	}
    	return new JsonModel($results);
    }
    
    private function addCustomFieldsSearch(QueryBuilder &$qbEvents, QueryBuilder &$qbModels, $search){
    	$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	//search relevant customfields
    	$qb = $em->createQueryBuilder();
    	$qb->select(array('s'))
    	->from('Application\Entity\Sector', 's')
    	->andWhere($qb->expr()->like('s.name', $qb->expr()->literal($search.'%')));
    	$sectors = $qb->getQuery()->getResult();
    	 
    	$qb = $em->createQueryBuilder();
    	$qb->select(array('a'))
    	->from('Application\Entity\Antenna', 'a')
    	->andWhere($qb->expr()->like('a.name', $qb->expr()->literal($search.'%')))
    	->orWhere($qb->expr()->like('a.shortname', $qb->expr()->literal($search.'%')));
    	$query = $qb->getQuery();
    	$antennas = $query->getResult();
    	
    	$qb = $em->createQueryBuilder();
    	$qb->select(array('r'))
    	->from('Application\Entity\Radar', 'r')
    	->andWhere($qb->expr()->like('r.name', $qb->expr()->literal($search.'%')))
    	->orWhere($qb->expr()->like('r.shortname', $qb->expr()->literal($search.'%')));
    	$query = $qb->getQuery();
    	$radars = $query->getResult();
    	
    	$qb = $em->createQueryBuilder();
    	$qb->select(array('f'))
    	->from('Application\Entity\Frequency', 'f')
    	->andWhere($qb->expr()->like('f.value', $qb->expr()->literal($search.'%')))
    	->orWhere($qb->expr()->like('f.othername', $qb->expr()->literal($search.'%')));
    	$query = $qb->getQuery();
    	$frequencies = $query->getResult();
    	
    	$qb = $em->createQueryBuilder();
    	$qb->select(array('st'))
    	->from('Application\Entity\Stack', 'st')
    	->andWhere($qb->expr()->like('st.name', $qb->expr()->literal($search.'%')));
    	$query = $qb->getQuery();
    	$stacks = $query->getResult();    	
    	
        $orModels = $qbModels->expr()->orX($qbModels->expr()->like('m.name', $qbModels->expr()->literal($search.'%')));
        $orEvents = $qbEvents->expr()->orX($qbEvents->expr()->like('v.value', $qbEvents->expr()->literal($search.'%')));
        
    	foreach ($antennas as $antenna){
    		$orEvents->add($qbEvents->expr()->andX(
    				$qbEvents->expr()->eq('t.type', '?1'),
    				$qbEvents->expr()->eq('v.value',$antenna->getId())
    		));
    		$qbEvents->setParameter('1', 'antenna');
    		
    		$orModels->add($qbModels->expr()->andX(
    				$qbModels->expr()->eq('t.type', '?1'),
    				$qbModels->expr()->eq('v.value',$antenna->getId())
    		));
    		$qbModels->setParameter('1', 'antenna');
    	}
    	
    	foreach ($sectors as $sector){
    		$orEvents->add($qbEvents->expr()->andX(
    				$qbEvents->expr()->eq('t.type', '?2'),
    				$qbEvents->expr()->eq('v.value',$sector->getId())
    		));
    		$qbEvents->setParameter('2', 'sector');
    		
    		$orModels->add($qbModels->expr()->andX(
    				$qbModels->expr()->eq('t.type', '?2'),
    				$qbModels->expr()->eq('v.value',$sector->getId())
    		));
    		$qbModels->setParameter('2', 'sector');
    	}
    	
    	foreach ($radars as $radar) {
    		$orEvents->add($qbEvents->expr()->andX(
    			$qbEvents->expr()->eq('t.type', '?3'),
    			$qbEvents->expr()->eq('v.value', $radar->getId())
    		));
    		$qbEvents->setParameter('3', 'radar');
    		
    		$orModels->add($qbModels->expr()->andX(
    				$qbModels->expr()->eq('t.type', '?3'),
    				$qbModels->expr()->eq('v.value', $radar->getId())
    		));
    		$qbModels->setParameter('3', 'radar');
    	}

    	foreach ($frequencies as $frequency){
    		$orEvents->add($qbEvents->expr()->andX(
    				$qbEvents->expr()->eq('t.type', '?4'),
    				$qbEvents->expr()->eq('v.value',$frequency->getId())
    		));
    		$qbEvents->setParameter('4', 'frequency');
    		
    		$orModels->add($qbModels->expr()->andX(
    				$qbModels->expr()->eq('t.type', '?4'),
    				$qbModels->expr()->eq('v.value',$frequency->getId())
    		));
    		$qbModels->setParameter('4', 'frequency');
    	}
        
    	foreach ($stacks as $stack){
    		$orEvents->add($qbEvents->expr()->andX(
    				$qbEvents->expr()->eq('t.type', '?5'),
    				$qbEvents->expr()->eq('v.value',$stack->getId())
    		));
    		$qbEvents->setParameter('5', 'stack');
    		
    		$orModels->add($qbModels->expr()->andX(
    				$qbModels->expr()->eq('t.type', '?5'),
    				$qbModels->expr()->eq('v.value',$stack->getId())
    		));
    		$qbModels->setParameter('5', 'stack');
    	}      
        
        //custom fields text
        $orEvents->add($qbEvents->expr()->andX(
                        $qbEvents->expr()->in('t.type', '?6'),
                        $qbEvents->expr()->like('v.value', $qbEvents->expr()->literal('%'.$search.'%'))
                ));
        $qbEvents->setParameter('6', array('text', 'string'));
                
        //custom fields text
        $orModels->add($qbModels->expr()->andX(
                        $qbModels->expr()->in('t.type', '?6'),
                        $qbModels->expr()->like('v.value', $qbModels->expr()->literal('%'.$search.'%'))
                ));
        $qbModels->setParameter('6', array('text', 'string'));
        
        $qbModels->andWhere($orModels);
        $qbEvents->andWhere($orEvents);
    }
    
    /**
     * 
     * @return \Zend\View\Model\JsonModel Exception : if query param 'return' is true, redirect to route application. 
     */
    public function saveAction(){   
    	
        $messages = array();
        $event = null;
        $return = $this->params()->fromQuery('return', null);
		
    	if($this->zfcUserAuthentication()->hasIdentity()){
    		
    		if($this->getRequest()->isPost()){
    			$post = array_merge_recursive($this->getRequest()->getPost()->toArray(),
    									$this->getRequest()->getFiles()->toArray());
    			$id = $post['id'] ? $post['id'] : null;
    		  		
    			$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    			
    			$credentials = false;
    			
    			if($id){
    				//modification
    				$event = $objectManager->getRepository('Application\Entity\Event')->find($id);
    				if($event){
    					if($this->isGranted('events.write') || $event->getAuthor()->getId() === $this->zfcUserAuthentication()->getIdentity()->getId()){
							$credentials = true;
							//si utilisateur n'a pas les droits events.status, le champ est désactivé et aucune valeur n'est envoyée
							if(!isset($post['status'])){
								$post['status'] = $event->getStatus()->getId();
							}			
    					}
    				}    				
    			} else {
    				//création
    				if($this->isGranted('events.create')){
    					$event = new Event();
    					$event->setAuthor($this->zfcUserAuthentication()->getIdentity());
    					//si utilisateur n'a pas les droits events.status, le champ est désactivé et aucune valeur n'est envoyée
    					if(!isset($post['status'])){
    						$post['status'] = 1;
    					}
    					$credentials = true;
    				}
    			}
    			                        
    			if($credentials){
    				
    				$form = $this->getSkeletonForm($event);
    				$form->setPreferFormInputFilter(true);
    				$form->setData($post);
    				 
    				if($form->isValid()){
    					
                                    $now = new \DateTime('now');
                                    $now->setTimezone(new \DateTimeZone('UTC'));
                                    
    					//TODO find why hydrator can't set a null value to a datetime
    					if(isset($post['enddate']) && empty($post['enddate'])){
    						$this->changeEndDate($event, null);
    					}
                                        
                                        //hydrator can't guess timezone, force UTC of end and start dates
    					if(isset($post['startdate']) && !empty($post['startdate'])){
    						$offset = date("Z");
    						$startdate = new \DateTime($post['startdate']);
    						$startdate->setTimezone(new \DateTimeZone("UTC"));
    						$startdate->add(new \DateInterval("PT".$offset."S"));
                                                if(isset($post['enddate']) && !empty($post['enddate'])) {
                                                    $enddate = new \DateTime($post['enddate']);
                                                    $enddate->setTimezone(new \DateTimeZone("UTC"));
                                                    $enddate->add(new \DateInterval("PT".$offset."S"));
                                                    //on change les deux dates d'un coup pour éviter les incohérences temporaires
                                                    $event->setDates($startdate, $enddate);
                                                    //vérification de cohérence
                                                    $this->changeStartDate($event, $startdate);
                                                    $this->changeEndDate($event, $enddate);
                                                } else {
                                                    $this->changeStartDate($event, $startdate);
                                                }
    					}
                                        
                                        //si statut terminé, non ponctuel et pas d'heure de fin
                                        //alors l'heure de fin est mise auto à l'heure actuelle
                                        //sauf si heure de début future (cas improbable)
                                        if(!$event->isPunctual()
                                            && $event->getStatus()->getId() == 3 
                                            && $event->getEnddate() == null){
                                            if($event->getStartdate() < $now && $event->setEnddate($now)) {
                                                $this->changeEndDate($event, $now);
                                            } else {
                                                //dans le cas contraire, retour au statut confirmé
                                                $confirm = $objectManager->getRepository('Application\Entity\Status')->find(2);
                                                $event->setStatus($confirm);
                                                $messages['error'][] = "Impossible de passer l'évènement au statut terminé.";
                                            }
                                        }
                                        
                                        //si annulé, non ponctuel et pas d'heure de fin
                                        //alors on met l'heure de fin à heure de début +90min
                                        if(!$event->isPunctual()
                                                && $event->getStatus()->getId() == 4
                                                && $event->getEnddate() == null){
                                            if($event->getStartdate() < $now){
                                                $this->changeEndDate($event, $now);
                                            } else {
                                                $enddate = clone $event->getStartdate();
                                                $enddate->add(new \DateInterval("PT90M"));
                                                $this->changeEndDate($event, $enddate);
                                            }
                                        }
                                        
    					//save optional datas
    					if(isset($post['custom_fields'])){
    						foreach ($post['custom_fields'] as $key => $value){
    							//génération des customvalues si un customfield dont le nom est $key est trouvé
    							$customfield = $objectManager->getRepository('Application\Entity\CustomField')->findOneBy(array('id'=>$key));
    							if($customfield){
    								$customvalue = $objectManager->getRepository('Application\Entity\CustomFieldValue')
    								->findOneBy(array('customfield'=>$customfield->getId(), 'event'=>$id));
    								if(!$customvalue){
    									$customvalue = new CustomFieldValue();
    									$customvalue->setEvent($event);
    									$customvalue->setCustomField($customfield);
    									$event->addCustomFieldValue($customvalue);
    								}
    								$customvalue->setValue($value);
    								$objectManager->persist($customvalue);
    							}
    						}
    					}
    					//create associated actions (only relevant if creation from a model)
    					if(isset($post['modelid'])){
    						$parentID = $post['modelid'];
    						//get actions
    						foreach ($objectManager->getRepository('Application\Entity\PredefinedEvent')->findBy(array('parent'=>$parentID)) as $action){
						if($action->getCategory() instanceof \Application\Entity\ActionCategory) {
								$child = new Event();
                                                                $child->setAuthor($event->getAuthor());
								$child->setParent($event);
								$child->setOrganisation($event->getOrganisation());
								$child->createFromPredefinedEvent($action);
								$child->setStatus($objectManager->getRepository('Application\Entity\Status')->findOneBy(array('defaut'=>true, 'open'=> true)));
								//customfields
								foreach($action->getCustomFieldsValues() as $customvalue){
									$newcustomvalue = new CustomFieldValue();
									$newcustomvalue->setEvent($child);
									$newcustomvalue->setCustomField($customvalue->getCustomField());
									$newcustomvalue->setValue($customvalue->getValue());
									$objectManager->persist($newcustomvalue);
								}
								$objectManager->persist($child);
    							}
    						}
    					}
    					//associated actions to be copied
    					if(isset($post['fromeventid'])){
    						$parentID = $post['fromeventid'];
    						foreach ($objectManager->getRepository('Application\Entity\Event')->findBy(array('parent'=>$parentID)) as $action){
							if($action->getCategory() instanceof \Application\Entity\ActionCategory){
								$child = new Event();
                                                                $child->setAuthor($event->getAuthor());
								$child->setParent($event);
								$child->setOrganisation($event->getOrganisation());
								$child->setCategory($action->getCategory());
								$child->setImpact($action->getImpact());
								$child->setPunctual($action->isPunctual());
								$child->setStatus($objectManager->getRepository('Application\Entity\Status')->findOneBy(array('defaut'=>true, 'open'=> true)));   								
								foreach ($action->getCustomFieldsValues() as $customvalue){
									$newcustomvalue = new CustomFieldValue();
									$newcustomvalue->setEvent($child);
									$newcustomvalue->setCustomField($customvalue->getCustomField());
									$newcustomvalue->setValue($customvalue->getValue());
									$objectManager->persist($newcustomvalue);
								}
								$objectManager->persist($child);
    							}
    						}
    					}
    					
    					//fichiers
    					if(isset($post['fichiers']) && is_array($post['fichiers'])){
    						foreach ($post['fichiers'] as $key => $f){
                                                    $file = $objectManager->getRepository('Application\Entity\File')->find($key);
                                                    if($file){
                                                        $file->addEvent($event);
                                                        $objectManager->persist($file);
                                                    }
      
    						}
    					}
    					
                                        //alertes
                                        if(isset($post['alarm']) && is_array($post['alarm'])){
                                            foreach ($post['alarm'] as $key => $alarmpost){
                                                //les modifications d'alarmes existantes sont faites en direct
                                                //et ne passe pas par le formulaire
                                                //voir AlarmController.php
                                                $alarm = new Event();
                                                $alarm->setCategory($objectManager->getRepository('Application\Entity\AlarmCategory')->findAll()[0]);
                                                $alarm->setAuthor($this->zfcUserAuthentication()->getIdentity());
                                                $alarm->setOrganisation($event->getOrganisation());
                                                $alarm->setParent($event);
                                                $alarm->setStatus($objectManager->getRepository('Application\Entity\Status')->findOneBy(array('open'=> true, 'defaut'=>true)));
                                                $offset = date("Z");
                                                $startdate = new \DateTime($alarmpost['date']);
                                                $startdate->setTimezone(new \DateTimeZone("UTC"));
                                                $startdate->add(new \DateInterval("PT".$offset."S"));
                                                $alarm->setStartdate($startdate);
                                                $alarm->setPunctual(true);
                                                $alarm->setImpact($objectManager->getRepository('Application\Entity\Impact')->find(5));
                                                $name = new CustomFieldValue();
                                                $name->setCustomField($alarm->getCategory()->getNamefield());
                                                $name->setValue($alarmpost['name']);
                                                $name->setEvent($alarm);
                                                $alarm->addCustomFieldValue($name);
                                                $comment = new CustomFieldValue();
                                                $comment->setCustomField($alarm->getCategory()->getTextfield());
                                                $comment->setValue($alarmpost['comment']);
                                                $comment->setEvent($alarm);
                                                $alarm->addCustomFieldValue($comment);
                                                $objectManager->persist($name);
                                                $objectManager->persist($comment);
                                                $objectManager->persist($alarm);
                                            }
                                        }
    					if($event->getStatus()->getId() == 3 || $event->getStatus()->getId() == 4) { //passage au statut terminé ou annulé
    						$this->closeEvent($event);
    					}
    					
    					$objectManager->persist($event);
    					try{
    						$objectManager->flush();
    						$messages['success'][] = ($id ? "Evènement modifié" : "Évènement enregistré");
    					} catch(\Exception $e){
    						$messages['error'][] = $e->getMessage();
    					}
    				} else {
    					//erase event
    					$event = null;
    					//formulaire invalide
    					$messages['error'][] = "Impossible d'enregistrer l'évènement.";
    					//traitement des erreurs de validation
    					$this->processFormMessages($form->getMessages(), $messages);
    				}
    					
    			} else {
    				$messages['error'][] = "Création ou modification impossible, droits insuffisants.";
    			}
    			
    		} else {
    			$messages['error'][] = "Requête illégale.";
    		}
    		
    	} else {
    		$messages['error'][] = "Utilisateur non authentifié, action impossible.";
    	}
    	
    	if($return){
    		foreach ($messages['success'] as $message){
    			$this->flashMessenger()->addSuccessMessage($message);
    		}
    		foreach ($messages['error'] as $message){
    			$this->flashMessenger()->addErrorMessage($message);
    		}
    		return $this->redirect()->toRoute('application');
    	} else {
    		$json = array();
    		$json['messages'] = $messages;
    		if($event){
    			$json['events'] = array($event->getId() => $this->getEventJson($event));
    		}
    		return new JsonModel($json);
    	}
    	
    }
    
    public function subformAction(){
    	$part = $this->params()->fromQuery('part', null);
    	
    	$viewmodel = new ViewModel();
    	$request = $this->getRequest();
    	 
    	//disable layout if request by Ajax
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    	 
    	$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	 
    	$form = $this->getSkeletonForm();
    	
    	if($part){
    		switch ($part) {
    			case 'subcategories':
    				$id = $this->params()->fromQuery('id');
    				$subcat = $this->filterReadableCategories($em->getRepository('Application\Entity\Category')->getChilds($id));
    				$subcatarray = array();
    				foreach ($subcat as $cat){
    					$subcatarray[$cat->getId()] = $cat->getName();
    				}
    				$viewmodel->setVariables(array(
    						'part' => $part,
    						'values' => $subcatarray,
    				));
    				break;
    			case 'predefined_events':
    				$id = $this->params()->fromQuery('id');
    				$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    				$category = $em->getRepository('Application\Entity\Category')->find($id);
    				$viewmodel->setVariables(array(
    						'part' => $part,
    						'values' => $em->getRepository('Application\Entity\PredefinedEvent')->getEventsWithCategoryAsArray($category),
    				));
    				break;
    			case 'custom_fields':
    				$viewmodel->setVariables(array('part' => $part,));
    				$form->add(new CustomFieldset($this->getServiceLocator(), $this->params()->fromQuery('id')));
    				break;
    			default:
    				;
    				break;
    		}
    	}
    	$viewmodel->setVariables(array('form' => $form));
    	return $viewmodel;
    }
    
    /**
     * Create a new form
     * @return \Zend\View\Model\ViewModel
     */
    public function formAction(){
    	
    	$viewmodel = new ViewModel();
    	$request = $this->getRequest();
    	
    	//disable layout if request by Ajax    	
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    	  	
    	$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	//création du formulaire : identique en cas de modif ou création
    	$form = $this->getSkeletonForm();
    	 
    	$id = $this->params()->fromQuery('id', null);
    	
    	$copy = $this->params()->fromQuery('copy', null);
    	
    	$model = $this->params()->fromQuery('model', null);
    	
    	$event = null;
    	
    	$pevent = null;
    	
    	$zonefilters = null;
    	
    	if($id || $model){
    		$cat = null;
    		if($id && $model){ //copie d'un modèle
    			$pevent = $em->getRepository('Application\Entity\PredefinedEvent')->find($id);
    			if($pevent){
    				$cat = $pevent->getCategory();
    				$viewmodel->setVariable('model', $pevent);
    				$zonefilters = $em->getRepository('Application\Entity\QualificationZone')->getAllAsArray($pevent->getOrganisation());
    				$form->get('category')->setValue($cat->getId());
    			}
    		} else if($id) { //modification d'un evt
    			$event = $em->getRepository('Application\Entity\Event')->find($id);
    			if($event){
    				$cat = $event->getCategory();
    				$zonefilters = $em->getRepository('Application\Entity\QualificationZone')->getAllAsArray($event->getOrganisation());
       			}
    			
    		}    		
    		if($cat && $cat->getParent()){
    			$form->get('categories')->get('subcategories')->setValueOptions(
    					$em->getRepository('Application\Entity\Category')->getChildsAsArray($cat->getParent()->getId()));
    			$form->get('categories')->get('root_categories')->setAttribute('value', $cat->getParent()->getId());
    			$form->get('categories')->get('subcategories')->setAttribute('value', $cat->getId());
    		} else {
    			$form->get('categories')->get('root_categories')->setAttribute('value', $cat->getId());
    		}
    		//custom fields
    		$form->add(new CustomFieldset($this->getServiceLocator(), $cat->getId()));
    	}
    	if(!$zonefilters) { //si aucun filtre => cas d'un nouvel evt
    		if($this->zfcUserAuthentication()->hasIdentity()){
    			$org = $this->zfcUserAuthentication()->getIdentity()->getOrganisation();
    			$form->get('organisation')->setValue($org->getId());
    			$zonefilters = $em->getRepository('Application\Entity\QualificationZone')->getAllAsArray($org);
    		} else {
    			//aucun utilisateur connecté ??? --> possible si deconnexion déans l'intervalle
    			throw new UnauthorizedException('Aucun utilisateur connecté.');
    		}
    	}
    	
    	if(!$zonefilters || count($zonefilters) == 0){//pas de zone => cacher l'élément
    		$form->remove('zonefilters');
    	} else {
    		$form->get('zonefilters')->setValueOptions($zonefilters);
    	}
    	
    	if($id && $pevent){ //copie d'un modèle
    		//prefill customfields with predefined values
    		foreach ($em->getRepository('Application\Entity\CustomField')->findBy(array('category'=>$cat->getId())) as $customfield){
    			$customfieldvalue = $em->getRepository('Application\Entity\CustomFieldValue')->findOneBy(array('event'=>$pevent->getId(), 'customfield'=>$customfield->getId()));
    			if($customfieldvalue){
    				$form->get('custom_fields')->get($customfield->getId())->setAttribute('value', $customfieldvalue->getValue());
    			}
    		}
    	}
    	
    	if(!$id || ($id && $copy) || ($id && $pevent)){//nouvel évènement
    		if($this->isGranted('events.status')){
    			//utilisateur opérationnel => statut confirmé dès le départ
    			$form->get('status')->setAttribute('value', 2);
    		} else {
    			$form->get('status')->setAttribute('value', 1);
    		}
    	}
    	
    	if($id && $event){ //modification d'un evt, prefill form
    		
    		//custom fields values
    		foreach ($em->getRepository('Application\Entity\CustomField')->findBy(array('category'=>$cat->getId())) as $customfield){
    			$customfieldvalue = $em->getRepository('Application\Entity\CustomFieldValue')->findOneBy(array('event'=>$event->getId(), 'customfield'=>$customfield->getId()));
    			if($customfieldvalue){
    				$form->get('custom_fields')->get($customfield->getId())->setAttribute('value', $customfieldvalue->getValue());
    			}
    		}
    		
    		//other values
    		$form->bind($event);
    		$form->setData($event->getArrayCopy());
    		if($copy){
    			$form->get('id')->setValue('');
    			$form->get('startdate')->setValue('');
    			$form->get('enddate')->setValue('');
                        $form->get('status')->setValue('');
    			$viewmodel->setVariables(array('event'=>$event, 'copy'=>$id));
    		} else {
    			$viewmodel->setVariables(array('event'=>$event));
    		}
    	}
    	    	
    	$viewmodel->setVariables(array('form' => $form));
    	return $viewmodel;
    	 
    }
    
    private function getSkeletonForm($event = null){
    	$em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	if(!$event){
    		$event = new Event();
    	}
    	
    	$builder = new AnnotationBuilder();
    	$form = $builder->createForm($event);
    	$form->setHydrator(new DoctrineObject($em))
    		->setObject($event);    	
    	
    	$form->get('status')
    		->setValueOptions($em->getRepository('Application\Entity\Status')->getAllAsArray());
    	
    	$form->get('impact')
    		->setValueOptions($em->getRepository('Application\Entity\Impact')->getAllAsArray());
    	
    	$form->get('organisation')->setValueOptions($em->getRepository('Application\Entity\Organisation')->getAllAsArray());
    	
    	//add default fieldsets
    	$rootCategories = $this->filterReadableCategories($em->getRepository('Application\Entity\Category')->getRoots(null, true));
    	$rootarray = array();
    	foreach ($rootCategories as $cat){
    		$rootarray[$cat->getId()] = $cat->getName();
    	}
    	
    	$form->add(new CategoryFormFieldset($rootarray));
    	
        $form->bind($event);
    	$form->setData($event->getArrayCopy());
    	
    	//replace default category element
    	$form->remove('category');
    	$category = new Element\Hidden('category');
    	$form->add($category);
    	
    	$form->add(array(
    			'name' => 'submit',
    			'attributes' => array(
    					'type' => 'submit',
    					'value' => 'Ajouter',
    					'class' => 'btn btn-primary',
    			),
    	));
    	    	
    	return $form;
    }
    
    public function getpredefinedvaluesAction(){
    	$predefinedId = $this->params()->fromQuery('id',null);
    	$json = array();
    	$defaultvalues = array();
    	$customvalues = array();
    	
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$entityService = $this->getServiceLocator()->get('EventService');
    	
    	$predefinedEvt = $objectManager->getRepository('Application\Entity\PredefinedEvent')->find($predefinedId);
    	
    	$defaultvalues['punctual'] = $predefinedEvt->isPunctual();
		
    	$defaultvalues['impact'] = $predefinedEvt->getImpact()->getId();
    	
    	foreach ($predefinedEvt->getZonefilters() as $filter){
    		$defaultvalues['zonefilters'][] = $filter->getId();
    	}
    	
    	$json['defaultvalues'] = $defaultvalues;
    	
    	foreach ($predefinedEvt->getCustomFieldsValues() as $customfieldvalue){
    		$customvalues[$customfieldvalue->getCustomField()->getId()] = $customfieldvalue->getValue();
    	}
    	
    	$json['customvalues'] = $customvalues;
    	
    	return new JsonModel($json);
    }
    
    public function getactionsAction(){
    	$parentId = $this->params()->fromQuery('id', null);
    	$json = array();
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	foreach ($objectManager->getRepository('Application\Entity\PredefinedEvent')->findBy(array('parent' => $parentId), array('place' => 'DESC')) as $action){
            if($action->getCategory() instanceof \Application\Entity\ActionCategory) {
    		$json[$action->getId()] = array('name' =>  $this->getServiceLocator()->get('EventService')->getName($action),
    										'impactname' => $action->getImpact()->getName(),
    										'impactstyle' => $action->getImpact()->getStyle());
            }
    	}
    	
    	return new JsonModel($json);
    }
    
    /*
     * Fichiers liés à un évènement, au format JSON
     */
    public function getfilesAction(){
        $eventid = $this->params()->fromQuery('id', null);
        $json = array();
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        foreach($objectManager->getRepository('Application\Entity\PredefinedEvent')->find($eventid)->getFiles() as $file){
            $data = array();
            $data['reference'] = $file->getReference();
            $data['path'] = $file->getPath();
            $data['name'] = ($file->getName() ? $file->getName() : $file->getFilename());
            $fichier = array();
            $fichier['id'] = $file->getId();
            $fichier['datas'] = $data;
            $json[] = $fichier;
        }
        return new JsonModel($json);
    }
    
    /**
     * Alarmes liées à un évènement, au format JSON
     */
    public function getalarmsAction(){
	$eventid = $this->params()->fromQuery('id', null);
	$json = array();
	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
	foreach($objectManager->getRepository('Application\Entity\PredefinedEvent')->findBy(array('parent' => $eventid)) as $alarm){
		if($alarm->getCategory() instanceof \Application\Entity\AlarmCategory){
			$alarmjson = array();
			$now = new \DateTime('NOW');
			$now->setTimezone(new \DateTimeZone("UTC"));
                        $delta = intval($alarm->getStartdateDelta());
                        if($delta < 0){
                            $invdelta = -$delta;
                            $interval = new \DateInterval('PT'.$invdelta.'M');
                            $interval->invert = 1;
                            $now->add($interval);
                        } else {
                            $now->add(new \DateInterval('PT'.$delta.'M'));
                        }
                        $alarmjson['delta'] = $delta;
			$alarmjson['datetime'] = $now->format(DATE_RFC2822);
			foreach($alarm->getCustomFieldsValues() as $value){
				if($value->getCustomField()->getId() == $alarm->getCategory()->getFieldname()->getId()){
					$alarmjson['name'] = $value->getValue();
				} else if($value->getCustomField()->getId() == $alarm->getCategory()->getTextField()->getId()) {
					$alarmjson['comment'] = $value->getValue();
				}
			}
			$json[] = $alarmjson;
		}
	}
	return new JsonModel($json);
    }
    
    /**
     * Return {'open' => '<true or false>'}
     * @return \Zend\View\Model\JsonModel
     */
    public function toggleficheAction(){
    	$evtId = $this->params()->fromQuery('id', null);
    	$json = array();
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	$event = $objectManager->getRepository('Application\Entity\Event')->find($evtId);
    	
    	if($event){
    		$event->setStatus($objectManager->getRepository('Application\Entity\Status')->findOneBy(array('defaut'=>true, 
    																									'open' => !$event->getStatus()->isOpen())));
    		$objectManager->persist($event);
    		$objectManager->flush();
    	}
    	
    	$json['open'] = $event->getStatus()->isOpen();
    	    	
    	return new JsonModel($json);
    }
    
    public function deletefileAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	 
    	$fileid = $this->params()->fromQuery('id', null);
    	$eventid = $this->params()->fromQuery('eventid', null);
    	$messages = array();
    	
    	if($fileid){
    		$file = $objectManager->getRepository('Application\Entity\File')->find($fileid);
                if($eventid && $file){
                    $event = $objectManager->getRepository('Application\Entity\Event')->find($eventid);
                    if($event){
                        $file->removeEvent($event);
                        $objectManager->persist($file);
                    } else {
                        $messages['error'][] = "Impossible d'enlever le fichier de l'évènement";
                    }
                } else {
                    if($file){
                    	$objectManager->remove($file);
                        $messages['success'][] = "Fichier correctement ajouté";
                    } else {
                        $messages['error'][] = "Impossible de supprimer le fichier : aucun fichier correspondant.";
                    }
                }
                try {
                     $objectManager->flush();
                } catch (\Exception $ex) {
                    $messages['error'][] = $ex->getMessage();
                }
        } else {
    		$messages['error'][] = "Impossible de supprimer le fichier : aucun paramètre trouvé.";
    	}
    	return new JsonModel($messages);
    }
    
    /**
     * {'evt_id_0' => {
     * 		'name' => evt_name,
     * 		'modifiable' => boolean,
     * 		'start_date' => evt_start_date,
     *		'end_date' => evt_end_date,
     *		'punctual' => boolean,
     *		'category' => evt_category_name,
     *		'category_short' => evt_category_short_name,
     *		'status_name' => evt_status_name,
     *		'actions' => {
     *			'action_name0' => open? (boolean),
     *			'action_name1' => open? (boolean),
     *			...
     *			}
     * 		},
     * 	'evt_id_1' => ...
     * }
     * @return \Zend\View\Model\JsonModel
     */
    public function geteventsAction(){
    	
    	$lastmodified = $this->params()->fromQuery('lastupdate', null);
    	
    	$day = $this->params()->fromQuery('day', null);
    	
    	$json = array();
        
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        
    	foreach ($objectManager->getRepository('Application\Entity\Event')->getEvents($this->zfcUserAuthentication(), $day, $lastmodified, true) as $event){ 		
    		$json[$event->getId()] = $this->getEventJson($event);
    	}
    	
    	return new JsonModel($json);
    }
    
    private function getEventJson(Event $event){
    	$eventservice = $this->getServiceLocator()->get('EventService');
    	$customfieldservice = $this->getServiceLocator()->get('CustomFieldService');
    	$json = array('name' => $eventservice->getName($event),
    					'modifiable' => $eventservice->isModifiable($event),
    					'start_date' => ($event->getStartdate() ? $event->getStartdate()->format(DATE_RFC2822) : null),
    					'end_date' => ($event->getEnddate() ? $event->getEnddate()->format(DATE_RFC2822) : null),
    					'punctual' => $event->isPunctual(),
    					'category_root' => ($event->getCategory()->getParent() ? $event->getCategory()->getParent()->getName() : $event->getCategory()->getName()),
    					'category_root_short' => ($event->getCategory()->getParent() ? $event->getCategory()->getParent()->getShortName() : $event->getCategory()->getShortName()),
    					'category' => $event->getCategory()->getName(),
    					'category_short' => $event->getCategory()->getShortName(),
    					'category_compact' => $event->getCategory()->isCompactMode(),
    					'status_name' => $event->getStatus()->getName(),
    					'status_id' => $event->getStatus()->getId(),
    					'impact_value' => $event->getImpact()->getValue(),
    					'impact_name' => $event->getImpact()->getName(),
    					'impact_style' => $event->getImpact()->getStyle(),
    					'archived' => $event->isArchived(),
    	);
    	
    	$fields = array();
    	foreach($event->getCustomFieldsValues() as $value){
		$formattedvalue = $customfieldservice->getFormattedValue($value->getCustomField(), $value->getValue());
		if($formattedvalue != null) {
			$fields[$value->getCustomField()->getName()] = $formattedvalue;
		}
    	}
        
        $formatter = \IntlDateFormatter::create(
                            \Locale::getDefault(),
                            \IntlDateFormatter::FULL,
                            \IntlDateFormatter::FULL,
                            'UTC',
                            \IntlDateFormatter::GREGORIAN,
                            'dd LLL, HH:mm');
    	foreach($event->getUpdates() as $update){
            $fields[$formatter->format($update->getCreatedOn())] = nl2br($update->getText());
        }
    	$json['fields'] = $fields;
    	
//     	$actions = array();
//     	foreach ($event->getChildren() as $child){
//             if($child->getStatus() != null) { //Status is required but...
//                 $actions[$eventservice->getName($child)] = $child->getStatus()->isOpen();
//             }
//     	}
//     	$json['actions'] = $actions;
//     	
    	return $json;
    }
    
    private function getModelJson(PredefinedEvent $model){
	$customfieldservice = $this->getServiceLocator()->get('CustomFieldService');
    	$json = array(
    		'name' => $model->getName(),
    		'category_root' => ($model->getCategory()->getParent() ? $model->getCategory()->getParent()->getName() : $model->getCategory()->getName()),
    		'category' => $model->getCategory()->getName(), 
    	);
    	$fields = array();
    	foreach($model->getCustomFieldsValues() as $value){
		$fields[$value->getCustomField()->getName()] = $customfieldservice->getFormattedValue($value->getCustomField(), $value->getValue());
    	}
    	$json['fields'] = $fields;
    	return $json;
    }
    
    /**
     * Liste des catégories racines visibles timeline
     * Au format JSON
     */
    public function getcategoriesAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$json = array();
    	$criteria = Criteria::create()->andWhere(Criteria::expr()->isNull('parent'));
    	$criteria->andWhere(Criteria::expr()->eq('timeline', true));
        $criteria->orderBy(array("place" => Criteria::ASC));
    	$categories = $objectManager->getRepository('Application\Entity\Category')->matching($criteria);
    	$readablecat = $this->filterReadableCategories($categories);
    	foreach ($readablecat as $category){
    		$json[$category->getId()] = array(
    			'name' => $category->getName(),
    			'short_name' => $category->getShortName(),
    			'color' => $category->getColor(),
    			'compact' => $category->isCompactMode(),
                        'place' => $category->getPlace()
    		);
    	}
    	
    	return new JsonModel($json);
    }
    
    private function filterReadableCategories($categories){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$readablecat = array();
    	foreach ($categories as $category){
    		if($this->zfcUserAuthentication()->hasIdentity()){
    			$roles = $this->zfcUserAuthentication()->getIdentity()->getRoles();
    			foreach ($roles as $role){
    				if($category->getReadroles(true)->contains($role)){
    					$readablecat[] = $category;
    					break;
    				}
    			}
    		} else {
    			$role = $this->getServiceLocator()->get('ZfcRbac\Options\ModuleOptions')->getGuestRole();
    			$roleentity = $objectManager->getRepository('Core\Entity\Role')->findOneBy(array('name'=>$role));
    			if($roleentity){
    				if($category->getReadroles(true)->contains($roleentity)){
    					$readablecat[] = $category;
    				}
    			}
    		}
    	
    	}
    	return $readablecat;
    }
    
    /**
     * Liste des impacts au format JSON
     */
    public function getimpactsAction(){
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	$json = array();
    	$impacts = $objectManager->getRepository('Application\Entity\Impact')->findAll();
    	foreach ($impacts as $impact){
    		$json[$impact->getId()] = array(
    				'name' => $impact->getName(),
    				'style' => $impact->getStyle(),
    				'value' => $impact->getValue(),
    		);
    	}
    	return new JsonModel($json);
    }
    
    public function gethistoryAction(){

    	$viewmodel = new ViewModel();
    	$request = $this->getRequest();
    	 
    	//disable layout if request by Ajax
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    	
    	$evtId = $this->params()->fromQuery('id', null);
    	
    	$eventservice = $this->getServiceLocator()->get('EventService');
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	$event = $objectManager->getRepository('Application\Entity\Event')->find($evtId);
    	
    	$history = null;
    	if($event){
    		$history = $eventservice->getHistory($event);
    	}
    	
    	$viewmodel->setVariable('history', $history);
    	
    	return $viewmodel;
    }
    
    /**
     * Usage :
     * $this->url('application', array('controller' => 'events'))+'/changefield?id=<id>&field=<field>&value=<newvalue>'
     * @return JSon with messages
     */
    public function changefieldAction() {
        $formatter = \IntlDateFormatter::create(
                            \Locale::getDefault(),
                            \IntlDateFormatter::FULL,
                            \IntlDateFormatter::FULL,
                            'UTC',
                            \IntlDateFormatter::GREGORIAN,
                            'dd LLL, HH:mm');
        
        $id = $this->params()->fromQuery('id', 0);
        $field = $this->params()->fromQuery('field', 0);
        $value = $this->params()->fromQuery('value', 0);
        $messages = array();
        $event = null;
        if ($id) {
            $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
            $event = $objectManager->getRepository('Application\Entity\Event')->find($id);
            if ($event) {
                //modification autorisée à l'auteur ou aux utilisateurs disposant des droits en écriture
                if ($this->zfcUserAuthentication()->hasIdentity() &&
                        ($event->getAuthor()->getId() == $this->zfcUserAuthentication()->getIdentity()->getId()
                        || $this->isGranted('events.write'))) {
                    switch ($field) {
                        case 'enddate' :
                            $this->changeEndDate($event, new \DateTime($value), $messages);
                            break;
                        case 'startdate' :
                            $this->changeStartDate($event, new \DateTime($value), $messages);
                            break;
                        case 'impact' :
                            $impact = $objectManager->getRepository('Application\Entity\Impact')->findOneBy(array('value' => $value));
                            if ($impact) {
                                $event->setImpact($impact);
                                $objectManager->persist($event);
                                $messages['success'][] = "Impact modifié.";
                            }
                            break;
                        case 'archived' :
                            $event->setArchived($value);
                            $objectManager->persist($event);
                            if($value){
                                $messages['success'][] = "Evènement archivé.";
                            } else {
                                $messages['success'][] = "Evènement non archivé.";
                            }
                            break;
                        case "status" :
                            if ($this->isGranted('events.status')) {
                                $status = $objectManager->getRepository('Application\Entity\Status')->findOneBy(array('name' => $value));
                                if ($status) {
                                    
                                    //si statut terminé et (pas d'heure de fin + pas ponctuel) -> heure de fin = now
                                    if(!$status->isOpen() && $status->isDefault() && !$event->getEnddate() && !$event->isPunctual()){
                                        $now = new \DateTime('now');
                                        $now->setTimezone(new \DateTimeZone('UTC'));
                                        if($event->setEnddate($now)){
                                            $event->setStatus($status);
                                            $messages['success'][] = "Date et heure de fin modifiée au ".$formatter->format($event->getEnddate());
                                            $messages['success'][] = "Evènement passé au statut ".$status->getName();
                                        } else {
                                            $messages['error'][] = "Impossible de changer le statut sans heure de fin";
                                        }
                                        //on ferme l'evt proprement
                                        if(!$status->isOpen()){
                                            $this->closeEvent($event);
                                        }
                                    } else if(!$status->isOpen() && !$status->isDefault() && !$event->getEnddate() && !$event->isPunctual()){
                                        //si statut annulé, non ponctuel et pas d'heure de fin
                                        // -> impossible                                    
                                    } else {
                                        $event->setStatus($status);
                                        $messages['success'][] = "Evènement passé au statut ".$status->getName();
                                    }
                                    $objectManager->persist($event);
                                    
                                }
                            } else {
                                $messages['error'][] = "Droits insuffisants pour changer le statut.";
                            }
                            break;
                        default :
                            break;
                    }
                    try {
                        $objectManager->flush();
                    } catch (\Exception $ex) {
                        $messages['error'][] = $ex->getMessage();
                    }
                } else {
                    $messages['error'][] = "Droits insuffisants pour modifier l'évènement.";
                }
            } else {
                $messages['error'][] = "Requête incorrect : impossible de modifier l'évènement.";
            }
        } else {
            $messages['error'][] = "Impossible de trouver l'évènement à modifier";
        }
        $json = array();
        $json['event'] = array($event->getId() => $this->getEventJson($event));
        $json['messages'] = $messages;
        return new JsonModel($json);
    }

    /**
     * 
     * @param \Application\Entity\Event $event
     * @param \DateTime $enddate
     * @param type $messages
     */
    private function changeEndDate(Event $event, $enddate, &$messages = null){
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $formatter = \IntlDateFormatter::create(
                    \Locale::getDefault(),
                    \IntlDateFormatter::FULL,
                    \IntlDateFormatter::FULL,
                    'UTC',
                    \IntlDateFormatter::GREGORIAN,
                    'dd LLL, HH:mm');
        if($event->setEnddate($enddate)){
            if($enddate){
                if(is_array($messages)){
                    $messages['success'][] = "Date et heure de fin modifiées au ".$formatter->format($event->getEnddate());
                }
            } else {
                if(is_array($messages)){
                    $messages['success'][] = "Date et heure de fin supprimées.";
                }
            }
            $now = new \DateTime('now');
            $now->setTimezone(new \DateTimeZone('UTC'));
            
            foreach ($event->getChildren() as $child){
                if($child->getCategory() instanceof FrequencyCategory){
                    $child->setEnddate($enddate);
                    $objectManager->persist($child);
                }
            }
            
            //passage au statut terminé si 
            //- evt confirmé ou (evt nouveau et heure de début passée)
            //et
            //- heure de fin proche de l'heure de début (15min)
            if ($this->isGranted('events.status') && $event->getEnddate()) {
                $status = $objectManager->getRepository('Application\Entity\Status')->findOneBy(array('open' => false, 'defaut' => true));
                if ( ($event->getStatus()->getId() == 2 ||
                    ($event->getStatus()->getId() <= 2 && $event->getStartDate() < $now))
                    && (($event->getEndDate()->format('U') - $now->format('U')) / 60) < 15) {
                    $event->setStatus($status);
                    //on ferme l'evt proprement
                    $this->closeEvent($event);
                    if(is_array($messages)){
                        $messages['success'][] = "Evènement passé au statut : terminé.";
                    }
                 }
            }
            
            $objectManager->persist($event);
        } else {
            if(is_array($messages)){
                $messages['error'][] = "Impossible de changer la date de fin.";
            }
        }
    }
    
    
    /**
     * Change la date de début d'un evt et 
     * - vérifie la cohérence des évènements fils
     * - vérifie la cohérence du statut
     * @param \Application\Entity\Event $event
     * @param \DateTime $startdate
     * @param array Messages
     * @return true Si tout s'est bien passé
     */
    private function changeStartDate(Event $event, \DateTime $startdate, &$messages = null) {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $formatter = \IntlDateFormatter::create(
                    \Locale::getDefault(),
                    \IntlDateFormatter::FULL,
                    \IntlDateFormatter::FULL,
                    'UTC',
                    \IntlDateFormatter::GREGORIAN,
                    'dd LLL, HH:mm');
        if ($event->setStartdate($startdate)) {
            if(is_array($messages)){
                $messages['success'][] = "Date et heure de début modifiées au " . $formatter->format($event->getStartdate());
            }
            //passage au statut confirmé si pertinent, droits ok et heure de début proche de l'heure actuelle
            if ($this->isGranted('events.status')) {
                $now = new \DateTime('now');
                $now->setTimezone(new \DateTimeZone('UTC'));
                $status = $objectManager->getRepository('Application\Entity\Status')->findOneBy(array('open' => true, 'defaut' => false));
                if ($event->getStatus()->getId() == 1 && (($event->getStartDate()->format('U') - $now->format('U')) / 60) < 15) {
                    $event->setStatus($status);
                    if(is_array($messages)){
                        $messages['success'][] = "Evènement passé au statut : confirmé.";
                    }
                }
            }
            //changement de l'heure de début des évènements fils si pertinent
            foreach($event->getChildren() as $child){
                if($child->getCategory() instanceof FrequencyCategory){
                    $child->setStartdate($startdate);
                    $objectManager->persist($child);
                }
            }
            $objectManager->persist($event);
        } else {
            if(is_array($messages)){
                $messages['error'][] = "Impossible de changer l'heure de début.";
            }
        }
    }

    private function closeEvent(Event $event){
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        //on termine les évènements fils de type fréquence
        foreach ($event->getChildren() as $child) {
            if ($child->getCategory() instanceof FrequencyCategory) {
                if ($event->getStatus()->getId() == 3) {
                    //date de fin uniquement pour les fermetures
                    $child->setEnddate($event->getEnddate());
                }
                $child->setStatus($event->getStatus());
            } else if ($child->getCategory() instanceof \Application\Entity\AlarmCategory) {
                //on annule les alarmes non acquittées dont la fin est postèrieure à la fin de l'evt
                if ($child->getStatus()->getId() != 3 && $child->getStartDate() > $event->getEnddate()) {
                    $cancelStatus = $objectManager->getRepository('Application\Entity\Status')->find(4);
                    $child->setStatus($cancelStatus);
                }
            }
            $objectManager->persist($child);
        }
    }
    
    public function getficheAction(){
    	$viewmodel = new ViewModel();
    	$request = $this->getRequest();
    	 
    	//disable layout if request by Ajax
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
    	
    	$id = $this->params()->fromQuery('id', null);
    	
    	$eventservice = $this->getServiceLocator()->get('EventService');
    	$objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
    	
    	$event = $objectManager->getRepository('Application\Entity\Event')->find($id);
    	
        $history = null;
    	if($event){
            $history = $eventservice->getHistory($event);
    	}
    	
        $viewmodel->setVariable('history', $history);
    	$viewmodel->setVariable('event', $event);
    	
    	return $viewmodel;
    }
    
    public function addnoteAction(){
        $id = $this->params()->fromQuery('id', null);
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $messages = array();
        
        if($id && $this->getRequest()->isPost()){
            $post = $this->getRequest()->getPost();
            $event = $em->getRepository('Application\Entity\Event')->find($id);
            if($event){
                $eventupdate = new \Application\Entity\EventUpdate();
                $eventupdate->setText($post['new-update']);
                $eventupdate->setEvent($event);
                $em->persist($eventupdate);
                try{
                    $em->flush();
                    $messages['success'][] = "Note correctement ajoutée.";
                } catch (\Exception $ex) {
                    $messages['error'][] = $ex->getMessage();
                }
            } else {
                $messages['error'][] = "Impossible d'ajouter la note (évènement non trouvé).";
            }
        } else {
            $messages['error'][] = "Impossible d'ajouter la note.";
        }
        
        return new JsonModel($messages);
    }
    
    public function savenoteAction(){
        $id = $this->params()->fromQuery('id', null);
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $messages = array();
        
        if($id && $this->getRequest()->isPost()){
            $note = $em->getRepository('Application\Entity\EventUpdate')->find($id);
            $post = $this->getRequest()->getPost();
            if($note){
                $note->setText($post['note']);
                $em->persist($note);
                try{
                    $em->flush();
                    $messages['success'][] = "Note correctement mise à jour.";
                } catch (\Exception $ex) {
                    $messages['error'][] = $ex->getMessage();
                }
            } else {
                $messages['error'][] = "Impossible de mettre à jour la note.";
            }
        }
        return new JsonModel($messages);
    }
    
    public function updatesAction(){
        $id = $this->params()->fromQuery('id', null);

        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $viewmodel = new ViewModel();
    	$request = $this->getRequest();
    	//disable layout if request by Ajax
    	$viewmodel->setTerminal($request->isXmlHttpRequest());
        
        
        $event = $em->getRepository('Application\Entity\Event')->find($id);
        
        $viewmodel->setVariable('updates', $event->getUpdates());
        
        return $viewmodel;
        
    }
    
    /**
     * Renvoie les evts les plus créés de la catégorie
     * @return \Zend\View\Model\JsonModel
     */
    public function suggestEventsAction(){
        $json = array();
        $catid = $this->params()->fromQuery('id', null);
        
        $em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        
        //search in category and its children
        $children = $em->getRepository('Application\Entity\Category')->findBy(array('parent' => $catid));
        $catids = array();
        foreach ($children as $child){
            $catids[] = $child->getId();
        }
        $catids[] = $catid;
        
    	//search relevant customfields
    	$qb = $em->createQueryBuilder();
        $qb->select(array('e', 'v', 'f', 'c', 'count(v.value) as cc'))
    		->from('Application\Entity\Event', 'e')
                ->leftJoin('e.category', 'c')
    		->leftJoin('e.custom_fields_values', 'v')
    		->innerJoin('v.customfield', 'f', Join::WITH, $qb->expr()->eq('f.id', 'c.fieldname'))
                ->andWhere($qb->expr()->in('c.id', '?1'))
                ->andWhere($qb->expr()->isNull('e.parent'))
                ->andWhere($qb->expr()->in('e.status', '?2'))
                ->groupBy('v.value')
                ->orderBy('cc', 'DESC')
                ->setMaxResults(6)
                ->setParameter(1, $catids)
                ->setParameter(2, array(3,4,5));
        
        $events = array();
        foreach ($qb->getQuery()->getResult() as $event){
            $events[] = $event[0]->getId();
        }
        
        $em->clear();
        foreach ($events as $id){
            $json[$id] = $this->getEventJson($em->getRepository('Application\Entity\Event')->find($id));
        }
        
        return new JsonModel($json);
    }
}
