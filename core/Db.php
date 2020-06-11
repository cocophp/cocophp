<?php
namespace core;

use core\config;
use core\singletonConnect;
/**
 * 数据库类。
 * 此处抛弃了orm和查询构造器等概念，转为sql模板，
 * 如查询 Db::command("Select * from a where id=?")->argv(1)->toArray('one');
 *   Db::command("Select * from a where name=?")->argv("zhangsan")->toArray('all');
 * 此处类似于POD参数绑定的方式，但又不同，不需要额外与数据库进行二次通信，网络性能有所保证。
 */
class Db{
    protected $__DbTable = '';
    protected $__DbArgv  = [];

    protected $__DbMode  = '';
    protected $__DbSql   = '';
    protected $__DbErr   = '';
    protected $_transations = 0;
    protected static $__that = NULL;
    protected static $singletonConnect = NULL;
    private function __construct(){
    }
    /**
     * 三个单例函数。
     * 其中除了Mysql之外，两个函数都可以动态调用，比如 $db=Db::Mysql();$db->command()
     */
    /**
     * Db::Mysql() 一般没有任何操作。仅返回单列
     */
    static public function Mysql(){
        if( self::$__that == NULL ){
            $table = get_called_class();
            self::$__that = new Db();
            self::$singletonConnect = singletonConnect::getInstance();
        }
        return self::$__that;
    }
    /**
     * table函数可直接设置当前主表指向，一般insert update时会很舒服，
     * 如 Db::table( 'a' )->insert();
     */
    static public function table( $table ){
        $that = Db::Mysql();
        $that->__DbTable = "";
        $prefix = config::get( 'DataBase.prefix' );
        if( $prefix !== false ){
            $that->__DbTable = $prefix;
        }
        $that->__DbTable .= $table;
        return $that;
    }
    /**
     * command 一般用于执行sql模板，模板内数据需要通过 argv() 函数注入，
     */
    static public function command( $sql ){
        $that = Db::Mysql();
        $that->__DbSql = $sql;
        return $that;
    }
    /**
     * 请谨慎使用此函数
     * 该函数与command最大的区别，就是不会解析sql模板，所以，任何sql攻击这里都不会防备。
     * 该函数会执行指定sql，如 Db::Mysql()->query( "...." );
     * 但是不会获取结果，想要获取结构，需要 toArray()等方法。
     */
    public function query( $sql ){
        return self::$singletonConnect->__query( $sql );
    }
    /**
     * 调试用三个函数，
     */
    /**
     * 获取最后执行的sql语句。
     */
    public function getLastSql(){
        return singletonConnect::$lastSql;
    }
    /**
     * 获取所有执行到的sql语句。
     */
    public function getMoreSql(){
        return singletonConnect::$moreSql;
    }
    /**
     * 获取sql执行错误信息。
     */
    public function getErrors(){
        return $this->__DbErr;
    }
    /**
     * 事务三联,为了实现事务嵌套，这里使用了 SAVEPOINT（保存点)的形式。
     */
    public function begin(){
        if( !$this->_transations ){
            $this->_transations++;
            return self::$singletonConnect->__query( 'begin' );
        }
        return self::$singletonConnect->__query(
            'SAVEPOINT transaction'.$this->_transations++
        );
    }
    public function rollback(){
        $this->_transations--;
        if( !$this->_transations ){
            return self::$singletonConnect->__query( 'rollback' );
        }
        return self::$singletonConnect->__query(
            'rollback to savepoint transaction'.$this->_transations
        );
    }
    public function commit(){
        $this->_transations--;
        if( !$this->_transations ){
            return self::$singletonConnect->__query( 'commit' );
        }
        return self::$singletonConnect->__query(
            'release savepoint transaction'.$this->_transations
        );
    }
    /**
     * 直接提供了事务级的函数。接受一个闭包函数
     */
    static public function transactions( $func, ...$args ){
        $Db = Db::Mysql();
        try {
            $Db->begin();
            $res = $func( ...$args );
            $Db->commit();
            return $res;
        } catch (\Exception $e) {
            $Db->rollback();
            throw $e;
        }
    }
    /**
     * 参数注入函数，一般配合command()共同使用，以过滤掉sql攻击。
     */
    public function argv( ...$args ){
        array_walk_recursive( $args, function($a){
            $this->__DbArgv[] = $this->__filterString( $a );
        } );
        return $this;
    }
    /**
     * 模板中可好用了，emm。如：
     * $sql = "
     *     Select * from a
     *     where is_del=?
     *         {$db->superArgv($parmas->search,'and name like ?')}
     *     order by id desc
     * ";
     */
    public function superArgv( $args, $where="" ){
        if( empty( $args ) ){
            return "";
        }
        $this->argv( $args );
        return $where;
    }
    /**
     * whereIn() 函数动态注入参数并替换掉模板中的 $tpl ,
     * 如 Db::command( 'select * from a where id in()' )->whereIn( $ids );
     * 若存在多个in条件，可自己加入锚点，如
     * Select * from a where id in(id) and name in(username)
     * ->whereIn( $ids, 'in(id)' )->whereIn( $names, 'in(username)' )->toArray()
     */
    public function whereIn( $args, $tpl = "in()" ){
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
            $tpl,
            "IN(" . implode( ',', $res ) . ")",
            $this->__DbSql
        );
        return $this;
    }
    /**
     * limit的哥哥，分页函数。可直接指定如 ->page( 2, 20 ) 来获取第二页数据(每页20条)。
     */
    public function page( $page_on, $page_size ){
        $page_size = (int)$page_size;
        $page_on   = (int)$page_on > 0 ? ($page_on-1)*$page_size : 0;
        // 计算分页
        return $this->limit( $page_on, $page_size );
    }
    /**
     * 依旧提供了limit函数，是因为某些时刻，我们需要的不只是分页拿数据。
     */
    public function limit( $page_on, $page_size=false ){
        $page_on   = (int)$page_on;
        $tmp = "";
        if( $page_size === false ){
            $tmp = " LIMIT {$page_on}";
        } else {
            $page_size = (int)$page_size;
            $tmp = " LIMIT {$page_on},{$page_size}";
        }
        $this->__DbSql =  str_replace( "limit()", $tmp, $this->__DbSql );
        return $this;
    }
    /**
     * insert三联。其实insertMore只是读起来更顺畅一点罢了。
     */
    /**
     * insert === insertMore
     */
    public function insert( ...$args ){
        return $this->insertMore( ...$args );
    }
    /**
     * insert === insertMore
     */
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
    /**
     * insertOne强制只写入第一条数据。
     */
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
    /**
     * 更新。这里的更新where可以是sql模板也可以是数组。
     * 此处的where若传入数组，则每一项都是and关系。
     * 若复杂where的业务情况，where请插入模板，然后通过argv注入参数，如：
     * Db::table( 'a' )->update( $update, 'where id=? or name=?')->argv()
     * Db::table( 'a' )->update( $update, 'where (id=? or name=?) and is_del=?')->argv()
     */
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
    /**
     * 此处的updateMore,会根据指定field更新对应数据，如：
     * $data = [ ['id'=>1,'name'=>'zhangsan'], ['id'=>2,'name'=>'lisi'] ];
     * Db::table('a')->updateMore( 'id', $data )
     * 会更新id==1的name为zhangsan，id==2的name为lisi
     * 注意此处会生成一条sql，所以本身是事务性的，成功则全成功，单条失败则全失败。
     */
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
    /**
     * 删除必须指定where条件。规则同update函数的where。
     */
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
    /**
     * 生成最终的sql语句。
     * TODO: 此处有个小bug，类似于formatData的sql语句，会有问题，如：
     * Select FROM_UNIXTIME(o.create_time,%Y-%m-%d) as `time` ... 的sql语句会报错，
     * 只能通过以下两种形式：
     * Db::command("Select FROM_UNIXTIME(o.create_time,?)")->argv('%Y-%m-%d')
     * Db::command("Select FROM_UNIXTIME(o.create_time,'%%Y-%%m-%%d')")
     * 也就是原模板中带有%会有问题。 只能通过转移或argv注入。
     */
    public function toSql(){
        if( !empty( $this->__DbArgv ) ){
            $this->__DbSql = str_replace( '?', '%s', $this->__DbSql );
            $this->__DbSql = sprintf( $this->__DbSql, ...$this->__DbArgv );
            $this->__DbArgv = [];
        }
        return $this->__DbSql;
    }
    /**
     * 除事务的三个函数，调试的三个函数之外，上面的所有函数都是注入sql模板和参数，并不执行。
     * 下面
     */
    /**
     * 执行。若sql报错，理论上不返回任何数据。后面版本中同样如此。
     */
    public function toExec(){
        return $this->toBool();
    }
    /**
     * 执行sql，并返回sql执行状态。
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
     * select会返回数组，其他均返回 firstID和rowCount。
     * 理论上,一次insertMore的id连续自增的.返回的firstID和rowCount可以确定全部插入数据ID.
     * 在单主写入的情况下是这样,若双主互备份,或者设置过自增id步长度,那id就不再是连续的,
     * 后面情况可以采用分布式id方式在脚本类直接生成全部来解决.
     *
     * 若您希望查询器做点事情，可传入相关函数。返回值会是最终结果。
     */
    public function toArray( $mode = 'all', $func = null, ...$args ){
        //拿到sql类型。
        $sqlMode = '';
        for ($i=0; $i < strlen($this->__DbSql); $i++) {
            if( in_array( $this->__DbSql[$i], [" ","　","\n","\r","\t"] ) ){
                if( $sqlMode != '' ){
                    break;
                }
                continue;
            }
            $sqlMode = $sqlMode . $this->__DbSql[$i];
        }
        $sqlMode = strtolower( $sqlMode );
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
                if( $func ){
                    $value = $func( $value, ...$args );
                }
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
    /**
     * 将查询结果转换为json字符串。
     * 参数同toArray()
     */
    public function toJson( $mode = 'all', $func = null, ...$args ){
        return json_encode( $this->toArray( $mode, $func, ...$args ) );
    }
    /**
     * 参数过滤器。此处只防sql截断攻击，不防sql注入和存储型xss攻击。
     * 您可以根据自身业务调整此函数。
     */
    protected function __filterString( $str ){
        return "\"" . addslashes( $str ) . "\"";
    }
}
