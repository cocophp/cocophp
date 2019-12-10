<?php

return [
    'system' =>[
        'Db' => [
            'host' => '192.168.8.9',
            'port' => '3306',
            'user' => '',
            'pswd' => '',
            'database' => '',
            'charset' => 'utf8',
        ],
        'Cache'=>[
            'host'   => '192.168.8.3',
            'port' => '6379',
            'auth' => 'X01wRN6eZigiNX5_',
            'type' => 'redis',
            'db'   => '1',
        ],
    ]
];
