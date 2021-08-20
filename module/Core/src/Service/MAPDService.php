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
namespace Core\Service;

use Application\Entity\Category;
use Application\Entity\CustomFieldValue;
use Application\Entity\Event;
use Application\Entity\MilCategory;
use Application\Entity\MilCategoryLastUpdate;
use Application\Entity\Status;
use Core\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Laminas\Http\Client;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Log\Logger;
use Laminas\Stdlib\Parameters;
use RuntimeException;

/**
 * Class MAPDService
 * @package Core\Service
 */
class MAPDService
{

    const ACTIVATIONS_ENDPOINT = "/activations";

    const ACTIVATIONSDIFF_ENDPOINT = "/activations/diff";

    private EntityManager $entityManager;
    
    private $config;

    private $errorEmail = false;

    private $client = null;

    private $verbose = false;

    private $uri = "";

    private $logger;

    private $defaultStatus;

    private EmailService $emailService;

    public function __construct(EntityManager $entityManager, $config, Logger $logger, EmailService $emailService)
    {
        $this->entityManager = $entityManager;
        $this->config = $config;
        $this->logger = $logger;
        $this->emailService = $emailService;
    }

    public function isEnabled()
    {
        return $this->getClient() !== null;
    }


    public function getStatus()
    {
        if($this->getClient() == null) {
            throw new RuntimeException('Unable to get MAPD CLient');
        }
        $request = new Request();
        $request->setMethod('GET');
        $request->setUri($this->uri . '/status');
        try {
            $response = $this->getClient()->dispatch($request);
            if ($response->isSuccess()) {
                return json_decode($response->getBody(), true);
            } else {
                return null;
            }
        } catch(Exception $e) {
            throw new RuntimeException($e->getMessage());
        }
    }

    private function getClient()
    {
        if($this->client == null && array_key_exists('mapd', $this->config)) {
            $mapd = $this->config['mapd'];
            $this->uri = $mapd['url'];
            $this->client = new Client();
            $this->client->setAdapter(Client\Adapter\Curl::class);
            if (array_key_exists('user', $mapd) && array_key_exists('password', $mapd)) {
                $this->client->setAuth($mapd['user'], $mapd['password'], Client::AUTH_BASIC);
            }
            if(array_key_exists('default_status', $mapd)) {
                $status = $this->entityManager->getRepository(Status::class)->find($mapd['default_status']);
                if($status) {
                    $this->defaultStatus = $status;
                }
            }
        }
        return $this->client;
    }

