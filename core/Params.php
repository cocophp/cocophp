<?php
namespace core;

/**
 * 参数接收类
 */
class Params {
    /**
     * @get 获取GET参数
     * @param  $params string 所需参数
     * @return string
     * @author Jan
     * @data 2019-01-09
     */
    static public function get( $key = '*' ){
        $key = str_replace( ' ', '', $key );
        $key = explode( '.', $key );
        $res = $_GET;
        foreach ($key as $value) {
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
     * @post 获取POST参数
     * @param  $str   string 获取的字段名称
     * @param  $level string 过滤级别
     * @return string
     * @author Jan
     * @data 2019-01-09
     */
    static public function post( $key = '*' ){
        $key = str_replace( ' ', '', $key );
        $key = explode( '.', $key );
        $res = $_POST;
        foreach ($key as $value) {
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
     * @set 塞入GET参数
     * @param  $str   string 获取的字段名称
     * @param  $level string 过滤级别
     * @return string
     * @author Jan
     * @data 2019-01-09
     */
    static public function set( $key, $value ){
        $_GET[ $key ] = $value;
    }
    static public function server( $key = '*' ){
        $key = str_replace( ' ', '', $key );
        $key = explode( '.', $key );
        $res = $_SERVER;
        foreach ($key as $value) {
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
}
