<?php
/**
 * 该服务, 主要用于提供和 "座位" 相关的处理, 如: 锁座, 释放座位, 获取可售座位 等
 */

namespace sdkService\service;


class serviceTicket extends serviceBase
{

    /**
     * 锁座接口
     *
     * @note 注意:
     *       1. 该接口, 若除微信电影票之外的渠道使用, 需要测试,因为这里有个 seatLable格式化的操作, 比如电影票就做了格式化,APP却没有
     *       2. 传手机号说明: 现在万达和微信电影票是统一的, 不再是分拆的. 万达锁座, 必须传手机号, 所以 phone 这个字段, 在万达座位图场景下, 毕传
     *
     * @param string openId             用户openId
     * @param intval scheduleId         排期编号, 老版的排期是字符串类型,新版是整型
     * @param string seatlable          锁座信息, 如: 01:1:1|01:5:6, 注意:有场区, 很早以前电影票是不传场区的.
     * @param string channelId          公众号缩写 电影票:3, IOS:8, Android:9
     * @param string salePlatformType   售卖平台类型 枚举 1 公众号、2渠道, 除了智慧影院,都传2
     * @param string subChannelId       渠道应用子来源，10位数字
     * @param intval appId              登录方式，认证体系APPID：0：通用，1为微信公众号，2为手机号...
     * @param intval userId             用户Id,如果没有,可传0, 需要打通账号体系来支撑
     * @param intval ticket             固定值:1
     * @param intval phone              手机号
     * @param intval cinemaNo           影院编号
     *
     * @return array
     */
    public function lockSeat(array $arrInput = [])
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['phone'] = self::getParam($arrInput, 'phone');
        $arrSendParams['saleObjType'] = self::getParam($arrInput, 'saleObjType');
        $arrSendParams['appId'] = self::getParam($arrInput, 'appId');
        $arrSendParams['cinemaNo'] = self::getParam($arrInput, 'cinemaNo');
        $arrSendParams['schedulePricingId'] = self::getParam($arrInput, 'schedulePricingId');
        $arrSendParams['salePlatformType'] = self::getParam($arrInput, 'salePlatformType');
        $arrSendParams['ticket'] = self::getParam($arrInput, 'ticket');
        $arrSendParams['bisServerId'] = self::getParam($arrInput, 'bisServerId');
        $arrSendParams['userId'] = self::getParam($arrInput, 'userId');
        $arrSendParams['seatlable'] = self::getParam($arrInput, 'seatlable');
        $arrSendParams['publicSignalShort'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['remarkJson'] = self::getParam($arrInput, 'remarkJson');
        $arrSendParams['spm'] = self::getParam($arrInput, 'spm');
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $lockSeatRes = $this->http(JAVA_API_LOCK_SEAT, $httpParams);
        if ($lockSeatRes['ret'] == 0) //java返回成功
        {
            if (isset($lockSeatRes['seatinfo']['refundType'])) {
                $lockSeatRes['seatinfo']['refundMsg'] = $this->model('order')->getRefundContent($lockSeatRes['seatinfo']['refundType']);
            }
        }

        return $lockSeatRes;
    }


