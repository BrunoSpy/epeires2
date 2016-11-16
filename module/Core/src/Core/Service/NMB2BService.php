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

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

/**
 *
 * @author Bruno Spyckerelle
 */
class NMB2BService implements ServiceLocatorAwareInterface
{

    private $sl;

    private $nmb2b;

    private $client = null;

    public function getServiceLocator()
    {
        return $this->sl;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->sl = $serviceLocator;
    }

    public function __construct(ServiceLocatorInterface $sl)
    {
        $this->setServiceLocator($sl);
        $config = $this->sl->get('config');
        $this->nmb2b = $config['nm_b2b'];
    }

    private function getSoapClient()
    {
        if($this->client == null) {
            $options = array();
            $options['trace'] = 1;
            $options['connection_timeout'] = (array_key_exists("timeout", $this->nmb2b) ? $this->nmb2b['timeout'] : 30000);
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
                $this->client = new \SoapClient(ROOT_PATH . $this->nmb2b['wsdl_path'] . $this->nmb2b['airspace_wsdl_filename'], $options);
            } catch (\SoapFault $e) {
                $text = "Message d'erreur : \n";
                $text .= $e->getMessage()."\n";
                $text .= "Last Request Header\n";
                $text .= $this->client->__getLastRequestHeaders()."\n";
                $text .= "Last Request\n";
                $text .= $this->client->__getLastRequest();
                $text .= "Last Response Header\n";
                $text .= $this->client->__getLastResponseHeaders()."\n";
                $text .= "Last Response\n";
                $text .= $this->client->__getLastResponse()."\n";
                $this->sendErrorEmail($text);
            }
        }
        return $this->client;
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
        $client = $this->getSoapClient();
        
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
            $this->sendErrorEmail($text);
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
        $client = $this->getSoapClient();
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
            $this->sendErrorEmail($text);
            throw new \RuntimeException('Erreur NM B2B');
            return null;
        }
        return $client->__getLastResponse();
    }

    public function sendErrorEmail($textError) {

        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        //TODO récupérer proprement l'organisation concernée
        $org = $objectManager->getRepository('Application\Entity\Organisation')->findAll();
        $ipoEmail = $org[0]->getIpoEmail();

        // prepare body with file attachment
        $text = new \Zend\Mime\Part($textError);
        $text->type = \Zend\Mime\Mime::TYPE_TEXT;
        $text->charset = 'utf-8';

        $mimeMessage = new \Zend\Mime\Message();
        $mimeMessage->setParts(array(
            $text
        ));

        $config = $this->getServiceLocator()->get('config');
        $message = new \Zend\Mail\Message();
        $message->addTo($ipoEmail)
            ->addFrom($config['emailfrom'])
            ->setSubject("Erreur lors de l'import de l'AUP via NM B2B")
            ->setBody($mimeMessage);

        $transport = new \Zend\Mail\Transport\Smtp();
        $transportOptions = new \Zend\Mail\Transport\SmtpOptions($config['smtp']);
        $transport->setOptions($transportOptions);
        $transport->send($message);
    }
}

