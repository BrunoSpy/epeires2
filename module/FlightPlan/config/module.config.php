<?php

namespace FlightPlan;

use Zend\Mvc\Router\Console\Simple;
use FlightPlan\Controller\FlightPlanController;

return [
    'router' => array(
        'routes' => array(
            'fp' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/flightplans[/:action]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                    ),
                    'defaults' => array(
                        'controller' => FlightPlanController::class,
                        'action' => 'index'
                    )
                )
            )
        )
    ),
    
    /**
     * Doctrine 2 Configuration
     */
    'doctrine' => [
        'driver' => [
            __NAMESPACE__ . '_driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [(__DIR__ . '/../src/Entity')]
            ],
            'orm_default' => [
                'drivers' => [
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                ]
            ]
        ]
    ],
    'view_manager' => [
        'template_map' => [
            'fp/layout'     => __DIR__ . '/../../../public/layout/default.phtml',
            'fp/index'      => __DIR__ . '/../view/flight-plan/index.phtml',
            'fp/form'       => __DIR__ . '/../view/flight-plan/form.phtml',
            'fp/helper/fp'  => __DIR__ . '/../view/flight-plan/helpers/flight-plan.phtml',
        ]
    ],
    'asset_manager' => [
        'resolver_configs' => [
            'map' => [
                'flightplan.js'                 => __DIR__ . '/../public/js/flightplan.js',
                'jquery.timepicker.js'          => __DIR__ . '/../public/js/jquery.timepicker.js',
                'stupidtable.js'                => __DIR__ . '/../../../public/components/jquery-stupid-table/stupidtable.min.js',
                'bootstrap-datetimepicker.js'   => __DIR__ . '/../../../public/components/bootstrap-material-datetimepicker/js/bootstrap-material-datetimepicker.js',
                'momentlocale.js'               => __DIR__ . '/../../../public/components/momentjs/locale/fr.js',
                'moment.js'                     => __DIR__ . '/../../../public/components/momentjs/min/moment.min.js'
            ]
        ]
    ],
];