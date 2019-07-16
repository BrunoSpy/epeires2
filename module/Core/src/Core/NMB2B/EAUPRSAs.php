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
namespace Core\NMB2B;

/**
 * Description of EAUPRSAs
 *
 * @author Bruno Spyckerelle
 */
class EAUPRSAs
{

    private $xml = null;

    private $aixmNS;

    public function __construct($strxml, $version)
    {
        if($strxml != null) {
            $this->xml = new \SimpleXMLElement($strxml);
            if($version >= 23) {
                $this->aixmNS = 'http://www.aixm.aero/schema/5.1.1';
            } else {
                $this->aixmNS = 'http://www.aixm.aero/schema/5.1';
            }
            // register namespaces
            $this->xml->registerXPathNamespace('adrmsg', "http://www.eurocontrol.int/cfmu/b2b/ADRMessage");
            $this->xml->registerXPathNamespace('gml', "http://www.opengis.net/gml/3.2");
            $this->xml->registerXPathNamespace('aixm', $this->aixmNS);
        }
    }

    /**
     * Get all Airspace elements whith a designator starting with <code>$designator</code>
     * 
     * @param type $designator            
     * @return array
     */
    public function getAirspacesWithDesignator($designator)
    {
        if($this->xml != null) {
            return $this->xml->xpath('//aixm:Airspace//aixm:AirspaceTimeSlice[starts-with(aixm:designator,"' . $designator . '")]/../..');
        } else {
            return null;
        }
    }

    public static function getAixmNS($version)
    {
        if($version < 23) {
            return 'http://www.aixm.aero/schema/5.1';
        } else {
            return 'http://www.aixm.aero/schema/5.1.1';
        }
    }

    /**
     * Return ICAO Designator of an Airspace element
     * 
     * @param \SimpleXMLElement $airspace            
     * @return type
     * @throws \UnexpectedValueException
     */
    public static function getAirspaceDesignator(\SimpleXMLElement $airspace, $version)
    {
        if ($airspace->getName() === 'Airspace') {
            $timeslices = $airspace->children(self::getAixmNS($version))->timeSlice;
            foreach ($timeslices as $timeslice) {
                $airspacetimeslice = $timeslice->children(self::getAixmNS($version))->AirspaceTimeSlice;
                foreach ($airspacetimeslice->children(self::getAixmNS($version)) as $child) {
                    if ($child->getName() === 'designator') {
                        return $child;
                    }
                }
            }
            return "";
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
    public static function getAirspaceTimeBegin(\SimpleXMLElement $airspace, $version)
    {
        if ($airspace->getName() === 'Airspace') {
            $timeslices = $airspace->children(self::getAixmNS($version))->timeSlice;
            if (count($timeslices) >= 2) {
                foreach ($timeslices as $timeslice) {
                    $validtime = $timeslice
                    ->children(self::getAixmNS($version))
                    ->AirspaceTimeSlice
                    ->children('http://www.opengis.net/gml/3.2')
                    ->validTime;
                    foreach ($validtime->children('http://www.opengis.net/gml/3.2') as $child) {
                        if ($child->getName() === 'TimePeriod') {
                            return $child->children('http://www.opengis.net/gml/3.2')->beginPosition;
                        }
                    }
                }
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
     * @param $version
     * @return \DateTime
     */
    public static function getAirspaceDateTimeBegin(\SimpleXMLElement $airspace, $version)
    {
        $timeBegin = EAUPRSAs::getAirspaceTimeBegin($airspace, $version);
        return new \DateTime($timeBegin . "+00:00");
    }

    /**
     *
     * @param \SimpleXMLElement $airspace            
     * @return string
     * @throws \UnexpectedValueException
     */
    public static function getAirspaceTimeEnd(\SimpleXMLElement $airspace, $version)
    {
        if ($airspace->getName() === 'Airspace') {
            $timeslices = $airspace->children(self::getAixmNS($version))->timeSlice;
            if (count($timeslices) === 2) {
                foreach ($timeslices as $timeslice) {
                    $validtime = $timeslice
                    ->children(self::getAixmNS($version))
                    ->AirspaceTimeSlice
                    ->children('http://www.opengis.net/gml/3.2')
                    ->validTime;
                    foreach ($validtime->children('http://www.opengis.net/gml/3.2') as $child) {
                        if ($child->getName() === 'TimePeriod') {
                            return $child->children('http://www.opengis.net/gml/3.2')->endPosition;
                        }
                    }
                }
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
    public static function getAirspaceDateTimeEnd(\SimpleXMLElement $airspace, $version)
    {
        $timeEnd = EAUPRSAs::getAirspaceTimeEnd($airspace, $version);
        return new \DateTime($timeEnd . '+00:00');
    }

    /**
     *
     * @param \SimpleXMLElement $airspace
     * @param $version
     * @return String
     * @throws \UnexpectedValueException
     */
    public static function getAirspaceUpperLimit(\SimpleXMLElement $airspace, $version)
    {
        if ($airspace->getName() === 'Airspace') {
            $timeslices = $airspace->children(self::getAixmNS($version))->timeSlice;
            if (count($timeslices) === 2) {
                foreach ($timeslices as $timeslice) {
                    $airspacetimeslice = $timeslice->children(self::getAixmNS($version))->AirspaceTimeSlice;
                    foreach ($airspacetimeslice->children(self::getAixmNS($version)) as $child) {
                        if ($child->getName() === 'activation') {
                            return $child
                                    ->children(self::getAixmNS($version))
                                    ->AirspaceActivation
                                    ->children(self::getAixmNS($version))
                                    ->levels
                                    ->children(self::getAixmNS($version))
                                    ->AirspaceLayer
                                    ->children(self::getAixmNS($version))
                                    ->upperLimit;
                        }
                    }
                }
            }
        } else {
            throw new \UnexpectedValueException("Airspace Element expected.");
        }
    }

    /**
     *
     * @param \SimpleXMLElement $airspace
     * @param $version
     * @return String
     * @throws \UnexpectedValueException
     */
    public static function getAirspaceLowerLimit(\SimpleXMLElement $airspace, $version)
    {
        if ($airspace->getName() === 'Airspace') {
            $timeslices = $airspace->children(self::getAixmNS($version))->timeSlice;
            if (count($timeslices) === 2) {
                foreach ($timeslices as $timeslice) {
                    $airspacetimeslice = $timeslice->children(self::getAixmNS($version))->AirspaceTimeSlice;
                    foreach ($airspacetimeslice->children(self::getAixmNS($version)) as $child) {
                        if ($child->getName() === 'activation') {
                            return $child
                                    ->children(self::getAixmNS($version))
                                    ->AirspaceActivation
                                    ->children(self::getAixmNS($version))
                                    ->levels
                                    ->children(self::getAixmNS($version))
                                    ->AirspaceLayer
                                    ->children(self::getAixmNS($version))
                                    ->lowerLimit;
                        }
                    }
                }
            }
        } else {
            throw new \UnexpectedValueException("Airspace Element expected.");
        }
    }
}