<?php
namespace core;

use core\config;

/**
 *
 */
class Cache{
    static private $__redis = NULL;
    private function __construct(){
        // 私有化构造函数，禁止外部实例化
    }
    static public function Redis(){
        if( self::$__redis == NULL ){
            self::$__redis = new \Redis();
            $config =  config::get( 'system.Cache' );
            self::$__redis->connect( $config['host'], $config['port'] );
            if( $config['auth'] and !self::$__redis->auth( $config['auth'] ) ){
                throw new \Exception("redis auth failed", 1);
            }
            if( $config['db'] !== "" ){
                self::$__redis->select( $config['db'] );
            }
        }
        return self::$__redis;
    }
}
