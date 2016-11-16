<?php

namespace Application\Form;

use Zend\Form\Annotation\AnnotationBuilder;
use Application\Entity\Organisation;
use Application\Entity\Afis;
class AfisForm
{
    const DEFAULT_METHOD = 'post';

    protected $form;

    public function __construct($em)
    {
        $organisations = $em->getRepository(Organisation::class);

        $this->form = (new AnnotationBuilder())->createForm(Afis::class);
        $this->form
            ->setAttributes([
                'method'    => self::DEFAULT_METHOD,
                'action'    => 'afis/save',
                'class'     => 'form-horizontal'
            ])
            ->add([
                'name' => 'submit',
                'attributes' => [
                    'type' => 'submit',
                    'value' => 'Enregistrer',
                    'class' => 'btn btn-primary btn-small'
                ]
            ])
            ->get('organisation')
            ->setValueOptions($organisations->getAllAsArray())
        ;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function showErrors(){
        $str = '';
        foreach ($this->form->getMessages() as $field => $messages)
            foreach ($messages as $typeErr => $message)
                $str.= " | ".$field.' : ['.$typeErr.'] '.$message;
        return $str;
    }
}