<?php

namespace Application;
use Application\Controller\Plugin\AfisSGBD;
use Application\Controller\Plugin\AfisMessages;
use Application\Controller\Plugin\SarBeaconsSGBD;

return [
    'invokables' => [
        'sbSGBD'		=> SarBeaconsSGBD::class,
        'afMessages'  	=> AfisMessages::class,
        'afSGBD'      	=> AfisSGBD::class
    ],
];