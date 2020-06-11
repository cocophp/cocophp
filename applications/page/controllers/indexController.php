<?php
namespace applications\page\controllers;

use applications\AuthBase;
use core\Template;
/**
 *
 */
class indexController{
    public function indexAction(){
        return "hello,world";
        return file_get_contents( './index.html' );
    }
    public function pageAction(){
        $this->a = [
            1,2,3,4
        ];
        return Template::view( $this, "index/index" );
    }
}
