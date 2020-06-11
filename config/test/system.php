<?php

return [
    'system' =>[
        'service' => '',
        'request' => [
            'modules' => 'page',
            'contros' => 'controllers\indexController',
            'action'  => 'indexAction',
        ],
        'default' => [
            'applicationPath' => '../applications',
            'consolePath'     => './',
        ],
        'logPath' => '/home/mosh/logs/EDM/',
        // 网站主域名。
        'domain'  => 'http://localhost/',
        // 资源域名，若有cdn可配置此项
        'sourceDomain' => ''
    ],
];
