<?php
namespace core\lib;
/**
 * @author Jan
 * @date 2019年07月04日17:49:24
 * 主要用于简化此场景下的curl请求.从标准库的curl中拿下来简单改造了一下.
 * 本质上就是一个curl请求
 */
class Curls{
    private $url;
    private $params;
    private $body;
    private $header;
    private $mode;
    private $errors;
    private $response;
    function __construct( $url = '' ){
        $this->url    = $url;
        $this->params = array();
        $this->body   = array();
        $this->header = array();
        $this->errors = '';
        $this->mode   = 'GET';
        $this->response= '';
    }
    function setUrl( $url ){
        $this->url = $url;
        return $this;
    }
    function setParams( $params ){
        $this->params = array_merge( $this->params, $params );
        return $this;
    }
    function setBody( $body ){
        $this->body = array_merge( $this->body, $body );
        return $this;
    }
    function setHeader( $header ){
        $this->header[] = $header;
        return $this;
    }
    function getErrors(){
        return $this->errors;
    }
    function getResponse(){
        return $this->response;
    }
    function modeIsGet(){
        $this->mode = 'GET';
        return $this;
    }
    function modeIsPost(){
        $this->mode = 'POST';
        return $this;
    }
    function requestIsFrom(){
        foreach ( $this->body as $key => $value) {
            $formData += "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data;";
            $formData += "name=\"$key\"\r\n\r\n$value\r\n";
        }
        $this->body = $formData + "------WebKitFormBoundary7MA4YWxkTrZu0gW--";
        $this->setHeader( 'content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW' );
        return $this->request();
    }
    function requestIsJson(){
        $this->setHeader( 'Content-Type: application/json' );
        $this->body = json_encode( $this->body );
        return $this->request();
    }
    function request(){
        if( empty( $this->params ) ){
            $this->params = '';
        }
        if( !empty( $this->params ) and is_array( $this->params ) ){
            $this->params = '?' . http_build_query( $this->params );
        }
        $curl = curl_init();
        curl_setopt_array( $curl, array(
                CURLOPT_URL => $this->url . $this->params,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 3,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $this->mode,
                CURLOPT_POSTFIELDS => $this->body,
                CURLOPT_HTTPHEADER => $this->header,
        ) );
        // 关闭https证书检查
        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 );

        $this->response = curl_exec($curl);
        $this->errors   = curl_error($curl);
        
        $this->url    = '';
        $this->params = array();
        $this->body   = array();
        $this->header = array();
        $this->mode   = 'GET';
        curl_close($curl);
        return empty( $this->errors );
    }
}
