<?php

namespace sdkService\service;

use sdkService\helper\Net;
use sdkService\helper\Utils;


/**
 * Class servicePay
 *
 * @package app\service
 */
class servicePay extends serviceBase
{

    /**
     * 获取微信支付串, 供前端拿到返回结果, 吊起微信支付
     *
     * @param string openId             用户openId
     * @param intval bank               银行编码,未使用字段,可传固定值0
     * @param string visitor            访问者字段,未使用字段,可传固定值: "dianying_web"
     * @param string cardNo             礼品卡编号,如: W15000023000000001
     * @param intval channelId          公众号缩写 电影票:3, IOS:8, Android:9
     * @param intval userId             用户Id,如果没有,可传0, 需要打通账号体系来支撑
     * @param string salePlatformType   售卖平台, 1表示公众号, 2表示渠道, 传2
     * @param string subChannelId       渠道应用子来源，10位数字
     * @param intval appId              登录方式，认证体系APPID：0：通用，1为微信公众号，2为手机号...
     * @param intval tempOrderId        锁座完成的临时订单编号
     * @param intval bonusId            使用的红包编号
     * @param intval discountId         优惠编号,优惠比如: 立减, 减至
     * @param intval presellId          预售券Id
     * @param intval payReduResId        支付方式优惠
     * @param string payType            未使用字段, 可以传: weixin_h5
     * @param intval subsrc             统计来源, 未使用字段, 可以传: 30610000
     * @param intval phone              手机号
     * @param string cardId             朋友的券参数：朋友的券cardid
     * @param intval encyCode           朋友的券参数：朋友的券加密code
     *
     * @return array
     */
    public function payOrderWeixin(array $arrInput = [])
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['phone'] = self::getParam($arrInput, 'phone');
        $arrSendParams['temp_order_id'] = self::getParam($arrInput, 'tempOrderId');
        $arrSendParams['iAppId'] = self::getParam($arrInput, 'appId');
        $arrSendParams['publicSignalShort'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['userId'] = self::getParam($arrInput, 'userId');
        $arrSendParams['cardNo'] = self::getParam($arrInput, 'cardNo');
        $arrSendParams['platForm'] = self::getParam($arrInput, 'iPlatForm');
        $arrSendParams['iSubChannelId'] = self::getParam($arrInput, 'subChannelId');
        //立减减至
        $arrSendParams['iBonusValueID'] = self::getParam($arrInput, 'iBonusValueID');
        //红包
        $arrSendParams['iBonusID'] = self::getParam($arrInput, 'iBonusID');
        //选坐券
        $arrSendParams['iPresellID'] = self::getParam($arrInput, 'iPresellID');
        $arrSendParams['payReduResId'] = self::getParam($arrInput, 'payReduResId');
        $arrSendParams['tradeType'] = self::getParam($arrInput, 'payType');
        $arrSendParams['subsrc'] = self::getParam($arrInput, 'subsrc', '30610000');
        $arrSendParams['cardId'] = self::getParam($arrInput, 'cardId');
        $arrSendParams['encyCode'] = self::getParam($arrInput, 'encyCode');
        $arrSendParams['visitor'] = self::getParam($arrInput, 'visitor');
        //小吃卖品
        if (isset($arrInput['snackId']) && isset($arrInput['snackNum'])) {
            $arrSendParams['snackId'] = self::getParam($arrInput, 'snackId');
            $arrSendParams['snackNum'] = self::getParam($arrInput, 'snackNum');
        }

        //增加判断新老用户参数
        $arrNewUserRes = $this->service('user')->checkNewuser(['openId' => $arrSendParams['openId'], 'idType' => 0]);
        $arrSendParams['isNew'] = (isset($arrNewUserRes['ret']) && ($arrNewUserRes['ret'] == 0)) ? $arrNewUserRes['data']['isNew'] : 0;
        //判断是否优惠降级
        if (\wepiao::$config['demote']) {
            $arrSendParams['status'] = 1;
        }

        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $arrReturn = $this->http(JAVA_API_PAY_ORDER_WEINXIN, $httpParams);

        return $arrReturn;
    }

