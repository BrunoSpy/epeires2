<?php

namespace Application\Form;

use Zend\Form\Annotation\AnnotationBuilder;
use Application\Entity\InterrogationPlan;

class SarBeaconsForm
{
    const DEFAULT_METHOD = 'post';

    protected $form;

    public function __construct($em)
    {

        $this->form = (new AnnotationBuilder())->createForm(InterrogationPlan::class);
        $this->form
            ->setAttributes([
                'method'    => self::DEFAULT_METHOD,
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
        ;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function printErrors(){
        $str = '';
        foreach ($this->form->getMessages() as $field => $messages)
            foreach ($messages as $typeErr => $message)
                $str.= " | ".$field.' : ['.$typeErr.'] '.$message;
        return $str;
    }
}