<?php
namespace applications\template\models;

use core\Db;
use tableField\publicField;
use tableField\edmTemplate;
/**
 *
 */
class select{
    static public function getTplList( $params, $main_id ){
        $db = Db::Mysql()->argv(
            $main_id,
            publicField::$del_type['undeleted'],
            edmTemplate::$tpl_type['used']
        );
        return $db->command(
              "select `tpl`.`tpl_id`, `tpl`.`tpl_title`, `tpl`.`create_time`, `tpl`.`modify_time`, "
            . "`createor`.`real_name` as `createor_name`, `modifyor`.`real_name` as `modifyor_name`  from `edm_template` as `tpl`"
            . "join `mmp_user_info` as `createor` on `tpl`.`create_main_id`=`createor`.`id` "
            . "join `mmp_user_info` as `modifyor` on `tpl`.`modify_main_id`=`modifyor`.`id` "
            . " where `tpl`.`main_id`=? and `tpl`.`is_del`=? and `tpl`.`tpl_type`=? order by `tpl`.`tpl_id` desc"
            . $db->page( $params['limit']['page_no'], $params['limit']['page_size'] )
        );
    }
    static public function getTplTotal( $main_id ){
        return Db::Mysql()->command(
            "select count(*)total from `edm_template`"
            . " where `main_id`=? and `is_del`=? and `tpl_type`=? "
        )->argv( $main_id, publicField::$del_type['undeleted'], edmTemplate::$tpl_type['used'] );
    }
    static public function getTplOnlyTitle( $main_id ){
        return $db = Db::Mysql()->command(
            "select `tpl_id`, `tpl_title` from `edm_template`".
            " where `main_id`=? and `is_del`=? and `tpl_type`=? order by `tpl_id` desc"
        )->argv( $main_id, publicField::$del_type['undeleted'], edmTemplate::$tpl_type['used'] );
    }
    static public function getTplDetail( $main_id, $tpl_id ){
        return $db = Db::Mysql()->command(
            "select * from `edm_template`" .
            ' where `tpl_id`=? and `main_id`=? and `is_del`=? and `tpl_type`=?'
        )->argv( $tpl_id, $main_id, publicField::$del_type['undeleted'], edmTemplate::$tpl_type['used'] );
    }
}
