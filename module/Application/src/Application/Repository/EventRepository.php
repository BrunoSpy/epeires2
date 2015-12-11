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
namespace Application\Repository;

use Application\Entity\CustomFieldValue;
use Application\Entity\Event;
use Application\Entity\Frequency;
use Application\Core\User;
use Zend\Session\Container;
use \Core\NMB2B\EAUPRSAs;
use Application\Entity\TemporaryResource;
use Application\Entity\Radar;
use Application\Entity\Antenna;

/**
 * Description of EventRepository
 *
 * @author Bruno Spyckerelle
 */
class EventRepository extends ExtendedRepository
{

    /**
     * Get all events readable by <code>$userauth</code>
     * intersecting <code>$day</code>
     * 
     * @param type $userauth            
     * @param type $day
     *            If null : use current day
     * @param type $lastmodified
     *            If not null : only events modified since <code>$lastmodified</code>
     * @param type $orderbycat            
     * @param type $onlytimeline            
     * @return type
     */
    public function getEvents($userauth, $day = null, $lastmodified = null, $orderbycat = false, $cats = null)
    {
        $parameters = array();
        
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select(array(
            'e',
            'f',
            'c',
            'p'
        ))
            ->from('Application\Entity\Event', 'e')
            ->leftJoin('e.zonefilters', 'f')
            ->leftJoin('e.category', 'c')
            ->leftJoin('c.parent', 'p')
            ->andWhere($qb->expr()
            ->isNull('e.parent')); // display only root events
        
        if ($cats) {
            $qb->andWhere($qb->expr()
                ->in('e.category', $cats));
        } else {
            // pas de catégorie => page d'accueil, enlever tous les évènements dont la catégorie n'est pas affichée sur la timeline
            $qb->andWhere($qb->expr()
                ->orX($qb->expr()
                ->andX($qb->expr()
                ->isNull('c.parent'), $qb->expr()
                ->eq('c.timeline', true)), $qb->expr()
                ->andX($qb->expr()
                ->isNotNull('c.parent'), $qb->expr()
                ->eq('c.timeline', true), $qb->expr()
                ->eq('p.timeline', true))));
        }
        
        // restriction éventuelle aux événements confirmés si timelineconfirmed, seulement en timeline (cats est null)
        if(!$cats)
        {
        $qb->andWhere($qb->expr()->orX(
                $qb->expr()->neq('c.timelineconfirmed',true),
                $qb->expr()->andX(
                        $qb->expr()->eq('c.timelineconfirmed',true),
                        $qb->expr()->in('e.status',array(2,3)))
                )
            );
        }
                
        // restriction à tous les evts modifiés depuis $lastmodified qqsoit la date de l'évènement
        if ($lastmodified) {
            $lastmodified = new \DateTime($lastmodified);
            $qb->andWhere($qb->expr()
                ->gte('e.last_modified_on', '?1'));
            $parameters[1] = $lastmodified->format("Y-m-d H:i:s");
            $qb->setParameters($parameters);
        } else {
            if ($day) { // restriction aux evts intersectant le jour spécifié
                $daystart = new \DateTime($day);
                $daystart->setTime(0, 0, 0);
                $dayend = new \DateTime($day);
                $dayend->setTime(23, 59, 59);
                $daystart = $daystart->format("Y-m-d H:i:s");
                $dayend = $dayend->format("Y-m-d H:i:s");
                // tous les évènements ayant une intersection non nulle avec $day
                $qb->andWhere($qb->expr()
                    ->orX(
                    // evt dont la date de début est le bon jour : inclus les ponctuels
                    $qb->expr()
                        ->andX($qb->expr()
                        ->gte('e.startdate', '?1'), $qb->expr()
                        ->lte('e.startdate', '?2')), 
                    // evt dont la date de début est passée : forcément non ponctuels
                    $qb->expr()
                        ->andX($qb->expr()
                        ->eq('e.punctual', 'false'), $qb->expr()
                        ->lt('e.startdate', '?1'), $qb->expr()
                        ->orX($qb->expr()
                        ->isNull('e.enddate'), $qb->expr()
                        ->gte('e.enddate', '?1')))));
                $parameters[1] = $daystart;
                $parameters[2] = $dayend;
                $qb->setParameters($parameters);
            } else {
                // sinon restriction aux evts autour du jour présent
                $now = new \DateTime('NOW');
                $start = clone $now;
                $start->sub(new \DateInterval('P3D'));
                $end = clone $now;
                $end->add(new \DateInterval('P1D'));
                $qb->andWhere($qb->expr()
                    ->orX(
                    // sans date de fin et non ponctuel
                    $qb->expr()
                        ->andX($qb->expr()
                        ->isNull('e.enddate'), $qb->expr()
                        ->eq('e.punctual', 'false')), 
                    // date de début antèrieure au début => date de fin postèrieure au début
                    $qb->expr()
                        ->andX($qb->expr()
                        ->lte('e.startdate', '?1'), $qb->expr()
                        ->gte('e.enddate', '?1')), 
                    // date de début antèrieure au début => date de début antérieure à la fin
                    $qb->expr()
                        ->andX($qb->expr()
                        ->gte('e.startdate', '?1'), $qb->expr()
                        ->lte('e.startdate', '?2'))));
                $parameters[1] = $start->format("Y-m-d H:i:s");
                $parameters[2] = $end->format("Y-m-d H:i:s");
                $qb->setParameters($parameters);
            }
        }
        
        // filtre par zone
        $session = new Container('zone');
        $zonesession = $session->zoneshortname;
        if ($userauth && $userauth->hasIdentity()) {
            // on filtre soit par la valeur en session soit par l'organisation de l'utilisateur
            // TODO gérer les evts partagés
            if ($zonesession != null) { // application d'un filtre géographique
                if ($zonesession != '0') {
                    // la variable de session peut contenir soit une orga soit une zone
                    $orga = $this->getEntityManager()
                        ->getRepository('Application\Entity\Organisation')
                        ->findOneBy(array(
                        'shortname' => $zonesession
                    ));
                    if ($orga) {
                        $qb->andWhere($qb->expr()
                            ->eq('e.organisation', $orga->getId()));
                    } else {
                        $zone = $this->getEntityManager()
                            ->getRepository('Application\Entity\QualificationZone')
                            ->findOneBy(array(
                            'shortname' => $zonesession
                        ));
                        if ($zone) {
                            $qb->andWhere($qb->expr()
                                ->andX($qb->expr()
                                ->eq('e.organisation', $zone->getOrganisation()
                                ->getId()), $qb->expr()
                                ->orX($qb->expr()
                                ->eq('f', $zone->getId()), $qb->expr()
                                ->isNull('f.id'))));
                        } else {
                            // throw error
                        }
                    }
                } else {
                    // tous les evts de l'org de l'utilisateur connecté
                    $orga = $userauth->getIdentity()->getOrganisation();
                    $qb->andWhere($qb->expr()
                        ->eq('e.organisation', $orga->getId()));
                }
            } else {
                // tous les evts de l'org de l'utilisateur connecté
                $orga = $userauth->getIdentity()->getOrganisation();
                $qb->andWhere($qb->expr()
                    ->eq('e.organisation', $orga->getId()));
            }
        } else {
            // aucun filtre autre que les rôles
        }
        
        // used by ReportController
        if ($orderbycat) {
            $qb->addOrderBy('e.category')->addOrderBy('e.startdate');
        }
        
        $events = $qb->getQuery()->getResult();
        
        $readableEvents = array();
        
        if ($userauth != null && $userauth->hasIdentity()) {
            $roles = $userauth->getIdentity()->getRoles();
            foreach ($events as $event) {
                $eventroles = $event->getCategory()->getReadroles();
                foreach ($roles as $role) {
                    if ($eventroles->contains($role)) {
                        $readableEvents[] = $event;
                        break;
                    }
                }
            }
        } else 
            if ($userauth != null) {
                $roleentity = $this->getEntityManager()
                    ->getRepository('Core\Entity\Role')
                    ->findOneBy(array(
                    'name' => 'guest'
                ));
                if ($roleentity) {
                    foreach ($events as $event) {
                        $eventroles = $event->getCategory()->getReadroles();
                        if ($eventroles->contains($roleentity)) {
                            $readableEvents[] = $event;
                        }
                    }
                }
            } else {
                $readableEvents = $events;
            }
        
        return $readableEvents;
    }

