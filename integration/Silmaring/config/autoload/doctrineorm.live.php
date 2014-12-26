<?php
return array(
    'doctrine' => array(
        'connection' => array(
            // default connection name
            'orm_default' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params' => array(
                    'host' => 'd41367.mysql.zone.ee',
                    'port' => '3306',
                    'user' => 'd41367sa75451',
                    'password' => 'Artmedia11',
                    'dbname' => 'd41367sd75525',
                    'charset' => 'utf8', // extra
                    'driverOptions' => array(
                        1002=>'SET NAMES utf8'
                    )
                )
            )
        ),
        'configuration' => array(
            'orm_default' => array(
                'metadata_cache' => 'filesystem',
                'query_cache'    => 'filesystem',
                'result_cache'   => 'filesystem',
            ),
        ),
    ),
);