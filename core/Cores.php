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
