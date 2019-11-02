<?php
namespace core;

use core\Config;
/**
 *
 */
class Cores{
    static public function execute(){
        try {
            $request = Config::get( 'system.request' );
            $contro  = "{$request['modules']}\\{$request['contros']}";
            $action  = Config::get( 'system.request.action' );
    	    $contro  = new $contro();
    	    // php5的情况下，拿不到方法也抛不出异常。只能手动判断
            if( !method_exists( $contro, $action ) ){
                exit( "function $action not found" );
            }
            $res     = $contro->$action();
            if( is_string( $res ) or is_numeric( $res ) ){
                return $res;
            }
            if( is_array( $res ) ){
                return json_encode( $res );
            }
        }catch (\Exception $e) {
            var_dump( $e->getMessage() );
        }
        catch( \Throwable $e ){
            var_dump( $e->getMessage() );
        }
        catch( \Error $e ){
            var_dump( $e->getMessage() );
        }
    }
}
