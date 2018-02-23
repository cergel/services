<?php

namespace sdkService\service;

use sdkService\helper;

/**
 * Class serviceOrder
 * @package app\service
 */
class serviceBonus extends serviceBase
{
    /**
     * 解锁优惠接口, 接触未支付订单中已经绑定的 红包 和 预售券 等优惠
     * @note    该接口返回结果, 和Java返回的结果格式一样, 没有自己再封装, 另外该接口最多调用Java 1 秒钟
     * @param   string sUin               用户openId
     * @param   string iOrderId           订单编号
     * @param   string sMpId              排期编号
     * @return  array {"data":null,"msg":"success","ret":"0","sub":"0"}
     */
    public function unlockBonus(array $arrInput = [])
    {
        //兼容订单号
        if (!empty($arrInput['orderId'])) {
            $arrInput['iOrderId'] = $arrInput['orderId'];
        }
        //参数处理
        $arrSendParams = [];
        $arrSendParams['sUin'] = self::getParam($arrInput, 'openId');
        $arrSendParams['iOrderId'] = self::getParam($arrInput, 'iOrderId');
        $arrSendParams['sMpId'] = self::getParam($arrInput, 'sMpId');
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
            'iTimeout' => 2,
            'iTryTimes' => 1,
        ];
        $lockSeatRes = $this->http(JAVA_API_UNLOCK_BONUS, $httpParams);
        return $lockSeatRes;
    }

    /**
     * 获取可用优惠
     * @note  这个接口, 可以用在 支付页 获取适用于当前订单的 可用优惠
     * @param string openId             用户openId
     * @param intval scheduleId         排期编号, 老版的排期是字符串类型,新版是整型
     * @param string channelId          公众号缩写 电影票:3, IOS:8, Android:9
     * @param string salePlatformType   售卖平台, 一般情况下为2
     * @param string subChannelId       渠道应用子来源，10位数字
     * @param intval appId              登录方式，认证体系APPID：0：通用，1为微信公众号，2为手机号...
     * @param intval userId             用户Id,如果没有,可传0, 需要打通账号体系来支撑
     * @param intval page               查询页码
     * @param intval num                查询单页的数量
     * @param intval status             查询优惠的状态, 1表示可用优惠. 0表示全部
     * @param intval orderId            订单号, 如果加了订单号, 则查询的是适用于这个订单的
     * @param string cardId             朋友的券参数：朋友的券cardid
     * @param intval encyCode           朋友的券参数：朋友的券加密code
     * @param string phone              手机号(用于判断立减和减值对手机号的限制)
     * @param int    isQrycard          是否返回礼品卡节点(注意:以前查询礼品卡是我们自己调用的,如果传入此参数,Java直接返回,我们不需要再调用)
     * @param string invalidFlg         返回订单不可用红包列表标记，0：默认值，只返回适合当前订单的可用红包；1：返回用户所有的有效红包，包括当前订单不可用的红包）
     * @return array
     */
    public function queryBonus(array $arrInput = [])
    {
        //判断是否优惠降级,优惠降级不返回任何优惠信息

        if (\wepiao::$config['demote']) {
            $data = [];
            $data['iPresellTotalCount'] = 0;
            $data['presell_list'] = [];
            $data['iBonusTotalCount'] = 0;
            $data['payreducnt'] = 0;
            $data['bonus_list'] = [];
            $data['voucherCnt'] = 0;
            $data['vocherLst'] = [];
            $data['payredulist'] = [];
            $data['discount_list'] = [];
            $return = self::getStOut();
            $return['data'] = $data;
            return $return;
        }


        //参数处理
        $arrSendParams = [];
        $arrSendParams['sUin'] = self::getParam($arrInput, 'openId');
        $arrSendParams['mpid'] = self::getParam($arrInput, 'scheduleId');
        $arrSendParams['iChannelId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['iPlatForm'] = 1;
        $arrSendParams['iSubChannelId'] = self::getParam($arrInput, 'subChannelId');
        $arrSendParams['iAppId'] = self::getParam($arrInput, 'appId');
        $arrSendParams['sUserId'] = self::getParam($arrInput, 'userId');
        $arrSendParams['status'] = self::getParam($arrInput, 'status', 1);
        $arrSendParams['page'] = self::getParam($arrInput, 'page');
        $arrSendParams['num'] = self::getParam($arrInput, 'num');
        $arrSendParams['cardId'] = self::getParam($arrInput, 'cardId');
        $arrSendParams['encyCode'] = self::getParam($arrInput, 'encyCode');
        $arrSendParams['iOrderId'] = self::getParam($arrInput, 'orderId');
        $arrSendParams['mobile'] = self::getParam($arrInput, 'phone');
        $arrSendParams['isQrycard'] = self::getParam($arrInput, 'isQrycard', 0);
        $arrSendParams['invalidFlg'] = self::getParam($arrInput, 'invalidFlg', 0);
        $arrSendParams['invalidFlg'] = intval($arrSendParams['invalidFlg']);
        if (isset($arrSendParams['iChannelId']) && $arrSendParams['iChannelId'] == 3) {
            $arrSendParams['isQrycard'] = 1;
        }
        //设置定票张数与影院ID
        if (isset($arrInput['cinId']) AND isset($arrInput['tktCnt'])) {
            $arrSendParams['cinId'] = self::getParam($arrInput, 'cinId');
            $arrSendParams['tktCnt'] = self::getParam($arrInput, 'tktCnt');
        }

        //由于Android红包查询的时候需要传入deviceId以及imei所以要在这里判断是否需要携带设备信息
        if (isset($arrInput['deviceid']) AND isset($arrInput['imei'])) {
            $arrSendParams['devId'] = self::getParam($arrInput, 'deviceid');
            $arrSendParams['imei'] = self::getParam($arrInput, 'imei');
        }

        //判断新老用户
        $arrNewUserRes = $this->service('user')->checkNewuser(['openId' => $arrSendParams['sUin'], 'idType' => 0]);
        $arrSendParams['isNew'] = (isset($arrNewUserRes['ret']) && ($arrNewUserRes['ret'] == 0)) ? $arrNewUserRes['data']['isNew'] : 0;
        $arrSendParams['sRemoteIp'] = helper\Net::getRemoteIp();
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];

        return $this->http(JAVA_API_QUERY_BONUS, $httpParams);
    }


    /**
     * @param array $arrInput
     * 'openId'=>'ddd'
     * 根据openid查询可用的活动ID
     */
    public function getDiscountByOpenId($arrInput = [])
    {

        $arrReturn = self::getStOut();
        $arrSendParams = array();
        $strOpenId = $arrInput['openId'];
        if (!empty($strOpenId)) {
            $arrSendParams['id'] = $strOpenId;
        } else {
            $arrReturn = self::getErrorOut(ERRORCODE_COMMON_PARAMS_ERROR);
            return $arrReturn;
        }
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
            'iTryTimes' => 1,
        ];

        $arrRet = $this->http(JAVA_API_GET_DISCOUNT, $httpParams);
        if ($arrRet['ret'] == 0 && $arrRet['sub'] == 0) {
            $arrReturn = $arrRet;
        } else {
            $arrReturn['ret'] = $arrRet['ret'];
            $arrReturn['sub'] = $arrRet['sub'];
            $arrReturn['msg'] = $arrRet['msg'];
        }
        return $arrReturn;
    }

    /**
     * 获取红包状态
     * @param array $arrInput
     * @return string
     */
    public function getStatus(array $arrInput = [])
    {
        $arrReturn = ['ret' => 0, 'sub' => 0, 'msg' => 'success'];
        $channelId = self::getParam($arrInput, 'channelId');
        $objdata = $this->model('Bonus')->getStatus($channelId);
        if ($objdata != null) {
            $objdata = json_decode($objdata, true);
            if ($objdata['redEnvelopStatus'] == 0) {
                $arrReturn['ret'] = -1;
                $arrReturn['sub'] = -1;
                $arrReturn['msg'] = '没有红包';
            } else {
                unset($objdata['redEnvelopStatus']);
                $arrReturn['data'] = $objdata;
            }
        } else {
            $arrReturn['ret'] = -1;
            $arrReturn['sub'] = -1;
            $arrReturn['msg'] = '没有红包';
        }
        return $arrReturn;
    }

    /**
     * 查询有效红包个数
     */
    public function getBonusCount($arrInput = [])
    {
        $arrReturn = self::getStOut();
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['disType'] = self::getParam($arrInput, 'disType', 0); //类型：0所有，1红包，2选座券
        $arrSendParams['platId'] = self::getParam($arrInput, 'platId', 1); //平台ID，1：电影，2：演出，3：体育
        $arrSendParams['chanId'] = self::getParam($arrInput, 'channelId'); //渠道编号，微信：3，手Q：28
        $arrSendParams['clientIp'] = self::getParam($arrInput, 'clientIp'); //用户IP
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
            'iTryTimes' => 1,
            'sendType' => 'json',
        ];
        $arrRet = $this->http(JAVA_API_GET_BONUS_COUNT, $httpParams, false); //传入requestId时会报错因此不传
        if ($arrRet['ret'] == 0 && $arrRet['sub'] == 0) {
            $arrReturn = $arrRet;
        } else {
            $arrReturn['ret'] = $arrRet['ret'];
            $arrReturn['sub'] = $arrRet['sub'];
            $arrReturn['msg'] = $arrRet['msg'];
        }
        return $arrReturn;
    }


    /**
     * 格瓦拉个人中心获取可用优惠
     * @note  只用在个人中心获取可用优惠
     * @param string userId             用户openId
     * @param string chanId          公众号缩写 电影票:3, IOS:8, Android:9
     * @param string salePlatformType   售卖平台, 一般情况下为1
     * @param string subChannelId       渠道应用子来源，10位数字
     * @param intval appId              登录方式，认证体系APPID：0：通用，1为微信公众号，2为手机号...
     * @param intval userId             用户Id,如果没有,可传0, 需要打通账号体系来支撑
     * @param intval page               查询页码
     * @param intval num                查询单页的数量
     * @param intval status             查询优惠的状态, 1表示可用优惠. 0表示全部
     * @param intval orderId            订单号, 如果加了订单号, 则查询的是适用于这个订单的
     * @param string cardId             朋友的券参数：朋友的券cardid
     * @param intval encyCode           朋友的券参数：朋友的券加密code
     * @param string phone              手机号(用于判断立减和减值对手机号的限制)
     * @param int    isQrycard          是否返回礼品卡节点(注意:以前查询礼品卡是我们自己调用的,如果传入此参数,Java直接返回,我们不需要再调用)
     * @param string invalidFlg         返回订单不可用红包列表标记，0：默认值，只返回适合当前订单的可用红包；1：返回用户所有的有效红包，包括当前订单不可用的红包）
     * @return array
     */
    public function queryGewaraBonusList(array $arrInput = [])
    {
        //判断是否优惠降级,优惠降级不返回任何优惠信息
        if (\wepiao::$config['demote']) {
            $data = [];
            $data['iPresellTotalCount'] = 0;
            $data['presell_list'] = [];
            $data['iBonusTotalCount'] = 0;
            $data['payreducnt'] = 0;
            $data['bonus_list'] = [];
            $data['voucherCnt'] = 0;
            $data['vocherLst'] = [];
            $data['payredulist'] = [];
            $data['discount_list'] = [];
            $return = self::getStOut();
            $return['data'] = $data;
            return $return;
        }

        //参数处理
        $arrSendParams = [];
        $arrSendParams['userId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['chanId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['page'] = self::getParam($arrInput, 'page');
        $arrSendParams['num'] = self::getParam($arrInput, 'num');
        $arrSendParams['iPlatId'] =self::getParam($arrInput, 'iPlatForm');
        $arrSendParams['subChanId'] = self::getParam($arrInput, 'subChannelId');
        $arrSendParams['queryType'] = self::getParam($arrInput, 'queryType');
        $arrSendParams['status'] = self::getParam($arrInput, 'status');

        $arrSendParams['sRemoteIp'] = helper\Net::getRemoteIp();
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $ret = $this->http(JAVA_API_QUERY_GEWARA_BONUS, $httpParams);
        return $ret;
    }


    /**
     * 格瓦拉获取可用优惠和银行特价活动
     * @note  这个接口, 可以用在 支付页 获取适用于当前订单的 可用优惠
     * @param string openId             用户openId
     * @param intval scheduleId         排期编号, 老版的排期是字符串类型,新版是整型
     * @param string channelId          公众号缩写 电影票:3, IOS:8, Android:9
     * @param string salePlatformType   售卖平台, 一般情况下为2
     * @param string subChannelId       渠道应用子来源，10位数字
     * @param intval appId              登录方式，认证体系APPID：0：通用，1为微信公众号，2为手机号...
     * @param intval userId             用户Id,如果没有,可传0, 需要打通账号体系来支撑
     * @param intval page               查询页码
     * @param intval num                查询单页的数量
     * @param intval status             查询优惠的状态, 1表示可用优惠. 0表示全部
     * @param intval orderId            订单号, 如果加了订单号, 则查询的是适用于这个订单的
     * @param string cardId             朋友的券参数：朋友的券cardid
     * @param intval encyCode           朋友的券参数：朋友的券加密code
     * @param string phone              手机号(用于判断立减和减值对手机号的限制)
     * @param int    isQrycard          是否返回礼品卡节点(注意:以前查询礼品卡是我们自己调用的,如果传入此参数,Java直接返回,我们不需要再调用)
     * @param string invalidFlg         返回订单不可用红包列表标记，0：默认值，只返回适合当前订单的可用红包；1：返回用户所有的有效红包，包括当前订单不可用的红包）
     * @return array
     */
    public function queryGewaraBonusAndPayList(array $arrInput = [])
    {
        //判断是否优惠降级,优惠降级不返回任何优惠信息

        if (\wepiao::$config['demote']) {
            $data = [];
            $data['iPresellTotalCount'] = 0;
            $data['presell_list'] = [];
            $data['iBonusTotalCount'] = 0;
            $data['payreducnt'] = 0;
            $data['bonus_list'] = [];
            $data['voucherCnt'] = 0;
            $data['vocherLst'] = [];
            $data['payredulist'] = [];
            $data['discount_list'] = [];
            $return = self::getStOut();
            $return['data'] = $data;
            return $return;
        }


        //参数处理
        $arrSendParams = [];
        $arrSendParams['userId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['mpId'] = self::getParam($arrInput, 'scheduleId');
        $arrSendParams['chanId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['iPlatId'] = self::getParam($arrInput, 'iPlatForm');;
        $arrSendParams['subChanId'] = self::getParam($arrInput, 'subChannelId');
        $arrSendParams['page'] = self::getParam($arrInput, 'page');
        $arrSendParams['num'] = self::getParam($arrInput, 'num');
        $arrSendParams['ordId'] = self::getParam($arrInput, 'orderId');
        $arrSendParams['mobile'] = self::getParam($arrInput, 'mobile');
        $arrSendParams['invalidFlg'] = self::getParam($arrInput, 'invalidFlg', 0);
        $arrSendParams['invalidFlg'] = intval($arrSendParams['invalidFlg']);

        $arrSendParams['cinId'] = self::getParam($arrInput, 'cinemaId');
        $arrSendParams['tktCnt'] = self::getParam($arrInput, 'tktCnt');
        $arrSendParams['devId'] = self::getParam($arrInput, 'deviceid');
        $arrSendParams['imei'] = self::getParam($arrInput, 'imei');

        $arrSendParams['sRemoteIp'] = helper\Net::getRemoteIp();
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $ret = $this->http(JAVA_API_QUERY_GEWARA_PAY_BONUS, $httpParams);
        return $ret;
    }


    /**
     * 通兑码兑换
     * @param $arrInput
     * @return array|bool|mixed
     */
    public function exchangeGewaraCode($arrInput)
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['rcvId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['code'] = self::getParam($arrInput, 'code');;
        $arrSendParams['platId'] = self::getParam($arrInput, 'plateId');;
        $arrSendParams['chanId'] = self::getParam($arrInput, 'channelId');;
        $arrSendParams['subChanId'] = self::getParam($arrInput, 'subChanId');
        $arrSendParams['mobile'] = self::getParam($arrInput, 'mobile');
        $arrSendParams['rcvIp'] = helper\Net::getRemoteIp();
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $ret = $this->http(JAVA_API_EXCHANGE_GEWARA_CODE, $httpParams);
        return $ret;
    }


    /**
     * V卡列表
     * @param $arrInput
     * @return array|bool|mixed
     */
    public function gewaraVcardList($arrInput)
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['suin'] = self::getParam($arrInput, 'suin');
        $arrSendParams['status'] = self::getParam($arrInput, 'status');;
        $arrSendParams['mpid'] = self::getParam($arrInput, 'mpid');;
        $arrSendParams['chanid'] = self::getParam($arrInput, 'channelId');;
        $arrSendParams['sub_chanid'] = self::getParam($arrInput, 'sub_chanid');
        $arrSendParams['page'] = self::getParam($arrInput, 'page');
        $arrSendParams['num'] = self::getParam($arrInput, 'num');
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $ret = $this->http(JAVA_API_GEWARA_VCARD_LIST, $httpParams);
        return $ret;
    }


    /**
     * V卡激活
     * @param $arrInput
     * @return array|bool|mixed
     */
    public function gewaraVcardActive($arrInput)
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['code'] = self::getParam($arrInput, 'code');
        $arrSendParams['suin'] = self::getParam($arrInput, 'suin');
        $arrSendParams['plat_id'] = self::getParam($arrInput, 'plat_id');
        $arrSendParams['mobile'] = self::getParam($arrInput, 'mobile');;
        $arrSendParams['chanid'] = self::getParam($arrInput, 'channelId');;
        $arrSendParams['sub_chanid'] = self::getParam($arrInput, 'sub_chanid');
        $arrSendParams['acti_ip'] = helper\Net::getRemoteIp();
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $ret = $this->http(JAVA_API_GEWARA_VCARD_ACTIVE, $httpParams);
        return $ret;
    }


    /**
     * 单张V卡信息
     * @param $arrInput
     * @return array|bool|mixed
     */
    public function gewaraVcardInfo($arrInput)
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['suin'] = self::getParam($arrInput, 'suin');
        $arrSendParams['card_no'] = self::getParam($arrInput, 'card_no');
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $ret = $this->http(JAVA_API_GEWARA_VCARD_INFO, $httpParams);
        return $ret;
    }
    /**
     * 领取或系统下发红包
     * @param array $arrInput
     * @return array
     */
    public function getBonus($arrInput = [])
    {
        $arrReturn = self::getStOut();
        //必传字段
        $arrSendParams['res_id'] = self::getParam($arrInput, 'bonusId'); //红包资源ID
        $arrSendParams['suin'] = self::getParam($arrInput, 'openId');
        $arrSendParams['rcv_cnt'] = self::getParam($arrInput, 'rcv_cnt'); //领取张数
        $arrSendParams['chanid'] = self::getParam($arrInput, 'channelId'); //红包领取渠道ID
        $arrSendParams['sub_chanid'] = self::getParam($arrInput, 'subChannelId'); //渠道领取子来源，10位数字
        $arrSendParams['rcv_ip'] = self::getParam($arrInput, 'clientIp'); //领取用户IP地址
        $arrSendParams['plat_id'] = 1; //平台ID，1：电影，2：演出，3：体育
        //非必传字段
        $arrSendParams['value'] = self::getParam($arrInput, 'value'); //红包的金额，以分为单位【按值发放】
        $arrSendParams['new_flg'] = self::getParam($arrInput, 'new_flg'); //是否为新用户，1为新用户，0老用户【新领取流程无需传递】
        $arrSendParams['auth_id'] = self::getParam($arrInput, 'auth_id'); //认证方式 (0：通用，1：微信公众号，2：手机号…)
        $arrSendParams['mobile'] = self::getParam($arrInput, 'phone'); //用户手机号
        $arrSendParams['mobile_chk_flg'] = self::getParam($arrInput,
            'mobile_chk_flg', 1); //手机号是否已经过PHP验证，新领取流程必传（0：未验证，1：已验证）
        $arrSendParams['encrypt_flg'] = self::getParam($arrInput,
            'encrypt_flg', 0); //openid是否加密标识（0：未加密，明文，1：加密，统一为commoncgi加密方式）
        //APP必传
        $arrSendParams['dev_id'] = self::getParam($arrInput, 'dev_id'); //领取用户设备ID（app必传）
        $arrSendParams['imei'] = self::getParam($arrInput, 'imei'); //IMEI（APP必传）
        $arrSendParams['new_imei'] = self::getParam($arrInput,
            'new_imei'); //新方式IMEI（APP必传，和imei + dev_id，三选一，new_imei > imei > dev_id）
        $arrSendParams['vers'] = self::getParam($arrInput, 'vers'); //app版本号，iOS或Android渠道必传，全网渠道如果为app环境也必传
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
            'iTryTimes' => 1,
        ];
        $arrRet = $this->http(JAVA_API_GET_BONUS, $httpParams, false);
        if ($arrRet['ret'] == 0 && $arrRet['sub'] == 0) {
            $arrReturn = $arrRet;
        } else {
            $arrReturn['ret'] = $arrRet['ret'];
            $arrReturn['sub'] = $arrRet['sub'];
            $arrReturn['msg'] = $arrRet['msg'];
        }
        return $arrReturn;
    }

    /**
     * 领取套装红包
     * @param array $arrInput
     * @return array|bool|mixed
     */
    public function getSuitBonus($arrInput = [])
    {
        $arrReturn = self::getStOut();
        //必传字段
        $arrSendParams['suitId'] = self::getParam($arrInput, 'suitId'); //红包资源ID
        $arrSendParams['suin'] = self::getParam($arrInput, 'openId');
        $arrSendParams['chanid'] = self::getParam($arrInput, 'channelId'); //红包领取渠道ID
        $arrSendParams['subChanid'] = self::getParam($arrInput, 'subChannelId',''); //渠道领取子来源，10位数字
        $arrSendParams['mobileChkFlg'] = self::getParam($arrInput,'mobile_chk_flg', 1); //手机号是否已经过PHP验证，新领取流程必传（0：未验证，1：已验证）
        $arrSendParams['encryptFlg'] = self::getParam($arrInput,'encrypt_flg', 0); //openid是否加密标识（0：未加密，明文，1：加密，统一为commoncgi加密方式）
        //非必传
        $arrSendParams['uId'] = self::getParam($arrInput, 'userId','');
        $arrSendParams['revMobile'] = self::getParam($arrInput, 'phone'); //用户手机号
        $arrSendParams['rcvIp'] = self::getParam($arrInput, 'clientIp'); //领取用户IP地址
        $arrSendParams['newFlg'] = self::getParam($arrInput, 'new_flg'); //是否为新用户，1为新用户，0老用户【新领取流程无需传递】
        $arrSendParams['value'] = self::getParam($arrInput, 'value'); //红包的金额，以分为单位【按值发放】
        $arrSendParams['rcvDev'] = self::getParam($arrInput, 'deviceId'); //领取用户设备ID（app必传）
        $arrSendParams['rcvImei'] = self::getParam($arrInput, 'imei'); //IMEI（APP必传）
        $arrSendParams['appVers'] = self::getParam($arrInput, 'appver'); //app版本号，iOS或Android渠道必传，全网渠道如果为app环境也必传
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
            'iTryTimes' => 1,
        ];
        $arrRet = $this->http(JAVA_API_GET_SUIT_BONUS, $httpParams, false);
        if ($arrRet['ret'] == 0 && $arrRet['sub'] == 0) {
            $arrReturn = $arrRet;
        } else {
            $arrReturn['ret'] = $arrRet['ret'];
            $arrReturn['sub'] = $arrRet['sub'];
            $arrReturn['msg'] = $arrRet['msg'];
        }
        return $arrReturn;
    }

    /**
     * 用户端套装实时数据查询
     * @param array $arrInput
     * @return array|bool|mixed
     */
    public function suitBonusInfo($arrInput = [])
    {
        $arrReturn = self::getStOut();
        //必传字段
        $arrSendParams['suitId'] = self::getParam($arrInput, 'suitId'); //红包资源ID
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'GET',
            'iTryTimes' => 1,
        ];
        $arrRet = $this->http(JAVA_API_SUIT_BONUS_INFO, $httpParams, false);
        if ($arrRet['ret'] == 0 && $arrRet['sub'] == 0) {
            $arrReturn = $arrRet;
        } else {
            $arrReturn['ret'] = $arrRet['ret'];
            $arrReturn['sub'] = $arrRet['sub'];
            $arrReturn['msg'] = $arrRet['msg'];
        }
        return $arrReturn;
    }

    /**
     * 首页拉新促销弹框活动
     * 注意：返回了数据仅表示该用户匹配活动要求，是否弹框还需要前端判断
     */
    public function NewcomerBonus($arrInput = [])
    {
        $res = self::getStOut();
        $channelId = self::getParam($arrInput, 'channelId');
        $time = time();

        $isNewUser = 1; //新用户为1，老用户为0，默认为新用户
        $data = [
            'channelId' => $channelId,
            'openId' => self::getParam($arrInput, 'openId'),
            'idType' => $this->service('User')->convChannelId($channelId),
        ];
        $response = $this->service('User')->checkNewUser($data);
        //判断新老用户
        if ($response['ret'] == 0 && $response['sub']) {
            $isNewUser = isset($response['data']['isNew']) ? $response['data']['isNew'] : $isNewUser;
        }
        //获取活动信息，并存入文件缓存，默认5分钟
        $objdata = $this->getCacheData('', 1);
        if (!$objdata) {
            $objdata = $this->model('Bonus')->NewcomerBonus($channelId);
            $objdata = json_decode($objdata, 1);
            $this->setCacheData('', $objdata);
        }
        foreach ($objdata as $item) {
            //判断活动时间、渠道是否符合，以及是否上线
            if ($item['state_type'] == 1 && $item['channel_id'] = $channelId &&
                    $item['start_time'] < $time && $item['end_time'] > $time
            ) {
                //$item['for_user']表示活动针对的用户：0全部，1新用户，2老用户。返回符合要求的第一项
                if ($item['for_user'] == 0 || ($item['for_user'] == 1 && $isNewUser == 1) ||
                    ($item['for_user'] == 2 && $isNewUser == 0)
                ) {
                    $getBonusSueccess = '';
                    //存在红包id则去领取红包
                    if (!empty($item['bonus_id'])) {
                        $data = [
                            'bonusId' => $item['bonus_id'],
                            'channelId' => self::getParam($arrInput, 'channelId'),
                            'openId' => self::getParam($arrInput, 'openId'),
                            'rcv_cnt' => self::getParam($arrInput, 'rcv_cnt', 1), //领取张数
                            'subChannelId' => self::getParam($arrInput, 'subChannelId'), //子渠道
                            'clientIp' => self::getParam($arrInput, 'clientIp'), //用户IP
                            'mobile_chk_flg' => 1, //该项必填
                            'encrypt_flg' => 0, //该项必填
                            'new_flg' => $isNewUser, //是否新用户
                        ];
                        $response = $this->getBonus($data);
                        //领取成功并且红包有效
                        if (isset($response['ret']) && $response['ret'] == 0 && $response['data']['status'] == 1) {
                            $getBonusSueccess = 1;
                        } else {
                            $getBonusSueccess = 0;
                        }
                    }
                    $res['data'] = [
                        'new_user' => $isNewUser, //用户是否是新用户
                        'bonus_id' => $item['bonus_id'], //红包ID
                        'bonus_url' => $item['bonus_url'], //链接地址
                        'background_url' => $item['background_url'], //背景图片
                        'get_bonus_success' => $getBonusSueccess, //是否领取成功，1成功，0失败，默认为空
                    ];
                    break;
                }
            }
        }
        return $res;
    }
}