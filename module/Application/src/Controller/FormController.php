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
namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;

/**
 *
 * @author Bruno Spyckerelle
 * @license https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
 */
abstract class FormController extends AbstractActionController
{

    protected function processFormMessages($messages, &$json = null)
    {
        if (is_array($json) && ! isset($json['error'])) {
            $json['error'] = array();
        }
        foreach ($messages as $key => $message) {
            foreach ($message as $mkey => $mvalue) { // les messages sont de la forme 'type_message' => 'message'
                if (is_array($mvalue)) {
                    foreach ($mvalue as $nkey => $nvalue) { // les fieldsets sont un niveau en dessous
                        if ($json) {
                            if(is_array($nvalue)) {
                                $nvalue = json_encode($nvalue);
                            }
                            $json['error'][] = "Champ " . addslashes($mkey) . " incorrect : " . addslashes($nvalue);
                        } else {
                            $this->flashMessenger()->addErrorMessage("Champ " . addslashes($mkey) . " incorrect : " . addslashes($nvalue));
                        }
                    }
                } else {
                    if ($json) {
                        $json['error'][] = "Champ " . addslashes($key) . " incorrect : " . addslashes($mvalue);
                    } else {
                        $this->flashMessenger()->addErrorMessage("Champ " . addslashes($key) . " incorrect : " . addslashes($mvalue));
                    }
                }
            }
        }
    }
}