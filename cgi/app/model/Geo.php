<?php

/**
 * 地理位置相关的Model
 */

namespace sdkService\model;

class Geo extends BaseNew
{

    public function wyIpLocate($strIp,$iChannelId)
    {
        return $this->redis($iChannelId,IP_DATABASE)->WYhGetAll('ip.'.$strIp);
    }


}