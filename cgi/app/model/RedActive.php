<?php

namespace sdkService\model;


class RedActive extends BaseNew
{
    const RED_KEY = "red_active_channel_";
    /**
     * 获取指定渠道的信息
     *
     * @param string $iChannelId
     *
     * @return array
     */
    public function getChannelRedActiveData($iChannelId = '')
    {
        return $this->redis($iChannelId,USER_MOVIE_PEE)->WYget(self::RED_KEY.$iChannelId);
    }




}