    /**
     * Get all events intersecting [$start, $end] and affected to the user's organisation
     * 
     * @param unknown $user            
     * @param unknown $start
     *            DateTime
     * @param unknown $end
     *            DateTime
     */
    public function getAllEvents($user, $start, $end)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select(array(
            'e'
        ))
            ->from('Application\Entity\Event', 'e')
            ->andWhere($qb->expr()
            ->isNull('e.parent'))
            ->andWhere($qb->expr() // display only root events
            ->orX(
            // sans date de fin et non ponctuel
            $qb->expr()
                ->andX($qb->expr()
                ->isNull('e.enddate'), $qb->expr()
                ->eq('e.punctual', 'false'), $qb->expr()
                ->lte('e.startdate', '?2')), 
            // non ponctuel, avec date de fin
            $qb->expr()
                ->andX($qb->expr()
                ->isNotNull('e.enddate'), $qb->expr()
                ->eq('e.punctual', 'false'), $qb->expr()
                ->lte('e.startdate', '?2'), $qb->expr()
                ->gte('e.enddate', '?1')), 
            // ponctuel
            $qb->expr()
                ->andX($qb->expr()
                ->eq('e.punctual', 'true'), $qb->expr()
                ->gte('e.startdate', '?1'), $qb->expr()
                ->lte('e.startdate', '?2'))));
        
