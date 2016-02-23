<?php

namespace FlightPlan;

use Zend\Loader\AutoloaderFactory;
use Zend\Loader\StandardAutoloader;

return [
    AutoloaderFactory::STANDARD_AUTOLOADER => [
        StandardAutoloader::LOAD_NS => [
            __NAMESPACE__ => __DIR__ . '/../src/' . __NAMESPACE__
        ]
    ]
];