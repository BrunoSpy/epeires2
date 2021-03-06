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

use Application\Entity\ActionCategory;
use Application\Entity\AlarmCategory;
use Application\Entity\ATFCMCategory;
use Application\Entity\Category;
use Application\Entity\CustomFieldValue;
use Application\Entity\Event;
use Application\Entity\Frequency;
use Application\Entity\FrequencyCategory;
use Application\Entity\Sector;
use Application\Entity\Stack;
use Application\Entity\SwitchObjectCategory;
use Application\Entity\Tab;
use Core\Entity\TemporaryResource;
use Application\Entity\SwitchObject;
use Application\Entity\Antenna;

use Core\Entity\User;

use DSNA\NMB2BDriver\Models\EAUPRSAs;
use DSNA\NMB2BDriver\Models\Regulation;
use LmcUser\Controller\Plugin\LmcUserAuthentication;

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
     * @param lmcUserAuthentication $userauth
     * @param \DateTime $day
     *            If null : use current day
     * @param \DateTime $end
     * @param \DateTime $lastmodified
     *            If not null : only events modified since <code>$lastmodified</code>
     * @param boolean $orderbycat
     * @param array $cats
     * @param array $status If $cats != null, restrict events status
     * @param boolean $default If default tab, take into account cat.timelineconfirmed parameter
     * @param string $zoneSession Unused parameter, to be cleaned
     * @return array
     * @throws \Exception
     */
    public function getEvents($userauth,
                              $day = null,
                              $end = null,
                              $lastmodified = null,
                              $orderbycat = false,
                              $cats = null,
                              $status = null,
                              $default = null,
                              $zoneSession = null)
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
            ->andWhere($qb->expr()->orX(
                $qb->expr()->isNull('e.parent'),
                $qb->expr()->isInstanceOf('c', 'Application\Entity\SwitchObjectCategory')
                )
            ); // display only root events if not switch object
        
        if ($cats) {
            $qb->andWhere($qb->expr()
                ->in('e.category', $cats));
            if($status) {
                $qb->andWhere($qb->expr()->in('e.status', $status));
            }
        }
        if ($default) {
            // restriction éventuelle aux événements confirmés si timelineconfirmed
            $qb->andWhere($qb->expr()
                ->orX($qb->expr()
                    ->neq('c.timelineconfirmed', true), $qb->expr()
                    ->neq('e.scheduled', true), $qb->expr()
                    ->andX($qb->expr()
                        ->eq('c.timelineconfirmed', true), $qb->expr()
                        ->in('e.status', array(2,3,4,5)), $qb->expr()
                        ->eq('e.scheduled',true)
                    )
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
            if ($day) {
                if($end == null) {
                    // restriction aux evts intersectant le jour spécifié
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
                    //catégories non archivés ce jour
                    $qb->andWhere(
                        $qb->expr()->orX(
                            $qb->expr()->eq('c.archived', 0),
                            $qb->expr()->andX(
                                $qb->expr()->eq('c.archived', true),
                                $qb->expr()->gt('c.archiveDate', '?3')
                            )
                        )
                    );
                    $parameters[1] = $daystart;
                    $parameters[2] = $dayend;
                    $parameters[3] = $daystart;
                    $qb->setParameters($parameters);
                } else {
                    $daystart = new \DateTime($day);
                    $daystart->setTime(0, 0, 0);
                    $daystart = $daystart->format("Y-m-d H:i:s");
                    $dayend = new \DateTime($end);
                    $dayend->setTime(23, 59, 59);
                    $dayend = $dayend->format("Y-m-d H:i:s");
                    //tous les évènements intersectant la période
                    $qb->andWhere($qb->expr()
                    ->orX($qb->expr() // sans date de fin et non ponctuel
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
                    //toutes les catégories non archivées
                    $qb->andWhere(
                        $qb->expr()->orX(
                            $qb->expr()->eq('c.archived', 0),
                            $qb->expr()->andX(
                                $qb->expr()->eq('c.archived', true),
                                $qb->expr()->gt('c.archiveDate', '?3')
                            )
                        )
                    );
                    $parameters[1] = $daystart;
                    $parameters[2] = $dayend;
                    $parameters[3] = $dayend;
                    $qb->setParameters($parameters);
                }
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
                //catégories non archivées
                $qb->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->eq('c.archived', 0),
                        $qb->expr()->andX(
                            $qb->expr()->eq('c.archived', true),
                            $qb->expr()->gt('c.archiveDate', '?3')
                        )
                    )
                );
                $parameters[1] = $start->format("Y-m-d H:i:s");
                $parameters[2] = $end->format("Y-m-d H:i:s");
                $now->setTime(0,0,0);
                $parameters[3] = $now->format("Y-m-d H:i:s");
                $qb->setParameters($parameters);
            }
        }
        
        // filtre par zone
        if ($userauth && $userauth->hasIdentity()) {
            // on filtre soit par la valeur en session soit par l'organisation de l'utilisateur
            // TODO gérer les evts partagés
            if ($zoneSession != null) { // application d'un filtre géographique
                if ($zoneSession != '0') {
                    // la variable de session peut contenir soit une orga soit une zone
                    $orga = $this->getEntityManager()
                        ->getRepository('Application\Entity\Organisation')
                        ->findOneBy(array(
                        'shortname' => $zoneSession
                    ));
                    if ($orga) {
                        $qb->andWhere($qb->expr()
                            ->eq('e.organisation', $orga->getId()));
                    } else {
                        $zone = $this->getEntityManager()
                            ->getRepository('Application\Entity\QualificationZone')
                            ->findOneBy(array(
                            'shortname' => $zoneSession
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
        
        return $this->restrictToReadableEvents($events, $userauth);
    }

    private function restrictToReadableEvents($events, $userauth) {
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
     * @param lmcUserAuthentication $user
     * @param \DateTime $start
     * @param \DateTime $end
     * @param boolean $exclude
     * @param array $status
     * @return array|mixed
     */
    public function getAllEvents($user, $start, $end, $exclude = false, $status = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select(array(
            'e, c'
        ))
            ->from('Application\Entity\Event', 'e')
            ->innerJoin('e.category', 'c')
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
        //restriction sur le statut
        if($status) {
            $qb->andWhere($qb->expr()->in('e.status', $status));
        }
        //exclusion des catégories pour rapport IPO
        if($exclude) {
            $qb->andWhere($qb->expr()->eq('c.exclude', '?3'));
            $parameters[3] = false;
        }
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
     * @param lmcUserAuthentication $user
     * @return array
     */
    public function getCurrentImportantEvents($user) {
        $qb = $this->getQueryEvents();
        $qb->andWhere($qb->expr()->eq('e.star', 'true'));
        if ($user !== null && $user->hasIdentity()) {
            $org = $user->getIdentity()->getOrganisation();

            $qb->andWhere($qb->expr()
                ->eq('e.organisation', $org->getId()));
        }
        return $this->restrictToReadableEvents($qb->getQuery()->getResult(), $user);
    }

    /**
     * Tous les évènements en cours concernant la catégorie <code>$category</code>
     * @param string $category
     * @return mixed
     * @throws \Exception
     */
    public function getCurrentEvents($category)
    {
        $now = new \DateTime('NOW');
        $now->setTimezone(new \DateTimeZone("UTC"));
        //on ajoute qq secondes pour éviter les effets de bords
        //notamment les requêtes qui suivent immédiatementla cloture d'un évènement
        $now->add(new \DateInterval('PT10S'));
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
        $qbEvents->andWhere($qbEvents->expr()->eq('cat.archived', 'false'));
        $query = $qbEvents->getQuery();
        
        return $query->getResult();
    }

    //TODO
    public function getRadarEvents()
    {
        $qbEvents = $this->getQueryEvents();
        $qbEvents->andWhere('cat INSTANCE OF Application\Entity\SwitchObjectCategory');
        $qbEvents->andWhere($qbEvents->expr()->eq('cat.archived', 'false'));
        
        $query = $qbEvents->getQuery();
        
        return $query->getResult();
    }

    public function getAfisEvents()
    {
        $qbEvents = $this->getQueryEvents();
        $qbEvents->andWhere('cat INSTANCE OF Application\Entity\AfisCategory');
        
        $query = $qbEvents->getQuery();
        
        return $query->getResult();
    }

    public function getCurrentRegulations($user) {
        $qb = $this->getQueryEvents();
        $qb->andWhere('cat INSTANCE OF Application\Entity\ATFCMCategory');
        if ($user !== null && $user->hasIdentity()) {
            $org = $user->getIdentity()->getOrganisation();

            $qb->andWhere($qb->expr()
                ->eq('e.organisation', $org->getId()));
        }
        return $this->restrictToReadableEvents($qb->getQuery()->getResult(), $user);
    }

    /**
     * Tous les évènements de type plan de vol entre deux dates paramétrables.
     *
     * @param \DateTime $start
     * @param \DateTime $end
     * @param array $status
     * @return array
     * @throws \Exception
     */
    public function getFlightPlanEvents($start=null, $end=null, $status=[1,2,3])
    {

        if ($start == null && $end == null) {
            $start = (new \DateTime())->setTime(0,0,0);
            $end = (new \DateTime())->setTime(0,0,0)->add(new \DateInterval('P1D'));
        }

        $qbEvents = $this->getEntityManager()->createQueryBuilder();
        $qbEvents->select(array(
            'e',
            'cat'
        ))
            ->from('Application\Entity\Event', 'e')
            ->innerJoin('e.category', 'cat')
            ->andWhere('cat INSTANCE OF Application\Entity\FlightPlanCategory')
            ->andWhere(
                $qbEvents->expr()->andX(
                    $qbEvents->expr()->gte('e.startdate', '?1'), 
                    $qbEvents->expr()->lte('e.startdate', '?2')
                )
            )
            ->andWhere($qbEvents->expr()->in('e.status', '?3'))
            ->setParameters([
                1 => $start,
                2 => $end,
                3 => $status
            ]);
        
        return $qbEvents->getQuery()->getResult();
    }

    /**
     * Tous les évènements courants de type plan d'interrogation.
     *
     * @param
     * @return array
     */
    public function getCurrentIntPlanEvents()
    {
        $qbEvents = $this->getQueryEvents();
        $qbEvents
            ->andWhere('cat INSTANCE OF Application\Entity\InterrogationPlanCategory')
            ->andWhere($qbEvents->expr()->in('e.status', '?4'))
            ->setParameter(4, [1, 2]);
        
        return $qbEvents->getQuery()->getResult();
    }

    /**
     * Tous les évènements terminés de type plan d'interrogation.
     *
     * @param
     * @return array
     */
    public function getEndedIntPlanEvents()
    {
        $qbEvents = $this->getEntityManager()->createQueryBuilder();
        $qbEvents->select(array(
            'e',
            'cat'
        ))
            ->from('Application\Entity\Event', 'e')
            ->innerJoin('e.category', 'cat')
            ->andWhere('cat INSTANCE OF Application\Entity\InterrogationPlanCategory')
            ->andWhere($qbEvents->expr()
                ->in('e.status', '?1'))
            ->setParameter(1, 3);
        
        return $qbEvents->getQuery()->getResult();
    }

    /**
     * Tous les évènements en cours pour un onglet
     * 
     * @param Tab $tab
     * @return array
     */
    public function getTabEvents(Tab $tab, $userauth)
    {
        $qbEvents = $this->getQueryEvents();
        $catsid = array();
        foreach ($tab->getCategories() as $cat) {
            if(!$cat->isArchived()){
                $catsid[] = $cat->getId();
            }
        }
        $qbEvents->andWhere($qbEvents->expr()
            ->in('cat.id', '?4'))
            ->setParameter(4, $catsid);
        $query = $qbEvents->getQuery();
        return $this->restrictToReadableEvents($query->getResult(), $userauth);
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

    public function getCurrentEventsCategory($catName) {
        $qb = $this->getQueryEvents();
        $qb->andWhere($qb->expr()->eq('cat.name', '?3'))
        ->setParameter(3, $catName);
        return $qb->getQuery()->getResult();
    }
    
    public function getFutureEventsCategory($catname) {
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
                ->gte('e.startdate','?2'))
            ->andWhere($qbEvents->expr()
                ->eq('cat.name', '?3'))
            ->setParameters(array(
                1 => array(
                    1,
                    2,
                    3
                ),
                2 => $now->format('Y-m-d H:i:s'),
                3 => $catname
            ));
        return $qbEvents->getQuery()->getResult();
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
    
    /**
     * @param Antenna $antenna
     * @param boolean $state
     * @param User $author
     * @param mixed $frequencies frequency or array of frequencies
     * @param array $messages
     */
    public function addSwitchAntennaStateEvent(Antenna $antenna, $state, User $author, $frequencies = null, &$messages = null)
    {
        $em = $this->getEntityManager();
        $now = new \DateTime('NOW');
        $now->setTimezone(new \DateTimeZone("UTC"));
        $statusClose = $em->getRepository('Application\Entity\Status')->find('3');
        $statusConfirme = $em->getRepository('Application\Entity\Status')->find('2');
        $impact = $em->getRepository('Application\Entity\Impact')->find('3');
        //récupération des évènements antennes existants
        $events = $em->getRepository('Application\Entity\Event')->getCurrentEvents('Application\Entity\AntennaCategory');
        // on récupère les évènements de l'antenne
        $antennaEvents = array();
        foreach ($events as $event) {
            $antennafield = $event->getCategory()->getAntennafield();
            $antennafieldvalue = $event->getCustomFieldValue($antennafield);
            if($antennafieldvalue !== null && $antennafieldvalue->getValue() == $antenna->getId()) {
                $antennaEvents[] = $event;
            }
        }
        if(count($antennaEvents) == 0) {
            if($state) {
                $messages['error'][] = "Erreur : aucun évènement concernant cette antenne en base de données.";
            } else {
                $categories = $this->getEntityManager()
                    ->getRepository('Application\Entity\AntennaCategory')
                    ->findBy(array(
                        'defaultantennacategory' => true
                    ));
                if(count($categories) !== 1) {
                    $messages['error'][] = "Aucune catégorie Antenne par défaut configurée : contactez votre administrateur.";
                } else {
                    $cat = $categories[0];
                    //création nouvel évènement
                    $event = new Event();
                    $event->setAuthor($author);
                    $event->setStatus($statusConfirme);
                    $event->setImpact($impact);
                    $event->setCategory($cat);
                    $event->setOrganisation($antenna->getOrganisation()); //TODO il faudra gérer une antenne partagée : créer autant d'évènements ?? ou OneToMany ?
                    $event->setPunctual(false);
                    $event->setStartdate($now);
                    $antennafieldvalue = new CustomFieldValue();
                    $antennafieldvalue->setCustomField($cat->getAntennaField());
                    $antennafieldvalue->setValue($antenna->getId());
                    $antennafieldvalue->setEvent($event);
                    $event->addCustomFieldValue($antennafieldvalue);
                    $statusvalue = new CustomFieldValue();
                    $statusvalue->setCustomField($cat->getStateField());
                    $statusvalue->setValue(true); //state = false -> indisponible = true
                    $statusvalue->setEvent($event);
                    $event->addCustomFieldValue($statusvalue);
                    //passage en couv secours pour les fréquences sur couv normale
                    if($frequencies) {
                        //sous-ensemble impacté
                        $freqids = "";
                        if(!is_array($frequencies)) {
                            // changement sur une seule fréquence
                            $frequencies = array($frequencies);
                        }
                        foreach ($frequencies as $freq) {
                            if($freq->hasAntenna($antenna)) {
                                $freqids .= (string) $freq->getId() . "\r";
                            }
                            if($freq->hasMainAntenna($antenna) || $freq->hasMainClimaxAntenna($antenna)) {
                                $this->addChangeFrequencyCovEvent(
                                    $freq,
                                    1,
                                    0,
                                    "Antenne principale indisponible",
                                    $now,
                                    null,
                                    $author,
                                    $event,
                                    $messages
                                );
                            }
                        }
                        $freqids = trim($freqids);
                        if(strlen($freqids) > 0) {
                            $freqvalue = new CustomFieldValue();
                            $freqvalue->setCustomField($cat->getFrequenciesField());
                            $freqvalue->setValue($freqids);
                            $freqvalue->setEvent($event);
                            $event->addCustomFieldValue($freqvalue);
                            $this->getEntityManager()->persist($freqvalue);
                        } else {
                            $messages['error'][] = "Aucune fréquence n'appartient à l'antenne : abandon.";
                            return;
                        }
                    } else {
                        //toutes les fréquences impactées
                        foreach ($antenna->getMainfrequencies() as $frequency) {
                            $this->addChangeFrequencyCovEvent(
                                $frequency,
                                1, // couv secours
                                0, // toujours dispo
                                "Antenne principale indisponible",
                                $now,
                                null,
                                $author,
                                $event,
                                $messages
                            );
                        }
                        foreach ($antenna->getMainfrequenciesclimax() as $frequency) {
                            $this->addChangeFrequencyCovEvent(
                                $frequency,
                                1, // couv secours
                                0, // toujours dispo
                                "Antenne principale indisponible",
                                $now,
                                null,
                                $author,
                                $event,
                                $messages
                            );
                        }
                        //TODO ajout de la fiche réflexe dans tous les cas ou seulement pour l'antenne complètement en panne ?
                        
                        // création de la fiche réflexe
                        if ($antenna->getModel()) {
                            foreach ($this->getEntityManager()->getRepository('Application\Entity\PredefinedEvent')->findBy(array(
                                'parent' => $antenna->getModel()
                                    ->getId()
                            )) as $action) {
                                $child = new Event();
                                $child->setParent($event);
                                $child->setAuthor($event->getAuthor());
                                $child->setOrganisation($event->getOrganisation());
                                $child->createFromPredefinedEvent($action);
                                $child->setStatus($this->getEntityManager()->getRepository('Application\Entity\Status')
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
                                    $this->getEntityManager()->persist($newvalue);
                                }
                                $child->updateAlarmDate();
                                $this->getEntityManager()->persist($child);
                            }
                            // ajout des fichiers
                            foreach ($antenna->getModel()->getFiles() as $file) {
                                $file->addEvent($event);
                            }
                        }
                    }
                    try {
                        $event->updateAlarms();
                        $this->getEntityManager()->persist($event);
                        $this->getEntityManager()->flush();
                        $messages['success'][] = "Nouvel évènement antenne créé.";
                    } catch (\Exception $e){
                        $messages['error'][] = $e->getMessage();
                    }
                }
            }
        } else if(count($antennaEvents) == 1) {
            $event = $antennaEvents[0];
            if($frequencies) {
                //calcul de l'ensemble des fréquences impactées
                $initialFreqIds = array();
                $freqidEventValue = $event->getCustomFieldValue($event->getCategory()
                    ->getFrequenciesField());
                if ($freqidEventValue) {
                    $initialFreqIds = explode("\r", $freqidEventValue->getValue());
                } else {
                    //pas de champ = toutes
                    foreach ($antenna->getAllFrequencies() as $f) {
                        $initialFreqIds[] = $f->getId();
                    }
                }
                $newFreqIds = array();
                if(!is_array($frequencies)) {
                    //changement sur une seule fréquence
                    // -> changement via freq widget
                    // -> delta à faire
                    if($frequencies->hasAntenna($antenna)) {
                        if ($state) {
                            $newFreqIds = array_diff($initialFreqIds, array($frequencies->getId()));
                        } else {
                            if (!in_array($frequencies->getId(), $initialFreqIds)) {
                                $newFreqIds = array_merge($initialFreqIds, array($frequencies->getId()));
                            }
                        }
                    }
                } else {
                    //array = passage de la liste exacte des fréquences impactées
                    foreach ($frequencies as $f){
                        if($f->hasAntenna($antenna)) {
                            $newFreqIds[] = $f->getId();
                        }
                    }
                }
                if(count($newFreqIds) == 0) {
                    //plus de fréquence impactée -> antenne ok -> on termine tout
                    $this->closeAntennaEvent($event, $statusClose, $now, $messages);
                } else {
                    $freqsOK = array_diff($initialFreqIds, $newFreqIds);
                    foreach ($freqsOK as $f) {
                        //passage en couv normale si besoin
                        $frequency = $this->getEntityManager()->getRepository('Application\Entity\Frequency')->find($f);
                        if($frequency && ($frequency->hasMainAntenna($antenna) || $frequency->hasMainClimaxAntenna($antenna))) {
                            $this->switchFrequencyCov($frequency, 0, "", $author, $event, $messages);
                        }
                    }
                    $newFreqsImpacted = array_diff($newFreqIds, $initialFreqIds);
                    //passage en couv secours si besoin
                    foreach ($newFreqsImpacted as $f) {
                        $frequency = $this->getEntityManager()->getRepository('Application\Entity\Frequency')->find($f);
                        if($frequency && ($frequency->hasMainAntenna($antenna) || $frequency->hasMainClimaxAntenna($antenna))) {
                            $this->switchFrequencyCov($frequency, 1, "Antenne principale HS", $author, $event, $messages);
                        }
                    }
                    //mise à jour du champ
                    $freqValue = "";
                    foreach ($newFreqIds as $f) {
                        $freqValue .= $f . "\r";
                    }
                    $freqValue = trim($freqValue);
                    if(!$freqidEventValue) {
                        $freqidEventValue = new CustomFieldValue();
                        $freqidEventValue->setEvent($event);
                        $freqidEventValue->setCustomField($event->getCategory()->getFrequenciesField());
                    }
                    $freqidEventValue->setValue($freqValue);
                    $this->getEntityManager()->persist($freqidEventValue);
                    $this->getEntityManager()->persist($event);
                    try {
                        $this->getEntityManager()->flush();
                    } catch(\Exception $e) {
                        $messages['error'][] = $e->getMessage();
                    }
                }
            } else {
                if(!$state) {
                    // pas de fréquence spécifiée -> on ajoute toutes les fréquences
                    $freqidEventValue = $event->getCustomFieldValue($event->getCategory()
                        ->getFrequenciesField());
                    $freqs = $antenna->getAllFrequencies();
                    if($freqidEventValue) {
                        $value = "";
                        foreach ($freqs as $f) {
                            $value .= $f->getId() . "\r";
                        }
                        $freqidEventValue->setValue(trim($value));
                        $this->getEntityManager()->persist($freqidEventValue);
                    } else {
                        // rien à faire
                    }
                    //passage en couv secours si besoin
                    foreach ($freqs as $f) {
                        if($f->hasMainAntenna() || $f->hasMainClimaxAntenna()) {
                            $this->switchFrequencyCov(
                                $f,
                                1,
                                "Antenne principale indisponible",
                                $author,
                                $event,
                                $messages
                            );
                        }
                    }
                    //TODO ajout de la fiche réflexe ???
                    $this->getEntityManager()->persist($event);
                    try {
                        $this->getEntityManager()->flush();
                        $messages['success'][] = "Antenne correctement mise à jour";
                    } catch(\Exception $e) {
                        $messages['error'][] = $e->getMessage();
                    }
                } else {
                    //pas de fréquence spécifiée = toutes -> on termine tout
                    $this->closeAntennaEvent($event, $statusClose, $now, $messages);
                }
            }
        } else {
            $messages['error'][] = "Erreur : plusieurs évènements en cours sur cette antenne : impossible de déterminer lequel modifier.";
        }
    }
    
    /**
     * @param Frequency $frequency
     * @param $cov 0 = normale, 1 = secours
     * @param $cause
     * @param $author
     * @param $event
     * @param $messages
     */
    public function switchFrequencyCov(Frequency $frequency, $cov, $cause, $author, $event, &$messages)
    {
        $frequencyevents = $this->getFrequencyEvents($frequency);
        $now = new \DateTime('NOW');
        $now->setTimezone(new \DateTimeZone("UTC"));
        if ($cov == 0 && count($frequencyevents) == 0) {
            // cas impossible
            // impossible de repasser en couv normale si il n'existe aucun evt
            $messages['error'][] = "Impossible de passer en couverture normale : pas d'évènement précédent trouvé.";
        } else
            if ($cov == 1 && (count($frequencyevents) == 0 || (count($frequencyevents) == 1 && $frequencyevents[0]->getParent() != null))) {
                // passage en couv secours
                // création d'un nouvel evt si :
                // - 0 evt en cours
                // - 1 evt en cours mais avec parent, pour éviter fermeture inopinée
                // création d'un nouvel évènement
                $this->addChangeFrequencyCovEvent(
                    $frequency,
                    $cov,
                    false, // sur un changement de couverture, la fréquence reste disponible
                    $cause,
                    $now,
                    null,
                    $author,
                    $event,
                    $messages
                );
            } else {
                // reste autre les cas count > 0
                foreach ($frequencyevents as $freqEvent) {
                    $statefield = $freqEvent->getCustomFieldValue($freqEvent->getCategory()
                        ->getStateField());
                    $state = ($statefield == null ? null : $statefield->getValue());
                    $antennafield = $freqEvent->getCustomFieldValue($freqEvent->getCategory()
                        ->getCurrentAntennaField());
                    $antenna = ($antennafield == null ? null : $antennafield->getValue());
                    $otherfreqfield = $freqEvent->getCustomFieldValue($freqEvent->getCategory()
                        ->getOtherFrequencyField());
                    $otherfreq = ($otherfreqfield == null ? null : $otherfreqfield->getValue());
                    if (($otherfreq == null || $otherfreq == 0) && // pas sur une autre freq
                        ($state == null || $state == false) && // disponible
                        $antenna != null && $antenna == 1 && // sur couv secours
                        $cov == 0) { // prochaine couv : normale
                        // retour en couv normale
                        // les autres champs sont vides -> fermeture
                        $freqEvent->close($this->getEntityManager()->getRepository('Application\Entity\Status')
                            ->find(3), $now);
                    } else {
                        // on met à jour le champ correspondant
                        if ($antennafield == null) {
                            $antennafield = new CustomFieldValue();
                            $antennafield->setEvent($freqEvent);
                            $antennafield->setCustomField($freqEvent->getCategory()
                                ->getCurrentAntennaField());
                        }
                        $antennafield->setValue($cov);
                        $this->getEntityManager()->persist($antennafield);
                    }
                    $this->getEntityManager()->persist($freqEvent);
                }
            }
        try {
            $this->getEntityManager()->flush();
            $messages['success'][] = 'Evènement correctement mis à jour.';
        } catch (\Exception $ex) {
            $messages['error'][] = $ex->getMessage();
        }
    }
    
    /**
     * @param Frequency $frequency
     * @return array
     */
    public function getFrequencyEvents(Frequency $frequency) {
        $events = $this->getCurrentEvents('Application\Entity\FrequencyCategory');
        $freqEvents = array();
        foreach ($events as $e){
            $freqField = $e->getCategory()->getFrequencyField();
            $freqFieldValue = $e->getCustomFieldValue($freqField);
            if($freqFieldValue && $freqFieldValue->getValue() == $frequency->getId()) {
                $freqEvents[] = $e;
            }
        }
        return $freqEvents;
    }
    
    private function closeAntennaEvent(Event $event, $status, $now, &$message) {
        $event->setStatus($status);
        // ferme evts fils de type frequencycategory
        foreach ($event->getChildren() as $child) {
            if ($child->getCategory() instanceof FrequencyCategory) {
                $child->setEnddate($now);
                $child->setStatus($status);
                $this->getEntityManager()->persist($child);
            }
        }
        $event->setEnddate($now);
        $this->getEntityManager()->persist($event);
        try {
            $this->getEntityManager()->flush();
            $messages['success'][] = "Evènement antenne correctement terminé.";
        } catch (\Exception $e) {
            $messages['error'][] = $e->getMessage();
        }
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
     * @param string $messages
     * @throws \Doctrine\ORM\ORMException
     */
    public function addSwitchFrequencyEvent(Frequency $from, Frequency $to, User $author, Event $parent = null, &$messages = null)
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
     * @param string $cov Value for the current antenna field
     * @param string $freqstatus Value for the current frequency state field
     * @param string $cause Valeur du champ cause
     * @param Event $parent
     * @param \DateTime $startdate
     * @param User $author
     * @param string $messages
     * @throws \Doctrine\ORM\ORMException
     */
    public function addChangeFrequencyCovEvent(
        Frequency $frequency,
        $cov,
        $freqstatus,
        $cause,
        \DateTime $startdate,
        $enddate,
        \Core\Entity\User $author,
        Event $parent = null,
        &$messages = null
    ) {
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
        $event->setEnddate($enddate);
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
            $causefield = new CustomFieldValue();
            $causefield->setCustomField($cat->getCauseField());
            $causefield->setEvent($event);
            $causefield->setValue($cause);
            $event->addCustomFieldValue($causefield);
            $em->persist($event);
            try {
                $em->flush();
                if ($messages != null) {
                    $messages['success'][] = "Changement de couverture de la fréquence " . $frequency->getValue() . " enregistré.";
                }
            } catch (\Exception $e) {
                error_log($e->getMessage());
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
     * @param EAUPRSAs $eauprsas
     * @param \Application\Entity\MilCategory $cat
     */
    public function addZoneMilEvents(EAUPRSAs $eauprsas, \Application\Entity\MilCategory $cat, \Application\Entity\Organisation $organisation, \Core\Entity\User $user, &$messages = null)
    {
        $addedEvents = 0;
        $zones = array();
        foreach ($eauprsas->getAirspacesWithDesignator($cat->getFilter()) as $airspace) {
            $designator = $airspace->getDesignator();
            if (preg_match($cat->getZonesRegex(), $designator)) {
                $timeBegin = $airspace->getDateTimeBegin();
                $timeEnd = $airspace->getDateTimeEnd();
                $lowerlevel = $airspace->getLowerLimit();
                $upperlevel = $airspace->getUpperLimit();

                $zone = array($designator, $timeBegin, $timeEnd, $lowerlevel, $upperlevel);
                $zones[] = $zone;
            }
        }

        //merge zones
        $zoneMerged = true;
        //à chaque fois qu'une zone est fusionnée, on repasse le tableau à la recherche d'une correspondance
        //on s'arrête quand plus aucune zone n'a été fusionnée
        while($zoneMerged) {
            $zoneMerged = false;
            for($i = 0, $size = count($zones); $i < $size; ++$i) {
                $zoneRef = $zones[$i];
                for($j = 0, $size2 = count($zones); $j < $size2; ++$j) {
                    if($j == $i)
                        continue;
                    $zoneComp = $zones[$j];

                    if(strcmp($zoneRef[0], $zoneComp[0]) == 0) {
                        if($zoneRef[1] == $zoneComp[1] && $zoneRef[2] == $zoneComp[2]) {
                            //même heures, on check les niveaux
                            if(strcmp($zoneRef[4], $zoneComp[3]) == 0){
                                $zones[$i][4] = $zoneComp[4];
                                array_splice($zones, $j, 1);
                                $zoneMerged = true;
                                break 2;
                            }
                            if(strcmp($zoneRef[3], $zoneComp[4]) == 0) {
                                $zones[$i][3] = $zoneComp[3];
                                array_splice($zones, $j, 1);
                                $zoneMerged = true;
                                break 2;
                            }
                        } else if(strcmp($zoneRef[3], $zoneComp[3]) == 0 && strcmp($zoneRef[4], $zoneComp[4]) == 0) {
                            //mêmes niveaux, on check les heures
                            if($zoneRef[1] == $zoneComp[2]){
                                //fusion, on mets à jour le tableau et on recommence à zéro
                                $zones[$i][1] = $zoneComp[1];
                                array_splice($zones, $j, 1);
                                $zoneMerged = true;
                                break 2;
                            }
                            if($zoneRef[2] == $zoneComp[1]) {
                                $zones[$i][2] = $zoneComp[2];
                                array_splice($zones, $j, 1);
                                $zoneMerged = true;
                                break 2;
                            }
                        }

                    }
                }
            }
        }

        //add events
        foreach ($zones as $zone){
            $designator = $zone[0];
            $timeBegin = $zone[1];
            $timeEnd = $zone[2];
            $lowerlevel = $zone[3];
            $upperlevel = $zone[4];
            $previousEvents = $this->findZoneMilEvent($designator, $timeBegin, $timeEnd, $upperlevel, $lowerlevel);
            // si aucun evt pour la même zone (= même nom, même niveaux) existe ou inclus le nouvel evt
            // on en crée un nouveau
            if (count($previousEvents) == 0) {
                $this->doAddMilEvent($cat, $organisation, $user, $designator, $timeBegin, $timeEnd, $upperlevel, $lowerlevel, $messages);
                $addedEvents++;
            }
        }

        return $addedEvents;
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

        $this->importModel($event, $user, $timeBegin, $designator, $organisation, $cat);
        
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
     * @param string $designator
     * @param \DateTime $timeBegin
     * @param \DateTime $timeEnd
     * @param string $upperLevel
     * @param string $lowerLevel
     * @return array
     * @throws \Doctrine\ORM\ORMException
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
        
        if ($resource instanceof SwitchObject) {
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
        } elseif ($resource instanceof Stack) {
            $qbEvents->andWhere($qbEvents->expr()
                ->andX($qbEvents->expr()
                    ->eq('t.type', '?1'), $qbEvents->expr()
                    ->eq('v.value', $resource->getId())));
            $qbEvents->setParameter('1', 'stack');
        } elseif ($resource instanceof Sector) {
            $qbEvents->andWhere($qbEvents->expr()
                ->andX($qbEvents->expr()
                    ->eq('t.type', '?1'), $qbEvents->expr()
                    ->eq('v.value', $resource->getId())));
            $qbEvents->setParameter('1', 'sector');
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

    /**
     * Renvoit un nouvel évènement créé à partir d'un évènement et d'une date de début
     * Les notes ne sont pas copiées, le statut est mis à "nouveau"
     * @param Event $event
     * @param \DateTime $start UTC start date
     */
    public function createFromEvent(Event $event, $start) {
        $newevent = new Event();
        $newevent->setAuthor($event->getAuthor());
        $newevent->setOrganisation($event->getOrganisation());
        $newevent->setCategory($event->getCategory());
        $newevent->setImpact($event->getImpact());
        $newevent->setStatus($this->getEntityManager()->getRepository('Application\Entity\Status')->find(1));
        if($event->getPlace() !== null) {
            $newevent->setPlace($event->getPlace());
        }
        //horaires
        $newevent->setScheduled($event->isScheduled());
        $newevent->setPunctual($event->isPunctual());
        if($start !== null) {//actions can have no start date
            $newevent->setStartdate($start);
            if (!$event->isPunctual() && $event->getEnddate() !== null) {
                $diff = $event->getStartdate()->diff($event->getEnddate());
                $end = clone $start;
                $end->add($diff);
                $newevent->setEnddate($end);
            }
        }
        //enfants
        foreach ($event->getChildren() as $child) {
            $childdate = $start;
            if($child->getCategory() instanceof AlarmCategory) {
                $diff = $event->getStartdate()->diff($child->getStartdate());
                $alarmdate = clone $newevent->getStartdate();
                $alarmdate->add($diff);
                $childdate = $alarmdate;
            } else if($child->getCategory() instanceof ActionCategory) {
                $childdate = null;
            }
            $childEvent = $this->createFromEvent($child, $childdate);
            $childEvent->setParent($newevent);
            $newevent->addChild($childEvent);
        }
        //champs
        foreach ($event->getCustomFieldsValues() as $customFieldsValue){
            $customFieldValue = new CustomFieldValue();
            $customFieldValue->setEvent($newevent);
            $customFieldValue->setCustomField($customFieldsValue->getCustomField());
            $customFieldValue->setValue($customFieldsValue->getValue());
            $newevent->addCustomFieldValue($customFieldValue);
        }
        //fichiers
        foreach ($event->getFiles() as $file) {
            $newevent->addFile($file);
        }
        return $newevent;
    }

    /**
     * Import data from a model, if one is found
     * @param Event $event
     * @param User $user
     * @param \DateTime $start
     * @param string $name
     * @param $organisation
     * @param Category $category
     * @throws \Doctrine\ORM\ORMException
     */
    private function importModel($event, $user, $start, $name, $organisation, $category)
    {
        $status = $this->getEntityManager()
            ->getRepository('Application\Entity\Status')
            ->find('1');
        $impact = $this->getEntityManager()
            ->getRepository('Application\Entity\Impact')
            ->find('2');
        // recherche d'un modèle existant
        $models = $this->getEntityManager()
            ->getRepository('Application\Entity\PredefinedEvent')
            ->findBy(array(
                'name' => $name,
                'organisation' => $organisation,
                'category' => $category
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
                    $startdate = $start;
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
            //ajout des fichiers
            foreach($model->getFiles() as $file) {
                $file->addEvent($event);
                $this->getEntityManager()->persist($file);
            }
            $event->updateAlarms();
        }
    }

    /**
     * @param $regulation
     * @param \Application\Entity\ATFCMCategory $category
     * @param $organisation
     * @param $user
     * @param \DateTime $day
     */
    public function addRegulation(Regulation $regulation, \Application\Entity\ATFCMCategory $category, $organisation, $user, $day, &$messages = null) {
        //first find if a regulation already exists
        $qb = $this->getEntityManager()->createQueryBuilder();

        // restriction aux evts intersectant le jour spécifié
        $daystart = clone $day;
        $daystart->setTime(0, 0, 0);
        $dayend = clone $day;
        $dayend->add(new \DateInterval('P1D'));
        $dayend->setTime(23, 59, 59);
        $daystart = $daystart->format("Y-m-d H:i:s");
        $dayend = $dayend->format("Y-m-d H:i:s");

        $internalid = $regulation->getDataId();

        $qb->select(array(
            'e',
            'v',
            'cat'
        ))
            ->from('Application\Entity\Event', 'e')
            ->leftJoin('e.custom_fields_values', 'v')
            ->leftJoin('e.category', 'cat')
            ->andWhere('cat INSTANCE OF Application\Entity\ATFCMCategory')
            ->andWhere($qb->expr()
                ->andX($qb->expr()
                    ->lte('e.startdate', '?2'), $qb->expr()
                    ->gte('e.enddate', '?1')))
            ->setParameters(array(
                1 => $daystart,
                2 => $dayend
            ));

        $results = $qb->getQuery()->getResult();

        $count = 0;

        $tempresults = array();

        foreach ($results as $e) {
            $this->getEntityManager()->refresh($e);
            $event = $this->getEntityManager()->getRepository(Event::class)->find($e->getId());
            if($event->getCustomFieldValue($category->getInternalId()) !== null && strcmp($event->getCustomFieldValue($category->getInternalId())->getValue(), $internalid) == 0) {
                $tempresults[] = $event;
            }
        }

        $results = $tempresults;

        if(count($results) == 0) {
            //do create a new event
            $this->doAddRegulationEvent(
                $category,
                $regulation->getRegulationName(),
                $regulation->getDataId(),
                $regulation->getDateTimeStart(),
                $regulation->getDateTimeEnd(),
                $regulation->getReason(),
                $regulation->getDescription(),
                $regulation->getNormalRates(),
                $regulation->getRegulationState(),
                $organisation,
                $user,
                $messages);
            $count++;
        } else {
            if(count($results) > 1) {
                //BUG
            } else {
                //update parameters
                $event = $results[0];
                $event->setStartdate($regulation->getDateTimeStart());
                $event->setEnddate($regulation->getDateTimeEnd());
                $reason = $event->getCustomFieldValue($category->getReasonField());
                if(!$reason) {
                    $reason = new CustomFieldValue();
                    $reason->setEvent($event);
                    $reason->setCustomField($category->getReasonField());
                }
                $reason->setValue($regulation->getReason());
                $description = $event->getCustomFieldValue($category->getDescriptionField());
                if(!$description) {
                    $description = new CustomFieldValue();
                    $description->setEvent($event);
                    $description->setCustomField($category->getDescriptionField());
                }
                $description->setValue($regulation->getDescription());
                $normalRate = $event->getCustomFieldValue($category->getNormalRateField());
                if(!$normalRate){
                    $normalRate = new CustomFieldValue();
                    $normalRate->setCustomField($category->getNormalRateField());
                    $normalRate->setEvent($event);
                }
                $normalRate->setValue($regulation->getNormalRates());
                $newRegulationState = $regulation->getRegulationState();
                $regulationState = $event->getCustomFieldValue($category->getRegulationStateField());
                if(!$regulationState) {
                    $regulationState = new CustomFieldValue();
                    $regulationState->setCustomField($category->getRegulationStateField());
                    $regulationState->setEvent($event);
                }
                $regulationState->setValue($newRegulationState);
                switch ($newRegulationState) {
                    case ATFCMCategory::APPLIED:
                    case ATFCMCategory::APPLYING:
                        $status = $this->getEntityManager()
                            ->getRepository('Application\Entity\Status')
                            ->find('2');
                        break;
                    case ATFCMCategory::CANCELLED:
                    case ATFCMCategory::CANCELLING:
                        $status = $this->getEntityManager()
                            ->getRepository('Application\Entity\Status')
                            ->find('4');
                        break;
                    case ATFCMCategory::TERMINATED:
                        $status = $this->getEntityManager()
                            ->getRepository('Application\Entity\Status')
                            ->find('3');
                        break;
                    default:
                        $status = $this->getEntityManager()
                            ->getRepository('Application\Entity\Status')
                            ->find('3');
                }
                $event->setStatus($status);
                try {
                    $this->getEntityManager()->persist($normalRate);
                    $this->getEntityManager()->persist($reason);
                    $this->getEntityManager()->persist($description);
                    $this->getEntityManager()->persist($regulationState);
                    $this->getEntityManager()->persist($event);
                    $this->getEntityManager()->flush();
                } catch (\Exception $e) {
                    error_log($e->getMessage());
                    if ($messages != null) {
                        $messages['error'][] = $e->getMessage();
                    }
                }
            }
        }
        return $count;
    }

    private function doAddRegulationEvent(
        ATFCMCategory $cat,
        $regulname,
        $internalidvalue,
        $timeBegin,
        $timeEnd,
        $reason,
        $description,
        $normalRate,
        $regulationState,
        $organisation,
        $user,
        &$messages)
    {
        $event = new Event();
        $event->setOrganisation($organisation);
        $event->setAuthor($user);
        $event->setCategory($cat);
        $event->setScheduled(false);
        $event->setPunctual(false);
        $event->setStartdate($timeBegin);
        $event->setReadOnly(true);
        switch ($regulationState) {
            case ATFCMCategory::APPLIED:
            case ATFCMCategory::APPLYING:
                $status = $this->getEntityManager()
                    ->getRepository('Application\Entity\Status')
                    ->find('2');
                break;
            case ATFCMCategory::CANCELLED:
            case ATFCMCategory::CANCELLING:
                $status = $this->getEntityManager()
                    ->getRepository('Application\Entity\Status')
                    ->find('4');
                break;
            case ATFCMCategory::TERMINATED:
                $status = $this->getEntityManager()
                    ->getRepository('Application\Entity\Status')
                    ->find('3');
                break;
            default:
                $status = $this->getEntityManager()
                    ->getRepository('Application\Entity\Status')
                    ->find('3');
        }
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
        $name->setValue($regulname);
        //internalid
        $internalid = new CustomFieldValue();
        $internalid->setCustomField($cat->getInternalId());
        $internalid->setEvent($event);
        $internalid->setValue($internalidvalue);
        //reason
        $reasonvalue = new CustomFieldValue();
        $reasonvalue->setCustomField($cat->getReasonField());
        $reasonvalue->setEvent($event);
        $reasonvalue->setValue($reason);
        //description
        $descriptionvalue = new CustomFieldValue();
        $descriptionvalue->setCustomField($cat->getDescriptionField());
        $descriptionvalue->setEvent($event);
        $descriptionvalue->setValue($description);
        //normalRate
        $normalRatevalue = new CustomFieldValue();
        $normalRatevalue->setCustomField($cat->getNormalRateField());
        $normalRatevalue->setEvent($event);
        $normalRatevalue->setValue($normalRate);
        //regulation state
        $regulationStateValue = new CustomFieldValue();
        $regulationStateValue->setCustomField($cat->getRegulationStateField());
        $regulationStateValue->setEvent($event);
        $regulationStateValue->setValue($regulationState);
        try {
            $this->importModel($event, $user, $timeBegin, $regulname, $organisation, $cat);
            $this->getEntityManager()->persist($name);
            $this->getEntityManager()->persist($descriptionvalue);
            $this->getEntityManager()->persist($reasonvalue);
            $this->getEntityManager()->persist($normalRatevalue);
            $this->getEntityManager()->persist($internalid);
            $this->getEntityManager()->persist($regulationStateValue);
            $this->getEntityManager()->persist($event);
            $this->getEntityManager()->flush();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            if ($messages != null) {
                $messages['error'][] = $e->getMessage();
            }
        }
    }


    /**
     * Find events matching a string and intersecting [$startdate, $enddate].
     * If $categories is null, search in all categories including archived ones.
     * @param $user
     * @param \DateTime $startdate
     * @param \DateTime $enddate
     * @param $search
     * @param null $categories
     * @param bool $onlytitle
     * @return array
     */
    public function searchEvents($user, \DateTime $startdate, \DateTime $enddate, $search, $categories = null, $onlytitle = false){
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select(array(
            'e', 'c', 'v', 't', 'cf'
        ))
            ->from('Application\Entity\Event', 'e')
            ->leftJoin('e.category', 'c')
            ->leftJoin('e.custom_fields_values', 'v')
            ->leftJoin('v.customfield', 'cf')
            ->leftJoin('cf.type', 't')
            ->andWhere($qb->expr()
                ->isNull('e.parent'));
        if($onlytitle) {
            $qb->andWhere($qb->expr()->eq('c.fieldname', 'cf.id'));
        }
        if($startdate !== null) {
            $qb->andWhere($qb->expr() // display only root events
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
        }
        if ($user !== null && $user->hasIdentity()) {
            $org = $user->getIdentity()->getOrganisation();

            $qb->andWhere($qb->expr()
                ->eq('e.organisation', $org->getId()));

            $parameters[1] = $startdate->format("Y-m-d H:i:s");
            $parameters[2] = $enddate->format("Y-m-d H:i:s");

        }
        if($categories && is_array($categories)) {
            $qb->andWhere($qb->expr()->in('c.id', '?3'));
            $parameters[3] = $categories;
        }

        //full text search on raw custom fields values
        $customfields = $qb->expr()->orX();
        $customfields->add('MATCH (v.value) AGAINST (?4 boolean) > 0');
        $parameters[4] = $search."*";

        //search specific custom fields : sectors, radars, stacks, antennas
        $qbc = $this->getEntityManager()->createQueryBuilder();
        $qbc->select(array('s'))
            ->from('Application\Entity\Sector', 's')
            ->andWhere($qb->expr()
                ->like('s.name', $qb->expr()
                    ->literal($search . '%')));
        $sectors = $qbc->getQuery()->getResult();

        foreach ($sectors as $sector) {
            $customfields->add($qb->expr()
                ->andX($qb->expr()
                    ->eq('t.type', '?5'), $qb->expr()
                    ->eq('v.value', $sector->getId())));
            $parameters[5] = 'sector';
        }

        $qbc = $this->getEntityManager()->createQueryBuilder();
        $qbc->select(array(
            'a'
        ))
            ->from('Application\Entity\Antenna', 'a')
            ->andWhere($qbc->expr()
                ->like('a.name', $qb->expr()
                    ->literal($search . '%')))
            ->orWhere($qbc->expr()
                ->like('a.shortname', $qb->expr()
                    ->literal($search . '%')));
        $query = $qbc->getQuery();
        $antennas = $query->getResult();

        foreach ($antennas as $antenna) {
            $customfields->add($qb->expr()
                ->andX($qb->expr()
                    ->eq('t.type', '?6'), $qb->expr()
                    ->eq('v.value', $antenna->getId())));
            $parameters[6] = 'antenna';
        }

        $qbc = $this->getEntityManager()->createQueryBuilder();
        $qbc->select(array(
            'r'
        ))
            ->from(SwitchObject::class, 'r')
            ->andWhere($qbc->expr()
                ->like('r.name', $qbc->expr()
                    ->literal($search . '%')))
            ->orWhere($qbc->expr()
                ->like('r.shortname', $qbc->expr()
                    ->literal($search . '%')));
        $query = $qbc->getQuery();
        $radars = $query->getResult();

        foreach ($radars as $radar) {
            $customfields->add($qb->expr()
                ->andX($qb->expr()
                    ->eq('t.type', '?7'), $qb->expr()
                    ->eq('v.value', $radar->getId())));
            $parameters[7] = 'radar';
        }

        $qbc = $this->getEntityManager()->createQueryBuilder();
        $qbc->select(array(
            'f'
        ))
            ->from('Application\Entity\Frequency', 'f')
            ->andWhere($qbc->expr()
                ->like('f.value', $qbc->expr()
                    ->literal($search . '%')))
            ->orWhere($qbc->expr()
                ->like('f.othername', $qbc->expr()
                    ->literal($search . '%')));
        $query = $qbc->getQuery();
        $frequencies = $query->getResult();

        foreach ($frequencies as $frequency) {
            $customfields->add($qb->expr()
                ->andX($qb->expr()
                    ->eq('t.type', '?8'), $qb->expr()
                    ->eq('v.value', $frequency->getId())));
            $parameters[8] = 'frequency';
        }

        $qbc = $this->getEntityManager()->createQueryBuilder();
        $qbc->select(array(
            'st'
        ))
            ->from('Application\Entity\Stack', 'st')
            ->andWhere($qbc->expr()
                ->like('st.name', $qbc->expr()
                    ->literal($search . '%')));
        $query = $qbc->getQuery();
        $stacks = $query->getResult();

        foreach ($stacks as $stack) {
            $customfields->add($qb->expr()
                ->andX($qb->expr()
                    ->eq('t.type', '?9'), $qb->expr()
                    ->eq('v.value', $stack->getId())));
            $parameters[9] = 'stack';
        }

        $qb->andWhere($customfields);

        $qb->setParameters($parameters);

        $results = $qb->getQuery()->getResult();

        $ids = array();
        foreach ($results as $r) {
            $ids[] = $r->getId();
        }

        $this->getEntityManager()->clear();

        return $this->getEntityManager()->getRepository(Event::class)->findById($ids);
    }

}
