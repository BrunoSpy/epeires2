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
 * Class RegulationListReply
 * @package Core\NMB2B
 * @author Bruno Spyckerelle
 * @license AGPL3
 */
class RegulationListReply
{
    private $xml = null;

    public function __construct($strxml)
    {
        if($strxml != null) {
            $this->xml = new \SimpleXMLElement($strxml);
        }
    }

    public function getRegulations() {
        if($this->xml != null) {
            return $this->xml->xpath('//regulations/item');
        } else {
            return null;
        }
    }

    /**
     * @param $regulation
     * @return string
     */
    public static function getDataId($regulation) {
        return (string) $regulation->dataId;
    }

    /**
     * @param $regulation
     * @return string
     */
    public static function getRegulationName($regulation) {
        return (string) $regulation->location->id;
    }

    public static function getDescription($regulation) {
        return (string) $regulation->location->description;
    }

    public static function getNormalRate($regulation)
    {
        return (string) $regulation->initialConstraints->normalRate;
    }

    /**
     * @param $regulation
     * @return string
     */
    public static function getReason($regulation) {
        return (string) $regulation->reason;
    }

    /**
     * @param $regulation
     * @return \DateTime
     */
    public static function getDateTimeStart($regulation) {
        $time = $regulation->applicability->wef . '+00:00';
        return new \DateTime($time);
    }

    /**
     * @param $regulation
     * @return \DateTime
     */
    public static function getDateTimeEnd($regulation) {
        $time = $regulation->applicability->unt . '+00:00';
        return new \DateTime($time);
    }
}