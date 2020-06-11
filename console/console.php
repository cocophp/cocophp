<?php

// 当前运行环境 用于区分 开发和线上的不同配置文件。
$env  = 'test';
// 框架路径 一般来说，不会动。
$core = '../core';
// 配置文件路径。
$cfg  = '../config';
// 这里applications的路径，放在了配置文件中。 config/system.php

// 加载自动注册机和配置文件。这两个确实只能手动包含。因为Autoload中用到了Config
include "{$core}/Autoload.php";
include "{$core}/Config.php";
// spl_autoload_register( ['Autoload','customizedRoute'] ); // 定制的加载规则。
spl_autoload_register( ['Autoload','appRoute'] );     // 正常的加载规则。
spl_autoload_register( ['Autoload','consoleRoute'] ); // cli的加载规则。
spl_autoload_register( ['Autoload','coreRoute'] );    // 正常的加载规则。

// 加载配置文件。此处可以根据自身项目定制拆分。
core\Config::init(
    require "$cfg/$env/system.php",
    require "$cfg/$env/applications.php",
    require "$cfg/$env/rsa.php",
    require "$cfg/$env/database.php"
);
// 重写掉 system.env 和 system.corePath
core\Config::set( "system.env", $env );
core\Config::set( "system.corePath", $core );
core\Config::set( 'system.mode', 'console' );

if( !isset($_SERVER['argv'][1]) ){
    exit( "必须指定一个路由,例： php console.php module/contro/action k1=v1 k2=v2\n" );
}
core\Route::analysis( $_SERVER['argv'][1] );
foreach ( $_SERVER['argv'] as $value ) {
    $t = explode( '=', $value );
    if( isset( $t[1] ) ){
        $_GET[ $t[0] ] = $t[1];
    }
}
echo core\Cores::execute();
