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
namespace Application\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;
use Application\Entity\Frequency;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class Sector extends AbstractHelper
{

    public function __invoke(Frequency $frequency, $name, $groupid = null)
    {
        $noBackup = !$frequency->getBackupantenna() && !$frequency->getBackupantennaclimax();

        $html = "<ul class=\"sector dropdown-menu\" data-freq=\"" . $frequency->getId() . "\">";
        $html .= "<div class=\"sector-color frequency-" . $frequency->getId() . "\">";
        $html .= "<li class=\"sector-name\">" . $name . "</li>";
        $html .= "<li class=\"sector-freq\"><a href=\"#\" class=\"actions-freq\" data-freq=\"" . $frequency->getId() . "\" ". ($groupid != null ? 'data-groupid="' . $groupid . '"' : '') .">" . $frequency->getValue() . "</a></li>";
        $html .= "</div>";
        $html .= "<li class=\"divider\"></li>";
        $html .= "<ul class=\"antennas\">";
        $html .= "<div data-antennaid=\""
            . $frequency->getMainantenna()->getId()
            . "\" class=\"mainantenna-color antenna-color "
            . ($noBackup ? "mainantenna-wide " : "")
            . "antenna-" . $frequency->getMainAntenna()->getId() . "\">";
        $html .= "<li><a href=\"#\" class=\"actions-antenna\" data-id=\"" . $frequency->getMainantenna()->getId() . "\">" . $frequency->getMainAntenna()->getShortname() . "</a></li>";
        $html .= "</div>";
        if($frequency->getBackupantenna()) {
            $html .= "<div data-antennaid=\"" . $frequency->getBackupantenna()->getId() . "\" class=\"backupantenna-color antenna-color antenna-" . $frequency->getBackupAntenna()->getId() . "\">";
            $html .= "<li><a href=\"#\" class=\"actions-antenna\" data-id=\"" . $frequency->getBackupantenna()->getId() . "\">" . $frequency->getBackupAntenna()->getShortname() . "</a></li>";
            $html .= "</div>";
        }
        $html .= "</ul>";
        
        if ($frequency->getMainantennaclimax() || $frequency->getBackupantennaclimax()) {
            $html .= "<ul class=\"antennas\">";
            $html .= "<div data-antennaid=\"" . ($frequency->getMainantennaclimax() ? $frequency->getMainantennaclimax()->getId() : "")
                . "\" class=\"mainantenna-color antenna-color antenna-climax-color "
                . ($noBackup ? "mainantenna-wide " : "")
                . "antenna-" . ($frequency->getMainantennaclimax() ? $frequency->getMainantennaclimax()->getId() : "") . "\">";
            $html .= "<li>" . ($frequency->getMainantennaclimax() ? "<a href=\"#\" class=\"actions-antenna\" data-id=\"" . $frequency->getMainantennaclimax()->getId() . "\">" . $frequency->getMainantennaclimax()->getShortname() . "</a>" : "") . "</li>";
            $html .= "</div>";
            if ($frequency->getBackupantennaclimax()) {
                $html .= "<div data-antennaid=\"" . ($frequency->getBackupantennaclimax() ? $frequency->getBackupantennaclimax()->getId() : "") . "\" class=\"backupantenna-color antenna-color antenna-climax-color antenna-" . ($frequency->getBackupantennaclimax() ? $frequency->getBackupantennaclimax()->getId() : "") . "\">";
                $html .= "<li>" . ($frequency->getBackupantennaclimax() ? "<a href=\"#\" class=\"actions-antenna\" data-id=\"" . $frequency->getBackupantennaclimax()->getId() . "\">" . $frequency->getBackupantennaclimax()->getShortname() . "</a>" : "") . "</li>";
                $html .= "</div>";
            }
            $html .= "</ul>";
        }
        
        $html .= '</ul>';
        
        return $html;
    }
}