<?php

namespace cocophp\core;

/**
 * 验证器基类
 * 该类支持面向对象式组合写法
 * 如 ( new BaseMatch )->match('id->table_id')->rule('int')->info('错误提示信息')->proving( $_POST );
 * 同时，支持数组解析。如 ( new BaseMatch )->obtained( $rules )->proving( $_POST );
 * @author Jan
 * @data   2018-12-24
 */
class match{
    private $index = '';
    private $matchs = array();
    private $rules = array();
    private $valid = array(
        'int'    => '/^\d+$/',  // 纯数字
        'number' => '/^[\d\.]+$/',  // 数字，可以带小数点
        'char'   => '/^[a-zA-Z0-9]+$/', // 只能是字符(字母，数字)
        'word'   => '/^[a-zA-Z]+$/',    // 只能是字母
        'chinese'=> '/^[\x{4e00}-\x{9fa5}]+$/u', // 只能是中文
        // 'world'  => '/^[a-zA-Z0-9\u4e00-\u9fa5]+$/', // 字母，数字，中文
        'email'  => '/^[A-Za-z\d]+([-_.][A-Za-z\d]+)*@([A-Za-z\d]+[-.])+[A-Za-z\d]{2,4}$/',
        'tel'    => '/^\d{5,11}$/',
    );
    /**
     * @param
     * @return
     * @author Jan
     * @data 2018-12-24
     */
    public function __construct(){
    }
    /**
     * @match 字段反转：将标段字段反转为数据库字段
     * @param  $match array  第一个元素为表单字段，第二元素为数据库字段
     * @param  $match string 表单字段->数据库字段
     * @return obj
     * @author Jan
     * @data 2018-12-24
     */
    public function match( $match ) {
        if( is_string( $match ) ) {
            $match = explode( '->', $match );
        }
        if( !isset( $match[1] ) ) {
            $match[1] = $match[0];
        }
        $this->index = $match[0];
        // 手动塞入所有默认值。
        $this->rules [ $match[0] ] = array();
        $this->rules [ $match[0] ]['required'] = false;     // 必须验证的字段
        $this->rules [ $match[0] ]['pretreat'] = false;     // 预处理函数
        $this->rules [ $match[0] ]['refuse']   = false;     // 拒绝接受
        $this->rules [ $match[0] ]['match']    = $match[1]; // key映射规则
        $this->rules [ $match[0] ]['info']     = '';        // 错误提示信息
        $this->rules [ $match[0] ]['rule']     = [];        // 验证规则
        $this->rules [ $match[0] ]['sever']    = 'default'; // 数组分流
        return $this;
    }
    /**
     * @rule 字段规则：
     * @param  $rules  array   验证规则：可以是自己的正则也可以是$this->valid定义过的正则别名
     * @param  $rules  string  验证规则：同上面的数组，只不过string时为单条规则
     * @param  $rules  func    验证规则：匿名函数，支持一个参数(多参数可以考虑数组传入);
     * @param  $params string  函数实参：$rules为匿名函数时，所传递的实参
     * @param  $params array   函数实参：$rules为匿名函数时，所传递的实参
     * @return obj
     * @author Jan
     * @data 2018-12-24
     */
    public function rule( $rules, $params = array() ) {
        if( is_object( $rules ) ) {
            $this->rules[ $this->index ]['rule'][] = array( $rules, $params );
        }
        if( is_string( $rules ) ) {
            $rules = array( $rules );
        }
        if( is_array( $rules ) ) {
            foreach ( $rules as $rule ) {
                $temp = strtolower( $rule );
                // 替换成相应的正则
                if( isset( $this->valid[ $temp ] ) ) {
                    $temp = $this->valid[ $temp ];
                }
                // 这里还要处理一下，对于length<20的情况。代码有点low。
                // 这里不能替换成相关的正则。因为，中文往往占用更多的字符串长度
                // 没办法，暂时移植到下面的验证中用 iconv_strlen 处理

                // if( strpos( $temp,'length' ) == 0 ) {
                //     if( strpos( $temp, '<' ) ) {
                //         $temp = explode( '<', $temp );
                //         $temp = '/^\w{0,'. ($temp[1]-1) .'}$/';
                //     }
                //     if( strpos( $temp, '>' ) ) {
                //         $temp = explode( '>', $temp );
                //         $temp = '/^\w{'. ($temp[1]+1) .',}$/';
                //     }
                //     if( strpos( $temp, '=' ) ) {
                //         $temp = explode( '=', $temp );
                //         $temp = '/^\w{'. $temp[1] .','. $temp[1] .'}$/';
                //     }
                // }
                $this->rules[ $this->index ]['rule'][] = $temp;
            }
        }
        return $this;
    }
    /**
     * @required 字段是否为必须验证。
     * @param
     * @return obj
     * @author Jan
     * @data 2018-12-24
     */
    public function required() {
        $this->rules[ $this->index ]['required'] = true;
        return $this;
    }
    /**
     * @refuse 字段是否为必须验证。
     * @param
     * @return obj
     * @author Jan
     * @data 2018-12-24
     */
    public function refuse() {
        $this->rules[ $this->index ]['refuse'] = true;
        return $this;
    }
    /**
     * @info 错误时的提示信息
     * @param
     * @return obj
     * @author Jan
     * @data 2018-12-24
     */
    public function info( $errorInfo ) {
        $this->rules[ $this->index ]['info'] = $errorInfo;
        return $this;
    }
    /**
     * @pretreat 预处理 该函数接收一个匿名函数，预处理所需验证字段。
     *                 请注意，匿名函数最终必须要将处理结果转换成字符串返回。
     * @param  $pretreat function 预处理的匿名函数。
     * @return obj
     * @author Jan
     * @data 2018-12-25
     */
    public function pretreat( $pretreat ) {
        $this->rules[ $this->index ]['pretreat'] = $pretreat;
        return $this;
    }
    /**
     * @pretreat 预处理 该函数接收一个匿名函数，预处理所需验证字段。
     *                 请注意，匿名函数最终必须要将处理结果转换成字符串返回。
     * @param  $pretreat function 预处理的匿名函数。
     * @return obj
     * @author Jan
     * @data 2018-12-26
     */
    public function sever( $sever ) {
        $this->rules[ $this->index ]['sever'] = $sever;
        return $this;
    }
    /**
     * @default 默认值。若所以给数据中不存在，则数据将修整为该函数提供的数值。
     * @param  $pretreat function 预处理的匿名函数。
     * @return obj
     * @author Jan
     * @data 2018-12-25
     */
    public function default( $date ) {
        $this->rules[ $this->index ]['default'] = $date;
        return $this;
    }
    /**
     * @obtained 该函数将从数据中得到一个完整的验证器。
     * @param  $arr array 验证器
     * @return obj
     * @author Jan
     * @data 2018-12-25
     */
    public function obtained( &$arr ) {

        foreach ($arr as $columns => $rule ) {
            $this->match( $columns );
            if( isset( $rule['match'] ) ) {
                $this->rules [ $columns ]['match'] = $rule['match'];
            }
            if( isset( $rule['info'] ) ) {
                $this->info( $rule['info'] );
            }
            if( isset( $rule['rule'] ) ) {
                if( isset( $rule['rule'][1]) and is_object($rule['rule'][0]) ) {
                    $this->rule( $rule['rule'][0], $rule['rule'][1] );
                } else {
                    $this->rule( $rule['rule'] );
                }
            }
            if( isset( $rule['required'] ) ) {
                $this->required();
            }
            if( isset( $rule['default'] ) ) {
                $this->default( $rule['default'] );
            }
            if( isset( $rule['pretreat'] ) ) {
                $this->pretreat( $rule['pretreat'] );
            }
            if( isset( $rule['sever'] ) ) {
                $this->sever( $rule['sever'] );
            }
        }
        return $this;
    }
    /**
     * @proving 自动验证器。
     * @param  $params array 要验证的数据
     * @return bool
     * @return 成功时，将通过引用的方式，将 $params 修改为matchs数据。
     * @return 失败时，将通过引用的方式，将 $params 修改为错误信息。
     * @author Jan
     * @data 2018-12-24
     */
    public function proving( &$params ) {
        // 此处只处理正则和匿名函数。
        $temp   = $params;
        $params = array();
        foreach ($this->rules as $key => $rule) {
            // 如果字段设置 refuse ，则只接受default，若不存在，则回跳过
            if( $rule['refuse'] ) {
                if( isset( $rule['default'] ) ) {
                    $params[ $rule['sever'] ][ $rule['match'] ] = $rule['default'];
                }
                continue;
            }
            // 如果字段不存在，则跳过验证。
            if( !isset( $temp[$key] ) ) {
                // 首先查看是否需要默认值
                // 对于提供默认值的字段，将不在验证。
                if( isset( $rule['default'] ) ) {
                    $params[ $rule['sever'] ][ $rule['match'] ] = $rule['default'];
                    continue;
                }
                // 当然，如果字段是必须验证字段但没有默认值，则会直接抛失败信息。
                if( $rule['required'] ) {
                    $params = array( $key, $rule['info'] );
                    return false;
                }
                // 对于数据中不存在的验证规则，这里不在验证。
                // 既没有默认值也不是必须验证字段。
                continue;
            }
            // 字段存在。那么肯定是要验证的。
            // 首先查看是否需要预处理
            if( $rule['pretreat'] !== false ) {
                $temp[$key] = $rule['pretreat']( $temp[$key] );
            }
            // 根据规则，验证相关字段。
            foreach ($rule['rule'] as $valid) {
                if( is_object( $valid[0] ) ) {
                    // 执行函数。
                    if( isset( $valid[1] ) ) {
                        $flag = $valid[0]($valid[1]);
                    } else {
                        $flag = $valid[0]();
                    }
                    if( !$flag ){
                        $params = array( $key, $rule['info'] );
                        return false;
                    }
                    continue;
                }
                if( !is_string( $valid ) ) {
                    $params = array( $key, '参数只接受字符数据' );
                    return false;
                }
                // 处理 length<20 这种情况
                if( strpos( $valid, 'length<' ) === 0 ) {
                    $t = explode( 'length<', $valid );
                    if( iconv_strlen( $temp[$key],'utf8' ) >= $t[1] ) {
                        $params = array( $key, $rule['info'] );
                        return false;
                    }
                    continue;
                }
                // 处理 length>20 这种情况
                if( strpos( $valid, 'length>' ) === 0 ) {
                    $t = explode( 'length>', $valid );
                    if( iconv_strlen( $temp[$key],'utf8' ) <= $t[1] ) {
                        $params = array( $key, $rule['info'] );
                        return false;
                    }
                    continue;
                }
                // 处理 length=20 这种情况
                if( strpos( $valid, 'length=' ) === 0 ) {
                    $t = explode( 'length=', $valid );
                    if( iconv_strlen( $temp[$key],'utf8' ) != $t[1] ) {
                        $params = array( $key, $rule['info'] );
                        return false;
                    }
                    continue;
                }
                // 最后，只剩下正则处理
                if( !preg_match( $valid,$temp[$key] ) ) {
                    $params = array( $key, $rule['info'] );
                    return false;
                }
            }

            // 数据反向回执
            $params[ $rule['sever'] ][ $rule['match'] ] = $temp[ $key ];
        }
        return true;
    }
}
