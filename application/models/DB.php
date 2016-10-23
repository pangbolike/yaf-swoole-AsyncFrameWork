<?php
/**
 * @name DBModel
 * @desc DBModel数据获取类
 * @author pangbolike
 */

require_once(dirname(__FILE__).'/../library/config.php');

define("REDIS_URL","");
define("REDIS_PORT",0);
define("REDIS_PASSWD","");
define("REDIS_DB",0);

class DBModel {

    private static $redisInstance;

    private static $redisLastTime = 0;

    public function __construct() {

    }

    // 获取redis实例
    public static function getRedis(){
        if (null == self::$redisInstance) {
            $redis = new Redis();
            $redis->pconnect(REDIS_URL, REDIS_PORT);
            $redis->auth(REDIS_PASSWD);
            $redis->select(REDIS_DB);
            self::$redisInstance = $redis;
        }else{
            $time = time();
            if ($time - self::$redisLastTime > 5){
                self::$redisLastTime = $time;
                try{
                    self::$redisInstance->ping();
                }catch(RedisException $e){
                    $redis = new Redis();
                    $redis->pconnect(REDIS_URL, REDIS_PORT);
                    $redis->auth(REDIS_PASSWD);
                    $redis->select(REDIS_DB);
                    self::$redisInstance = $redis;
                }
            }
        }
        return self::$redisInstance;
    }

}
