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

use LmcUser\Form\ProvidesEventsForm;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class ChangePassword extends ProvidesEventsForm
{

    /**
     *
     * @var /LmcUser/Options/AuthenticationOptionsInterface
     */
    protected $authOptions;

    public function __construct($name = null)
    {
        parent::__construct($name);
        
        $this->add(array(
            'name' => 'id',
            'options' => array(
                'label' => ''
            ),
            'attributes' => array(
                'type' => 'hidden'
            )
        ));
        
        $this->add(array(
            'name' => 'newCredential',
            'options' => array(
                'label' => 'Nouveau mot de passe'
            ),
            'attributes' => array(
                'type' => 'password'
            )
        ));
        
        $this->add(array(
            'name' => 'newCredentialVerify',
            'options' => array(
                'label' => 'Vérification nouveau mot de passe'
            ),
            'attributes' => array(
                'type' => 'password'
            )
        ));
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'value' => 'Enregistrer',
                'type' => 'submit',
                'class' => 'btn btn-small btn-primary'
            )
        ));
    }
}