        if ($user !== null && $user->hasIdentity()) {
            $org = $user->getIdentity()->getOrganisation();
            
            $qb->andWhere($qb->expr()
                ->eq('e.organisation', $org->getId()));
            
            $parameters[1] = $start->format("Y-m-d H:i:s");
            $parameters[2] = $end->format("Y-m-d H:i:s");
            $qb->setParameters($parameters);
            
            $query = $qb->getQuery();
            
            return $query->getResult();
        } else {
            return array();
        }
    }

    /**
     * Tous les évènements en cours concernant la catégorie <code>$category</code>
     */
    public function getCurrentEvents($category)
    {
        $now = new \DateTime('NOW');
        $now->setTimezone(new \DateTimeZone("UTC"));
        $qbEvents = $this->getEntityManager()->createQueryBuilder();
        $qbEvents->select(array(
            'e',
            'cat'
        ))
            ->from('Application\Entity\Event', 'e')
            ->innerJoin('e.category', 'cat')
            ->andWhere('cat INSTANCE OF ' . $category)
            ->andWhere($qbEvents->expr()
            ->eq('e.punctual', 'false'))
            ->andWhere($qbEvents->expr()
            ->lte('e.startdate', '?1'))
            ->andWhere($qbEvents->expr()
            ->orX($qbEvents->expr()
            ->isNull('e.enddate'), $qbEvents->expr()
            ->gte('e.enddate', '?2')))
            ->andWhere($qbEvents->expr()
            ->in('e.status', '?3'))
            ->setParameters(array(
            1 => $now->format('Y-m-d H:i:s'),
            2 => $now->format('Y-m-d H:i:s'),
            3 => array(
                1,
                2,
                3
            )
        )); // statuts nouveau, en cours et terminé uniquement
        
        $query = $qbEvents->getQuery();
        
        return $query->getResult();
    }

    /**
     * Tous les évènements de la page radio en cours ou dans l'heure
     */
    public function getRadioEvents()
    {
        $qbEvents = $this->getQueryEvents();
        $qbEvents->andWhere($qbEvents->expr()
            ->orX('cat INSTANCE OF Application\Entity\FrequencyCategory', 'cat INSTANCE OF Application\Entity\AntennaCategory', 'cat INSTANCE OF Application\Entity\BrouillageCategory'));
        
        $query = $qbEvents->getQuery();
        
        return $query->getResult();
    }

    public function getRadarEvents()
    {
        $qbEvents = $this->getQueryEvents();
        $qbEvents->andWhere('cat INSTANCE OF Application\Entity\RadarCategory');
        
        $query = $qbEvents->getQuery();
        
        return $query->getResult();
    }

    /**
     * Tous les évènements en cours et à venir dans moins d'une heure pour un onglet
     * 
     * @param Application\Entity\Tab $tab            
     */
    public function getTabEvents(\Application\Entity\Tab $tab)
    {
        $qbEvents = $this->getQueryEvents();
        $catsid = array();
        foreach ($tab->getCategories() as $cat) {
            $catsid[] = $cat->getId();
        }
        $qbEvents->andWhere($qbEvents->expr()
            ->in('cat.id', '?4'))
            ->setParameter(4, $catsid);
        $query = $qbEvents->getQuery();
        return $query->getResult();
    }

    /**
     * Retourne un Query Builder pour tous les évènements en cours
     */
    private function getQueryEvents()
    {
        $now = new \DateTime('NOW');
        $now->setTimezone(new \DateTimeZone("UTC"));
        $qbEvents = $this->getEntityManager()->createQueryBuilder();
        $qbEvents->select(array(
            'e',
            'cat'
        ))
            ->from('Application\Entity\Event', 'e')
            ->innerJoin('e.category', 'cat')
            ->andWhere($qbEvents->expr()
            ->in('e.status', '?1'))
            ->andWhere($qbEvents->expr()
            ->andX($qbEvents->expr()
            ->eq('e.punctual', 'false'), $qbEvents->expr()
            ->lte('e.startdate', '?2'), $qbEvents->expr()
            ->orX($qbEvents->expr()
            ->isNull('e.enddate'), $qbEvents->expr()
            ->gte('e.enddate', '?2'))))
            ->setParameters(array(
            1 => array(
                1,
                2,
                3
            ),
            2 => $now->format('Y-m-d H:i:s')
        ));
        return $qbEvents;
    }

    /**
     * Tous les éléments prévus concernant la catégorie <code>$category</code>
     * - Date de début dans les 12h
     */
    public function getPlannedEvents($category)
    {
        $now = new \DateTime('NOW');
        $now->setTimezone(new \DateTimeZone("UTC"));
        $qbEvents = $this->getEntityManager()->createQueryBuilder();
        $qbEvents->select(array(
            'e',
            'cat'
        ))
            ->from('Application\Entity\Event', 'e')
            ->innerJoin('e.category', 'cat')
            ->andWhere('cat INSTANCE OF ' . $category)
            ->andWhere($qbEvents->expr()
            ->andX($qbEvents->expr()
            ->gte('e.startdate', '?1'), $qbEvents->expr()
            ->lte('e.startdate', '?2')))
            ->andWhere($qbEvents->expr()
            ->in('e.status', '?3'))
            ->setParameters(array(
            1 => $now->format('Y-m-d H:i:s'),
            2 => $now->add(new \DateInterval('PT12H'))
                ->format('Y-m-d H:i:s'),
            3 => array(
                1,
                2,
                3
            )
        ));
        
        $query = $qbEvents->getQuery();
        
        return $query->getResult();
    }

    public function addSwitchFrequencyStateEvent(Frequency $freq, \DateTime $startdate, \Core\Entity\User $author, Event $parent = null, &$messages = null)
    {
        $em = $this->getEntityManager();
        $event = new Event();
        if ($parent) {
            $event->setParent($parent);
        }
        $status = $em->getRepository('Application\Entity\Status')->find('2');
        $impact = $em->getRepository('Application\Entity\Impact')->find('3');
        $event->setImpact($impact);
        $event->setStatus($status);
        $event->setStartdate($startdate);
        $event->setPunctual(false);
        // TODO fix horrible en attendant de gérer correctement les fréquences sans secteur
        if ($freq->getDefaultsector()) {
            $event->setOrganisation($freq->getDefaultsector()
                ->getZone()
                ->getOrganisation());
            $event->addZonefilter($freq->getDefaultsector()
                ->getZone());
        } else {
            $event->setOrganisation($author->getOrganisation());
        }
        $event->setAuthor($author);
        $categories = $em->getRepository('Application\Entity\FrequencyCategory')->findBy(array(
            'defaultfrequencycategory' => true
        ));
        if ($categories) {
            $cat = $categories[0];
            $event->setCategory($cat);
            $frequencyfieldvalue = new CustomFieldValue();
            $frequencyfieldvalue->setCustomField($cat->getFrequencyField());
            $frequencyfieldvalue->setEvent($event);
            $frequencyfieldvalue->setValue($freq->getId());
            $event->addCustomFieldValue($frequencyfieldvalue);
            $statusfield = new CustomFieldValue();
            $statusfield->setCustomField($cat->getStateField());
            $statusfield->setEvent($event);
            $statusfield->setValue(true); // unavailable
            $event->addCustomFieldValue($statusfield);
            $em->persist($frequencyfieldvalue);
            $em->persist($statusfield);
            $em->persist($event);
            try {
                $em->flush();
                if ($messages) {
                    $messages['success'][] = "Fréquence " . $freq->getValue() . " passée au statut indisponible.";
                }
            } catch (\Exception $e) {
                if ($messages) {
                    $messages['error'][] = $e->getMessage();
                } else {
                    error_log($e->getMessage());
                }
            }
        } else {
            $messages['error'][] = "Impossible de passer les couvertures en secours : aucune catégorie trouvée.";
        }
    }

    /**
     * Crée un nouvel évènement pour un changement de fréquence
     * 
     * @param \Application\Repository\Frequency $from            
     * @param \Application\Repository\Frequency $to            
     * @param \Application\Repository\Event $parent            
     * @param type $messages            
     */
    public function addSwitchFrequencyEvent(Frequency $from, Frequency $to, \Core\Entity\User $author, Event $parent = null, &$messages = null)
    {
        $now = new \DateTime('NOW');
        $now->setTimezone(new \DateTimeZone("UTC"));
        $em = $this->getEntityManager();
        $event = new Event();
        $event->setParent($parent);
        $status = $em->getRepository('Application\Entity\Status')->find('2');
        $impact = $em->getRepository('Application\Entity\Impact')->find('3');
        $event->setImpact($impact);
        $event->setStatus($status);
        $event->setStartdate($now);
        $event->setPunctual(false);
        // TODO fix horrible en attendant de gérer correctement les fréquences sans secteur
        if ($from->getDefaultsector()) {
            $event->setOrganisation($from->getDefaultsector()
                ->getZone()
                ->getOrganisation());
            $event->addZonefilter($from->getDefaultsector()
                ->getZone());
        } else {
            $event->setOrganisation($author->getOrganisation());
        }
        $event->setAuthor($author);
        $categories = $em->getRepository('Application\Entity\FrequencyCategory')->findBy(array(
            'defaultfrequencycategory' => true
        ));
        if ($categories) {
            $cat = $categories[0];
            $event->setCategory($cat);
            $frequencyfieldvalue = new CustomFieldValue();
            $frequencyfieldvalue->setCustomField($cat->getFrequencyField());
            $frequencyfieldvalue->setEvent($event);
            $frequencyfieldvalue->setValue($from->getId());
            $event->addCustomFieldValue($frequencyfieldvalue);
            $statusfield = new CustomFieldValue();
            $statusfield->setCustomField($cat->getStateField());
            $statusfield->setEvent($event);
            $statusfield->setValue(false); // available
            $event->addCustomFieldValue($statusfield);
            $freqfield = new CustomFieldValue();
            $freqfield->setCustomField($cat->getOtherFrequencyField());
            $freqfield->setEvent($event);
            $freqfield->setValue($to->getId());
            $event->addCustomFieldValue($freqfield);
            $em->persist($frequencyfieldvalue);
            $em->persist($statusfield);
            $em->persist($freqfield);
            $em->persist($event);
            try {
                $em->flush();
                $messages['success'][] = "Changement de fréquence pour " . $from->getName() . " enregistré.";
            } catch (\Exception $e) {
                $messages['error'][] = $e->getMessage();
            }
        } else {
            $messages['error'][] = "Erreur : aucune catégorie trouvée.";
        }
    }

    /**
     * Create a new frequency event
     * 
     * @param Frequency $frequency            
     * @param type $cov
     *            Value for the current antenna field
     * @param type $freqstatus
     *            Value for the current frequency state field
     * @param Event $parent            
     * @param \DateTime $startdate            
     * @param User $author            
     * @param type $messages            
     */
    public function addChangeFrequencyCovEvent(Frequency $frequency, $cov, $freqstatus, \DateTime $startdate, \Core\Entity\User $author, Event $parent = null, &$messages = null)
    {
        $em = $this->getEntityManager();
        $event = new Event();
        if ($parent) {
            $event->setParent($parent);
        }
        $status = $em->getRepository('Application\Entity\Status')->find('2');
        $impact = $em->getRepository('Application\Entity\Impact')->find('3');
        $event->setImpact($impact);
        $event->setStatus($status);
        $event->setStartdate($startdate);
        $event->setPunctual(false);
        // TODO fix horrible en attendant de gérer correctement les fréquences sans secteur
        if ($frequency->getDefaultsector()) {
            $event->setOrganisation($frequency->getDefaultsector()
                ->getZone()
                ->getOrganisation());
            $event->addZonefilter($frequency->getDefaultsector()
                ->getZone());
        } else {
            $event->setOrganisation($author->getOrganisation());
        }
        $event->setAuthor($author);
        $categories = $em->getRepository('Application\Entity\FrequencyCategory')->findBy(array(
            'defaultfrequencycategory' => true
        ));
        if ($categories) {
            $cat = $categories[0];
            $event->setCategory($cat);
            $frequencyfieldvalue = new CustomFieldValue();
            $frequencyfieldvalue->setCustomField($cat->getFrequencyField());
            $frequencyfieldvalue->setEvent($event);
            $frequencyfieldvalue->setValue($frequency->getId());
            $event->addCustomFieldValue($frequencyfieldvalue);
            $statusfield = new CustomFieldValue();
            $statusfield->setCustomField($cat->getStateField());
            $statusfield->setEvent($event);
            $statusfield->setValue($freqstatus);
            $event->addCustomFieldValue($statusfield);
            $covfield = new CustomFieldValue();
            $covfield->setCustomField($cat->getCurrentAntennaField());
            $covfield->setEvent($event);
            $covfield->setValue($cov);
            $event->addCustomFieldValue($covfield);
            $em->persist($frequencyfieldvalue);
            $em->persist($statusfield);
            $em->persist($covfield);
            $em->persist($event);
            try {
                $em->flush();
                if ($messages != null) {
                    $messages['success'][] = "Changement de couverture de la fréquence " . $frequency->getValue() . " enregistré.";
                }
            } catch (\Exception $e) {
                if ($messages != null) {
                    $messages['error'][] = $e->getMessage();
                } else {
                    error_log($e->getMessage());
                }
            }
        } else {
            if ($messages != null) {
                $messages['error'][] = "Impossible de passer les couvertures en secours : aucune catégorie trouvée.";
            }
        }
    }

    /*
     * Add events from <code>$eauprsas</code> to the corresponding <code>$cat</code>
     * @param \Core\NMB2B\EAUPRSAs $eauprsas
     * @param \Application\Entity\MilCategory $cat
     */
    public function addZoneMilEvents(EAUPRSAs $eauprsas, \Application\Entity\MilCategory $cat, \Application\Entity\Organisation $organisation, \Core\Entity\User $user, &$messages = null)
    {
        foreach ($eauprsas->getAirspacesWithDesignator($cat->getFilter()) as $airspace) {
            $designator = (string) EAUPRSAs::getAirspaceDesignator($airspace);
            if (preg_match($cat->getZonesRegex(), $designator)) {
                $timeBegin = EAUPRSAs::getAirspaceDateTimeBegin($airspace);
                $timeEnd = EAUPRSAs::getAirspaceDateTimeEnd($airspace);
                $lowerlevel = (string) EAUPRSAs::getAirspaceLowerLimit($airspace);
                $upperlevel = (string) EAUPRSAs::getAirspaceUpperLimit($airspace);
                $previousEvents = $this->findZoneMilEvent($designator, $timeBegin, $timeEnd, $upperlevel, $lowerlevel);
                // si aucun evt pour la même zone (= même nom, même niveaux) existe ou inclus le nouvel evt
                // on en crée un nouveau
                if (count($previousEvents) == 0) {
                    $this->doAddMilEvent($cat, $organisation, $user, $designator, $timeBegin, $timeEnd, $upperlevel, $lowerlevel, $messages);
                }
            }
        }
    }

    private function doAddMilEvent(\Application\Entity\MilCategory $cat, \Application\Entity\Organisation $organisation, \Core\Entity\User $user, $designator, \DateTime $timeBegin, \DateTime $timeEnd, $upperLevel, $lowerLevel, &$messages)
    {
        $event = new \Application\Entity\Event();
        $event->setOrganisation($organisation);
        $event->setAuthor($user);
        $event->setCategory($cat);
        $event->setScheduled(false);
        $event->setPunctual(false);
        $event->setStartdate($timeBegin);
        $status = $this->getEntityManager()
            ->getRepository('Application\Entity\Status')
            ->find('1');
        $event->setStatus($status);
        $impact = $this->getEntityManager()
            ->getRepository('Application\Entity\Impact')
            ->find('2');
        $event->setImpact($impact);
        $event->setEnddate($timeEnd);
        // name
        $name = new \Application\Entity\CustomFieldValue();
        $name->setCustomField($cat->getFieldname());
        $name->setEvent($event);
        $name->setValue($designator);
        // upperlevel
        $upper = new \Application\Entity\CustomFieldValue();
        $upper->setCustomField($cat->getUpperLevelField());
        $upper->setEvent($event);
        $upper->setValue($upperLevel);
        // lowerlevel
        $lower = new \Application\Entity\CustomFieldValue();
        $lower->setCustomField($cat->getLowerLevelField());
        $lower->setEvent($event);
        $lower->setValue($lowerLevel);
        
        // recherche d'un modèle existant
        $models = $this->getEntityManager()
            ->getRepository('Application\Entity\PredefinedEvent')
            ->findBy(array(
            'name' => $designator,
            'organisation' => $organisation,
            'category' => $cat
        ));
        if (count($models) === 1) {
            $model = $models[0];
            // ajout des mémos
            foreach ($model->getChildren() as $child) {
                if ($child->getCategory() instanceof \Application\Entity\AlarmCategory) {
                    $alarm = new Event();
                    $alarm->setCategory($this->getEntityManager()
                        ->getRepository('Application\Entity\AlarmCategory')
                        ->findAll()[0]);
                    $alarm->setAuthor($user);
                    $alarm->setOrganisation($organisation);
                    $alarm->setParent($event);
                    $alarm->setStatus($status);
                    $alarm->setPunctual(true);
                    $alarm->setImpact($impact);
                    $startdate = $timeBegin;
                    $alarm->setStartdate($startdate);
                    
                    $namememo = new CustomFieldValue();
                    $namefield = $alarm->getCategory()->getNamefield();
                    $namememo->setCustomField($namefield);
                    $namememo->setValue($child->getCustomFieldValue($namefield)
                        ->getValue());
                    $namememo->setEvent($alarm);
                    $alarm->addCustomFieldValue($namememo);
                    $comment = new CustomFieldValue();
                    $commentfield = $alarm->getCategory()->getTextfield();
                    $comment->setCustomField($commentfield);
                    $comment->setValue($child->getCustomFieldValue($commentfield)
                        ->getValue());
                    $comment->setEvent($alarm);
                    $alarm->addCustomFieldValue($comment);
                    $deltabegin = new CustomFieldValue();
                    $beginfield = $alarm->getCategory()->getDeltaBeginField();
                    $deltabegin->setCustomField($beginfield);
                    $deltabegin->setValue($child->getCustomFieldValue($beginfield)
                        ->getValue());
                    $deltabegin->setEvent($alarm);
                    $alarm->addCustomFieldValue($deltabegin);
                    $deltaend = new CustomFieldValue();
                    $endfield = $alarm->getCategory()->getDeltaEndField();
                    $deltaend->setCustomField($endfield);
                    $deltaend->setValue($child->getCustomFieldValue($endfield)
                        ->getValue());
                    $deltaend->setEvent($alarm);
                    $alarm->addCustomFieldValue($deltaend);
                    $event->addChild($alarm);
                    $this->getEntityManager()->persist($namememo);
                    $this->getEntityManager()->persist($comment);
                    $this->getEntityManager()->persist($deltabegin);
                    $this->getEntityManager()->persist($deltaend);
                    $this->getEntityManager()->persist($alarm);
                }
            }
            $event->updateAlarms();
        }
        
        try {
            $this->getEntityManager()->persist($name);
            $this->getEntityManager()->persist($upper);
            $this->getEntityManager()->persist($lower);
            $this->getEntityManager()->persist($event);
            $this->getEntityManager()->flush();
        } catch (\Exception $ex) {
            error_log($ex->getMessage());
            if ($messages != null) {
                $messages['error'][] = $ex->getMessage();
            }
        }
    }

    /**
     * Tries to find an event called <code>$designator</code>
     * with same <code>$upperLevel</code> and <code>$lowerLevel</code>
     * and including <code>$timeBegin</code>, <code>$timeEnd</code>,
     * 
     * @param type $designator            
     * @param type $timeBegin            
     * @param type $timeEnd            
     */
    private function findZoneMilEvent($designator, $timeBegin, $timeEnd, $upperLevel, $lowerLevel)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select(array(
            'e',
            'v',
            'cat'
        ))
            ->from('Application\Entity\Event', 'e')
            ->leftJoin('e.custom_fields_values', 'v')
            ->leftJoin('e.category', 'cat')
            ->andWhere($qb->expr()
            ->eq('v.customfield', 'cat.fieldname'))
            ->andWhere('cat INSTANCE OF Application\Entity\MilCategory')
            ->andWhere($qb->expr()
            ->eq('v.value', '?1'))
            ->andWhere($qb->expr()
            ->andX($qb->expr()
            ->lte('e.startdate', '?2'), $qb->expr()
            ->gte('e.enddate', '?3')))
            ->setParameters(array(
            1 => $designator,
            2 => $timeBegin->format('Y-m-d H:i:s'),
            3 => $timeEnd->format('Y-m-d H:i:s')
        ));
        $tempresults = $qb->getQuery()->getResult();
        // then match lowerlimit and upper limit
        $results = array();
        foreach ($tempresults as $event) {
            $this->getEntityManager()->refresh($event);
            $lowerLevelMatch = false;
            $upperLevelMatch = false;
            // reload event because left joins stripped of events from some customfield values
            $tempevent = $this->getEntityManager()
                ->getRepository('Application\Entity\Event')
                ->find($event->getId());
            foreach ($tempevent->getCustomFieldsValues() as $value) {
                if ($value->getCustomField()->getId() == $tempevent->getCategory()
                    ->getLowerLevelField()
                    ->getId()) {
                    $lowerLevelMatch = (strcmp($value->getValue(), $lowerLevel) == 0);
                }
                if ($value->getCustomField()->getId() == $tempevent->getCategory()
                    ->getUpperLevelField()
                    ->getId()) {
                    $upperLevelMatch = (strcmp($value->getValue(), $upperLevel) == 0);
                }
            }
            if ($lowerLevelMatch && $upperLevelMatch) {
                $results[] = $tempevent;
            }
        }
        return $results;
    }

    /**
     * Sets read-only all events linked to the following resource
     * 
     * @param TemporaryResource $resource            
     */
    public function setReadOnly(TemporaryResource $resource)
    {
        $qbEvents = $this->getEntityManager()->createQueryBuilder();
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
            ->isNull('e.parent'));
        
        if ($resource instanceof Radar) {
            $qbEvents->andWhere($qbEvents->expr()
                ->andX($qbEvents->expr()
                ->eq('t.type', '?1'), $qbEvents->expr()
                ->eq('v.value', $resource->getId())));
            $qbEvents->setParameter('1', 'radar');
        } elseif ($resource instanceof Antenna) {
            $qbEvents->andWhere($qbEvents->expr()
                ->andX($qbEvents->expr()
                ->eq('t.type', '?1'), $qbEvents->expr()
                ->eq('v.value', $resource->getId())));
            $qbEvents->setParameter('1', 'antenna');
        }
        
        $query = $qbEvents->getQuery();
        $events = $query->getResult();
        
        foreach ($events as $event) {
            $event->setReadOnly(true);
            if (! $event->isPunctual() && $event->getEnddate() === null) {
                $statusClosed = $this->getEntityManager()
                    ->getRepository('Application\Entity\Status')
                    ->find('3');
                $now = new \DateTime('now');
                $now->setTimezone(new \DateTimeZone('UTC'));
                $event->close($statusClosed, $now);
            }
            $this->getEntityManager()->persist($event);
        }
        try {
            $this->getEntityManager()->flush();
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }
}
