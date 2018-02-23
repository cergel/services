<?php

namespace sdkService\service;


class serviceRedSpot extends serviceBase
{
    
    /**
     * 获取城市列表
     *
     * @param string $channelId 渠道编号
     * @param string $openId    openId
     *
     * @return
     */
    public function getRedSpotInfo(array $arrInput = [])
    {
        $iChannelId = self::getParam($arrInput, 'channelId');
        $strOpenId = self::getParam($arrInput, 'openId');
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        if (empty( $iChannelId )) {
            return $return;
        }
        //读取各渠道总开关
        $redspotChannels = $this->model('redSpot')->redSpotChannels($iChannelId);
        $result = [];
        foreach ($redspotChannels as $channelName => $strTime) {
            $arrTime = explode('|', $strTime);//开始时间|结束时间
            $time = time();
            //若开关开启且当前时间在设定时间内
            if ( !empty( $arrTime[0] ) && $time >= $arrTime[1] && $time <= $arrTime[2]) {
                //判断是否需要显示红点，未买过演出票的显示1
                $redSpotUser = $this->model('redSpot')->redSpotUser($channelName, $strOpenId, $iChannelId);
                //获取演出红点投放段ID，用于前端点击后存储
                $redSpotId = $this->model('redSpot')->redSpotId($channelName, $iChannelId);
                $result[$channelName] = [
                    'status' => $redSpotUser,
                    'id'     => $redSpotId,
                ];
            }
        }
        if ( !empty( $result )) {
            $return['data'] = $result;
        }
        
        return $return;
    }
    
}