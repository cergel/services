<?php
/**
 * Created by PhpStorm.
 * User: xiangli
 * Date: 2016/9/7
 * Time: 19:49
 */

namespace sdkService\model;

class StarGreeting extends BaseNew
{
    protected $redisType = GROUP_SHARE_FREQUENT;
    protected $dbConfig = 'dbActive';
    protected $tableName = 't_star_greeting';

    const GREETING_INFO_CACHE_EXPIRE = 2592000;//问候有效期一个月86400*30

    public function __construct()
    {
        $this->redis = $this->redis('common', $this->redisType);
    }

    /**
     * 从数据库查询一条问候
     * @param $id
     * @return mixed
     */
    public function queryGreetFromDb($id){
        $field='*';
        $params = [':id' => $id,];
        $where = 'id = :id';
        $openId = $this->getPdoHelper()->fetchOne($where, $params, $field);
        return $openId;
    }

    /**
     * 查询某个渠道线上的明星问候
     * @return bool|string
     */
    public function queryOnlineGreeting($channel_id){
        $keyTemplate = STAR_GREETING_ONLINE_ID;
        $input = ['channelId' => $channel_id];
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        return $this->redis('common', $this->redisType)->WYget($redisKey);
    }

    /**
     * 设置在线的明显问候id
     * @param $id
     * @return bool
     */
    public function setOnlineGreeting($id,$channel_id){
        $keyTemplate = STAR_GREETING_ONLINE_ID;
        $input = ['channelId' => $channel_id];
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        return $this->redis('common', $this->redisType)->WYset($redisKey,$id);
    }

    /**
     * 获取某个id的问候的数据
     * @param $id
     * @return mixed
     */
    public function getGreetingInfo($id){
        $input = ['greetingId' => $id];
        $keyTemplate = STAR_GREETING_INFO;
        $where = 'id = :id';
        $whereParams = [':id' => $id];
        return $this->queryStringCache($input, $keyTemplate, $this->dbConfig, $this->tableName, self::GREETING_INFO_CACHE_EXPIRE, $where, $whereParams);
    }
}