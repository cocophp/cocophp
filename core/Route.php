<?php
namespace core;

use core\Config;
/**
 *
 */
class Route{
    static public function analysis( $url ){
        // 此处写的真心不好.
        $url = explode( '?', $url )[0];
        $service = '/'.Config::get( "system.service") . '/'; // , 'controller';
        if( $url == $service ){
            return;
        }
        $url = str_replace( $service, '', $url );
        $url = explode( '/', $url );
        if( !empty( $url[ count($url)-1 ] ) ){
            Config::set( 'system.request.action',  $url[ count($url)-1 ] . 'Action' );
            unset( $url[ count($url)-1 ] );
        }
        if( !empty( $url[ count($url)-1 ] ) ){
            Config::set( 'system.request.contros', 'controllers\\' . $url[ count($url)-1 ] . 'Controller' );
            unset( $url[ count($url)-1 ] );
        }
        if( empty( $url ) ){
            $url[] = Config::get( 'system.request.modules' );
        }
        if( Config::get( 'system.request.modules' ) != 'console' ){
            Config::set( 'system.request.modules', Config::get('system.defualt.applicationPath') . '\\' . implode( '\\', $url ) );
        }
    }
}
