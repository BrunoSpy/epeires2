<?php

namespace Application;
use Application\Controller\Plugin\SarBeaconsSGBD;
use Application\Controller\Plugin\SarBeaconsMessages;
return [
    'invokables' => [
        'sarBeaconsMessages'  => SarBeaconsMessages::class,
        'sarBeaconsSGBD'      => SarBeaconsSGBD::class
    ],
];