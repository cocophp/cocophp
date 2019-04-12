<?php

namespace cocophp\db;

use cocophp\db\factory;
use cocophp\core\config;

class DB{

    private $table   = '';
    private $error   = '';
    private $prefix  = '';
    private $lastSql = '';
    private $moreSql = array();

    public function __construct( $table = '' ){
        $this->prefix= config::get( 'mysql.prefix' );
        $this->table( $table );
    }
    public function prefix( $prefix ){
        $this->prefix = $prefix;
    }
    public function table( $table ){
        $this->table = $this->replaceJoin( $this->prefix.$table, '`' );
    }
    public function getLastSql(){
        return $this->lastSql;
    }
    public function getMoreSql(){
        return $this->moreSql;
    }
    public function getError(){
        return $this->error;
    }
    private $part = array();
    public function part(){
        $part = func_get_args();
        if( empty( $part ) ){
            return $this;
        }
        foreach ( $part as $p ) {
            if( is_array( $p ) ){
                $this->part( ...$p );
            }
            if( is_string( $p ) ){
                $this->part[] = $this->replaceJoin( $p, '`' );
            }
        }
        return $this;
    }
    private function replaceJoin( $str, $implode = '"' ){
        if( $implode == '`' ){
            return $this->replace( $str, $implode );
        }
        return $implode . $this->replace( $str, $implode ) . $implode;
    }
    private function replace( $str, $implode = '"' ){
        $str = str_replace( '\\', '\\\\', $str );
        $str = str_replace( '"', '\\"', $str );
        if( $implode == '`' ){
            $str = str_replace( '`', '', $str );
            $temp = explode( '.', $str );
            foreach ($temp as $key => $value) {
                $value = trim( $value );
                if( $value == '*' ){
                    $temp[ $key ] = $value;
                } else {
                    $temp[ $key ] = '`' . $value . '`';
                }

            }
            $str = implode( '.', $temp );
        }
        return $str;
    }
    private $where = array();
    public function whereIn( $where, $value ){
        foreach ( $value as $key => $v ) {
            $value[ $key ] = $this->replaceJoin( $v );
        }
        $where     = $this->replaceJoin( $where, '`' );
        $relations = ' in ';
        $value     = implode( ',', $value );
        if( empty( $this->where ) ){
            $this->where = ' WHERE 1 ';
        }
        $this->where[] = array( '('.$where.$relations.'('. $value .'))', ' AND ' );
        return $this;
    }
    public function or(){
        $this->where[ count( $this->where ) - 1 ][ 1 ] = ' OR ';
        return $this;
    }
    public function where( $where, $relations = false, $value = false ){
        if( empty( $where ) ){
            return $this;
        }
        $where = $this->_where( $where, $relations, $value );
        if( !empty( $where ) ){
            $this->where[] = array( '('.$where.')', ' AND ', );
        }
        return $this;
    }
    public function whereOr( $where, $relations = false, $value = false ){
        return $this->where( $where, $relations, $value )->or();
    }
    private function _where( $where, $relations = false, $value = false ){
        if( empty( $where ) ){
            return ;
        }
        $res = '';
        if( is_array( $where ) ){
            foreach ($where as $key => $wheres ) {
                if( is_numeric( $key ) ){
                    $res = $this->_where( ...$where );
                }
                if( is_array( $wheres ) ){
                    if( isset( $wheres[1] ) ){
                        $relations = $wheres[1];
                    }
                    if( isset( $wheres[2] ) ){
                        $value = $wheres[2];
                    }
                    $wheres = $wheres[0];
                    if( empty( $res ) ){
                        $res = $this->_where( $wheres, $relations, $value );
                    } else {
                        $res .= ' AND ' . $this->_where( $wheres, $relations, $value );
                    }
                }
                if( is_string( $key ) ){
                    if( empty( $res ) ){
                        $res = $this->_where( $key, $wheres );
                    } else {
                        $res .= ' AND ' . $this->_where( $key, $wheres );
                    }
                }
            }
        }
        if( is_string( $where ) or is_numeric( $where ) ){
            if( $relations === false){
                throw new \Exception( "The SQL columns `{$where}` is NULL,and you have to provide parameters.", 1);
            }
            if( $value === false ){
                $value = $relations;
                $relations = '=';
            }
            if( is_numeric( $where ) ){
                if( $this->whereType ){
                    return $where.' '.$relations.' '.$this->replaceJoin($value);
                } else {
                    return $where.' '.$relations.' '.$this->replaceJoin($value,'`');
                }
            }
            if( $this->whereType ){
                return $this->replaceJoin($where,'`').' '.$relations.' '.$this->replaceJoin($value);
            } else {
                return $this->replaceJoin($where,'`').' '.$relations.' '.$this->replaceJoin($value,'`');
            }
        }
        return $res;
    }
    private $order = array();
    public function order(){
        $order = func_get_args();
        if( empty( $order ) ){
            return $this;
        }
        foreach ( $order as $k => $o ) {
            if( is_array( $o ) ){
                if( isset($o[0]) ){
                    $this->order( ...$o );
                } else {
                    foreach ($o as $key => $value) {
                        $this->order( $key, $value );
                    }
                }
            }
            if( is_string( $o ) ){
                if( !isset( $order[ 1 ] ) ){
                    throw new \Exception("Order is need two argv", 1);
                }
                $this->order[] = $this->replaceJoin( $order[ 0 ],'`').' '.$this->replace( $order[ 1 ] );
                break;
            }
        }
        return $this;
    }
    private $limit  = array();
    public function limit( $limit ){
        $limit = func_get_args();
        if( empty( $limit ) ){
            return $this;
        }
        foreach ( $limit as $l ) {
            if( is_array( $l ) ){
                $this->limit( ...array_values($l) );
            }
            if( is_numeric( $l ) ){
                if( count( $this->limit ) >= 2 ){
                    $this->limit = array();
                }
                $this->limit[] = $l;
                if( isset( $this->limit[ 1 ] ) ){
                    $this->limit[0] = ( $this->limit[0] - 1 ) * $this->limit[ 1 ];
                }
            }
        }
        return $this;
    }
    private $group  = '';
    public function group( $group ){
        if( empty( $group ) ){
            return $this;
        }
        if( is_string( $group ) ){
            $this->group = ' GROUP BY ' . $group ;
        }
        return $this;
    }
    public function select(){
        $where = func_get_args();
        if( !empty( $where ) ){
            $this->where( ...$where );
        }
        return $this->_find();
    }
    public function find(){
        $where = func_get_args();
        if( !empty( $where ) ){
            $this->where( ...$where );
        }
        $this->limit( 1 );
        $res = $this->_find();
        if( empty($res) ){
            return [];
        }
        return $res[0];
    }
    public function count(){
        $where = ' WHERE 1 ';
        foreach ($this->where as $key => $value) {
            $where .= $value[1]. $value[0];
        }
        $join = '';
        foreach ( $this->join as $value) {
            $join .= implode( '', $value );
        }
        $part = 'COUNT(*)';
        $order= $this->sqlJoin( 'order', ' ORDER BY ' );
        $limit= $this->sqlJoin( 'limit', ' LIMIT ' );
        $sql = 'SELECT '.$part.' FROM '.$this->table.$join.$where.$order.$this->group;

        $redis = factory::redis();
        if( $redis ){
            $res = $redis->get( md5( $sql ) );
            if( $res ){
                $this->lastSql = $sql;
                $this->moreSql[] = $sql;
                return json_decode( $res, true );
            }
        }
        $res = $this->query( $sql );
        if( $res === false ){
            $this->error = staticMysql::get()->error;
            return 0;
        }
        if( $res->num_rows == 0 ){
            return 0;
        }
        $resArr = $res->fetch_assoc();
        $resArr = $resArr['COUNT(*)'];
        $res->free_result();
        if( $redis ){
            $redis->set( md5( $sql ), json_encode( $resArr ), 2  );
        }
        return $resArr;
    }
    private function _find(){
        $where = ' WHERE 1 ';
        foreach ($this->where as $key => $value) {
            $where .= $value[1]. $value[0];
        }
        $join = '';
        foreach ( $this->join as $value) {
            $join .= implode( '', $value );
        }
        $part = $this->sqlJoin( 'part' , '', '*' );
        $order= $this->sqlJoin( 'order', ' ORDER BY ' );
        $limit= $this->sqlJoin( 'limit', ' LIMIT ' );
        $sql = 'SELECT '.$part.' FROM '.$this->table.$join.$where.$order.$this->group.$limit;
        $this->part = $this->join = $this->where = $this->order = $this->limit = array();
        $this->group = '';
        $redis = factory::redis();
        if( $redis ){
            $res = $redis->get( md5( $sql ) );
            if( $res ){
                $this->lastSql = $sql;
                $this->moreSql[] = $sql;
                return json_decode( $res, true );
            }
        }
        $res = $this->query( $sql );
        if( $res === false ){
            $this->error = staticMysql::get()->error;
            return [];
        }
        if( $res->num_rows == 0 ){
            return [];
        }
        $resArr = array();
        while($a = $res->fetch_assoc()){
          $resArr[] = $a;
        }
        $res->free_result();
        if( $redis ){
            $redis->set( md5( $sql ), json_encode( $resArr ), 2  );
        }
        return $resArr;
    }
    private $join = array();
    public function inner(){
        $this->join[ count($this->join)-1 ][ 'link' ] = ' INNER JOIN ';
        return $this;
    }
    public function left(){
        $this->join[ count($this->join)-1 ][ 'link' ] = ' LEFT JOIN ';
        return $this;
    }
    public function right(){
        $this->join[ count($this->join)-1 ][ 'link' ] = ' RIGHT JOIN ';
        return $this;
    }
    public function leftJoin(){
        return $this->join( ...func_get_args() )->left();
    }
    public function rightJoin(){
        return $this->join( ...func_get_args() )->right();
    }
    public $whereType = true;
    public function join( $table, $db ){
        $obj = new DB();
        $obj->whereType = false;
        $db( $obj );
        $where = '';
        foreach ( $obj->where as $key => $value ) {
            if( empty( $where ) ){
                $where .= $value[0];
                continue;
            }
            $where .= $value[1] . $value[0];
        }
        $this->join[] = array(
            'link'  => ' INNER JOIN ',
            'table' => $this->replaceJoin( $table, '`' ),
            'joins' => ' ON ',
            'where' => $where,
        );
        return $this;
    }
    private function __join(){
        $argv = func_get_args();
        $res = array();
        foreach ($argv as $key => $value) {
            if( !$key ){
                $res[ 'table' ] = $this->replaceJoin( $value, '`' );
                continue;
            }
        }
    }
    private function sqlJoin( $arr, $part, $default = '', $implode = ',' ){
        if( empty( $this->$arr ) ){
            return $default;
        }
        return $part . implode( $implode, $this->$arr );
    }
    private $data = array();
    private $update = array();
    public function data(){
        $data = func_get_args();
        if( empty( $data ) ){
            return $this;
        }
        $flag = false;
        if( !empty( $this->part ) ){
            $flag = true;
        }
        foreach ( $data as $k => $d ) {
            if( isset( $d[0] ) ){
                foreach ($d as $value) {
                    $this->data( $value );
                }
                continue;
            }
            $insertData = '(';
            if( $flag ){
                foreach ( $this->part as $k => $v ) {
                    if( !isset( $d [ $k ] ) ){
                        continue;
                    }
                    $insertData .= $this->replaceJoin( $d [ $k ] ) .',';
                }
            } else {
                foreach ( $d as $key => $value) {
                    $this->part[ $key ] = $this->replaceJoin( $key, '`');
                    $insertData .= $this->replaceJoin( $value ) .',';
                }
            }
            foreach ( $d as $key => $value) {
                $this->update[] = $this->replaceJoin( $key, '`').'='.$this->replaceJoin( $value );
            }
            if( $insertData !== '(' ){
                $insertData[ strlen($insertData) - 1] = ')';
                $this->data[] = $insertData;
            }
            $flag = true;
        }
        return $this;
    }
    public function insert(){
        $data = func_get_args();
        if( !empty( $data ) ){
            $this->data( $data );
        }
        $part = $this->sqlJoin( 'part' , '', '*' );
        $data = $this->sqlJoin( 'data', ' VALUES ' );
        if( empty( $this->data ) ){
            $this->part = $this->data = $this->update = array();
            $this->error = 'Empty DATA for INSERT INTO '.$this->table.' ...';
            return false;
        }
        $sql = 'INSERT INTO '.$this->table.'('.$part.')'.$data;
        $this->part = $this->data = $this->update = array();
        $res = $this->query( $sql );
        if( $res ){
            return [
                'id'  => staticMysql::get()->insert_id,
                'row' => staticMysql::get()->affected_rows,
            ];
        }
        $this->error = staticMysql::get()->error;
        return false;
    }
    public function update(){
        $data = func_get_args();
        if( !empty( $data ) ){
            $this->data( $data );
        }
        if( empty( $this->update ) or empty( $this->where ) ){
            $this->update = $this->where = array();
            $this->error = 'Empty DATA or WHERE for UPDATE SET '.$this->table.'...';
            return false;
        }
        $where = ' WHERE 1 ';
        foreach ($this->where as $key => $value) {
            $where .= $value[1]. $value[0];
        }
        $this->sqlJoin( 'update', ' SET ' );
        $sql = 'UPDATE '.$this->table.$this->update.$where;
        $this->update = $this->where = array();
        $res = $this->query( $sql );
        if( $res ){
            return staticMysql::get()->affected_rows;
        }
        $this->error = staticMysql::get()->error;
        return false;
    }
    public function updateMore( $datas, $where ){
        if( empty( $datas ) or empty( $where ) ){
            $this->error = 'Empty DATA or WHERE for UPDATA(more) SET '.$this->table.'...';
            return false;
        }
        // 神奇的算法，时间复杂度居然是O(n+1)
        $upColumns = array();
        $In = array();
        // 此处还是要拿到第一个数组的全部字段,
        // 后面出现数组长度不一的情况，给错误，不然会出现误更新的情况。
        foreach ($datas as $data) {
            foreach ($data as $key => $value) {
                $column = $this->replaceJoin( $key, '`' );
                if( $key == $where ) {
                    continue;
                }
                $upColumns[ $key ] = $column . " = CASE " . $this->replaceJoin( $where, '`' );
            }
            break;
        }
        foreach ($datas as $key => $data) {
            foreach ($upColumns as $key => $value) {
                if( !isset( $data[ $key ] ) ){
                    throw new \Exception($this->replaceJoin( $key, '`' ) . " is NULL ", 1);
                }
                $column = $this->replaceJoin( $key, '`' );
                $value  = $this->replaceJoin( $data[ $key ] );
                $upColumns[ $key ] .= " WHEN " . $data[$where] . ' THEN ' . $value;
            }
            $In[] = $this->replaceJoin( $data[ $where ] );
        }
        // 下面注释为原算法
        // foreach ($datas as $data) {
        //     foreach ($data as $column => $value) {
        //         if( $column == $where ) {
        //             $In[] = $value;
        //             continue;
        //         }
        //         if( !isset( $upColumns[ $column ] ) ) {
        //             $upColumns[ $column ] = $column . " = CASE " . $where;
        //         }
        //         $upColumns[ $column ] .= " WHEN " . $data[$where] . " THEN '" . $value . "'";
        //     }
        // }
        $sql = "UPDATE " . $this->table . " SET "
            .  implode( ' END, ', $upColumns )
            .  " END WHERE " . $this->replaceJoin( $where, '`' ) . " IN(" . implode( ',',$In ) . ")";
        $res = $this->query( $sql );
        if( $res ){
            return staticMysql::get()->affected_rows;
        }
        $this->error = staticMysql::get()->error;
        return false;
    }
    public function remove(){
        $where = func_get_args();
        if( !empty( $where ) ){
            $this->where( ...$where );
        }
        if( empty( $this->where ) ){
            $this->where = array();
            $this->error = 'Empty WHERE for DELETE FROM '.$this->table.'...';
            return false;
        }
        $where = ' WHERE 1 ';
        foreach ($this->where as $key => $value) {
            $where .= $value[1]. $value[0];
        }
        $sql = "DELETE FROM ".$this->table.$where;
        $this->where = array();
        $res = $this->query( $sql );
        if( $res ){
            return staticMysql::get()->affected_rows;
        }
        $this->error = staticMysql::get()->error;
        return false;
    }
    public function query( $sql ){
        $this->lastSql = $sql;
        $this->moreSql[] = $sql;
        return staticMysql::get()->query( $sql );
    }
    public function charset( $charset ){
        staticMysql::get()->query( 'set names "' . $charset . '"' );
        return $this;
    }
}
/**
 *
 */
class staticMysql{
    static private $mysql = false;
    static public function get( ){
        if( !self::$mysql ){
            foreach ( config::get( 'mysql' ) as $key => $value) {
                $$key = $value;
            }
            self::$mysql = new \mysqli( $ip, $user, $pswd, $database, $port );
            self::$mysql->query('set names "' . $charset . '"');
        }
        return self::$mysql;
    }
}
