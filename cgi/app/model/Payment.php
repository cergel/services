<?php

namespace sdkService\model;

use sdkService\helper\Utils;

class Payment extends BaseNew
{
    /**
     * 京东支付生成APP H5支付链接
     * @param $channelId
     * @param $params
     */
    public function getPayLinkForJdPayment($channelId, &$params)
    {
        $token = md5(date("YmdHis") . uniqid(mt_rand(10000, 99999)));
        $redis = $this->redis($channelId, RESOURCE_STATIC);
        $key = "pay_h5:" . $token;
        $payParameter = json_encode($params['data']['paymentInfo']['payParameter']);
        $params['data']['easy_payment_link'] = Utils::getHost() . "/wap-payment/jd/{$token}?channelId={$channelId}";
        $redis->WYset($key, $payParameter, 600);
    }

    /**
     * 格瓦拉支付H5支付链接
     * @param $channelId
     * @param $params
     */
    public function getPayLinkForGewara($channelId, &$params)
    {
        $token = md5(date("YmdHis") . uniqid(mt_rand(10000, 99999)));
        $redis = $this->redis($channelId, RESOURCE_STATIC);
        $key = "pay_h5:" . $token;
        $payParameter = json_encode($params['data']['paymentInfo']['payParameter']);
        $params['data']['easy_payment_link'] = Utils::getHost() . "/wap-payment/gewara/{$token}?channelId={$channelId}";
        $redis->WYset($key, $payParameter, 600);
    }


    /**
     * 卡bin支付参数保存
     * @param $channelId
     * @param $token
     * @param $payParams
     */
    public function saveCardBinParams($payParams, &$response)
    {
        $token = md5(date("YmdHis") . uniqid(mt_rand(10000, 99999)));
        $channelId = $payParams['channelId'];
        $redis = $this->redis($channelId, RESOURCE_STATIC);
        $key = "pay_bin_" . htmlspecialchars($token);
        $redis->WYset($key, json_encode($payParams));
        $redis->WYexpire($key, 600);
        $response['ret'] = 0;
        $response['sub'] = 0;
        $response['data']['paymentInfo'] = [];
        $response['data']['paymentInfo']['payStatus'] = "0";
        $response['data']['paymentInfo']['orderId'] = $payParams['orderId'];
        $response['data']['paymentInfo']['realPaymentPrice'] = 0;
        $response['data']['paymentInfo']['payParameter'] = new \stdClass();
        $response['data']['easy_payment_link'] = Utils::getHost() . "/wap/bankcard.html?token={$token}&channelId={$channelId}&orderId={$payParams['orderId']}";
    }

    public function saveOrderCardNo($params)
    {
        $channelId = $params['channelId'];
        $redis = $this->redis($channelId, RESOURCE_STATIC);
        $key = 'pay_orderid_cardno:' . $params['orderId'];
        $ret = $redis->WYset($key, $params['bankCardNo']);
        $redis->WYexpire($key, 600);
        return $ret;
    }

    public function getOrderCardNo($params)
    {
        $channelId = $params['channelId'];
        $redis = $this->redis($channelId, RESOURCE_STATIC);
        $key = 'pay_orderid_cardno:' . $params['orderId'];
        return $redis->WYget($key);
    }


    /**
     * 通过TOKEN获取APP H5 卡bin 支付参数
     * @param $channelId
     * @param $token
     * @return mixed
     */
    public function getBinPaymentToken($channelId, $token)
    {
        $redis = $this->redis($channelId, RESOURCE_STATIC);
        $token = "pay_bin_" . htmlspecialchars($token);
        return $redis->WYget($token);
    }

    /**
     * 获取h5支付的token
     * @param $channelId
     * @param $token
     * @return mixed
     */
    public function getEasyPaymentToken($channelId, $token)
    {
        $redis = $this->redis($channelId, RESOURCE_STATIC);
        $token = "pay_h5:" . htmlspecialchars($token);
        return $redis->WYget($token);
    }

    /**
     * VIP折扣卡格瓦拉支付支付传处理
     * @param $channelId
     * @param $params
     */
    public function getVipPayLinkForGewara($channelId, &$params)
    {
        $token = md5(date("YmdHis") . uniqid(mt_rand(10000, 99999)));
        $redis = $this->redis($channelId, RESOURCE_STATIC);
        $key = "pay_h5:" . $token;
        $payParameter = json_encode($params['data']);
        $params['data']['easy_payment_link'] = Utils::getHost() . "/wap-payment-vipcard/gewara/{$token}?channelId={$channelId}";
        $redis->WYset($key, $payParameter, 600);
    }

    /**
     * VIP折扣卡京东支付处理
     * @param $channelId
     * @param $params
     */
    public function getVipPayLinkForJd($channelId, &$params)
    {
        $token = md5(date("YmdHis") . uniqid(mt_rand(10000, 99999)));
        $redis = $this->redis($channelId, RESOURCE_STATIC);
        $key = "pay_h5:" . $token;
        $payParameter = json_encode($params['data']);
        $params['data']['easy_payment_link'] = Utils::getHost() . "/wap-payment-vipcard/jd/{$token}?channelId={$channelId}";
        $redis->WYset($key, $payParameter, 600);
    }

}