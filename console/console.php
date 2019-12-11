<?php

// error_reporting(E_ALL);
// 最简单的路由转发.让service更轻量.
spl_autoload_register( function( $class ){
    $file = __DIR__ . '/../' . str_replace( '\\' , '/', $class ) . '.php';
    if ( file_exists( $file ) ) {
        require_once $file;
    }
    // var_dump( get_included_files() );
} );

use core\Config;
use core\Route;
use core\Cores;
use core\Console;
$env = 'uat';

Config::init(
    require __DIR__ . "/../config/$env/system.php",
    require __DIR__ . "/../config/$env/applications.php",
    require __DIR__ . "/../config/$env/rsa.php",
    require __DIR__ . "/../config/$env/database.php"
);
Config::set( 'system.request.modules', 'index' );
Config::set( 'system.defualt.applicationPath', 'console' );
if( !isset($_SERVER['argv'][1]) ){
    Console::show();
}
Route::analysis( $_SERVER['argv'][1] );
for ($i=count($_SERVER['argv'])-1; $i > 1; $i--) {
    $t = explode( '=', $_SERVER['argv'][$i] );
    if( isset( $t[1] ) ){
        $_GET[ $t[0] ] = $t[1];
    }
}
echo Cores::execute();
