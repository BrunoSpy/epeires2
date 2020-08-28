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

use Application\Entity\SwitchObject;
use Application\Entity\SwitchObjectCategory;
use Application\Entity\Tab;
use Application\Services\CustomFieldService;
use Application\Services\EventService;
use Doctrine\ORM\EntityManager;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Application\Entity\Event;
use Application\Entity\CustomFieldValue;
use Laminas\Form\Annotation\AnnotationBuilder;
use Doctrine\Laminas\Hydrator\DoctrineObject;
use Application\Form\CustomFieldset;

/**
 *
 * @author Bruno Spyckerelle
 */
class SwitchlistTabController extends TabController
{

    private $entityManager;
    private $customfieldservice;
    private $eventservice;

    public function __construct(EntityManager $entityManager,
                                CustomFieldService $customfieldService,
                                EventService $eventservice,
                                $config, $mattermost, $sessioncontainer)
    {
        parent::__construct($config, $mattermost, $sessioncontainer);
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
        $tabid = $this->params()->fromQuery('tabid', null);

        $viewmodel->setVariables(array(
            'messages' => $return,
            'form' => $this->getObjectForm(),
            'tabid' => $tabid,
        ));

        $tab = $this->entityManager->getRepository(Tab::class)->find($tabid);
        $viewmodel->setVariable('direction', ($tab->isHorizontal() ? 'row' : 'column'));
        $so = $tab->getCategories()[0]->getSwitchObjects()->toArray();
        $viewmodel->setVariable('switchobjectsObjects', $so);
        $viewmodel->setVariable('switchobjects', $this->getSwitchObjects($tabid));

        return $viewmodel;
    }

    private function getObjectForm() {
        $event = new Event();
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($event);
        $form->setHydrator(new DoctrineObject($this->entityManager))->setObject($event);

        $tabid = $this->params()->fromQuery('tabid', null);
        $tab = $this->entityManager->getRepository(Tab::class)->find($tabid);
        $cat = $tab->getCategories()[0];

        $form->add(new CustomFieldset($this->entityManager, $this->customfieldservice, $cat->getId()));
        //uniquement les champs ajoutés par conf
        $form->get('custom_fields')->remove($cat->getSwitchObjectField()->getId());
        $form->get('custom_fields')->remove($cat->getStateField()->getId());
        return $form;
    }
    
    public function switchobjectAction()
    {
        $messages = array();

        $tabid = $this->params()->fromQuery('tabid', null);
        $tab = $this->entityManager->getRepository(Tab::class)->find($tabid);
        $cat = null;
        if($tab) {
            $cat = $tab->getCategories()[0];
        } else {
            $messages['error'][] = "Paramètre tabid incorrect ou manquant";
            return new JsonModel($messages);
        }
        if($cat == null || !($cat instanceof SwitchObjectCategory)) {
            $messages['error'][] = "Aucune catégorie trouvée.";
            return new JsonModel($messages);
        }

        if ($this->isGranted('events.write') && $this->lmcUserAuthentication()->hasIdentity()) {

            $post = $this->getRequest()->getPost();
            $state = $this->params()->fromQuery('state', null);
            $objectid = $this->params()->fromQuery('objectid', null);
            
            $now = new \DateTime('NOW');
            $now->setTimezone(new \DateTimeZone("UTC"));
            
            if ($state != null && $objectid) {
                $events = $this->entityManager
                    ->getRepository('Application\Entity\Event')
                    ->getCurrentEvents('Application\Entity\SwitchObjectCategory');

                $object = $this->entityManager->getRepository(SwitchObject::class)->find($objectid);

                $objectevents = array();
                foreach ($events as $event) {
                    $objectfield = $event->getCategory()->getSwitchObjectField();
                    foreach ($event->getCustomFieldsValues() as $value) {
                        if ($value->getCustomField()->getId() == $objectfield->getId()) {
                            if ($value->getValue() == $objectid) {
                                $objectevents[] = $event;
                            }
                        }
                    }
                }
                
                if ($state == 'true') {
                    // passage d'un objet à l'état OPE -> recherche de l'evt à fermer
                    if (count($objectevents) == 1) {
                        $event = $objectevents[0];
                        $endstatus = $this->entityManager->getRepository('Application\Entity\Status')->find('3');
                        $event->setStatus($endstatus);
                        $event->setEnddate($now);

                        //si objet est parent -> on ferme aussi tous les évènements enfants
                        if($object->getParent() == null) {
                            foreach ($object->getChildren() as $child) {
                                $childevents = array();
                                foreach ($events as $e) {
                                    $objectfield = $e->getCategory()->getSwitchObjectField();
                                    foreach ($e->getCustomFieldsValues() as $value) {
                                        if($value->getCustomField()->getId() == $objectfield->getId()) {
                                            if($value->getValue() == $child->getId()) {
                                                $childevents[] = $e;
                                            }
                                        }
                                    }
                                }
                                if(count($childevents) > 1) {
                                    $messages['error'][] = "Impossible de déterminer l'évènement à terminer.";
                                } elseif (count($childevents) == 1) {
                                    $childevent = $childevents[0];
                                    $childevent->setStatus($endstatus);
                                    $childevent->setEnddate($now);
                                    $this->entityManager->persist($childevent);
                                }
                            }
                        }
                        $this->entityManager->persist($event);
                        try {
                            $this->entityManager->flush();
                            $messages['success'][] = "Evènement correctement terminé.";
                        } catch (\Exception $e) {
                            $messages['error'][] = $e->getMessage();
                        }
                    } else {
                        $messages['error'][] = "Impossible de déterminer l'évènement à terminer.";
                    }
                } else {
                    // passage d'un objet à l'état HS -> on vérifie qu'il n'y a pas d'evt en cours
                    if (count($objectevents) > 0) {
                        $messages['error'][] = "Un évènement est déjà en cours pour cet objet, impossible d'en créer un nouveau";
                    } else {
                        $this->createEvent($cat, $object, $now, isset($post['custom_fields']) ? $post['custom_fields'] : null);
                        //si l'objet a un parent, il faut créer l'évènement pour le parent aussi
                        if($object->getParent() !== null) {
                            $this->createEvent($cat, $object->getParent(), $now);
                        }
                        try {
                            $this->entityManager->flush();
                            $messages['success'][] = "Nouvel évènement objet créé.";
                        } catch (\Exception $e) {
                            $messages['error'][] = $e->getMessage();
                        }

                    }
                }
            } else {
                $messages['error'][] = "Requête incorrecte, impossible de trouver l'objet correspondant.";
            }
        } else {
            $messages['error'][] = "Droits insuffisants pour modifier l'état de l'objet";
        }
        return new JsonModel($messages);
    }

