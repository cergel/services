<?php
namespace redisTools;

//redis运行类  作用：1.连接redis 2.执行命令
class redisOperator
{
    private $timeOut = 10;//定义超时时间
    /**
     * @var \Redis
     */
    private $redis = null;
    private static $operator = [];//多单例

    //单例模式，禁止new 操作
    private function __construct()
    {
    }

    //通过传入配置文件，生成对应的key
    public static function getObjKey($arrRedisConf)
    {
        $info = [
            'host' => $arrRedisConf['host'],
            'port' => $arrRedisConf['port'],
            'databases' => isset($arrRedisConf['database']) ? $arrRedisConf['database'] : 0,
            'prefix' => isset($arrRedisConf['prefix']) ? $arrRedisConf['prefix'] : '',
        ];
        return md5(json_encode($info));
    }

    /**
     * 加载配置，获取redis单例
     * @param $arrRedisConf
     * @return \redisManager\redisOperator
     * @throws \Exception
     */
    public static function getInstance($arrRedisConf)
    {
        if (!empty($arrRedisConf)) {
            $objKey = self::getObjKey($arrRedisConf);
            if (empty(self::$operator[$objKey])) {
                self::$operator[$objKey] = new self();
                self::$operator[$objKey]->redisConnect($arrRedisConf);
            }
            return self::$operator[$objKey];
        } else {
            throw new redisManagerException('redis conf empty');
        }
    }

    //卸载单例
    public static function unsetInstance($arrRedisConf)
    {
        $objKey = self::getObjKey($arrRedisConf);
        self::$operator[$objKey] = null;
    }

    //连接redis方法，
    public function redisConnect($arrRedisConf)
    {
        $this->redis = new \Redis();
        $ip = $arrRedisConf['host'];
        $port = $arrRedisConf['port'];
        $timeOut = isset($arrRedisConf['timeout']) ? $arrRedisConf['timeout'] : $this->timeOut;
        $prefix = isset($arrRedisConf['prefix']) ? $arrRedisConf['prefix'] : '';
        $pwd = isset($arrRedisConf['password']) ? $arrRedisConf['password'] : '';
        $databases = isset($arrRedisConf['database']) ? $arrRedisConf['database'] : 0;
        $mixRet = $this->redis->connect($ip, $port, $timeOut);
        if (!$mixRet) {
            throw new redisManagerException("Redis server can not connect!");
        }
        if ($pwd) {
            $this->redis->auth($pwd);
        }
        if ($prefix) {
            $this->redis->setOption(\Redis::OPT_PREFIX, $prefix);
        }
        if ($databases) {
            $this->redis->select($databases);
        }
    }

    //rPush
    public function WYrPush($strKey, $mixValue)
    {
        return $this->redis->rPush($strKey, $mixValue);
    }

    //lPush
    public function WYlPush($strKey, $mixValue)
    {
        return $this->redis->lPush($strKey, $mixValue);
    }

    //keys
    public function WYkeys($str)
    {
        return $this->redis->keys($str);
    }

    //lPop
    public function WYlPop($strKey)
    {
        return $this->redis->lPop($strKey);
    }

    //rPop
    public function WYrPop($strKey)
    {
        return $this->redis->rPop($strKey);
    }

    //listFindAll

    public function WYlRange($strKey, $start = 0, $end = -1)
    {
        return $this->redis->lRange($strKey, $start, $end);
    }

    //setsAdd
    public function WYsAdd($strKey, $mixValue)
    {
        return $this->redis->sAdd($strKey, $mixValue);
    }

    //WYsDiffStore
    public function WYsDiffStore($dst, $strKey1, $strKey2)
    {
        return $this->redis->sDiffStore($dst, $strKey1, $strKey2);
    }

    //WYsPop
    public function WYsPop($strKey)
    {
        return $this->redis->sPop($strKey);
    }

    //如果$strKey不存在，此函数会返回空数组而不是false
    //setsFindAll
    public function WYsMembers($strKey)
    {
        return $this->redis->sMembers($strKey);
    }

    //setsDel
    public function WYsRem($strKey, $mixValue)
    {
        return $this->redis->sRem($strKey, $mixValue);
    }

    //hashMset,hmset
    public function WYhMset($strHashKey, $arrHashKeyValue, $expireTime = -1)
    {
        $r = $this->redis->hMset($strHashKey, $arrHashKeyValue);
        if ($expireTime > 0) {
            $this->redis->expire($strHashKey, $expireTime);
        }
        return $r;
    }

