<?php

namespace Core\NMB2B;

/**
 * Description of EAUPRSAs
 *
 * @author Bruno Spyckerelle
 */
class EAUPRSAs {
    
    private $xml;
    
    public function __construct($strxml) {
        $this->xml = new SimpleXMLElement($strxml);
    }
    
    
}
