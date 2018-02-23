<?php
namespace redisTools\route\defaultRouter;
use redisTools\route\AbstractRouter;

/**
 * Class mixRouter  默认router
 * @package redisManager\route\mixRouter
 */
class defaultRouter extends AbstractRouter
{

    private $defaultConf = null;//默认配置
    private $stableType = null;//3种（null,write,read）
    private $onceType = null;//3种（null,write,read）

    //当数据为hash结构时，是否用小key作为一致性哈希的key
    private $useSubKeyStable = false;
    private $useSubKeyOnce = false;


    //调用哈希算法类，获取最终配置
    /**
     * @param $strKey
     * @param $rediscConf:格式如下
     *
     * [
            [
                'write' => [
                    'host'     => '192.168.0.1',
                    'port'     => 6379,
                    'timeout'  => 10,
                    'password' => 'xxx',
                    "prefix"   => "xxx",
                    "database" => 7,
                ],
                'read'  => [
                    'host'     => '192.168.0.2',
                    'port'     => 6379,
                    'timeout'  => 10,
                    'password' => 'xxx',
                    "prefix"   => "xxx",
                    "database" => 7,
                ],
            ],
            .........
        ],
     *
     * @return mixed
     * @throws \Exception
     */
    public static function hashConf($strKey, $rediscConf){
        if(count($rediscConf)==1){//如果只有一个元素的话，不用一致性哈希算法
            return $rediscConf[0];
        }else{
            $hashDesObj = hashDes::instance($rediscConf);
            return $hashDesObj->lookupConfig($strKey);
        }
    }

    //返回一个数组，里面是全部redis的配置
    public function getRedisConfToArray(){
        $returnConf=[];
        //遍历各个节点
        foreach($this->redisConf['db'] as $v){
            $returnConf[]=$v['write'];
            $returnConf[]=$v['read'];
        }
        return $returnConf;
    }

    //设置只用write库
    public function setWriteStable()
    {
        $this->stableType = 'write';
        return $this->stableType;
    }

    //设置只用read库
    public function setReadStable()
    {
        $this->stableType = 'read';
        return $this->stableType;
    }

    //设置成使用默认配置文件读取
    public function setDefaultStable()
    {
        $this->stableType = null;
        return $this->stableType;
    }

    //设置只用write库一次
    public function setWriteOnce()
    {
        $this->onceType = 'write';
        return $this->onceType;
    }

    //设置只用read库一次
    public function setReadOnce()
    {
        $this->onceType = 'read';
        return $this->onceType;
    }

    //设置成使用默认配置文件读取一次
    public function setDefaultOnce()
    {
        $this->onceType = null;
        return $this->onceType;
    }

    //设置为一直使用 SubKey
    public function setUseSubKeyStable(){
        $this->useSubKeyStable = true;
    }

    //设置为使用一次SubKey
    public function setUseSubKeyOnce(){
        $this->useSubKeyOnce = true;
    }

    //设置为一直使用hash小key,【一般不建议用】
    public function setUseSubKeyStableDefault(){
        $this->useSubKeyStable = false;
    }

    //设置为使用一次为默认key
    public function setUseSubKeyOnceDefault(){
        $this->useSubKeyOnce = false;
    }

    //获取最终配置
    public function getFinalConf($fun, $params)
    {
        $enableFun = ['WYhSet', 'WYhGet', 'WYhExists', 'WYhDel'];
        if($this->useSubKeyOnce===true){
            //这种情况是用hash小key来做,只使用一次
            if (in_array($fun, $enableFun)) {
                $hashConf = self::hashConf($params[1], $this->redisConf['db']);
                $this->setUseSubKeyOnceDefault();
            } else {
                throw new \Exception("{$fun} not allow use SubKey");
            }
        }elseif($this->useSubKeyStable ===true){
            //这种情况是用hash小key来做,一直使用，一般不推荐
            if (in_array($fun, $enableFun)) {
                $hashConf = self::hashConf($params[1], $this->redisConf['db']);
            } else {
                throw new \Exception("{$fun} not allow use SubKey");
            }
        }else {
            $hashConf = self::hashConf($params[0], $this->redisConf['db']);
        }

        if(empty($this->defaultConf)){
            $this->defaultConf = require(__DIR__ . '/default.conf.php');
        }

        $arrWriteFun = isset($this->redisConf['writeFun']) ? $this->redisConf['writeFun'] :  $this->defaultConf['writeFun'];
        $arrReadFun = isset($this->redisConf['readFun']) ? $this->redisConf['readFun'] :  $this->defaultConf['readFun'];

        if($this->onceType){//如果有设置一次的
            if($this->onceType=='write'){
                $finalConf = $hashConf['write'];
            }else{
                $finalConf = $hashConf['read'];
            }
            $this->setDefaultOnce();
        }elseif($this->stableType){//如果有设置持续的
            if ($this->stableType == 'write') {
                $finalConf = $hashConf['write'];
            } else {
                $finalConf = $hashConf['read'];
            }
        }else{//都没设置，走默认配置
            if (in_array($fun, $arrWriteFun)) {
                $finalConf = $hashConf['write'];
            } elseif (in_array($fun, $arrReadFun)) {
                $finalConf = $hashConf['read'];
            } else {
                throw new \Exception("function {$fun} not defined in config");
            }
        }
        return $finalConf;
    }

}