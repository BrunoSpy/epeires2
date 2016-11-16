<?php

namespace Afis;

use Afis\Form\AfisForm;
use Doctrine\ORM\EntityManager;

return [
    'invokables' => [
        'aform' => AfisForm::class,
    ],
    'factories' => [
        'afisForm' => function($entity, $sm) {
            $form = AfisForm::newInstance($entity, $sm->get(EntityManager::class));
            return $form;
        }
    ],
];