    /**
     * 【新版】锁座接口
     * 为了配合改签,锁座接口也使用新接口
     *
     * @note 注意:
     *       1. 该接口, 若除微信电影票之外的渠道使用, 需要测试,因为这里有个 seatLable格式化的操作, 比如电影票就做了格式化,APP却没有
     *       2. 传手机号说明: 现在万达和微信电影票是统一的, 不再是分拆的. 万达锁座, 必须传手机号, 所以 phone 这个字段, 在万达座位图场景下, 毕传
     *
     * @param Y      string openId             用户openId
     * @param Y      int    scheduleId         排期编号, 老版的排期是字符串类型,新版是整型
     * @param Y      string seatlable          锁座信息, 如: 01:1:1|01:5:6, 注意:有场区, 很早以前电影票是不传场区的.
     * @param Y      string channelId          公众号缩写 电影票:3, IOS:8, Android:9
     * @param Y      string subChannelId       子渠道来源(通常为10位)
     * @param Y      string salePlatformType   售卖平台类型 枚举 1 公众号、2渠道, 除了智慧影院,都传2
     * @param Y      int    spm                内部来源追踪
     * @param N      int    phone              手机号(非必须参数,原来的锁座接口,就不需要这个参数)
     * @param Y      int    cinemaNo           影院编号
     * @param N      string seatLockType       锁座类型,1表示改签类型的锁座
     * @param N      string fromOrderId        改签的原始订单id
     * @param N      string fromOpenId         改签的原始订单id的用户openId(此值可以暂时不传)
     * @param N      string cardNo             会员卡id
     *
     * @return array
     */
    public function lockSeatV1(array $arrInput = [])
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['movieId'] = self::getParam($arrInput, 'movieId');
        $arrSendParams['publicSignalShort'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['schedulePricingId'] = self::getParam($arrInput, 'scheduleId');
        $arrSendParams['seatlable'] = self::getParam($arrInput, 'seatlable');
        $arrSendParams['cinemaNo'] = self::getParam($arrInput, 'cinemaNo');
        $arrSendParams['saleObjType'] = self::getParam($arrInput, 'subChannelId');
        $arrSendParams['phone'] = self::getParam($arrInput, 'phone');
        $arrSendParams['spm'] = self::getParam($arrInput, 'spm');
        $arrSendParams['seatLockType'] = self::getParam($arrInput, 'seatLockType');
        $arrSendParams['fromOrderId'] = self::getParam($arrInput, 'fromOrderId');
        $arrSendParams['unionOpenId'] = self::getParam($arrInput, 'unionOpenId');
        $arrSendParams['cardNo'] = self::getParam($arrInput, 'cardNo');
        //下面这些参数,可以不传
        $arrSendParams['appId'] = self::getParam($arrInput, 'appId');
        $arrSendParams['salePlatformType'] = self::getParam($arrInput, 'salePlatformType');
        $arrSendParams['ticket'] = self::getParam($arrInput, 'ticket');
        $arrSendParams['bisServerId'] = self::getParam($arrInput, 'bisServerId');
        $arrSendParams['userId'] = self::getParam($arrInput, 'userId');
        $arrSendParams['remarkJson'] = self::getParam($arrInput, 'remarkJson');
        //获取当前时间锁座的片子是开场前几天
        $iMovieId = $arrSendParams['movieId'];
        $showDate = '';
        if ( !empty($iMovieId)) {
            $arrMovieSimpleData = $this->service('movie')->readMovieAndScoreNewStatic([
                'channelId' => $arrSendParams['publicSignalShort'],
                'movieIds'  => [$iMovieId],
            ]);
            $showDate = !empty($arrMovieSimpleData['data'][$iMovieId]['date']) ? $arrMovieSimpleData['data'][$iMovieId]['date'] : '';
            if ( !empty($showDate)) {
                $showDate = date('Y-m-d', strtotime($showDate));
            }
        }
        $arrSendParams['firstShowDate'] = $showDate;
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $lockSeatRes = $this->http(JAVA_API_LOCK_SEAT_V1, $httpParams);
        if ($lockSeatRes['ret'] != 0) //java返回成功
        {
            //处理错误文案
            $msg = "锁座失败，请稍候再试";
            //1、处理书健文案之外的内容
            if (($lockSeatRes['ret'] == '-3')) {
                //sub码以70003开头的，都是书健的错误文案
                if (strpos($lockSeatRes['sub'], '70003') === false) {
                    $lockSeatRes['msg'] = $msg;
                }
            } //2、处理接口响应失败的情况
            else {
                $lockSeatRes['msg'] = $msg;
            }
        }

        return $lockSeatRes;
    }


