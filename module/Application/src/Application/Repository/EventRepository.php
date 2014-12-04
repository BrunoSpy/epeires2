<?php

namespace Application\Repository;

use Application\Entity\CustomFieldValue;
use Application\Entity\Event;
use Application\Entity\Frequency;
use Application\Core\User;
use Zend\Session\Container;

/**
 * Description of EventRepository
 *
 * @author Bruno Spyckerelle
 */
class EventRepository extends ExtendedRepository {

    /**
     * Get all events readable by <code>$userauth</code>
     * intersecting <code>$day</code>
     * @param type $day If null : use current day
     * @param type $lastmodified If not null : only events modified since <code>$lastmodified</code>
     * @return type
     */
    public function getEvents($userauth, $day = null, $lastmodified = null, $orderbycat = false) {

        $parameters = array();

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select(array('e', 'f'))
                ->from('Application\Entity\Event', 'e')
                ->leftJoin('e.zonefilters', 'f')
                ->andWhere($qb->expr()->isNull('e.parent')); //display only root events
        //restriction à tous les evts modifiés depuis $lastmodified
        if ($lastmodified) {
            $lastmodified = new \DateTime($lastmodified);
            $qb->andWhere($qb->expr()->gte('e.last_modified_on', '?3'));
            $parameters[3] = $lastmodified->format("Y-m-d H:i:s");
        }

        if ($day) {
            $daystart = new \DateTime($day);
            $daystart->setTime(0, 0, 0);
            $dayend = new \DateTime($day);
            $dayend->setTime(23, 59, 59);
            $daystart = $daystart->format("Y-m-d H:i:s");
            $dayend = $dayend->format("Y-m-d H:i:s");
            //tous les évènements ayant une intersection non nulle avec $day
            $qb->andWhere($qb->expr()->orX(
                            //evt dont la date de début est le bon jour : inclus les ponctuels
                            $qb->expr()->andX(
                                    $qb->expr()->gte('e.startdate', '?1'), $qb->expr()->lte('e.startdate', '?2')
                            ),
                            //evt dont la date de début est passée : forcément non ponctuels
                            $qb->expr()->andX(
                                    $qb->expr()->eq('e.punctual', 'false'), $qb->expr()->lt('e.startdate', '?1'), $qb->expr()->orX(
                                            $qb->expr()->isNull('e.enddate'), $qb->expr()->gte('e.enddate', '?1')
                                    )
                            )
                    )
            );
            $parameters[1] = $daystart;
            $parameters[2] = $dayend;
            $qb->setParameters($parameters);
        } else {
            //every events of the last 3 days :
            // * no enddate and not punctual
            // * startdate > now - 3days && (enddate == null || (enddate != null && enddate <= now))
            $now = new \DateTime('NOW');
            $qb->andWhere($qb->expr()->orX(
                    $qb->expr()->andX($qb->expr()->isNull('e.enddate'), $qb->expr()->eq('e.punctual', 'false')),
                    $qb->expr()->andX(
                            $qb->expr()->gte('e.startdate', '?1'),
                            $qb->expr()->orX(
                                    $qb->expr()->isNull('e.enddate'),
                                    $qb->expr()->lte('e.enddate', '?2')
                            )
                    )
            ));
            $parameters[1] = $now->sub(new \DateInterval('P3D'))->format('Y-m-d H:i:s');
            $parameters[2] = $now->format('Y-m-d H:i:s');
            $qb->setParameters($parameters);
        }

        //filtre par zone
        $session = new Container('zone');
        $zonesession = $session->zoneshortname;
        if ($userauth && $userauth->hasIdentity()) {
            //on filtre soit par la valeur en session soit par l'organisation de l'utilisateur
            //TODO gérer les evts partagés
            if ($zonesession != null) { //application d'un filtre géographique
                if ($zonesession != '0') {
                    //la variable de session peut contenir soit une orga soit une zone
                    $orga = $this->getEntityManager()->getRepository('Application\Entity\Organisation')->findOneBy(array('shortname' => $zonesession));
                    if ($orga) {
                        $qb->andWhere($qb->expr()->eq('e.organisation', $orga->getId()));
                    } else {
                        $zone = $this->getEntityManager()->getRepository('Application\Entity\QualificationZone')->findOneBy(array('shortname' => $zonesession));
                        if ($zone) {
                            $qb->andWhere($qb->expr()->andX(
                                            $qb->expr()->eq('e.organisation', $zone->getOrganisation()->getId()), $qb->expr()->orX(
                                                    $qb->expr()->eq('f', $zone->getId()), $qb->expr()->isNull('f.id'))
                                    )
                            );
                        } else {
                            //throw error
                        }
                    }
                } else {
                    //tous les evts de l'org de l'utilisateur connecté
                    $orga = $userauth->getIdentity()->getOrganisation();
                    $qb->andWhere($qb->expr()->eq('e.organisation', $orga->getId()));
                }
            } else {
                //tous les evts de l'org de l'utilisateur connecté
                $orga = $userauth->getIdentity()->getOrganisation();
                $qb->andWhere($qb->expr()->eq('e.organisation', $orga->getId()));
            }
        } else {
            //aucun filtre autre que les rôles
        }

        if ($orderbycat) {
            $qb->addOrderBy('e.category')
                    ->addOrderBy('e.startdate');
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
        } else if ($userauth != null) {
            $roleentity = $this->getEntityManager()->getRepository('Core\Entity\Role')->findOneBy(array('name' => 'guest'));
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
     * Tous les évènements en cours concernant la catégorie <code>$category</code>
     */
    public function getCurrentEvents($category) {
        $now = new \DateTime('NOW');
        $now->setTimezone(new \DateTimeZone("UTC"));
        $qbEvents = $this->getEntityManager()->createQueryBuilder();
        $qbEvents->select(array('e', 'cat'))
                ->from('Application\Entity\Event', 'e')
                ->innerJoin('e.category', 'cat')
                ->andWhere('cat INSTANCE OF ' . $category)
                ->andWhere($qbEvents->expr()->eq('e.punctual', 'false'))
                ->andWhere($qbEvents->expr()->lte('e.startdate', '?1'))
                ->andWhere($qbEvents->expr()->orX(
                                $qbEvents->expr()->isNull('e.enddate'), $qbEvents->expr()->gte('e.enddate', '?2')))
                ->setParameters(array(1 => $now->format('Y-m-d H:i:s'),
                    2 => $now->format('Y-m-d H:i:s')));

        $query = $qbEvents->getQuery();

        return $query->getResult();
    }

    /**
     * Tous les éléments prévus concernant la catégorie <code>$category</code>
     * - Date de début dans les 12h
     */
    public function getPlannedEvents($category) {
        $now = new \DateTime('NOW');
        $now->setTimezone(new \DateTimeZone("UTC"));
        $qbEvents = $this->getEntityManager()->createQueryBuilder();
        $qbEvents->select(array('e', 'cat'))
                ->from('Application\Entity\Event', 'e')
                ->innerJoin('e.category', 'cat')
                ->andWhere('cat INSTANCE OF ' . $category)
                ->andWhere($qbEvents->expr()->andX(
                                $qbEvents->expr()->gte('e.startdate', '?1'), $qbEvents->expr()->lte('e.startdate', '?2')))
                ->setParameters(array(1 => $now->format('Y-m-d H:i:s'),
                    2 => $now->add(new \DateInterval('PT12H'))->format('Y-m-d H:i:s')));

        $query = $qbEvents->getQuery();

        return $query->getResult();
    }

    
    public function addSwitchFrequencyStateEvent(Frequency $freq, \DateTime $startdate, \Core\Entity\User $author, Event $parent = null, &$messages = null) {
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
        //TODO fix horrible en attendant de gérer correctement les fréquences sans secteur
        if ($freq->getDefaultsector()) {
            $event->setOrganisation($freq->getDefaultsector()->getZone()->getOrganisation());
            $event->addZonefilter($freq->getDefaultsector()->getZone());
        } else {
            $event->setOrganisation($author->getOrganisation());
        }
        $event->setAuthor($author);
        $categories = $em->getRepository('Application\Entity\FrequencyCategory')->findBy(array('defaultfrequencycategory' => true));
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
            $statusfield->setValue(true); //unavailable
            $event->addCustomFieldValue($statusfield);
            $em->persist($frequencyfieldvalue);
            $em->persist($statusfield);
            $em->persist($event);
            try {
                $em->flush();
                if($messages){
                    $messages['success'][] = "Fréquence " . $freq->getValue() . " passée au statut indisponible.";
                }
            } catch (\Exception $e) {
                if($messages){
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
     * @param \Application\Repository\Frequency $from
     * @param \Application\Repository\Frequency $to
     * @param \Application\Repository\Event $parent
     * @param type $messages
     */
    public function addSwitchFrequencyEvent(Frequency $from, Frequency $to, \Core\Entity\User $author, Event $parent = null, &$messages = null) {
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
        //TODO fix horrible en attendant de gérer correctement les fréquences sans secteur
        if ($from->getDefaultsector()) {
            $event->setOrganisation($from->getDefaultsector()->getZone()->getOrganisation());
            $event->addZonefilter($from->getDefaultsector()->getZone());
        } else {
            $event->setOrganisation($author->getOrganisation());
        }
        $event->setAuthor($author);
        $categories = $em->getRepository('Application\Entity\FrequencyCategory')->findBy(array('defaultfrequencycategory' => true));
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
            $statusfield->setValue(false); //available
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
     * @param Frequency $frequency
     * @param type $cov Value for the current antenna field
     * @param type $freqstatus Value for the current frequency state field
     * @param Event $parent
     * @param \DateTime $startdate
     * @param User $author
     * @param type $messages
     */
    public function addChangeFrequencyCovEvent(
            Frequency $frequency, 
            $cov,
            $freqstatus,
            \DateTime $startdate, 
            \Core\Entity\User $author, 
            Event $parent = null, 
            &$messages = null) {
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
        //TODO fix horrible en attendant de gérer correctement les fréquences sans secteur
        if ($frequency->getDefaultsector()) {
            $event->setOrganisation($frequency->getDefaultsector()->getZone()->getOrganisation());
            $event->addZonefilter($frequency->getDefaultsector()->getZone());
        } else {
            $event->setOrganisation($author->getOrganisation());
        }
        $event->setAuthor($author);
        $categories = $em->getRepository('Application\Entity\FrequencyCategory')->findBy(array('defaultfrequencycategory' => true));
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
                if($messages != null){
                    $messages['success'][] = "Changement de couverture de la fréquence " . $frequency->getValue() . " enregistré.";
                }
            } catch (\Exception $e) {
                if($messages != null) {
                    $messages['error'][] = $e->getMessage();
                } else {
                    error_log($e->getMessage());
                }
            }
        } else {
            if($messages != null){
                $messages['error'][] = "Impossible de passer les couvertures en secours : aucune catégorie trouvée.";
            }
        }
    }

}
