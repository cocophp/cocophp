<?php
namespace applications;

use core\Cache;
use core\Config;
use core\lib\Rsa;
/**
 * 基础的权限验证类。
 * 参数序列化，得到MD5值。将该值通过rsa加密，防止修改。
 * 同时，用户敏感信息也会放到这里。
 */
class AuthBase{
    function __construct(){
        // 拿到token。
        if( empty( $_SERVER['HTTP_TOKEN'] ) ){
            echo json_encode( [
                'code' => 1007,
                'msg'  => '请提供有效的token',
            ] ); exit;
        }
        $userInfo = $_SERVER['HTTP_TOKEN'];

        // 防止重放攻击。
        $this->oneRequest( $userInfo );

        Rsa::decrypt( $userInfo );
        // $cacheToken = Cache::Redis()->get( 'access_token' );
        // 暂时写死。sdk的redis缓存，地方太多了。
        $cacheToken = "KgTSdnhYnsLo2FTnfcLuCZKRpLDqfeJ1_随机数_Lo2FTnfcLuCZKRpLDqf";
        $userInfo = explode( '_', $userInfo );
        // 判断token一致性。
        if( md5( $cacheToken ) != $userInfo[3] ){
            echo json_encode( [
                'code' => 1008,
                'msg'  => '请提供有效的token',
                'debug'=>[
                    $cacheToken,
                    $userInfo,
                ]
            ] ); exit;
        }
        // 将参数序列化，判断传输中是否修改过。
        $params = $_GET + $_POST;
        ksort( $params );
        $serialize = md5( serialize( $params ) );
        // 判断token一致性。
        if( $serialize != $userInfo[2] ){
            echo json_encode( [
                'code' => 1009,
                'msg'  => '请提供有效的token',
            ] ); exit;
        }
        // 将main_id 和modify_main_id暴力存储到$_GET 和 $_POST中
        if( !empty( $userInfo[0] ) ){
            $_GET[ 'main_id' ] = $_POST[ 'main_id' ] = $userInfo[0];
        }
        if( !empty( $userInfo[1] ) ){
            $_GET[ 'modify_main_id' ] = $_POST[ 'modify_main_id' ] = $userInfo[1];
        }
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
