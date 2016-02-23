<?php

namespace FlightPlan\Form;

use Zend\Form\Form;
use Zend\Form\Annotation\AnnotationBuilder;

class FlightPlanForm extends Form 
{
    public static $instance = NULL;
    const DEFAULT_METHOD = 'post';

    public static function newInstance($entity, $em)
    {
        if(is_null(self::$instance))
        {
            
            self::$instance = (new AnnotationBuilder())->createForm($entity);
            
            self::$instance
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
        return self::$instance;
    }
    
}