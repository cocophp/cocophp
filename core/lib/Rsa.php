<?php
namespace core\lib;

use core\Config;
/**
 *
 */
class Rsa{
    /**
     * rsa 加密
     * @param  str   encrypt string;
     * @return boole the encryption results
     */
    static public function encrypt( &$str ){
        $key = openssl_pkey_get_public( Config::get( 'rsa.publicKey' ) );
        if (!$key) {
            $str = "This public key is not available";
            return false;
        }
        // $return_en = openssl_public_encrypt( $str, $str, $key );
        if ( !openssl_public_encrypt( $str, $str, $key ) ) {
            $str = "encrypt failed";
            return false;
        }
        $str = base64_encode( $str );
        return true;
    }
    /**
     * rsa 解密
     * @param  str   decrypt string;
     * @return boole the decryption results
     */
    static public function decrypt( &$res ){
        $key = openssl_pkey_get_private( Config::get( 'rsa.privateKey' ) );
        if (!$key) {
            $res = "This private key is not available";
            return false;
        }
        if( !openssl_private_decrypt( base64_decode( $res ), $res, $key ) ) {
            $res = "RSA decrypt failed";
            return false;
        }
        return true;
    }
}
