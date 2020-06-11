<?php
namespace applications\api\models;

use core\Db;
/**
 *
 */
class cocoSysUserModel{
    public function getUserByName( $p ){
        $sql = "
            Select * from coco_sys_user where user_name=? limit 1
        ";
        return Db::command( $sql )->argv( $p->user )->toArray( 'one' );
    }
}
