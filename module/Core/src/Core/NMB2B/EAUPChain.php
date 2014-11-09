<?php

namespace Core\NMB2B;

/**
 * Description of EAUPChain
 *
 * @author Bruno Spyckerelle
 */
class EAUPChain {

    /**
     *
     * @var type SimpleXMLElement
     */
    private $xml;
    
    /**
     * 
     * @param type $strxml
     */
    public function __construct($strxml) {
        $this->xml = new \SimpleXMLElement($strxml);
    }

    public function getLastSequenceNumber(){
        $sequenceNumber = -1;
        foreach($this->xml->children('http://schemas.xmlsoap.org/soap/envelope/')
                ->Body
                ->children('eurocontrol/cfmu/b2b/AirspaceServices')
                ->EAUPChainRetrievalReply
                ->children('')
                ->data
                ->chain
                ->eaups as $eaup){
            $sequenceNumber = $eaup->eaupId->sequenceNumber;
        }
        return $sequenceNumber;
    }
    
}
