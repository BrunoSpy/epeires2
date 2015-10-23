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
 * Description of EAUPChain
 *
 * @author Bruno Spyckerelle
 */
class EAUPChain
{

    /**
     *
     * @var type SimpleXMLElement
     */
    private $xml;

    /**
     *
     * @param type $strxml            
     */
    public function __construct($strxml)
    {
        $this->xml = new \SimpleXMLElement($strxml);
    }

    public function getLastSequenceNumber()
    {
        $sequenceNumber = - 1;
        foreach ($this->xml
            ->children('http://schemas.xmlsoap.org/soap/envelope/')
            ->Body
            ->children('eurocontrol/cfmu/b2b/AirspaceServices')
            ->EAUPChainRetrievalReply
            ->children('')
            ->data
            ->chain
            ->eaups as $eaup) {
            $sequenceNumber = $eaup->eaupId->sequenceNumber;
        }
        return $sequenceNumber;
    }
}
