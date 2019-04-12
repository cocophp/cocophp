<?php
namespace cocophp\std;

use cocophp\std\params;
/**
 *
 */
class output{
    function __construct(){

    }
    public function limit( $limit ){
        $total = 0;
        if( isset( $this->limit['total'] ) ){
            $total =  $this->limit['total'];
        }
        $this->limit = $limit;
        return $this->total( $total );
    }
    public function total( int $total ){
        $this->limit[ 'total' ] = $total;
        return $this;
    }
    public $code = 0;
    public function code( int $code ){
        $this->code = $code;
        return $this;
    }
    public $msg = '';
    public function message( $msg ){
        $this->msg = $msg;
        return $this;
    }
    public function error(){
        $info = func_get_args();
        foreach ( $info as $key => $value) {
            if( is_array( $value ) ){
                if( isset($value[0] ) ){
                    $this->error( ...$value );
                } else {
                    $this->code( $value[ 'code' ] );
                    $this->message( $value[ 'msg' ] );
                }
            } else {
                $this->code( $info[0] );
                $this->message( $info[1] );
                break;
            }
        }
        unset( $this->data );
        unset( $this->limit );
        return $this;
    }
    public $data = array();
    public function data(){
        $data = func_get_args();
        foreach ($data as $key => $value) {
            $this->data[] = $value;
        }
        return $this;
    }
    public function change( $path, $data ){
        $temp = &$this->data;
        $path = str_replace( ' ', '', $path );
        $path = explode( '.', $path );
        foreach ($path as $v ) {
            if( !isset( $temp[ $v ] ) ){
                $temp[ $v ] = array();
            } else {
                if( !is_array( $temp [ $v ] ) ){
                    $t = $temp[ $v ];
                    $temp[ $v ] = array();
                    $temp[ $v ][ $v ] = $t;
                }
            }
            $temp = &$temp[ $v ];
        }
        $temp = $data;
    }
    public function debug(){
        $debug = func_get_args();
        foreach ($debug as $key => $value) {
            $this->debug[] = $value;
        }
        return $this;
    }
    public function toArray(){
        if( !$this->xss ){
            return json_decode( json_encode( $this ), true );
        }
        $this->data = $this->superXss( $this->data );
        return json_decode( json_encode( $this ), true );
    }
    public function toChar(){
        $this->data = $this->superSpecialChars( $this->data );
        return $this;
    }
    public function toJson(){
        return json_encode( $this->toArray() );
    }
    public function toConsole(){
        $res = '';
        if( isset( $this->debug ) ){
            foreach ($this->debug as $key => $value) {
                $res .= "<script>console.log( '====== debug\'s {$key} => \n".print_r($value,true)." ')</script>\r";
            }
        }
        if( isset( $this->data ) ){
            foreach ($this->data as $key => $value) {
                $res .= "<script>console.log( '====== data\'s {$key} => \n".print_r($value,true)." ')</script>\r";
            }
        }
        if( isset( $this->limit ) ){
            foreach ($this->limit as $key => $value) {
                $res .= "<script>console.log( \"====== limit's {$key} => {$value} \")</script>\r";
            }
        }
        return str_replace( "\n", '\\n\t\t', $res );
    }
    public function strToUrl( $str ){
        if( strpos( '/', $str ) === 0 ){
            return $str;
        }
        if( strpos( 'http', $str ) === 0 ){
            return $str;
        }
        if( params::server( 'SERVER_PORT' ) == 80 ){
            return 'http://' . $str;
        } else {
            return 'https://' . $str;
        }
    }
    private $xss = false;
    public function toXss(){
        $this->xss = true;
        return $this;
    }
    public function superSpecialChars( $argv ){
        if( is_array( $argv ) ){
            foreach ($argv as $key => $value) {
                $argv[$key] = $this->superSpecialChars( $value );
            }
        } else {
            return htmlspecialchars( $argv );
        }
        return $argv;
    }
    public function superXss( $argv ){
        if( is_array( $argv ) ){
            foreach ($argv as $key => $value) {
                $argv[$key] = $this->superXss( $value );
            }
        } else {
            $matchs = array(
                // link
                '/<link(.*?)>/i' => '&lt;link$1&gt;',
                // script
                '/<script(.*?)>/i'       => '&lt;script$1&gt;',
                '/<\/script(.*?)>/i'       => '&lt;\script$1&gt;',
                // css
                '/<style(.*?)>/i'        => '&lt;style$1&gt;',
                '/<\/style(.*?)>/i'        => '&lt;\style$1&gt;',
                // 截断攻击
                '/"/'        => '&quot;',
                // 实验过，在前端页面中，<和script之间不能空格字符。丢弃下面的正则
                // 上面标签攻击只firefox中测试有效，其他浏览器有待验证
                // '/<(\s*)script(.*)>/U'       => '&lt;$1script$2&gt;',
                // '/<(\s*)style(.*)>/U'        => '&lt;$1style$2&gt;',
                // '/<(\s*)link(.*)>/U' => '&lt;$1link$2&gt;',
            );
            foreach ($matchs as $key => $value) {
                $argv = preg_replace( $key, $value, $argv );
            }
        }
        return $argv;
    }
}
