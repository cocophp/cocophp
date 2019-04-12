<?php
function view( $view = '', $data = array() ){
    if( $view == '' ){
        $view = cocophp\core\route::$action;
    }
    $path = cocophp\core\route::$pathModule . 'view/' . $view . '.php';
    if( !file_exists( $path ) ){
        return redirect404();
    }
    include $path;

}
function redirect404(){
    return '<html><head><meta charset="utf-8"><title>您所查看的内容不存在</title></head><body><h1>404</h1>
                <h2>Page Not found</h2>
                <p>您查看的内容已经不存在</p>
            </body>
            <style media="screen">
            body{text-align: center;}
            h1{font-size: 200px;line-height: 100px;margin: 100px 0 0;}
            h2{font-size: 100px;line-height: 25px;margin: 100px 0 0;}
            p{ margin: 100px;color: red;}</style></html>';
}
