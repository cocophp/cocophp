<?php

spl_autoload_register( function( $class ){
    $file = __DIR__ . '/../' . str_replace( '\\' , '/', $class ) . '.php';
    if ( file_exists( $file ) ) {
        require_once $file;
    }
} );

use core\Config;
use core\Route;
use core\Cores;
use core\Console;
$env = 'test';

Config::init(
    require __DIR__ . "/../config/$env/system.php",
    require __DIR__ . "/../config/$env/applications.php",
    require __DIR__ . "/../config/$env/rsa.php",
    require __DIR__ . "/../config/$env/database.php"
);

Route::analysis( $_SERVER['REQUEST_URI'] );
echo Cores::execute();
