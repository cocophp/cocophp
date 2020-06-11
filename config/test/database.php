<?php

return [
    'system' =>[
        'Db' => [
            'host' => 'localhost',
            'port' => '3306',
            'user' => 'root',
            'pswd' => 'root',
            'database' => 'test',
            'charset' => 'utf8',
        ],
        'Redis'=>[
            'host' => 'localhost',
            'port' => '6379',
            'auth' => '',
            'type' => 'redis',
            'db'   => '1',
        ],
    ]
];
