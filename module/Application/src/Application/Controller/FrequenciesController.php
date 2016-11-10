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
 */
namespace Application\Controller;

use Application\Services\CustomFieldService;
use Application\Services\EventService;
use Doctrine\ORM\EntityManager;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Session\Container;
use Zend\Form\Annotation\AnnotationBuilder;
use Doctrine\Common\Collections\Criteria;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Application\Entity\Event;
use Application\Entity\CustomFieldValue;
use Application\Entity\FrequencyCategory;
use Application\Form\CustomFieldset;

/**
 *
 * @author Bruno Spyckerelle
 */
class FrequenciesController extends TabController
{

    private $entityManager;
    private $customfieldservice;
    private $eventservice;

    public function __construct(EntityManager $entityManager,
                                EventService $eventservice,
                                CustomFieldService $customfieldService,
                                $config)
    {
        parent::__construct($config);
        $this->entityManager = $entityManager;
        $this->eventservice = $eventservice;
        $this->customfieldservice = $customfieldService;
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

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select(array(
            's',
            'z'
        ))
            ->from('Application\Entity\SectorGroup', 's')
            ->leftJoin('s.zone', 'z')
            ->andWhere($qb->expr()
            ->eq('s.display', true))
            ->orderBy('s.position', 'ASC');
        
        $session = new Container('zone');
        $zonesession = $session->zoneshortname;
        
        if ($zonesession != null) {
            if ($zonesession != '0') {
                $orga = $this->entityManager->getRepository('Application\Entity\Organisation')->findOneBy(array(
                    'shortname' => $zonesession
                ));
                if ($orga) {
                    $qb->andWhere($qb->expr()
                        ->eq('z.organisation', $orga->getId()));
                } else {
                    $zone = $this->entityManager->getRepository('Application\Entity\QualificationZone')->findOneBy(array(
                        'shortname' => $zonesession
                    ));
                    if ($zone) {
                        $qb->andWhere($qb->expr()
                            ->andX($qb->expr()
                            ->eq('z.organisation', $zone->getOrganisation()
                            ->getId()), $qb->expr()
                            ->eq('s.zone', $zone->getId())));
                    } else {
                        // error
                    }
                }
            }
        }
        if (($zonesession == null || ($zonesession != null && $zonesession == '0')) && $this->zfcUserAuthentication()->hasIdentity()) {
            $orga = $this->zfcUserAuthentication()
                ->getIdentity()
                ->getOrganisation();
            $qb->andWhere($qb->expr()
                ->eq('z.organisation', $orga->getId()));
        }
        
        // pas de session, pas d'utilisateur connecté => tout ?
        
        $groups = $qb->getQuery()->getResult();
        
        $criteria = Criteria::create();
        $criteria->andWhere(Criteria::expr()->isNull('defaultsector'));
        $criteria->andWhere(Criteria::expr()->eq('decommissionned', false));
        $otherfrequencies = $this->entityManager->getRepository('Application\Entity\Frequency')->matching($criteria);
        
        $frequencyMenu = isset($this->config['frequency_test_menu']) ? $this->config['frequency_test_menu'] : false;
        $frequencyColors = $this->config['frequency_tab_colors'];
        $viewmodel->setVariables(array(
            'antennas' => $this->getAntennas(),
            'messages' => $return,
            'groups' => $groups,
            'other' => $otherfrequencies,
            'frequencyTestMenu' => $frequencyMenu,
            'frequencyColors' => $frequencyColors
        ));
        
        return $viewmodel;
    }

