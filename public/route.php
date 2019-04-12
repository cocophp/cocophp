<?php

return [
    '/'   => "index/index/index",
    'doc' => 'index/document/index/index',
    'doc/([a-zA-Z]+)' => 'index/document/index/$1',
    'doc/([a-zA-Z]+)/(\d+)' => 'index/document/index/$1?id=$2',
];
