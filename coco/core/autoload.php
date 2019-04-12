<?php
/**
 * autoload
 */
class autoload {
    static private $nameMap = array(
        'applications' => applications,
        'cocophp'      => cocophp,
    );
    static public function palth( $class ){
        $vendor = explode('\\',$class);
        if( isset( self::$nameMap[ $vendor[ 0 ] ] ) ){
            $vendor[ 0 ] = self::$nameMap[ $vendor[ 0 ] ];
        }
        $file = implode( '/', $vendor );
        $file .= '.php';
        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
    static public function loading(){
        spl_autoload_register( 'autoload::palth' );
    }
}
