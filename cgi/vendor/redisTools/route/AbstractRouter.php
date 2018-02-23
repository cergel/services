<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/11
 * Time: 11:57
 */
namespace redisTools\route;
/**
 * 抽象Router，每个router都要继承此类
 */
Abstract class AbstractRouter{

    protected static $selfObj = [];//存储自身的单例
    protected $redisConf;//传入的redis配置文件


    //私有构造函数，外部new操作
    protected function __construct($redisConf)
    {
        $this->redisConf = $redisConf;
    }

    /**
     * 获取单例
     * @param array $redisConf redis配置
     * @return obj 返回router对象
     * @throws \Exception
     */
    public static function getInstance($redisConf)
    {
        if (!empty($redisConf)) {
            $md5Key = md5(json_encode($redisConf));
            if (empty(self::$selfObj[$md5Key])) {
                self::$selfObj[$md5Key] = new static($redisConf);
            }
            return self::$selfObj[$md5Key];
        } else {
            throw new \Exception('router Error: redis conf is empty');
        }
    }

    //返回全部配置的数组
    abstract  public function getRedisConfToArray();


    /**
     * 获取最终redis配置
     * @param $fun redis要执行的方法名
     * @param $strKey redis要执行的方法的参数数组(通常由__call()函数获取)
     * @return mixed
     */
    abstract public function getFinalConf($fun, $params);
}