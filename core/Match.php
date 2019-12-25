<?php
namespace core;
/**
 * 验证器
 * @author Jan
 * @data   2018-12-24
 */
class match{
    private $currentIndex = '';
    private $allMatchs = array();
    static public $verifys = [
        'int'    => '/^[-]{0,1}\d*$/',                                // 纯数字
        'float'  => '/^[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?$/',     // 数字，可以带小数点
        'char'   => '/^[a-zA-Z0-9]*$/',                               // 只能是字符(字母，数字)
        'ukChar' => '/^[a-zA-Z]*$/',                                  // 只能是字母
        'cnChar' => '/^[\x{4e00}-\x{9fa5}]*$/uisU',                   // 只能是中文
        'word'   => '/^[a-zA-Z0-9\x{4e00}-\x{9fa5}]*$/uisU',          // 字母，数字，中文
        // email 支持 nickName@ip 但是这里并不能校验ip是否正确。可参阅 https://www.regular-expressions.info/email.html
        'email'  => "/^(?=[a-zA-Z0-9@.!#$%&'*+\/=?^_`{|}~-]{6,254}$)(?=[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]{1,64}@)[a-zA-Z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:(?=[a-zA-Z0-9-]{1,63}\.)[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+(?=[a-zA-Z0-9-]{1,63}\z)[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/",
        'ip'     => "/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/",
        'tel'    => '/^\d{5,11}$/',
    ];
    static function verify( $data, $rule ){
        if( strpos( $rule , 'length') === 0 ){
            $rule = explode( " ", $rule );
            // $func = 'preg_match_all';
            switch( $rule[1] ){
                case "<"  : $tmp = sprintf( "/^.{0,%d}$/us", $rule[2]-1 ); break;
                case ">"  : $tmp = sprintf( "/^.{%d,}$/us", $rule[2]+1 ); break;
                case "<=" : $tmp = sprintf( "/^.{0,%d}$/us", $rule[2] ); break;
                case ">=" : $tmp = sprintf( "/^.{%d,}$/us", $rule[2] ); break;
                case "between" : $tmp = sprintf( "/^.{%d,%d}$/us", $rule[2], $rule[3]  ); break;
            }
            return (bool)preg_match( $tmp, $data );
        }
        if ( strpos( $rule , 'values') === 0 ) {
            $rule = explode( " ", $rule );
            $func = function( $v, $min, $max ){
                if( $min === false ) return $v <= $max;
                if( $max === false ) return $v >= $min;
                if( $v<$min or $v>$max ) return false;
                return true;
            };
            switch( $rule[1] ){
                case "<" : return $func( $data, false, $rule[2]-1 );
                case ">" : return $func( $data, $rule[2]+1, false );
                case "<=" : return $func( $data, false,$rule[2] );
                case ">=" : return $func( $data, $rule[2], false );
                case "between" : return $func( $data, $rule[2], $rule[3] );
                default: return false;
            }
        }
        return (bool)preg_match( match::$verifys[$rule], $data );
    }
    public function match( $in, $out = '' ){
        $in = explode( '->', str_replace( ' ', '', $in ) );
        if( isset( $in[1] ) ) {
            $out = $in[1];
        } else {
            if( $out == '' ){
                $out = $in[0];
            }
        }
        $this->currentIndex = $in[0];
        if( !isset( $this->allMatchs[ $in[0] ] ) ){
            $this->allMatchs [ $in[0] ] = array();
            $this->allMatchs [ $in[0] ]['required']    = false;     // 必须验证的字段
            $this->allMatchs [ $in[0] ]['matchBefore'] = [];        // 预处理函数
            $this->allMatchs [ $in[0] ]['matchAfter']  = [];        // 预处理函数
            $this->allMatchs [ $in[0] ]['refuse']      = false;     // 拒绝接受
            $this->allMatchs [ $in[0] ]['match']       = $out;      // key映射规则
            $this->allMatchs [ $in[0] ]['info']        = '';        // 错误提示信息
            $this->allMatchs [ $in[0] ]['code']        = "1000";
            $this->allMatchs [ $in[0] ]['rule']        = [];        // 验证规则
            $this->allMatchs [ $in[0] ]['sever']       = []; // 数组分流
            // $this->allMatchs [ $in[0] ]['sever']       = [ 'default' => 'default' ]; // 数组分流
        }
        return $this;
    }
    public function defaultValue( $default ){
        $this->allMatchs [ $this->currentIndex ][ 'default' ] = $default;
        return $this;
    }
    public function required( $required = true ){
        $this->allMatchs [ $this->currentIndex ][ 'required' ] = $required;
        return $this;
    }
    public function refuse( $refuse = true ){
        $this->allMatchs [ $this->currentIndex ][ 'refuse' ] = $refuse;
        return $this;
    }
    public function contrary( $contrary = true ){
        $lastRuleIndex = count( $this->allMatchs[ $this->currentIndex ][ 'rule'] );
        if( $lastRuleIndex != 0 ){
            $this->allMatchs [ $this->currentIndex ][ 'rule' ][ $lastRuleIndex-1 ][ 'contrary' ] = $contrary;
        }
        return $this;
    }
    public function contraryRule(){
        return call_user_func_array( [$this, 'rule'], func_get_args() )->contrary();
    }
    public function rule(){
        $argv = func_get_args();
        if( empty($argv) ){
            return $this;
        }
        $rule  = $argv[0];
        if( is_object( $rule ) or function_exists( $rule ) ){
            unset( $argv[0] );
            $this->allMatchs [ $this->currentIndex ][ 'rule' ][] = [
                'func'     => $rule,
                'argv'     => $argv,
                'contrary' => false,
            ];
            return $this;
        }
        foreach ( $argv as $key => $preg ) {
            $rule = 'preg_match';
            $args = array();
            if( isset( match::$verifys[$preg] ) ){
                $args = [ match::$verifys[$preg], 'this' ];
            } else if( strpos( $preg , 'length') === 0 ){
                $preg = explode( " ", $preg );
                // $func = 'preg_match_all';
                switch( $preg[1] ){
                    case "<"  : $args[] = sprintf( "/^.{0,%d}$/us", $preg[2]-1 ); break;
                    case ">"  : $args[] = sprintf( "/^.{%d,}$/us", $preg[2]+1 ); break;
                    case "<=" : $args[] = sprintf( "/^.{0,%d}$/us", $preg[2] ); break;
                    case ">=" : $args[] = sprintf( "/^.{%d,}$/us", $preg[2] ); break;
                    case "between" : $args[] = sprintf( "/^.{%d,%d}$/us", $preg[2], $preg[3]  ); break;
                    default : $args[] = '/^.{999999999,}$/';
                }
                $args[] = 'this';
            } else if ( strpos( $preg , 'values') === 0 ) {
                $preg = explode( " ", $preg );
                $rule = function( $v, $min, $max ){
                    if( $min === false ) return $v <= $max;
                    if( $max === false ) return $v >= $min;
                    if( $v<$min or $v>$max ) return false;
                    return true;
                };
                switch( $preg[1] ){
                    case "<" : $args = [ 'this', false, $preg[2]-1 ]; break;
                    case ">" : $args = [ 'this', $preg[2]+1, false ]; break;
                    case "<=" : $args = [ 'this', false,$preg[2] ];   break;
                    case ">=" : $args = [ 'this', $preg[2], false ];  break;
                    case "between" : $args = [ 'this', $preg[2], $preg[3] ];  break;
                    default : $args = [ 0, false, 1 ];
                }
            } else {
                $args = [ $preg, 'this' ];
            }
            $this->allMatchs [ $this->currentIndex ][ 'rule' ][] = [
                'func'     => $rule,
                'argv'     => $args,
                'contrary' => false,
            ];
        }
        return $this;
    }
    public function matchBefore(){
        $argv = func_get_args();
        if( empty($argv) ){
            return $this;
        }
        $func  = $argv[0];
        unset( $argv[0] );

        $this->allMatchs [ $this->currentIndex ]['matchBefore'][] = [
            'func' => $func,
            'argv' => $argv,
        ];
        return $this;
    }
    public function matchAfter(){
        $argv = func_get_args();
        if( empty($argv) ){
            return $this;
        }
        $func  = $argv[0];
        unset( $argv[0] );

        $this->allMatchs [ $this->currentIndex ]['matchAfter'][] = [
            'func' => $func,
            'argv' => $argv,
        ];
        return $this;
    }
    public function sever( ){
        $this->allMatchs [ $this->currentIndex ][ 'sever' ]
            = array_merge( $this->allMatchs [ $this->currentIndex ][ 'sever' ], func_get_args() );

        if( isset( $this->allMatchs [ $this->currentIndex ][ 'sever' ][ 'default' ] ) ){
            unset( $this->allMatchs [ $this->currentIndex ][ 'sever' ][ 'default' ] );
        }
        return $this;
    }
    public function info( $info ){
        $this->allMatchs [ $this->currentIndex ][ 'info' ] = $info;
        return $this;
    }
    public function code( $code ){
        $this->allMatchs [ $this->currentIndex ][ 'code' ] = $code;
        return $this;
    }
    public function proving( &$res ){
        $params = $res;
        $res    = array();
        foreach ( $this->allMatchs as $part => $rule ) {
            if( $rule['refuse'] ) {
                if( isset( $rule['default'] ) ) {
                    $this->setSever( $res, $rule['sever'], $rule['match'], $rule['default'] );
                }
                continue;
            }
            if( !isset( $params[ $part ] ) ) {
                if( $rule['required'] ) {
                    $res = [ 'field'=>$part, 'msg'=>$rule[ 'info' ], 'code' =>$rule['code'] ];
                    return false;
                }
                if( isset( $rule['default'] ) ) {
                    $this->setSever( $res, $rule['sever'], $rule['match'], $rule['default'] );
                }
                continue;
            }
            $params[ $part ] = $this->runMatchAction( $rule[ 'matchBefore' ], $params[ $part ] );
            if( !$this->runRuleAction( $rule[ 'rule' ], $params[ $part ] ) ){
                $res = [ 'field'=>$part, 'msg'=>$rule[ 'info' ], 'code' =>$rule['code'] ];
                return false;
            }
            $params[ $part ] = $this->runMatchAction( $rule[ 'matchAfter' ], $params[ $part ] );
            if( $params[ $part ] === false ){
                continue;
            }
            $this->setSever( $res, $rule['sever'], $rule['match'], $params[ $part ] );
        }
        return true;
    }
    private function setSever( &$res, &$sever, &$key, &$value ){
        if( empty( $sever ) ){
            $res[ $key ] = $value;
            return;
        }
        foreach ( $sever as $k => $v ) {
            $res[ $v ][ $key ] = $value;
        }
    }
    private function runRuleAction( &$allAction, &$values ){
        foreach ( $allAction as $action ) {
            foreach ($action['argv'] as $key => $value) {
                if( $value == 'this' ){
                    $action['argv'][ $key ] = $values;
                }
            }
            $res = call_user_func_array( $action['func'], $action[ 'argv' ] );
            if( $action['contrary'] ){
                $res = !$res;
            }
            if( !$res ){
                return false;
            }
        }
        return true;
    }
    private function runMatchAction( &$allAction, &$value ){
        $res = $value;
        foreach ( $allAction as $action ) {
            foreach ($action['argv'] as $key => $value) {
                if( $value == 'this' ){
                    $action['argv'][ $key ] = $res;
                }
            }
            $res = call_user_func_array( $action['func'], $action[ 'argv' ] );
            // if( !$res ){
            //     return false;
            // }
        }
        return $res;
    }
}
