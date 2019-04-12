<?php

namespace applications\index\service;

use applications\index\params\userParamsHelper;
use applications\index\model\userModelHelper;
use cocophp\core\match;
/**
 *
 */
class userServerHelper{
    static public function login( &$res ){
        if( !userParamsHelper::login( $res ) ){
            return false;
        }
        if( !userModelHelper::getUserInfo( $res ) ){
            return false;
        }
        return true;
    }
    static public function list( &$res ){
        if( !userParamsHelper::list( $res ) ){
            return false;
        }
        if( !userModelHelper::getUserlist( $res ) ){
            return false;
        }
        return true;
    }
}
