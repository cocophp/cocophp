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
            if( is_array( $res ) or is_object( $res ) ){
                return json_encode( $res );
            }
        }catch (\Exception $e) {
            self::viewError( $e, 'Exception' );
        }
        catch( \Throwable $e ){
            self::viewError( $e, 'Throwable' );
        }
        catch( \Error $e ){
            self::viewError( $e, 'Error' );
        }
    }
    static private function viewError( $e, $type ){
        if( Config::get( 'system.mode' ) == 'console' ){
            echo "{$type} : {$e->getMessage()}\n";
            foreach ($e->getTrace() as $key => $value) {
                echo "In file {$value['file']} line {$value['line']} : ";
                echo "{$value['class']} {$value['type']} {$value['function']}()\n";
            }
        } else {
            if( Config::get( "system.env") == "prod" ){
                echo "System internal error";
                exit;
            }
            echo "<h2>{$type} : {$e->getMessage()}</h2>\n";
            echo "<p> In file {$e->getFile()} line {$e->getLine()} : ";
            foreach ($e->getTrace() as $key => $value) {
                echo "<p> In file {$value['file']} line {$value['line']} : ";
                echo "{$value['class']} {$value['type']} {$value['function']}()</p>\n";
            }
            echo "<style>.a:{text-indent:20px;}</style>";
        }
    }
}