    //hashGet , getHashInfo
    public function WYhGet($strKey, $mixValue)
    {
        return $this->redis->hGet($strKey, $mixValue);
    }

    //哈希类型做自增， 自减操作，$value可以为负数
    public function WYhIncrBy($key, $hashKey, $value)
    {
        return $this->redis->hIncrBy($key, $hashKey, $value);
    }

    //hashGetBatch
    public function WYhMGet($strKey, $arrValue)
    {
        return $this->redis->hMGet($strKey, $arrValue);
    }

    //hashExists
    public function WYhExists($strKey, $mixValue)
    {
        return $this->redis->hExists($strKey, $mixValue);
    }

    //hashFindAll , getObjectInfo
    public function WYhGetAll($strKey)
    {
        return $this->redis->hGetAll($strKey);
    }

    //hashDel
    public function WYhDel($strKey, $hashKey)
    {
        return $this->redis->hDel($strKey, $hashKey);
    }

    //del
    public function WYdelete($strKey)
    {
        return $this->redis->delete($strKey);
    }

    //listLength
    public function WYlLen($key)
    {
        return $this->redis->lLen($key);
    }
    
    //del list-value
    public function WYlRem($key , $value)
    {
        return $this->redis->lRem($key,$value,0);
    }

    //setHashInfo setObjectInfo
    public function WYhSet($key, $hashkey, $value)
    {
        return $this->redis->hSet($key, $hashkey, $value);
    }

    //expire
    public function WYexpire($strKey, $iSecond)
    {
        if ($iSecond == -1) {
            return $this->redis->persist($strKey);
        } else {
            return $this->redis->expire($strKey, $iSecond);
        }
    }

    //set
    public function WYset($strKey, $strValue, $expireTime = 0)
    {
        $r = $this->redis->set($strKey, $strValue);
        if ($expireTime > 0) {
            $this->redis->expire($strKey, $expireTime);
        }
        return $r;
    }

    //get
    public function WYget($strKey)
    {
        return $this->redis->get($strKey);
    }

    //incr
    public function WYincr($strKey)
    {
        return $this->redis->incr($strKey);
    }

    //decr
    public function WYdecr($strKey)
    {
        return $this->redis->decr($strKey);
    }

    //incrby
    public function WYincrBy($key, $value)
    {
        return $this->redis->incrBy($key, $value);
    }

    //decrby
    public function WYdecrBy($key, $value)
    {
        return $this->redis->decrBy($key, $value);
    }

    //setsSisMember
    public function WYsIsMember($strKey, $strValue)
    {
        return $this->redis->sIsMember($strKey, $strValue);
    }

    //检查key是否存在
    public function WYexists($strKey)
    {
        return $this->redis->exists($strKey);
    }

    //zRange
    public function WYzRange($key, $start, $end, $withscore = null)
    {
        return $this->redis->zRange($key, $start, $end, $withscore);
    }

    //zRangeByScore
    public function WYzRangeByScore($key, $start, $end, $withscore = array( 'withscores' => TRUE))
    {
        return $this->redis->zRangeByScore($key, $start, $end, $withscore);
    }

    //zCard
    public function WYzCard($key)
    {
        return $this->redis->zCard($key);
    }

    //zRevRange
    public function WYzRevRange($key, $start, $end, $withscore = null)
    {
        return $this->redis->zRevRange($key, $start, $end, $withscore);
    }

    //zAdd
    public function WYzAdd($key, $score, $value)
    {
        return $this->redis->zAdd($key, $score, $value);
    }

    //zRank
    public function WYzRank($key, $member)
    {
        return $this->redis->zRank($key, $member);
    }

    //hashKeys
    public function WYhKeys($key)
    {
        return $this->redis->hKeys($key);
    }

    //hashLength
    public function WYhLen($key)
    {
        return $this->redis->hLen($key);
    }

    // setnx
    public function WYsetnx($key, $value)
    {
        return $this->redis->setnx($key, $value);
    }


    //删除有序集合中的指定元素
    public function WYzRem($key, $value)
    {
        return $this->redis->zRem($key, $value);
    }

    //获取集合元素数量
    public function WYsCard($key)
    {
        return $this->redis->sCard($key);
    }


}