    public function switchfrequencyAction()
    {
        $json = array();
        $messages = array();
        
        if ($this->isGranted('events.write') && $this->zfcUserAuthentication()->hasIdentity()) {
            $fromid = $this->params()->fromQuery('fromid', null);
            $toid = $this->params()->fromQuery('toid', null);
            
            $now = new \DateTime('NOW');
            $now->setTimezone(new \DateTimeZone("UTC"));
            
            if ($fromid && $toid) {

                $fromfreq = $this->entityManager->getRepository('Application\Entity\Frequency')->find($fromid);
                $tofreq = $this->entityManager->getRepository('Application\Entity\Frequency')->find($toid);
                
                if ($fromfreq && $tofreq) {
                    
                    // recherche des évènements sur la fréquence d'origine
                    $events = $this->entityManager->getRepository('Application\Entity\Event')->getCurrentEvents('Application\Entity\FrequencyCategory');
                    $frequencyEvents = array();
                    foreach ($events as $event) {
                        $frequencyfield = $event->getCategory()->getFrequencyField();
                        foreach ($event->getCustomFieldsValues() as $value) {
                            if ($value->getCustomField()->getId() == $frequencyfield->getId()) {
                                if ($value->getValue() == $fromid) {
                                    $frequencyEvents[] = $event;
                                }
                            }
                        }
                    }
                    // 0 evt : on en crée un nouveau
                    // 1 evt : on modifie
                    // 2 ou + : indécidable -> erreur
                    if (count($frequencyEvents) == 0) {
                        $this->entityManager->getRepository('Application\Entity\Event')->addSwitchFrequencyEvent($fromfreq, $tofreq, $this->zfcUserAuthentication()
                            ->getIdentity(), null, $messages);
                    } else 
                        if (count($frequencyEvents) == 1) {
                            $event = $frequencyEvents[0];
                            // une exception : si l'évènement a un parent, il faut créer un nouvel évènement
                            // sinon il sera fermé automatiquement à la fermeture du parent
                            if ($event->getParent() != null) {
                                $this->entityManager->getRepository('Application\Entity\Event')->addSwitchFrequencyEvent($fromfreq, $tofreq, $this->zfcUserAuthentication()
                                    ->getIdentity(), null, $messages);
                            } else {
                                // deux cas : changement de fréquence ou retour à la fréquence nominale
                                // dans le deuxième cas, il faut fermer l'évènement si couv normale et freq dispo
                                if ($fromid == $toid) {
                                    // on vérifie que l'évènement existant a bien un champ changement de fréquence
                                    $previousfield = null;
                                    $otherfields = false;
                                    foreach ($event->getCustomFieldsValues() as $value) {
                                        if ($value->getCustomField()->getId() == $event->getCategory()
                                            ->getOtherFrequencyField()
                                            ->getId()) {
                                            $previousfield = $value;
                                        } elseif ($value->getCustomField()->getId() == $event->getCategory()
                                            ->getCurrentAntennaField()
                                            ->getId()) {
                                            if ($value->getValue() == 1) {
                                                $otherfields = true;
                                            }
                                        }
                                    }
                                    if ($previousfield) {
                                        // si il y a d'autres champs autre que le champ "indisponible", on ne ferme pas l'évènement
                                        // sinon on ferme
                                        if ($otherfields) {
                                            $previousfield->setValue($toid);
                                            $this->entityManager->persist($previousfield);
                                        } else {
                                            $endstatus = $this->entityManager->getRepository('Application\Entity\Status')->find('3');
                                            $event->setEnddate($now);
                                            $event->setStatus($endstatus);
                                        }
                                        $this->entityManager->persist($event);
                                        try {
                                            $this->entityManager->flush();
                                            $messages['success'][] = "Fréquence mise à jour";
                                        } catch (\Exception $ex) {
                                            $messages['error'][] = $ex->getMessage();
                                        }
                                    } else {
                                        $messages['error'][] = "Erreur : fréquence identique.";
                                    }
                                } else {
                                    $previousfield = null;
                                    foreach ($event->getCustomFieldsValues() as $value) {
                                        if ($value->getCustomField()->getId() == $event->getCategory()
                                            ->getOtherFrequencyField()
                                            ->getId()) {
                                            $previousfield = $value;
                                        }
                                    }
                                    if ($previousfield) {
                                        $previousfield->setValue($toid);
                                        $this->entityManager->persist($previousfield);
                                    } else {
                                        $customvalue = new CustomFieldValue();
                                        $customvalue->setEvent($event);
                                        $customvalue->setCustomField($event->getCategory()
                                            ->getOtherFrequencyField());
                                        $customvalue->setValue($toid);
                                        $event->addCustomFieldValue($customvalue);
                                        $this->entityManager->persist($customvalue);
                                        $this->entityManager->persist($event);
                                    }
                                    try {
                                        $this->entityManager->flush();
                                        $messages['success'][] = "Evénement modifié";
                                    } catch (\Exception $ex) {
                                        $messages['error'][] = $ex->getMessage();
                                    }
                                }
                            }
                        } else {
                            $messages['error'][] = "Impossible de changer de fréquence : plusieurs évènements sur cette fréquence existent déjà.";
                        }
                } else {
                    $messages['error'][] = "Impossible de trouver les fréquences à échanger.";
                }
            }
        } else {
            $messages['error'][] = "Droits insuffisants";
        }
        $json['messages'] = $messages;
        return new JsonModel($json);
    }

