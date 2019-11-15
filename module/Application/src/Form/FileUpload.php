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

use Zend\InputFilter;
use Zend\Form\Form;
use Zend\Form\Element;

/**
 * Description of FileUpload
 *
 * @author Bruno Spyckerelle
 */
class FileUpload extends Form
{

    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);
        $this->addElements();
        $this->setInputFilter($this->createInputFilter());
    }

    public function addElements()
    {
        $file = new Element\File('file');
        $file->setLabel("Import ")->setAttributes(array(
            'id' => 'file',
            'multiple' => false
        ));
        
        $name = new Element\Text('name');
        $name->setLabel('Fichier');
        $name->setAttribute('placeholder', 'Titre');
        $name->setAttribute('class', 'input-medium');
        
        $ref = new Element\Text('reference');
        $ref->setLabel("Référence ");
        $ref->setAttribute('placeholder', 'Ref. (facultatif)');
        $ref->setAttribute('class', 'input-medium');
        
        $url = new Element\Text('url');
        $url->setLabel('Url ');
        $url->setAttribute('placeholder', 'Url');
        $url->setAttribute('class', 'input-large');
        
        $this->add($url);
        $this->add($ref);
        $this->add($name);
        $this->add($file);
    }

    public function createInputFilter()
    {
        $inputFilter = new InputFilter\InputFilter();
        
        // File Input
        $file = new InputFilter\FileInput('file');
        $file->setRequired(false);
        $file->getFilterChain()->attachByName('filerenameupload', array(
            'target' => './public/files/',
            'overwrite' => false,
            'use_upload_name' => false,
            'randomize' => true
        ));
        $inputFilter->add($file);
        
        return $inputFilter;
    }
}
