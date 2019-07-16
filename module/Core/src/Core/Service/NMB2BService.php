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
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 *
 * @author Bruno Spyckerelle
 */
class NMB2BService
{

    private $nmb2b;

    private $entityManager;
    
    private $config;
    
    private $airspaceClient = null;

    private $flowClient = null;

    private $errorEmail = false;

    private $version;
    private $floatVersion;

    public function __construct(EntityManager $entityManager, $config)
    {
        $this->entityManager = $entityManager;
        $this->config = $config;
        $this->nmb2b = $config['nm_b2b'];
    }

    private function getClient($wsdl) {
        $client = null;
        $options = array();
        $options['trace'] = 1;
        $options['connection_timeout'] = (array_key_exists("timeout", $this->nmb2b) ? $this->nmb2b['timeout'] : 30000);
        if(array_key_exists("timeout", $this->nmb2b)) {
            $socket_timeout = intval($this->nmb2b['timeout'] / 1000);
            ini_set('default_socket_timeout', $socket_timeout);
        }
        $options['exceptions'] = true;
        $options['cache_wsdl'] = WSDL_CACHE_NONE;
        $options['local_cert'] = ROOT_PATH . $this->nmb2b['cert_path'];
        $options['passphrase'] = $this->nmb2b['cert_password'];

        $options['stream_context'] = stream_context_create(array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        ));

