<?php
use Doctrine\Migrations\Tools\Console\Command;
use Doctrine\Migrations\DependencyFactory;

use Roave\PsrContainerDoctrine\Migrations\CommandFactory;
use Roave\PsrContainerDoctrine\Migrations\DependencyFactoryFactory;

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
        'migrations_configuration' => array( // /!\ do not change these settings /!\
            'orm_default' => array(
                'table_storage' => array(
                    'table_name' => 'migrations',
                ),
                'migrations_paths' => array('DoctrineORMModule\Migrations' => 'data/DoctrineORMModule/Migrations')
            )
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
        ),
        'dependencies' => [
            'factories' => [
                Command\CurrentCommand::class => CommandFactory::class,
                Command\DiffCommand::class => CommandFactory::class,
                Command\DumpSchemaCommand::class => CommandFactory::class,
                Command\ExecuteCommand::class => CommandFactory::class,
                Command\GenerateCommand::class => CommandFactory::class,
                Command\LatestCommand::class => CommandFactory::class,
                Command\ListCommand::class => CommandFactory::class,
                Command\MigrateCommand::class => CommandFactory::class,
                Command\RollupCommand::class => CommandFactory::class,
                Command\SyncMetadataCommand::class => CommandFactory::class,
                Command\StatusCommand::class => CommandFactory::class,
                Command\UpToDateCommand::class => CommandFactory::class,
                Command\VersionCommand::class => CommandFactory::class,

                DependencyFactory::class => DependencyFactoryFactory::class,
                ]
            ]
    )
);