    /**
     * 获取可售座位, 整体json版本
     * 注意：此方法暂时不可用
     *
     * @note 整体json版本,也就是 先获取座位图,然后获取可售座位, 然后融合到一个json返回给前端,前端直接渲染就可以了. 另外,该接口还融合了
     * 解锁座位, 解锁优惠等. 另外, 这版接口,是获取可售座位渲染, 而不是获取不可售座位渲染, 这点需要注意
     *
     * @param   intval channelId            公众号缩写: 3微信电影票, 8 IOS, 9 安卓, 等
     * @param   intval salePlatformType     售卖平台类型 枚举 1 公众号、2渠道, 除了智慧影院,都传2
     * @param   string openId               用户openId
     * @param   intval userId               用户userId
     * @param   intval appId                登录方式，认证体系APPID：0：通用，1为微信公众号，2为手机号...
     * @param   string subChannelId         渠道应用子来源，10位数字
     *
     * @param   intval roomId              影厅id
     * @param   string cinemaId             影院id
     * @param   string scheduleId    排期编号
     *
     * @return array
     */
    public function querySeat(array $arrInput = [])
    {
        //定义返回结果
        $arrReturn = self::getStOut();
        //定义参数
        $arrSendParams = [];
        $arrSendParams['channelId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['salePlatformType'] = self::getParam($arrInput, 'salePlatformType');
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['userId'] = self::getParam($arrInput, 'userId');
        $arrSendParams['appId'] = self::getParam($arrInput, 'appId');
        $arrSendParams['saleObjType'] = self::getParam($arrInput, 'subChannelId');
        $arrSendParams['scheduleId'] = self::getParam($arrInput, 'scheduleId');
        $arrSendParams['cinemaId'] = self::getParam($arrInput, 'cinemaId');
        $arrSendParams['roomId'] = self::getParam($arrInput, 'roomId');
        //接口调用——start
        //1.获取未支付订单, 解锁优惠和座位
        $arrUnlockRes = $this->unlockSeatAndBonus($arrSendParams);
        //2.融合座位图和可售座位接口, 将数据整体返回给前端
        $arrSeatRes = $this->getMergedRoomSeat($arrSendParams);
        //3.获取用户手机号
        $arrMobileRes = $this->service('user')->getMobile($arrSendParams);
        //4.合并返回结果
        $arrReturn = $this->_querySeatMergeResult($arrUnlockRes, $arrSeatRes, $arrMobileRes);

        return $arrReturn;
    }

    /**
     * 获取未支付订单, 解锁优惠和座位
     * 该方法,可传 orderId 参数, 如果没有接受到该参数, 该方法获取用户的未支付订单, 来获取未支付订单的编号
     *
     * @param   intval channelId            公众号缩写: 3微信电影票, 8 IOS, 9 安卓, 等
     * @param   intval salePlatformType     售卖平台类型 枚举 1 公众号、2渠道, 除了智慧影院,都传2
     * @param   string openId               用户openId
     * @param   intval userId               用户userId
     * @param   intval appId                登录方式，认证体系APPID：0：通用，1为微信公众号，2为手机号...
     * @param   string orderId              未支付订单编号,如果没有传此参数,则会主动获取未支付订单
     * @param   string scheduleId           排期编号, 老版的排期是字符串类型,新版是整型
     *
     * @return  array {'ret':0,'sub':0,'msg':'success','data':{'bonusUnlock':1,'seatUnlock':0,"unpaymentOrderId":"38293892932"}}
     */
    public function unlockSeatAndBonus(array $arrInput = [])
    {
        //定义返回结果
        $arrReturn = self::getStOut();
        $arrReturn['data']['bonusUnlock'] = 0;  //优惠解锁是否成功
        $arrReturn['data']['seatUnlock'] = 0;   //座位解锁是否成功
        //定义参数
        $arrSendParams = [];
        $arrSendParams['channelId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['salePlatformType'] = self::getParam($arrInput, 'salePlatformType');
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['userId'] = self::getParam($arrInput, 'userId');
        $arrSendParams['appId'] = self::getParam($arrInput, 'appId');
        $orderId = self::getParam($arrInput, 'orderId');
        $arrSendParams['orderId'] = !empty($orderId) ? $orderId : '';
        $arrSendParams['sMpId'] = self::getParam($arrInput, 'scheduleId');
        $arrSendParams['iOrderId'] = !empty($orderId) ? $orderId : '';
        unset($arrInput);

        if (empty($arrSendParams['orderId'])) {
            //1. 获取未支付订单
            $arrUnpayOrderRes = $this->service('order')->queryUnPayOrder($arrSendParams);
            $arrSendParams['orderId'] = $arrSendParams['iOrderId'] = isset($arrUnpayOrderRes['lockinfo']['sTempOrderID']) ? $arrUnpayOrderRes['lockinfo']['sTempOrderID'] : '';
            $arrSendParams['sMpId'] = (isset($arrUnlockSeatRes['ret']) && ($arrUnlockSeatRes['ret'] == 0)) ? ($arrUnlockSeatRes['seatinfo']['sInfo'] == 'success') : 0;
            $arrSendParams['iOrderId'] = $arrSendParams['orderId'];
        }

        //2. 解锁座
        $arrUnlockSeatRes = $this->unlockSeat($arrSendParams);
        $iUnlockSeatStatus = (isset($arrUnlockSeatRes['ret']) && ($arrUnlockSeatRes['ret'] == 0)) ? '1' : '0';
        //3. 解锁优惠
        $iUnlockBonusStatus = 1;
        /*$arrUnlockBonusRes = $this->service('bonus')->unlockBonus($arrSendParams);
        $iUnlockBonusStatus = (isset($arrUnlockBonusRes['ret']) && ($arrUnlockBonusRes['ret'] == 0)) ? '1' : '0';*/
        //处理返回结果
        $arrReturn['data']['bonusUnlock'] = $iUnlockBonusStatus;
        $arrReturn['data']['seatUnlock'] = $iUnlockSeatStatus;

        //返回数据
        return $arrReturn;
    }

    /**
     * 解锁座位
     *
     * @note    该接口返回结果, 和Java返回的结果格式一样, 没有自己再封装, 另外该接口最多调用Java 2 秒钟
     *          另外就是, 由于只调用了 Java 1秒钟, 所以
     *
     * @param   string orderId    订单编号
     * @param   string openId     用户openId
     * @param   intval appId      登录方式，认证体系APPID：0：通用，1为微信公众号，2为手机号...
     * @param   string userId     用户userId
     *
     * @return  {"seatinfo":{"sUnlockedTempOrderId":"150910170947199073","sInfo":"success",
     * "sUnlockedSeatLable":"01:5:7|01:6:9","iSubCode":"0","sUnlockedMpId":"130629586","iRetCode":"0"},"ret":"0","sub":"0","msg":"success"}
     */
    public function unlockSeat(array $arrInput = [])
    {
        //参数拼装
        $arrSendParams = [];
        $arrSendParams['orderId'] = self::getParam($arrInput, 'orderId');
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['appId'] = self::getParam($arrInput, 'appId');
        $arrSendParams['userId'] = self::getParam($arrInput, 'userId');
        //调用接口
        $arrReturn = self::getStOut();
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
            'iTimeout' => 2,
            'iTryTimes' => 1,
        ];
        $arrReturn = $this->http(JAVA_API_UNLOCK_BY_ORDER, $httpParams);

        return $arrReturn;

    }

    /**
     * 融合座位图和可售座位接口, 将数据整体返回给前端
     *
     * @note 在原有座位图基础上, 增加了一个字段, 表示这个座位是否可售
     *
     * @param   intval channelId            公众号缩写: 3微信电影票, 8 IOS, 9 安卓, 等
     * @param   intval roomId               影厅id
     * @param   string cinemaId             影院id
     * @param   string schedulePricingId    排期编号
     *
     * @return array
     */
    public function getMergedRoomSeat(array $arrInput = [])
    {
        //定义返回结果
        $arrReturn = self::getStOut();

        //处理业务逻辑
        //1.获取座位图数据
        $arrRoomParams = [];
        $arrRoomParams['cinemaId'] = self::getParam($arrInput, 'cinemaId');
        $arrRoomParams['roomId'] = self::getParam($arrInput, 'roomId');
        $arrRoomParams['channelId'] = self::getParam($arrInput, 'channelId');
        $arrCinemaRoomRes = $this->service('cinema')->readCinemaRoom($arrRoomParams);

        //2.获取可售座位接口
        $arrSeatParams = [];
        $arrSeatParams['cinemaNo'] = self::getParam($arrInput, 'cinemaId');
        $arrSeatParams['channelId'] = self::getParam($arrInput, 'channelId');
        $arrSeatParams['scheduleId'] = self::getParam($arrInput, 'scheduleId');
        $arrAvailableSeats = $this->getAvailableSeat($arrSeatParams);

        //3.融合座位图和接口中的可售座位
        return $this->_mergeRoomAndAvailableSeat($arrCinemaRoomRes, $arrAvailableSeats);
    }

    /**
     * @param   array $arrUnlockRes 解锁红包和座位的返回结果: {'ret':0,'sub':0,'msg':'success','data':{'bonusUnlock':1,'seatUnlock':0}}
     * @param   array $arrSeatRes 可售座位结果
     * @param   array $arrMobileRes 查询手机号结果: {"data":{"mobileNo":"15652673019"},"ret":"0","sub":"0","msg":"SUCCESS"}
     */
    private function _querySeatMergeResult($arrUnlockRes, $arrSeatRes, $arrMobileRes)
    {
        $arrReturn = self::getStOut();
        //参数校验
        if (empty($arrUnlockRes) || empty($arrSeatRes) || empty($arrMobileRes)) {
            $arrReturn['ret'] = ERROR_RET_SDK_MERFE_EEQUEST_EMPTY;
            $arrReturn['sub'] = ERROR_RET_SDK_MERFE_EEQUEST_EMPTY;
            $arrReturn['msg'] = ERROR_MSG_SDK_MERFE_EEQUEST_EMPTY;
        } else {
            $arrReturn['data']['mobile'] = (isset($arrMobileRes['ret']) && ($arrMobileRes['ret'] == 0) && isset($arrMobileRes['data']['mobileNo'])) ? $arrMobileRes['data']['mobileNo'] : '';
            $arrReturn['data']['unlockInfo'] = (isset($arrUnlockRes['ret']) && ($arrUnlockRes['ret'] == 0) && $arrUnlockRes['data']) ? $arrUnlockRes['data'] : '';
            $arrReturn['data']['seats'] = (isset($arrSeatRes['ret']) && ($arrSeatRes['ret'] == 0) && isset($arrSeatRes['data'])) ? $arrSeatRes['data'] : '';
        }

        return $arrReturn;
    }

    /**
     * 融合redis中座位图数据和Java接口中可售座位数据
     *
     * @todo 仅仅遍历了座位图, 便实现融合. 但可能还有更优的处理方式
     * @private
     *
     * @param   array $arrCinemaRoomRes 座位图
     * @param   array $arrAvailableSeats 可售座位
     *
     * @return  array
     */
    private function _mergeRoomAndAvailableSeat(array $arrCinemaRoomRes = [], array $arrAvailableSeats = [])
    {
        $arrReturn = self::getStOut();
        //如果座位图获取正常,并且可用座位接口数据正常,则进行合并
        if (isset($arrCinemaRoomRes['ret']) && ($arrCinemaRoomRes['ret'] == 0) && isset($arrAvailableSeats['ret']) && ($arrAvailableSeats['ret'] == 0)) {
            //解析Java可售座位为数组
            $arrAvailableSeatsFormat = $this->_formatAvailableSeat($arrAvailableSeats);
            foreach ($arrCinemaRoomRes['data']['sSeatInfo'] as $rowKey => $arrSeatRowInfo) {
                foreach ($arrSeatRowInfo['detail'] as $kno => $arrSeat) {
                    //遍历[格式化后]的Java可用座位,如果key在座位图中,则设置为可用
                    $strRoomSeatKey = $arrSeatRowInfo['area'] . '_' . $arrSeatRowInfo['row'] . '_' . $arrSeat['n'];
                    //如果座位的key,在可售座位key中,则表示可售
                    $arrAvailableSeatsFormatKeys = array_keys($arrAvailableSeatsFormat);
                    $arrCinemaRoomRes['data']['sSeatInfo'][$rowKey]['detail'][$kno]['available'] = 0;
                    $arrCinemaRoomRes['data']['sSeatInfo'][$rowKey]['detail'][$kno]['gold'] = 1;
                    if (in_array($strRoomSeatKey, $arrAvailableSeatsFormatKeys)) {
                        $arrCinemaRoomRes['data']['sSeatInfo'][$rowKey]['detail'][$kno]['available'] = $arrAvailableSeatsFormat[$strRoomSeatKey]['available'];
                        $arrCinemaRoomRes['data']['sSeatInfo'][$rowKey]['detail'][$kno]['gold'] = $arrAvailableSeatsFormat[$strRoomSeatKey]['gold'];
                    }
                }
            }
            $arrReturn = $arrCinemaRoomRes;
        }
        //数据不正常的处理情况
        //
        else {
            if (isset($arrCinemaRoomRes['ret']) && ($arrCinemaRoomRes['ret'] != 0)) {
                $arrReturn['ret'] = ERROR_RET_STATIC_DATA_CINEMA_ROOM_EMPTY;
                $arrReturn['sub'] = ERROR_RET_STATIC_DATA_CINEMA_ROOM_EMPTY;
                $arrReturn['msg'] = ERROR_MSG_STATIC_DATA_CINEMA_ROOM_EMPTY;
                $arrReturn['data'] = '';
            } elseif (isset($arrAvailableSeats['ret']) && ($arrAvailableSeats['ret'] != 0)) {
                $arrReturn['ret'] = ERROR_RET_JAVA_AVAILABLE_SEAT;
                $arrReturn['sub'] = ERROR_RET_JAVA_AVAILABLE_SEAT;
                $arrReturn['msg'] = ERROR_MSG_JAVA_AVAILABLE_SEAT;
                $arrReturn['data'] = '';
            }
        }

        return $arrReturn;
    }

    /**
     * 格式化从Java获取的可售座位
     * 为了让可售座位与座位图融合遍历更方便,甚至一次遍历即可完成,我把该方法的返回结果,做成[01_1_16=>['gold'=>0,'available'=>1],...]这种形式
     *
     * @param $arrAvailableSeats
     *
     * @return array
     */
    private function _formatAvailableSeat($arrAvailableSeats)
    {
        $arrReturn = [];
        $strAvaliableSeat = $arrAvailableSeats['data'];
        //如果Java结果data为空
        if (empty($strAvaliableSeat)) {
            return $arrReturn;
        }
        //java结果data不为空
        $arrRows = explode('|', $strAvaliableSeat);
        if (!empty($arrRows)) {
            $arrReturnSeats = [];
            foreach ($arrRows as $strRow) {
                //$strRow: 01:1:1-1,2-1,3-1,4-1,5-1,6-1,7-1,8-1,9-1,10-1,11-1,12-1
                $arrRowInfo = explode(':', $strRow);
                $areaNo = $arrRowInfo[0];
                $rowNo = $arrRowInfo[1];
                $sRowSeat = $arrRowInfo[2];
                $arrRowSeat = explode(',', $sRowSeat);  //分割成 ['1 - 1','2 - 1','3 - 1','4 - 0']这种格式化, 1-1表示第一个座位为非自由库存
                $arrSaveRowInfo['row'] = $rowNo;
                $arrSaveRowInfo['area'] = $areaNo;
                $arrSaveRowInfo['detail'] = [];
                //遍历,为detail添加每一个座位的内容
                foreach ($arrRowSeat as $sSeat) {
                    //sSeat内容格式如: 5-1
                    $arrSeat = explode('-', $sSeat);
                    $seatNo = $arrSeat[0];
                    $key = $areaNo . '_' . $rowNo . '_' . $seatNo;
                    //黄金座位, 就是自由库存的座位, 标识为0, 非自由库存的标识为1
                    $value = ['gold' => $arrSeat[1], 'available' => 1];
                    $arrReturnSeats[$key] = $value;
                }
            }
            $arrReturn = $arrReturnSeats;
            unset($arrReturnSeats);
        }

        return $arrReturn;
    }

    /**
     * 获取可售座位
     *
     * @param   intval channelId    公众号缩写: 3微信电影票, 8 IOS, 9 安卓, 等
     * @param   string cinemaNo     影院Id,如: 1000103
     * @param   string scheduleId   排期编号
     * @param   string requestId    requestId，最好是传入此字段，否则
     */
    public function getAvailableSeat(array $arrInput = [])
    {
        //参数拼装
        $arrSendParams = [];
        $arrSendParams['scheduleId'] = self::getParam($arrInput, 'scheduleId');
        $arrSendParams['cinemaNo'] = self::getParam($arrInput, 'cinemaNo');
        $arrSendParams['tpId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['requestId'] = $this->getReqiuseId();
        if (empty($arrSendParams['requestId'])) {
            throw new \InvalidArgumentException(ERROR_MSG_LACK_OF_REQUESTID, ERROR_RET_LACK_OF_REQUESTID);
        }
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'GET',
        ];
        $arrReturn = $this->http(JAVA_API_AVAILABLE, $httpParams);

        return $arrReturn;
    }

    /**
     * 获取不可售座位(新版订单中心 兼容APP)
     *
     * @param string  schedulePricingId 排期ID
     * @param string  cinemaNo  影院ID
     * @param string  channelId 渠道号
     */
    public function getUnvailableSeat(array $arrInput = [])
    {
        $arrSendParams = [];
        $arrSendParams['schedulePricingId'] = self::getParam($arrInput, 'schedulePricingId');
        $arrSendParams['cinemaNo'] = self::getParam($arrInput, 'cinemaNo');
        $arrSendParams['tpId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['requestId'] = $this->getReqiuseId();
        if (empty($arrSendParams['requestId'])) {
            throw new \InvalidArgumentException(ERROR_MSG_LACK_OF_REQUESTID, ERROR_RET_LACK_OF_REQUESTID);
        }
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $arrReturn = $this->http(JAVA_API_UNAVAILABLE, $httpParams);

        return $arrReturn;
    }

    /**
     * 获取座位图接口(融合版)
     * 注意: 自 2016.10.25日起, 座位图接口都需要调用此融合版本。不需要请求座位图后再次调用可售座位图接口, 来实现需求
     *
     * @param      string    mpId                排期编号
     * @param      string    channelId           渠道编号
     * @param      string    cinemaId            影院id
     * @param      string    movieId             影片id
     * @param      string    salePlatformType    售卖平台类型 枚举 1 公众号、2渠道, 除了智慧影院,都传2
     *
     * @return array
     */
    public function qryMergeSeats($arrInput = [])
    {
        $return = self::getStOut();
        $strErrorMsg = '抱歉，该影院网络或系统异常，请更换其他场次，或稍候重试';
        $arrParams = [];
        //获取所有参数
        $arrParams['scheduleId'] = self::getParam($arrInput, 'mpId');           //排期编号
        $arrParams['channelId'] = self::getParam($arrInput, 'channelId', 3);     //渠道编号
        $arrParams['cinemaNo'] = self::getParam($arrInput, 'cinemaId');         //影院id
        $arrParams['movieId'] = self::getParam($arrInput, 'movieId');         //影片id

        //注意，获取可售座位异常的情况，前端需要弹层显示，因此，即使参数错误，这里的文案，也必须是产品定的文案
        if (empty($arrParams['scheduleId']) || empty($arrParams['channelId']) || empty($arrParams['cinemaNo'])) {
            $return['ret'] = $return['sub'] = -1;
            $return['msg'] = $strErrorMsg;

            return $return;
        }

        //1、获取未支付订单，拿到临时订单编号 $strUnPayedOrderId 以及 排期批价Id ，解锁座位
        $arrUserLockParams = [
            'publicSignalShort' => self::getParam($arrInput, 'channelId', 3),
            'salePlatformType' => self::getParam($arrInput, 'salePlatformType'),
            'openId' => self::getParam($arrInput, 'openId'),
            'appId' => self::getParam($arrInput, 'appId'),
            'userId' => self::getParam($arrInput, 'userId'),
        ];

        $unlockType = !empty(\wepiao::$config['unlockType']) ? \wepiao::$config['unlockType'] : 0;
        $intBackRes = 0;
        if ($unlockType == 0) {
            $res = $this->service('order')->queryUnPayOrder($arrUserLockParams);
            $strUnPayedOrderId = ($res['ret'] == 0) && ($res['sub'] == 0) && isset($res['lockinfo']['sTempOrderID']) ? $res['lockinfo']['sTempOrderID'] : '';
            $strSchedulePricingId = ($res['ret'] == 0) && ($res['sub'] == 0) && isset($res['lockinfo']['schedulePricingId']) ? $res['lockinfo']['schedulePricingId'] : '';
            //2、解锁优惠和座位（如果有未支付订单，解锁座位、解锁当前用户的可用优惠）
            if (!empty($strUnPayedOrderId)) {
                $arrUnlockParams = [
                    'publicSignalShort' => $arrUserLockParams['publicSignalShort'],
                    'salePlatformType' => $arrUserLockParams['salePlatformType'],
                    'orderId' => $strUnPayedOrderId,
                    'scheduleId' => $strSchedulePricingId,
                    'sUin' => $arrUserLockParams['openId'],
                    'appId' => $arrUserLockParams['appId'],
                    'userId' => $arrUserLockParams['userId'],
                    'openId' => $arrUserLockParams['openId'],
                ];
                $res = $this->unlockSeatAndBonus($arrUnlockParams);
                if (isset($res['ret']) && $res['ret'] == 0) {
                    $intBackRes = !empty($res['seatUnlock']) ? $res['seatUnlock'] : 0;
                }
            }
        } else {
            $arrUserLockParams['forceGet'] = 1;
            $arrUserLockParams['limit'] = 1;
            $unpayData = $this->service('order')->queryUnPayOrderMultiV1($arrUserLockParams);
            if (!empty($unpayData['lockinfo']) && is_array($unpayData['lockinfo'])) {
                foreach ($unpayData['lockinfo'] as $tmpOrderInfo) {
                    $strUnPayedOrderId = !empty($tmpOrderInfo['sTempOrderID']) ? $tmpOrderInfo['sTempOrderID'] : '';
                    $strSchedulePricingId = !empty($tmpOrderInfo['schedulePricingId']) ? $tmpOrderInfo['schedulePricingId'] : '';
                    if (!empty($strUnPayedOrderId)) {
                        $arrUnlockParams = [
                            'channelId' => $arrUserLockParams['publicSignalShort'],
                            'salePlatformType' => $arrUserLockParams['salePlatformType'],
                            'orderId' => $strUnPayedOrderId,
                            'scheduleId' => $strSchedulePricingId,
                            'sUin' => $arrUserLockParams['openId'],
                            'appId' => $arrUserLockParams['appId'],
                            'userId' => $arrUserLockParams['userId'],
                            'openId' => $arrUserLockParams['openId'],
                        ];
                        $res = $this->unlockSeatAndBonus($arrUnlockParams);
                        if (isset($res['ret']) && $res['ret'] == 0) {
                            $intBackRes = !empty($res['seatUnlock']) ? $res['seatUnlock'] : 0;
                        }
                    }
                }
            }
        }

        //2、获取融合版座位图
        $return = $this->service('Goods')->qryMergedSeats($arrParams);
        if (isset($return['ret']) && ($return['ret'] == 0) && !empty($return['data'])) {
            $iTotalSeatNum = !empty($return['data']['totalSeats']) ? $return['data']['totalSeats'] : 0;
            $iLeftSeatNum = !empty($return['data']['salableSeats']) ? $return['data']['salableSeats'] : 0;
            //是否有好座
            $iBestSeats = !empty($return['data']['bestAvailable']) ? 1 : 0;
            //兼容处理，如果剩余座位<0，按0处理
            if ($iLeftSeatNum < 0) {
                $iLeftSeatNum = 0;
            }
            //保存剩余座位数
            if ($iTotalSeatNum > 0) {
                $this->service('Goods')->saveSeatNuForWithLeftSeat([
                    'totalSeatNum' => $iTotalSeatNum,
                    'leftSeatNum' => $iLeftSeatNum,
                    'cinemaId' => $arrParams['cinemaNo'],
                    'schduleId' => $arrParams['scheduleId'],
                    'channelId' => $arrParams['channelId'],
                    'bestSeats'=>$iBestSeats
                ]);
            }
        } else {
            $return['msg'] = $strErrorMsg;
        }

        return $return;
    }

    /**
     * 获取[可售]座位新版
     * 新版融合了 获取未支付订单、解锁座位、解锁优惠、判断用户是否有手机号、获取不可售座位4个接口
     * 该接口如果未能正常处理可售座位，会返回产品之前定义的，获取不可售座位时的错误文案
     *
     * @param      int       wanda               是否为万达
     * @param      string    scheduleId          排期编号
     * @param      string    channelId           渠道编号
     * @param      string    cinemaId            影院id
     * @param      string    roomId              影厅id
     * @param      string    bisServerId         接入商server id，这个主要用于做隔离部署用
     * @param      string    openId              用户openId，这个主要用于释放用户的座位和优惠
     * @param      string    appId               登录方式，认证体系APPID：0：通用，1为微信公众号，2为手机号...
     * @param      string    salePlatformType    售卖平台类型 枚举 1 公众号、2渠道, 除了智慧影院,都传2
     * @param      int       userId              用户Id,如果没有,可传0, 需要打通账号体系来支撑
     *
     * @return array
     */
    public function qryAvailableSeats($arrInput = [])
    {
        $return = self::getStOut();
        $strErrorMsg = '抱歉，该影院网络或系统异常，请更换其他场次，或稍候重试';

        //获取所有参数
        $iWanda = self::getParam($arrInput, 'wanda', false);                    //是否为万达
        $strScheduleId = self::getParam($arrInput, 'scheduleId');        //排期编号
        $iChannelId = self::getParam($arrInput, 'channelId');                   //渠道编号
        $iCinemaId = self::getParam($arrInput, 'cinemaId');                     //影院id
        $strRoomId = self::getParam($arrInput, 'roomId');                       //影厅id
        $strBisServerId = self::getParam($arrInput, 'bisServerId');             //接入商server id
        $strOpenId = self::getParam($arrInput, 'openId');                       //用户openId
        $iAppId = self::getParam($arrInput, 'appId');                           //应用id
        $isalePlatformType = self::getParam($arrInput, 'salePlatformType', 2);  //售卖平台
        $iUserId = self::getParam($arrInput, 'userId');                         //用户id

        //注意，获取可售座位异常的情况，前端需要弹层显示，因此，即使参数错误，这里的文案，也必须是产品定的文案
        if (empty($iChannelId) || empty($strScheduleId) || empty($iCinemaId) || empty($strRoomId) || empty($iAppId) || empty($strOpenId)) {
            $return['ret'] = $return['sub'] = -1;
            $return['msg'] = $strErrorMsg;

            return $return;
        }

        //1、获取未支付订单，拿到临时订单编号 $strUnPayedOrderId 以及 排期批价Id ，解锁座位
        $arrUserLockParams = [
            'publicSignalShort' => $iChannelId,
            'salePlatformType' => $isalePlatformType,
            'openId' => $strOpenId,
            'appId' => $iAppId,
            'userId' => $iUserId,
        ];

        $unlockType = !empty(\wepiao::$config['unlockType']) ? \wepiao::$config['unlockType'] : 0;
        $intBackRes = 0;
        if ($unlockType == 0) {
            $res = $this->service('order')->queryUnPayOrder($arrUserLockParams);
            $strUnPayedOrderId = ($res['ret'] == 0) && ($res['sub'] == 0) && isset($res['lockinfo']['sTempOrderID']) ? $res['lockinfo']['sTempOrderID'] : '';
            $strSchedulePricingId = ($res['ret'] == 0) && ($res['sub'] == 0) && isset($res['lockinfo']['schedulePricingId']) ? $res['lockinfo']['schedulePricingId'] : '';
            //2、解锁优惠和座位（如果有未支付订单，解锁座位、解锁当前用户的可用优惠）
            if (!empty($strUnPayedOrderId)) {
                $arrUnlockParams = [
                    'publicSignalShort' => $iChannelId,
                    'salePlatformType' => $isalePlatformType,
                    'orderId' => $strUnPayedOrderId,
                    'scheduleId' => $strSchedulePricingId,
                    'sUin' => $strOpenId,
                    'appId' => $iAppId,
                    'userId' => $iUserId,
                    'openId' => $strOpenId,
                ];
                $res = $this->unlockSeatAndBonus($arrUnlockParams);
                if (isset($res['ret']) && $res['ret'] == 0) {
                    $intBackRes = !empty($res['seatUnlock']) ? $res['seatUnlock'] : 0;
                }
            }
        } else {
            $arrUserLockParams['forceGet'] = 1;
            $arrUserLockParams['limit'] = 1;
            $unpayData = $this->service('order')->queryUnPayOrderMultiV1($arrUserLockParams);
            if (!empty($unpayData['lockinfo']) && is_array($unpayData['lockinfo'])) {
                foreach ($unpayData['lockinfo'] as $tmpOrderInfo) {
                    $strUnPayedOrderId = !empty($tmpOrderInfo['sTempOrderID']) ? $tmpOrderInfo['sTempOrderID'] : '';
                    $strSchedulePricingId = !empty($tmpOrderInfo['schedulePricingId']) ? $tmpOrderInfo['schedulePricingId'] : '';
                    if (!empty($strUnPayedOrderId)) {
                        $arrUnlockParams = [
                            'publicSignalShort' => $iChannelId,
                            'salePlatformType' => $isalePlatformType,
                            'orderId' => $strUnPayedOrderId,
                            'scheduleId' => $strSchedulePricingId,
                            'sUin' => $strOpenId,
                            'appId' => $iAppId,
                            'userId' => $iUserId,
                            'openId' => $strOpenId,
                        ];
                        $res = $this->unlockSeatAndBonus($arrUnlockParams);
                        if (isset($res['ret']) && $res['ret'] == 0) {
                            $intBackRes = !empty($res['seatUnlock']) ? $res['seatUnlock'] : 0;
                        }
                    }
                }
            }
        }

        //3、获取用户手机号
        $strMobileNo = '';
        $arrMobileParams = [
            'channelId' => $iChannelId,
            'salePlatformType' => $isalePlatformType,
            'openId' => $strOpenId,
            'appId' => $iAppId,
            'userId' => $iUserId,
        ];
        $arrMobileRes = $this->service('User')->getMobile($arrMobileParams);
        $strMobileNo = (isset($arrMobileRes['ret']) && ($arrMobileRes['ret'] == 0) && isset($arrMobileRes['data']['mobileNo'])) ? $arrMobileRes['data']['mobileNo'] : '';

        //4、如获取不可售座位图
        $arrSeatsParams = [
            'scheduleId' => $strScheduleId,
            'channelId' => $iChannelId,
            'cinemaId' => $iCinemaId,
            'bisServerId' => $strBisServerId,
        ];
        $arrUnsoldSeatAndLocalSeats = $this->service('Goods')->qryUnsoldSeatsAndLocalSeats($arrSeatsParams);

        //调用Java接口失败
        if ($arrUnsoldSeatAndLocalSeats['ret'] != 0 || $arrUnsoldSeatAndLocalSeats['sub'] != 0) {
            $return['ret'] = $return['sub'] = $arrUnsoldSeatAndLocalSeats['ret'];
            $return['msg'] = $strErrorMsg;

            return $return;
        }

        //获取座位图
        $strAvaliableSeatsInfo = '';
        $arrRoomInfoRes = $this->service('Cinema')->readCinemaRoom(['cinemaId' => $iCinemaId, 'roomId' => $strRoomId, 'channelId' => $iChannelId]);
        $arrRoomInfo = (isset($arrRoomInfoRes['ret']) && ($arrRoomInfoRes['ret'] == 0) && isset($arrRoomInfoRes['data'])) ? $arrRoomInfoRes['data'] : '';
        //做不可售座位+自有库存的可售座位，和座位图的融合，得到可售座位
        if (!empty($arrRoomInfo)) {
            $arrRes = $this->service('Goods')->getAvaliableSeatsByRoomInfoAndUnsold($arrRoomInfo, $arrUnsoldSeatAndLocalSeats['data']);
            $strAvaliableSeatsInfo = (isset($arrRes['strAvaliableSeats']) && is_string($arrRes['strAvaliableSeats'])) ? $arrRes['strAvaliableSeats'] : '';
            $iLeftSeatNum = (isset($arrRes['leftSeatNum']) && is_numeric($arrRes['leftSeatNum'])) ? $arrRes['leftSeatNum'] : 0;
            $iTotalSeatNum = (isset($arrRes['totalSeatNum']) && is_numeric($arrRes['totalSeatNum'])) ? $arrRes['totalSeatNum'] : 0;
            //保存剩余座位数
            if ($iTotalSeatNum > 0) {
                $this->service('Goods')->saveSeatNuForWithLeftSeat([
                    'totalSeatNum' => $iTotalSeatNum,
                    'leftSeatNum' => $iLeftSeatNum,
                    'roomId' => $strRoomId,
                    'cinemaId' => $iCinemaId,
                    'schduleId' => $strScheduleId,
                    'channelId' => $iChannelId,
                ]);
            }
        } else {
            //获取座位图失败的情况
            $return['ret'] = $return['sub'] = '-19';
            $return['msg'] = $strErrorMsg;

            return $return;
        }

        //拼装返回结果
        $return['data'] = [
            'seats' => $strAvaliableSeatsInfo,
            'mobile' => $strMobileNo,
            'bonusUnlock' => $intBackRes,
            'seatUnlock' => 0,
        ];

        return $return;
    }

}