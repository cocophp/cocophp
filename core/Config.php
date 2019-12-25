<?php
namespace core;
/**
 *
 */
class Config{
    static public $conf = array();
    /**
     * 设置配置文件值，若不存在则会自动创建
     * @var [type]
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
     * 获取配置文件值，若改值不存在则返回default
     * @var [type]
     */
    static public function get( $key = '*', $default = false ){
        $key = str_replace( ' ', '', $key );
        $key = explode( '.', $key );
        $res = self::$conf;
        foreach ( $key as $value ) {
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
                return $default;
            }
        }
        return $res;
    }
    static public function init( ...$args ){
        foreach ( $args as $cnf ) {
            if( $cnf !== false ){
                self::$conf = array_merge_recursive( $cnf, self::$conf );
            }
        }
    }
}
