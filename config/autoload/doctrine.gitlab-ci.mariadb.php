<?php
return array(
	'doctrine' => array(
		'connection' => array(
			'orm_default' => array(
				'driverClass' =>'Doctrine\DBAL\Driver\PDOMySql\Driver',
				'params' => array(
					'host'     => 'mariadb',
					'port'     => '3306',
					'user'     => 'root',
					'password' => 'epeires2',
					'dbname'   => 'epeires2',
					'charset' => 'utf8'
                                )
                        )
                ),
            )
    );
