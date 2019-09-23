<?php

// error_reporting(E_ALL);
// 最简单的路由转发.让service更轻量.
spl_autoload_register( function( $class ){
    $file = __DIR__ .'/' . str_replace( '\\' , '/', $class ) . '.php';
    if ( file_exists( $file ) ) {
        require_once $file;
    }
    // var_dump( get_included_files() );
} );

use core\Config;
use core\Route;
use core\Cores;

Config::init(
    require __DIR__ . '/config/applications.php',
    require __DIR__ . '/config/system.php',
    require __DIR__ . '/config/rsa.php',
    require __DIR__ . '/config/database.php'
);
Route::analysis( $_SERVER['REQUEST_URI'] );
echo Cores::execute();
