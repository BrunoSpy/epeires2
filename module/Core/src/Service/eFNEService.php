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
        $client->setEncType('multipart/form-data');

        $request = new Request();
        $request->setMethod('POST');
        $request->setUri($efneUrl);

        // CHAMPS OBLIGATOIRES
        $request->getPost()->set('event_date', $customFields['date']);
        $request->getPost()->set('position', $customFields['position']);
        $request->getPost()->set('regroupement', $customFields['regroupement']);
        $request->getPost()->set('description', $customFields['description']);
        $request->getPost()->set('options.proceed', 'true');
        $request->getPost()->set('options.bypass_validation', 'true');

        // CHAMPS SUPPLÉMENTAIRES (possibilité d'en rajouter)
        if (!empty($customFields['lieu'])) {
            $request->getPost()->set('lieu', $customFields['lieu']);
        }
        if (!empty($customFields['redactors'])) {
            $redactors = explode(',', $customFields['redactors']);
            foreach ($redactors as $index => $redactor) {
                $request->getPost()->set("redactors[$index].fullname", $redactor);
                $request->getPost()->set("redactors[$index].team", $redactorsteam);
                $request->getPost()->set("redactors[$index].role", "CDS");
            }
        }
        if (!empty($customFields['aircrafts'])) {
            $aircrafts = explode(',', $customFields['aircrafts']);
            foreach ($aircrafts as $index => $aircraft) {
                $request->getPost()->set("aircrafts[$index].callsign", $aircraft);
            }
        }
        
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