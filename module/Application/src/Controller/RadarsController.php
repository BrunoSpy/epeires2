<?php
/*
 * This file is part of Epeires².
 * Epeires² is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Epeires² is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Epeires². If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace Application\Controller;

use Application\Services\CustomFieldService;
use Application\Services\EventService;
use Doctrine\ORM\EntityManager;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Application\Entity\Event;
use Application\Entity\CustomFieldValue;
use Laminas\Form\Annotation\AnnotationBuilder;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Application\Form\CustomFieldset;

/**
 *
 * @author Bruno Spyckerelle
 */
class RadarsController extends TabController
{

    private $entityManager;
    private $customfieldservice;
    private $eventservice;

    public function __construct(EntityManager $entityManager,
                                CustomFieldService $customfieldService,
                                EventService $eventservice,
                                $config, $mattermost)
    {
        parent::__construct($config, $mattermost);
        $this->entityManager = $entityManager;
        $this->customfieldservice = $customfieldService;
        $this->eventservice = $eventservice;
    }

    public function indexAction()
    {
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
        
        $viewmodel->setVariables(array(
            'messages' => $return,
            'form' => $this->getRadarForm()
        ));
        
        $viewmodel->setVariable('radars', $this->getRadars());
        
        return $viewmodel;
    }

    private function getRadarForm() {
        $event = new Event();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($event);
        $form->setHydrator(new DoctrineObject($this->entityManager))->setObject($event);
        
        $categories = $this->entityManager->getRepository('Application\Entity\RadarCategory')->findBy(array(
            'defaultradarcategory' => true,
            'archived' => false
        ));
        if ($categories) {
            $cat = $categories[0];
            $form->add(new CustomFieldset($this->entityManager, $this->customfieldservice, $cat->getId()));
            //uniquement les champs ajoutés par conf
            $form->get('custom_fields')->remove($cat->getRadarfield()->getId());
            $form->get('custom_fields')->remove($cat->getStatefield()->getId());
            return $form;
        } else {
            return null;
        }
    }
    
