<?php
namespace applications\index\controller;

use cocophp\std\output;
use applications\index\service\userServerHelper;
/**
 *
 */
class user {
    function login(){
        if( !userServerHelper::login( $res ) ){
            return $res->toJson();
        }
        // if( !userServerHelper::login( $res ) ){
        //     return $res->toJson();
        // }
        return $res->toJson();
    }
    function list(){
        if( !userServerHelper::list( $res ) ){
            return $res->toJson();
        }
        return $res->toJson();
    }
}
?>
