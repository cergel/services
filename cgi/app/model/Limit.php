<?php

namespace sdkService\model;

class Limit extends BaseNew
{
    
    /**
     * 通用的，记录请求限制的方法
     *
     * @param int    $iChannelId  渠道编号
     * @param string $strLimitKey key
     * @param int    $iTimeLimit  限制时间
     *
     * @return int
     */
    public function recordLimit($iChannelId, $strLimitKey, $iTimeLimit)
    {
        $num = 0;
        if ( !empty($iChannelId) && !empty($iTimeLimit) && !empty($strLimitKey)) {
            $redis = $this->redis($iChannelId, COMMON_LIMIT);
            if ($iTimeLimit == -1) {
                $redis->WYdelete($strLimitKey);
                
                return $num;
            }
            $num = $redis->WYincrBy($strLimitKey, 1);
            if ($num == 1) {
                $redis->WYexpire($strLimitKey, $iTimeLimit);
            }
        }
        
        return $num;
    }
    
    /**
     * 获取记录次数
     *
     * @param int    $iChannelId  渠道编号
     * @param string $strLimitKey key
     *
     * @return int
     */
    public function getLimit($iChannelId, $strLimitKey)
    {
        $redis = $this->redis($iChannelId, COMMON_LIMIT);
        
        return (int)$redis->WYget($strLimitKey);
    }
    
    /**
     * 修改限制时间，默认30分钟
     *
     * @param $channelId
     * @param $id
     * @param $extendLimitTime
     *
     * @return bool
     */
    public function extendLimitTime($channelId, $strLimitKey, $extendLimitTime = 1800)
    {
        $redis = $this->redis($channelId, COMMON_LIMIT);
        
        //返回1或0，修改成功取1，键不存在或修改失败返回0
        return $redis->WYexpire($strLimitKey, $extendLimitTime);
    }
    
    /**
     * 获取某个IP的网络号对应的限制规则
     * 注意：我们假定子网掩码为 255.255.255.0
     *
     * @param $channelId
     * @param $id
     * @param $extendLimitTime
     *
     * @return []
     */
    public function getIpLimitRule($channelId = '', $strIp = '')
    {
        $return = [];
        $redis = $this->redis($channelId, COMMON_LIMIT);
        //返回1或0，修改成功取1，键不存在或修改失败返回0
        $res = $redis->WYhGetAll($strIp);
        $return = !empty($res) ? $res : [];
        
        return $return;
    }
    
}