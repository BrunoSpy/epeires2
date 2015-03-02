<?php

/**
 * Epeires 2
 * @license   https://www.gnu.org/licenses/agpl-3.0.html Affero Gnu Public License
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
class FileUpload extends Form {

    public function __construct($name = null, $options = array()) {
        parent::__construct($name, $options);
        $this->addElements();
        $this->setInputFilter($this->createInputFilter());
    }

    public function addElements() {
        $file = new Element\File('file');
        $file->setLabel("Import : ")
                ->setAttributes(array(
                    'id' => 'file',
                    'multiple' => false
        ));

        $name = new Element\Text('name');
        $name->setLabel('Fichier :');
        $name->setAttribute('placeholder', 'Titre');
        $name->setAttribute('class', 'input-medium');

        $ref = new Element\Text('reference');
        $ref->setLabel("Référence : ");
        $ref->setAttribute('placeholder', 'Ref. (facultatif)');
        $ref->setAttribute('class', 'input-medium');

        $url = new Element\Text('url');
        $url->setLabel('Url : ');
        $url->setAttribute('placeholder', 'Url');
        $url->setAttribute('class', 'input-large');

        $this->add($url);
        $this->add($ref);
        $this->add($name);
        $this->add($file);
    }

    public function createInputFilter() {
        $inputFilter = new InputFilter\InputFilter();

        // File Input
        $file = new InputFilter\FileInput('file');
        $file->setRequired(false);
        $file->getFilterChain()->attachByName(
                'filerenameupload', array(
            'target' => './public/files/',
            'overwrite' => false,
            'use_upload_name' => false,
            'randomize' => true
                )
        );
        $inputFilter->add($file);

        return $inputFilter;
    }

}
