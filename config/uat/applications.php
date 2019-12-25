<?php

return [
    'applications' => [
        'edm'      => [
            'upload' => [
                'csvPath'  => 'edm/csv/'   . date( 'Y-m-d', time() ) . '/',
                'imgPath'  => 'edm/img/'   . date( 'Y-m-d', time() ) . '/',
                'morePath' => 'edm/other/' . date( 'Y-m-d', time() ) . '/',
            ],
        ],
    ],
];
