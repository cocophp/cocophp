<?php
namespace console\index\controllers;

use core\Db;
use core\Cache;
/**
 *
 */
class testController{
    /**
     * test   php console.php test/test
     */
    function tesAction(){
        var_dump( json_encode( [
            'task_id' => 1,
            'path' => '/home/mosh/system3/wwwroot/api/vhost/app-edm/upload/1.csv',
            'modify_main_id' => 50055,
            'service_id' => 33
        ] ) );

        var_dump( json_encode( [
            'task_id' =>  1,
            'member'  => -1,
            'staff'   => -1,
            'service_id' => 145,
            'modify_main_id' => 5265,
        ] ) );
    }
}
