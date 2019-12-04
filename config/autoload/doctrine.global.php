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
            'default_group' => [
                'object_manager' => 'doctrine.entitymanager.orm_default',
                'invokables' => [
                    'Application\Fixtures\CategoryFixture' => 'Application\Fixtures\CategoryFixture',
                    'Application\Fixtures\CustomFieldFixture' => 'Application\Fixtures\CustomFieldFixture',
                    'Application\Fixtures\EventFixture' => 'Application\Fixtures\EventFixture',
                    'Application\Fixtures\OrganisationFixture' => 'Application\Fixtures\OrganisationFixture',
                    'Application\Fixtures\RecurrenceFixture' => 'Application\Fixtures\RecurrenceFixture',
                ]
            ]
        ),
        'configuration' => array(
            'orm_default' => array(
                'string_functions' => array(
                    'match' => 'DoctrineExtensions\Query\Mysql\MatchAgainst'
                )
            )
        )
    )
);
