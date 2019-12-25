<?php
namespace applications\template\params;

use core\Match;
use tableField\edmTask;
use tableField\edmTemplate;
use tableField\publicField;
use tableField\edmAdminService;
/**
 *
 */
class paramsBuilder{
    static public function show( &$res, $maxSize = 100 ){
        $match = publicField::mainID( new match() );
        publicField::limit( $match, $maxSize );
        return $match->proving( $res );
    }
    static public function detail( &$res ){
        $match = publicField::mainID( new match() );
        edmTemplate::tplID( $match );
        $match->rule( 'values > 0' )->required();
        return $match->proving( $res );
    }
    static public function edit( &$res ){
        $match = publicField::mainID( new match() );
        publicField::modifyTime( $match );
        publicField::modifyMainID( $match );
        edmTemplate::tplID( $match );
        edmTemplate::tplTitle( $match );
        edmTemplate::tplContent( $match );
        return $match->proving( $res );
    }
}