    /**
     * Update datas of a MAPD Category
     * @param Category $cat
     * @param DateTime $day
     * @param User $user
     * @throws RuntimeException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Exception
     */
    public function updateCategory(Category $cat, DateTime $day, User $user)
    {

        $start = clone $day;
        $start->setTimezone(new \DateTimeZone('UTC'));
        $start->setTime(0, 0, 0);

        $end = clone $start;
        $end->setTime(23,59,59);

        $this->logger->info('Mise à jour catégorie '.$cat->getName());

        if($cat instanceof MilCategory && strcmp($cat->getOrigin(), MilCategory::MAPD) == 0) {
            //determine if initialisation needed or update
            $lastUpdate = $cat->getLastUpdates()->filter(function(MilCategoryLastUpdate $lastUpdate) use ($day) {
                return strcmp($lastUpdate->getDay(), $day->format('Y-m-d')) == 0;
            });
            //in order to be consistent with NM B2B bahavior : add * at the end of the filter
            //if user wants a specific zone, he must use a regex
            $filter = $cat->getFilter();
            if(strlen($filter) > 0) {
                $filter = strcmp(substr($cat->getFilter(), -1), "*") == 0 ? $cat->getFilter() : $cat->getFilter() . '*';
            }

            if($lastUpdate->isEmpty()) {
                $this->logger->info('No data : get data from /activations');
                //no data for this day
                $eauprsas = $this->getEAUPRSA($filter, $start, $end);

                if($eauprsas !== null && $eauprsas['lastModified'] !== null) {
                    $lastModified = new DateTime($eauprsas['lastModified']);
                    $milLastUpdate = new MilCategoryLastUpdate($lastModified, $cat, $day->format('Y-m-d'));
                    $cat->addLastUpdate($milLastUpdate);

                    foreach ($eauprsas['results'] as $zonemil) {
                        if(strlen($cat->getZonesRegex()) == 0 || (strlen($cat->getZonesRegex()) > 0 && preg_match($cat->getZonesRegex(), $zonemil['areaName']))) {
                            $eventid = $this->entityManager->getRepository(Event::class)->getZoneMilEventId($cat, $zonemil['id']);
                            if($eventid !== -1) {
                                $event = $this->entityManager->getRepository(Event::class)->find($eventid);
                            } else {
                                $event = null;
                            }
                            //$event should be null but if the first event in the db is created by Epeires, event is already in the epeires side
                            if($event == null) {
                                $this->entityManager->getRepository(Event::class)->doAddMilEvent(
                                    $cat,
                                    $user->getOrganisation(),
                                    $user,
                                    $zonemil['areaName'],
                                    new \DateTime($zonemil['dateFrom']),
                                    new  \DateTime($zonemil['dateUntil']),
                                    $zonemil['maxFL'],
                                    $zonemil['minFL'],
                                    $zonemil['id'],
                                    $messages,
                                    false,
                                    $this->defaultStatus
                                );
                            }
                        }
                    }

                    $this->entityManager->persist($milLastUpdate);
                    $this->entityManager->flush();

                }

            } else {
                $this->logger->info('Data present : get data from /activations/diff');
                $lastModified = $lastUpdate->first()->getLastUpdate();
                $eauprsas = $this->getEAUPRSADiff($filter, $start, $end, $lastModified);
                $this->logger->debug(print_r($eauprsas, true));
                if($eauprsas !== null && $eauprsas['lastModified'] !== null) {
                    $newLastModified = $eauprsas['lastModified'];
                    $lastUpdate->first()->setLastUpdate(new \DateTime($newLastModified));
                    foreach ($eauprsas['results'] as $zonemil) {
                        if(strlen($cat->getZonesRegex()) == 0 || (strlen($cat->getZonesRegex()) > 0 && preg_match($cat->getZonesRegex(), $zonemil['areaName']))) {
                            $eventid = $this->entityManager->getRepository(Event::class)->getZoneMilEventId($cat, $zonemil['id']);
                            if($eventid !== -1) {
                                $event = $this->entityManager->getRepository(Event::class)->findOneBy(array('id' => $eventid));
                            } else {
                                $event = null;
                            }
                            //query getzonemilevent filters custom fields, refresh needed
                            if($event !== null) {
                                $this->entityManager->refresh($event);
                            }
                            switch ($zonemil['diffType']) {
                                case 'created':
                                    if ($event == null) {
                                        $this->entityManager->getRepository(Event::class)->doAddMilEvent(
                                            $cat,
                                            $user->getOrganisation(),
                                            $user,
                                            $zonemil["areaName"],
                                            new \DateTime($zonemil['dateFrom']),
                                            new \DateTime($zonemil['dateUntil']),
                                            $zonemil['maxFL'],
                                            $zonemil['minFL'],
                                            $zonemil['id'],
                                            $messages,
                                            false
                                        );
                                    } else {
                                        //do nothing, event should not pre-exist
                                    }
                                    break;
                                case 'updated':
                                    if ($event !== null) {
                                        $upperlevel = $event->getCustomFieldValue($cat->getUpperLevelField());
                                        if ($upperlevel == null) {
                                            $upperlevel = new CustomFieldValue();
                                            $upperlevel->setEvent($event);
                                            $upperlevel->setCustomField($cat->getUpperLevelField());
                                        }
                                        $upperlevel->setValue($zonemil['maxFL']);

                                        $lowerLevel = $event->getCustomFieldValue($cat->getLowerLevelField());
                                        if ($lowerLevel == null) {
                                            $lowerLevel = new CustomFieldValue();
                                            $lowerLevel->setEvent($event);
                                            $lowerLevel->setCustomField($cat->getLowerLevelField());
                                        }
                                        $lowerLevel->setValue($zonemil['minFL']);

                                        $event->setDates(new Datetime($zonemil['dateFrom']), new Datetime($zonemil['dateUntil']));

                                        $this->entityManager->persist($lowerLevel);
                                        $this->entityManager->persist($upperlevel);
                                    } else {
                                        $this->logger->info("Tentative de mise à jour d'un évènement non connue : création");
                                        //update of an unknown event, should not happen but let's create it
                                        //can happen if event is hosted by multiple categories
                                        $this->entityManager->getRepository(Event::class)->doAddMilEvent(
                                            $cat,
                                            $user->getOrganisation(),
                                            $user,
                                            $zonemil["areaName"],
                                            new \DateTime($zonemil['dateFrom']),
                                            new \DateTime($zonemil['dateUntil']),
                                            $zonemil['maxFL'],
                                            $zonemil['minFL'],
                                            $zonemil['id'],
                                            $messages,
                                            false
                                        );
                                    }
                                    break;
                                case 'deleted':
                                    if ($event !== null) {
                                        $status = $this->entityManager->getRepository(Status::class)->find(5);
                                        $event->setStatus($status);
                                    }
                                    break;
                            }
                            if ($event !== null) {
                                $this->entityManager->persist($event);
                            }
                        }
                    }

                    $this->entityManager->persist($lastUpdate->first());
                    $this->entityManager->flush();

                }
            }

        }
    }

