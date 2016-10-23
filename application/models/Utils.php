<?php
/**
 * @name Utils
 * @desc Utils 工具类
 * @author pangbolike
 */

require_once(dirname(__FILE__).'/../library/config.php');

class UtilsModel {

    private static $errMsgMap = array(
        ERR_PARAM_ERR => 'param err',
        ERR_NO_AUTH => 'need login',
        ERR_FORBIDDEN => 'forbidden',
        ERR_NOT_FOUND => 'not found',
        ERR_SYSTEM => 'system err');

    public function __construct() {
        
    }

    //增加callback函数
    public static function addCallBack($fun,$str)
  	{
      	if (empty($fun))
            return $str;
      	return $fun . '(' . $str . ')';
  	}

    public static function getRsp($ret){
        if (!isset($ret['msg']) && isset(self::$errMsgMap[$ret['ret']])){
            $ret['msg'] = self::$errMsgMap[$ret['ret']];
        }
        return self::addCallBack(ServerModel::getQuery('callback'), self::getUrlJson($ret));
    }


    //生成queryStr
    public static function getQueryStr($json){
        $ans = "?";
        $flag = false;
        foreach ($json as $key => $val) {
            if ($flag)
                $ans = $ans."&";
            else
                $flag = true;
            $ans = $ans.$key."=".urlencode($val);
        }
        return $ans;
    }

    //生成参数json
    public static function getParamsJson($query,$paramArr){
        $ans = array();
        foreach ($paramArr as $key) {
            $val = $query[$key];
            if (null != $val){
                $ans[$key] = $val;
            }
        }
        return $ans;
    }

    //检查json内的参数是否存在
    public static function checkParams($json,$arr){
        foreach ($arr as $key) {
            if (!isset($json[$key]))
                return false;
        }
        return true;
    }
    
    public static function getHeader($token){
        $header = array();
        $header[] = "Content-Type:application/json";
        $header[] = "userToken:".$token;
        return $header;
    }

    public static function signRequest($data){
        error_log(REQUEST_SIGN_SECRET);
        $time = time();
        error_log($time.'');
        $signer = md5($time . $data . $time . REQUEST_SIGN_SECRET);
        $ans = "timestamp=$time&sign=$signer";
        error_log('signrequest = '.$ans);
        return $ans;
    }

    //使用curl发送post请求
    public static function post_by_curl($url, $post_string, $header){
      	$ch = curl_init();
        UtilsModel::log_debug('post data = '.$post_string);
        UtilsModel::log_debug("url = $url");
      	curl_setopt($ch,CURLOPT_URL,$url);
        if ($header != null){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
      	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
      	curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
      	curl_setopt($ch,CURLOPT_POST,1);
      	curl_setopt($ch,CURLOPT_POSTFIELDS,$post_string);
      	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        $data = json_decode(curl_exec($ch),true);
      	curl_close($ch);
      	return $data;
  	}

    //使用curl发送get请求
    public static function get_by_curl($action,$query_string,$header,$needCreate = false){
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,REQUEST_URI_PRE.$action.$query_string);
        //echo REQUEST_URI_PRE.$action.$query_string;
        if ($header != null){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        if ($needCreate)
            $data["result"] = json_decode(curl_exec($ch),true);
        else
            $data = json_decode(curl_exec($ch),true);
        $data["http_code"] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $data;
    }

    //对json进行编码
    public static function getUrlJson($arr)
    {
        return addcslashes(urldecode(json_encode(self::_urlencode($arr))),"\r\n");
    }

    public static function _urlencode($elem)
    {
        if(is_array($elem))
      {
        $na = array();
        foreach($elem as $k=>$v)
        {
            $na[self::_urlencode($k)] = self::_urlencode($v);
        }
        return $na;
      }
      return is_numeric($elem) ? $elem :urlencode($elem);
    }

    public static function log_debug($msg){
        if (DEBUG) {
            if (is_array($msg)) {
                error_log(UtilsModel::getUrlJson($msg));
            } else {
                error_log($msg);
            }
        }
    }

    public static function log_error($msg){
        if (is_array($msg)) {
            error_log('[ERROR]' . UtilsModel::getUrlJson($msg));
        } else {
            error_log('[ERROR]' . $msg);
        }
    }

    public static function getDay($timestamp){
        if (null != $timestamp)
            return date('Ymd', $timestamp);
        else
            return date('Ymd');
    }

    public static function objectToArray($obj){
        return is_object($obj) ? get_object_vars($obj) : $obj;
    }
    
}
