<?php
namespace core;

/**
 *
 */
class Console{
    // 反射 console/controllers下面全部文件,生成函数列表
    static public function show(){
        echo "必须指定一个命令路由\n";
        exit;
    }
}
