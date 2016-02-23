<?php

namespace Afis\Form;

use Zend\Form\Form;
use Zend\Form\Annotation\AnnotationBuilder;
use Application\Entity\Organisation;

class AfisForm extends Form 
{
    public static $instance = NULL;
    const DEFAULT_METHOD = 'post';

    
    public function __construct($name = null, $options = array()) {
        parent::__construct($name, $options);
        $this->setAttributes([
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
                        ]);
    }
    
    
    public static function newInstance($entity, $em)
    {
        if(is_null(self::$instance))
        {
            $organisations = $em->getRepository(Organisation::class);
            
            self::$instance = (new AnnotationBuilder())->createForm($entity);
            
            self::$instance
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
        return self::$instance;
    }
    
}