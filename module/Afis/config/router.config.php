<?php

namespace Afis;

use Afis\Controller\AfisController;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;
use Zend\Mvc\Router\Console\Simple;

return [
    'afis' => [
        'type' => Segment::class,
        'options' => [
            'route'    => '/afis[/:action]',
            'defaults' => [
                'controller' => AfisController::class,
                'action'     => 'index',
            ],
        ],
        'may_terminate' => true,
    ],
];