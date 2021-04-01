<?php
return array(
    'log' => array(
        'EpeiresLogger' => array(
            'writers' => array(
                'stream' => [
                    'options' => [
                        'filters' => [
                            'priority' => [
                                'name' => 'priority',
                                'options' => [
                                    'operator' => '<=',
                                    'priority' => \Laminas\Log\Logger::ERR,
                                ],
                            ],
                        ],
                    ],
                ],
            ),
        )
    )
);