<?php
/**
 * Created by Netbeans IDE.
 * User: baibaoqiang
 * Date: 17/02/08
 * Time: 上午11:20
 */

namespace sdkService\model;


class Bonus extends BaseNew
{
    /**
     * 获取首页红包状态
     * @param $channelId
     * @param $openId `
     * @return array
     */
    public function getStatus($channelId)
    {
        $return = [];

        $Key = 'weixinsp_red_envelop';
        //获取用户所有的消息
        $redis = $this->redis($channelId, BONUS_STATUS);
        $return = $redis->WYget($Key);

        return $return;
    }

    /**
     * 首页拉新促销弹框活动
     */
    public function NewcomerBonus($channelId)
    {
        $randomNum = rand(1, 10);
        $Key = 'static_new_promotion_sharing_data_string_' . $channelId . '_' . $randomNum;
        //获取用户所有的消息
        $redis = $this->redis($channelId, STATIC_MOVIE_DATA);
        $return = $redis->WYget($Key);
        return $return;
    }

}