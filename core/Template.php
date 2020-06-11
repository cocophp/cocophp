<?php
namespace core;
/**
 * 模板类引擎
 * User: Jan
 * Date: 2018/07/05
 * __construct($_tplFile) // 需要打开的模板文件路径
 * compile($_cacheFile)   // 需要生成的缓存文件路径
 * 声明：一个很low的模板引擎。暂时能用就好，希望某天大神帮着改造
 */
class Template{
    private static $_rules = [
        // if系列
        '/\{\{[\s]{0,}if(.*)\}\}/U'                => '<?php if$1{?>',       // if
        '/\{\{[\s]{0,}switch(.*)\}\}/U'            => '<?php switch$1{?>',   // switch
        '/\{\{[\s]{0,}else[\s]{0,}\}\}/U'          => '<?php }else{?>',      // else
        '/\{\{[\s]{0,}else[\s]{0,}if(.*)\}\}/U'    => '<?php }else if$1{?>', // else if
        '/\{\{[\s]{0,}end[\s]{0,}(if|switch)[\s]{0,}\}\}/U'=> '<?php }?>',           // endif end switch

        // 循环系列
        '/\{\{[\s]{0,}foreach(.*)\}\}/U'          => '<?php foreach$1:?>',  // foreach
        '/\{\{[\s]{0,}(for|while)(.*)\}\}/U'      => '<?php $1 $2{?>',      // for while
        '/\{\{[\s]{0,}do[\s]{0,}\{[\s]{0,}\}\}/U' => '<?php do{?>',         // do...while
        '/\{\{[\s]{0,}end[\s]{0,}do[\s]{0,}while(.*)\}\}/U' => '<?php }while$1;?>',// do...while

        '/\{\{[\s]{0,}endforeach[\s]{0,}\}\}/U'             => '<?php endforeach;?>', // endforeach
        '/\{\{[\s]{0,}end[\s]{0,}(for|while)[\s]{0,}\}\}/U' => '<?php } ?>',          // endfor endwhile

        // 通配系列
        '/\{\{[\s]{0,}run(.*)\}\}/U'                   => '<?php $1;?>',          // 正常代码
        '/\{\{(.*)\}\}/U'                              => '<?=$1;?>',            // 普通常量或函数
    ];
    private static $_include = [
        // include 系列
        '/\{\{[\s]{0,}include[\s]{1,}(.*)\}\}/U'       => '<?php include $1;?>',
        '/\{\{[\s]{0,}include_once[\s]{0,}(.*)\}\}/U'  => '<?php include_once $1;?>',
        '/\{\{[\s]{0,}require[\s]{1,}(.*)\}\}/U'       => '<?php require $1;?>',
        '/\{\{[\s]{0,}require_once[\s]{0,}(.*)\}\}/U'  => '<?php require_once $1;?>',
    ];
    private static $route = [];
    private static $tpl = '';
    public static function view( $data, $template = '' ){
        $route = Config::get( 'system.request' );
        // 解析modules
        $route[ 'modules' ] = str_replace( '\applications\\', '', $route[ 'modules' ] );
        // 解析controller
        $route[ 'contros' ] = str_replace( 'controllers\\', '', $route[ 'contros' ] );
        $route[ 'contros' ] = str_replace( 'Controller', '', $route[ 'contros' ] );
        // 解析action。
        $route[ 'action' ]  = str_replace( 'Action', '', $route[ 'action' ] );
        self::$route = $route;
        $templatePath = self::path( $template );
        $t = str_replace( '.', '', $templatePath );
        $t = '../runtime/' . str_replace( '/', '.', $t );
        // 查看缓存文件是否存。
        if( file_exists( $t ) and filemtime( $t ) > filemtime( $templatePath ) ){
            include $t;
            return '';
        }
        //生成编译文件
        if( !@self::$tpl = file_get_contents( $templatePath ) ){
            throw new \Exception( $templatePath . " not found", 1 );
        }
        self::replace();
        // 将文件写入缓存
        if( !@file_put_contents( $t, self::$tpl ) ){
            throw new \Exception( $templatePath . " write false", 1 );
        }
        include $t;
        return '';
    }
    private static function path( $p ){
        // 解析template
        $templatePath = [];
        $route = self::$route;
        if( $p ){
            $templatePath = explode( '/', $p );
        }
        if( !empty( $templatePath[ count($templatePath) - 1 ] ) ){
            $route[ 'action' ] = $templatePath[ count($templatePath) - 1 ];
            unset( $templatePath[ count($templatePath) - 1 ] );
        }
        if( !empty( $templatePath[ count($templatePath) - 1 ] ) ){
            $route[ 'contros' ] = $templatePath[ count($templatePath) - 1 ];
            unset( $templatePath[ count($templatePath) - 1 ] );
        }
        if( count( $templatePath ) ){
            $route[ 'modules' ] = implode( "/", $templatePath );
        }
        $route[ 'contros' ] = 'views/' . $route[ 'contros' ];
        return Config::get( 'system.default.applicationPath' ) . '/' . implode( '/', $route ) . ".php";
    }
    private static function replace(){
        //解析生成的模板
        self::includeReplace();
        foreach ( self::$_rules as $key => $value ) {
            if( preg_match( $key, self::$tpl ) ){
                self::$tpl = preg_replace( $key,$value, self::$tpl );
            }
        }
    }
    // 递归替换模板内子页面
    private static function includeReplace(){
        foreach ( self::$_include as $key => $value ) {
            $rule = array();
            if( preg_match( $key, self::$tpl, $rule ) ){
                // 糟糕。首先模板生成之前，代码是未曾奔跑的。
                // 如果出现 include APP./Test/View/Index/test_include.php 将无法解析。
                // 貌似上面的用法，也是很让用户难受。so,决定自己解析和定义include规则。
                // emmm，就根据当前 modules 来确定和猜测用户试图加载的子模板吧。
                // 替换调非法字符 ?:*"'()
                $rule = preg_replace(  '/[ \?|\:|\*|\"|\'|\(|\)]{1,}/', '', $rule[1] );
                $file = self::path( $rule );
                // self::$files[] = $file;
                if( !@$t = file_get_contents( $file ) ){
                    throw new \Exception( $file . " not found", 1 );
                }
                self::$tpl = preg_replace( $key, file_get_contents($file), self::$tpl );
                self::includeReplace();
            }
        }
    }
}
