<?php
namespace applications\api\params;

use core\Match;
use applications\api\tables\{ cocoSysUser, cocoSysRole };
/**
 *
 */
class userParams{
    static public function login( &$params ){
        $m = new Match();
        cocoSysUser::userName( $m->match('user') )
                    ->required()
                    ->info( '请输入用户名，长度为6-255。')
                    ->rule( 'length > 5' );
        cocoSysUser::password( $m->match('password') )
                    ->required()
                    ->info( '请输入密码，长度为6-255。')
                    ->rule( 'length > 5' );

        return $m->proving( $params );
    }
}
