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
        $service = '';
        if( Config::get( "system.service" ) ){
            $service .= Config::get( "system.service" );
        }
        $url = str_replace( $service, '', $url );
        $url = explode( '/', $url );
        foreach( $url as $k => $v ){
            if( empty( $v ) ){
                unset( $url[ $k ] );
            }
        }
        $url = array_values( $url );
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
        if( Config::get( 'system.mode' ) == 'console' ){
            Config::set( 'system.request.modules', '\console\\' . implode( '\\', $url ) );
        } else {
            Config::set( 'system.request.modules', '\applications\\' . implode( '\\', $url ) );
        }
    }
}
