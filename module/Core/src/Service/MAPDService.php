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
            $this->client = new Client($mapd['url']);
            if (array_key_exists('user', $mapd) && array_key_exists('password', $mapd)) {
                $this->client->setAuth($mapd['user'], $mapd['password']);
            }
        }
        return $this->client;
    }

    /**
     * Retrieve RSAs for a specific date
     *
     * @param string $filter
     * @param \DateTime $start
     * @param \DateTime $end
     * @return array
     * @throws \RuntimeException
     */
    public function getEAUPRSA($filter, \DateTime $start, \DateTime $end)
    {
        if($this->getClient() == null) {
            throw new \RuntimeException('Unable to get MAPD CLient');
        }
        $request = new Request();
        $request->setMethod('GET');
        $request->setUri($this->getClient()->getUri() . '/activations');
        $request->setQuery(new Parameters(array(
            'start' => $start->format("Y-m-d\TH:i:s"), //TODO change server to accept ISO8601
            'end' => $end->format("Y-m-d\TH:i:s"),
            'areaName' => $filter)));


        $response = $this->getClient()->dispatch($request);
        if($response->isSuccess()) {
            error_log(print_r($response->getBody(), true));
            return json_decode($response->getBody(), true);
        } else {
            throw new \RuntimeException($response->getStatusCode().' : '.$response->getReasonPhrase());
        }

    }

    public function getEAUPRSADiff($filter, \DateTime $start, \DateTime $end, \DateTime $since)
    {
        if($this->getClient() == null) {
            throw new \RuntimeException('Unable to get MAPD CLient');
        }
        $request = new Request();
        $request->setMethod('GET');
        $request->setUri($this->getClient()->getUri() . '/activations/diff');
        $request->setQuery(new Parameters(array(
            'start' => $start->format("Y-m-d\TH:i:s"), //TODO change server to accept ISO8601
            'end' => $end->format("Y-m-d\TH:i:s"),
            'since' => $since->format("Y-m-d\TH:i:s"),
            'areaName' => $filter)));

        error_log(print_r($request->getQuery(), true));

        $response = $this->getClient()->dispatch($request);
        if($response->isSuccess()) {
            error_log(print_r($response->getBody(), true));
            return json_decode($response->getBody(), true);
        } else if ($response->getStatusCode() == Response::STATUS_CODE_304) {
            //do nothing
        } else {
            throw new \RuntimeException($response->getStatusCode().' : '.$response->getReasonPhrase());
        }
    }

    public function createRSA($name, \DateTime $start, \DateTime $end, $upperFL, $lowerFL)
    {

    }

    public function updateRSA($id, \DateTime $start = null, \DateTime $end = null, $upperFL = null, $lowerFL = null)
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

