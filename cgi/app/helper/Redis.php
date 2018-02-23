<?php

namespace sdkService\helper;

class Redis
{

    /**
     * @var \Redis
     */
    private $Redis = null;

    /**
     * 设置当前操作类的内部redis实例对象
     *
     * @param \Redis $redis
     */
    public function setRedisObj(\Redis $redis)
    {
        if ( !empty( $redis )) {
            $this->Redis = $redis;
        }
    }

    public function listPush($strKey, $mixValue, $strType = 'L')
    {
        if ($strType == 'R') {
            return $this->Redis->rPush($strKey, $mixValue);
        }
        else {
            return $this->Redis->lPush($strKey, $mixValue);
        }

    }

    public function listPop($strKey, $strType = 'R')
    {
        if ($strType == 'L') {
            return $this->Redis->lPop($strKey);
        }
        else {
            return $this->Redis->rPop($strKey);
        }
    }

    public function listFindAll($strKey)
    {
        return $this->Redis->lRange($strKey, 0, -1);
    }

    public function setsAdd($strKey, $mixValue)
    {
        return $this->Redis->sAdd($strKey, $mixValue);
    }

    public function setsFindAll($strKey)
    {
        return $this->Redis->sMembers($strKey);
    }

    public function setsIsMember($key,$value){
        return $this->Redis->sIsMember($key,$value);
    }

    //从集合中随机返回一个元素
    public function setsRandMember($key){
        return $this->Redis->sRandMember($key);
    }

    public function setsDel($strKey, $mixValue)
    {
        return $this->Redis->sRem($strKey, $mixValue);
    }

    public function hashMset($strHashKey, $arrHashKeyValue)
    {
        return $this->Redis->hMset($strHashKey, $arrHashKeyValue);
    }

    /**
     * hashGet
     *
     * @param string $strKey   hashKey
     * @param string $mixValue subKey
     * @param string $hashType hash方式，以$strKey还是$mixValue来做哈希。如果值为“subKey”，则用$mixValue来做哈希
     *
     * @return string
     */
    public function hashGet($strKey, $mixValue)
    {
        return $this->Redis->hGet($strKey, $mixValue);
    }

    public function hashGetBatch($strKey, $arrValue)
    {
        return $this->Redis->hMGet($strKey, $arrValue);
    }

    /**
     * hashExists
     *
     * @param string $strKey   hashKey
     * @param string $mixValue subKey
     * @param string $hashType hash方式，以$strKey还是$mixValue来做哈希。如果值为“subKey”，则用$mixValue来做哈希
     *
     * @return bool
     */
    public function hashExists($strKey, $mixValue, $hashType = '')
    {
        return $this->Redis->hExists($strKey, $mixValue);
    }

    public function hashFindAll($strKey)
    {
        return $this->Redis->hGetAll($strKey);
    }

    /**
     * hashDel
     *
     * @param string $strKey
     * @param string $mixValue
     * @param string $hashType hash方式，以$strKey还是$mixValue来做哈希。如果值为“subKey”，则用$mixValue来做哈希
     *
     * @return int
     */
    public function hashDel($strKey, $mixValue, $hashType = '')
    {
        return $this->Redis->hDel($strKey, $mixValue);
    }

    public function hashKeys($strKey)
    {
        return $this->Redis->hKeys($strKey);
    }

    public function del($strKey)
    {
        return $this->Redis->delete($strKey);
    }

    public function listLength($key)
    {
        return $this->Redis->lLen($key);
    }

    public function setObjectInfo($key, $hashkey, $value)
    {
        return $this->Redis->hSet($key, $hashkey, $value);
    }

    public function getHashInfo($key, $hashkey)
    {
        return $this->Redis->hGet($key, $hashkey);
    }

    public function expire($strKey, $iSecond)
    {
        return $this->Redis->expire($strKey, $iSecond);
    }

    public function set($strKey, $strValue)
    {
        return $this->Redis->set($strKey, $strValue);
    }

    public function get($strKey)
    {
        return $this->Redis->get($strKey);
    }

    public function exists($strKey)
    {
        return $this->Redis->exists($strKey);
    }

    public function incrby($key, $value)
    {
        return $this->Redis->incrby($key, $value);
    }

    public function hashLength($key)
    {
        return $this->Redis->hLen($key);
    }


    /**
     * 返回有序集 key 中，指定区间内的成员。 按照score由大到小排
     * @param $key
     * @param $start
     * @param $end -1 代表全部成员
     * @return array
     */
    public function zRevRange($key,$start,$end){
        return $this->Redis->zRevRange($key,$start,$end);
    }

    /**
     * 返回有序集 key 中，指定区间内的成员。按照score由小到大排
     * @param $key
     * @param $start
     * @param $end -1 代表全部成员
     * @return array
     */
    public function zRange($key,$start,$end){
        return $this->Redis->zRange($key,$start,$end);
    }

    /**
     * 将一个 member 元素及其 score 值加入到有序集 key 当中。
     * @param $key
     * @param $score
     * @param $value
     * @return int
     */
    public function zAdd($key,$score,$value){
        return $this->Redis->zAdd($key,$score,$value);
    }
    /**
     * 返回有序集 key 中成员数量。
     * @param $key
     * @return int
     */
    public function zCard($key){
        return $this->Redis->zCard($key);
    }


    //返回指定key中member成员的排名，如果member不存在返回false
    public function zRank($key,$member){
        return $this->Redis->zRank($key,$member);
    }
    

    
}