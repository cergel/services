<?php

namespace sdkService\model;

class RedSpot extends BaseNew
{
    
    /**
     * 导流红点，读取要查询的渠道
     * redis中示例 'show' => '1|1473119538|1473159538' 开关|开始时间|结束时间
     */
    public function redSpotChannels($iChannelId = '')
    {
        $return = [];
        $data = $this->redis($iChannelId, MESSAGE_CENTER)->WYhGetAll(RED_SPOT_CHANNELS);
        if ( !empty( $data )) {
            $return = $data;
        }
        
        return $return;
    }
    
    /**
     * 导流红点判断用户是否需要显示红点，未买过演出票的显示1
     * 注：大数据会将买过演出的用户openid导入至10.66.183.247，对应key是wx_3_redspot_show，勿随意修改！
     *
     * @param string $channelName 如show，目前也仅给演出使用
     * @param string $strOpenId
     *
     * @return 1|0
     */
    public function redSpotUser($channelName = '', $strOpenId = '', $iChannelId = '')
    {
        $isPerchased = 0;
        if (empty( $channelName ) || empty( $strOpenId ) || empty( $iChannelId )) {
            return $isPerchased;
        }
        $strKey = RED_SPOT_USER . $channelName;
        $isPerchased = $this->redis($iChannelId, MESSAGE_CENTER)->WYsIsMember($strKey, $strOpenId);
        $isPerchased = empty( $isPerchased ) ? 1 : 0;
        
        return $isPerchased;
    }
    
    /**
     * 获取导流红点对应ID，用于前端存储
     *
     * @param string $channelName 如show
     *
     * @return number
     */
    public function redSpotId($channelName = '', $iChannelId = '')
    {
        $return = 0;
        if (empty( $channelName ) || empty( $iChannelId )) {
            return $return;
        }
        $strKey = sprintf(RED_SPOT_ID, $channelName);
        $return = $this->redis($iChannelId, MESSAGE_CENTER)->WYget($strKey);
        
        return empty( $return ) ? 0 : intval($return);
    }
    
}