<?php

namespace sdkService\service;


/**
 * 兑换券服务 目前营销部分, 也只有兑换券这个服务还在我们这里, 其他都再商业化了
 *
 * @package app\service
 */
class serviceExchange extends serviceBase
{
    
    /**
     * 获取兑换券详情
     *
     * @note  这个接口, 可以用在 支付页 获取适用于当前订单的 可用优惠
     *
     * @param string channelId          公众号缩写 电影票:3, IOS:8, Android:9
     * @param string grouponId          团购券Id
     * @param string openId             用户openId
     *
     * @return array
     */
    public function query(array $arrInput = [])
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['iGrouponID'] = self::getParam($arrInput, 'grouponId');
        $arrSendParams['iChannelNo'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['idType'] = self::getParam($arrInput, 'idType');
        //判断新老用户
        $arrNewUserRes = $this->service('user')->checkNewuser([
            'openId' => $arrSendParams['openId'],
            'idType' => $arrSendParams['idType'],
        ]);
        $arrSendParams['isNew'] = ( isset( $arrNewUserRes['ret'] ) && ( $arrNewUserRes['ret'] == 0 ) ) ? $arrNewUserRes['data']['isNew'] : 0;
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $arrReturn = $this->http(JAVA_API_EXCHANGE_INFO, $httpParams);
        
        return $arrReturn;
    }
    
    /**
     * 兑换券验证
     *
     * @todo  该接口未实现, 什么时候需要什么时候再迁移过来
     *
     * @param string sigin              签名
     * @param string checkWay           校验方式: 1为pos来源,这个时候posVTypeId和posNumber必须，2为biz来源,这个时候merchantbrachid和userId必须
     * @param string posVTypeId         Pos机验证类型
     * @param string posNumber          POS机序列号
     * @param string merchantbranchid   门店Id, 非必须, biz查询和验证的时候不能为空
     * @param string type               接口调用类型: 1位数字，1：查询，2：消费
     * @param string excode             10位数字【兑换码】
     * @param string userId             用户Id, 非必须
     *
     * @return array
     */
    public function validateExchange(array $arrInput = [])
    {
    }
    
    /**
     * 获取兑换券订单列表
     *
     * @note  这个接口, 可以用在 支付页 获取适用于当前订单的 可用优惠
     *
     * @param string channelId          公众号缩写 电影票:3, IOS:8, Android:9
     * @param string pageNum            页码
     * @param string pageSize           单页条数
     * @param string channelId          公众号缩写 电影票:3, IOS:8, Android:9
     * @param string salePlatformType   售卖平台类型 枚举 1 公众号、2渠道, 除了智慧影院,都传2
     * @param intval appId              登录方式，认证体系APPID：0：通用，1为微信公众号，2为手机号...
     * @param intval userId             用户Id,如果没有,可传0, 需要打通账号体系来支撑
     *
     * @return array
     */
    public function orderList(array $arrInput = [])
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['page'] = self::getParam($arrInput, 'pageNum', 1);
        $arrSendParams['num'] = self::getParam($arrInput, 'pageSize', 10);
        $arrSendParams['publicSignalShort'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['salePlatformType'] = self::getParam($arrInput, 'salePlatformType');
        $arrSendParams['appId'] = self::getParam($arrInput, 'appId');
        $arrSendParams['userId'] = self::getParam($arrInput, 'userId');
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $arrReturn = $this->http(JAVA_API_EXCHANGE_ORDER_LIST, $httpParams);
        
        return $arrReturn;
    }
    
    /**
     * 获取兑换券订单详情
     *
     * @note  这个接口, 可以用在 支付页 获取适用于当前订单的 可用优惠
     *
     * @param string channelId          公众号缩写 电影票:3, IOS:8, Android:9
     * @param intval openId             用户openId
     * @param string orderId            用户的订单id
     *
     * @return array
     */
    public function orderInfo(array $arrInput = [])
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['orderId'] = self::getParam($arrInput, 'orderId');
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $arrReturn = $this->http(JAVA_API_EXCHANGE_ORDER_INFO, $httpParams);
        
        return $arrReturn;
    }
    
    
}