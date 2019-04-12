<?php

namespace applications\index\params;
use cocophp\std\params;
use cocophp\std\error;
use cocophp\std\output;
use cocophp\core\match;
/**
 *
 */
class userParamsHelper{
    static public function login( &$res ){
        $res = params::get();
        $paramsMatch = new match();
        self::userName( $paramsMatch );
        self::passWord( $paramsMatch );
        self::limit   ( $paramsMatch );
        return self::proving( $paramsMatch, $res );
    }
    static public function list( &$res ){
        $res = params::get();
        $paramsMatch = new match();
        self::limit( $paramsMatch );
        return self::proving( $paramsMatch, $res );
    }
    static private function limit( $paramsMatch ){
        $paramsMatch->match( 'page_on' );
        $paramsMatch->rule( 'int' );
        $paramsMatch->info( '当前页码必须是数字' );
        $paramsMatch->sever( 'limit' );
        $paramsMatch->default( 1 );

        $paramsMatch->match( 'page_size' );
        $paramsMatch->rule( 'int', 'length<3' );
        $paramsMatch->info( '每页展示最高不能超过99个' );
        $paramsMatch->sever( 'limit' );
        $paramsMatch->default( 20 );
    }
    static private function userName( $paramsMatch ){
        $paramsMatch->match( 'user->user_name' );
        $paramsMatch->rule( 'length>5', 'length<21', 'char' );
        $paramsMatch->info( '用户名必须提供：长度为6--20的字母或数字' );
        $paramsMatch->required();
        $paramsMatch->sever( 'where' );
    }
    static private function passWord( $paramsMatch ){
        $paramsMatch->match( 'pswd->password' );
        $paramsMatch->rule( 'length>5', 'length<21', 'char' );
        $paramsMatch->info( '密码必须提供：长度为6--20的字母或数字' );
        $paramsMatch->required();
        $paramsMatch->sever( 'data' );
    }

    static private function proving( $paramsMatch, &$res ){
        if( $paramsMatch->proving( $res ) ){
            return true;
        }
        $res = ( new output )->error( error::buildError( $res ) );
        return false;
    }
}
