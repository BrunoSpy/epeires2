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

use Application\Entity\CustomFieldValue;
use Application\Entity\Event;
use Application\Entity\MilCategory;
use Application\Entity\MilCategoryLastUpdate;
use Application\Entity\Organisation;
use Application\Entity\Status;
use Core\Controller\AbstractEntityManagerAwareController;
use Core\Entity\User;
use Core\Service\MAPDService;
use Core\Service\NMB2BService;
use Doctrine\ORM\EntityManager;
use Laminas\Console\Request as ConsoleRequest;
use Laminas\Db\Sql\Ddl\Column\Datetime;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class MilController extends AbstractEntityManagerAwareController
{

    private $nmb2b;
    private $mapd;

    public function __construct(EntityManager $entityManager, NMB2BService $nmb2b, MAPDService $mapd)
    {
        parent::__construct($entityManager);
        $this->nmb2b = $nmb2b;
        $this->mapd = $mapd;
    }

    public function importAction()
    {
        $request = $this->getRequest();

        if (! $request instanceof ConsoleRequest) {
            throw new \RuntimeException('Action only available from console.');
        }

        $j = $request->getParam('delta');

        $org = $request->getParam('orgshortname');
        $organisation = $this->getEntityManager()->getRepository('Application\Entity\Organisation')->findOneBy(array(
            'shortname' => $org
        ));

        $email = $request->getParam('email');

        $verbose = $request->getParam('verbose');

        $username = $request->getParam('username');

        $user = $this->getEntityManager()->getRepository('Core\Entity\User')->findOneBy(array(
            'username' => $username
        ));

        $service = $request->getParam('service');
        if(strcmp($service, 'nmb2b') == 0) {
            $this->importNMB2B($j, $organisation, $user, $email, $verbose);
        } elseif (strcmp($service, 'mapd') == 0) {
            $this->importMAPD($j, $organisation, $user, $email);
        } else {
            throw new \RuntimeException('Service '.$service.' unknown.');
        }
    }

    private function importNMB2B($delta, Organisation $organisation, User $user, $email, $verbose)
    {
        if($email) {
            $this->nmb2b->activateErrorEmail();
        } else {
            $this->nmb2b->deActivateErrorEmail();
        }

        if($verbose) {
            $this->nmb2b->setVerbose(true);
        } else {
            $this->nmb2b->setVerbose(false);
        }

        $day = new \DateTime('now');
        if ($delta) {
            if ($delta > 0) {
                $day->add(new \DateInterval('P' . $delta . 'D'));
            } else {
                $delta = - $delta;
                $interval = new \DateInterval('P' . $delta . 'D');
                $interval->invert = 1;
                $day->add($interval);
            }
        }
        $startImport = microtime(true);
        $totalDL = 0;
        $totalTR = 0;
        $totalEvents = 0;
        echo "Lancement du téléchargement de l'AUP pour " . $organisation->getName()."\n";

        try {
            $startSeq = microtime(true);
            echo "Récupération du nombre de séquences\n";
            $eaupchain = $this->nmb2b->getEAUPCHain($day);
            $dl = microtime(true) - $startSeq;
            $totalDL += $dl;
            echo "Séquences récupérées en ".$dl." secondes\n";
        } catch(\RuntimeException $e) {
            $dl = microtime(true) - $startSeq;
            echo "Erreur au bout de ". $dl . " secondes.\n";
            echo "Erreur fatale pendant le téléchargement"."\n";
            echo "Les données téléchargées sont incomplètes"."\n";
            echo "Le rapport d'erreur a été envoyé sur l'adresse de l'IPO, si configuré"."\n";
            return;
        }

        $lastAUPSequenceNumber = $eaupchain->getAUPSequenceNumber();
        
        //$lastSequence = $eaupchain->getLastSequenceNumber();
        $milcats = $this->getEntityManager()->getRepository('Application\Entity\MilCategory')->findBy(array(
            'archived' => false,
            'origin' => MilCategory::NMB2B
        ));

        //filter by state
        $designators = array();
        foreach ($milcats as $cat){
            $filter = $cat->getFilter();
            if(strlen($filter) > 2) {
                $start = substr($filter, 0, 2);
                if(!in_array($start, $designators)){
                    $designators[] = $start;
                }
            }
        }
        //foreach state
        //to avoid multiple requests, we do not take into account filter but only the entire state
        foreach ($designators as $designator) {
            try {
                $startSeq = microtime(true);
                echo "Téléchargement des zones ".$designator.", séquence ".$lastAUPSequenceNumber."\n";
                $eauprsas = $this->nmb2b->getEAUPRSA($designator.'*', $day, $lastAUPSequenceNumber);
                $dl = microtime(true) - $startSeq;
                $totalDL += $dl;
                echo "Téléchargement terminé en ".$dl." secondes"."\n";
            } catch(\RuntimeException $e) {
                $dl = microtime(true) - $startSeq;
                echo "Erreur au bout de ". $dl . " secondes.\n";
                echo "Erreur fatale pendant le téléchargement"."\n";
                echo "Les données téléchargées sont incomplètes"."\n";
                echo "Le rapport d'erreur a été envoyé sur l'adresse de l'IPO, si configuré"."\n";
                //abort import
                //email sent by service
                return;
            }
            $startEpeires = microtime(true);
            
            echo "Création des évènements ".$designator.' séquence '.$lastAUPSequenceNumber." dans Epeires..."."\n";
            $evts = 0;
            foreach ($milcats as $cat) {
                $evts += $this->getEntityManager()->getRepository('Application\Entity\Event')->addZoneMilEvents($eauprsas, $cat, $organisation, $user);
            }
            $tr = microtime(true) - $startEpeires;
            $totalTR += $tr;
            echo $evts . " évènements créés en ".$tr.' secondes'."\n";
            $totalEvents += $evts;
        }
        //}
        echo "Fin de l'import de l'AUP en ".(microtime(true)-$startImport).' secondes'."\n";
        echo 'Téléchargement : '.$totalDL.' secondes'."\n";
        echo 'Traitement : '.$totalTR.' secondes'."\n";
        echo "Nombre d'évènements créés : ".$totalEvents."\n";
    }

    private function importMAPD($delta, Organisation $organisation, User $user, $email)
    {
        $day = new \DateTime('now');
        if ($delta) {
            if ($delta > 0) {
                $day->add(new \DateInterval('P' . $delta . 'D'));
            } else {
                $delta = - $delta;
                $interval = new \DateInterval('P' . $delta . 'D');
                $interval->invert = 1;
                $day->add($interval);
            }
        }

        $start = clone $day;
        $start->setTimezone(new \DateTimeZone('UTC'));
        $start->setTime(0, 0, 0);

        $end = clone $start;
        $end->setTime(23,59,59);

        $milcats = $this->getEntityManager()->getRepository(MilCategory::class)->findBy(array('archived'=>false, 'origin' => MilCategory::MAPD));
        $messages = array();
        foreach ($milcats as $milcat) {
            //determine if initialisation needed or update
            $lastUpdate = $milcat->getLastUpdates()->filter(function(MilCategoryLastUpdate $lastUpdate) use ($day) {
                return strcmp($lastUpdate->getDay(), $day->format('Y-m-d')) == 0;
            });
            //in order to be consistent with NM B2B bahavior : add * at the end of the filter
            //if user wants a specific zone, he must use a regex
            $filter = strcmp(substr($milcat->getFilter(), -1), "*") == 0 ? $milcat->getFilter() : $milcat->getFilter().'*';

            if($lastUpdate->isEmpty()) {
                //init
                error_log('no data for this day : init');
                $eauprsas = $this->mapd->getEAUPRSA($filter, $start, $end);
                //TODO temp bugfix
                $lastModified = new \DateTime($eauprsas['lastModified']);
                $milLastUpdate = new MilCategoryLastUpdate($lastModified, $milcat, $day->format('Y-m-d'));
                $this->getEntityManager()->persist($milLastUpdate);
                $milcat->addLastUpdate($milLastUpdate);

                foreach ($eauprsas["results"] as $zonemil) {
                    if(preg_match($milcat->getZonesRegex(), $zonemil["areaName"])) {
                        $this->getEntityManager()->getRepository(Event::class)->doAddMilEvent(
                            $milcat,
                            $organisation,
                            $user,
                            $zonemil["areaName"],
                            new \DateTime($zonemil['dateFrom']),
                            new  \DateTime($zonemil['dateUntil']),
                            $zonemil['maxFL'],
                            $zonemil['minFL'],
                            $zonemil['id'],
                            $messages
                        );
                    }
                }
                try {
                    $this->getEntityManager()->flush();
                } catch (\Exception $e) {
                    error_log($e->getMessage());
                }
            } else {
                error_log('data for this day : check if new');
                //last-modified
                $lastModified = $lastUpdate->first()->getLastUpdate();

                $eauprsas = $this->mapd->getEAUPRSADiff($milcat->getFilter(), $start, $end, $lastModified);

                if($eauprsas !== null) {

                    $newLastModified = new \Datetime($eauprsas['lastModified']);
                    $lastUpdate->first()->setLastUpdate($newLastModified);
                    $this->getEntityManager()->persist($lastUpdate->first());

                    foreach ($eauprsas['results'] as $zonemil) {
                        if(preg_match($milcat->getZonesRegex(), $zonemil["areaName"])) {
                            continue;
                        }
                        if (strcmp($zonemil['diffType'], "created") == 0) {

                            //verify if event is already in database juste in case...
                            $event = $this->getEntityManager()->getRepository(Event::class)->find($this->getEntityManager()->getRepository(Event::class)->getZoneMilEventId($milcat, $zonemil['id']));
                            if($event != null) {
                                //TODO throw a nice exception
                            } else {
                                $this->getEntityManager()->getRepository(Event::class)->doAddMilEvent(
                                    $milcat,
                                    $organisation,
                                    $user,
                                    $zonemil["areaName"],
                                    new \DateTime($zonemil['dateFrom']),
                                    new \DateTime($zonemil['dateUntil']),
                                    $zonemil['maxFL'],
                                    $zonemil['minFL'],
                                    $zonemil['id'],
                                    $messages
                                );
                            }
                        } else {
                            $event = $this->getEntityManager()->getRepository(Event::class)->find($this->getEntityManager()->getRepository(Event::class)->getZoneMilEventId($milcat, $zonemil['id']));
                            if($event !== null) {
                                if(strcmp($zonemil['diffType'], "modified")) {
                                    $upperlevel = $event->getCustomFieldValue($milcat->getUpperLevelField());
                                    if(!$upperlevel) {
                                        $upperlevel = new CustomFieldValue();
                                        $upperlevel->setEvent($event);
                                        $upperlevel->setCustomField($milcat->getUpperLevelField());
                                    }
                                    $upperlevel->setValue($zonemil['maxFL']);

                                    $lowerLevel = $event->getCustomFieldValue($milcat->getLowerLevelField());
                                    if(!$lowerLevel){
                                        $lowerLevel = new CustomFieldValue();
                                        $lowerLevel->setEvent($event);
                                        $lowerLevel->setCustomField($milcat->getLowerLevelField());
                                    }
                                    $lowerLevel->setValue($zonemil['minFL']);

                                    $event->setDates(new Datetime($zonemil['dateFrom']), new Datetime($zonemil['dateUntil']));

                                    $this->getEntityManager()->persist($lowerLevel);
                                    $this->getEntityManager()->persist($upperlevel);

                                } else if(strcmp($zonemil['diffType'], "deleted")) {
                                    $status = $this->getEntityManager()->getRepository(Status::class)->find(5);
                                    $event->setStatus($status);
                                }
                                $this->getEntityManager()->persist($event);
                            }
                        }
                    }
                    try{
                        $this->getEntityManager()->flush();
                    } catch (\Exception $e) {
                        error_log($e->getMessage());
                    }
                }

            }
        }
    }

}
