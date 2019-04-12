<?php

namespace cocophp\db;

use cocophp\core\config;
use cocophp\db\DB;
/**
 * @factory; db factory;
 */
class factory{
    static private $data;

    static public function db( $table = '' ) {
        return new DB( $table );
    }
    static public function redis(){
        if( self::$data[ 'redis' ] === false ){
            return false;
        }
        if( !is_object( self::$data[ 'redis' ] ) ){
            try {
                self::$data[ 'redis' ] = new \Redis();
                self::$data[ 'redis' ]->connect( config::get( 'redis.ip' ),config::get( 'redis.port' ) );
                $password = config::get( 'redis.pswd' );
                if( $password and !empty( $password ) ){
                    if( !self::$data[ 'redis' ]->auth( $password ) ){
                        return false;
                    }
                }
            } catch (\Exception $e) {
                self::$data[ 'redis' ] = false;
                return false;
            }
        }
        return self::$data[ 'redis' ];
    }
}
