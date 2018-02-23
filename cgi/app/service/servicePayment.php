<?php
/**
 * Created by PhpStorm.
 * User: panyuanxin
 * Date: 16/7/28
 * Time: 上午11:17
 */

namespace sdkService\service;

class servicePayment extends serviceBase
{
    /**
     * 格式化支付串
     * 针对格瓦拉和京东支付这种H5页面需要渲染出一个中间页面帮助客户端发送POST请求
     * @param $channelId
     * @param $response
     * @param $payType
     */
    public function formatPaymentReturnParamsForApp(array $arrInput = [])
    {
        $channelId = self::getParam($arrInput, 'channelId');
        $response = self::getParam($arrInput, 'response');
        $payType = self::getParam($arrInput, 'payType');
        //京东支付
        if ($payType == 12) {
            $this->model("Payment")->getPayLinkForJdPayment($channelId, $response);
        } elseif (!in_array($payType, [1, 2, 7, 17])) {
            $this->model("Payment")->getPayLinkForGewara($channelId, $response);
        }
        return compact('channelId', 'response', 'payType');
    }

    /**
     * 格式化支付串
     * 卡bin支付页面
     * @param $channelId 渠道号
     * @param $response 返回值
     * @param $payType   支付类型
     * @param $payParams 发起支付的参数
     */
    public function formatPaymentCardbin(array $arrInput = [])
    {
        $payParams = self::getParam($arrInput, 'payParams');
        $response = self::getParam($arrInput, 'response');
        $this->model("Payment")->saveCardBinParams($payParams, $response);
        return $response;
    }


    /**
     * 获取H5 支付串参数
     * @param $channelId 渠道号
     * @param $token 临时生成的token
     * @param $bankCardNo 等待支付的银行卡号
     * @return mixed
     */
    public function binPaymentToken(array $arrInput = [])
    {
        $channelId = self::getParam($arrInput, 'channelId');
        $token = self::getParam($arrInput, 'token');
        $bankCardNo = self::getParam($arrInput, 'bankCardNo');

        $payParams = $this->model("Payment")->getBinPaymentToken($channelId, $token);
        $payParams = json_decode($payParams, true);
        if (!$payParams) {
            $return['ret'] = -1;
            $return['sub'] = -1;
            $return['msg'] = "支付信息获取失败！";
            return $return;
        }
        //获取营销信息并且将用户卡号放入营销信息之中
        $disInfo = json_decode($payParams['disInfo'], true);
        if ($disInfo) {
            $disInfo['bankCardNo'] = $bankCardNo;
            $payParams['disInfo'] = json_encode($disInfo);
        }
        //缓存五分钟订单-银行卡号
        $cardNoParams = [
            'channelId' => $channelId,
            'bankCardNo' => $bankCardNo,
            'orderId' => $payParams['orderId'],
        ];

        $payResult = $this->service("Pay")->payV3($payParams);
        if ($payResult['ret'] == 0) {
            if (!empty($bankCardNo) && !empty($payParams['orderId'])) {
                $this->model("Payment")->saveOrderCardNo($cardNoParams);
            }
        }
        return $payResult;
    }

    public function getBinPaymentOrderCardInfo(array $arrInput = [])
    {
        $params['channelId'] = $arrInput['channelId'];
        $params['orderId'] = $arrInput['orderId'];
        return $this->model("Payment")->getOrderCardNo($params);
    }

    /**
     * 通过token兑换H5支付信息
     * @param $channelId
     * @param $token
     * @return mixed
     */
    public function getEasyPaymentToken(array $arrInput = [])
    {
        $channelId = self::getParam($arrInput, 'channelId');
        $token = self::getParam($arrInput, 'token');
        return $this->model("payment")->getEasyPaymentToken($channelId, $token);
    }

    /**
     * 格式化vip会员卡支付的信息
     * @param $channelId
     * @param $response
     * @param $payType
     */
    public function paymentLinkForVip($channelId, &$response, $payType)
    {
        if (in_array($payType, \wyCupboard::$config['gawara_pay_type'])) {
            $this->model("Payment")->getVipPayLinkForGewara($channelId, $response);
        } elseif ($payType == 12) {
            $this->model("Payment")->getVipPayLinkForJd($channelId, $response);
        }
    }

    /**
     * 格瓦拉格式化vip会员卡支付的信息
     * @param $channelId
     * @param $response
     * @param $payType
     */
    public function gwlPaymentLinkForVip(array $arrInput = [])
    {
        $channelId = self::getParam($arrInput, 'channelId');
        $response = self::getParam($arrInput, 'response');
        $payType = self::getParam($arrInput, 'payType');
        if ($payType == 12) {
            $this->model("Payment")->getVipPayLinkForJd($channelId, $response);
        } else {
            $this->model("Payment")->getVipPayLinkForGewara($channelId, $response);
        }
        return compact('channelId', 'response', 'payType');
    }
}