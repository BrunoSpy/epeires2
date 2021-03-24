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
use Laminas\Http\Client;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Stdlib\Parameters;

/**
 *
 * @author Bruno Spyckerelle
 */
class MAPDService
{

    private $entityManager;
    
    private $config;

    private $errorEmail = false;

    private $client = null;

    private $verbose = false;

    private $uri = "";

    public function __construct(EntityManager $entityManager, $config)
    {
        $this->entityManager = $entityManager;
        $this->config = $config;

    }

    public function isEnabled()
    {
        return $this->getClient() !== null;
    }

    private function getClient()
    {
        if($this->client == null && array_key_exists('mapd', $this->config)) {
            $mapd = $this->config['mapd'];
            $this->uri = $mapd['url'];
            $this->client = new Client();
            if (array_key_exists('user', $mapd) && array_key_exists('password', $mapd)) {
                $this->client->setAuth($mapd['user'], $mapd['password']);
            }
        }
        return $this->client;
    }

    /**
     * Update datas of a MAPD Category
     * @param Category $cat
     * @param DateTime $day
     * @param User $user
     * @param $messages
     * @throws \Exception
     */
    public function updateCategory(Category $cat, DateTime $day, User $user, &$messages)
    {

        $start = clone $day;
        $start->setTimezone(new \DateTimeZone('UTC'));
        $start->setTime(0, 0, 0);

        $end = clone $start;
        $end->setTime(23,59,59);

        if($cat instanceof MilCategory && strcmp($cat->getOrigin(), MilCategory::MAPD) == 0) {
            //determine if initialisation needed or update
            $lastUpdate = $cat->getLastUpdates()->filter(function(MilCategoryLastUpdate $lastUpdate) use ($day) {
                return strcmp($lastUpdate->getDay(), $day->format('Y-m-d')) == 0;
            });
            //in order to be consistent with NM B2B bahavior : add * at the end of the filter
            //if user wants a specific zone, he must use a regex
            $filter = strcmp(substr($cat->getFilter(), -1), "*") == 0 ? $cat->getFilter() : $cat->getFilter().'*';


            if($lastUpdate->isEmpty()) {
                //no data for this day
                $eauprsas = $this->getEAUPRSA($filter, $start, $end);

                if($eauprsas !== null && $eauprsas['lastModified'] !== null) {
                    $lastModified = new DateTime($eauprsas['lastModified']);
                    $milLastUpdate = new MilCategoryLastUpdate($lastModified, $cat, $day->format('Y-m-d'));
                    $cat->addLastUpdate($milLastUpdate);

                    foreach ($eauprsas['results'] as $zonemil) {
                        if(strlen($cat->getZonesRegex()) == 0 || (strlen($cat->getZonesRegex()) > 0 && preg_match($cat->getZonesRegex(), $zonemil['areaName']))) {
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
                                false
                            );
                        }
                    }

                    try {
                        $this->entityManager->persist($milLastUpdate);
                        $this->entityManager->flush();
                    } catch(\Exception $e) {
                        error_log($e->getMessage());
                    }

                }

            } else {
                $lastModified = $lastUpdate->first()->getLastUpdate();

                $eauprsas = $this->getEAUPRSADiff($filter, $start, $end, $lastModified);

                if($eauprsas !== null && $eauprsas['lastModified'] !== null) {
                    $newLastModified = $eauprsas['lastModified'];
                    $lastUpdate->first()->setLastUpdate(new \DateTime($newLastModified));

                    foreach ($eauprsas['results'] as $zonemil) {
                        if(strlen($cat->getZonesRegex()) == 0 || (strlen($cat->getZonesRegex()) > 0 && preg_match($cat->getZonesRegex(), $zonemil['areaName']))) {
                            continue;
                        }
                        $event = $this->entityManager->getRepository(Event::class)->find(
                            $this->entityManager->getRepository(Event::class)->getZoneMilEventId($cat, $zonemil['id'])
                        );
                        switch ($zonemil['diffType']){
                            case 'created':
                                if($event == null) {
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
                            case 'modified':
                                if($event !== null) {
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

                                    try{
                                        $this->entityManager->persist($lowerLevel);
                                        $this->entityManager->persist($upperlevel);
                                    } catch (\Exception $e) {
                                        error_log($e->getMessage());
                                    }
                                }
                                break;
                            case 'deleted':
                                if($event !== null) {
                                    $status = $this->entityManager->getRepository(Status::class)->find(5);
                                    $event->setStatus($status);
                                }
                                break;
                        }
                        if($event !== null) {
                            try {
                                $this->entityManager->persist($event);
                            } catch (\Exception $e) {
                                error_log($e->getMessage());
                            }
                        }
                    }

                    try {
                        $this->entityManager->persist($lastUpdate->first());
                        $this->entityManager->flush();
                    } catch(\Exception $e) {
                        error_log($e->getMessage());
                    }

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
     * @return array
     * @throws \RuntimeException
     */
    public function getEAUPRSA($filter, DateTime $start, DateTime $end)
    {
        if($this->getClient() == null) {
            throw new \RuntimeException('Unable to get MAPD CLient');
        }
        $request = new Request();
        $request->setMethod('GET');
        $request->setUri($this->uri . '/activations');
        $request->setQuery(new Parameters(array(
            'start' => $start->format("Y-m-d\TH:i:s"), //TODO change server to accept ISO8601
            'end' => $end->format("Y-m-d\TH:i:s"),
            'areaName' => $filter)));


        $response = $this->getClient()->dispatch($request);
        if($response->isSuccess()) {
            if($response->getStatusCode() == 204) {
                return null;
            } else {
                return json_decode($response->getBody(), true);
            }
        } else {
            return null;
        }

    }

    public function getEAUPRSADiff($filter, DateTime $start, DateTime $end, DateTime $since)
    {
        if($this->getClient() == null) {
            throw new \RuntimeException('Unable to get MAPD CLient');
        }
        $request = new Request();
        $request->setMethod('GET');
        $request->setUri($this->uri . '/activations/diff');
        $request->setQuery(new Parameters(array(
            'start' => $start->format("Y-m-d\TH:i:s"), //TODO change server to accept ISO8601
            'end' => $end->format("Y-m-d\TH:i:s"),
            'since' => $since->format("Y-m-d\TH:i:s"),
            'areaName' => $filter)));

        $response = $this->getClient()->dispatch($request);
        if($response->isSuccess()) {
            return json_decode($response->getBody(), true);
        } else if ($response->getStatusCode() == Response::STATUS_CODE_304) {
            //do nothing
        } else {
            throw new \RuntimeException($response->getStatusCode().' : '.$response->getReasonPhrase());
        }
    }

    public function createRSA($name, DateTime $start, DateTime $end, $upperFL, $lowerFL)
    {

    }

    public function updateRSA($id, DateTime $start = null, DateTime $end = null, $upperFL = null, $lowerFL = null)
    {
        if($start == null && $end == null && $upperFL == null && $lowerFL == null) {
            return;
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
        if (array_key_exists('emailfrom', $this->config) && array_key_exists('smtp', $this->config)) {
            $message = new \Laminas\Mail\Message();
            $message->addTo($ipoEmail)
                ->addFrom($this->config['emailfrom'])
                ->setSubject("Erreur lors de l'import de l'AUP via NM B2B")
                ->setBody($mimeMessage);
    
            $transport = new \Laminas\Mail\Transport\Smtp();
            $transportOptions = new \Laminas\Mail\Transport\SmtpOptions($this->config['smtp']);
            $transport->setOptions($transportOptions);
            $transport->send($message);
        }
    }

    public function setVerbose(bool $verbose)
    {
        $this->verbose = $verbose;
    }

}

