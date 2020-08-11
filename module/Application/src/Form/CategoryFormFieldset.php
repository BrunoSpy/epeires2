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
namespace Application\Form;

use Laminas\Form\Fieldset;
use Laminas\InputFilter\InputFilterProviderInterface;

/**
 *
 * @author Bruno Spyckerelle
 *        
 */
class CategoryFormFieldset extends Fieldset implements InputFilterProviderInterface
{

    public function __construct($array)
    {
        parent::__construct('categories');
        
        $this->add(array(
            'name' => 'root_categories',
            'type' => 'Laminas\Form\Element\Select',
            'options' => array(
                'label' => 'Catégorie',
                'value_options' => $array,
                'empty_option' => 'Choisir la catégorie'
            ),
            'attributes' => array(
                'id' => 'root_categories'
            )
        ));
        
        $this->add(array(
            'name' => 'subcategories',
            'type' => 'Laminas\Form\Element\Select',
            'options' => array(
                'label' => 'Sous-catégorie',
                'empty_option' => 'Veuillez choisir une catégorie'
            ),
            'attributes' => array(
                'id' => 'subcategories'
            )
        ));
    }

    public function getInputFilterSpecification()
    {
        return array(
            'root_categories' => array(
                'required' => false
            ),
            'subcategories' => array(
                'required' => false
            )
        )
        ;
    }
}