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

namespace Core\Exceptions;

class MAPDException extends \RuntimeException
{

    const ERRORS = [
        "E007" => "Impossible de modifier le nom d'une zone.",
        "E010" => "Impossible de créer la zone, la durée maximale autorisée est de 24h.",
        "E011" => "Il existe déjà une zone sur le créneau horaire spécifié.",
        "E020" => "Droits insuffisants pour effectuer cette opération."
    ];

    public function __construct(string $body, int $statusCode)
    {
        $json = json_decode($body, true);
        if($json !== null && array_key_exists('errors', $json)) {
            $message = "";
            foreach ($json['errors'] as $error) {
                $message .= self::ERRORS[$error['code']];
            }
            parent::__construct($message);
        } else {
            //message au format inconnu
            parent::__construct('Erreur '.$statusCode.' : '.$body);
        }
    }
}