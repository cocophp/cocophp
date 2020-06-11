<?php

use core\Config;
/**
 *
 */
class Autoload{
    /**
     * 定制路由。此处判断只包含 controller(Controller) 的类才会走这里。
     * 可以减少不必要的 file_exists 判断。
     */
    static function customizedRoute( $class ){
        if( strpos( $class, 'ontroller' ) === false ){
            return;
        }
        if( strpos( $class, 'applications' ) !== 0 ){
            return;
        }
        $mainID     = 0;
        if( !empty( $_GET['main_id'] ) ){
            $mainID = $_GET['main_id'];
        }
        $customized = Config::get( "customized.main_id.$mainID", '' );

        $class = str_replace( "applications", $customized, $class );
        $file  =  str_replace( '\\' , '/', $class ) . '.php';
        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
    /**
     * applications的自动加载
     */
    static function appRoute( $class ){
        $appPath = Config::get( 'system.default.applicationPath', '' );
        if( strpos( $class, 'applications' ) !== 0 ){
            return;
        }
        $class = str_replace( "applications", $appPath, $class );
        $file  =  str_replace( '\\' , '/', $class ) . '.php';
        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
    /**
     * console 的自动加载。
     */
    static function consoleRoute( $class ){
        $appPath = Config::get( 'system.default.consolePath', '' );
        if( strpos( $class, 'console' ) !== 0 ){
            return;
        }
        $class = str_replace( "console", $appPath, $class );
        $file  =  str_replace( '\\' , '/', $class ) . '.php';
        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
    /**
     * 核心文件的自动加载。
     */
    static function coreRoute( $class ){
        if( strpos( $class, 'core' ) !== 0 ){
            return;
        }
        $file = '../' . str_replace( '\\' , '/', $class ) . '.php';
        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
}
