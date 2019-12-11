<?php

// error_reporting(E_ALL);
// 最简单的路由转发.让service更轻量.
spl_autoload_register( function( $class ){
    $file = __DIR__ .'/' . str_replace( '\\' , '/', $class ) . '.php';
    if ( !file_exists( $file ) ) {
    	throw new \Exception( "class $class not found", 1 );
    }
    require_once $file;
    // var_dump( get_included_files() );
} );

use core\Config;
use core\Route;
use core\Cores;
$env = 'uat';

Config::init(
    require __DIR__ . "/config/$env/applications.php",
    require __DIR__ . "/config/$env/system.php",
    require __DIR__ . "/config/$env/rsa.php",
    require __DIR__ . "/config/$env/database.php"
);
Route::analysis( $_SERVER['REQUEST_URI'] );
echo Cores::execute();
