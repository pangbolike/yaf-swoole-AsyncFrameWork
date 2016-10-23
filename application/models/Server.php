<?php
/**
 * @name ServerModel
 * @desc ServerModel 适配server层能力
 * @author pangbolike
 */

require_once(dirname(__FILE__).'/../library/config.php');

define('DEFAULT_COOKIE_DOMAIN', 'jt.pangbolike.com');

class ServerModel {

    public function __construct() {
        
    }

    public static function getQueryArr(){
        return HttpServer::$get;
    }

    public static function getQuery($key){
        return HttpServer::$get[$key];
    }

    public static function getRawPostData(){
        return HttpServer::$rawConent;
    }

    public static function getPostJson(){
        return json_decode(HttpServer::$rawConent, true);
    }

    public static function setCookie($key, $value = '', $domain  = DEFAULT_COOKIE_DOMAIN, $expire = 0 , $path = '/', $secure = false , $httponly = false){
        if (null != HttpServer::$rsp){
            HttpServer::$rsp->cookie($key, $value, $expire , $path, $domain, $secure , $httponly);
        }
    }

    public static function getCookie($key){
        return HttpServer::$cookie[$key];
    }

    public static function getCookieArr(){
        return HttpServer::$cookie;
    }

    public static function setHeader($key, $value){
        HttpServer::$rsp->header($key, $value);
    }

    public static function getRemoteIp(){
        return HttpServer::$header['x-real-ip'];
    }

    public static function setTaskData($taskData){
        HttpServer::$task = true;
        HttpServer::$taskData = $taskData;
    }

    public static function getTaskData(){
        return HttpServer::$taskData;
    }

    public static function setRsp($data){
        if (is_array($data)){
            HttpServer::$rspData = UtilsModel::getRsp($data);
        }else{
            HttpServer::$rspData = $data;
        }
    }
}
