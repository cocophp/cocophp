<?php

use cocophp\core\route;
use cocophp\core\config;
use cocophp\std\error;

const route     = "route.php";
const config    = "config.php";
const functions = "functions.php";
/**
 *  该类将动作解析成对应的app层控制器。
 */
class coco{
    public function running(){
        try {
            if( file_exists( union.functions ) ){
                require_once union.functions;
            }
            if( !route::loading( union.route ) ){
                throw new \Exception("Route replace false!!", 1);
            }
            config::loading( union.config );
            $module = applications;
            // 加载所有层级的 functions,route,config
            foreach ( route::$module as $m ) {
                $module = $module . $m . '/' ;
                if( file_exists( $module . functions ) ){
                    require_once $module . functions;
                }
                if( !route::loading( $module . route ) ){
                    throw new \Exception("Route replace false!!", 1);
                }
                config::loading( $module . config );
            }
            $module = route::$nameContro;
            $action = route::$action;
            return ( new $module() )->$action();
        } catch (\Exception $e) {
            return ( new error() )->show( $e );
        }
    }
}
