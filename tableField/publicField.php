<?php
namespace tableField;
/**
 *
 */
class publicField{
    static $del_type = [
        'undeleted' => 1,
        'deleted'   => 2,
    ];
    static public function mainID( $paramsMatch ){
        $paramsMatch->match( 'main_id' );
        $paramsMatch->rule( 'int' );
        $paramsMatch->rule( "values between 1 4294967295" );
        $paramsMatch->info( '请提供正确的main_id' );
        $paramsMatch->required();
        return $paramsMatch;
    }
    static public function modifyMainID( $paramsMatch ){
        $paramsMatch->match( 'modify_main_id' );
        $paramsMatch->rule( 'int' );
        $paramsMatch->rule( "values between 1 4294967295" );
        $paramsMatch->info( '请提供正确的修改者id' );
        $paramsMatch->required();
        return $paramsMatch;
    }
    static public function search( $paramsMatch ){
        $paramsMatch->match( 'search' );
        $paramsMatch->rule( "length between 0 255" );
        $paramsMatch->defaultValue( '' );
        $paramsMatch->info( '请提供正确的搜索值' );
        $paramsMatch->matchAfter( function( $s ){
            if( empty( $s ) ){
                return '';
            }
            return "%$s%";
        }, 'this' );
        return $paramsMatch;
    }
    static public function modifyTime( $paramsMatch ){
        $paramsMatch->match( 'modify_time' );
        $paramsMatch->refuse();
        $paramsMatch->defaultValue( time() );
        return $paramsMatch;
    }
    static public function startTime( $paramsMatch ){
        $paramsMatch->match( 'start_time' );
        $paramsMatch->matchAfter( function($time){
            if( strtotime( $time ) ){
                return strtotime( $time );
            }
            return 0;
        }, 'this' );
        $paramsMatch->info( '请提供正确的发送时间' );
        $paramsMatch->defaultValue( 0 );
        return $paramsMatch;
    }
    static public function endTime( $paramsMatch ){
        $paramsMatch->match( 'end_time' );
        $paramsMatch->matchAfter( function($time){
            if( strtotime( $time ) ){
                return strtotime( $time );
            }
            return 0;
        }, 'this' );
        $paramsMatch->info( '请提供正确的发送时间' );
        $paramsMatch->defaultValue( 0 );
        return $paramsMatch;
    }
    static public function isDel( $paramsMatch ){
        $paramsMatch->match( 'is_del' );
        $paramsMatch->rule( 'int' );
        $paramsMatch->rule( "in_array", 'this', [0, 1, 2] );
        $paramsMatch->info( '请提供正确的删除类型' );
        return $paramsMatch;
    }
    static public function limit( $paramsMatch, $maxSize ){
        $paramsMatch->match( 'page_no' );
        $paramsMatch->rule( 'int' );
        $paramsMatch->rule( 'values > 0' );
        $paramsMatch->info( '展示页标必须大于0' );
        $paramsMatch->sever( 'limit' );
        $paramsMatch->defaultValue( 1 );

        $paramsMatch->match( 'page_size' );
        $paramsMatch->rule( 'int' );
        $paramsMatch->rule( "values between 1 $maxSize" );
        $paramsMatch->info( "每页展示条数只能在1--{$maxSize}之间" );
        $paramsMatch->sever( 'limit' );
        $paramsMatch->defaultValue( 10 );
        return $paramsMatch;
    }
}
