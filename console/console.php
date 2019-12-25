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

Config::set( 'system.defualt.applicationPath', 'console' );
if( !isset($_SERVER['argv'][1]) ){
    exit( "必须指定一个路由： php console.php index/index/index k1=v1 k2=v2\n" );
}
Route::analysis( $_SERVER['argv'][1] );

foreach ( $_SERVER['argv'] as $value ) {
    $t = explode( '=', $value );
    if( isset( $t[1] ) ){
        $_GET[ $t[0] ] = $t[1];
    }
}
echo Cores::execute();
