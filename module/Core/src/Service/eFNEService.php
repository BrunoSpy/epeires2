<?php

namespace Core\Service;

use Laminas\Http\Client;
use Laminas\Http\Headers;
use Laminas\Http\Request;

class eFNEService
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function sendEventToEFNE($customFields)
    {
        if(!array_key_exists('efne', $this->config)) {
            throw new \RuntimeException('Aucune configuration eFNE trouvée');
        }
        
        $mainUrl = $this->config['efne']['url'];
        $loginUrl = $mainUrl . '/efne/api/account/token/login/';
        $efneUrl = $mainUrl . '/efne/api/fne/draft/';

        $client = new Client();
        $client->setOptions([
            'timeout' => 30
        ]);

        // Login 
        $headers = new Headers();
        $headers->addHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded'
        ]);
        $client->setHeaders($headers);
        $client->setUri($loginUrl);
        $client->setMethod('POST');
        $client->setParameterPost([
            'username' => $this->config['efne']['username'],
            'password' => $this->config['efne']['password']
        ]);
        $client->send();

        // Envoie des données vers eFNE
        $client->resetParameters(true);

        $request = new Request();
        $request->setMethod('POST');
        $request->setUri($efneUrl);

        // Prepare the data
        $data = array(
            'event_date' => $customFields['date'],
            'position' => $customFields['position'],
            'regroupement' => $customFields['regroupement'],
            'description' => $customFields['description'],
            'options' => array(
                'proceed' => true,  // changed from 'true' to true
                'bypass_validation' => true,  // changed from 'true' to true
            ),
            'redactors' => array(),
            'aircrafts' => array(),
        );
        
        if (!empty($customFields['redactors'])) {
            $redactors = explode(',', $customFields['redactors']);
            foreach ($redactors as $index => $redactor) {
                $data['redactors'][$index] = array(
                    'fullname' => $redactor,
                    'team' => $redactorsteam,
                    'role' => 'CDS',
                );
            }
        }
        
        if (!empty($customFields['aircrafts'])) {
            $aircrafts = explode(',', $customFields['aircrafts']);
            foreach ($aircrafts as $index => $aircraft) {
                $data['aircrafts'][$index] = array(
                    'callsign' => $aircraft,
                );
            }
        }
        
        $jsonData = json_encode($data, JSON_PRETTY_PRINT);

        // Set the headers and the body
        $request->getHeaders()->addHeaders(array(
            'Content-Type' => 'application/json',
            'Content-Length' => strlen($jsonData)
        ));
        $request->setContent($jsonData);

        // Send the request
        $response = $client->send($request);

        if (!$response->isSuccess()) {
            throw new \RuntimeException(sprintf(
                'Erreur lors de la requête POST à l\'URL %s : %s',
                $efneUrl,
                $response->getStatusCode()
            ));
        }

        return $response;
    }
}