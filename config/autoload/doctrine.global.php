<?php
return array(
    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params' => array(
                    'host' => '127.0.0.1',
                    'port' => '3306',
                    'user' => 'root',
                    'password' => '',
                    'dbname' => 'epeires2',
                    'charset' => 'utf8'
                )
            )
        ),
        'migrations' => array( // /!\ do not change these settings /!\
            'connection' => 'doctrine.connection.orm_default',
            'migrations_table' => 'migrations',
            'migrations_namespace' => 'DoctrineORMModule\Migrations',
            'migrations_directory' => 'data/DoctrineORMModule/Migrations'
        ),
        'fixture' => array(
            'Application_fixture' => __DIR__ . '/../../module/Application/src/Application/Fixtures'
        )
    )
);
