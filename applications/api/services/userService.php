<?php
namespace applications\api\services;

use core\{ Cache, Config };
use applications\api\models\cocoSysUserModel;
/**
 * 用户模块统一服务。
 * 注意php的静态调用，请小心使用 $this，
 */
class userService{
    /**
     * 用户名 密码登录
     * @param user string
     * @param pswd string
     * @return array token
     *               expire
     */
    public function login( $p ){
        $user = cocoSysUserModel::getUserByName( $p );
        var_dump($user);
        if( empty( $user ) ){
            throw new \Exception("用户名或密码错误，请重试", 1);
        }
        $passwordHash = md5( $p->user_password . $user['user_salt'] );
        if( $passwordHash != $user['password'] ){
            throw new \Exception("用户名或密码错误，请重试.", 1);
        }
        $expire = Config::get( "applications.user.expire", 60 *60 );
        $access_token = md5( $user['user_id'] . time() );
        // 不做单点登录设置。所以这里不需要管之前的token在服务器中的样子。
        // 否在，这里直接清掉之前的token就能变成单点登录了。
        Cache::Redis()->set( $access_token, json_encode( $user ) );
        // 设置过期时间。两小时
        Cache::Redis()->expire( $access_token, $expire );

        return [
            'token'  => $access_token,
            'expire' => $expire,
        ];
    }
    /**
     * 权限校验
     * @param  user object the userInfo
     * @param  url  string
     * @return bool
     */
    public function Authority( $user, $url ){
        // TODO:
        return true;
    }
}
