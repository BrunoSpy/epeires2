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

use Application\Entity\AlarmCategory;
use Application\Entity\AntennaCategory;
use Application\Entity\Category;
use Application\Entity\EventUpdate;
use Application\Entity\FrequencyCategory;
use Application\Entity\ActionCategory;
use Application\Entity\PostItCategory;
use Application\Entity\Recurrence;
use Application\Entity\Event;
use Application\Entity\CustomFieldValue;
use Application\Entity\PredefinedEvent;

use Application\Entity\Status;
use Application\Services\CustomFieldService;
use Application\Services\EventService;
use Doctrine\ORM\EntityManager;
use Laminas\View\Model\ViewModel;
use Laminas\View\Model\JsonModel;
use Laminas\Form\Annotation\AnnotationBuilder;
use Laminas\Form\Element;

use Application\Form\CategoryFormFieldset;
use Application\Form\CustomFieldset;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Doctrine\ORM\QueryBuilder;

use ZfcRbac\Exception\UnauthorizedException;


/**
 *
 * @author Bruno Spyckerelle
 */
class EventsController extends TabsController
{

    private $eventservice;
    private $customfieldservice;
    private $zfcRbacOptions;
    private $translator;

    public function __construct(EntityManager $entityManager,
                                EventService $eventService,
                                CustomFieldService $customfieldService,
                                $zfcrbacOptions,
                                $config, $mattermost, $translator, $sessionContainer)
    {
        parent::__construct($entityManager, $config, $mattermost, $sessionContainer);
        $this->eventservice = $eventService;
        $this->customfieldservice = $customfieldService;
        $this->zfcRbacOptions = $zfcrbacOptions;
        $this->translator = $translator;
    }
    
    public function indexAction()
    {
        parent::indexAction();

        $return = $this->messages;

        if ($this->flashMessenger()->hasErrorMessages()) {
            foreach ($this->flashMessenger()->getErrorMessages() as $m) {
                $return['error'][] = $m;
            }
        }

        if ($this->flashMessenger()->hasSuccessMessages()) {
            foreach ($this->flashMessenger()->getSuccessMessages() as $m) {
                $return['success'][] = $m;
            }
        }
    
        $this->flashMessenger()->clearMessages();

        $userauth = $this->zfcUserAuthentication();
        $onlyroot = false;
        $cats = array();

        $hasDefaultTab = false;

        //fusion des tabs principaux pour les rôles de l'utilisateur
        if ($userauth != null && $userauth->hasIdentity()) {
            $roles = $userauth->getIdentity()->getRoles();

            foreach ($roles as $r) {
                $tabs = $r->getReadtabs();
                foreach ($tabs as $t) {
                    if($t->isDefault()) {
                        foreach ($t->getCategories() as $c) {
                            if(!in_array($c->getId(), $cats)) {
                                $cats[] = $c->getId();
                            }
                        }
                        $hasDefaultTab = true;
                        $onlyroot += $t->isOnlyroot();
                        //break;
                    }
                }
                if(!$hasDefaultTab) {
                    if(!empty($tabs) && $tabs[0] !== null){
                        //pas de tab par défaut -> suppression bouton + passage au premier tab
                        return $this->redirect()->toRoute('application', array('controller' => 'tabs'), array('query' => array('tabid' => $tabs[0]->getId())));
                    }

                }
            }
        } else {
            if ($userauth != null) {
                $roleentity = $this->getEntityManager()
                    ->getRepository('Core\Entity\Role')
                    ->findOneBy(array(
                        'name' => 'guest'
                    ));
                if ($roleentity) {
                    foreach ($roleentity->getReadtabs() as $t) {
                        if($t->isDefault()) {
                            foreach ($t->getCategories() as $c) {
                                if (!in_array($c->getId(), $cats)) {
                                    $cats[] = $c->getId();
                                }
                            }
                            $onlyroot = $t->isDefault();
                        }
                    }
                }
            }
        }

        $this->layout()->showHome = true;

        $this->viewmodel->setVariable('onlyroot', $onlyroot);
        $this->viewmodel->setVariable('cats', $cats);
        $this->viewmodel->setVariable('default', true);

    
        $this->viewmodel->setVariable('messages', $return);
    
        return $this->viewmodel;
    }
    
