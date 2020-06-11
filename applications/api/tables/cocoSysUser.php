<?php
namespace applications\api\tables;
/**
 *
 */
class cocoSysUser{
    static public function userID( $paramsMatch ){
        $paramsMatch->alias( 'user_id' );
        $paramsMatch->rule( 'int' );
        $paramsMatch->rule( "values between 1 4294967295" );
        $paramsMatch->info( '请选择一个正确的用户' );
        return $paramsMatch;
    }
    static public function userName( $paramsMatch ){
        $paramsMatch->alias( 'user_name' );
        $paramsMatch->rule( "length between 0 255" );
        $paramsMatch->info( '请输入用户名称，长度不能超过255个字符' );
        $paramsMatch->defaultValue( '' );
        return $paramsMatch;
    }
    static public function password( $paramsMatch ){
        $paramsMatch->alias( 'user_password' );
        $paramsMatch->rule( "length between 0 255" );
        $paramsMatch->info( '请输入密码，长度不能超过255个字符' );
        $paramsMatch->defaultValue( '' );
        return $paramsMatch;
    }
    static public function rePassword( $paramsMatch ){
        $paramsMatch->alias( 'rePswd' );
        $paramsMatch->rule( "length between 0 255" );
        $paramsMatch->info( '请再次确认密码，' );
        $paramsMatch->defaultValue( '' );
        return $paramsMatch;
    }
    static public function oldPassword( $paramsMatch ){
        $paramsMatch->alias( 'oldPswd' );
        $paramsMatch->rule( "length between 0 255" );
        $paramsMatch->info( '请提供旧密码以验证您当前身份' );
        $paramsMatch->defaultValue( '' );
        return $paramsMatch;
    }
    static public function userAbstruct( $paramsMatch ){
        $paramsMatch->alias( 'user_abstruct' );
        $paramsMatch->rule( "length between 0 255" );
        $paramsMatch->info( '请输入用户简介，长度不能超过255个字符' );
        $paramsMatch->defaultValue( '' );
        return $paramsMatch;
    }
    static public function userBadge( $paramsMatch ){
        $paramsMatch->alias( 'user_badge' );
        $paramsMatch->rule( "length between 0 255" );
        $paramsMatch->info( '请提供正确的用户头像（图片路径）' );
        $paramsMatch->defaultValue( '' );
        return $paramsMatch;
    }
}
