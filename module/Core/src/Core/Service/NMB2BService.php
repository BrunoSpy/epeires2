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
use DSNA\NMB2BDriver\Exception\WSDLFileUnavailable;
use DSNA\NMB2BDriver\Models\EAUPChain;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use DSNA\NMB2BDriver\NMB2BClient;

/**
 *
 * @author Bruno Spyckerelle
 */
class NMB2BService
{

    private $nmb2b;

    private $entityManager;
    
    private $config;
    
    private $nmb2bClient;

    private $errorEmail = false;

    public function __construct(EntityManager $entityManager, $config)
    {
        $this->entityManager = $entityManager;
        $this->config = $config;
        $this->nmb2b = $config['nm_b2b'];

        $options = array();
        $options['connection_timeout'] = (array_key_exists("timeout", $this->nmb2b) ? $this->nmb2b['timeout'] : 30000);
        if(array_key_exists("timeout", $this->nmb2b)) {
            $socket_timeout = intval($this->nmb2b['timeout'] / 1000);
            ini_set('default_socket_timeout', $socket_timeout);
        }
        if (array_key_exists('proxy_host', $this->nmb2b)) {
            $options['proxy_host'] = $this->nmb2b['proxy_host'];
        }
        if (array_key_exists('proxy_port', $this->nmb2b)) {
            $options['proxy_port'] = $this->nmb2b['proxy_port'];
        }
        if (array_key_exists('force_url', $this->nmb2b)) {
            $options['location'] = $this->nmb2b['force_url'];
        }

        $this->nmb2bClient = new NMB2BClient(
            ROOT_PATH . $this->nmb2b['cert_path'],
            $this->nmb2b['cert_password'],
            $this->nmb2b['wsdl'],
            $options
        );
    }

    /**
     * Retrieve RSAs for a specific date
     * 
     * @param type $designators            
     * @param \DateTime $date
     * @param int $sequencenumber
     * @return type
     * @throws WSDLFileUnavailable
     */
    public function getEAUPRSA($designators, \DateTime $date, $sequencenumber)
    {

        try {
            return $this->nmb2bClient->airspaceServices()->retrieveEAUPRSAs($designators, $date, $sequencenumber);
        } catch (\SoapFault $e) {
            $text = "Message d'erreur : \n";
            $text .= $e->getMessage()."\n\n";
            $text .= "Last Request Header\n";
            $text .= $this->nmb2bClient->airspaceServices()->getFullErrorMessage();
            if($this->errorEmail) {
                $this->sendErrorEmail($text);
            } else {
                error_log($text);
            }
            throw new \RuntimeException('Erreur NM B2B');
        }catch (WSDLFileUnavailable $e) {
            error_log($e->getMessage());
        }
    }

    /**
     *
     * @param \DateTime $date
     * @return EAUPChain
     */
    public function getEAUPChain(\DateTime $date)
    {
        try {
            return $this->nmb2bClient->airspaceServices()->retrieveEAUPChain($date);
        } catch(\SoapFault $e){
            $text = "Message d'erreur : \n";
            $text .= $e->getMessage()."\n\n";
            $text .= $this->nmb2bClient->airspaceServices()->getFullErrorMessage();
            if($this->errorEmail) {
                $this->sendErrorEmail($text);
            } else {
                error_log($text);
            }
            throw new \RuntimeException('Erreur NM B2B');
            return null;
        } catch (WSDLFileUnavailable $e) {
            error_log($e->getMessage());
        }
    }

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param string $regex
     * @return null|string
     */
    public function getRegulationsList(\DateTime $start, \DateTime $end, $regex = "LF*") {

        try {
            return $this->nmb2bClient->flowServices()->queryRegulations($start, $end, $regex);
        } catch (\SoapFault $e) {
            $text = "Message d'erreur : \n";
            $text .= $e->getMessage()."\n\n";
            $text .= $this->nmb2bClient->flowServices()->getFullErrorMessage();
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