    // TODO move to IPOController
    public function saveipoAction()
    {
        $messages = array();
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $ipoid = $post['nameipo'];
            $em = $this->getEntityManager();
            $ipo = $em->getRepository('Application\Entity\IPO')->find($ipoid);
            if ($ipo) {
                // un seul IPO par organisation
                $ipos = $em->getRepository('Application\Entity\IPO')->findBy(array(
                    'organisation' => $ipo->getOrganisation()
                        ->getId()
                ));
                foreach ($ipos as $i) {
                    $i->setCurrent(false);
                    $em->persist($i);
                }
                $ipo->setCurrent(true);
                $em->persist($ipo);
                try {
                    $em->flush();
                    $messages['success'][] = $this->translator->translate("IPO")." en fonction modifié";
                } catch (\Exception $e) {
                    $messages['error'][] = $e->getMessage();
                }
            } else {
                $messages['error'][] = "Impossible de modifier ".$this->translator->translate("the IPO");
            }
        }
        return new JsonModel($messages);
    }
    
    // TODO move to IPOController
    public function getIPOAction()
    {
        $json = array();
        $objectmanager = $this->getEntityManager();
        if ($this->zfcUserAuthentication()->hasIdentity()) {
            
            $currentipo = $objectmanager->getRepository('Application\Entity\IPO')->findOneBy(array(
                'organisation' => $this->zfcUserAuthentication()
                    ->getIdentity()
                    ->getOrganisation()
                    ->getId(),
                'current' => true
            ));
            if($currentipo) {
                $json[$currentipo->getId()] = $currentipo->getName();
            }
        }
        
        return new JsonModel($json);
    }

    public function testAuthenticationAction() {
        if($this->zfcUserAuthentication()->hasIdentity()) {
            $this->getResponse()->setStatusCode(200);
            return new JsonModel();
        } else {
            $this->getResponse()->setStatusCode(403);
            return new JsonModel();
        }
    }
    
    /**
     * Returns a Json with all relevant events and models
     */
    public function searchAction()
    {
        $em = $this->getEntityManager();
        $results = array();
        if ($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $search = $post['search'];
            if (strlen($search) >= 2) {
                
                // search events
                $results['events'] = array();
                $qbEvents = $em->createQueryBuilder();
                $qbEvents->select(array(
                    'e',
                    'v',
                    'c',
                    't',
                    'cat'
                ))
                    ->from('Application\Entity\Event', 'e')
                    ->leftJoin('e.custom_fields_values', 'v')
                    ->leftJoin('v.customfield', 'c')
                    ->leftJoin('c.type', 't')
                    ->leftJoin('e.category', 'cat')
                    ->andWhere($qbEvents->expr()
                    ->isNull('e.parent'))
                    ->orderBy('e.startdate', 'DESC')
                    ->setMaxResults(10);
                
                // search models
                $results['models'] = array();
                $qbModels = $em->createQueryBuilder();
                $qbModels->select(array(
                    'm',
                    'v',
                    'c',
                    't',
                    'cat'
                ))
                    ->from('Application\Entity\PredefinedEvent', 'm')
                    ->innerJoin('m.custom_fields_values', 'v')
                    ->innerJoin('v.customfield', 'c')
                    ->innerJoin('c.type', 't')
                    ->innerJoin('m.category', 'cat')
                    ->andWhere($qbModels->expr()
                    ->isNull('m.parent'))
                    ->andWhere($qbModels->expr()
                    ->eq('m.searchable', true));
                
                $this->addCustomFieldsSearch($qbEvents, $qbModels, $search);
                
                $query = $qbEvents->getQuery();
                $events = $query->getResult();
                // events are loaded partially during query
                // as a consequence, we need to reload them
                $eventsid = array();
                foreach ($events as $event) {
                    $eventsid[] = $event->getId();
                }
                
                $query = $qbModels->getQuery();
                $models = $query->getResult();
                $modelsid = array();
                foreach ($models as $model) {
                    $modelsid[] = $model->getId();
                }
                
                $em->clear();
                foreach ($eventsid as $id) {
                    $results['events'][$id] = $this->eventservice->getJSON($em->getRepository('Application\Entity\Event')
                        ->find($id));
                }
                foreach ($modelsid as $id) {
                    $results['models'][$id] = $this->getModelJson($em->getRepository('Application\Entity\PredefinedEvent')
                        ->find($id));
                }
            }
        }
        return new JsonModel($results);
    }

    private function addCustomFieldsSearch(QueryBuilder &$qbEvents, QueryBuilder &$qbModels, $search)
    {
        $em = $this->getEntityManager();
        // search relevant customfields
        $qb = $em->createQueryBuilder();
        $qb->select(array(
            's'
        ))
            ->from('Application\Entity\Sector', 's')
            ->andWhere($qb->expr()
            ->like('s.name', $qb->expr()
            ->literal($search . '%')));
        $sectors = $qb->getQuery()->getResult();
        
        $qb = $em->createQueryBuilder();
        $qb->select(array(
            'a'
        ))
            ->from('Application\Entity\Antenna', 'a')
            ->andWhere($qb->expr()
            ->like('a.name', $qb->expr()
            ->literal($search . '%')))
            ->orWhere($qb->expr()
            ->like('a.shortname', $qb->expr()
            ->literal($search . '%')));
        $query = $qb->getQuery();
        $antennas = $query->getResult();
        
        $qb = $em->createQueryBuilder();
        $qb->select(array(
            'r'
        ))
            ->from('Application\Entity\Radar', 'r')
            ->andWhere($qb->expr()
            ->like('r.name', $qb->expr()
            ->literal($search . '%')))
            ->orWhere($qb->expr()
            ->like('r.shortname', $qb->expr()
            ->literal($search . '%')));
        $query = $qb->getQuery();
        $radars = $query->getResult();
        
        $qb = $em->createQueryBuilder();
        $qb->select(array(
            'f'
        ))
            ->from('Application\Entity\Frequency', 'f')
            ->andWhere($qb->expr()
            ->like('f.value', $qb->expr()
            ->literal($search . '%')))
            ->orWhere($qb->expr()
            ->like('f.othername', $qb->expr()
            ->literal($search . '%')));
        $query = $qb->getQuery();
        $frequencies = $query->getResult();
        
        $qb = $em->createQueryBuilder();
        $qb->select(array(
            'st'
        ))
            ->from('Application\Entity\Stack', 'st')
            ->andWhere($qb->expr()
            ->like('st.name', $qb->expr()
            ->literal($search . '%')));
        $query = $qb->getQuery();
        $stacks = $query->getResult();
        
        $orModels = $qbModels->expr()->orX($qbModels->expr()
            ->like('m.name', $qbModels->expr()
            ->literal($search . '%')));
        $orEvents = $qbEvents->expr()->orX($qbEvents->expr()
            ->like('v.value', $qbEvents->expr()
            ->literal($search . '%')));
        
        $orModels->add($qbModels->expr()
            ->orX($qbModels->expr()
            ->like('cat.name', $qbModels->expr()
            ->literal($search . '%')), $qbModels->expr()
            ->like('cat.shortname', $qbModels->expr()
            ->literal($search . '%'))));
        
        $orEvents->add($qbEvents->expr()
            ->orX($qbEvents->expr()
            ->like('cat.name', $qbEvents->expr()
            ->literal($search . '%')), $qbEvents->expr()
            ->like('cat.shortname', $qbEvents->expr()
            ->literal($search . '%'))));
        
        foreach ($antennas as $antenna) {
            $orEvents->add($qbEvents->expr()
                ->andX($qbEvents->expr()
                ->eq('t.type', '?1'), $qbEvents->expr()
                ->eq('v.value', $antenna->getId())));
            $qbEvents->setParameter('1', 'antenna');
            
            $orModels->add($qbModels->expr()
                ->andX($qbModels->expr()
                ->eq('t.type', '?1'), $qbModels->expr()
                ->eq('v.value', $antenna->getId())));
            $qbModels->setParameter('1', 'antenna');
        }
        
        foreach ($sectors as $sector) {
            $orEvents->add($qbEvents->expr()
                ->andX($qbEvents->expr()
                ->eq('t.type', '?2'), $qbEvents->expr()
                ->eq('v.value', $sector->getId())));
            $qbEvents->setParameter('2', 'sector');
            
            $orModels->add($qbModels->expr()
                ->andX($qbModels->expr()
                ->eq('t.type', '?2'), $qbModels->expr()
                ->eq('v.value', $sector->getId())));
            $qbModels->setParameter('2', 'sector');
        }
        
        foreach ($radars as $radar) {
            $orEvents->add($qbEvents->expr()
                ->andX($qbEvents->expr()
                ->eq('t.type', '?3'), $qbEvents->expr()
                ->eq('v.value', $radar->getId())));
            $qbEvents->setParameter('3', 'radar');
            
            $orModels->add($qbModels->expr()
                ->andX($qbModels->expr()
                ->eq('t.type', '?3'), $qbModels->expr()
                ->eq('v.value', $radar->getId())));
            $qbModels->setParameter('3', 'radar');
        }
        
        foreach ($frequencies as $frequency) {
            $orEvents->add($qbEvents->expr()
                ->andX($qbEvents->expr()
                ->eq('t.type', '?4'), $qbEvents->expr()
                ->eq('v.value', $frequency->getId())));
            $qbEvents->setParameter('4', 'frequency');
            
            $orModels->add($qbModels->expr()
                ->andX($qbModels->expr()
                ->eq('t.type', '?4'), $qbModels->expr()
                ->eq('v.value', $frequency->getId())));
            $qbModels->setParameter('4', 'frequency');
        }
        
        foreach ($stacks as $stack) {
            $orEvents->add($qbEvents->expr()
                ->andX($qbEvents->expr()
                ->eq('t.type', '?5'), $qbEvents->expr()
                ->eq('v.value', $stack->getId())));
            $qbEvents->setParameter('5', 'stack');
            
            $orModels->add($qbModels->expr()
                ->andX($qbModels->expr()
                ->eq('t.type', '?5'), $qbModels->expr()
                ->eq('v.value', $stack->getId())));
            $qbModels->setParameter('5', 'stack');
        }
        
        // custom fields text
        $orEvents->add($qbEvents->expr()
            ->andX($qbEvents->expr()
            ->in('t.type', '?6'), $qbEvents->expr()
            ->like('v.value', $qbEvents->expr()
            ->literal('%' . $search . '%'))));
        $qbEvents->setParameter('6', array(
            'text',
            'string'
        ));
        
        // custom fields text
        $orModels->add($qbModels->expr()
            ->andX($qbModels->expr()
            ->in('t.type', '?6'), $qbModels->expr()
            ->like('v.value', $qbModels->expr()
            ->literal('%' . $search . '%'))));
        $qbModels->setParameter('6', array(
            'text',
            'string'
        ));
        
        $qbModels->andWhere($orModels);
        $qbEvents->andWhere($orEvents);
    }

    /**
     *
     * @return \Laminas\View\Model\JsonModel Exception : if query param 'return' is true, redirect to route application.
     */
    public function saveAction()
    {
        $messages = array();
        $event = null;
        $events = array();
        $sendEvents = array();
        $return = $this->params()->fromQuery('return', null);
        
        if ($this->zfcUserAuthentication()->hasIdentity()) {
            
            if ($this->getRequest()->isPost()) {
                $post = array_merge_recursive($this->getRequest()
                    ->getPost()
                    ->toArray(), $this->getRequest()
                    ->getFiles()
                    ->toArray());
                $id = $post['id'] ? $post['id'] : null;
                
                $objectManager = $this->getEntityManager();

                $deleteStatus = $objectManager->getRepository('Application\Entity\Status')->find('5');

                $credentials = false;

                if ($id) {
                    // modification
                    $event = $objectManager->getRepository('Application\Entity\Event')->find($id);
                    if ($event) {
                        if ($this->isGranted('events.write') || $event->getAuthor()->getId() === $this->zfcUserAuthentication()
                            ->getIdentity()
                            ->getId()) {
                            $credentials = true;
                            // si utilisateur n'a pas les droits events.status, le champ est désactivé et aucune valeur n'est envoyée
                            if (! isset($post['status'])) {
                                $post['status'] = $event->getStatus()->getId();
                            }
                        }
                    }
                } else {
                    // création
                    if ($this->isGranted('events.create')) {
                        $event = new Event();
                        $event->setAuthor($this->zfcUserAuthentication()
                            ->getIdentity());
                        $event->setOrganisation($this->zfcUserAuthentication()->getIdentity()->getOrganisation());
                        // si utilisateur n'a pas les droits events.status, le champ est désactivé et aucune valeur n'est envoyée
                        if (! isset($post['status'])) {
                            $post['status'] = 1;
                        }
                        $credentials = true;
                    }
                }
                
                if ($credentials) {
                    //préparation de certains champs
                    $startdate = new \DateTime($post['startdate']);
                    $offset = $startdate->getTimezone()->getOffset($startdate);
                    $startdate->setTimezone(new \DateTimeZone("UTC"));
                    $startdate->add(new \DateInterval("PT" . $offset . "S"));

                    $enddate = null;
                    if(isset($post['enddate']) && !empty($post['enddate'])) {
                        $enddate = new \DateTime($post['enddate']);
                        $offset = $enddate->getTimezone()->getOffset($enddate);
                        $enddate->setTimezone(new \DateTimeZone("UTC"));
                        $enddate->add(new \DateInterval("PT" . $offset . "S"));
                    }

                    $diff = 0;
                    if($enddate !== null) {
                        $diff = $startdate->diff($enddate);
                    }

                    $now = new \DateTime('now');
                    $now->setTimezone(new \DateTimeZone('UTC'));

                    $modrecurrence = false;

                    $recurrence = null;

                    if(isset($post['recurrencepattern']) && !empty($post['recurrencepattern'])) {
                        if($id) {
                            //récurrence existante
                            $recurrence = $event->getRecurrence();
                            if($recurrence === null) {
                                //en cas de modification d'un évènement seul et ajout d'une recurrence
                                $recurrence = new Recurrence($startdate, "");
                                $event->setStatus($deleteStatus);
                                $this->closeEvent($event);
                            }
                            if(isset($post['exclude']) && $post['exclude'] == "true") {
                                $recurrence->exclude($event);
                                $event->setRecurrence(null);
                                $status = $objectManager->getRepository('Application\Entity\Status')->find($post['status']);
                                $event->setStatus($status);
                                if($enddate !== null) {
                                    $event->setDates($startdate, $enddate);
                                }
                                $this->changeEndDate($event, $enddate);
                                $this->changeStartDate($event, $startdate);
                                $events[] = $event;
                            } else {
                                //si la règle de récurrence a changé, on exclut les évènements passés
                                //on supprime les évènements restants
                                //et on crée une nouvelle récurrence
                                $test = $recurrence->getStartdate() == $startdate;
                                //changement de récurrence si
                                //* le pattern a changé ou
                                //* la date de début de l'évènement a changé
                                if(strcmp($recurrence->getRecurrencePattern(), $post['recurrencepattern']) !== 0 ||
                                    !($event->getStartdate() == $startdate)) {
                                    $recurrentEvents = $recurrence->getEvents();
                                    foreach ($recurrentEvents as $e) {
                                        if ($e->isPast($now)) {
                                            $e->setRecurrence(null);
                                        } else {
                                            $e->setStatus($deleteStatus);
                                        }
                                        $objectManager->persist($e);
                                        $sendEvents[] = $e;
                                    }
                                    //si le statut est positionné à "Supprimé"
                                    //on ne crée pas de nouveaux évènements
                                    if($post['status'] != 5) {
                                        //$objectManager->remove($recurrence);
                                        $recurrence = new Recurrence($startdate, $post['recurrencepattern']);
                                        $objectManager->persist($recurrence);
                                        foreach ($recurrence->getRSet() as $occurrence) {
                                            $e = new Event();
                                            $e->setRecurrence($recurrence);
                                            $e->setAuthor($this->zfcUserAuthentication()
                                                ->getIdentity());
                                            $e->setOrganisation($this->zfcUserAuthentication()->getIdentity()->getOrganisation());
                                            $status = $objectManager->getRepository('Application\Entity\Status')->find(1);
                                            $e->setStatus($status);
                                            $e->setStartdate($occurrence);
                                            if ($enddate !== null) {
                                                $end = clone $occurrence;
                                                $end->add($diff);
                                                $e->setEnddate($end);
                                            }
                                            $modrecurrence = true;
                                            $events[] = $e;
                                        }
                                    }
                                } else {
                                    //sinon on exclut simplement les evts passés
                                    //mise à jour des évènements futurs en fonction des champs modifiés
                                    $recurrentEvents = $recurrence->getEvents();
                                    foreach ($recurrentEvents as $e){
                                        if($e->isPast($now)) {
                                            $recurrence->exclude($e);
                                            $e->setRecurrence(null);
                                            $objectManager->persist($e);
                                            $sendEvents[] = $e;
                                        } else {
                                            $events[] = $e;
                                            //exception pour la suppression : appliqué à tous les évènements futurs
                                            if($post['status'] == 5) {
                                                $e->setStatus($deleteStatus);
                                            }
                                        }
                                    }
                                    //on mets à jour date de fin et statut de l'évènement sélectionné
                                    $status = $objectManager->getRepository('Application\Entity\Status')->find($post['status']);
                                    $event->setStatus($status);
                                    if($enddate !== null) {
                                        $event->setDates($startdate, $enddate);
                                    }
                                    $this->changeEndDate($event, $enddate);
                                    $this->changeStartDate($event, $startdate);
                                }
                            }
                        } else {
                            //nouvelle récurrence
                            $pattern = $post['recurrencepattern'];
                            $recurrence = new Recurrence($startdate, $pattern);
                            $objectManager->persist($recurrence);
                            $rset = $recurrence->getRSet();
                            foreach ($rset as $occurrence) {
                                $e = new Event();
                                $e->setRecurrence($recurrence);
                                $status = $objectManager->getRepository('Application\Entity\Status')->find(1);
                                $e->setStatus($status);
                                $e->setStartdate($occurrence);
                                $e->setAuthor($this->zfcUserAuthentication()
                                    ->getIdentity());
                                $e->setOrganisation($this->zfcUserAuthentication()->getIdentity()->getOrganisation());
                                if($enddate !== null) {
                                    $end = clone $occurrence;
                                    $end->add($diff);
                                    $e->setEnddate($end);
                                }
                                $events[] = $e;
                            }
                        }
                    } else {
                        //un seul évènement
                        $status = $objectManager->getRepository('Application\Entity\Status')->find($post['status']);
                        $event->setStatus($status);
                        if($enddate !== null) {
                            $event->setDates($startdate, $enddate);
                        }
                        $this->changeEndDate($event, $enddate);
                        $this->changeStartDate($event, $startdate);
                        $events[] = $event;
                    }

                    foreach ($events as $e) {

                        //statut et date de fin sont gérés au dessus
                        //car les traitements sont spécifiques à chaque cas

                        //impact
                        $impact = $objectManager->getRepository('Application\Entity\Impact')->find($post['impact']);
                        $e->setImpact($impact);

                        //catégorie
                        $e->setCategory($objectManager->getRepository('Application\Entity\Category')->find($post['category']));

                        //champs horaires : ponctuel, programmé
                        $e->setPunctual($post['punctual']);
                        $e->setScheduled($post['scheduled']);

                        //cohérence horaires, statut
                        // si statut terminé, non ponctuel et pas d'heure de fin
                        // alors l'heure de fin est mise auto à l'heure actuelle
                        // sauf si heure de début future (cas improbable)
                        if (! $e->isPunctual() && $e->getStatus()->getId() == 3 && $e->getEnddate() == null) {
                            if ($e->getStartdate() < $now && $e->setEnddate($now)) {
                                $this->changeEndDate($e, $now);
                            } else {
                                // dans le cas contraire, retour au statut confirmé
                                $confirm = $objectManager->getRepository('Application\Entity\Status')->find(2);
                                $e->setStatus($confirm);
                                $messages['error'][] = "Impossible de passer l'évènement au statut terminé.";
                            }
                        }
                        // si annulé, non ponctuel et pas d'heure de fin
                        // alors on met l'heure de fin à heure de début +90min
                        if (! $e->isPunctual() && $e->getStatus()->getId() == 4 && $e->getEnddate() == null) {
                            if ($e->getStartdate() < $now) {
                                $this->changeEndDate($e, $now);
                            } else {
                                $enddate = clone $e->getStartdate();
                                $enddate->add(new \DateInterval("PT90M"));
                                $this->changeEndDate($e, $enddate);
                            }
                        }

                        //custom fields
                        if (isset($post['custom_fields'])) {
                            foreach ($post['custom_fields'] as $key => $value) {
                                // génération des customvalues si un customfield dont le nom est $key est trouvé
                                $customfield = $objectManager
                                    ->getRepository('Application\Entity\CustomField')
                                    ->findOneBy(array('id' => $key));
                                if ($customfield) {
                                    if (is_array($value)) {
                                        $temp = "";
                                        foreach ($value as $v) {
                                            $temp .= (string) $v . "\r";
                                        }
                                        $value = trim($temp);
                                    }
                                    $customvalue = $objectManager
                                        ->getRepository('Application\Entity\CustomFieldValue')
                                        ->findOneBy(array(
                                            'customfield' => $customfield->getId(),
                                            'event' => $e->getId())
                                        );
                                    if (! $customvalue) {
                                        $customvalue = new CustomFieldValue();
                                        $customvalue->setEvent($e);
                                        $customvalue->setCustomField($customfield);
                                        $e->addCustomFieldValue($customvalue);
                                    }
                                    $customvalue->setValue($value);
                                }
                            }
                        }

                        //une fois les évènements fils positionnés, on vérifie si il faut clore l'évènement
                        if ($e->getStatus()->getId() == 3 // passage au statut terminé
                            || $e->getStatus()->getId() == 4 // passage au statut annulé
                            || $e->getStatus()->getId() == 5) { // passage au statut supprimé
                            $this->closeEvent($e);
                        }

                        // create associated actions (only relevant if creation from a model)
                        //en cas d'évènements récurrents, seuls les nouveaux évènements doivent être impactés
                        if (isset($post['modelid'])) {
                            $parentID = $post['modelid'];
                            // get actions
                            foreach ($objectManager->getRepository('Application\Entity\PredefinedEvent')->findBy(array(
                                'parent' => $parentID
                            ), array(
                                'place' => 'ASC'
                            )) as $action) {
                                if ($action->getCategory() instanceof ActionCategory) {
                                    $child = new Event();
                                    $child->setAuthor($e->getAuthor());
                                    $child->setParent($e);
                                    $child->setOrganisation($e->getOrganisation());
                                    $child->createFromPredefinedEvent($action);
                                    $child->setStatus($objectManager->getRepository('Application\Entity\Status')
                                        ->findOneBy(array(
                                            'defaut' => true,
                                            'open' => true
                                        )));
                                    // customfields
                                    foreach ($action->getCustomFieldsValues() as $customvalue) {
                                        $newcustomvalue = new CustomFieldValue();
                                        $newcustomvalue->setEvent($child);
                                        $newcustomvalue->setCustomField($customvalue->getCustomField());
                                        $newcustomvalue->setValue($customvalue->getValue());
                                        $objectManager->persist($newcustomvalue);
                                    }
                                    $e->addChild($child);
                                    $objectManager->persist($child);
                                }
                            }
                        }

                        //en cas de mod de récurrence, les actions et fichiers ne sont as inclus dans le formulaire
                        //on les reprend de l'évènement modifié
                        if($modrecurrence) {
                            //actions et alarmes
                            foreach($event->getChildren() as $child) {
                                $newchild = new Event();
                                $newchild->createFromEvent($child);
                                $newchild->setParent($e);
                                $newchild->setStatus($objectManager->getRepository('Application\Entity\Status')
                                    ->findOneBy(array(
                                        'defaut' => true,
                                        'open' => true
                                    )));
                                foreach ($child->getCustomFieldsValues() as $value) {
                                    $newcustomvalue = new CustomFieldValue();
                                    $newcustomvalue->setEvent($newchild);
                                    $newcustomvalue->setCustomField($value->getCustomField());
                                    $newcustomvalue->setValue($value->getValue());
                                    $objectManager->persist($newcustomvalue);
                                }

                                if($child->getCategory() instanceof AlarmCategory) {
                                    $start = clone $e->getStartdate();
                                    $diff = $event->getStartdate()->diff($child->getStartdate());
                                    $start->add($diff);
                                    $newchild->setStartdate($start);
                                }
                                $e->addChild($newchild);
                                $objectManager->persist($newchild);
                            }
                            //fichiers
                            foreach ($event->getFiles() as $f) {
                                $f->addEvent($e);
                                $objectManager->persist($f);
                            }
                        }

                        // associated actions to be copied
                        if (isset($post['fromeventid'])) {
                            $parentID = $post['fromeventid'];
                            foreach ($objectManager->getRepository('Application\Entity\Event')->findBy(array(
                                'parent' => $parentID
                            ), array(
                                'place' => 'DESC'
                            )) as $action) {
                                if ($action->getCategory() instanceof \Application\Entity\ActionCategory) {
                                    $child = new Event();
                                    $child->setAuthor($e->getAuthor());
                                    $child->setParent($e);
                                    $child->setOrganisation($event->getOrganisation());
                                    $child->setCategory($action->getCategory());
                                    $child->setImpact($action->getImpact());
                                    $child->setPunctual($action->isPunctual());
                                    $child->setStatus($objectManager->getRepository('Application\Entity\Status')
                                        ->findOneBy(array(
                                            'defaut' => true,
                                            'open' => true
                                        )));
                                    foreach ($action->getCustomFieldsValues() as $customvalue) {
                                        $newcustomvalue = new CustomFieldValue();
                                        $newcustomvalue->setEvent($child);
                                        $newcustomvalue->setCustomField($customvalue->getCustomField());
                                        $newcustomvalue->setValue($customvalue->getValue());
                                        $child->addCustomFieldValue($newcustomvalue);
                                        //$objectManager->persist($newcustomvalue);
                                    }
                                    $e->addChild($child);
                                    //$objectManager->persist($child);
                                }
                            }
                        }

                        // fichiers
                        if (isset($post['fichiers']) && is_array($post['fichiers'])) {
                            foreach ($post['fichiers'] as $key => $f) {
                                $file = $objectManager->getRepository('Application\Entity\File')->find($key);
                                if ($file) {
                                    $file->addEvent($e);
                                   // $e->addFile($file);
                                    //$objectManager->persist($file);
                                }
                            }
                        }

                        // alertes
                        if (isset($post['alarm']) && is_array($post['alarm'])) {
                            foreach ($post['alarm'] as $key => $alarmpost) {
                                // les modifications d'alarmes existantes sont faites en direct
                                // et ne passent pas par le formulaire
                                // voir AlarmController.php
                                $alarm = new Event();
                                $alarm->setCategory($objectManager->getRepository('Application\Entity\AlarmCategory')
                                    ->findAll()[0]);
                                $alarm->setAuthor($this->zfcUserAuthentication()
                                    ->getIdentity());
                                $alarm->setOrganisation($e->getOrganisation());
                                $alarm->setParent($e);
                                $alarm->setStatus($objectManager->getRepository('Application\Entity\Status')
                                    ->findOneBy(array(
                                        'open' => true,
                                        'defaut' => true
                                    )));
                                $startdate = new \DateTime($alarmpost['date']);
                                $offset = $startdate->getTimezone()->getOffset($startdate);
                                $startdate->setTimezone(new \DateTimeZone("UTC"));
                                $startdate->add(new \DateInterval("PT" . $offset . "S"));
                                $alarm->setStartdate($startdate);
                                $alarm->setPunctual(true);
                                $alarm->setImpact($objectManager->getRepository('Application\Entity\Impact')
                                    ->find(5));
                                $name = new CustomFieldValue();
                                $name->setCustomField($alarm->getCategory()
                                    ->getNamefield());
                                $name->setValue($alarmpost['name']);
                                $name->setEvent($alarm);
                                $alarm->addCustomFieldValue($name);
                                $comment = new CustomFieldValue();
                                $comment->setCustomField($alarm->getCategory()
                                    ->getTextfield());
                                $comment->setValue($alarmpost['comment']);
                                $comment->setEvent($alarm);
                                $alarm->addCustomFieldValue($comment);
                                $deltabegin = new CustomFieldValue();
                                $deltabegin->setCustomField($alarm->getCategory()
                                    ->getDeltaBeginField());
                                $deltabegin->setValue($alarmpost['deltabegin']);
                                $deltabegin->setEvent($alarm);
                                $alarm->addCustomFieldValue($deltabegin);
                                $deltaend = new CustomFieldValue();
                                $deltaend->setCustomField($alarm->getCategory()
                                    ->getDeltaEndField());
                                $deltaend->setValue($alarmpost['deltaend']);
                                $deltaend->setEvent($alarm);
                                $alarm->addCustomFieldValue($deltaend);
                                $e->addChild($alarm);
                                //$objectManager->persist($name);
                                //$objectManager->persist($comment);
                                //$objectManager->persist($deltabegin);
                                //$objectManager->persist($deltaend);
                                $objectManager->persist($alarm);
                            }
                        }


                        //certains évènements induisent des évènements fils
                        //il faut les créer à ce moment
                        if( !$id ) { //uniquement lors d'une création d'évènement
                            if( $e->getCategory() instanceof AntennaCategory ) {
                                $frequencies = $e->getCustomFieldValue($e->getCategory()->getFrequenciesField());
                                $antennaState = $e->getCustomFieldValue($e->getCategory()->getStatefield())->getValue();
                                $antennaId = $e->getCustomFieldValue($e->getCategory()->getAntennafield())->getValue();
                                $antenna = $objectManager->getRepository('Application\Entity\Antenna')->find($antennaId);
                                $freqs = array();
                                if($frequencies) {
                                    $freqids = explode("\r", $frequencies->getValue());
                                    foreach($freqids as $freqid) {
                                        $freq = $objectManager->getRepository('Application\Entity\Frequency')->find($freqid);
                                        if($freq) {
                                            $freqs[] = $freq;
                                        }
                                    }
                                }
                                if(!$frequencies || count($freqs) == 0) {
                                    //pas d'info sur les fréquences impactées : toutes les fréquences de l'antenne sont en panne
                                    foreach($antenna->getMainfrequencies() as $freq) {
                                        $freqs[] = $freq;
                                    }
                                    foreach($antenna->getMainfrequenciesclimax() as $freq){
                                        $freqs[] = $freq;
                                    }
                                }
                                if($antenna && $antennaState == 1) {
                                    //antenne indisponible : il faut créer les changements de couverture
                                    //pour les fréquences impactées
                                    foreach($freqs as $freq) {
                                        if($freq->hasMainAntenna($antenna) || $freq->hasMainClimaxAntenna($antenna)){
                                            $objectManager
                                                ->getRepository('Application\Entity\Event')
                                                ->addChangeFrequencyCovEvent(
                                                    $freq,
                                                    1, // couv secours
                                                    0, // toujours dispo
                                                    "Antenne principale indisponible",
                                                    $e->getStartdate(),
                                                    $e->getEnddate(),
                                                    $this->zfcUserAuthentication()->getIdentity(),
                                                    $e,
                                                    $messages
                                                );
                                        }
                                    }
                                }
                            }
                        }


                        //mises en cohérence des alarmes
                        $e->updateAlarms();
                        $objectManager->persist($e);
                    }

                    try {
                        if($recurrence !== null) {
                            $objectManager->persist($recurrence);
                            $messages['success'][] = "Récurrence correctement enregistrée.";
                        }
                        $objectManager->flush();
                        $messages['success'][] = ($id ? "Evènement modifié" : "Évènement enregistré");
                    } catch (\Exception $e) {
                        $messages['error'][] = "Impossible d'enregistrer l'évènement.";
                        $messages['error'][] = $e->getMessage();
                        $events = array();
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
        
        if ($return) {
            foreach ($messages['success'] as $message) {
                $this->flashMessenger()->addSuccessMessage($message);
            }
            foreach ($messages['error'] as $message) {
                $this->flashMessenger()->addErrorMessage($message);
            }
            return $this->redirect()->toRoute('application');
        } else {
            $json = array();
            $json['messages'] = $messages;
            $jsonevents = array();
            foreach ($events as $e) {
                $jsonevents[$e->getId()] = $this->eventservice->getJSON($e);
            }
            foreach ($sendEvents as $e) {
                $jsonevents[$e->getId()] = $this->eventservice->getJSON($e);
            }
            if (count($jsonevents) > 0) {
                $json['events'] = $jsonevents;
            }
            return new JsonModel($json);
        }
    }

    public function subformAction()
    {
        $part = $this->params()->fromQuery('part', null);
        //$tabid = $this->params()->fromQuery('tabid', null);
        $cats = $this->params()->fromQuery('cats', null);
        $viewmodel = new ViewModel();
        $request = $this->getRequest();
        
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $em = $this->getEntityManager();
        
        $form = $this->getSkeletonForm($cats);
        
        if ($part) {
            switch ($part) {
                case 'subcategories':
                    $id = $this->params()->fromQuery('id');
                    $subcat = $this->filterReadableCategories($em->getRepository('Application\Entity\Category')
                        ->getChilds($id));
                    $other = true;
                    if ($cats !== null) {
                        //restrict subcats to those present in cats list
                        $tempsubcat = array();
                        foreach ($subcat as $cat) {
                            if (in_array($cat->getId(), $cats)) {
                                $tempsubcat[] = $cat;
                            }
                        }
                        $subcat = $tempsubcat;
                        //don't add 'other' field if list is restricted and parent not in list
                        if(!in_array($id, $cats)) {
                            $other = false;
                        }
                    }
                    $subcatarray = array();
                    foreach ($subcat as $cat) {
                        $subcatarray[$cat->getId()] = $cat->getName();
                    }
                    $viewmodel->setVariables(array(
                        'other' => $other,
                        'part' => $part,
                        'values' => $subcatarray
                    ));
                    break;
                case 'predefined_events':
                    $id = $this->params()->fromQuery('id');
                    $em = $this->getEntityManager();
                    $category = $em->getRepository('Application\Entity\Category')->find($id);
                    $viewmodel->setVariables(array(
                        'part' => $part,
                        'values' => $em->getRepository('Application\Entity\PredefinedEvent')
                            ->getEventsWithCategoryAsArray($category),
                        'subvalues' => $em->getRepository('Application\Entity\PredefinedEvent')
                            ->getEventsFromCategoryAsArray($category)
                    ));
                    break;
                case 'custom_fields':
                    $viewmodel->setVariables(array(
                        'part' => $part
                    ));
                    $form->add(new CustomFieldset($this->entityManager, $this->customfieldservice, $this->params()
                        ->fromQuery('id')));
                    break;
                default:
                    ;
                    break;
            }
        }
        $viewmodel->setVariables(array(
            'form' => $form
        ));
        return $viewmodel;
    }

    public function getCustomValuesAction()
    {
        $origin = $this->params()->fromQuery('origin', null);
        $value = $this->params()->fromQuery('value', null);
        $target = $this->params()->fromQuery('target', null);
        $values = array();
        if($origin !== null && $value !== null && $target !== null) {
            $fieldOrigin = $this->getEntityManager()->getRepository('Application\Entity\CustomField')->find($origin);
            //TODO étendre le principe à n'importe quels champs
            $antenna = $this->getEntityManager()->getRepository('Application\Entity\Antenna')->find($value);
            foreach ($antenna->getAllFrequencies() as $f) {
                $pair = array();
                $pair["name"] = $f->getName();
                $pair["id"] = $f->getId();
                $values[] = $pair;
            }
        }
        return new JsonModel($values);
    }

    /**
     * Create a new form
     * 
     * @return \Laminas\View\Model\ViewModel
     */
    public function formAction()
    {
        $viewmodel = new ViewModel();
        $request = $this->getRequest();
        
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $em = $this->getEntityManager();
        
        // création du formulaire : identique en cas de modif ou création
        
        //$tabid = $this->params()->fromQuery('tabid', null);
        
        $cats = $this->params()->fromQuery('cats', null);

        $form = $this->getSkeletonForm($cats);
        
        $id = $this->params()->fromQuery('id', null);
        
        $copy = $this->params()->fromQuery('copy', null);
        
        $model = $this->params()->fromQuery('model', null);
        
        $event = null;
        
        $pevent = null;
        
        $zonefilters = null;
        
        if ($id || $model) {
            $cat = null;
            if ($id && $model) { // copie d'un modèle
                $pevent = $em->getRepository('Application\Entity\PredefinedEvent')->find($id);
                if ($pevent) {
                    $cat = $pevent->getCategory();
                    $viewmodel->setVariable('model', $pevent);
                    $viewmodel->setVariable('actions', $this->getActions($pevent->getId()));
                    $zonefilters = $em->getRepository('Application\Entity\QualificationZone')->getAllAsArray($pevent->getOrganisation());
                    $form->get('category')->setValue($cat->getId());
                }
            } else 
                if ($id) { // modification d'un evt
                    $event = $em->getRepository('Application\Entity\Event')->find($id);
                    if ($event) {
                        $cat = $event->getCategory();
                        $zonefilters = $em->getRepository('Application\Entity\QualificationZone')->getAllAsArray($event->getOrganisation());
                        $viewmodel->setVariable('actions', $this->getActions($event->getId()));
                    }
                }
            if ($cat && $cat->getParent()) {
                $form->get('categories')
                    ->get('subcategories')
                    ->setValueOptions($em->getRepository('Application\Entity\Category')
                    ->getChildsAsArray($cat->getParent()->getId()));
                
                $form->get('categories')
                    ->get('root_categories')
                    ->setAttribute('value', $cat->getParent()
                    ->getId());
                $form->get('categories')
                    ->get('subcategories')
                    ->setAttribute('value', $cat->getId());
            } else {
                $form->get('categories')
                    ->get('root_categories')
                    ->setAttribute('value', $cat->getId());
            }
            // custom fields
            $form->add(new CustomFieldset($this->entityManager, $this->customfieldservice, $cat->getId()));
        }
        if (! $zonefilters) { // si aucun filtre => cas d'un nouvel evt
            if ($this->zfcUserAuthentication()->hasIdentity()) {
                $org = $this->zfcUserAuthentication()
                    ->getIdentity()
                    ->getOrganisation();
                $form->get('organisation')->setValue($org->getId());
                $zonefilters = $em->getRepository('Application\Entity\QualificationZone')->getAllAsArray($org);
            } else {
                // aucun utilisateur connecté ??? --> possible si deconnexion déans l'intervalle
                throw new UnauthorizedException('Aucun utilisateur connecté.');
            }
        }
        
        if (! $zonefilters || count($zonefilters) == 0) { // pas de zone => cacher l'élément
            $form->remove('zonefilters');
        } else {
            $form->get('zonefilters')->setValueOptions($zonefilters);
        }
        
        if ($id && $pevent) { // copie d'un modèle
                            // prefill customfields with predefined values
            foreach ($em->getRepository('Application\Entity\CustomField')->findBy(array(
                'category' => $cat->getId()
            )) as $customfield) {
                $customfieldvalue = $em->getRepository('Application\Entity\CustomFieldValue')->findOneBy(array(
                    'event' => $pevent->getId(),
                    'customfield' => $customfield->getId()
                ));
                if ($customfieldvalue) {
                    $form->get('custom_fields')
                        ->get($customfield->getId())
                        ->setAttribute('value', $customfieldvalue->getValue());
                }
            }
            if ($pevent->isProgrammed()) {
                $form->get('scheduled')->setValue(true);
            }
            if ($pevent->isPunctual()) {
                $form->get('punctual')->setValue(true);
            }
        }
        
        if (! $id || ($id && $copy) || ($id && $pevent)) { // nouvel évènement
            if ($this->isGranted('events.confirme')) {
                // utilisateur opérationnel => statut confirmé dès le départ
                $form->get('status')->setAttribute('value', 2);
            } else {
                $form->get('status')->setAttribute('value', 1);
            }
        }
        
        if ($id && $event) { // modification d'un evt, prefill form
                           
            // custom fields values
            foreach ($em->getRepository('Application\Entity\CustomField')->findBy(array(
                'category' => $cat->getId()
            )) as $customfield) {
                $customfieldvalue = $em->getRepository('Application\Entity\CustomFieldValue')->findOneBy(array(
                    'event' => $event->getId(),
                    'customfield' => $customfield->getId()
                ));
                if ($customfieldvalue) {
                    if ($customfield->isMultiple()) {
                        $values = explode("\r", $customfieldvalue->getValue());
                        $form->get('custom_fields')
                            ->get($customfield->getId())
                            ->setValue($values);
                    } else {
                        $form->get('custom_fields')
                            ->get($customfield->getId())
                            ->setAttribute('value', $customfieldvalue->getValue());
                    }
                }
            }
            // other values
            $form->bind($event);
            $form->setData($event->getArrayCopy());
            if ($copy) {
                $form->get('id')->setValue('');
                $form->get('startdate')->setValue('');
                $form->get('enddate')->setValue('');
                $form->get('status')->setValue('');
                $viewmodel->setVariables(array(
                    'event' => $event,
                    'copy' => $id
                ));
            } else {
                $viewmodel->setVariables(array(
                    'event' => $event
                ));
            }
        }
        
        $viewmodel->setVariables(array(
            'form' => $form
        ));
        return $viewmodel;
    }

    private function getSkeletonForm($cats, $event = null)
    {
        $em = $this->getEntityManager();
        
        if (! $event) {
            $event = new Event();
        }
        
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($event);
        $form->setHydrator(new DoctrineObject($em))->setObject($event);

        $status = $em->getRepository('Application\Entity\Status')
            ->getAllAsArray();
        if(!$this->isGranted('events.delete')) {
            unset($status[5]);
        }

        $form->get('status')->setValueOptions($status);
        
        $form->get('impact')->setValueOptions($em->getRepository('Application\Entity\Impact')
            ->getAllAsArray());
        
        $form->get('organisation')->setValueOptions($em->getRepository('Application\Entity\Organisation')
            ->getAllAsArray());
        
        // add default fieldsets
        $rootCategories = array();
        $tempRootCategories = array();
        foreach($cats as $catid){
            $cat = $em->getRepository(Category::class)->find($catid);
            if($cat) {
                if($cat->getParent() === null) {
                    if (!in_array($cat, $tempRootCategories)) {
                        $tempRootCategories[] = $cat;
                    }
                } else {
                    if (!in_array($cat->getParent(), $tempRootCategories)) {
                        $tempRootCategories[] = $cat->getParent();
                    }
                }
            }
        }
        uasort($tempRootCategories, function ($a, $b) {
            return ($a->getPlace() < $b->getPlace()) ? -1 : 1;
        });
        $rootCategories = $this->filterReadableCategories($tempRootCategories);
        /*
        $tab = $em->getRepository('Application\Entity\Tab')->find($tabid);
        if ($tab) {
            $cats = $tab->getCategories()->filter(function ($a) {
                return $a->getParent() === null;
            });
            $rootCategories = $this->filterReadableCategories($cats);
        }
        */
        $rootarray = array();
        foreach ($rootCategories as $cat) {
            $rootarray[$cat->getId()] = $cat->getName();
        }
        $form->add(new CategoryFormFieldset($rootarray));
        
        $form->bind($event);
        $form->setData($event->getArrayCopy());
        
        // replace default category element
        $form->remove('category');
        $category = new Element\Hidden('category');
        $form->add($category);
        
        $form->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Ajouter',
                'class' => 'btn btn-primary'
            )
        ));
        
        return $form;
    }

    public function getpredefinedvaluesAction()
    {
        $predefinedId = $this->params()->fromQuery('id', null);
        $json = array();
        $defaultvalues = array();
        $customvalues = array();
        
        $objectManager = $this->getEntityManager();

        $predefinedEvt = $objectManager->getRepository('Application\Entity\PredefinedEvent')->find($predefinedId);
        
        $defaultvalues['punctual'] = $predefinedEvt->isPunctual();
        
        $defaultvalues['impact'] = $predefinedEvt->getImpact()->getId();
        
        $defaultvalues['programmed'] = $predefinedEvt->isProgrammed();

        $defaultvalues['duration'] = $predefinedEvt->getDuration();
        
        foreach ($predefinedEvt->getZonefilters() as $filter) {
            $defaultvalues['zonefilters'][] = $filter->getId();
        }
        
        $json['defaultvalues'] = $defaultvalues;
        
        foreach ($predefinedEvt->getCustomFieldsValues() as $customfieldvalue) {
            $value = $customfieldvalue->getValue();
            if(strpos($value, "\r") > 0) {
                $value = explode("\r", $value);
            }

            $customvalues[$customfieldvalue->getCustomField()->getId()] = $value;
        }
        
        $json['customvalues'] = $customvalues;
        
        return new JsonModel($json);
    }

    public function getactionsAction()
    {
        $parentId = $this->params()->fromQuery('id', null);
        $json = array();
        $objectManager = $this->getEntityManager();
        
        foreach ($objectManager->getRepository('Application\Entity\PredefinedEvent')->findBy(array(
            'parent' => $parentId
        ), array(
            'place' => 'DESC'
        )) as $action) {
            if ($action->getCategory() instanceof \Application\Entity\ActionCategory) {
                $color = $action->getCustomFieldValue($action->getCategory()
                    ->getColorfield());
                $json[$action->getId()] = array(
                    'id' => $action->getId(),
                    'name' => $this->eventservice->getName($action),
                    'place' => $action->getPlace(),
                    'color' => ($color !== null ? $color->getValue() : ''),
                    'impactname' => $action->getImpact()->getName(),
                    'impactstyle' => $action->getImpact()->getStyle()
                );
            }
        }
        
        return new JsonModel($json);
    }

    public function actionsAction()
    {
        $viewmodel = new ViewModel();
        $parentId = $this->params()->fromQuery('id', null);
        $compact = $this->params()->fromQuery('compact', false);
        // disable layout if request by Ajax
        $viewmodel->setTerminal($this->getRequest()->isXmlHttpRequest());
        
        $viewmodel->setVariable('actions', $this->getActions($parentId));
        $viewmodel->setVariable('compact', $compact);
        return $viewmodel;
    }

    public function actionsStatusAction() {
        $json = array();
        $parentId = $this->params()->fromQuery('id', null);
        $actions = $this->getActions($parentId);
        foreach ($actions as $action) {
            $json[$action->getId()] = $action->getStatus()->isOpen();
        }
        return new JsonModel($json);
    }
    
    private function getActions($eventid)
    {
        $objectManager = $this->getEntityManager();
        
        $qb = $objectManager->createQueryBuilder();
        $qb->select(array(
            'e',
            'cat'
        ))
            ->from('Application\Entity\AbstractEvent', 'e')
            ->innerJoin('e.category', 'cat')
            ->andWhere('cat INSTANCE OF Application\Entity\ActionCategory')
            ->andWhere($qb->expr()
            ->eq('e.parent', $eventid))
            ->orderBy("e.place", 'ASC');
        
        return $qb->getQuery()->getResult();
    }
    
    /*
     * Fichiers liés à un évènement, au format JSON
     */
    public function getfilesAction()
    {
        $eventid = $this->params()->fromQuery('id', null);
        $json = array();
        $objectManager = $this->getEntityManager();
        foreach ($objectManager->getRepository('Application\Entity\PredefinedEvent')
            ->find($eventid)
            ->getFiles() as $file) {
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
    public function getalarmsAction()
    {
        $eventid = $this->params()->fromQuery('id', null);
        $json = array();
        $objectManager = $this->getEntityManager();
        foreach ($objectManager->getRepository('Application\Entity\PredefinedEvent')->findBy(array(
            'parent' => $eventid
        )) as $alarm) {
            if ($alarm->getCategory() instanceof \Application\Entity\AlarmCategory) {
                $alarmjson = array();
                foreach ($alarm->getCustomFieldsValues() as $value) {
                    if ($value->getCustomField()->getId() == $alarm->getCategory()
                        ->getFieldname()
                        ->getId()) {
                        $alarmjson['name'] = $value->getValue();
                    } elseif ($value->getCustomField()->getId() == $alarm->getCategory()
                        ->getTextField()
                        ->getId()) {
                        $alarmjson['comment'] = $value->getValue();
                    } elseif ($value->getCustomField()->getId() == $alarm->getCategory()
                        ->getDeltaBeginField()
                        ->getId()) {
                        $alarmjson['deltabegin'] = preg_replace('/\s+/', '', $value->getValue());
                    } elseif ($value->getCustomField()->getId() == $alarm->getCategory()
                        ->getDeltaEndField()
                        ->getId()) {
                        $alarmjson['deltaend'] = preg_replace('/\s+/', '', $value->getValue());
                    }
                }
                $json[] = $alarmjson;
            }
        }
        return new JsonModel($json);
    }

    /**
     * Return {'open' => '<true or false>'}
     * 
     * @return \Laminas\View\Model\JsonModel
     */
    public function toggleficheAction()
    {
        $evtId = $this->params()->fromQuery('id', null);
        $json = array();
        $objectManager = $this->getEntityManager();
        
        $event = $objectManager->getRepository('Application\Entity\Event')->find($evtId);
        
        if ($event) {
            $event->setStatus($objectManager->getRepository('Application\Entity\Status')
                ->findOneBy(array(
                'defaut' => true,
                'open' => ! $event->getStatus()
                    ->isOpen()
            )));
            $objectManager->persist($event);
            $objectManager->flush();
        }
        
        $json['open'] = $event->getStatus()->isOpen();
        
        return new JsonModel($json);
    }

    public function deletefileAction()
    {
        $objectManager = $this->getEntityManager();
        
        $fileid = $this->params()->fromQuery('id', null);
        $eventid = $this->params()->fromQuery('eventid', null);
        $exclude = $this->params()->fromQuery('exclude', false);
        $exclude = $exclude == "true";
        $messages = array();
        
        if ($fileid) {
            $file = $objectManager->getRepository('Application\Entity\File')->find($fileid);
            if ($eventid && $file) {
                $event = $objectManager->getRepository('Application\Entity\Event')->find($eventid);
                if ($event) {
                    if($event->getRecurrence() && !$exclude) {
                        //suppression du fichier à tous les évènements de la récurrence
                        //sauf ceux antérieurs à la date actuelle
                        $recurrentEvents = $event->getRecurrence()->getEvents();
                        $now = new \DateTime('now');
                        $now->setTimezone(new \DateTimeZone('UTC'));
                        foreach ($recurrentEvents as $e) {
                            if (!$e->isPast($now)) {
                                $file->removeEvent($e);
                            }
                        }
                    } else { //suppression du fichier uniquement pour l'évènement indiqué
                        $file->removeEvent($event);
                    }
                    $objectManager->persist($file);
                } else {
                    $messages['error'][] = "Impossible d'enlever le fichier de l'évènement.";
                }
            } else {
                if ($file) {
                    if(count($file->getEvents()) == 0) {
                        $objectManager->remove($file);
                        $messages['success'][] = "Fichier correctement retiré.";
                    } 
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
     * Retourne le nombre d'évènements courants ou à venir dans l'heure pour les onglets
     */
    public function getNumberEventsTabAction()
    {
        $em = $this->getEntityManager();
        $json = array();
        foreach ($em->getRepository('Application\Entity\Tab')->findAll() as $tab) {
            $events = $em->getRepository('Application\Entity\Event')->getTabEvents($tab, $this->zfcUserAuthentication());
            $json[$tab->getId()] = count($events);
        }
        $json['radar'] = count($em->getRepository('Application\Entity\Event')->getRadarEvents());
        $json['radio'] = count($em->getRepository('Application\Entity\Event')->getRadioEvents());
        $json['afis'] = count($em->getRepository('Application\Entity\Event')->getAfisEvents());
        $json['flightplans'] = count($em->getRepository('Application\Entity\Event')->getFlightPlanEvents(null,null,[1,2]));
        $json['sarbeacons'] = count($em->getRepository('Application\Entity\Event')->getCurrentIntPlanEvents());

        return new JsonModel($json);
    }

    /**
     * {'evt_id_0' => {
     * 'name' => evt_name,
     * 'modifiable' => boolean,
     * 'start_date' => evt_start_date,
     * 'end_date' => evt_end_date,
     * 'punctual' => boolean,
     * 'category' => evt_category_name,
     * 'category_short' => evt_category_short_name,
     * 'status_name' => evt_status_name,
     * 'actions' => {
     * 'action_name0' => open? (boolean),
     * 'action_name1' => open? (boolean),
     * ...
     * }
     * },
     * 'evt_id_1' => ...
     * }
     * 
     * @return \Laminas\View\Model\JsonModel
     */
    public function geteventsAction()
    {
        $lastmodified = $this->params()->fromQuery('lastupdate', null);
        
        $day = $this->params()->fromQuery('day', null);
        
        $cats = $this->params()->fromQuery('cats', null);

        $default = $this->params()->fromQuery('default', null);

        $extendedFormat = $this->params()->fromQuery('ext', false);

        $json = array();
        
        $objectManager = $this->getEntityManager();
        
        foreach ($objectManager->getRepository('Application\Entity\Event')->getEvents(
            $this->zfcUserAuthentication(), 
            $day,
            null,
            $lastmodified, 
            true, 
            $cats,
            null,
            $default
        ) as $event) {
            $json[$event->getId()] = $this->eventservice->getJSON($event, $extendedFormat);
        }
        
        if (count($json) === 0) {
            $this->getResponse()->setStatusCode(304);
            return new JsonModel();
        }
        
        $this->getResponse()
            ->getHeaders()
            ->addHeaderLine('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT');
        
        return new JsonModel($json);
    }

    private function hex2rgb($hex) {
        $hex = str_replace("#", "", $hex);

        if(strlen($hex) == 3) {
            $r = hexdec(substr($hex,0,1).substr($hex,0,1));
            $g = hexdec(substr($hex,1,1).substr($hex,1,1));
            $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
        }
        $rgb = array($r, $g, $b);
        return $rgb;
    }


    public function geteventsFCAction() {
        $start = $this->params()->fromQuery('start', null);
        $end = $this->params()->fromQuery('end', null);
        $cats = $this->params()->fromQuery('cats', null);
        $rootcolor = $this->params()->fromQuery('rootcolor', true);
        $lastmodified = $this->params()->fromQuery('lastupdate', null);

        $om = $this->getEntityManager();

        $events = array();

        foreach ($om->getRepository('Application\Entity\Event')->getEvents(
            $this->zfcUserAuthentication(),
            $start,
            $end,
            $lastmodified,
            true,
            $cats
        ) as $event) {
            $e = $this->eventservice->getJSON($event);
            $e['title'] = $this->eventservice->getName($event);
            $e['start'] = $event->getStartdate()->format(DATE_ISO8601);
            if($event->isPunctual()) {
                $e['end'] = $event->getStartdate()->format(DATE_ISO8601);
            }
            if($event->getEnddate()) {
                $e['end'] = $event->getEnddate()->format(DATE_ISO8601);
            } else {
                if($event->isPunctual()) {
                    $tempend = clone $event->getStartdate();
                    $tempend->add(new \DateInterval('PT1M'));
                    $e['end'] = $tempend->format(DATE_ISO8601);
                } else {
                    $tempEnd = new \DateTime($end);
                    $tempEnd->add(new \DateInterval('P2D'));
                    $e['end'] = $tempEnd->format(DATE_ISO8601);
                }
            }
            $e['color'] = (($rootcolor && $event->getCategory()->getParent() !== null) ? $event->getCategory()->getParent()->getColor() : $event->getCategory()->getColor() );

            $rgb = $this->hex2rgb($e['color']);
            $yiq = 1 - ($rgb[0]*0.299 + $rgb[1]*0.587 + $rgb[2]*0.114)/255;
            $e['textColor'] = ($yiq >= 0.5 ? '#fff' : '#000' );
            $events[] = $e;
        }

        if (count($events) === 0) {
            $this->getResponse()->setStatusCode(304);
            return new JsonModel();
        }

        $this->getResponse()
            ->getHeaders()
            ->addHeaderLine('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT');

        return new JsonModel($events);
    }

    private function getModelJson(PredefinedEvent $model)
    {
        $json = array(
            'name' => $model->getName(),
            'category_root' => ($model->getCategory()->getParent() ? $model->getCategory()
                ->getParent()
                ->getName() : $model->getCategory()->getName()),
            'category' => $model->getCategory()->getName(),
            'duration' => $model->getDuration()
        );
        $fields = array();
        foreach ($model->getCustomFieldsValues() as $value) {
            $fields[$value->getCustomField()->getName()] = $this->customfieldservice->getFormattedValue($value->getCustomField(), $value->getValue());
        }
        $json['fields'] = $fields;
        return $json;
    }

    public function getRecurrHumanReadableAction() {
        $json = array();
        $pattern = $this->params()->fromQuery('pattern', null);
        $start = $this->params()->fromQuery('start', null);
        $text = '';
        if($pattern !== null) {
            $text = Recurrence::getHumanReadableFromPattern($start, $pattern);
        }
        $json['text'] = $text;
        return new JsonModel($json);
    }
    
    /**
     * Liste des catégories visibles d'un onglet
     * Au format JSON
     */
    public function getcategoriesAction()
    {
        
        $now = new \DateTime('now');
        $now->setTimezone(new \DateTimeZone('UTC'));
        $now->setTime(0,0,0);
        $day = $this->params()->fromQuery('day', null);
        if($day) {
            $day = new \DateTime($day);
        } else {
            $day = $now;
        }
        $objectManager = $this->getEntityManager();
        $qb = $objectManager->createQueryBuilder();
        $qb->select('c')
            ->from('Application\Entity\Category', 'c')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('c.archived', 0),
                    $qb->expr()->andX(
                        $qb->expr()->eq('c.archived',true),
                        $qb->expr()->gt('c.archiveDate', '?1')
                    )
                )
            )
        ->setParameter(1, $day->format('Y-m-d H:i:s'));
        
        $rootonly = $this->params()->fromQuery('rootonly', true);
        $cats = $this->params()->fromQuery('cats', null);
        if ($cats) {
            $categories = $objectManager->getRepository('Application\Entity\Category')->findBy(array(
                'id' => $cats
            ));
            if ($categories && count($categories) > 0) {
                $orCat = $qb->expr()->orX();
                foreach ($categories as $category) {
                    $orCat->add($qb->expr()
                        ->eq('c.id', $category->getId()));
                }
                $qb->andWhere($orCat);
            }
        }
        $json = array();
        if ($rootonly == true) {
            $qb->andWhere($qb->expr()
                ->isNull('c.parent'));
        }
        
        $qb->orderBy("c.place", 'ASC');
        $categories = $qb->getQuery()->getResult();
        $readablecat = $this->filterReadableCategories($categories);
        foreach ($readablecat as $category) {
            $json[$category->getId()] = array(
                'id' => $category->getId(),
                'name' => $category->getName(),
                'short_name' => $category->getShortName(),
                'color' => $category->getColor(),
                'compact' => $category->isCompactMode(),
                'place' => $category->getPlace(),
                'parent_id' => ($category->getParent() ? $category->getParent()->getId() : - 1),
                'parent_place' => ($category->getParent() ? $category->getParent()->getPlace() : - 1)
            );
        }
        
        return new JsonModel($json);
    }

    /**
     * Return models to display when a category label is hovered
     */
    public function getQuickModelsAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $json = array();
        if($id) {
            $om = $this->getEntityManager();
            $cat = $om->getRepository(Category::class)->find($id);
            if($cat) {
                return new JsonModel($om->getRepository(PredefinedEvent::class)->getQuickAccessModelsFromCategoryAsArray($cat));
            }
        }
        return new JsonModel($json);
    }

    private function filterReadableCategories($categories)
    {
        $objectManager = $this->getEntityManager();
        $readablecat = array();
        foreach ($categories as $category) {
            if ($this->zfcUserAuthentication()->hasIdentity()) {
                $roles = $this->zfcUserAuthentication()
                    ->getIdentity()
                    ->getRoles();
                foreach ($roles as $role) {
                    if ($category->getReadroles(true)->contains($role)) {
                        $readablecat[] = $category;
                        break;
                    }
                }
            } else {
                $role = $this->zfcRbacOptions->getGuestRole();
                $roleentity = $objectManager->getRepository('Core\Entity\Role')->findOneBy(array(
                    'name' => $role
                ));
                if ($roleentity) {
                    if ($category->getReadroles(true)->contains($roleentity)) {
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
    public function getimpactsAction()
    {
        $objectManager = $this->getEntityManager();
        $json = array();
        $impacts = $objectManager->getRepository('Application\Entity\Impact')->findAll();
        foreach ($impacts as $impact) {
            $json[$impact->getId()] = array(
                'name' => $impact->getName(),
                'style' => $impact->getStyle(),
                'value' => $impact->getValue()
            );
        }
        return new JsonModel($json);
    }

    public function gethistoryAction()
    {
        $viewmodel = new ViewModel();
        $request = $this->getRequest();
        
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $evtId = $this->params()->fromQuery('id', null);
        
        $objectManager = $this->getEntityManager();
        
        $event = $objectManager->getRepository('Application\Entity\Event')->find($evtId);
        
        $history = null;
        if ($event) {
            $history = $this->eventservice->getHistory($event);
        }
        
        $viewmodel->setVariable('history', $history);
        
        return $viewmodel;
    }

    /**
     * Usage :
     * $this->url('application', array('controller' => 'events'))+'/changefield?id=<id>&field=<field>&value=<newvalue>'
     * 
     * @return JSon with messages
     */
    public function changefieldAction()
    {
        $formatter = \IntlDateFormatter::create(\Locale::getDefault(), \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'UTC', \IntlDateFormatter::GREGORIAN, 'dd LLL, HH:mm');
        
        $id = $this->params()->fromQuery('id', 0);
        $field = $this->params()->fromQuery('field', 0);
        $value = $this->params()->fromQuery('value', 0);
        $messages = array();
        $event = null;
        if ($id) {
            $objectManager = $this->getEntityManager();
            $event = $objectManager->getRepository('Application\Entity\Event')->find($id);
            if ($event) {
                // modification autorisée à l'auteur ou aux utilisateurs disposant des droits en écriture
                if ($this->zfcUserAuthentication()->hasIdentity() && ($event->getAuthor()->getId() == $this->zfcUserAuthentication()
                    ->getIdentity()
                    ->getId() || $this->isGranted('events.write'))) {
                    switch ($field) {
                        case 'enddate':
                            $this->changeEndDate($event, new \DateTime($value), $messages);
                            break;
                        case 'startdate':
                            $this->changeStartDate($event, new \DateTime($value), $messages);
                            break;
                        case 'impact':
                            $impact = $objectManager->getRepository('Application\Entity\Impact')->findOneBy(array(
                                'value' => $value
                            ));
                            if ($impact) {
                                $event->setImpact($impact);
                                $objectManager->persist($event);
                                $messages['success'][] = "Impact modifié.";
                            }
                            break;
                        case "status":
                            $status = $objectManager->getRepository('Application\Entity\Status')->find($value);
                            // si statut terminé et (pas d'heure de fin + pas ponctuel) -> heure de fin = now
                            if (! $status->isOpen() && $status->isDefault() && ! $event->getEnddate() && ! $event->isPunctual()) {
                                $now = new \DateTime('now');
                                $now->setTimezone(new \DateTimeZone('UTC'));
                                if ($event->setEnddate($now)) {
                                    $event->setStatus($status);
                                    $messages['success'][] = "Date et heure de fin modifiée au " . $formatter->format($event->getEnddate());
                                    $messages['success'][] = "Evènement passé au statut " . $status->getName();
                                } else {
                                    $messages['error'][] = "Impossible de changer le statut sans heure de fin";
                                }
                                // on ferme l'evt proprement
                                if (! $status->isOpen()) {
                                    $this->closeEvent($event);
                                }
                            } else 
                                if (! $status->isOpen() && ! $status->isDefault()) {
                                    // si statut annulé
                                    $event->cancelEvent($status);
                                    $messages['success'][] = "Evènement passé au statut " . $status->getName();
                                } else {
                                    $event->setStatus($status);
                                    $messages['success'][] = "Evènement passé au statut " . $status->getName();
                                }
                            $objectManager->persist($event);
                            break;
                        case 'star':
                            $event->setStar($value);
                            $objectManager->persist($event);
                            if ($value) {
                                $messages['success'][] = "Evènement marqué important.";
                            } else {
                                $messages['success'][] = "Evènement marqué non important.";
                            }
                            break;
                        default:
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
        $json['event'] = array(
            $event->getId() => $this->eventservice->getJSON($event)
        );
        $json['messages'] = $messages;
        return new JsonModel($json);
    }

    /**
     * Change the status of an event to "Supprimé"
     * If event has no end date, sets end date = start date + 1h
     * @return JsonModel
     */
    public function deleteeventAction()
    {
        $id = $this->params()->fromQuery('id', 0);
        $messages = array();
        $json = array();
        if($this->isGranted('events.delete')) {
            if ($id) {
                $objectManager = $this->getEntityManager();
                $event = $objectManager->getRepository('Application\Entity\Event')->find($id);
                if ($event) {
                    $deleteStatus = $objectManager->getRepository('Application\Entity\Status')->find(5);
                    $event->setStatus($deleteStatus);
                    if(!$event->isPunctual() && $event->getEnddate() == null) {
                        $enddate = clone $event->getStartdate();
                        $enddate->add(new \DateInterval('PT1H'));
                        $event->setEnddate($enddate);
                    }
                    $this->closeEvent($event);
                    try {
                        $objectManager->persist($event);
                        $objectManager->flush();
                        $messages['success'][] = "Évènement correctement supprimé";
                        $json['event'] = array(
                            $event->getId() => $this->eventservice->getJSON($event)
                        );
                    } catch (\Exception $e) {
                        $messages['error'][] = $e->getMessage();
                    }
                } else {
                    $messages['error'][] = "Suppression d'évènement impossible : évènement non trouvé.";
                }
            } else {
                $messages['error'][] = "Suppression d'évènement impossible : ID non valide.";
            }
        } else {
            $messages['error'][] = "Suppression impossible : droits insuffisants";
        }
        $json['messages'] = $messages;
        return new JsonModel($json);
    }

    /**
     * Send an event by email to the corresponding IPO
     */
    public function sendEventAction()
    {
        $id = $this->params()->fromQuery('id', 0);
        $messages = array();
        if ($id) {
            $objectManager = $this->getEntityManager();
            $event = $objectManager->getRepository('Application\Entity\Event')->find($id);
            $formatter = \IntlDateFormatter::create(\Locale::getDefault(), \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'UTC', \IntlDateFormatter::GREGORIAN, 'dd LLL, HH:mm');
            if ($event) {
                $content = 'Nom : ' . $this->eventservice->getName($event) . '<br />';
                $content .= 'Début : ' . $formatter->format($event->getStartdate()) . '<br />';
                $content .= 'Fin : ' . ($event->getEnddate() ? $formatter->format($event->getEnddate()) : 'Inconnu') . '<br />';
                foreach ($event->getCustomFieldsValues() as $value) {
                    $content .= $value->getCustomField()->getName() . ' : ' . $this->customfieldservice->getFormattedValue($value->getCustomField(), $value->getValue()) . '<br />';
                }
                foreach ($event->getUpdates() as $update){
                    $content .= $this->eventservice->getUpdateAuthor($update).' le '.$formatter->format($update->getCreatedOn()) . ' : <br />';
                    $content .= nl2br($update->getText()).'<br />';
                }
                $text = new \Laminas\Mime\Part($content);
                $text->type = \Laminas\Mime\Mime::TYPE_HTML;
                $text->charset = 'utf-8';
                
                $mimeMessage = new \Laminas\Mime\Message();
                $mimeMessage->setParts(array(
                    $text
                ));
                
                if (! array_key_exists('emailfrom', $this->config) || ! array_key_exists('smtp', $this->config)) {
                    $messages['error'][] = "Envoi d'email non configuré, contactez votre administrateur.";
                } else {
                    $message = new \Laminas\Mail\Message();
                    $message->addTo($event->getOrganisation()
                        ->getIpoEmail())
                        ->addFrom($this->config['emailfrom'])
                        ->setSubject("Envoi d'un évènement par le CDS : " . $this->eventservice->getName($event))
                        ->setBody($mimeMessage);
                    
                    $transport = new \Laminas\Mail\Transport\Smtp();
                    $transportOptions = new \Laminas\Mail\Transport\SmtpOptions($this->config['smtp']);
                    $transport->setOptions($transportOptions);
                    try {
                        $transport->send($message);
                        $update = new EventUpdate();
                        $update->setHidden(true);
                        $update->setEvent($event);
                        $update->setText("Evènement envoyé par email à " . $event->getOrganisation()->getIpoEmail());
                        $this->getEntityManager()->persist($update);
                        $this->getEntityManager()->flush();
                        $messages['success'][] = "Evènement correctement envoyé à " . $event->getOrganisation()->getIpoEmail();
                    } catch (\Exception $e) {
                        $messages['error'][] = $e->getMessage();
                    }
                }
            } else {
                $messages['error'][] = "Envoi d'email impossible : évènement non trouvé.";
            }
        } else {
            $messages['error'][] = "Envoi d'email impossible : évènement non trouvé.";
        }
        
        $json = array();
        
        $json['messages'] = $messages;
        return new JsonModel($json);
    }

    /**
     *
     * @param \Application\Entity\Event $event            
     * @param \DateTime $enddate            
     * @param type $messages            
     */
    private function changeEndDate(Event $event, $enddate, &$messages = null)
    {
        $objectManager = $this->getEntityManager();
        $formatter = \IntlDateFormatter::create(\Locale::getDefault(), \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'UTC', \IntlDateFormatter::GREGORIAN, 'dd LLL, HH:mm');
        if ($event->setEnddate($enddate)) {
            if ($enddate) {
                if (is_array($messages)) {
                    $messages['success'][] = "Date et heure de fin modifiées au " . $formatter->format($event->getEnddate());
                }
            } else {
                if (is_array($messages)) {
                    $messages['success'][] = "Date et heure de fin supprimées.";
                }
            }
            $now = new \DateTime('now');
            $now->setTimezone(new \DateTimeZone('UTC'));
            
            foreach ($event->getChildren() as $child) {
                if ($child->getCategory() instanceof FrequencyCategory) {
                    $child->setEnddate($enddate);
                    $objectManager->persist($child);
                }
            }
            
            $event->updateAlarms();
            
            // passage au statut terminé si
            // - evt confirmé ou (evt nouveau et heure de début passée)
            // et
            // - heure de fin proche de l'heure de début (15min)
            if ($this->isGranted('events.confirme') && $event->getEnddate()) {
                $status = $objectManager->getRepository('Application\Entity\Status')->findOneBy(array(
                    'open' => false,
                    'defaut' => true
                ));
                if (($event->getStatus()->getId() == 2 || ($event->getStatus()->getId() <= 2 && $event->getStartDate() < $now)) && (($event->getEndDate()->format('U') - $now->format('U')) / 60) < 15) {
                    $event->setStatus($status);
                    // on ferme l'evt proprement
                    $this->closeEvent($event);
                    if (is_array($messages)) {
                        $messages['success'][] = "Evènement passé au statut : \"Fin confirmée\".";
                    }
                }
            }
            
            $objectManager->persist($event);
        } else {
            if (is_array($messages)) {
                $messages['error'][] = "Impossible de changer la date de fin.";
            }
        }
    }

    /**
     * Change la date de début d'un evt et
     * - vérifie la cohérence des évènements fils
     * - vérifie la cohérence du statut
     * 
     * @param \Application\Entity\Event $event            
     * @param \DateTime $startdate            
     * @param
     *            array Messages
     * @return true Si tout s'est bien passé
     */
    private function changeStartDate(Event $event, \DateTime $startdate, &$messages = null)
    {
        $objectManager = $this->getEntityManager();
        $formatter = \IntlDateFormatter::create(
            \Locale::getDefault(), 
            \IntlDateFormatter::FULL, 
            \IntlDateFormatter::FULL, 
            'UTC', 
            \IntlDateFormatter::GREGORIAN, 
            'dd LLL, HH:mm'
        );
        if ($event->setStartdate($startdate)) {
            if (is_array($messages)) {
                $messages['success'][] = "Date et heure de début modifiées au " . $formatter->format($event->getStartdate());
            }
            // passage au statut confirmé si pertinent, droits ok et heure de début proche de l'heure actuelle
            if ($this->isGranted('events.confirme')) {
                $now = new \DateTime('now');
                $now->setTimezone(new \DateTimeZone('UTC'));
                $status = $objectManager->getRepository('Application\Entity\Status')->findOneBy(array(
                    'open' => true,
                    'defaut' => false
                ));
                if ($event->getStatus()->getId() == 1 && (($event->getStartDate()->format('U') - $now->format('U')) / 60) < 15) {
                    $event->setStatus($status);
                    if (is_array($messages)) {
                        $messages['success'][] = "Evènement passé au statut : confirmé.";
                    }
                }
            }
            // changement de l'heure de début des évènements fils si pertinent
            foreach ($event->getChildren() as $child) {
                if ($child->getCategory() instanceof FrequencyCategory) {
                    $child->setStartdate($startdate);
                    $objectManager->persist($child);
                }
            }
            $event->updateAlarms();
            $objectManager->persist($event);
        } else {
            if (is_array($messages)) {
                $messages['error'][] = "Impossible de changer l'heure de début.";
            }
        }
    }

    /**
     * Cloture d'un evt : terminé ou annulé (statut 3 ou 4)
     * TODO : use $event->close or $event->cancel
     * 
     * @param Event $event            
     */
    private function closeEvent(Event $event)
    {
        $objectManager = $this->getEntityManager();
        foreach ($event->getChildren() as $child) {
            if ($child->getCategory() instanceof FrequencyCategory) {
                // on termine les évènements fils de type fréquence
                if ($event->getStatus()->getId() == 3) {
                    // date de fin uniquement pour les fermetures
                    $child->setEnddate($event->getEnddate());
                }
                $child->setStatus($event->getStatus());
            } else 
                if ($child->getCategory() instanceof AlarmCategory) {
                    // si evt annulé uniquement : on annule toutes les alarmes
                    if ($event->getStatus()->getId() == 4 || $event->getStatus()->getId() == 5) {
                        $child->setStatus($event->getStatus());
                    }
                }
            $objectManager->persist($child);
        }
    }

    public function getficheAction()
    {
        $viewmodel = new ViewModel();
        $request = $this->getRequest();
        
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $id = $this->params()->fromQuery('id', null);
        
        $objectManager = $this->getEntityManager();
        
        $event = $objectManager->getRepository('Application\Entity\Event')->find($id);
        
        $history = null;
        if ($event) {
            $history = $this->eventservice->getHistory($event);
        }
        
        $viewmodel->setVariable('history', $history);
        $viewmodel->setVariable('event', $event);
        $viewmodel->setVariable('actions', $this->getActions($event->getId()));
        
        return $viewmodel;
    }

    public function addnoteAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $em = $this->getEntityManager();
        
        $messages = array();
        
        if ($id && $this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            $event = $em->getRepository('Application\Entity\Event')->find($id);
            if ($event && strlen(trim($post['new-update'])) > 0) {
                $eventupdate = new \Application\Entity\EventUpdate();
                $eventupdate->setText($post['new-update']);
                $eventupdate->setEvent($event);
                $event->setLastModifiedOn();
                $em->persist($eventupdate);
                $em->persist($event);
                try {
                    $em->flush();
                    $messages['success'][] = "Note correctement ajoutée.";
                    $messages['events'] = array(
                        $event->getId() => $this->eventservice->getJSON($event)
                    );
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

    public function savenoteAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $em = $this->getEntityManager();
        
        $messages = array();
        
        if ($id && $this->getRequest()->isPost()) {
            $note = $em->getRepository('Application\Entity\EventUpdate')->find($id);
            $post = $this->getRequest()->getPost();
            if ($note) {
                $note->setText(nl2br($post['note']));
                $em->persist($note);
                $note->getEvent()->setLastModifiedOn();
                $em->persist($note->getEvent());
                try {
                    $em->flush();
                    $messages['success'][] = "Note correctement mise à jour.";
                    $messages['events'] = array(
                        $note->getEvent()->getId() => $this->eventservice->getJSON($note->getEvent())
                    );
                } catch (\Exception $ex) {
                    $messages['error'][] = $ex->getMessage();
                }
            } else {
                $messages['error'][] = "Impossible de mettre à jour la note.";
            }
        }
        return new JsonModel($messages);
    }

    public function updatesAction()
    {
        $id = $this->params()->fromQuery('id', null);
        
        $em = $this->getEntityManager();
        
        $viewmodel = new ViewModel();
        $request = $this->getRequest();
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $event = $em->getRepository('Application\Entity\Event')->find($id);
        
        $viewmodel->setVariable('updates', $event->getUpdates());
        
        return $viewmodel;
    }


    /**
     * Renvoie les heures de relève pour chaque opsup affiché
     */
    public function getshifthoursAction() {
        $json = array();
        $em = $this->getEntityManager();
        if ($this->zfcUserAuthentication()->hasIdentity() && $this->isGranted('events.mod-opsup')) {
            $user =  $this->zfcUserAuthentication()->getIdentity();
            $shifthours = array();
            foreach ($em->getRepository('Application\Entity\ShiftHour')->findAll() as $shifthour) {
                foreach ($shifthour->getOpSupType()->getRoles() as $role) {
                    if($user->hasRole($role->getName())) { //l'utilisateur peut afficher le type d'opsup
                        //on vérifie si il y a une restriction sur la zone
                        if($shifthour->getQualificationZone() !== null) {
                            if ($user->getZone() !== null
                                && $shifthour->getQualificationZone()->getId() == $user->getZone()->getId()) {
                                $shifthours[] = array(
                                        'id' => $shifthour->getId(),
                                        'name' => $shifthour->getOpSupType()->getName(),
                                        'zone' => $shifthour->getQualificationZone()->getName(),
                                        'hour' => $shifthour->getFormattedHourUTC()
                                );
                            }
                        } else {
                            $shifthours[] = array(
                                    'id' => $shifthour->getId(),
                                    'name' => $shifthour->getOpSupType()->getName(),
                                    'zone' => '',
                                    'hour' => $shifthour->getFormattedHourUTC()
                            );
                        }
                        //inutile de vérifier les autres rôles
                        break;
                    }
                }
            }
            $json = $shifthours;
        }
        return new JsonModel($json);
    }
    
    /*
     * Post-it en cours non acquittés.
     * Seuls les postits de l'utilisateur sont envoyés.
     * Si lastupdate contient une date valide, envoit les postits modifiés depuis lastupdate, y compris ceux acquittés
     * Dans tous les cas : nécessite d'être identifié.
     */
    public function getpostitsAction()
    {
        $formatter = \IntlDateFormatter::create(
            \Locale::getDefault(),
            \IntlDateFormatter::FULL,
            \IntlDateFormatter::FULL,
            'UTC',
            \IntlDateFormatter::GREGORIAN,
            'HH:mm'
        );
        $postits = array();
        if ($this->zfcUserAuthentication()->hasIdentity()) {
            
            $user = $this->zfcUserAuthentication()
                ->getIdentity()
                ->getId();
            
            $lastupdate = $this->params()->fromQuery('lastupdate', null);
            
            $userroles = array();
            foreach ($this->zfcUserAuthentication()
                         ->getIdentity()
                         ->getRoles() as $role) {
                $userroles[] = $role->getId();
            }
            $qbEvents = $this->getEntityManager()->createQueryBuilder();
            $qbEvents->select(array(
                'e',
                'cat',
                'roles'
            ))
                ->from('Application\Entity\Event', 'e')
                ->innerJoin('e.category', 'cat')
                ->innerJoin('cat.readroles', 'roles')
                ->andWhere($qbEvents->expr()
                    ->eq('e.author', $user))
                ->andWhere('cat INSTANCE OF Application\Entity\PostItCategory')
                ->andWhere($qbEvents->expr()
                    ->in('e.status', '?2'))
                ->andWhere($qbEvents->expr()
                    ->in('roles.id', '?3'))
                ->andWhere($qbEvents->expr()
                    ->lte('e.startdate', '?1'));
            
            $now = new \DateTime('NOW');
            $now->setTimezone(new \DateTimeZone("UTC"));
    
            $qbEvents
                ->setParameters(array(
                    1 => $now->format('Y-m-d H:i:s'),
                    3 => $userroles
                ));
            
            if ($lastupdate && $lastupdate != 'undefined') {
                $from = new \DateTime($lastupdate);
                $from->setTimezone(new \DateTimeZone("UTC"));
                // uniquement les postits créés et modifiées à partir de lastupdate
                $qbEvents->andWhere($qbEvents->expr()
                    ->gte('e.last_modified_on', '?4'))
                    ->setParameter(4, $from->format('Y-m-d H:i:s'))
                    ->setParameter(2, array( 1, 2, 3, 4, 5));
            } else {
                //lors de l'initialisation, on ne prend que les postits ouverts
                $qbEvents->setParameter(2, array(1,2));
            }
            $result = $qbEvents->getQuery()->getResult();
            foreach ($result as $p) {
                $postit = $this->entityManager->getRepository('Application\Entity\Event')->find($p->getId());
                $namefield = $postit->getCustomFieldValue($postit->getCategory()->getNamefield());
                if($namefield) {
                    $name =  $namefield->getValue();
                } else {
                    $name = "Pas de titre";
                }
                $contentfield = $postit->getCustomFieldValue($postit->getCategory()->getTextfield());
                if($contentfield){
                    $content = $contentfield->getValue();
                } else {
                    $content = "";
                }
                
                $postitjson = array();
                $postitjson['id'] = $postit->getId();
                $postitjson['datetime'] = $postit->getStartdate()->format(DATE_RFC2822);
                if($postit->getStatus()->getId() == 2) {
                    $postitjson['open'] = 1;
                } else {
                    $postitjson['open'] = 0;
                }
                $postitjson['name'] = $name;
                $postitjson['content'] = $content;
                $postits[] = $postitjson;
            }
        }
        if (empty($postits)) {
            $this->getResponse()->setStatusCode(304);
            return new JsonModel();
        }
        $this->getResponse()
            ->getHeaders()
            ->addHeaderLine('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT');
        
        return new JsonModel($postits);
    }
    
    public function addpostitAction(){
        $id = $this->params()->fromQuery('id', null);
        $em = $this->getEntityManager();
        $messages = array();
        if ($this->isGranted('events.create') && $this->zfcUserAuthentication()->hasIdentity()) {
            if ($this->getRequest()->isPost()) {
        
                $post = $this->getRequest()->getPost();
                if ($id) {
                    //update
                    $postit = $em->getRepository(Event::class)->find($id);
                    if($postit) {
                        $name = $postit->getCustomFieldValue($postit->getCategory()->getNamefield());
                        $name->setValue($post['name']);
                        $text = $postit->getCustomFieldValue($postit->getCategory()->getTextfield());
                        $text->setValue($post['content']);
                        $postit->setLastModifiedOn();
                        $em->persist($name);
                        $em->persist($text);
                        $em->persist($postit);
                        try{
                            $em->flush();
                            $messages['success'][] = 'Post-It correctement mis à jour.';
                        } catch(\Exception $e){
                            $messages['error'][] = $e->getMessage();
                        }
                    }
                } else {
                    //new postit
                    $cat = $em->getRepository(PostItCategory::class)->findAll();
                    if (count($cat) !== 1) {
                        $messages['error'][] = "Impossible d'ajouter la note. Contactez votre administrateur : la catégorie système est mal configurée.";
                    } else {
                        $postitCategory = $cat[0];
                        $postit = new Event();
                        $now = new \DateTime();
                        $now->setTimezone(new \DateTimeZone("UTC"));
                        $postit->setStartdate($now);
                        $postit->setCategory($postitCategory);
                        $postit->setPunctual(false);
                        $postit->setAuthor($this->zfcUserAuthentication()->getIdentity());
                        
                        $confirmedStatus = $em->getRepository(Status::class)->find('2');
                        $postit->setStatus($confirmedStatus);
                        $postit->setOrganisation($this->zfcUserAuthentication()->getIdentity()->getOrganisation());
                        $name = new CustomFieldValue();
                        $name->setCustomField($postitCategory->getNamefield());
                        $name->setValue($post['name']);
                        $name->setEvent($postit);
                        $content = new CustomFieldValue();
                        $content->setEvent($postit);
                        $content->setValue($post['content']);
                        $content->setCustomField($postitCategory->getTextfield());
                        $em->persist($name);
                        $em->persist($content);
                        $em->persist($postit);
                
                        try {
                            $em->flush();
                            $messages['success'][] = "Post-It correctement enregistré";
                        } catch (\Exception $e) {
                            $messages['error'][] = $e->getMessage();
                        }
                    }
            
                }
        
            } else {
                $messages['error'][] = "Impossible d'ajouter le post-it.";
            }
        } else {
            $messages['error'][] = "Impossible de créer le post-it : droits insuffisants.";
        }
        return new JsonModel($messages);
    }
    
    public function deletepostitAction(){
        $id = $this->params()->fromQuery('id', null);
        $em = $this->getEntityManager();
        $messages = array();
        if($id) {
            $p = $em->getRepository(Event::class)->find($id);
            $closeStatus = $em->getRepository(Status::class)->find('3');
            if($p) {
                $now = new \DateTime();
                $now->setTimezone(new \DateTimeZone('UTC'));
                $p->close($closeStatus, $now);
                $em->persist($p);
                try{
                    $em->flush();
                    $messages['success'][] = "Post-It correctement supprimé";
                } catch(\Exception $e) {
                    $messages['error'][] = $e->getMessage();
                }
            }
        }
        return new JsonModel($messages);
    }

    /**
     * Add a reference to the Mattermost post
     */
    public function linkEventToPostAction()
    {
        $id = $this->params()->fromQuery('id', null);
        $postid = $this->params()->fromQuery('postid', null);
        $messages = array();
        if($id && $postid) {
            $event = $this->getEntityManager()->getRepository(Event::class)->find($id);
            if($event) {
                $event->setMattermostPostId($postid);
                try {
                    $this->getEntityManager()->persist($event);
                    $this->getEntityManager()->flush();
                } catch (\Exception $e) {
                    $messages['error'][] = $e->getMessage();
                }
            }
        }
        return new JsonModel($messages);
    }
}