    public function switchradarAction()
    {
        $messages = array();
        if ($this->isGranted('events.write') && $this->zfcUserAuthentication()->hasIdentity()) {

            $post = $this->getRequest()->getPost();
            $state = $this->params()->fromQuery('state', null);
            $radarid = $this->params()->fromQuery('radarid', null);
            
            $now = new \DateTime('NOW');
            $now->setTimezone(new \DateTimeZone("UTC"));
            
            if ($state != null && $radarid) {
                $events = $this->entityManager
                    ->getRepository('Application\Entity\Event')
                    ->getCurrentEvents('Application\Entity\RadarCategory');
                
                $radarevents = array();
                foreach ($events as $event) {
                    $radarfield = $event->getCategory()->getRadarfield();
                    foreach ($event->getCustomFieldsValues() as $value) {
                        if ($value->getCustomField()->getId() == $radarfield->getId()) {
                            if ($value->getValue() == $radarid) {
                                $radarevents[] = $event;
                            }
                        }
                    }
                }
                
                if ($state == 'true') {
                    // passage d'un radar à l'état OPE -> recherche de l'evt à fermer
                    if (count($radarevents) == 1) {
                        $event = $radarevents[0];
                        $endstatus = $this->entityManager->getRepository('Application\Entity\Status')->find('3');
                        $event->setStatus($endstatus);
                        $event->setEnddate($now);
                        $this->entityManager->persist($event);
                        try {
                            $this->entityManager->flush();
                            $messages['success'][] = "Evènement radar correctement terminé.";
                        } catch (\Exception $e) {
                            $messages['error'][] = $e->getMessage();
                        }
                    } else {
                        $messages['error'][] = "Impossible de déterminer l'évènement à terminer.";
                    }
                } else {
                    // passage d'un radar à l'état HS -> on vérifie qu'il n'y a pas d'evt en cours
                    if (count($radarevents) > 0) {
                        $messages['error'][] = "Un évènement est déjà en cours pour ce radar, impossible d'en créer un nouveau";
                    } else {
                        $event = new Event();
                        $status = $this->entityManager->getRepository('Application\Entity\Status')->find('2');
                        $impact = $this->entityManager->getRepository('Application\Entity\Impact')->find('3');
                        $event->setStatus($status);
                        $event->setStartdate($now);
                        $event->setImpact($impact);
                        $event->setPunctual(false);
                        $radar = $this->entityManager->getRepository('Application\Entity\Radar')->find($radarid);
                        $event->setOrganisation($radar->getOrganisation());
                        $event->setAuthor($this->zfcUserAuthentication()
                            ->getIdentity());
                        
                        $categories = $this->entityManager->getRepository('Application\Entity\RadarCategory')->findBy(array(
                            'defaultradarcategory' => true
                        ));
                        if ($categories) {
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
                            $this->entityManager->persist($radarfieldvalue);
                            $this->entityManager->persist($statusvalue);
                            //on ajoute les valeurs des champs persos
                            if (isset($post['custom_fields'])) {
                                foreach ($post['custom_fields'] as $key => $value) {
                                    // génération des customvalues si un customfield dont le nom est $key est trouvé
                                    $customfield = $this->entityManager->getRepository('Application\Entity\CustomField')->findOneBy(array(
                                        'id' => $key
                                    ));
                                    if ($customfield) {
                                        if (is_array($value)) {
                                            $temp = "";
                                            foreach ($value as $v) {
                                                $temp .= (string) $v . "\r";
                                            }
                                            $value = trim($temp);
                                        }
                                        $customvalue = new CustomFieldValue();
                                        $customvalue->setEvent($event);
                                        $customvalue->setCustomField($customfield);
                                        $event->addCustomFieldValue($customvalue);
                                        
                                        $customvalue->setValue($value);
                                        $this->entityManager->persist($customvalue);
                                    }
                                }
                            }
    
                            // création de la fiche réflexe
                            if ($radar->getModel()) {
                                foreach ($this->entityManager->getRepository('Application\Entity\PredefinedEvent')->findBy(array(
                                    'parent' => $radar->getModel()->getId()
                                )) as $action) {
                                    $child = new Event();
                                    $child->setParent($event);
                                    $child->setAuthor($event->getAuthor());
                                    $child->setOrganisation($event->getOrganisation());
                                    $child->createFromPredefinedEvent($action);
                                    $child->setStatus($this->entityManager->getRepository('Application\Entity\Status')
                                        ->findOneBy(array(
                                            'defaut' => true,
                                            'open' => true
                                        )));
                                    foreach ($action->getCustomFieldsValues() as $value) {
                                        $newvalue = new CustomFieldValue();
                                        $newvalue->setEvent($child);
                                        $newvalue->setCustomField($value->getCustomField());
                                        $newvalue->setValue($value->getValue());
                                        $child->addCustomFieldValue($newvalue);
                                        $this->entityManager->persist($newvalue);
                                    }
                                    $child->updateAlarmDate();
                                    $this->entityManager->persist($child);
                                }
                                // ajout des fichiers
                                foreach ($radar->getModel()->getFiles() as $file) {
                                    $file->addEvent($event);
                                }
                            }
                            
                            //et on sauve le tout
                            $this->entityManager->persist($event);
                            try {
                                $this->entityManager->flush();
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

    public function getRadarStateAction()
    {
        return new JsonModel($this->getRadars(false));
    }

    private function getRadars($full = true)
    {

        $radars = array();
        
        foreach ($this->entityManager->getRepository('Application\Entity\Radar')->findBy(array(
            'decommissionned' => false
        )) as $radar) {
            // avalaible by default
            if ($full) {
                $radars[$radar->getId()] = array();
                $radars[$radar->getId()]['name'] = $radar->getName();
                $radars[$radar->getId()]['status'] = true;
            } else {
                $radars[$radar->getId()] = true;
            }
        }
        
        $results = $this->entityManager->getRepository('Application\Entity\Event')->getCurrentEvents('Application\Entity\RadarCategory');
        
        foreach ($results as $result) {
            $statefield = $result->getCategory()
                ->getStatefield()
                ->getId();
            $radarfield = $result->getCategory()
                ->getRadarfield()
                ->getId();
            $radarid = 0;
            $available = true;
            foreach ($result->getCustomFieldsValues() as $customvalue) {
                if ($customvalue->getCustomField()->getId() == $statefield) {
                    $available = ! $customvalue->getValue();
                } else 
                    if ($customvalue->getCustomField()->getId() == $radarfield) {
                        $radarid = $customvalue->getValue();
                    }
            }
            if (array_key_exists($radarid, $radars)) {
                if ($full) {
                    $radars[$radarid]['status'] *= $available;
                } else {
                    $radars[$radarid] *= ($available ? true : false);
                }
            }
        }
        
        return $radars;
    }

    public function  getficheAction()
    {
        $viewmodel = new ViewModel();
        $request = $this->getRequest();
    
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
    
        $radarId = $this->params()->fromQuery('id', null);
        
        $radar = $this->entityManager->getRepository('Application\Entity\Radar')->find($radarId);
        
        $fiche = null;
        $history = null;
        $actions = null;
        if($radar) {
            $events = $this->entityManager
                ->getRepository('Application\Entity\Event')
                ->getCurrentEvents('Application\Entity\RadarCategory');
            $radarEvents = array();
            foreach ($events as $event) {
                $radarField = $event->getCustomFieldValue($event->getCategory()->getRadarfield());
                if ($radarField->getValue() == $radarId) {
                    $radarEvents[] = $event;
                }
            }
    
            if (count($radarEvents) >= 1) {
                $event = $radarEvents[0];
                $fiche = $event;
                $history = $this->eventservice->getHistory($event);
    
    
                $qb = $this->entityManager->createQueryBuilder();
                $qb->select(array(
                    'e',
                    'cat'
                ))
                    ->from('Application\Entity\AbstractEvent', 'e')
                    ->innerJoin('e.category', 'cat')
                    ->andWhere('cat INSTANCE OF Application\Entity\ActionCategory')
                    ->andWhere($qb->expr()
                        ->eq('e.parent', $fiche->getId()));
    
                $actions = $qb->getQuery()->getResult();
            }
        }
        $viewmodel->setVariable('history', $history);
        $viewmodel->setVariable('fiche', $fiche);
        $viewmodel->setVariable('actions', $actions);
        return $viewmodel;
    }
}