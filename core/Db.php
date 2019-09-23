<?php
namespace core;

use core\config;
use core\singletonConnect;
/**
 *
 */
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
    /**
     *
     */
    public function command( $sql ){
        $this->__DbSql = $sql;
        return $this;
    }
    /**
     *
     */
    public function argv(){
        $argv = func_get_args();
        array_walk_recursive( $argv, function($a){
            $this->__DbArgv[] = $this->__filterString( $a );
        } );
        return $this;
    }
    /**
     *
     */
    public function whereIn( $args ){
        $res = [];
        foreach ($args as $value) {
            if( !empty( $value ) ){
                $res[] = '?';
                $this->argv( $value );
            }
        }
        return 'IN(' . implode( ',', $res ) . ')';
    }
    /**
     *
     */
    public function whereUnknown( $args ){
        $res = [];
        foreach ($args as $key => $value) {
            if( !empty( $value ) ){
                $res[] = "$key=?";
                $this->argv( $value );
            }
        }
        return implode( ' AND ', $res );
    }
    /**
     *
     */
    public function page( $page_no, $page_size ){
        $page_size = (int)$page_size;
        $page_no   = (int)$page_no > 0 ? ($page_no-1)*$page_size : 0;
        // 计算分页
        return $this->limit( $page_no, $page_size );
    }
    /**
     *
     */
    public function limit( $page_no, $page_size=false ){
        $page_no   = (int)$page_no;
        if( $page_size === false ){
            return " LIMIT {$page_no}";
        }
        $page_size = (int)$page_size;
        return " LIMIT {$page_no},{$page_size}";
    }
    /**
     *
     */
    public function insert(){
        return call_user_func_array( [$this, 'insertMore'], func_get_args() );
        return $this->insertMore( func_get_args() );
    }
    /**
     *
     */
    protected function __filterString( $str ){
        return "\"" . addslashes( $str ) . "\"";
    }
    /**
     *
     */
    public function insertMore(){
        $data  = array();
        $datas = array();
        $field = array();
        $hasOcc= true;
        $index = 0;
        $argv = func_get_args();
        if( empty($argv) ){
            return $this;
        }
        // 我擦，这个用起来真的好痛苦。万恶的php5.5
        foreach ($argv as $key => $value) {
            foreach ($value as $v) {
                unset( $argv[$key] );
                $argv[] = $v;
            }
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
    /**
     *
     */
    public function insertOne(){
        $datas = array();
        $field = array();
        $argv = func_get_args();
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
    /**
     *
     */
    public function update( $field, $where = [] ){
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
        $temp = [];
        $this->__DbSql .= " WHERE ";
        foreach ( $where as $f => $d ) {
            $temp[] = "`$f`=" . $this->__filterString( $d );
        }
        $this->__DbSql .= implode( ' AND ', $temp );
        return $this;
    }
    /**
     *
     */
    public function updateMore( $filed ){
        $argv = func_get_args();
        if( isset( $argv[0] ) ){
            unset( $argv[0] );
        }
        $upField = array();
        $inField = array();
        $hasOcc  = true;
        // 我擦，这个用起来真的好痛苦。万恶的php5.5
        foreach ($argv as $key => $value) {
            foreach ($value as $v) {
                unset( $argv[$key] );
                $argv[] = $v;
            }
        }
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
    /**
     *
     */
    public function delete(){
        $where = func_get_args();
        if( empty( $where ) ){
            throw new \Exception("Delete must give a where", 1);
        }
        // delete from onewechat_user_info where id = 0;
        $this->__DbSql = "DELETE FROM `{$this->__DbTable}` WHERE";
        $temp = array();
        foreach ( $where as $w ) {
            foreach ($w as $f => $d ) {
                $temp[] = "`$f`=" . $this->__filterString( $d );
            }
        }
        $this->__DbSql .= implode( ' AND ', $temp );
        $this->__DbArgv = [];
        return $this;
    }
    /**
     *
     */
    public function toExec(){
        return $this->toBool();
    }
    /**
     *
     */
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
    public function toArray(){
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
            // Mysqli类内部实现了迭代器接口,不需要$res->fetch_assoc()
            foreach ( $res as $value) {
                $r[] = $value;
            }
            return $r;
        }
        return [
            'rowCount'=> self::$singletonConnect->__getConn()->affected_rows,
            'firstID' => self::$singletonConnect->__getConn()->insert_id,
        ];
    }
    /**
     *
     */
    public function toJson(){
        return json_encode( $this->toArray() );
    }
    /**
     *
     */
    public function toSql(){
        // 若 $this->__DbArgv 不为空，则解析并嵌入到sql语句中。
        // var_dump( $this->__DbSql,$this->__DbArgv );
        if( !empty( $this->__DbArgv ) ){
            $this->__DbSql = str_replace( '?', '%s', $this->__DbSql );
            // 这个处理起来真的好麻烦啊 php5.6以上,可能直接用下面函数就搞定了.
            // $this->__DbSql = sprintf( $this->__DbSql, ...$this->__DbArgv );
            // 没办法,暂时先这样吧
            $this->__DbArgv = array_reverse( $this->__DbArgv );
            $this->__DbArgv[] = $this->__DbSql;
            $this->__DbSql = call_user_func_array( 'sprintf', array_reverse( $this->__DbArgv ) );
            $this->__DbArgv = [];
        }
        return $this->__DbSql;
    }
    public function getErrors(){
        return $this->__DbErr;
    }
}