    public function switchantennaAction()
    {
        $json = array();
        $messages = array();
        if ($this->isGranted('events.write') && $this->zfcUserAuthentication()->hasIdentity()) {
            $state = $this->params()->fromQuery('state', null);
            if($state == null) {
                $messages['error'][] = "Requête incomplète : état de l'antenne manquant.";
            } else {
                $state = ($state == "true" ? true : false);
                $antennaid = $this->params()->fromQuery('antennaid', null);
                $freqid = $this->params()->fromQuery('freq', null);
                $freqids = $this->params()->fromQuery('frequencies', null);
                $frequencies = array();
                if ($antennaid) {
                    $antenna = $this->entityManager->getRepository('Application\Entity\Antenna')->find($antennaid);
                    if ($antenna) {
                        if ($freqid) {
                            $frequency = $this->entityManager->getRepository('Application\Entity\Frequency')->find($freqid);
                            if ($frequency) {
                                $frequencies[] = $frequency;
                            }
                        } else {
                            if ($freqids) {
                                foreach ($freqids as $fid) {
                                    $frequency = $this->entityManager->getRepository('Application\Entity\Frequency')->find($fid);
                                    if ($frequency) {
                                        $frequencies[] = $frequency;
                                    }
                                }
                            }
                        }
                        if (count($frequencies) == 0) {
                            $frequencies = null;
                        }
                        $this->entityManager->getRepository('Application\Entity\Event')->addSwitchAntennaStateEvent(
                            $antenna,
                            $state,
                            $this->zfcUserAuthentication()->getIdentity(),
                            $frequencies,
                            $messages
                        );
                    } else {
                        $messages['error'][] = "Impossible de trouver l'antenne demandée.";
                    }
                } else {
                    $messages['error'][] = "Requête incomplète : identifiant de l'antenne manquant.";
                }
            }
        } else {
            $messages['error'][] = 'Droits insuffisants pour modifier l\'état de l\'antenne.';
        }
        $json['messages'] = $messages;
        $json['frequencies'] = $this->getFrequencies();
        return new JsonModel($json);
    }

    public function getFrequenciesOnAntennaAction() {
        $antennaid = $this->params()->fromQuery('antennaid', null);
        $json = array();
        if($antennaid) {
            $antenna = $this->entityManager->getRepository('Application\Entity\Antenna')->find($antennaid);
            if($antenna) {
                foreach ($antenna->getAllFrequencies() as $f) {
                    $json[$f->getId()] = $f->getName();
                }
            }
        }
        return new JsonModel($json);
    }
    
