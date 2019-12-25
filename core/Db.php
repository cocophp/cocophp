<?php
namespace core;

use core\config;
use core\singletonConnect;

class Db{
    protected $__DbTable = '';
    protected $__DbArgv  = [];

    protected $__DbMode  = '';
    protected $__DbSql   = '';
    protected $__DbErr   = '';
    protected static $__that = NULL;
    protected static $singletonConnect = NULL;

    static public function Mysql(){
        if( self::$__that == NULL ){
            $table = get_called_class();
            self::$__that = new Db();
            self::$singletonConnect = singletonConnect::getInstance();
        }
        return self::$__that;
    }
    private function __construct(){
    }
    public function query( $sql ){
        return self::$singletonConnect->__query( $sql );
    }
    public function getLastSql(){
        return singletonConnect::$lastSql;
    }
    public function getMoreSql(){
        return singletonConnect::$moreSql;
    }
    public function begin(){
        return self::$singletonConnect->__query( 'begin' );
    }
    public function rollback(){
        return self::$singletonConnect->__query( 'rollback' );
    }
    public function commit(){
        return self::$singletonConnect->__query( 'commit' );
    }
    public function table( $table ){
        $this->__DbTable = "";
        $prefix = config::get( 'DataBase.prefix' );
        if( $prefix !== false ){
            $this->__DbTable = $prefix;
        }
        $this->__DbTable .= $table;
        return $this;
    }
    public function command( $sql ){
        $this->__DbSql = $sql;
        return $this;
    }
    public function argv( ...$args ){
        array_walk_recursive( $args, function($a){
            $this->__DbArgv[] = $this->__filterString( $a );
        } );
        return $this;
    }
    public function whereIn( $args ){
        $res = [];
        foreach ($args as $value) {
            if( !empty( $value ) ){
                $res[] = $this->__filterString( $value );
            }
        }
        if( empty( $res ) ){
            return '';
        }
        $this->__DbSql =  str_replace(
            "in()",
            "IN(" . implode( ',', $res ) . ")",
            $this->__DbSql
        );
        return $this;
    }
    public function page( $page_no, $page_size ){
        $page_size = (int)$page_size;
        $page_no   = (int)$page_no > 0 ? ($page_no-1)*$page_size : 0;
        // 计算分页
        return $this->limit( $page_no, $page_size );
    }
    public function limit( $page_no, $page_size=false ){
        $page_no   = (int)$page_no;
        $tmp = "";
        if( $page_size === false ){
            $tmp = " LIMIT {$page_no}";
        } else {
            $page_size = (int)$page_size;
            $tmp = " LIMIT {$page_no},{$page_size}";
        }
        $this->__DbSql =  str_replace( "limit()", $tmp, $this->__DbSql );
        return $this;
    }
    public function insert( ...$args ){
        return $this->insertMore( ...$args );
    }
    protected function __filterString( $str ){
        return "\"" . addslashes( $str ) . "\"";
    }
    public function insertMore( ...$argv ){
        $data  = array();
        $datas = array();
        $field = array();
        $hasOcc= true;
        $index = 0;
        if( empty($argv) ){
            return $this;
        }
        foreach ( $argv as $key => $value ) {
            if( empty($value) ){
                continue;
            }
            $data  = array();
            $index = 0;
            foreach ($value as $f => $d) {
                $data[] = $this->__filterString( $d );
                if( $hasOcc ){
                    $field[ "`$f`" ] = $index++;
                    continue;
                }
                if( !isset( $field["`$f`"] ) ){
                    throw new \Exception( "The table field($f) is Must be provided", 1 );
                }
                if( $field["`$f`"] != $index++ ){
                    throw new \Exception( "The table field($f) is misplaced", 1 );
                }
            }
            $hasOcc = false;
            $datas[] = '(' . implode( ',', $data ) . ')';
        }
        if( empty( $datas ) ){
            return $this;
        }
        $this->__DbSql = "INSERT INTO {$this->__DbTable}(" . implode(',', array_keys($field)) . ") VALUES" . implode(',', $datas);
        $this->__DbArgv = [];
        return $this;
    }
    public function insertOne( ...$argv ){
        $datas = array();
        $field = array();
        $this->__DbSql = "INSERT INTO `{$this->__DbTable}`";
        foreach ( $argv as $value ) {
            foreach ( $value as $f => $d ) {
                $datas[] = $this->__filterString( $d );
                $field[] = "`$f`";
            }
            break;
        }
        $this->__DbSql .= '(' . implode(',', $field) . ') VALUES(' . implode(",", $datas ) .')';
        $this->__DbArgv = [];
        return $this;
    }
    public function update( $field, $where = '' ){
        $this->__DbSql = "UPDATE `{$this->__DbTable}` SET ";
        $this->__DbArgv = [];
        $temp = [];
        foreach ( $field as $f => $d ) {
            $temp[] = "`$f`=" . $this->__filterString( $d );
        }
        $this->__DbSql .= implode( ',', $temp );
        if( empty( $where ) ){
            return $this;
        }
        if( is_string( $where ) ){
            $this->__DbSql .= " WHERE " . $where;
            return $this;
        }
        $temp = [];
        $this->__DbSql .= " WHERE ";
        foreach ( $where as $f => $d ) {
            $temp[] = "`$f`=" . $this->__filterString( $d );
        }
        $this->__DbSql .= implode( ' AND ', $temp );
        return $this;
    }
    public function updateMore( $filed, ...$argv ){
        $upField = array();
        $inField = array();
        $hasOcc  = true;
        if( empty( $argv ) ){
            return $this;
        }
        foreach ($argv as $key => $data) {
            foreach ($data as $column => $value) {
                if( $column == $filed ) {
                    $inField[] = $this->__filterString($value);
                    continue;
                }
                if( $hasOcc ){
                    $upField[ $column ] = "`$column` = CASE `$filed`";
                }
                if( !isset( $upField[ $column ] ) ) {
                    throw new \Exception( $column . " 在下标 " . $key . " 中不存在，请仔细核对原数组", 1 );
                }
                if( !isset( $data[ $filed ] ) ){
                    throw new \Exception( "更新数组中必须提供条件字段", 1 );
                }
                $upField[ $column ] .= " WHEN " . $this->__filterString($data[ $filed ]) . ' THEN ' . $this->__filterString($value);
            }
            $hasOcc = false;
        }
        if( empty( $upField ) ){
            return $this;
        }
        $this->__DbArgv = [];
        $this->__DbSql = "UPDATE `{$this->__DbTable}` SET " .  implode( ' END, ', $upField )
            .  " END WHERE `$filed` IN(" . implode( ',', $inField ) . ")";
        return $this;
    }
    public function delete( $where = '' ){
        if( empty( $where ) ){
            throw new \Exception("Delete must give a where", 1);
        }
        $this->__DbSql = "DELETE FROM `{$this->__DbTable}` WHERE ";

        if( is_string( $where ) ){
            $this->__DbSql .= $where;
            return $this;
        }
        $temp = [];
        foreach ( $where as $f => $d ) {
            $temp[] = "`$f`=" . $this->__filterString( $d );
        }
        $this->__DbSql .= implode( ' AND ', $temp );
        return $this;
    }
    public function toExec(){
        return $this->toBool();
    }
    public function toBool(){
        $sql = $this->toSql();
        if( empty( $sql ) ){
            return true;
        }
        $res = self::$singletonConnect->__query( $sql );
        if( $res === false ){
            $this->__DbErr = self::$singletonConnect->__getConn()->error;
            return false;
        }
        $this->__DbSql = '';
        return true;
    }
    /**
     * 理论上,一次insertMore的id连续自增的.返回的firstID和rowCount可以确定全部插入数据ID.
     * 在单主写入的情况下是这样,若双主互备份,或者设置过自增id步长度,那id就不再是连续的,
     * 后面情况可以采用分布式id方式在脚本类直接生成全部来解决.
     */
    public function toArray( $mode = 'all' ){
        $sqlMode = explode( ' ', $this->__DbSql );
        foreach ($sqlMode as $value) {
            if( !empty( $value ) ){
                $sqlMode = strtolower( $value );
                break;
            }
        }
        $res = self::$singletonConnect->__query( $this->toSql() );
        if( $res === false ){
            $this->__DbErr = self::$singletonConnect->__getConn()->error;
            if( $sqlMode == 'select' ){
                return [];
            }
            return [
                'rowCount'=> 0,
                'firstID' => 0,
            ];
        }

        $r = array();
        if( $sqlMode == 'select' ){
            foreach ( $res as $value) {
                if( $mode == 'one' ){
                    return $value;
                }
                $r[] = $value;
            }
            return $r;
        }
        return [
            'rowCount'=> self::$singletonConnect->__getConn()->affected_rows,
            'firstID' => self::$singletonConnect->__getConn()->insert_id,
        ];
    }
    public function toJson( $mode = 'all' ){
        return json_encode( $this->toArray( $mode ) );
    }
    public function toSql(){
        if( !empty( $this->__DbArgv ) ){
            $this->__DbSql = str_replace( '?', '%s', $this->__DbSql );
            $this->__DbSql = sprintf( $this->__DbSql, ...$this->__DbArgv );
            $this->__DbArgv = [];
        }
        return $this->__DbSql;
    }
    public function getErrors(){
        return $this->__DbErr;
    }
}
