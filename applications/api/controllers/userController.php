<?php
namespace applications\api\controllers;

use core\{ Db, Cache, Resule };
use applications\api\params\userParams;
use applications\api\services\userService;
/**
 * api唯一对外不需要用户身份校验的业务流
 */
class userController extends Resule{
    /**
     * 登录
     */
    public function loginAction(){
        // $p = json_decode( file_get_contents( 'php://input') );
        $p = (object)$_GET;
        if( !userParams::login( $p ) ){
            return $this->failed( $p->msg );
        }
        $user = [];
        try {
            $user = userService::login( $p );
            return $this->success( $user );
        } catch (\Exception $e) {
            $this->debug = Db::Mysql()->getErrors();
            return $this->failed( $e->getMessage() );
        }
    }
}
