<?php
namespace core;

use core\Config;
/**
 * 标准输出。 您也可以尝试定制其他
 */
class Resule{
    public $status;
    public $message;
    public $data;
    public $debug;
    function __construct(){
        $this->status = true;
        $this->message= "";
        $this->data   = [];
        $this->debug  = [];
    }
    public function success( $data = [] ){
        $this->status = true;
        $this->data   = $data;
        $this->fmtThis();
    }
    public function failed( $message = "" ){
        $this->status  = false;
        $this->message = $message;
        $this->fmtThis();
    }
    public function fmtThis(){
        $env = Config::get( 'system.env' );
        $res = [
            "status"  => $this->status,
            "message" => $this->message,
            "data"    => $this->data,
            "debug"   => $this->debug,
        ];
        if( $env != 'prod' ){
            $res['debug'] = $this->debug;
        }
        echo json_encode( $res );
        exit;
    }
}