    public function createEvent($cat, $object, $now, $customfields = null)
    {
        $event = new Event();
        $status = $this->entityManager->getRepository('Application\Entity\Status')->find('2');
        $impact = $this->entityManager->getRepository('Application\Entity\Impact')->find('3');
        $event->setStatus($status);
        $event->setStartdate($now);
        $event->setImpact($impact);
        $event->setPunctual(false);
        $object = $this->entityManager->getRepository(SwitchObject::class)->find($object->getId());
        $event->setOrganisation($object->getOrganisation());
        $event->setAuthor($this->lmcUserAuthentication()
            ->getIdentity());

        $objectfieldvalue = new CustomFieldValue();
        $objectfieldvalue->setCustomField($cat->getSwitchObjectField());
        $objectfieldvalue->setValue($object->getId());
        $objectfieldvalue->setEvent($event);
        $event->addCustomFieldValue($objectfieldvalue);
        $statusvalue = new CustomFieldValue();
        $statusvalue->setCustomField($cat->getStateField());
        $statusvalue->setValue(true);
        $statusvalue->setEvent($event);
        $event->addCustomFieldValue($statusvalue);
        $event->setCategory($cat);
        $this->entityManager->persist($objectfieldvalue);
        $this->entityManager->persist($statusvalue);
        //on ajoute les valeurs des champs persos
        if ($customfields) {
            foreach ($customfields as $key => $value) {
                // génération des customvalues si un customfield dont le nom est $key est trouvé
                $customfield = $this->entityManager->getRepository('Application\Entity\CustomField')->findOneBy(array(
                    'id' => $key
                ));
                if ($customfield) {
                    if(is_array($value)) {
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
        if ($object->getModel()) {
            foreach ($this->entityManager->getRepository('Application\Entity\PredefinedEvent')->findBy(array(
                'parent' => $object->getModel()->getId()
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
            foreach ($object->getModel()->getFiles() as $file) {
                $file->addEvent($event);
            }
        }

        //et on sauve le tout
        $this->entityManager->persist($event);

    }

    public function getObjectStateAction()
    {
        $tabid = $this->params()->fromQuery("tabid", null);
        return new JsonModel($this->getSwitchObjects($tabid, false));
    }

    private function getSwitchObjects($tabid, $full = true)
    {

        $objects = array();

        $tab = $this->entityManager->getRepository(Tab::class)->find($tabid);
        $cat = $tab->getCategories()[0];

        foreach ($cat->getSwitchObjects() as $object) {
            if($object->isDecommissionned()) {
                break;
            }
            // avalaible by default
            if ($full) {
                $objects[$object->getId()] = array();
                $objects[$object->getId()]['name'] = $object->getName();
                $objects[$object->getId()]['status'] = true;
            } else {
                $objects[$object->getId()] = true;
            }
        }
        
        $results = $this->entityManager->getRepository('Application\Entity\Event')->getCurrentEvents('Application\Entity\SwitchObjectCategory');
        
        foreach ($results as $result) {
            $statefield = $result->getCategory()
                ->getStateField()
                ->getId();
            $objectfield = $result->getCategory()
                ->getSwitchObjectField()
                ->getId();
            $objectid = 0;
            $available = true;
            foreach ($result->getCustomFieldsValues() as $customvalue) {
                if ($customvalue->getCustomField()->getId() == $statefield) {
                    $available = ! $customvalue->getValue();
                } else 
                    if ($customvalue->getCustomField()->getId() == $objectfield) {
                        $objectid = $customvalue->getValue();
                    }
            }
            if (array_key_exists($objectid, $objects)) {
                if ($full) {
                    $objects[$objectid]['status'] *= $available;
                } else {
                    $objects[$objectid] *= ($available ? true : false);
                }
            }
        }
        
        return $objects;
    }

    public function  getficheAction()
    {
        $viewmodel = new ViewModel();
        $request = $this->getRequest();
    
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
    
        $radarId = $this->params()->fromQuery('id', null);
        
        $radar = $this->entityManager->getRepository(SwitchObject::class)->find($radarId);
        
        $fiche = null;
        $history = null;
        $actions = null;
        if($radar) {
            $events = $this->entityManager
                ->getRepository('Application\Entity\Event')
                ->getCurrentEvents('Application\Entity\SwitchObjectCategory');
            $radarEvents = array();
            foreach ($events as $event) {
                $radarField = $event->getCustomFieldValue($event->getCategory()->getSwitchObjectField());
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