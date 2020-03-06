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

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilter;

/**
 * Class ImportForm
 * @package Administration\Form
 *
 */
class ImportForm extends Form
{

    public function __construct()
    {
        parent::__construct('json-form');

        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');

        $this->addElements();

        $this->addInputFilter();
    }

    protected function addElements()
    {
        $this->add([
            'type' => 'file',
            'name' => 'jsonfile',
            'attributes'=>[
                'id' => 'jsonfile'
            ],
            'options'=>[
                'label'=>'Fichier au format JSON'
            ]
        ]);

        $this->add([
            'type'=>'submit',
            'name'=>'submitjson',
            'attributes'=>[
                'value' => 'Importer',
                'id'=>'submitjson',
                'class' => 'btn btn-primary'
            ]
        ]);
    }

    private function addInputFilter()
    {
        $inputFilter= new InputFilter();
        $this->setInputFilter($inputFilter);

        $inputFilter->add([
           'type' => 'Laminas\InputFilter\FileInput',
           'name' => 'jsonfile',
           'required' => true,
           'validators' => [
               ['name' => 'FileUploadFile'],
               [
                   'name' => 'FileMimeType',
                   'options' => ['mimeType' => ['application/json', 'text/plain']]
               ]
           ]
        ]);
    }

}