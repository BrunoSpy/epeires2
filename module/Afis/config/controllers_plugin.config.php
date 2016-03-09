<?php

namespace Afis;
use Afis\Controller\Plugin\AfisSGBD;
use Afis\Controller\Plugin\AfisMessages;
return [
    'invokables' => [
        'afisMessages'  => AfisMessages::class,
        'afisSGBD'      => AfisSGBD::class
    ],
];