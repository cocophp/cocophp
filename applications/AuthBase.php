<?php
namespace applications;

/**
 *
 */
class AuthBase{
    function __construct(){
    }
    /**
     * 防止重放攻击。
     * 原理还是很简单的。
     * 请求参数序列化，生成md5，若规定时间内请求过，则不在请求。
     */
    private function oneRequest( $token ){
        $redis = Cache::Redis();
        $requestNum = 1;
        if( $redis ){
            $requestNum = $redis->incr( $token );
            $redis->expire( $token, 5*60 );
        }
        if( $requestNum > 1 ){
            exit;
        }
    }
}
