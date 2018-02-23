<?php

namespace sdkService\service;


class serviceRedActive extends serviceBase
{

    /**
     * 获取红点信息
     *
     * @param string $channelId 渠道编号
     *
     * @return array
     */
    public function getRedActive(array $arrInput = [])
    {
        $iChannelId = self::getParam($arrInput, 'channelId');
        $return = self::getStOut();
        if (empty( $iChannelId )) {
            return $return;
        }
        $data = $this->model('redActive')->getChannelRedActiveData($iChannelId);
        $data = !empty($data)?json_decode($data,true):(object)[];
        $return['data'] = $data;
        return $return;
    }

}