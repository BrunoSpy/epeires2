<?php

namespace FlightPlan\Form;

use Zend\Form\Annotation\AnnotationBuilder;
use FlightPlan\Entity\FlightPlan;

class FlightPlanForm
{
    const DEFAULT_METHOD = 'post';

    protected $form;

    public function __construct($em)
    {
        $this->form = (new AnnotationBuilder())->createForm(FlightPlan::class);
        $this->form
            ->setAttributes([
                'method'    => self::DEFAULT_METHOD,
                'action'    => 'flightplans/save',
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

    public function showErrors(){
        $str = '';
        foreach ($this->form->getMessages() as $field => $messages)
        foreach ($messages as $typeErr => $message)
        $str.= " | ".$field.' : ['.$typeErr.'] '.$message;
        return $str;
    }
}