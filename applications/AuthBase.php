<?php
namespace applications;

use core\{ Config, Cache, Resule };
use applications\api\models\rbac;
/**
 *
 */
class AuthBase extends Resule {

    /**
     * 大概处理这么几件事情：
     * 1 . 用户身份验证。权限验证。
     * 2 . 参数序列化。前端参数都是放在了body里(json)
     *     所以，这里会统一整理数据到 $this->params 中。
     * 3 . 防重放攻击。
     * 4 . 采用rsa的形式加密token. 问题是前端如何保证token的安全性?
     */
    function __construct( $type = "auth" ){
        parent::__construct();
        try {
            // 1. 身份及权限验证。采用token的形式验证用户信息。
            if( empty( $_SERVER[ 'HTTP_X_AUTH_TOKEN' ] ) ){
                // 尝试从接口获取
                $this->userInfo = userService::loginOnApi();
            } else {
                $this->userInfo = json_decode( Cache::Redis()->get( $_SERVER[ 'HTTP_X_AUTH_TOKEN' ] ) );
            }
            $this->oneRequest( $token );
            if( empty( $this->userInfo ) ){
                throw new \Exception('登录已过期，请重新登录', 1);
            }
            // 鉴定用户权限。 若传入其他type，则不校验接口权限。
            // rbac的验证方式。
            if( $type == "auth" ){
                userService::Authority( $this->userInfo, $url );
                // $this->auths = rbac::getAuthIDsByUser( $this->userInfo->user_id );
                // $authIDs = array_column( $this->auths, 'auth_id' );
                // $urlAuth = rbac::getCurrentAuthIDByUrl();
                // if( !in_array( $urlAuth, $authIDs ) ){
                //     // return $this->error( '您暂无此功能权限');
                // }
            }
        } catch (\Exception $e) {
            return $this->failed( $e->getMessage() );
        }

        // 2. 这里并没有处理body中的数据，是由于上传文件也是通过body过来的。
        // 3. 暂时不做重放攻击
        // $this->oneRequest( $token ); // 此token为参数序列化后的hash值
        // 4. 略。
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
