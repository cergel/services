<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/28
 * Time: 11:12
 */
namespace sdkService\drivers;

use \sdkService\helper;

/**
 * 此类是对redisManager这个类包的一个包装
 * Class RedisManager
 * @package sdkService\drivers
 */
class RedisManager{

    private static $manager=null;//存放redisManager的实例

    /**
     * 作用：
     * 1.根据$channelId,$redisConKey拿到配置
     * 2.返回redisManager
     * @param $channelId
     * @param $redisConKey
     * @return \redisManager\redisManager
     * @throws \Exception
     */
    public static function getInstance($channelId,$redisConKey){
        if(isset(\wepiao::$config['params']['redis'][$redisConKey][$channelId])){
            $conf = \wepiao::$config['params']['redis'][$redisConKey][$channelId];
        }else{
            $conf = \wepiao::$config['params']['redis'][$redisConKey]["common"];
        }

        $sub = md5(json_encode($conf));
        if(empty(self::$manager[$sub])){
            self::$manager[$sub] = \redisTools\redisManager::getInstance($conf);
        }
        return self::$manager[$sub];
    }

}