    /**
     * 购买兑换券, 获取购买支付串
     *
     * @param intval channelId          公众号缩写 电影票:3, IOS:8, Android:9
     * @param string openId             用户openId
     * @param intval phone              手机号
     * @param intval bank               银行类型, 无用, 传0即可, 如果不传, 也会自动用0作为默认值
     * @param string subsrc             统计来源, 未使用字段, 可以传: 30610000
     * @param intval ticketType         票类, 1电子票, 2兑换券, 所以这里肯定得传2了, 因为买兑换券
     * @param intval category           来源分类, 无用, 传默认1就可以
     * @param intval userType           用户类型, 1:微信用户, 2:其他.
     * @param intval payItem            购买兑换券的数量, 格式如: "000008D2BACB0D0A227200051FABFBFF000006E4*1"
     * @param intval cinemaId           影院编号
     * @param intval idType             微信为11,获取新老用户使用此参数
     *
     * @param intval paySource          支付来源, 或渠道来源, 如: 115
     * @param string salePlatformType   售卖平台, 1表示公众号, 2表示渠道, 非微影院都传2
     * @param intval userId             用户userId
     * @param intval appId              登录方式，认证体系APPID：0：通用，1为微信公众号，2为手机号...
     *
     * @return array
     */
    public function payExchangeWeixin(array $arrInput = [])
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['UserId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['iChannelNo'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['iCinemaNo'] = self::getParam($arrInput, 'cinemaId');
        $arrSendParams['channelId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['iMobilePhone'] = self::getParam($arrInput, 'phone');
        $arrSendParams['iBankType'] = self::getParam($arrInput, 'bank', 0);
        $arrSendParams['vm_pvsrc'] = self::getParam($arrInput, 'subsrc', '30610000');
        $arrSendParams['iTicketType'] = self::getParam($arrInput, 'ticketType', 2);
        $arrSendParams['category'] = self::getParam($arrInput, 'category', 1);
        $arrSendParams['uin_type'] = self::getParam($arrInput, 'userType', 1);
        $arrSendParams['sPayItem'] = self::getParam($arrInput, 'payItem');
        $arrSendParams['iPaySource'] = self::getParam($arrInput, 'paySource', 1);
        $arrSendParams['salePlatformType'] = self::getParam($arrInput, 'salePlatformType');
        $arrSendParams['userId'] = self::getParam($arrInput, 'userId');
        $arrSendParams['appId'] = self::getParam($arrInput, 'appId');
        $arrSendParams['isNew'] = self::getParam($arrInput, 'isNew', '');
        $arrSendParams['idType'] = self::getParam($arrInput, 'idType', '11');
        //因为要调用 私有方法: $this->payExchange,所以,原始的自定义参数还得保留,继续作为原来参数传入进去
        $arrSendParams = array_merge($arrSendParams, $arrInput);
        //判断新老用户
        $arrNewUserRes = $this->service('user')->checkNewuser($arrSendParams);
        $arrSendParams['isNew'] = (isset($arrNewUserRes['ret']) && ($arrNewUserRes['ret'] == 0)) ? $arrNewUserRes['data']['isNew'] : 0;
        //判断手机号,如果前端没有传手机号,则获取一次
        if (empty($arrSendParams['iMobilePhone'])) {
            $arrNewUserRes = $this->service('user')->getUserPhone($arrSendParams);
            $arrSendParams['iMobilePhone'] = $arrSendParams['phone'] = (isset($arrNewUserRes['ret']) && ($arrNewUserRes['ret'] == 0)) ? $arrNewUserRes['data']['mobileNo'] : '';
        }
        $arrReturn = $this->payExchange($arrSendParams);

        return $arrReturn;
    }

    /**
     * 购买兑换券, 获取购买支付串
     *
     * @note    获取支付串的原始方法, 私有, 尽可内部调用
     * @access  private
     *
     * @param intval channelId          公众号缩写 电影票:3, IOS:8, Android:9
     * @param string openId             用户openId
     * @param intval phone              手机号
     * @param intval bank               银行类型, 无用, 传0即可, 如果不传, 也会自动用0作为默认值
     * @param string subsrc             统计来源, 未使用字段, 可以传: 30610000
     * @param intval ticketType         票类, 1电子票, 2兑换券, 所以这里肯定得传2了, 因为买兑换券
     * @param intval category           来源分类, 无用, 传默认1就可以
     * @param intval userType           用户类型, 1:微信用户, 2:其他.
     * @param intval payItem            购买兑换券的数量, 格式如: "000008D2BACB0D0A227200051FABFBFF000006E4*1"
     * @param intval isNew              是否为新用户
     * @param intval cinemaId           影院Id
     */
    protected function payExchange(array $arrInput = [])
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['UserId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['iChannelNo'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['iMobilePhone'] = self::getParam($arrInput, 'phone');
        $arrSendParams['iBankType'] = self::getParam($arrInput, 'bank', 0);
        $arrSendParams['vm_pvsrc'] = self::getParam($arrInput, 'subsrc', '30610000');
        $arrSendParams['iTicketType'] = self::getParam($arrInput, 'ticketType', 2);
        $arrSendParams['category'] = self::getParam($arrInput, 'category', 1);
        $arrSendParams['uin_type'] = self::getParam($arrInput, 'userType', 1);
        $arrSendParams['sPayItem'] = self::getParam($arrInput, 'payItem');
        $arrSendParams['iPaySource'] = self::getParam($arrInput, 'paySource', 1);
        $arrSendParams['isNew'] = self::getParam($arrInput, 'isNew', 0);
        $arrSendParams['iCinemaID'] = self::getParam($arrInput, 'cinemaId');
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $arrReturn = $this->http(JAVA_API_EXCHANGE_BUY, $httpParams);

        return $arrReturn;
    }

    /**
     * @note    获取财付通(手Q支付串)
     *
     * @param string openId 用户openId
     * @param string phone 用户支付填写的手机号
     * @param string orderId 支付的订单Id
     * @param string publicSignalShort 渠道Id
     * @param string appId    认证体系APPID(0：通用，1：微信公众号，2：手机号…)
     * @param string userId 用户Userid
     * @param string bonusId 红包及值的列表，红包间用“,”分隔，红包ID与值与“:”分隔。
     *               如1:300,5:500,22:100
     * @param string bonusValueId 立减红包低值ID及值的列表，立减红包间用“,”分隔，低值ID与值与“:”分隔。
     *               如1:300,5:500,22:100
     * @param string presellId    用户的预售码id列表，用“,”分隔
     * @param string platForm 应用平台（1：电影票，2：演出票）
     * @param string cardno 礼品卡
     * @param string payReduResId 支付方式优惠id
     * @param string tradeType 交易类型
     * @param string status 是否营销降级
     */
    public function tenPayment(array $arrInput = [])
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['phone'] = self::getParam($arrInput, 'phone');
        $arrSendParams['orderId'] = self::getParam($arrInput, 'orderId');
        $arrSendParams['publicSignalShort'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['appId'] = self::getParam($arrInput, 'appId');
        $arrSendParams['platForm'] = self::getParam($arrInput, 'platForm');
        $arrSendParams['tradeType'] = self::getParam($arrInput, 'tradeType');
        $arrSendParams['status'] = self::getParam($arrInput, 'status');
        $arrSendParams['userId'] = self::getParam($arrInput, 'userId');
        //营销降级
        $arrSendParams['status'] = self::getParam($arrInput, 'status');
        //红包
        $arrSendParams['bonusId'] = self::getParam($arrInput, 'bonusId');
        $arrSendParams['bonusValueId'] = self::getParam($arrInput, 'bonusValueId');
        $arrSendParams['presellId'] = self::getParam($arrInput, 'presellId');
        $arrSendParams['payReduResId'] = self::getParam($arrInput, 'payReduResId');
        $arrSendParams['cardno'] = self::getParam($arrInput, 'cardno');
        //小吃卖品
        if (isset($arrInput['snackId']) && isset($arrInput['snackNum'])) {
            $arrSendParams['snackId'] = self::getParam($arrInput, 'snackId');
            $arrSendParams['snackNum'] = self::getParam($arrInput, 'snackNum');
        }
        //判断是否优惠降级
        if (\wepiao::$config['demote']) {
            $arrSendParams['status'] = 1;
        }
        //增加判断新老用户参数
        $arrNewUserRes = $this->service('user')->checkNewuser(['openId' => $arrSendParams['openId'], 'idType' => 0]);
        $arrSendParams['isNew'] = (isset($arrNewUserRes['ret']) && ($arrNewUserRes['ret'] == 0)) ? $arrNewUserRes['data']['isNew'] : 0;
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $arrReturn = $this->http(JAVA_API_PAY_ORDER_TENPAY, $httpParams);

        return $arrReturn;
    }

    /**
     * 支付宝支付串获取
     *
     * @param string openId 用户openId
     * @param string phone 用户手机号
     * @param string orderId 订单号
     * @param string channelId 渠道
     * @param string appId APPID
     * @param string platForm 平台
     * @param string tradeType (支付串类型,如APP)
     * @param string userId 用户ID
     * @param string bonusId 红包ID
     * @param string bonusValueId 优惠活动ID
     * @param string presellId 预售券编号
     * @param string payReduResId 支付方式优惠
     * @param string cardno 卡号
     */
    public function aliPayment(array $arrInput = [])
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['phone'] = self::getParam($arrInput, 'phone');
        $arrSendParams['orderId'] = self::getParam($arrInput, 'orderId');
        $arrSendParams['publicSignalShort'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['appId'] = self::getParam($arrInput, 'appId');
        $arrSendParams['platForm'] = self::getParam($arrInput, 'platForm');
        $arrSendParams['tradeType'] = self::getParam($arrInput, 'tradeType');
        $arrSendParams['status'] = self::getParam($arrInput, 'status');
        $arrSendParams['userId'] = self::getParam($arrInput, 'userId');
        //红包
        $arrSendParams['bonusId'] = self::getParam($arrInput, 'bonusId');
        $arrSendParams['bonusValueId'] = self::getParam($arrInput, 'bonusValueId');
        $arrSendParams['presellId'] = self::getParam($arrInput, 'presellId');
        $arrSendParams['payReduResId'] = self::getParam($arrInput, 'payReduResId');
        $arrSendParams['cardno'] = self::getParam($arrInput, 'cardno');
        //小吃卖品
        if (isset($arrInput['snackId']) && isset($arrInput['snackNum'])) {
            $arrSendParams['snackId'] = self::getParam($arrInput, 'snackId');
            $arrSendParams['snackNum'] = self::getParam($arrInput, 'snackNum');
        }
        //判断是否优惠降级
        if (\wepiao::$config['demote']) {
            $arrSendParams['status'] = 1;
        }
        //增加判断新老用户参数
        $arrNewUserRes = $this->service('user')->checkNewuser(['openId' => $arrSendParams['openId'], 'idType' => 0]);
        $arrSendParams['isNew'] = (isset($arrNewUserRes['ret']) && ($arrNewUserRes['ret'] == 0)) ? $arrNewUserRes['data']['isNew'] : 0;
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $arrReturn = $this->http(JAVA_API_PAY_ORDER_ALIPAY, $httpParams);

        return $arrReturn;
    }

    /**
     * 银联苹果支付串获取
     *
     * @param string openId 用户openId
     * @param string phone 用户手机号
     * @param string orderId 订单号
     * @param string channelId 渠道
     * @param string appId APPID
     * @param string platForm 平台
     * @param string tradeType (支付串类型,如APP)
     * @param string userId 用户ID
     * @param string bonusId 红包ID
     * @param string bonusValueId 优惠活动ID
     * @param string presellId 预售券编号
     * @param string payReduResId 支付方式优惠
     * @param string cardno 卡号
     */
    public function appleUnionpay(array $arrInput = [])
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['phone'] = self::getParam($arrInput, 'phone');
        $arrSendParams['orderId'] = self::getParam($arrInput, 'orderId');
        $arrSendParams['publicSignalShort'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['appId'] = self::getParam($arrInput, 'appId');
        $arrSendParams['platForm'] = self::getParam($arrInput, 'platForm');
        $arrSendParams['tradeType'] = self::getParam($arrInput, 'tradeType');
        $arrSendParams['status'] = self::getParam($arrInput, 'status');
        $arrSendParams['userId'] = self::getParam($arrInput, 'userId');
        //红包
        $arrSendParams['bonusId'] = self::getParam($arrInput, 'bonusId');
        $arrSendParams['bonusValueId'] = self::getParam($arrInput, 'bonusValueId');
        $arrSendParams['presellId'] = self::getParam($arrInput, 'presellId');
        $arrSendParams['payReduResId'] = self::getParam($arrInput, 'payReduResId');
        $arrSendParams['cardno'] = self::getParam($arrInput, 'cardno');
        //小吃卖品
        if (isset($arrInput['snackId']) && isset($arrInput['snackNum'])) {
            $arrSendParams['snackId'] = self::getParam($arrInput, 'snackId');
            $arrSendParams['snackNum'] = self::getParam($arrInput, 'snackNum');
        }
        //判断是否优惠降级
        if (\wepiao::$config['demote']) {
            $arrSendParams['status'] = 1;
        }
        //增加判断新老用户参数
        $arrNewUserRes = $this->service('user')->checkNewuser(['openId' => $arrSendParams['openId'], 'idType' => 0]);
        $arrSendParams['isNew'] = (isset($arrNewUserRes['ret']) && ($arrNewUserRes['ret'] == 0)) ? $arrNewUserRes['data']['isNew'] : 0;
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $arrReturn = $this->http(JAVA_API_PAY_ORDER_APPLEPAY, $httpParams);

        return $arrReturn;
    }

    /**
     * 京东支付支付串获取
     *
     * @param string openId 用户openId
     * @param string phone 用户手机号
     * @param string orderId 订单号
     * @param string channelId 渠道
     * @param string appId APPID
     * @param string platForm 平台
     * @param string tradeType (支付串类型,如APP)
     * @param string userId 用户ID
     * @param string bonusId 红包ID
     * @param string bonusValueId 优惠活动ID
     * @param string presellId 预售券编号
     * @param string payReduResId 支付方式优惠
     * @param string cardno 卡号
     */
    public function jdPayment(array $arrInput = [])
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['phone'] = self::getParam($arrInput, 'phone');
        $arrSendParams['orderId'] = self::getParam($arrInput, 'orderId');
        $arrSendParams['publicSignalShort'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['appId'] = self::getParam($arrInput, 'appId');
        $arrSendParams['platForm'] = self::getParam($arrInput, 'platForm');
        $arrSendParams['tradeType'] = self::getParam($arrInput, 'tradeType');
        $arrSendParams['userId'] = self::getParam($arrInput, 'userId');
        //红包
        $arrSendParams['bonusId'] = self::getParam($arrInput, 'bonusId');
        $arrSendParams['bonusValueId'] = self::getParam($arrInput, 'bonusValueId');
        $arrSendParams['presellId'] = self::getParam($arrInput, 'presellId');
        $arrSendParams['payReduResId'] = self::getParam($arrInput, 'payReduResId');
        $arrSendParams['cardno'] = self::getParam($arrInput, 'cardno');
        //小吃卖品
        if (isset($arrInput['snackId']) && isset($arrInput['snackNum'])) {
            $arrSendParams['snackId'] = self::getParam($arrInput, 'snackId');
            $arrSendParams['snackNum'] = self::getParam($arrInput, 'snackNum');
        }
        //判断是否优惠降级
        if (\wepiao::$config['demote']) {
            $arrSendParams['status'] = 1;
        }
        //增加判断新老用户参数
        $arrNewUserRes = $this->service('user')->checkNewuser(['openId' => $arrSendParams['openId'], 'idType' => 0]);
        $arrSendParams['isNew'] = (isset($arrNewUserRes['ret']) && ($arrNewUserRes['ret'] == 0)) ? $arrNewUserRes['data']['isNew'] : 0;
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $arrReturn = $this->http(JAVA_API_PAY_ORDER_JDPAYMENT, $httpParams);

        return $arrReturn;
    }

    /**
     * 获取微信支付串, 供前端拿到返回结果, 吊起微信支付 V1版 支持小吃卖品
     *
     * @param string openId             用户openId
     * @param string phone              用户电话
     * @param string orderId            订单号
     * @param string channelId          渠道号
     * @param string tradeType          支付串类型 JSAPI|APP|NATIVE
     * @param string snackId            卖品ID
     * @param string snackNum           卖品数量
     * @param string disInfo            优惠信息
     *
     * @return array
     */
    public function payOrderWeixinV1(array $arrInput = [])
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['phone'] = self::getParam($arrInput, 'phone');
        $arrSendParams['orderId'] = self::getParam($arrInput, 'orderId');
        $arrSendParams['channelId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['tradeType'] = self::getParam($arrInput, 'tradeType');
        $arrSendParams['snackId'] = self::getParam($arrInput, 'snackId', '');
        $arrSendParams['snackNum'] = self::getParam($arrInput, 'snackNum', '');
        $arrSendParams['disInfo'] = self::getParam($arrInput, 'disInfo', '');
        //判断是否优惠降级
        if (\wepiao::$config['demote']) {
            $arrSendParams['marketingStatus'] = 1;
        }
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $arrReturn = $this->http(JAVA_API_PAY_ORDER_WEINXIN_V1, $httpParams);

        return $arrReturn;
    }

    /**
     * 格瓦拉-银行支付接口
     *
     * @param string openId 用户openId
     * @param string phone  用户电话
     * @param string orderId 订单号
     * @param string publicSignalShort  渠道号
     * @param string returnUrl  前端回调地址
     * @param string payType  支付方式
     * @param string appId  认证体系APPID(0：通用，1：微信公众号，2：手机号…)
     * @param string userId  用户Userid
     * @param string bonusId  红包及值的列表
     * @param string bonusValueId  立减红包低值ID及值的列表
     * @param string presellId  选坐券
     * @param string platForm 应用平台（1：电影票，2：演出票）
     * @param string cardno  礼品卡
     * @param string tradeType 支付方式（JSAPI、APP） 默认使用JSAPI
     *
     * @return array
     */
    public function gewaraPayment($arrInput)
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['phone'] = self::getParam($arrInput, 'phone');
        $arrSendParams['orderId'] = self::getParam($arrInput, 'orderId');
        $arrSendParams['publicSignalShort'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['returnUrl'] = self::getParam($arrInput, 'returnUrl');
        $arrSendParams['payType'] = self::getParam($arrInput, 'payType');
        $arrSendParams['appId'] = self::getParam($arrInput, 'appId');
        $arrSendParams['userId'] = self::getParam($arrInput, 'userId');
        $arrSendParams['platForm'] = self::getParam($arrInput, 'platForm');
        $arrSendParams['tradeType'] = self::getParam($arrInput, 'tradeType');
        $arrSendParams['userId'] = self::getParam($arrInput, 'userId');
        $arrSendParams['tradeType'] = self::getParam($arrInput, 'tradeType');
        //红包
        $arrSendParams['bonusId'] = self::getParam($arrInput, 'bonusId');
        $arrSendParams['bonusValueId'] = self::getParam($arrInput, 'bonusValueId');
        $arrSendParams['presellId'] = self::getParam($arrInput, 'presellId');
        $arrSendParams['payReduResId'] = self::getParam($arrInput, 'payReduResId');
        $arrSendParams['cardno'] = self::getParam($arrInput, 'cardno');
        //小吃卖品
        if (isset($arrInput['snackId']) && isset($arrInput['snackNum'])) {
            $arrSendParams['snackId'] = self::getParam($arrInput, 'snackId');
            $arrSendParams['snackNum'] = self::getParam($arrInput, 'snackNum');
        }
        //判断是否优惠降级
        if (\wepiao::$config['demote']) {
            $arrSendParams['status'] = 1;
        }

        //增加判断新老用户参数
        $arrNewUserRes = $this->service('user')->checkNewuser(['openId' => $arrSendParams['openId'], 'idType' => 0]);
        $arrSendParams['isNew'] = (isset($arrNewUserRes['ret']) && ($arrNewUserRes['ret'] == 0)) ? $arrNewUserRes['data']['isNew'] : 0;
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $arrReturn = $this->http(JAVA_API_PAY_ORDER_GEWARA, $httpParams);

        return $arrReturn;
    }

    /**
     * 新版全支付接口（全支付V1(微信, 支付宝, 财付通, 京东, 银联, 格瓦拉)）
     *
     * @param string                                       openId             Y   用户openId
     * @param string                                       orderId            Y   锁座完成的临时订单编号
     * @param string                                       phone              Y   手机号
     * @param string                                       channelId          Y   公众号缩写 电影票:3, IOS:8, Android:9
     * @param string                                       payType            Y   支付方式(1:微信, 2:支付宝, 7:财付通,12:京东支付, 17:银联支付, 99:预算, 其他格瓦拉)
     * @param string                                       marketingStatus    N   营销状态(如果该字段有值，不调用营销)
     * @param string                                       tradeType          N   支付方式(JSAPI、APP)，默认为JSAPI
     * @param string                                       snackId            N   卖品Id
     * @param string                                       snackNum           N   卖品数量
     * @param string                                       showUrl            N   商品展示网址（支付宝支付专用）
     * @param string                                       returnUrl          N   页面跳转同步通知页面路径（支付宝, 格瓦拉公用）
     * @param string                                       disInfo            N   营销活动JSON串，如果没有营销数据，就不要传。它目前格式如下：
     * @param string                                       smsToken           N   手机号token（用于解密出领红包人的手机号）
     *                                                     ——————————————————
     *                                                     useIp       string  Y   使用IP（注意，只要有disInfo内容，里边就必须有userIp，不过这个service自己加了，不用业务传）
     *                                                     bnsId       string  N   红包编号
     *                                                     reduId      string  N   立减减至ID
     *                                                     presellId   string  N   选座券编号
     *                                                     payReduId   string  N   支付方式优惠ID
     *                                                     acctNo      string  N   第三方支付账号
     *                                                     bankCardNo  string  N   银行卡卡号
     *                                                     vCardNo     string  N   V卡编号
     *                                                     cardId      string  N   朋友的券卡券ID
     *                                                     encyCode    string  N   朋友的券加密code
     *                                                     ——————————————————
     * @param string                                       smsCode             N   短信码（当需要用户手机号校验的时候传入）
     *
     * @return array
     */
    public function pay(array $arrInput = [])
    {
        //参数处理
        $return = self::getStOut();
        $return['data'] = [];
        $arrSendParams = [];
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['orderId'] = self::getParam($arrInput, 'orderId');
        $arrSendParams['phone'] = self::getParam($arrInput, 'phone');
        $arrSendParams['channelId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['payType'] = self::getParam($arrInput, 'payType');
        $arrSendParams['tradeType'] = self::getParam($arrInput, 'tradeType');
        $arrSendParams['snackId'] = self::getParam($arrInput, 'snackId');
        $arrSendParams['snackNum'] = self::getParam($arrInput, 'snackNum');
        $arrSendParams['showUrl'] = self::getParam($arrInput, 'showUrl');
        $arrSendParams['returnUrl'] = self::getParam($arrInput, 'returnUrl');
        $arrSendParams['disInfo'] = self::getParam($arrInput, 'disInfo');
        $arrSendParams['goodsInfoList'] = self::getParam($arrInput, 'goodsInfoList');
        $arrSendParams['smsCode'] = self::getParam($arrInput, 'smsCode');
        $arrSendParams['smsToken'] = self::getParam($arrInput, 'smsToken');
        //参数校验
        if (empty($arrSendParams['openId']) || empty($arrSendParams['orderId']) || empty($arrSendParams['phone']) || empty($arrSendParams['channelId']) || empty($arrSendParams['payType'])) {
            $return['ret'] = $return['sub'] = -1000;
            $return['msg'] = 'param error';

            return $return;
        }
        $arrDisInfo = [];
        if (!empty($arrSendParams['disInfo'])) {
            $arrDisInfo = json_decode($arrSendParams['disInfo'], true);
            $arrDisInfo['isMobileChk'] = 0;
        }

        //用户传了短信码，则认为需要短信验证
        if (!empty($arrSendParams['smsCode']) && !empty($arrSendParams['smsToken'])) {
            //从token中解密手机号出来
            $arrPhoneNumberRes = $this->service('Sms')->decryptPhone([
                'token' => $arrSendParams['smsToken'],
                'channelId' => $arrSendParams['channelId'],
            ]);
            $strPhoneNum = (isset($arrPhoneNumberRes['ret']) && ($arrPhoneNumberRes['ret'] == 0) && !empty($arrPhoneNumberRes['data']['phone'])) ? $arrPhoneNumberRes['data']['phone'] : '';
            //用户传了验证码，进行验证
            $arrVerifyRes = $this->service('Sms')->verifySmsCode([
                'phone_number' => $strPhoneNum,
                'code' => $arrSendParams['smsCode'],
                'channelId' => $arrSendParams['channelId'],
            ]);
            //验证失败的情况
            if (!isset($arrVerifyRes['errorcode']) || ($arrVerifyRes['errorcode'] != 0)) {
                $return['ret'] = $return['sub'] = -1002;
                $return['msg'] = '验证码输入错误';

                return $return;
            }
            //验证成功
            //将验证成功标识传到后端服务
            $arrDisInfo['isMobileChk'] = 1;

        }

        //补充参数
        //1、marketingStatus设置：判断是否没有使用优惠，或开启了优惠降级（如果开启优惠降低，调用Java接口的时候，需要设置参数marketingStatus为1，意思是Java侧不调用其他后端优惠接口）
        $iDemote = !empty(\wepiao::$config['demote']) ? \wepiao::$config['demote'] : 0;
        //降级了,或者票优惠与卖品优惠都为空的话,就传降级参数,告知订单中心,不要调用营销中心了
        if (!empty($iDemote) || (empty($arrDisInfo) && empty($arrSendParams['goodsInfoList']))) {
            $arrSendParams['marketingStatus'] = 1;
        }
        //2、调用者IP地址
        if (!empty($arrDisInfo)) {
            $strIp = Net::getRemoteIp();
            $arrDisInfo['useIp'] = $strIp;
        }
        $arrSendParams['disInfo'] = !empty($arrDisInfo) ? json_encode($arrDisInfo) : '';

        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $res = $this->http(JAVA_API_PAY_ALL_TYPE, $httpParams);

        //判断是否需要对当前用户开启短信验证手机号（ret为-10000911，msg为手机号）
        $url = $strPhone = $strSmsToken = '';
        if (!empty($res['ret'])) {
            $return['ret'] = $res['ret'];
            $return['sub'] = $res['sub'];
            $return['msg'] = is_numeric($res['msg']) ? 'failure' : $res['msg'];     //不能返回领取红包人的真实手机号
        }
        if (!empty($res['sub']) && ($res['sub'] == -10000911) && !empty($res['msg']) && is_numeric($res['msg'])) {
            $arrVerifyUrl = $this->service('SlideVerify')->getH5Url();
            $url = !empty($arrVerifyUrl['data']['url']) ? $arrVerifyUrl['data']['url'] : '';
            //返回给用户的手机号修改为 186****9999 的形式
            $strPhone = $res['msg'];
            $arrTokenRes = $this->service('Sms')->encryptPhone(['channelId' => $arrSendParams['channelId'], 'phoneNumber' => $strPhone]);
            if (isset($arrTokenRes['ret']) && ($arrTokenRes['ret'] == 0) && !empty($arrTokenRes['data']['token'])) {
                $strSmsToken = $arrTokenRes['data']['token'];
            }
        }

        $return['data']['paymentInfo'] = !empty($res['data']) ? $res['data'] : new \stdClass();
        $return['data']['slideUrl'] = $url;
        $return['data']['phone'] = !empty($strPhone) ? substr_replace($strPhone, '****', 3, 4) : '';
        $return['data']['smsToken'] = $strSmsToken;

        //透传非0的sub做卡bin验证等等
        if (!empty($res['sub'])) {
            $return['sub'] = $res['sub'];
            $return['data']['paymentInfo'] = new \stdClass();
        }

        return $return;
    }

    /**
     * 单独购买卖品发起支付
     *
     *  openId
     * phone
     * channelId
     * payType
     * cinemaNo
     * orderSource
     * cityNo
     * tradeType
     * spm
     * snackInfos = [
     *
     *      ['snackNum'=>4,'snackId'=>5555]
     * ]
     *
     */
    public function snack($arrInput = [])
    {
        $arrSendParams = [];
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['phone'] = self::getParam($arrInput, 'phone');
        $arrSendParams['channelId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['payType'] = self::getParam($arrInput, 'payType');
        $arrSendParams['cinemaNo'] = self::getParam($arrInput, 'cinemaNo');
        $arrSendParams['orderSource'] = self::getParam($arrInput, 'orderSource');
        $arrSendParams['cityNo'] = self::getParam($arrInput, 'cityNo');
        $arrSendParams['tradeType'] = self::getParam($arrInput, 'tradeType');
        $arrSendParams['buySource'] = self::getParam($arrInput, 'buySource');
        $arrSendParams['spm'] = self::getParam($arrInput, 'spm', $this->getSpmFromCookie());
        $arrSendParams['snackInfos'] = self::getParam($arrInput, 'snackInfos', []);;

        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
            'sendType' => 'json'
        ];
        $result = $this->http(JAVA_API_SNACKPAY, $httpParams);
        return $result;
    }

    /**
     * 影院会员卡支付
     *
     * @param       array  memberCardInfo   会员卡信息。字符串类型的json(我们自己会decode)
     * @param       string spm              大数据用来统计用的
     *
     * @param array $arrInput
     *
     * 此方法如果多传了一个是否是分享活动的字段actType=1 ,说明给折扣卡3N好友活动使用,如果actType=1,前端传过来的memberCardInfo
     * 里面会有shareCode
     */
    public function vipcard($arrInput = [])
    {
        $arrSendParams = [];
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['phone'] = self::getParam($arrInput, 'phone');
        $arrSendParams['channelId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['payType'] = self::getParam($arrInput, 'payType');
        $arrSendParams['tradeType'] = self::getParam($arrInput, 'tradeType');
        $arrSendParams['orderSource'] = self::getParam($arrInput, 'orderSource');
        $arrSendParams['cityNo'] = self::getParam($arrInput, 'cityId');
        $arrSendParams['spm'] = self::getParam($arrInput, 'spm', $this->getSpmFromCookie());
        $arrSendParams['memberCardInfo'] = self::getParam($arrInput, 'memberCardInfo', '[]');
        $arrSendParams['returnUrl'] = self::getParam($arrInput, 'returnUrl');
        $arrSendParams['errorUrl'] = self::getParam($arrInput, 'errorUrl');
        if (!empty($arrSendParams['memberCardInfo'])) {
            $arrSendParams['memberCardInfo'] = json_decode($arrSendParams['memberCardInfo'], true);
        }

        $actType = self::getParam($arrInput, 'actType');
        $isGwl = self::getParam($arrInput, 'isGwl');

        if (!empty($actType)) {
            $javaUrl = JAVA_API_VIPCARD_CARD_PAY_ACTIVE;
        } elseif ($isGwl) {
            $arrSendParams['reqSource'] = self::getParam($arrInput, 'reqSource');
            $arrSendParams['merchantCode'] = self::getParam($arrInput, 'merchantCode');
            $arrSendParams['bankCode'] = self::getParam($arrInput, 'bankCode');
            $strIp = Net::getRemoteIp();
            $arrSendParams['useIp'] = $strIp;
            $arrSendParams['interfaceVersion']='JdV2';
            $javaUrl = JAVA_API_VIPCARD_CARD_PAY_ACTIVE_V2;
        } else {
            $javaUrl = JAVA_API_VIPCARD_CARD_PAY;
        }
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
            'sendType' => 'json',
        ];
        $result = $this->http($javaUrl, $httpParams);
        return $result;
    }

    /**
     * 新版全支付接口（全支付V2(微信, 支付宝, 财付通, 京东, 银联, 格瓦拉)）
     * 注意,和 v1比, 也就是 pay 方法比, v2版本支持改签
     *
     * @param string                                       openId             Y   用户openId
     * @param string                                       orderId            Y   锁座完成的临时订单编号
     * @param string                                       phone              Y   手机号
     * @param string                                       channelId          Y   公众号缩写 电影票:3, IOS:8, Android:9
     * @param string                                       payType            Y   支付方式(1:微信, 2:支付宝, 7:财付通,12:京东支付, 17:银联支付, 99:预算, 其他格瓦拉)
     * @param string                                       marketingStatus    N   营销状态(如果该字段有值，不调用营销)
     * @param string                                       tradeType          N   支付方式(JSAPI、APP)，默认为JSAPI
     * @param string                                       snackId            N   卖品Id
     * @param string                                       snackNum           N   卖品数量
     * @param string                                       showUrl            N   商品展示网址（支付宝支付专用）
     * @param string                                       returnUrl          N   页面跳转同步通知页面路径（支付宝, 格瓦拉公用）
     * @param string                                       disInfo            N   营销活动JSON串，如果没有营销数据，就不要传。它目前格式如下：
     * @param string                                       smsToken           N   手机号token（用于解密出领红包人的手机号）
     *                                                     ——————————————————
     *                                                     useIp       string  Y   使用IP（注意，只要有disInfo内容，里边就必须有userIp，不过这个service自己加了，不用业务传）
     *                                                     bnsId       string  N   红包编号
     *                                                     reduId      string  N   立减减至ID
     *                                                     presellId   string  N   选座券编号
     *                                                     payReduId   string  N   支付方式优惠ID
     *                                                     acctNo      string  N   第三方支付账号
     *                                                     bankCardNo  string  N   银行卡卡号
     *                                                     vCardNo     string  N   V卡编号
     *                                                     cardId      string  N   朋友的券卡券ID
     *                                                     encyCode    string  N   朋友的券加密code
     *                                                     ——————————————————
     * @param string                                       smsCode             N   短信码（当需要用户手机号校验的时候传入）
     *
     * @return array
     */
    public function payV2(array $arrInput = [])
    {
        //参数处理
        $return = self::getStOut();
        $return['data'] = [];
        $arrSendParams = [];
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['orderId'] = self::getParam($arrInput, 'orderId');
        $arrSendParams['phone'] = self::getParam($arrInput, 'phone');
        $arrSendParams['channelId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['payType'] = self::getParam($arrInput, 'payType');
        $arrSendParams['tradeType'] = self::getParam($arrInput, 'tradeType');
        $arrSendParams['snackId'] = self::getParam($arrInput, 'snackId');
        $arrSendParams['snackNum'] = self::getParam($arrInput, 'snackNum');
        $arrSendParams['showUrl'] = self::getParam($arrInput, 'showUrl');
        $arrSendParams['returnUrl'] = self::getParam($arrInput, 'returnUrl');
        $arrSendParams['disInfo'] = self::getParam($arrInput, 'disInfo');
        $arrSendParams['goodsInfoList'] = self::getParam($arrInput, 'goodsInfoList');
        $arrSendParams['smsCode'] = self::getParam($arrInput, 'smsCode');
        $arrSendParams['smsToken'] = self::getParam($arrInput, 'smsToken');
        //参数校验
        if (empty($arrSendParams['openId']) || empty($arrSendParams['orderId']) || empty($arrSendParams['phone']) || empty($arrSendParams['channelId']) || empty($arrSendParams['payType'])) {
            $return['ret'] = $return['sub'] = -1000;
            $return['msg'] = 'param error';

            return $return;
        }
        $arrDisInfo = [];
        if (!empty($arrSendParams['disInfo'])) {
            $arrDisInfo = json_decode($arrSendParams['disInfo'], true);
            $arrDisInfo['isMobileChk'] = 0;
        }

        //用户传了短信码，则认为需要短信验证
        if (!empty($arrSendParams['smsCode']) && !empty($arrSendParams['smsToken'])) {
            //从token中解密手机号出来
            $arrPhoneNumberRes = $this->service('Sms')->decryptPhone([
                'token' => $arrSendParams['smsToken'],
                'channelId' => $arrSendParams['channelId'],
            ]);
            $strPhoneNum = (isset($arrPhoneNumberRes['ret']) && ($arrPhoneNumberRes['ret'] == 0) && !empty($arrPhoneNumberRes['data']['phone'])) ? $arrPhoneNumberRes['data']['phone'] : '';
            //用户传了验证码，进行验证
            $arrVerifyRes = $this->service('Sms')->verifySmsCode([
                'phone_number' => $strPhoneNum,
                'code' => $arrSendParams['smsCode'],
                'channelId' => $arrSendParams['channelId'],
            ]);
            //验证失败的情况
            if (!isset($arrVerifyRes['errorcode']) || ($arrVerifyRes['errorcode'] != 0)) {
                $return['ret'] = $return['sub'] = -1002;
                $return['msg'] = '验证码输入错误';

                return $return;
            }
            //验证成功
            //将验证成功标识传到后端服务
            $arrDisInfo['isMobileChk'] = 1;

        }

        //补充参数
        //1、marketingStatus设置：判断是否没有使用优惠，或开启了优惠降级（如果开启优惠降低，调用Java接口的时候，需要设置参数marketingStatus为1，意思是Java侧不调用其他后端优惠接口）
        $iDemote = !empty(\wepiao::$config['demote']) ? \wepiao::$config['demote'] : 0;
        //降级了,或者票优惠与卖品优惠都为空的话,就传降级参数,告知订单中心,不要调用营销中心了
        if (!empty($iDemote) || (empty($arrDisInfo) && empty($arrSendParams['goodsInfoList']))) {
            $arrSendParams['marketingStatus'] = 1;
        }
        //2、调用者IP地址
        if (!empty($arrDisInfo)) {
            $strIp = Net::getRemoteIp();
            $arrDisInfo['useIp'] = $strIp;
        }
        $arrSendParams['disInfo'] = !empty($arrDisInfo) ? json_encode($arrDisInfo) : '';

        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $res = $this->http(JAVA_API_PAY_ALL_TYPE_V2, $httpParams);

        //判断是否需要对当前用户开启短信验证手机号（ret为-10000911，msg为手机号）
        $url = $strPhone = $strSmsToken = '';
        if (!empty($res['ret'])) {
            $return['ret'] = $res['ret'];
            $return['sub'] = $res['sub'];
            $return['msg'] = is_numeric($res['msg']) ? 'failure' : $res['msg'];     //不能返回领取红包人的真实手机号
        }
        if (!empty($res['sub']) && ($res['sub'] == -10000911) && !empty($res['msg']) && is_numeric($res['msg'])) {
            $arrVerifyUrl = $this->service('SlideVerify')->getH5Url();
            $url = !empty($arrVerifyUrl['data']['url']) ? $arrVerifyUrl['data']['url'] : '';
            //返回给用户的手机号修改为 186****9999 的形式
            $strPhone = $res['msg'];
            $arrTokenRes = $this->service('Sms')->encryptPhone(['channelId' => $arrSendParams['channelId'], 'phoneNumber' => $strPhone]);
            if (isset($arrTokenRes['ret']) && ($arrTokenRes['ret'] == 0) && !empty($arrTokenRes['data']['token'])) {
                $strSmsToken = $arrTokenRes['data']['token'];
            }
        }
        $return['data']['paymentInfo'] = !empty($res['data']) ? $res['data'] : new \stdClass();
        $return['data']['slideUrl'] = $url;
        $return['data']['phone'] = !empty($strPhone) ? substr_replace($strPhone, '****', 3, 4) : '';
        $return['data']['smsToken'] = $strSmsToken;

        return $return;
    }

    /**
     * 新版全支付接口（全支付V3(微信, 支付宝, 财付通, 京东, 银联, 格瓦拉)）
     * 注意,和 v1比, 也就是 pay 方法比, v2版本支持改签
     *
     * @param string                                       openId             Y   用户openId
     * @param string                                       orderId            Y   锁座完成的临时订单编号
     * @param string                                       phone              Y   手机号
     * @param string                                       channelId          Y   公众号缩写 电影票:3, IOS:8, Android:9
     * @param string                                       payType            Y   支付方式(1:微信, 2:支付宝, 7:财付通,12:京东支付, 17:银联支付, 99:预算, 其他格瓦拉)
     * @param string                                       marketingStatus    N   营销状态(如果该字段有值，不调用营销)
     * @param string                                       tradeType          N   支付方式(JSAPI、APP)，默认为JSAPI
     * @param string                                       snackId            N   卖品Id
     * @param string                                       snackNum           N   卖品数量
     * @param string                                       showUrl            N   商品展示网址（支付宝支付专用）
     * @param string                                       returnUrl          N   页面跳转同步通知页面路径（支付宝, 格瓦拉公用）
     * @param string                                       disInfo            N   营销活动JSON串，如果没有营销数据，就不要传。它目前格式如下：
     * @param string                                       smsToken           N   手机号token（用于解密出领红包人的手机号）
     *                                                     ——————————————————
     *                                                     useIp       string  Y   使用IP（注意，只要有disInfo内容，里边就必须有userIp，不过这个service自己加了，不用业务传）
     *                                                     bnsId       string  N   红包编号
     *                                                     reduId      string  N   立减减至ID
     *                                                     presellId   string  N   选座券编号
     *                                                     payReduId   string  N   支付方式优惠ID
     *                                                     acctNo      string  N   第三方支付账号
     *                                                     bankCardNo  string  N   银行卡卡号
     *                                                     vCardNo     string  N   V卡编号
     *                                                     cardId      string  N   朋友的券卡券ID
     *                                                     encyCode    string  N   朋友的券加密code
     *                                                     ——————————————————
     * @param string                                       smsCode             N   短信码（当需要用户手机号校验的时候传入）
     *
     * @return array
     */
    public function payV3(array $arrInput = [])
    {
        //参数处理
        $return = self::getStOut();
        $return['data'] = [];
        $arrSendParams = [];
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['orderId'] = self::getParam($arrInput, 'orderId');
        $arrSendParams['phone'] = self::getParam($arrInput, 'phone');
        $arrSendParams['channelId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['payType'] = self::getParam($arrInput, 'payType');
        $arrSendParams['tradeType'] = self::getParam($arrInput, 'tradeType');
        $arrSendParams['snackId'] = self::getParam($arrInput, 'snackId');
        $arrSendParams['snackNum'] = self::getParam($arrInput, 'snackNum');
        $arrSendParams['showUrl'] = self::getParam($arrInput, 'showUrl');
        $arrSendParams['returnUrl'] = self::getParam($arrInput, 'returnUrl');
        $arrSendParams['disInfo'] = self::getParam($arrInput, 'disInfo');
        $arrSendParams['goodsInfoList'] = self::getParam($arrInput, 'goodsInfoList');
        $arrSendParams['smsCode'] = self::getParam($arrInput, 'smsCode');
        $arrSendParams['smsToken'] = self::getParam($arrInput, 'smsToken');
        //格瓦拉v2新增
        $arrSendParams['reqSource'] = self::getParam($arrInput, 'reqSource');
        $arrSendParams['merchantCode'] = self::getParam($arrInput, 'merchantCode');
        $arrSendParams['bankCode'] = self::getParam($arrInput, 'bankCode');
        $arrSendParams['gatewayCode'] = self::getParam($arrInput, 'gatewayCode');
        //权益相关
        $arrSendParams['rightsId'] = self::getParam($arrInput, 'rightsId');
        $arrSendParams['goodsId'] = self::getParam($arrInput, 'goodsId');
        $arrSendParams['goodsNum'] = self::getParam($arrInput, 'goodsNum');
        //jdv2参数
        $arrSendParams['interfaceVersion'] = self::getParam($arrInput, 'interfaceVersion');
        //参数校验
        if (empty($arrSendParams['openId']) || empty($arrSendParams['orderId']) || empty($arrSendParams['phone']) || empty($arrSendParams['channelId']) || empty($arrSendParams['payType'])) {
            $return['ret'] = $return['sub'] = -1000;
            $return['msg'] = 'param error';
            return $return;
        }
        //检测是否是格瓦拉渠道
        if (in_array($arrSendParams['channelId'], [80, 84])) {
            if (!isset($arrSendParams['reqSource']) || !isset($arrSendParams['merchantCode']) || !isset($arrSendParams['bankCode']) || !isset($arrSendParams['gatewayCode'])) {
                $return['ret'] = $return['sub'] = -1000;
                $return['msg'] = 'param error';
                return $return;
            }
        }
        $arrDisInfo = [];
        if (!empty($arrSendParams['disInfo'])) {
            $arrDisInfo = json_decode($arrSendParams['disInfo'], true);
            $arrDisInfo['isMobileChk'] = 0;
        }

        //用户传了短信码，则认为需要短信验证
        if (!empty($arrSendParams['smsCode']) && !empty($arrSendParams['smsToken'])) {
            //从token中解密手机号出来
            $arrPhoneNumberRes = $this->service('Sms')->decryptPhone([
                'token' => $arrSendParams['smsToken'],
                'channelId' => $arrSendParams['channelId'],
            ]);
            $strPhoneNum = (isset($arrPhoneNumberRes['ret']) && ($arrPhoneNumberRes['ret'] == 0) && !empty($arrPhoneNumberRes['data']['phone'])) ? $arrPhoneNumberRes['data']['phone'] : '';
            //用户传了验证码，进行验证
            $arrVerifyRes = $this->service('Sms')->verifySmsCode([
                'phone_number' => $strPhoneNum,
                'code' => $arrSendParams['smsCode'],
                'channelId' => $arrSendParams['channelId'],
            ]);
            //验证失败的情况
            if (!isset($arrVerifyRes['errorcode']) || ($arrVerifyRes['errorcode'] != 0)) {
                $return['ret'] = $return['sub'] = -1002;
                $return['msg'] = '验证码输入错误';

                return $return;
            }
            //验证成功
            //将验证成功标识传到后端服务
            $arrDisInfo['isMobileChk'] = 1;

        }

        //补充参数
        //1、marketingStatus设置：判断是否没有使用优惠，或开启了优惠降级（如果开启优惠降低，调用Java接口的时候，需要设置参数marketingStatus为1，意思是Java侧不调用其他后端优惠接口）
        $iDemote = !empty(\wepiao::$config['demote']) ? \wepiao::$config['demote'] : 0;
        //降级了,或者票优惠与卖品优惠都为空的话,就传降级参数,告知订单中心,不要调用营销中心了
        if (!empty($iDemote) || (empty($arrDisInfo) && empty($arrSendParams['goodsInfoList']))) {
            $arrSendParams['marketingStatus'] = 1;
        }
        //2、调用者IP地址
        if (!empty($arrDisInfo)) {
            $strIp = Net::getRemoteIp();
            $arrDisInfo['useIp'] = $strIp;
        }
        $arrSendParams['disInfo'] = !empty($arrDisInfo) ? json_encode($arrDisInfo) : '';

        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $res = $this->http(JAVA_API_PAY_ALL_TYPE_V3, $httpParams);
        //判断是否需要对当前用户开启短信验证手机号（ret为-10000911，msg为手机号）
        $url = $strPhone = $strSmsToken = '';
        if (!empty($res['ret'])) {
            $return['ret'] = $res['ret'];
            $return['sub'] = $res['sub'];
            $return['msg'] = is_numeric($res['msg']) ? 'failure' : $res['msg'];     //不能返回领取红包人的真实手机号
        }
        if (!empty($res['sub']) && ($res['sub'] == -10000911) && !empty($res['msg']) && is_numeric($res['msg'])) {
            $arrVerifyUrl = $this->service('SlideVerify')->getH5Url();
            $url = !empty($arrVerifyUrl['data']['url']) ? $arrVerifyUrl['data']['url'] : '';
            //返回给用户的手机号修改为 186****9999 的形式
            $strPhone = $res['msg'];
            $arrTokenRes = $this->service('Sms')->encryptPhone(['channelId' => $arrSendParams['channelId'], 'phoneNumber' => $strPhone]);
            if (isset($arrTokenRes['ret']) && ($arrTokenRes['ret'] == 0) && !empty($arrTokenRes['data']['token'])) {
                $strSmsToken = $arrTokenRes['data']['token'];
            }
        }
        $return['data']['paymentInfo'] = !empty($res['data']) ? $res['data'] : new \stdClass();
        $return['data']['slideUrl'] = $url;
        $return['data']['phone'] = !empty($strPhone) ? substr_replace($strPhone, '****', 3, 4) : '';
        $return['data']['smsToken'] = $strSmsToken;

        return $return;
    }
    /**
     * 获取格瓦拉可用支付列表
     * @param array $arrInput
     * @return array|bool|mixed
     */
    public function gewaraPayMethods(array $arrInput = [])
    {
        //参数处理
        $return = self::getStOut();
        $return['data'] = [];
        $arrSendParams = [];
        $arrSendParams['channelId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['businessId'] = (int)self::getParam($arrInput, 'businessId');
        $arrSendParams['bankResId'] = self::getParam($arrInput, 'bankResId');
        $arrSendParams['version'] = self::getParam($arrInput, 'version');
        $arrSendParams['requestId'] =    $this->getReqiuseId();
        $arrSendParams['timeStr'] = date('YmdHis');
        //参数校验
        if (empty($arrSendParams['channelId']) || empty($arrSendParams['businessId']) || empty($arrSendParams['version'])) {
            $return['ret'] = $return['sub'] = -1000;
            $return['msg'] = 'param error';
            return $return;
        }
        $arrSendParams['sign'] = $this->getSign($arrSendParams);
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'GET',
        ];
        $res = $this->http(JAVA_GEWARA_PAY_METHODS, $httpParams, false);
        return $res;
    }

    /**
     * 生成加密sign
     * @param $params
     * @return string
     */
    private function getSign(&$params)
    {
        $signData = [];
        //步骤1
        ksort($params);
        foreach ($params as $key => $val) {
            if (!empty($val)) {
                $signData[] = $key . '=' . $val;
            } else {
                unset($params[$key]);
            }
        }
        //步骤2
        $signData[] = 'businessKey=' . JAVA_PAY_GEWARA_BUSINESS_KEY;
        return strtoupper(md5(implode('&', $signData)));
    }
}