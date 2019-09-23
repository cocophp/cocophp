<?php
namespace applications\template\controllers;

use applications\AuthBase;
use core\Db;
use applications\template\params\paramsBuilder;
use applications\template\models\select;
use tableField\publicField;
use tableField\edmTemplate;
/**
 *
 */
class asyncController extends AuthBase{
    function __construct(){
        parent::__construct();
    }
    public function indexAction(){
        $params = $_POST;
        if( !paramsBuilder::show( $params ) ){
            return $params;
        }
        return [
            'code' => 0,
            'msg'  => "SUCCESS",
            'data' => [
                'list'  => $this->formatList( $params ),
            ],
            'debug' => [
                'DbErrorInfo' => Db::Mysql()->getErrors(),
                'moreSql'     => Db::Mysql()->getMoreSql(),
            ],
            'limit' => [
                'total'     => select::getTplTotal( $params['main_id'] )->toArray()[0]['total'],
                'page_no'   => $params['limit']['page_no'],
                'page_size' => $params['limit']['page_size'],
            ],
        ];
    }
    private function formatList( $params ){
        $res = select::getTplList( $params, $params['main_id'] )->toArray();
        foreach ( $res  as $key => $value ) {
            $res[ $key ][ 'create_time' ] = date( 'Y-m-d H:i:s', $value[ 'create_time' ] );
            $res[ $key ][ 'modify_time' ] = date( 'Y-m-d H:i:s', $value[ 'modify_time' ] );
        }
        return $res;
    }
    public function listAction(){
        $params = $_POST;
        if( !paramsBuilder::show( $params ) ){
            return $params;
        }
        return [
            'code' => 0,
            'msg'  => "SUCCESS",
            'data' => [
                'list'  => select::getTplOnlyTitle( $params['main_id'] )->toArray(),
            ],
            'debug' => [
                'DbErrorInfo' => Db::Mysql()->getErrors(),
                'moreSql'     => Db::Mysql()->getMoreSql(),
            ],
        ];
    }
    public function detailAction(){
        $params = $_POST;
        if( !paramsBuilder::detail( $params ) ){
            return $params;
        }
        $detail = select::getTplDetail( $params['main_id'], $params[ 'tpl_id'] )->toArray();
        if( empty( $detail ) ){
            return[
                'code' => '999',
                'msg'  => '未检索到任何相关信息',
                'debug'=> [
                    Db::Mysql()->getErrors(),
                    Db::Mysql()->getMoreSql(),
                ]
            ];
        }
        $detail[0]['contacts']   = [];
        $detail[0]['uploadInfo'] = [];
        return [
            'code' => '0',
            'msg'  => 'SUCCESS',
            'data' => $detail[0],
            'debug'=> [
                Db::Mysql()->getErrors(),
                Db::Mysql()->getMoreSql(),
            ]
        ];
    }
    public function editAction(){
        $params = $_POST;
        if( !paramsBuilder::edit( $params ) ){
            return $params;
        }
        if( empty( $params['tpl_id'] ) ){
            return $this->createTask( $params );
        }
        return $this->updateTask( $params );
    }
    public function removeAction(){
        $params = $_POST;
        if( !paramsBuilder::detail( $params ) ){
            return $params;
        }
        $tmp = Db::Mysql()->table('edm_template')->update(
            [
                'is_del'      => publicField::$del_type['deleted'],
                'modify_time' => time(),
                'modify_main_id' => $_POST['main_id'],
            ],
            $params
        )->toBool();

        if( !Db::Mysql()->table('edm_template')->update( ['is_del'=>publicField::$del_type['deleted']], $params )->toBool() ){
            return [
                'code' => '999',
                'msg'  => '系统繁忙，请稍后重试',
            ];
        }
        return [
            'code' => 0,
            'msg'  => '删除成功',
        ];
    }
    private function createTask( $params ){
        $template = [
            'tpl_title'      => $params['tpl_title'],
            'tpl_content'    => $params['tpl_content'],
            'tpl_type'       => edmTemplate::$tpl_type['used'],

            'create_time'    => $params['modify_time'],
            'modify_time'    => $params['modify_time'],
            'main_id'        => $params['main_id'],
            'create_main_id' => $params['modify_main_id'],
            'modify_main_id' => $params['modify_main_id'],
        ];
        $db = Db::Mysql();
        if( !$db->table('edm_template')->insertOne( $template )->toBool() ){
            return [
                'code' => '999',
                'msg'  => '系统繁忙，请稍后重试',
                'debug' => [
                    $db->getErrors(),
                    $db->getMoreSql(),
                ]
            ];
        }
        return [
            'code' => 0,
            'msg'  => '添加成功',
        ];
    }
    private function updateTask( $params ){
        $template = [
            'tpl_title'      => $params['tpl_title'],
            'tpl_content'    => $params['tpl_content'],

            'modify_time'    => $params['modify_time'],
            'modify_main_id' => $params['modify_main_id'],
        ];
        $where = [
            'tpl_id'   => $params['tpl_id'],
            'tpl_type' => edmTemplate::$tpl_type['used'],
        ];
        $db = Db::Mysql();
        if( !$db->table('edm_template')->update( $template, $where )->toBool() ){
            return [
                'code' => '999',
                'msg'  => '系统繁忙，请稍后重试',
                'debug' => [
                    $db->getErrors(),
                    $db->getMoreSql(),
                ]
            ];
        }
        return [
            'code' => 0,
            'msg'  => '修改成功',
        ];
    }
}