    /**
     * Retrieve RSAs for a specific date
     *
     * @param string $filter
     * @param DateTime $start
     * @param DateTime $end
     * @return mixed
     * @throws RuntimeException
     */
    public function getEAUPRSA(string $filter, DateTime $start, DateTime $end)
    {
        if($this->getClient() == null) {
            throw new RuntimeException('Unable to get MAPD CLient');
        }
        $request = new Request();
        $request->setMethod('GET');
        $request->setUri($this->uri . self::ACTIVATIONS_ENDPOINT);

        $parameters = array(
            'start' => $start->format("Y-m-d\TH:i:s\Z"), //TODO change server to accept ISO8601
            'end' => $end->format("Y-m-d\TH:i:s\Z"));

        if(strlen($filter) > 0 && strcmp($filter, '*') !== 0) {
            $parameters['areaName'] = $filter;
        }

        $request->setQuery(new Parameters($parameters));

        $response = $this->getClient()->dispatch($request);
        if($response->isSuccess()) {
            if($response->getStatusCode() == 204) {
                return null;
            } else {
                return json_decode($response->getBody(), true);
            }
        } else {
            throw new RuntimeException('Récupération initiale '. $filter.' : '.$response->getStatusCode().' : '.$response->getReasonPhrase());
        }

    }

    /**
     * @param $filter
     * @param DateTime $start
     * @param DateTime $end
     * @param DateTime $since
     * @return mixed
     * @throws RuntimeException
     */
    public function getEAUPRSADiff($filter, DateTime $start, DateTime $end, DateTime $since)
    {
        if($this->getClient() == null) {
            throw new RuntimeException('Unable to get MAPD CLient');
        }
        $request = new Request();
        $request->setMethod('GET');
        $request->setUri($this->uri . self::ACTIVATIONSDIFF_ENDPOINT);

        $parameters = array(
            'start' => $start->format("Y-m-d\TH:i:s\Z"), //TODO change server to accept ISO8601
            'end' => $end->format("Y-m-d\TH:i:s\Z"),
            'since' => $since->format("Y-m-d\TH:i:s\Z")
            );

        if(strlen($filter) > 0 && strcmp($filter, '*') !== 0) {
            $parameters['areaName'] = $filter;
        }

        $request->setQuery(new Parameters($parameters));

        $response = $this->getClient()->dispatch($request);
        if($response->isSuccess()) {
            return json_decode($response->getBody(), true);
        } else if ($response->getStatusCode() == Response::STATUS_CODE_304) {
            //do nothing
        } else {
            throw new RuntimeException('Récupération différentielle '.$filter.' : '.$response->getStatusCode().' : '.$response->getReasonPhrase());
        }
    }


