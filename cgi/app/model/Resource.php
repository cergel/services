<?php
/**
 * Created by PhpStorm.
 * User: bbq
 * Date: 17/4/1
 * Time: 下午1:44
 */

namespace sdkService\model;
class Resource extends BaseNew
{
    public function getIcon($channelId)
    {
        //获取三端底部icon
        $redis = $this->redis($channelId, RESOURCE_STATIC);
        $keyTemplate = ICON_CONFIG;
        $input = ['channelId' => $channelId];
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        return $redis->WYget($redisKey);
    }

    //app获取支付后红包数量
    public function getRedPacketNum($channelId)
    {
        $num = 0;
        $redis = $this->redis($channelId, RESOURCE_STATIC);
        $redPacketKey = APP_RED_PACKET_DATA_KEY;
        $redPacketData = $redis->WYget($redPacketKey);
        if ($redPacketData) {
            $redPacketData = json_decode($redPacketData, true);
            $num = isset($redPacketData['num']) ? $redPacketData['num'] : 0;
        }
        return $num;
    }

    //获取客户端可退票的影院
    public function getAppRefundCinema($channelId)
    {
        $return = [];
        $redis = $this->redis($channelId, RESOURCE_STATIC);
        $key = APP_REFUND_CINEMA;

        $cinema = $redis->WYhGetAll($key);
        if ($cinema) {
            foreach ($cinema as $key => $value) {
                $return[] = (int)$key;
            }
        }
        return $return;
    }
}