<?php
namespace cocophp\core;

/**
 * 基础类
 * 用于管理所有配置信息
 */

class config
{
    /**
     * @conf; all configs;
     */
    static private $conf = array();
    /**
     * @set; change config's value;
     */
    static public function set( $key, $value ){
        $key = str_replace( ' ', '', $key );
        $key  = explode( '.', $key );
        $temp = &self::$conf;
        foreach ($key as $v ) {
            if( !isset( $temp[ $v ] ) ){
                $temp[ $v ] = array();
            } else {
                if( !is_array( $temp [ $v ] ) ){
                    $t = $temp[ $v ];
                    $temp[ $v ] = array();
                    $temp[ $v ][ $v ] = $t;
                }
            }
            $temp = &$temp[ $v ];
        }
        if( is_array( $value ) ){
            $temp = array_merge( $temp, $value);
        } else {
            $temp = $value;
        }
    }
    /**
     * @get; have config's value;
     */
    static public function get( $key = '*' ){
        $key = str_replace( ' ', '', $key );
        $key = explode( '.', $key );
        $res = self::$conf;
        foreach ($key as $key => $value) {
            if( isset( $res[ $value ] ) ){
                if( is_array( $res[ $value ] ) ){
                    $res = $res[ $value ];
                } else {
                    return $res[ $value ];
                }
            } else {
                if( $value == '*' ){
                    return $res;
                }
                return false;
            }
        }
        return $res;
    }
    /**
     * @loading; loading config in the path file;
     */
    static public function loading( $path ){
        if( file_exists( $path ) ){
            $conf = require_once( $path );
            foreach ( $conf as $key => $value) {
                self::set( $key, $value );
                // self::$conf[ $key ] = $value;
            }
        }
    }
}
