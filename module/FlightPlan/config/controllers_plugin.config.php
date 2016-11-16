<?php

namespace Afis;

use Afis\Controller\Plugin\AfisMessages;
use FlightPlan\Controller\Plugin\FlightPlanSGBD;

return [
    'invokables' => [
        'fpMessages'  => AfisMessages::class,
        'fpSGBD'      => FlightPlanSGBD::class
    ],
];