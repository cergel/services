<?php

namespace sdkService\model;


class Wxapp extends BaseNew
{

    //获取token from redis
    public function readMptoken($iChannelId)
    {
        $data = $this->redis($iChannelId, WXAPP_TOKEN)->WYget(KEY_WXAPP_TOKEN);

        return $data;
    }

    //存储token to redis
    public function setMptoken($iChannelId,$strToken,$iExpire)
    {
        $data = $this->redis($iChannelId, WXAPP_TOKEN)->WYset(KEY_WXAPP_TOKEN,$strToken);
        $this->redis($iChannelId, WXAPP_TOKEN)->WYexpire(KEY_WXAPP_TOKEN,$iExpire);
        return $data;
    }

}