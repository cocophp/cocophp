<?php
namespace core;
/**
 *
 */
class Config{
    static public $conf = array();
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
    static public function init( $confs ){
        $confs = func_get_args();
        foreach ( $confs as $key => $cnf ) {
            self::$conf = array_merge_recursive( $cnf, self::$conf );
        }
        // var_dump( self::$conf );
    }
}
