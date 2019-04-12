<?php
namespace applications\index\controller;

use cocophp\core\config;
use cocophp\core\params;
use cocophp\db\factory;
use cocophp\db\DB;
/**
 *
 */
class index {
    function index(){
        return view( 'welcome' );
    }
}
