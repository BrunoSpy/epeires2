<?php

namespace Application;
use Application\Controller\Plugin\FlightPlansSGBD;
use Application\Controller\Plugin\SGBDPlugin;
use Application\Controller\Plugin\FlashMessage;

return [
    'invokables' => [
        'sgbd'		=> SGBDPlugin::class,
        'fpSGBD'  	=> FlightPlansSGBD::class,
        'msg'  		=> FlashMessage::class,

    ],
];