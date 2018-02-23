<?php

/**
 * 地理位置相关的Model
 */

namespace sdkService\model;

class Ip extends BaseNew
{

    private $preKey = 'IP_LIMIT_';

    /**
     * 记录增加IP的次数
     *
     * @param int $channelId 用户端的唯一标识，推荐  channelId_ip_controller_action 这种
     * @param string $id 用户端的唯一标识，推荐  channelId_ip_controller_action 这种
     * @param int $timeLimit 时限
     */
    public function recordIp($channelId, $id, $timeLimit)
    {
        $redisKey = $this->preKey . $id;
        $redis = $this->redis($channelId, STATIC_MOVIE_DATA);
        $num = $redis->WYincrBy($redisKey, 1);//$num 调用incrby后增到了几
        if ($num == 1) {
            $redis->WYexpire($redisKey, $timeLimit);
        }
        if ($timeLimit == -1) {
            $redis->WYdelete($redisKey);
        }

        return $num;
    }

    /**
     * 获取IP已记录次数
     *
     * @param int $iChannelId
     * @param string $id 用户端的唯一标识
     * @return int
     */
    public function getIpNums($iChannelId, $id)
    {
        $redis = $this->redis($iChannelId, STATIC_MOVIE_DATA);
        $redisKey = $this->preKey . $id;
        return (int)$redis->WYget($redisKey);
    }

    /**
     * 修改IP限制时间，默认30分钟
     * @param $channelId
     * @param $id
     * @param $extendLimitTime
     * @return bool
     */
    public function extendLimitTime($channelId, $id, $extendLimitTime = 1800)
    {
        $redisKey = $this->preKey . $id;
        $redis = $this->redis($channelId, STATIC_MOVIE_DATA);
        //返回1或0，修改成功取1，键不存在或修改失败返回0
        return $redis->WYexpire($redisKey, $extendLimitTime);
    }
}