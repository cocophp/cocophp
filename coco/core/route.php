<?php

namespace cocophp\core;

use cocophp\std\params;
/**
 *  路由解析类
 */
class route
{
    /**
     * @modules; default module;
     */
    static public $module = array( 'index' );
    /**
     * @controller; default controller;
     */
    static public $contro = 'index';
    /**
     * @action; default action;
     */
    static public $action = 'index';
    /**
     * @nameContro; controller namespace;
     */
    static public $nameContro = 'applications\index\controller\index';
    /**
     * @pathContro; controller path;
     */
    // static public $pathContro = applications.'/index/controller/index';
    /**
     * @pathModule; module path;
     */
    static public $pathModule = applications.'/index/';
    /**
     * @loading; load route;
     */
    static public function loading( $path ){
        if( !file_exists( $path ) ){
            return true;
        }
        $request = params::get('route');
        // 并没有获取到任何路由信息
        if( $request === false ){
            $request = '/';
        }
        $request = strtolower( $request );
        // 恰好该路由为静态html资源
        if( file_exists( './' . $request . '.html' ) ){
            require_once( './' . $request .'.html' );
            exit;
        }
        // 试图在配置路由配置中拿到
        // 加在所有路由配置
        $temp    = require_once( $path );
        // 存在，这直接使用。
        if( isset( $temp[ $request ] ) ){
            return self::defaultRoute( self::explain( $temp[ $request ] ) );
        }
        foreach ($temp as $key => $value) {
            // 尝试正则匹配。
            // 预处理规则。
            $temp = array();
            $preg = self::preg( $key );
            if( preg_match( $preg, $request, $temp ) ){
                $value = self::explain( $value );
                unset( $temp[0] );
                foreach ($temp as $k => $v) {
                    $value = str_replace( '$'.$k, $v, $value );
                }
                return self::defaultRoute( $value );
            }
        }
        return self::defaultRoute( $request );
    }
    /**
     * @preg; str replace rotue;
     */
    static private function preg( $preg ){
        $preg = str_replace( '/', '\/', $preg );
        $preg = str_replace( ' ', '', $preg );
        $preg = strtolower( $preg );
        return '/^' . $preg . '$/';
    }
    /**
     * @explain;
     */
    static private function explain( $route ){
        if( is_array( $route ) ){
            $route = implode( '/', $route );
        }
        if( is_object( $route ) ){
            $route = $route();
        }
        return $route;
    }
    /**
     * @defaultRoute;
     */
    static private function defaultRoute( $route ){
        $param = array();
        $route = explode( '?', $route );
        if( isset( $route[ 1 ] ) ) {
            $param = explode( '&', $route[ 1 ] );
        }
        $_GET['route'] = $route[0];
        $route = explode( '/', $route[ 0 ] );
        switch ( count( $route) ) {
            case 0: break;
            case 1: self::$action = $route[ 0 ];
                    break;

            case 2: self::$action = $route[ 1 ];
                    self::$contro = $route[ 0 ];
                    break;

            default:self::$action = $route[ count($route)-1 ]; unset( $route[ count($route)-1 ] );
                    self::$contro = $route[ count($route)-1 ]; unset( $route[ count($route)-1 ] );
                    self::$module = $route;
                    break;
        }
        self::$nameContro = 'applications\\' . implode( '\\', self::$module ) . '\\controller\\' . self::$contro;
        // self::$pathContro = applications . implode( '/', self::$module ) . '/controller/' . self::$contro;
        self::$pathModule = applications . implode( '/', self::$module ) . '/';
        foreach ($param as $key => $value) {
            $temp = explode( '=', $value );
            $_GET[ $temp[ 0 ] ] = $temp[ 1 ];
        }
        return true;
    }
}
