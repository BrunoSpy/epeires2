<?php

namespace Afis;

use Doctrine\ORM\EntityManager;
use DateTime;

return [
    'invokables' => [
        'em' => function ($sm) 
                {
                    return $sm->get(EntityManager::class);
                },
        'today' => DateTime::class
    ],
];