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
        $this->xml = new \SimpleXMLElement($strxml);
        //register namespaces
        $this->xml->registerXPathNamespace('adrmsg',"http://www.eurocontrol.int/cfmu/b2b/ADRMessage");
        $this->xml->registerXPathNamespace('gml', "http://www.opengis.net/gml/3.2");
        $this->xml->registerXPathNamespace('aixm', 'http://www.aixm.aero/schema/5.1');
    }
    
    /**
     * Get all Airspace elements whith a designator starting with <code>$designator</code>
     * @param type $designator
     * @return array
     */
    public function getAirspacesWithDesignator($designator){
        return $this->xml->xpath('//aixm:Airspace//aixm:AirspaceTimeSlice[starts-with(aixm:designator,"'.$designator.'")]/../..');
    }
    
    /**
     * Return ICAO Designator of an Airspace element
     * @param \SimpleXMLElement $airspace
     * @return type
     * @throws \UnexpectedValueException
     */
    public static function getAirspaceDesignator(\SimpleXMLElement $airspace){
        if($airspace->getName() === 'Airspace'){
            return $airspace
                        ->children('http://www.aixm.aero/schema/5.1')
                        ->timeSlice[0]
                        ->children('http://www.aixm.aero/schema/5.1')
                        ->AirspaceTimeSlice
                        ->children('http://www.aixm.aero/schema/5.1')->designator;
        } else {
            throw new \UnexpectedValueException("Airspace Element expected.");
        }
    }
    
    /**
     * 
     * @param \SimpleXMLElement $airspace
     * @return string
     * @throws \UnexpectedValueException
     */
    public static function getAirspaceTimeBegin(\SimpleXMLElement $airspace){
        if($airspace->getName() === 'Airspace'){
            $timeslices = $airspace
                        ->children('http://www.aixm.aero/schema/5.1')
                        ->timeSlice;
            if(count($timeslices) === 2 ){
                return $timeslices[1]
                        ->children('http://www.aixm.aero/schema/5.1')
                        ->AirspaceTimeSlice
                        ->children('http://www.opengis.net/gml/3.2')
                        ->validTime
                        ->children('http://www.opengis.net/gml/3.2')
                        ->TimePeriod
                        ->children('http://www.opengis.net/gml/3.2')
                        ->beginPosition;
            } else {
                throw new \UnexpectedValueException("Not a valid Airspace.");
            }
        } else {
            throw new \UnexpectedValueException("Airspace Element expected.");
        }
    }
    
    /**
     * 
     * @param \SimpleXMLElement $airspace
     * @return \DateTime
     */
    public static function getAirspaceDateTimeBegin(\SimpleXMLElement $airspace){
        $timeBegin = EAUPRSAs::getAirspaceTimeBegin($airspace);
        return new \DateTime($timeBegin."+00:00");
    }
    
    /**
     * 
     * @param \SimpleXMLElement $airspace
     * @return string
     * @throws \UnexpectedValueException
     */
    public static function getAirspaceTimeEnd(\SimpleXMLElement $airspace){
        if($airspace->getName() === 'Airspace'){
            $timeslices = $airspace
                        ->children('http://www.aixm.aero/schema/5.1')
                        ->timeSlice;
            if(count($timeslices) === 2 ){
                return $timeslices[1]
                        ->children('http://www.aixm.aero/schema/5.1')
                        ->AirspaceTimeSlice
                        ->children('http://www.opengis.net/gml/3.2')
                        ->validTime
                        ->children('http://www.opengis.net/gml/3.2')
                        ->TimePeriod
                        ->children('http://www.opengis.net/gml/3.2')
                        ->endPosition;
            } else {
                throw new \UnexpectedValueException("Not a valid Airspace.");
            }
        } else {
            throw new \UnexpectedValueException("Airspace Element expected.");
        }
    }
    
    /**
     * 
     * @param \SimpleXMLElement $airspace
     * @return \DateTime
     */
    public static function getAirspaceDateTimeEnd(\SimpleXMLElement $airspace){
        $timeEnd = EAUPRSAs::getAirspaceTimeEnd($airspace);
        return new \DateTime($timeEnd.'+00:00');
    }
    
    /**
     * 
     * @param \SimpleXMLElement $airspace
     * @return String
     * @throws \UnexpectedValueException
     */
    public static function getAirspaceUpperLimit(\SimpleXMLElement $airspace){
        if($airspace->getName() === 'Airspace'){
            $timeslices = $airspace
                        ->children('http://www.aixm.aero/schema/5.1')
                        ->timeSlice;
            if(count($timeslices) === 2 ){
                return $timeslices[1]
                        ->children('http://www.aixm.aero/schema/5.1')
                        ->AirspaceTimeSlice
                        ->children('http://www.aixm.aero/schema/5.1')
                        ->activation
                        ->children('http://www.aixm.aero/schema/5.1')
                        ->AirspaceActivation
                        ->children('http://www.aixm.aero/schema/5.1')
                        ->levels
                        ->children('http://www.aixm.aero/schema/5.1')
                        ->AirspaceLayer
                        ->children('http://www.aixm.aero/schema/5.1')
                        ->upperLimit;
            }
        } else {
            throw new \UnexpectedValueException("Airspace Element expected.");
        }
    }
    
        /**
     * 
     * @param \SimpleXMLElement $airspace
     * @return String
     * @throws \UnexpectedValueException
     */
    public static function getAirspaceLowerLimit(\SimpleXMLElement $airspace){
        if($airspace->getName() === 'Airspace'){
            $timeslices = $airspace
                        ->children('http://www.aixm.aero/schema/5.1')
                        ->timeSlice;
            if(count($timeslices) === 2 ){
                return $timeslices[1]
                        ->children('http://www.aixm.aero/schema/5.1')
                        ->AirspaceTimeSlice
                        ->children('http://www.aixm.aero/schema/5.1')
                        ->activation
                        ->children('http://www.aixm.aero/schema/5.1')
                        ->AirspaceActivation
                        ->children('http://www.aixm.aero/schema/5.1')
                        ->levels
                        ->children('http://www.aixm.aero/schema/5.1')
                        ->AirspaceLayer
                        ->children('http://www.aixm.aero/schema/5.1')
                        ->lowerLimit;
            }
        } else {
            throw new \UnexpectedValueException("Airspace Element expected.");
        }
    }
}