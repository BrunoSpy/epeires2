<?php

namespace Core\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Soap\Client as SoapClient;

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
        return new SoapClient(ROOT_PATH.$this->nmb2b['wsdl_path'].'/AirspaceServices_PREOPS_18.5.0.wsdl', array(
            'local_cert' => ROOT_PATH.$this->nmb2b['cert_path'],
            'passphrase' => $this->nmb2b['cert_password']
        ));
    }
    
    public function getEAUPRSA(){
        error_log('test');
        $client = $this->getSoapClient();
                       
        $now = new \DateTime('now');
        
        $params = array(
            'sendTime' => $now->format('Y-m-d H:i:s'),
            'rsaDesignators' => 'LF*',
            'eaupId'=> array(
                'chainDate' => '2014-10-28',
                'sequenceNumber' => '1'
            ));
        $result = $client->retrieveEAUPRSAs($params);
        error_log(print_r($result));
    }
}