    public function switchFrequencyStateAction()
    {
        $messages = array();
        if ($this->isGranted('events.write') && $this->zfcUserAuthentication()->hasIdentity()) {
            $state = $this->params()->fromQuery('state', null);
            $frequencyid = $this->params()->fromQuery('freqid', null);
            
            if ($state != null && $frequencyid != null) {
                $now = new \DateTime('NOW');
                $now->setTimezone(new \DateTimeZone("UTC"));
                $freq = $this->entityManager->getRepository('Application\Entity\Frequency')->find($frequencyid);
                if ($freq) {
                    $frequencyevents = array();
                    foreach ($this->entityManager->getRepository('Application\Entity\Event')->getCurrentEvents('Application\Entity\FrequencyCategory') as $event) {
                        if ($event->getCustomFieldValue($event->getCategory()
                            ->getFrequencyField())
                            ->getValue() == $freq->getId()) {
                            $frequencyevents[] = $event;
                        }
                    }
                    if (count($frequencyevents) == 0 && $state === 'true') {
                        // cas impossible : nouvel evt freq disponible...
                        $messages['error'][] = "Impossible de trouver l'évènement correspondant.";
                    } else 
                        if ($state != 'true' && (count($frequencyevents) == 0 || (count($frequencyevents) == 1 && $frequencyevents[0]->getParent() != null))) {
                            // passage en indisponible : création si :
                            // - pas d'evt
                            // - 1 evt : création si il y a un parent
                            // pour éviter la fermeture inopinée
                            $this->entityManager->getRepository('Application\Entity\Event')->addSwitchFrequencyStateEvent($freq, $now, $this->zfcUserAuthentication()
                                ->getIdentity(), null, $messages);
                            try {
                                $this->entityManager->flush();
                                $messages['success'][] = "Nouvel évènement fréquence créé.";
                            } catch (\Exception $e) {
                                $messages['error'][] = $e->getMessage();
                            }
                        } else {
                            // reste les autres cas (count > 0), on fait la mise à jour ou la fermeture si possible
                            foreach ($frequencyevents as $freqEvent) {
                                $statefield = $freqEvent->getCustomFieldValue($freqEvent->getCategory()
                                    ->getStateField());
                                $stateva = ($statefield == null ? null : $statefield->getValue());
                                $antennafield = $freqEvent->getCustomFieldValue($freqEvent->getCategory()
                                    ->getCurrentAntennaField());
                                $antenna = ($antennafield == null ? null : $antennafield->getValue());
                                $otherfreqfield = $freqEvent->getCustomFieldValue($freqEvent->getCategory()
                                    ->getOtherFrequencyField());
                                $otherfreq = ($otherfreqfield == null ? null : $otherfreqfield->getValue());
                                if (($otherfreq == null || $otherfreq == 0) && ($antenna == null || $antenna == 0) && $stateva != null && $stateva == true && $state == 'true') {
                                    // passage en disponible
                                    // les autres champs sont vides -> fermeture
                                    $freqEvent->close($this->entityManager->getRepository('Application\Entity\Status')
                                        ->find(3), $now);
                                } else {
                                    // on met à jour le champ correspondant sans fermer l'évènement
                                    if ($statefield == null) {
                                        $statefield = new CustomFieldValue();
                                        $statefield->setCustomField($freqEvent->getCategory()
                                            ->getStateField());
                                        $statefield->setEvent($freqEvent);
                                    }
                                    $statefield->setValue(($state != 'true'));
                                    $this->entityManager->persist($statefield);
                                }
                                $this->entityManager->persist($freqEvent);
                            }
                            try {
                                $this->entityManager->flush();
                                $messages['success'][] = "Evènement fréquence modifié.";
                            } catch (\Exception $e) {
                                $messages['error'][] = $e->getMessage();
                            }
                        }
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

    public function switchCovertureAction()
    {
        $messages = array();
        if ($this->isGranted('events.write') && $this->zfcUserAuthentication()->hasIdentity()) {
            $cov = $this->params()->fromQuery('cov', null);
            $frequencyid = $this->params()->fromQuery('frequencyid', null);
            $cause = $this->params()->fromQuery('cause', '');
            if ($cov != null && $frequencyid) {
                $now = new \DateTime('NOW');
                $now->setTimezone(new \DateTimeZone("UTC"));
                $freq = $this->entityManager->getRepository('Application\Entity\Frequency')->find($frequencyid);
                $cov = intval($cov);
                if ($freq) {
                    $this->entityManager
                        ->getRepository('Application\Entity\Event')
                        ->switchCovFrequency(
                            $freq,
                            $cov,
                            $cause,
                            $this->zfcUserAuthentication()->getIdentity(),
                            null,
                            $messages);
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

    public function getAntennaStateAction()
    {
        return new JsonModel($this->getAntennas(true));
    }

    /**
     * State of the frequencies
     * 
     * @return \Zend\View\Model\JsonModel
     */
    public function getFrequenciesStateAction()
    {
        return new JsonModel($this->getFrequencies());
    }

    private function getFrequencies()
    {

        $frequencies = array();
        $results = $this->entityManager->getRepository('Application\Entity\Frequency')->findBy(array(
            'decommissionned' => false
        ));
        
        // retrieve antennas state once and for all
        $antennas = $this->getAntennas(true);
        
        foreach ($results as $frequency) {
            $frequencies[$frequency->getId()] = array();
            $frequencies[$frequency->getId()]['name'] = $frequency->getValue();
            $frequencies[$frequency->getId()]['status'] = 0;
            $frequencies[$frequency->getId()]['cov'] = 0;
            $frequencies[$frequency->getId()]['otherfreq'] = 0;
            $frequencies[$frequency->getId()]['otherfreqid'] = 0;
            $frequencies[$frequency->getId()]['otherfreqname'] = '';
            $frequencies[$frequency->getId()]['planned'] = false;
            $frequencies[$frequency->getId()]['main'] = $frequency->getMainantenna()->getId();
            $frequencies[$frequency->getId()]['backup'] = $frequency->getBackupAntenna()->getId();
            $frequencies[$frequency->getId()]['mainstatus'] = 1;
            $frequencies[$frequency->getId()]['backupstatus'] = 1;
            if ($frequency->getMainantennaclimax()) {
                $frequencies[$frequency->getId()]['mainclimax'] = $frequency->getMainantennaclimax()->getId();
                $frequencies[$frequency->getId()]['mainclimaxstatus'] = 1;
            }
            if ($frequency->getBackupantennaclimax()) {
                $frequencies[$frequency->getId()]['backupclimax'] = $frequency->getBackupantennaclimax()->getId();
                $frequencies[$frequency->getId()]['backupclimaxstatus'] = 1;
            }
            
            // état de la fréquence : indispo uniquement si toutes antennes en panne
            if (count($antennas[$frequency->getMainAntenna()->getId()]['frequencies']) == 0 || in_array($frequency->getId(), $antennas[$frequency->getMainAntenna()->getId()]['frequencies'])) {
                $frequencies[$frequency->getId()]['status'] += $antennas[$frequency->getMainAntenna()->getId()]['status'];
                $frequencies[$frequency->getId()]['mainstatus'] *= $antennas[$frequency->getMainAntenna()->getId()]['status'];
            } else {
                $frequencies[$frequency->getId()]['status'] += 1;
            }
            if (count($antennas[$frequency->getBackupAntenna()->getId()]['frequencies']) == 0 || in_array($frequency->getId(), $antennas[$frequency->getBackupAntenna()->getId()]['frequencies'])) {
                $frequencies[$frequency->getId()]['status'] += $antennas[$frequency->getBackupAntenna()->getId()]['status'];
                $frequencies[$frequency->getId()]['backupstatus'] *= $antennas[$frequency->getBackupAntenna()->getId()]['status'];
            } else {
                $frequencies[$frequency->getId()]['status'] += 1;
            }
            if ($frequency->getMainantennaclimax()) {
                if (count($antennas[$frequency->getMainAntennaclimax()->getId()]['frequencies']) == 0 || in_array($frequency->getId(), $antennas[$frequency->getMainAntennaclimax()->getId()]['frequencies'])) {
                    $frequencies[$frequency->getId()]['status'] += $antennas[$frequency->getMainAntennaclimax()->getId()]['status'];
                    $frequencies[$frequency->getId()]['mainclimaxstatus'] *= $antennas[$frequency->getMainAntennaclimax()->getId()]['status'];
                } else {
                    $frequencies[$frequency->getId()]['status'] += 1;
                }
            }
            if ($frequency->getBackupantennaclimax()) {
                if (count($antennas[$frequency->getBackupantennaclimax()->getId()]['frequencies']) == 0 || in_array($frequency->getId(), $antennas[$frequency->getBackupantennaclimax()->getId()]['frequencies'])) {
                    $frequencies[$frequency->getId()]['status'] += $antennas[$frequency->getBackupantennaclimax()->getId()]['status'];
                    $frequencies[$frequency->getId()]['backupclimaxstatus'] *= $antennas[$frequency->getBackupantennaclimax()->getId()]['status'];
                } else {
                    $frequencies[$frequency->getId()]['status'] += 1;
                }
            }
        }
        
        foreach ($this->entityManager->getRepository('Application\Entity\Event')->getCurrentEvents('Application\Entity\FrequencyCategory') as $event) {
            $statefield = $event->getCategory()
                ->getStateField()
                ->getId();
            $frequencyfield = $event->getCategory()
                ->getFrequencyField()
                ->getId();
            $covfield = $event->getCategory()
                ->getCurrentAntennaField()
                ->getId();
            $otherfreqfield = $event->getCategory()
                ->getOtherFrequencyField()
                ->getId();
            $frequencyid = 0;
            $otherfreqid = 0;
            $available = true;
            $cov = 0;
            foreach ($event->getCustomFieldsValues() as $customvalue) {
                if ($customvalue->getCustomField()->getId() == $statefield) {
                    $available = ! $customvalue->getValue();
                } elseif ($customvalue->getCustomField()->getId() == $frequencyfield) {
                    $frequencyid = $customvalue->getValue();
                } elseif ($customvalue->getCustomField()->getId() == $covfield) {
                    $cov = $customvalue->getValue();
                } elseif ($customvalue->getCustomField()->getId() == $otherfreqfield) {
                    $otherfreqid = $customvalue->getValue();
                }
            }
            if (array_key_exists($frequencyid, $frequencies)) { // peut être inexistant si la fréquence a été supprimée alors que des évènements existent
                
                $frequencies[$frequencyid]['status'] *= $available;
                $frequencies[$frequencyid]['cov'] = $cov;
                $otherfreq = $this->entityManager->getRepository('Application\Entity\Frequency')->find($otherfreqid);
                if ($otherfreq) {
                    $frequencies[$frequencyid]['otherfreq'] = $otherfreq->getValue();
                    $frequencies[$frequencyid]['otherfreqname'] = $otherfreq->getName();
                    $frequencies[$frequencyid]['otherfreqid'] = $otherfreq->getId();
                    $frequencies[$frequencyid]['main'] = $otherfreq->getMainantenna()->getId();
                    $frequencies[$frequencyid]['backup'] = $otherfreq->getBackupantenna()->getId();
                    if ($otherfreq->getMainantennaclimax()) {
                        $frequencies[$frequencyid]['mainclimax'] = $otherfreq->getMainantennaclimax()->getId();
                    }
                    if ($otherfreq->getBackupantennaclimax()) {
                        $frequencies[$frequencyid]['backupclimax'] = $otherfreq->getBackupantennaclimax()->getId();
                    }
                } else {}
            }
        }
        
        // on refait une passe pour mettre les couvertures en cohérence en cas de changement de fréquence
        foreach ($frequencies as $key => $freq) {
            if ($freq['otherfreqid'] != 0) {
                $frequencies[$key]['cov'] = $frequencies[$freq['otherfreqid']]['cov'];
            }
        }
        
        // on donne aussi les evènements dans les 12h
        foreach ($this->entityManager->getRepository('Application\Entity\Event')->getPlannedEvents('Application\Entity\FrequencyCategory') as $event) {
            $frequencyidfield = $event->getCustomFieldValue($event->getCategory()
                ->getFrequencyField());
            $frequencyid = ($frequencyidfield == null ? null : $frequencyidfield->getValue());
            if (array_key_exists($frequencyid, $frequencies)) { // peut être inexistant si la fréquence a été supprimée alors que des évènements existent
                $frequencies[$frequencyid]['planned'] = true;
            }
        }
        
        return $frequencies;
    }

    private function getAntennas($full = true)
    {

        $antennas = array();
        
        foreach ($this->entityManager->getRepository('Application\Entity\Antenna')->findBy(array(
            'decommissionned' => false
        )) as $antenna) {
            // avalaible by default
            if ($full) {
                $antennas[$antenna->getId()] = array();
                $antennas[$antenna->getId()]['name'] = $antenna->getName();
                $antennas[$antenna->getId()]['shortname'] = $antenna->getShortname();
                $antennas[$antenna->getId()]['status'] = true;
                $antennas[$antenna->getId()]['planned'] = false;
                $antennas[$antenna->getId()]['frequencies'] = [];
            } else {
                $antennas[$antenna->getId()] = true;
            }
        }
        
        foreach ($this->entityManager->getRepository('Application\Entity\Event')->getCurrentEvents('Application\Entity\AntennaCategory') as $result) {
            $statefield = $result->getCategory()
                ->getStateField()
                ->getId();
            $antennafield = $result->getCategory()
                ->getAntennafield()
                ->getId();
            $frequenciesfield = $result->getCategory()
                ->getFrequenciesField()
                ->getId();
            $antennaid = 0;
            $available = true;
            $frequencies = [];
            foreach ($result->getCustomFieldsValues() as $customvalue) {
                if ($customvalue->getCustomField()->getId() == $statefield) {
                    $available = ! $customvalue->getValue();
                } else 
                    if ($customvalue->getCustomField()->getId() == $antennafield) {
                        $antennaid = $customvalue->getValue();
                    } else 
                        if ($customvalue->getCustomField()->getId() == $frequenciesfield) {
                            $frequencies = explode("\r", $customvalue->getValue());
                        }
            }
            if ($full) {
                $antennas[$antennaid]['status'] *= $available;
                $antennas[$antennaid]['frequencies'] = $frequencies;
            } else {
                $antennas[$antennaid] *= $available;
            }
        }
        
        if ($full) {
            foreach ($this->entityManager->getRepository('Application\Entity\Event')->getPlannedEvents('Application\Entity\AntennaCategory') as $result) {
                $statefield = $result->getCategory()
                    ->getStateField()
                    ->getId();
                $antennafield = $result->getCategory()
                    ->getAntennafield()
                    ->getId();
                $frequenciesfield = $result->getCategory()
                    ->getFrequenciesField()
                    ->getId();
                $antennaid = 0;
                $planned = false;
                $frequencies = [];
                foreach ($result->getCustomFieldsValues() as $customvalue) {
                    if ($customvalue->getCustomField()->getId() == $statefield) {
                        $planned = $customvalue->getValue();
                    } elseif ($customvalue->getCustomField()->getId() == $antennafield) {
                        $antennaid = $customvalue->getValue();
                    } elseif ($customvalue->getCustomField()->getId() == $frequenciesfield) {
                        $frequencies = explode("\r", $customvalue->getValue());
                    }
                }
                $antennas[$antennaid]['planned'] = $planned;
                $antennas[$antennaid]['frequencies'] = $frequencies;
            }
        }
        
        return $antennas;
    }

    public function getfrequenciesAction()
    {
        $frequencyid = $this->params()->fromQuery('id', null);
        $frequencies = array();
        if ($frequencyid) {
            $frequency = $this->entityManager->getRepository('Application\Entity\Frequency')->find($frequencyid);
            $qb = $this->entityManager->createQueryBuilder();
            $qb->select(array(
                'f'
            ))
                ->from('Application\Entity\Frequency', 'f')
                ->leftJoin('f.defaultsector', 'd')
                ->andWhere($qb->expr()
                ->eq('f.organisation', '?1'))
                ->addOrderBy('d.zone', 'DESC')
                ->addOrderBy('d.name', 'ASC')
                ->setParameter('1', $frequency->getOrganisation()
                ->getId());
            $place = 0;
            foreach ($qb->getQuery()->getResult() as $freq) {
                $frequencies[$freq->getId()] = array(
                    'place' => $place,
                    'data' => ($freq->getDefaultSector() ? $freq->getDefaultSector()->getName() . " " . $freq->getValue() : $freq->getOtherName() . " " . $freq->getValue())
                );
                $place ++;
            }
        }
        return new JsonModel($frequencies);
    }

    public function getficheAction()
    {
        $viewmodel = new ViewModel();
        $request = $this->getRequest();
        
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());
        
        $antennaId = $this->params()->fromQuery('id', null);

        $antenna = $this->entityManager->getRepository('Application\Entity\Antenna')->find($antennaId);
        
        $fiche = null;
        $history = null;
        if ($antenna) {
            $events = $this->entityManager->getRepository('Application\Entity\Event')->getCurrentEvents('Application\Entity\AntennaCategory');
            $antennaEvents = array();
            foreach ($events as $event) {
                foreach ($event->getCustomFieldsValues() as $value) {
                    if ($value->getCustomField()->getId() == $event->getCategory()
                        ->getAntennafield()
                        ->getId()) {
                        if ($value->getValue() == $antennaId) {
                            $antennaEvents[] = $event;
                        }
                    }
                }
            }
            // recherche aussi sur les evts planifiés
            $events = $this->entityManager->getRepository('Application\Entity\Event')->getPlannedEvents('Application\Entity\AntennaCategory');
            foreach ($events as $event) {
                foreach ($event->getCustomFieldsValues() as $value) {
                    if ($value->getCustomField()->getId() == $event->getCategory()
                        ->getAntennafield()
                        ->getId()) {
                        if ($value->getValue() == $antennaId) {
                            $antennaEvents[] = $event;
                        }
                    }
                }
            }
            
            if (count($antennaEvents) >= 1) {
                $event = $antennaEvents[0];
                $fiche = $event;
                $history = $this->eventservice->getHistory($event);
            }
        }
        
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
        
        $viewmodel->setVariable('history', $history);
        $viewmodel->setVariable('fiche', $fiche);
        $viewmodel->setVariable('actions', $actions);
        return $viewmodel;
    }

    public function formbrouillageAction()
    {
        $freqId = $this->params()->fromQuery('id', null);
        
        $viewmodel = new ViewModel();
        $request = $this->getRequest();
        
        // disable layout if request by Ajax
        $viewmodel->setTerminal($request->isXmlHttpRequest());

        $brouillagecat = $this->entityManager->getRepository('Application\Entity\BrouillageCategory')->findOneBy(array(
            'defaultbrouillagecategory' => true
        ));
        if ($brouillagecat) {
            $form = $this->getFormBrouillage($freqId)['form'];
            $viewmodel->setVariable('freqid', $freqId);
            $viewmodel->setVariable('form', $form);
        } else {
            $viewmodel->setVariable('error', 'Aucune catégorie Brouillage configurée : contactez l\'administrateur');
        }
        
        return $viewmodel;
    }

    public function savebrouillageAction()
    {
        $messages = array();
        $event = null;
        $return = $this->params()->fromQuery('return', null);
        
        $freqId = $this->params()->fromQuery('freqid', null);

        if ($this->zfcUserAuthentication()->hasIdentity() && $this->isGranted('events.create')) {
            if ($this->getRequest()->isPost()) {
                $post = $this->getRequest()->getPost();
                $datas = $this->getFormBrouillage($freqId);
                $form = $datas['form'];
                $event = $datas['event'];
                $form->setPreferFormInputFilter(true);
                $form->setData($post);
                if ($form->isValid()) {
                    // getAuthor
                    $event->setAuthor($this->zfcUserAuthentication()
                        ->getIdentity());
                    $event->setOrganisation($this->zfcUserAuthentication()
                        ->getIdentity()
                        ->getOrganisation());
                    
                    if (isset($post['startdate']) && ! empty($post['startdate'])) {
                        $offset = date("Z");
                        $startdate = new \DateTime($post['startdate']);
                        $startdate->setTimezone(new \DateTimeZone("UTC"));
                        $startdate->add(new \DateInterval("PT" . $offset . "S"));
                        $event->setStartdate($startdate);
                    }
                    if (isset($post['custom_fields'])) {
                        foreach ($post['custom_fields'] as $key => $value) {
                            // génération des customvalues si un customfield dont le nom est $key est trouvé
                            $customfield = $this->entityManager->getRepository('Application\Entity\CustomField')->findOneBy(array(
                                'id' => $key
                            ));
                            if ($customfield) {
                                // $customvalue = $objectManager->getRepository('Application\Entity\CustomFieldValue')
                                // ->findOneBy(array('customfield'=>$customfield->getId(), 'event'=>$id));
                                // if(!$customvalue){
                                $customvalue = new CustomFieldValue();
                                $customvalue->setEvent($event);
                                $customvalue->setCustomField($customfield);
                                $event->addCustomFieldValue($customvalue);
                                // }
                                $customvalue->setValue($value);
                                $this->entityManager->persist($customvalue);
                            }
                        }
                    }
                    $this->entityManager->persist($event);
                    try {
                        $this->entityManager->flush();
                        $messages['success'][] = "Brouillage enregistré";
                    } catch (\Exception $e) {
                        $messages['error'][] = $e->getMessage();
                    }
                } else {
                    $this->processFormMessages($form->getMessages(), $messages);
                }
            }
        } else {
            $messages['error'][] = "Connexion obligatoire pour créer un évènement";
        }
        return new JsonModel(array(
            'messages' => $messages,
            'eventid' => $event->getId()
        ));
    }

    private function getFormBrouillage($idfreq, $event = null)
    {
        if (! $event) {
            $event = new Event();
            $event->setCategory($this->entityManager->getRepository('Application\Entity\BrouillageCategory')
                ->findOneBy(array(
                'defaultbrouillagecategory' => true
            )));
        }
        
        $builder = new AnnotationBuilder();
        $form = $builder->createForm($event);
        $form->setHydrator(new DoctrineObject($this->entityManager))->setObject($event);
        
        $form->bind($event);
        $form->setData($event->getArrayCopy());
        
        $form->get('impact')->setValueOptions($this->entityManager->getRepository('Application\Entity\Impact')
            ->getAllAsArray());
        
        if ($this->zfcUserAuthentication()->hasIdentity()) {
            $org = $this->zfcUserAuthentication()
                ->getIdentity()
                ->getOrganisation();
            $form->get('organisation')->setValue($org->getId());
        }
        
        $form->get('startdate')->setAttribute('required', true);
        
        $form->get('status')->setValue(3);
        $form->get('punctual')->setValue(true);
        
        $form->add(new CustomFieldset($this->entityManager, $this->customfieldservice, $event->getCategory()
            ->getId()));
        
        $form->get('custom_fields')
            ->get($event->getCategory()
            ->getFrequencyField()
            ->getId())
            ->setValue($idfreq);
        
        $form->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Enregistrer',
                'class' => 'btn btn-primary'
            )
        ));
        
        $form->add(array(
            'name' => 'submitfne',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Enregistrer + FNE',
                'class' => 'btn btn-primary'
            )
        ));
        
        return array(
            'form' => $form,
            'event' => $event
        );
    }
}
