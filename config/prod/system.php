<?php

return [
    'system' =>[
        'service' => '',
        'request' => [
            'modules' => 'index',
            'contros' => 'controllers\indexController',
            'action'  => 'indexAction',
        ],
        'defualt' => [
            'applicationPath' => '\applications',
        ],
        'logPath' => '/home/mosh/logs/EDM/',
    ],
];
