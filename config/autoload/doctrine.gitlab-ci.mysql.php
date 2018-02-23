<?php
return array(
	'doctrine' => array(
		'connection' => array(
			'orm_default' => array(
				'driverClass' =>'Doctrine\DBAL\Driver\PDOMySql\Driver',
				'params' => array(
					'host'     => 'mysql',
					'port'     => '3306',
					'user'     => 'root',
					'password' => 'epeires2',
					'dbname'   => 'epeires2',
					'charset' => 'utf8',
                    //BUG fix that will be useless with doctrine/orm >= 2.6 (requires PHP >= 7.1)
                    'driverOptions' => array(
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))"
                    ),
                )
            )
        ),
    )
);
