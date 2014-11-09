<?php

namespace Core\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
/**
 * @author Bruno Spyckerelle
 */
class NMB2BService implements ServiceLocatorAwareInterface {
    
    private $sl;
    
    private $nmb2b;
        
    public function getServiceLocator() {
        return $this->sl;
    }
    
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
        $this->sl = $serviceLocator;
    }
    
    public function __construct(ServiceLocatorInterface $sl) {
        $this->setServiceLocator($sl);
        $config = $this->sl->get('config');
        $this->nmb2b = $config['nm_b2b'];
    }
    
    private function getSoapClient(){
        try {
        $client = new \SoapClient(ROOT_PATH.$this->nmb2b['wsdl_path'].$this->nmb2b['airspace_wsdl_filename'],
                array(
                    'trace' => 1,
                    'connection_timeout' => 2000,
                    'exceptions' =>true, 
                    'cache_wsdl' => WSDL_CACHE_NONE,
                    'local_cert' => ROOT_PATH.$this->nmb2b['cert_path'],
                    'passphrase' => $this->nmb2b['cert_password']));
        } catch (\SoapFault $e){
            error_log(print_r($e, true));
        }
        return $client;
    }
    
    /**
     * Retrieve RSAs for a specific date
     * @param type $designators
     * @param \DateTime $date
     * @return type
     */
    public function getEAUPRSA($designators, \DateTime $date, $sequencenumber){
        $client = $this->getSoapClient();
                       
        $now = new \DateTime('now');
        
        $params = array(
            'sendTime' => $now->format('Y-m-d H:i:s'),
            'rsaDesignators' => $designators,
            'eaupId'=> array(
                'chainDate' => $date->format('Y-m-d'),
                'sequenceNumber' => $sequencenumber
            ));
        
        $client->retrieveEAUPRSAs($params);
        
        return $client->__getLastResponse();
    }
    
    /**
     * 
     * @param \DateTime $date
     */
    public function getEAUPChain(\DateTime $date){
        $client = $this->getSoapClient();
        $now = new \DateTime('now');
        
        $params = array(
            'sendTime' => $now->format('Y-m-d H:i:s'),
            'chainDate' => $date->format('Y-m-d')
        );
        $client->retrieveEAUPChain($params);
        return $client->__getLastResponse();
    }
}

