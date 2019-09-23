<?php
namespace core\lib;

use core\Config;
/**
 *
 */
class Logger{
    static public function push( $info, $category = 'errors', $logName = '' ){
        if( empty( $category ) ){
            $category = 'errors';
        }
        if( empty( $logName ) ){
            $logName = date( 'Y-m-d', time() );
        }
        $path = Config::get( 'system.logPath' ) . "$category";
        if( !is_string( $info ) ){
            $info = print_r( $info, true );
        }
        @mkdir( $path, 0777, true );
        @error_log( $info . "\n", 3, "$path/$logName" );
    }
}
