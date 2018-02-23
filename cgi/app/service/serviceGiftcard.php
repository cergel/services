<?php

namespace sdkService\service;

use sdkService\helper;


/**
 * 礼品卡服务
 * @package app\service
 */
class serviceGiftcard extends serviceBase
{

    /**
     * 获取用户的礼品卡
     * @note  这个接口, 可以用在 支付页 获取适用于当前订单的 可用优惠
     * @param intval status         状态：1：有效, 2：无效【已过期/已用完】
     * @param string scheduleId     查询场次可用礼品卡，查询场次时必填
     * @param string openId         用户openId
     * @param string userId         用户id
     * @param string channelId      渠道编号
     * @param string subChannelId   子渠道编号
     * @param string channelId      渠道编号
     * @param string page           页码, 默认取第一页
     * @param string num            单页条目数, 默认取所有, 就是1000个
     * @return array
     */
    public function queryGiftcard(array $arrInput = [])
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['status'] = self::getParam($arrInput, 'status');
        $arrSendParams['mpid'] = self::getParam($arrInput, 'scheduleId');
        $arrSendParams['suin'] = self::getParam($arrInput, 'openId');
        $arrSendParams['uid'] = self::getParam($arrInput, 'userId');
        $arrSendParams['chanid'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['auth_id'] = '';
        $arrSendParams['sub_chanid'] = self::getParam($arrInput, 'subChannelId');
        $arrSendParams['page'] = self::getParam($arrInput, 'page', 1);
        $arrSendParams['num'] = self::getParam($arrInput, 'num', 1000);
        $arrSendParams['sRemoteIp'] = helper\Net::getRemoteIp();
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'GET',
        ];
        $arrReturn = $this->http(JAVA_API_GIFT_CAED_QUERY_INFO, $httpParams);
        return $arrReturn;
    }


}