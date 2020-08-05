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


use Application\Entity\SunriseSunset;

use Core\Controller\AbstractEntityManagerAwareController;
use Doctrine\ORM\EntityManager;
use Laminas\Http\Client;
use Laminas\Http\Request;
use Laminas\View\Model\JsonModel;

/**
 *
 * @author Bruno Spyckerelle
 *
 */
class SunrisesunsetController extends AbstractEntityManagerAwareController
{

    private $url;
    private $lat;
    private $lon;

    function __construct(EntityManager $entityManager, $config)
    {
        parent::__construct($entityManager);

        if(array_key_exists("sunrise", $config) ) {
        	$this->url = $config["sunrise"]["service"];
	        $this->lat = $config["sunrise"]["lat"];
        	$this->lon = $config["sunrise"]["lon"];
	}

    }

    private function getValuesFromService($date)
    {
        //get sunrise from API and persist
        $request = new Request();
        $request->setMethod('GET');
        $request->setUri($this->url."?lat=".$this->lat."&lng=".$this->lon."&date=".$date->format('Y-m-d')."&formatted=0");

        //TODO Proxy
        $client = new Client();
        $client->resetParameters();
        $response = $client->dispatch($request);

        $result = json_decode($response->getBody(), true);

        $entry = new SunriseSunset();
        $entry->setDate($date);
        $entry->setSunrise(new \DateTime($result["results"]["sunrise"]));
        $entry->setSunset(new \DateTime($result["results"]["sunset"]));

        try{
            $this->getEntityManager()->persist($entry);
            $this->getEntityManager()->flush();
        } catch (\Exception $e) {
            //print exception
        }

        return $entry;
    }

    public function getsunriseAction()
    {

        $date = $this->params()->fromQuery('date', null);
        if($date !== null) {
            try {
                $date = \DateTime::createFromFormat('Y-m-d', $date, new \DateTimeZone('UTC'));
            } catch (\Exception $e) {
                var_dump($e->getMessage());
            }
        } else {
            $date = new \DateTime('now');

        }
        $sunrise = $this->getEntityManager()->getRepository(SunriseSunset::class)->findOneBy(array('date' => $date));
        if($sunrise == null) {
            $sunrise = $this->getValuesFromService($date);
        }

        return new JsonModel(array("sunrise" => $sunrise->getSunrise()->format(DATE_ISO8601)));

    }

    public function getsunsetAction()
    {
        $date = $this->params()->fromQuery('date', null);
        if($date !== null) {
            try {
                $date = \DateTime::createFromFormat('Y-m-d', $date, new \DateTimeZone('UTC'));
            } catch (\Exception $e) {
                var_dump($e->getMessage());
            }
        } else {
            $date = new \DateTime('now');

        }
        $sunrise = $this->getEntityManager()->getRepository(SunriseSunset::class)->findOneBy(array('date' => $date));

        if($sunrise == null) {
            $sunrise = $this->getValuesFromService($date);
        }

        return new JsonModel(array("sunset" => $sunrise->getSunset()->format(DATE_ISO8601)));

    }
}
