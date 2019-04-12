<?php

namespace cocophp\std;

/**
 * 错误处理类
 */

class error{
    function show( $e ){
        return $e . "\n";
        return " class error is building, please wait!!\n"
            .  $e
            .  "\n";
    }
    static public function buildError( $info ){
        return array( 10000, $info[1] );
    }
}