    /**
     * @param Event $event
     * @return int Internal Id of the event, -1 if problem...
     * @throws Exception
     */
    public function saveRSA(Event $event): int
    {
        if($this->getClient() == null) {
            throw new RuntimeException('Unable to get MAPD CLient');
        }
        if(!($event->getCategory() instanceof MilCategory) ||
            ($event->getCategory() instanceof MilCategory && strcmp($event->getCategory()->getOrigin(), MilCategory::MAPD) !== 0)) {
            throw new RuntimeException("Tentative d'enregistrer un évènement non lié à MAPD");
        }
        if($event->getId()) {
            //mod
            $internalId = $event->getCustomFieldValue($event->getCategory()->getInternalidField());
            if($internalId){
                $name = $event->getCustomFieldValue($event->getCategory()->getFieldname())->getValue();
                $minfl = $event->getCustomFieldValue($event->getCategory()->getLowerLevelField())->getValue();
                $maxfl = $event->getCustomFieldValue($event->getCategory()->getUpperLevelField())->getValue();
                $start = $event->getStartdate();
                $end = $event->getEnddate();
                if(!$this->checkName($name, $event->getCategory())) {
                    throw new RuntimeException("Impossible de modifier l'évènement : le nom n'est pas cohérent avec la catégorie.");
                }
                try {
                    $this->updateRSA($internalId->getValue(), $name, $start, $end, $maxfl, $minfl);
                } catch (Exception $e) {
                    throw $e;
                }
            } else {
                throw new RuntimeException("Impossible de mettre à jour un évènement sans identifiant MAPD.");
            }
        } else {
            $name = $event->getCustomFieldValue($event->getCategory()->getFieldname())->getValue();
            if(!$this->checkName($name, $event->getCategory())) {
                throw new RuntimeException('Impossible de créer une zone avec un nom ne correspondant pas à la catégorie.');
            }

            $minfl = $event->getCustomFieldValue($event->getCategory()->getLowerLevelField())->getValue();
            $maxfl = $event->getCustomFieldValue($event->getCategory()->getUpperLevelField())->getValue();
            $start = $event->getStartdate();
            $end = $event->getEnddate();
            try {
                $response = $this->createRSA($name, $start, $end, $maxfl, $minfl);
            } catch(Exception $e) {
                throw $e;
            }
            return $response['id'];
        }
        return -1;
    }

