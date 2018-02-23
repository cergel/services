<?php

namespace sdkService\model;

/**
 * Class Pay
 * 此类暂时不使用 2016-08-04
 *
 * @package sdkService\model
 */
class Pay extends BaseNew
{

    //验证码的超时时间
    private $paySmsMark;

    public function __construct()
    {
        parent::__construct();
        $this->paySmsMark = 'smsSmsMark_';
    }

    /**
     * 标识用户在支付的时候，需要短信验证
     * 只是单纯的存一个1或者0而已
     *
     * @param $id
     *
     * @return bool
     */
    public function createMark($channelId = '', $id = '')
    {
        $return = false;
        if ( !empty( $id ) && !empty( $channelId )) {
            $strKey = $this->paySmsMark . $id;
            $res = $this->redis($channelId, GROUP_SHARE_FREQUENT)->WYset($strKey, 1);
            $return = $res ? true : false;
        }

        return $return;
    }

    /**
     * @param $id
     * @param $code
     *
     * @return bool
     */
    public function deleteMark($channelId = '', $id = '')
    {
        $return = false;
        if ( !empty( $id ) && !empty( $channelId )) {
            $strKey = $this->paySmsMark . $id;
            $res = $this->redis($channelId, GROUP_SHARE_FREQUENT)->WYdelete($strKey);
            $return = $res ? true : false;
        }

        return $return;
    }

    /**
     * 判断用户是否需要验证码验证
     *
     * @param string $id
     * @param string $channelId
     *
     * @return bool true标识当前用户需要验证
     */
    public function needSms($channelId = '', $id = '')
    {
        $return = false;
        if ( !empty( $id ) && !empty( $channelId )) {
            $strKey = $this->paySmsMark . $id;
            $res = $this->redis($channelId, GROUP_SHARE_FREQUENT)->WYexists($strKey);
            $return = $res ? true : false;
        }

        return $return;
    }

}