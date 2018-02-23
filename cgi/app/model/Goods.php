<?php

namespace sdkService\model;

class Goods extends BaseNew
{
    
    /**
     * 保存某个排期剩余座位和总座位数量信息
     *
     * @param string $strKey
     * @param array  $arrData
     * @param string $channelId
     *
     * @return bool
     */
    public function saveSeatsLeft($strKey = '', $arrData = [], $channelId = '')
    {
        $return = false;
        if ( !empty( $strKey ) && !empty( $channelId ) && !empty( $arrData )) {
            //注意，所有平台的余票紧张的原始信息，都存在专门的
            $arrData = array_map('json_encode', $arrData);
            $this->redis($channelId, TICKET_LEFT_OVER)->WYhMset($strKey, $arrData);
            $return = $this->redis($channelId, TICKET_LEFT_OVER)->WYexpire($strKey, 21600);
        }
        
        return $return;
    }
    
}