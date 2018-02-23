<?php

namespace sdkService\service;


//ip限制类
class serviceIpLimit extends serviceBase
{

    /**
     * 记录IP的次数
     *
     * @param   int       channelId   渠道编号
     * @param   string    id          用户端唯一标识
     * @param   int       timeLimit   有效时间
     * @return  arrary  ['ret'=>0,'sub'=>0,'data'=>['count'=>20]]
     */
    public function recordIp($arrInput)
    {
        $return = static::getStOut();
        $iChannelId = $arrInput['channelId'];
        $id = $arrInput['id'];
        $timeLimit = $arrInput['timeLimit'];
        $return['data']['count'] = $this->model('Ip')->recordIp($iChannelId, $id, $timeLimit);

        return $return;
    }


    /**
     * 获取IP调用次数
     *
     * @param   string  id          用户端唯一标识
     * @param   int     channelId   渠道编号
     * @return  array   ['ret'=>0,'sub'=>0,'data'=>['count'=>231]]
     */
    public function getIpNums($arrInput)
    {
        $return = static::getStOut();
        $iChannelId = $arrInput['channelId'];
        $id = $arrInput['id'];
        $return['data']['count'] = $this->model('Ip')->getIpNums($iChannelId, $id);

        return $return;
    }

    /**
     * 修改IP限制时间
     * 当IP的请求触发限制，在超过限制的那一次调用，将key的过期时间延长（默认30分钟），之间此ip访问受限
     * @param   string  id          用户端唯一标识
     * @param   int     channelId   渠道编号
     * @param   int     extendLimitTime   限制的秒数，默认30分钟
     * @return array
     */
    public function extendLimitTime ($arrInput)
    {
        $return = static::getStOut();
        $iChannelId = $arrInput['channelId'];
        $id = $arrInput['id'];
        $extendLimitTime = $arrInput['extendLimitTime'];
        $changeExpire = $this->model('Ip')->extendLimitTime($iChannelId, $id, $extendLimitTime);

        if ($changeExpire) {
            return $return;
        } else {
            return ['ret' => -1, 'sub' => -1, 'msg' => 'change expire time fail'];
        }
    }
}