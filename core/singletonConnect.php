<?php
namespace core;

use core\Config;
use PDO;
/**
 *
 */
class singletonConnect{
    static public $lastSql = "";
    static public $moreSql = [];

    static private $__conn = NULL;
    static private $__that = NULL;
    static private $__debug = true;
    static public function getInstance(){
        self::ping();

        if( self::$__that == NULL ){
            self::$__that = new singletonConnect();
        }
        return self::$__that;
    }
    private function __construct(){
    }
    public function __query( $sql ){
        self::$lastSql = $sql;
        if( self::$__debug ){
            self::$moreSql[] = $sql;
        } else {
            self::ping();
        }
        return self::$__conn->query( $sql );
    }
    public function __getConn(){
        return self::$__conn;
    }
    // TODO: 脚本内的延时写的有点问题额。。回头要优化
    static public function ping( $n = 0){
        if( $n >=3 ){
            return false;
        }
        $DbConf = Config::get( 'system.Db' );
        if( self::$__conn == NULL ){
            self::$__conn = new \mysqli(
                $DbConf[ 'host' ],
                // "{$DbConf[ 'host' ]}:{$DbConf[ 'port' ]}",
                $DbConf[ 'user' ],
                $DbConf[ 'pswd' ],
                $DbConf[ 'database' ]
            );
            self::$__debug  = Config::get( 'system.request.modules') != 'console';
        }
        if( !self::$__conn->query("SET NAMES {$DbConf[ 'charset' ]}") ){
             self::$__conn = NULL;
            self::ping( $n++ );
        }
    }
    static public function connectDb() {
        $DbConf = Config::get( 'system.Db' );
        self::$__conn = new \mysqli(
            $DbConf[ 'host' ],
            // "{$DbConf[ 'host' ]}:{$DbConf[ 'port' ]}",
            $DbConf[ 'user' ],
            $DbConf[ 'pswd' ],
            $DbConf[ 'database' ]
        );
        self::$__conn->query("SET NAMES {$DbConf[ 'charset' ]}");
    }
}
