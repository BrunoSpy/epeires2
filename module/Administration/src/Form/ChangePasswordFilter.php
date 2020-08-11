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
namespace Administration\Form;

use Laminas\InputFilter\InputFilter;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class ChangePasswordFilter extends InputFilter
{

    public function __construct()
    {
        $identityParams = array(
            'name' => 'id',
            'required' => true,
            'validators' => array()
        );
        
        $this->add($identityParams);
        
        $this->add(array(
            'name' => 'newCredential',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'min' => 6
                    )
                )
            ),
            'filters' => array(
                array(
                    'name' => 'StringTrim'
                )
            )
        ));
        
        $this->add(array(
            'name' => 'newCredentialVerify',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'min' => 6
                    )
                ),
                array(
                    'name' => 'identical',
                    'options' => array(
                        'token' => 'newCredential'
                    )
                )
            ),
            'filters' => array(
                array(
                    'name' => 'StringTrim'
                )
            )
        ));
    }
}
