<?php

namespace Application;
use Application\Controller\Plugin\SGBDPlugin;
use Application\Controller\Plugin\FlashMessagePlugin;

return [
    'invokables' => [
        'sgbd'		=> SGBDPlugin::class,
        'msg'  		=> FlashMessagePlugin::class,

    ],
];