        if (array_key_exists('proxy_host', $this->nmb2b)) {
            $options['proxy_host'] = $this->nmb2b['proxy_host'];
        }
        if (array_key_exists('proxy_port', $this->nmb2b)) {
            $options['proxy_port'] = $this->nmb2b['proxy_port'];
        }
        if (array_key_exists('force_url', $this->nmb2b)) {
            $options['location'] = $this->nmb2b['force_url'];
        }
        try {
            $client = new \SoapClient($wsdl, $options);
            $this->extractNMVersion($wsdl);
        } catch (\SoapFault $e) {
            $text = "Message d'erreur : \n";
            $text .= $e->getMessage()."\n";
            $text .= "Last Request Header\n";
            $text .= $client->__getLastRequestHeaders()."\n";
            $text .= "Last Request\n";
            $text .= $client->__getLastRequest();
            $text .= "Last Response Header\n";
            $text .= $client->__getLastResponseHeaders()."\n";
            $text .= "Last Response\n";
            $text .= $client->__getLastResponse()."\n";
            if($this->errorEmail) {
                $this->sendErrorEmail($text);
            } else {
                error_log($text);
            }
        }
        return $client;
    }

    private function getAirspaceSoapClient()
    {
        if($this->airspaceClient == null) {
            return $this->getClient(ROOT_PATH . $this->nmb2b['wsdl_path'] . $this->nmb2b['airspace_wsdl_filename']);
        }
        return $this->airspaceClient;
    }

    private function getFlowSoapClient() {
        if($this->flowClient == null) {
            return $this->getClient(ROOT_PATH . $this->nmb2b['wsdl_path'] . $this->nmb2b['flow_wsdl_filename']);
        }
        return $this->flowClient;
    }

    private function extractNMVersion($wsdl)
    {
        $data = file_get_contents($wsdl);
        if($data == false) {
            throw new \Exception("Unable to load WSDL");
        }
        $xml = new \DOMDocument();
        $xml->loadXML($data);

        $location = $xml->getElementsByTagNameNS("http://schemas.xmlsoap.org/wsdl/soap/", "address");
        foreach ($location as $l){
            $loc = $l->getAttribute("location");
            $url = explode('/', $loc);
            $this->version = end($url);
        }
        $aVersion = explode(".", $this->version);
        $this->floatVersion = (int) $aVersion[0] + ((int) $aVersion[1])*0.1 + ((int) $aVersion[2])*0.01;
        error_log($this->floatVersion);
    }

    /**
     * x.y.z version of NM Services
     * @return string
     */
    public function getNMVersion()
    {
        return $this->version;
    }

    public function getNMVersionFloat()
    {
        return $this->floatVersion;
    }

    /**
     * Retrieve RSAs for a specific date
     * 
     * @param type $designators            
     * @param \DateTime $date
     * @param int $sequencenumber
     * @return type
     */
    public function getEAUPRSA($designators, \DateTime $date, $sequencenumber)
    {
        $client = $this->getAirspaceSoapClient();
        
        $now = new \DateTime('now');
        
        $params = array(
            'sendTime' => $now->format('Y-m-d H:i:s'),
            'eaupId' => array(
                'chainDate' => $date->format('Y-m-d'),
                'sequenceNumber' => $sequencenumber
            )
        );
        
        if ($designators !== null && strlen($designators) > 0) {
            $params['rsaDesignators'] = $designators;
        }
        try {
            $client->retrieveEAUPRSAs($params);
        } catch (\SoapFault $e) {
            $text = "Message d'erreur : \n";
            $text .= $e->getMessage()."\n\n";
            $text .= "Last Request Header\n";
            $text .= $client->__getLastRequestHeaders()."\n\n";
            $text .= "Last Request\n";
            $text .= $client->__getLastRequest()."\n\n";
            $text .= "Last Response Header\n";
            $text .= $client->__getLastResponseHeaders()."\n\n";
            $text .= "Last Response\n";
            $text .= $client->__getLastResponse()."\n";
            if($this->errorEmail) {
                $this->sendErrorEmail($text);
            } else {
                error_log($text);
            }
            throw new \RuntimeException('Erreur NM B2B');
        }
        return $client->__getLastResponse();
    }

    /**
     *
     * @param \DateTime $date            
     */
    public function getEAUPChain(\DateTime $date)
    {
        $client = $this->getAirspaceSoapClient();
        $now = new \DateTime('now');
        
        $params = array(
            'sendTime' => $now->format('Y-m-d H:i:s'),
            'chainDate' => $date->format('Y-m-d')
        );
        try {
            $client->retrieveEAUPChain($params);
        } catch(\SoapFault $e){
            $text = "Message d'erreur : \n";
            $text .= $e->getMessage()."\n\n";
            $text .= "Last Request Header\n";
            $text .= $client->__getLastRequestHeaders()."\n\n";
            $text .= "Last Request\n";
            $text .= $client->__getLastRequest()."\n\n";
            $text .= "Last Response Header\n";
            $text .= $client->__getLastResponseHeaders()."\n\n";
            $text .= "Last Response\n";
            $text .= $client->__getLastResponse()."\n";
            if($this->errorEmail) {
                $this->sendErrorEmail($text);
            } else {
                error_log($text);
            }
            throw new \RuntimeException('Erreur NM B2B');
            return null;
        }
        return $client->__getLastResponse();
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param string $regex
     * @return null|string
     */
    public function getRegulationsList(\DateTime $start, \DateTime $end, $regex = "", $filtre = "") {
        $client = $this->getFlowSoapClient();
        $now = new \DateTime('now');

        if(strlen($regex) == 0 && strlen($filtre) == 0) $regex = "LF*";

        $params = array(
            'sendTime' => $now->format('Y-m-d H:i:s'),
            'queryPeriod' => array(
                'wef' => $start->format('Y-m-d H:i'),
                'unt' => $end->format('Y-m-d H:i')
            ),
            'dataset' => array(
                'type' => 'OPERATIONAL'
            ),
            'tvs' => array(
                'item' => explode(",",$regex)
            ),
            'requestedRegulationFields' => array(
                'item' => array('location','reason','lastUpdate', 'applicability', 'initialConstraints', 'regulationState')
            )
        );

        try {
            $client->queryRegulations($params);
        } catch (\SoapFault $e) {
            $text = "Message d'erreur : \n";
            $text .= $e->getMessage()."\n\n";
            $text .= "Last Request Header\n";
            $text .= $client->__getLastRequestHeaders()."\n\n";
            $text .= "Last Request\n";
            $text .= $client->__getLastRequest()."\n\n";
            $text .= "Last Response Header\n";
            $text .= $client->__getLastResponseHeaders()."\n\n";
            $text .= "Last Response\n";
            $text .= $client->__getLastResponse()."\n";
            if($this->errorEmail) {
                $this->sendErrorEmail($text);
            } else {
                error_log($text);
            }
            throw new \RuntimeException('Erreur NM B2B');
            return null;
        }
        return $client->__getLastResponse();
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
        $text = new \Zend\Mime\Part($textError);
        $text->type = \Zend\Mime\Mime::TYPE_TEXT;
        $text->charset = 'utf-8';

        $mimeMessage = new \Zend\Mime\Message();
        $mimeMessage->setParts(array(
            $text
        ));
        if (array_key_exists('emailfrom', $this->config) && array_key_exists('smtp', $this->config)) {
            $message = new \Zend\Mail\Message();
            $message->addTo($ipoEmail)
                ->addFrom($this->config['emailfrom'])
                ->setSubject("Erreur lors de l'import de l'AUP via NM B2B")
                ->setBody($mimeMessage);
    
            $transport = new \Zend\Mail\Transport\Smtp();
            $transportOptions = new \Zend\Mail\Transport\SmtpOptions($this->config['smtp']);
            $transport->setOptions($transportOptions);
            $transport->send($message);
        }
    }
}

