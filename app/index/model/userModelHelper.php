<?php

namespace applications\index\model;
use cocophp\std\params;
use cocophp\std\error;
use cocophp\std\output;
use cocophp\core\match;
use cocophp\db\factory;
/**
 *
 */
class userModelHelper{
    static public function getUserInfo( &$params ){
        $user = factory::db( 'user' )->where( $params['where'] )->part( '*' )->limit( $params['limit'] )->select();
        if( empty( $user ) ){
            $params = ( new output() )->error( error::buildError( ['empty', '用户名不正确，请核对后重试！']));
            return false;
        }
        if( $user[ 'passowrd' ] != $params[ 'data' ][ 'password' ] ){
            $params = ( new output() )->error( error::buildError( ['empty', '密码不正确，请核对后重试！']));
            return false;
        }
        $params = ( new output() )->data( ...$user )->limit( $params['limit'] );
        return true;
    }
    static public function getUserList( &$res ){
        $p = $res;
        $user = factory::db( 'user' )->part( '*' )->limit( $p['limit'] )->where('id','aaa');
        $res  = new output();
        $res->limit( $p['limit'] );
        $res->total( $user->count() );
        $res->data( ...$user->select() );
        $res->debug( ...$user->getMoreSql() );
        return true;
    }
}
