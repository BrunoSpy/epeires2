<?php

namespace Bookcase\Form;

use Zend\Form\Form;
use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterAwareTrait;

class BookForm extends Form implements InputFilterAwareInterface
{
    use InputFilterAwareTrait;
    
    protected $name = 'import-book';
    
    public function __construct()
    {
        parent::__construct();
        
        $this
            ->setName($this->name)
            ->setAttribute('method', 'post')
            ->add([
                'name' => 'title',
                'type' => 'text',
                'options' => [
                    'label' => 'Titre'
                ]
            ])
            ->add([
                'name' => 'datepub',
                'type' => 'text',
                'options' => [
                    'label' => 'Date de publication'
                ]
            ])
            ->add([
                'name' => 'publisher',
                'type' => 'text',
                'options' => [
                    'label' => 'Éditeur'
                ]
            ])
            ->add([
                'name' => 'notes',
                'type' => 'textarea',
                'options' => [
                    'label' => 'Notes'
                ]
            ])
            ->add([
                'name' => 'token',
                'type' => 'csrf'
            ])
            ->add([
                'name' => 'import',
                'type' => 'submit',
                'attributes' => [
                    'value' => 'Importer'
                ]
            ]);
    }
}