    /**
     * @param $name
     * @param $cat
     * @return bool
     */
    private function checkName($name, $cat) : bool
    {
        $filter = $cat->getFilter();
        if(strcmp(substr($filter, -1), "*") == 0) {
            $filter = substr($filter, 0, -1);
        }
        if(preg_match('/'.$filter.'\w*/', $name)){
            $regex = $cat->getZonesRegex();
            if(!(strlen($regex) == 0 || (strlen($regex) > 0 && preg_match($regex, $name)))){
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

    /**
     * @param $name
     * @param DateTime $start
     * @param DateTime $end
     * @param $upperFL
     * @param $lowerFL
     * @return mixed
     */
    private function createRSA($name, DateTime $start, DateTime $end, $upperFL, $lowerFL)
    {
        if($this->getClient() == null) {
            throw new RuntimeException('Unable to get MAPD CLient');
        }
        if($end == null) {
            throw new RuntimeException('Impossible de créer un évènement MAPD sans date de fin.');
        }
        if($upperFL == null || strlen($upperFL) == 0){
            $upperFL = 660;
        }
        if($lowerFL == null || strlen($lowerFL) == 0){
            $lowerFL = 0;
        }
        $parameters = array(
            'areaName' => $name,
            'minFL' => $lowerFL,
            'maxFL' => $upperFL,
            'dateFrom' => $start->format("Y-m-d\TH:i:s\Z"),
            'dateUntil' => $end->format("Y-m-d\TH:i:s\Z")
        );
        /*
        $request = new Request();
        $request->setUri($this->uri . self::ACTIVATIONS_ENDPOINT);
        $request->setMethod(Request::METHOD_POST);
        $request->setPost(new Parameters());
        */

        $this->getClient()->setUri($this->uri . self::ACTIVATIONS_ENDPOINT);
        $this->getClient()->setMethod(Request::METHOD_POST);
        $this->getClient()->setRawBody(json_encode($parameters));
        $this->getClient()->setEncType('application/json');

        $response = $this->getClient()->send();

        if($response->getStatusCode() == Response::STATUS_CODE_201) {
            return json_decode($response->getBody(), true);
        } else {
            throw new RuntimeException($response->getStatusCode(). ' '. $response->getReasonPhrase().'<br>'.$response->getBody());
        }
    }

    private function updateRSA(int $id, $name, DateTime $start = null, DateTime $end = null, $upperFL = null, $lowerFL = null)
    {
        if($start == null && $end == null && $upperFL == null && $lowerFL == null) {
            throw new RuntimeException("Imossible de mettre à jour la zone, paramètres manquants.");
        }
        if($this->getClient() == null) {
            throw new RuntimeException('Unable to get MAPD CLient');
        }
        $parameters = array(
        'areaName' => $name,
        'minFL' => $lowerFL,
        'maxFL' => $upperFL,
        'dateFrom' => $start->format("Y-m-d\TH:i:s\Z"),
        'dateUntil' => $end->format("Y-m-d\TH:i:s\Z")
        );
        /*
        $request = new Request();
        $request->setUri($this->uri . self::ACTIVATIONS_ENDPOINT . '/' . $id);
        $request->setMethod(Request::METHOD_PUT);

        $request->setPost(new Parameters($parameters));
        */
        $this->getClient()->setUri($this->uri . self::ACTIVATIONS_ENDPOINT . '/' . $id);
        $this->getClient()->setMethod(Request::METHOD_PUT);
        $this->getClient()->setRawBody(json_encode($parameters));
        $this->getClient()->setEncType('application/json');

        error_log(print_r(json_encode($parameters), true));

        $response = $this->getClient()->send();

        if($response->getStatusCode() == Response::STATUS_CODE_200) {
            return json_decode($response->getBody(), true);
        } else {
            throw new RuntimeException($response->getStatusCode() . ' '.$response->getReasonPhrase().'<br>'
                                    .$response->getBody());
        }
    }

    public function cancelRSA($id)
    {

    }

    public function activateErrorEmail()
    {
        $this->errorEmail = true;
    }

    public function deActivateErrorEmail()
    {
        $this->errorEmail = false;
    }

    public function sendErrorEmail($textError) {


        //TODO récupérer proprement l'organisation concernée
        $org = $this->entityManager->getRepository('Application\Entity\Organisation')->findAll();
        $ipoEmail = $org[0]->getIpoEmail();

        // prepare body with file attachment
        $text = new \Laminas\Mime\Part($textError);
        $text->type = \Laminas\Mime\Mime::TYPE_TEXT;
        $text->charset = 'utf-8';

        $mimeMessage = new \Laminas\Mime\Message();
        $mimeMessage->setParts(array(
            $text
        ));

        $this->emailService->sendEmailTo(
            $ipoEmail,
            "Erreur lors de l'import de l'AUP via MAPD",
            $mimeMessage,
            $org[0]
        );

    }

    public function setVerbose(bool $verbose)
    {
        $this->verbose = $verbose;
    }

}

