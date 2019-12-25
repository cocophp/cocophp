<?php
namespace applications\index\controllers;

use applications\AuthBase;
use core\Db;
use applications\template\params\paramsBuilder;
use applications\template\models\select;
use tableField\publicField;
use tableField\edmTemplate;
/**
 *
 */
class indexController extends AuthBase{
    public function indexAction(){
        return "cocophp: hello,phper!!";
    }
}
