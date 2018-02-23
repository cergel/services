<?php

namespace sdkService\service;

use sdkService\helper\Utils;

/**
 * Class serviceOrder
 *
 * @package app\service
 */
class serviceOrder extends serviceBase
{

    /**
     * 获取未支付订单
     *
     * @note 该接口, 若除微信电影票之外的渠道使用, 需要测试,因为这里有个 seatLable格式化的操作, 比如电影票就做了格式化,APP却没有
     *
     * @param string openId             用户openId
     * @param string channelId          公众号缩写 电影票:3, IOS:8, Android:9
     * @param string salePlatformType   售卖平台类型 枚举 1 公众号、2渠道, 除了智慧影院,都传2
     * @param intval appId              登录方式，认证体系APPID：0：通用，1为微信公众号，2为手机号...
     * @param intval userId             用户Id,如果没有,可传0, 需要打通账号体系来支撑
     *
     * @return array
     */
    public function queryUnPayOrder(array $arrInput = [])
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['publicSignalShort'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['salePlatformType'] = self::getParam($arrInput, 'salePlatformType');
        $arrSendParams['appId'] = self::getParam($arrInput, 'appId');
        $arrSendParams['userId'] = self::getParam($arrInput, 'userId');
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $lockSeatRes = $this->http(JAVA_API_UNPAY_ORDER, $httpParams);
        //if ($lockSeatRes['ret'] == 0 && $lockSeatRes['lockinfo']) {
        //    $lockSeatRes['lockinfo']['refundMsg'] = $this->model('order')->getRefundContent((string)$lockSeatRes['lockinfo']['refundType']);
        //}

        return $lockSeatRes;
    }

    /**
     * 获取已支付订单
     *
     * @param string openId             用户openId
     * @param string channelId          公众号缩写 电影票:3, IOS:8, Android:9
     * @param string salePlatformType   售卖平台类型 枚举 1 公众号、2渠道, 除了智慧影院,都传2
     * @param intval appId              登录方式，认证体系APPID：0：通用，1为微信公众号，2为手机号...
     * @param intval userId             用户Id,如果没有,可传0, 需要打通账号体系来支撑
     * @param intval page               页码, 默认取第一页
     * @param intval num                单页的数量, 默认取全部, 设置1000条
     *
     * @return array
     */
    public function queryPaidOrder(array $arrInput = [])
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['publicSignalShort'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['salePlatformType'] = self::getParam($arrInput, 'salePlatformType');
        $arrSendParams['appId'] = self::getParam($arrInput, 'appId');
        $arrSendParams['userId'] = self::getParam($arrInput, 'userId');
        $arrSendParams['page'] = self::getParam($arrInput, 'page', 1);
        $arrSendParams['num'] = self::getParam($arrInput, 'num', 1000);
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $return = static::getStOut();
        //Java接口有问题，如果没有订单，返回结果是ret=-1，所以需要将这种状况处理一下
        $arrData = $this->http(JAVA_API_QUERY_ORDER, $httpParams);
        if ($arrData['ret'] == 0) {
            $return = $arrData;
            //格式化已经删除的订单
            self::__FormatDeleteOrder($arrSendParams['publicSignalShort'], $return['data']);
        }

        return $return;
    }

    /**
     * 根据订单ID 查询订单信息
     *
     * @param string orderId 订单编号
     * @param string openId  用户的openId
     *
     * @return array
     */
    public function queryOrderinfo(array $arrInput = [])
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['orderId'] = self::getParam($arrInput, 'orderId');
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $arrOrderInfo = $this->http(JAVA_API_QUERY_ORDER_BY_ID, $httpParams);
        if ($arrOrderInfo['ret'] == 0 && $arrOrderInfo['sub'] == 0) {
            // if (isset($arrOrderInfo['data']['refundFlg'])) {
            //    $arrOrderInfo['data']['refundMsg'] = $this->model('order')->getRefundContent((string)$arrOrderInfo['data']['refundFlg']);
            //}
        }

        return $arrOrderInfo;
    }

    /**
     * 【新版】订单详情接口
     * 新版和旧版(queryOrderinfo)之所以要区别,是因为改签功能上了之后,老订单会有改签订单,这样旧版APP会崩溃。而且新版订单返回内容也有变化。
     * 另外,接口之所以叫V1,也是因为订单中心的此版本接口也是V1版
     *
     * @param string orderId    订单编号
     * @param string openId     用户的openId
     * @param string payStatus  疑似支付判断(默认为0,如果是1,查询未支付订单则不会再查询出来此订单)
     * @param string yupiaoRefundShow  区分不同页面文案(默认为true订单详情页,如果是false,查询未支付订单则不会再查询出来此订单)
     * @return array
     */
    public function queryOrderinfoV1(array $arrInput = [])
    {
        //参数处理
        $arrReturn = self::getStOut();
        $arrReturn['data'] = new \stdClass();
        $arrSendParams = [];
        $arrSendParams['channelId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['orderId'] = self::getParam($arrInput, 'orderId');
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['payStatus'] = self::getParam($arrInput, 'payStatus', 0);
        $arrSendParams['yupiaoRefundShow'] = self::getParam($arrInput, 'yupiaoRefundShow', 'true');
        $realOpenId= self::getParam($arrInput, 'realOpenId');
        $realChannelId= self::getParam($arrInput, 'realChannelId');
        if(!empty($realOpenId) && !empty($realChannelId)){
            $arrSendParams['openId'] = $realOpenId;
            $arrSendParams['channelId'] = $realChannelId;
        }
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $arrOrderInfo = $this->http(JAVA_API_QUERY_ORDER_INFO_V1, $httpParams);
        if ($arrOrderInfo['ret'] == 0 && $arrOrderInfo['sub'] == 0) {
            //文案内容处理
            $arrOrderInfo = $this->msgControl($arrOrderInfo, $arrSendParams , 2);
            //增加影片海报图
            if (isset($arrOrderInfo['data']['movieInfo']['id'])) {
                $arrOrderInfo['data']['movieInfo']['posterUrl'] = 'https://appnfs.wepiao.com/dataImage/movie/poster.jpg';
                $arrMovieInfo = $this->service('movie')->readMovieInfoNewStatic(['channelId' => $arrInput['channelId'], 'movieId' => $arrOrderInfo['data']['movieInfo']['id']]);
                if (!empty($arrMovieInfo['data']['poster_url'])) {
                    $arrOrderInfo['data']['movieInfo']['posterUrl21'] = $arrMovieInfo['data']['poster_url_size21'];
                }
            }
            //判断用户是否收藏过此影院
            $iFavor = $this->isFavorite($arrOrderInfo, $arrSendParams);
            if (!empty($arrOrderInfo['data'])) {
                $arrOrderInfo['data']['cinemaFavor'] = $iFavor;
                $arrReturn['data'] = $arrOrderInfo['data'];
            }
        } else {
            $arrReturn['ret'] = $arrOrderInfo['ret'];
            $arrReturn['sub'] = $arrOrderInfo['sub'];
            $arrReturn['msg'] = $arrOrderInfo['msg'];
        }

        return $arrReturn;
    }

    /**
     * 退款接口
     *
     * @param string orderId            订单编号
     * @param string refundReason       退款原因
     * @param string openId             用户openId
     * @param string channelId          渠道编号
     * @param string salePlatformType   售卖平台
     * @param string openId             用户openId
     *
     * @return array
     */
    public function refund(array $arrInput = [])
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['orderId'] = self::getParam($arrInput, 'orderId');
        $arrSendParams['refundReason'] = self::getParam($arrInput, 'refundReason');
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['publicSignalShort'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['salePlatformType'] = self::getParam($arrInput, 'salePlatformType');
        $arrSendParams['appId'] = self::getParam($arrInput, 'appId');
        $arrSendParams['userId'] = self::getParam($arrInput, 'userId');
        $arrReturn = self::getStOut();
        //参数判断
        if (empty($arrSendParams['orderId']) || empty($arrSendParams['refundReason'])) {
            $arrReturn['ret'] = $arrReturn['sub'] = ERROR_RET_ORDER_REFUND_PARAM_ERROR;
            $arrReturn['msg'] = ERROR_MSG_ORDER_REFUND_PARAM_ERROR;
        } elseif (empty($arrSendParams['openId'])) {
            $arrReturn['ret'] = $arrReturn['sub'] = ERROR_RET_ORDER_REFUND_OPENID_ERROR;
            $arrReturn['msg'] = ERROR_MSG_ORDER_REFUND_OPENID_ERROR;
        }
        //如果参数有误
        if ($arrReturn['ret'] != 0) {
            return $arrReturn;
        }
        //如果参数正常, 调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
            'iTimeout' => 15,
        ];
        $arrOrderInfo = $this->http(JAVA_API_REFUND_ORDER, $httpParams);

        return $arrOrderInfo;
    }

    /**
     *
     * @param       $channelId
     * @param array $OrderList
     */
    private function __FormatDeleteOrder($channelId, &$OrderList = [])
    {
        $Orders = $OrderIds = [];
        //新版用户中心订单列表可能为null而非空数组
        if ($OrderList == null) {
            $OrderList = [];
        }

        foreach ($OrderList as $Item) {
            //if (isset($Item['refundFlg'])) {
            //    $Item['refundMsg'] = $this->model('order')->getRefundContent((string)$Item['refundFlg']);
            //}
            $Orders[$Item['order_id']] = $Item;
        }
        $OrderIds = array_keys($Orders);
        //查询本批订单号是否有被删除的
        $OrderDeleteList = [];
        $OrderDeleteList = $this->model('Order')->getOrderDelStatus($channelId, $OrderIds);
        foreach ($OrderDeleteList as $OrderId => $DelFlag) {
            if ($DelFlag) {
                unset($Orders[$OrderId]);
            }
        }
        $OrderList = array_values($Orders);
    }

    /*
     * 查询历史订单【新结构】
     * @param array $arrInput
     * @return mixed
     */
    public function queryHistoryOrderNew(array $arrInput = [])
    {
        //参数处理
        $openid = self::getParam($arrInput, 'openId');
        $pageIndex = self::getParam($arrInput, 'pageIndex');  //分页页数，非必须
        $pageSize = self::getParam($arrInput, 'pageSize');    //分页每页条数，非必须
        $orderType = self::getParam($arrInput, 'orderType');    //查询类型，1兑换券，2订单
        $daysBefore = self::getParam($arrInput, 'daysBefore');//天数，查询XX天前的数据，非必须
        $orderby = self::getParam($arrInput, 'orderby', 'dt desc');//排序规则，默认按时间倒排

        //查询关系树得到所有openid的集合
        $openidAll = [];
        $resOpenid = $this->service('user')->getIdRelation(['id' => $openid]);
        if ($resOpenid['ret'] == 0 && $resOpenid['sub'] == 0) {
            if ($resOpenid['data']['idRelation']['idType'] == 0) {
                $openidAll[] = $resOpenid['data']['idRelation']['id'];
            } else {
                foreach ($resOpenid['data']['idRelation']['idUnderBound'] as $item) {
                    if (in_array($item['idType'], ['11', '12', '13'])) { //微信、手Q、手机号
                        $openidAll[] = $item['id'];
                    } elseif ($item['idType'] == 30) {  //是unionid时取其下的openid
                        foreach ($item['idUnderBound'] as $wxitem) {
                            $openidAll[] = $wxitem['id'];
                        }
                    }
                }

            }
        }

        $response = $this->model('order')->getHistoryOrderFromDBNew($openidAll, $pageIndex, $pageSize, $orderType, $daysBefore, $orderby);

        //过滤已删除订单
        if (!empty($response['data'])) {
            self::__FormatDeleteOrder($arrInput['channelId'], $response['data']);
        }

        return $response;
    }

    /**
     * 删除订单接口
     * 目前没校验openId和订单的openID是否为同一用户
     *
     * @param string orderId            订单编号
     * @param string openId             用户openId
     * @param string channelId          渠道编号
     *
     * @return array
     */
    public function delOrder(array $arrInput = [])
    {
        $openId = self::getParam($arrInput, 'openId', '');
        $orderId = self::getParam($arrInput, 'orderId', '');
        $arrReturn = self::getStOut();

        if (empty($orderId)) {
            return self::getErrorOut(ERRORCODE_DEL_ORDER_ORDERID_ERROR);
        }
        if (empty($openId)) {
            return self::getErrorOut(ERRORCODE_DEL_ORDER_OPENID_ERROR);
        }
        //改为调取订单中心删除订单
        //$ret = $this->model('Order')->delOrder($arrInput['channelId'], $orderId);
        $params = [];
        $params['arrData']['openId'] = $openId;
        $params['arrData']['orderId'] = $orderId;
        $arrData = $this->http(JAVA_API_DEL_ORDER, $params);
        if ($arrData['ret'] == 0) {
            $arrReturn = $arrData;
        } else {
            $arrReturn = self::getErrorOut(ERRORCODE_DEL_ORDER_REDIS_ERROR);
        }

        return $arrReturn;
    }

    /**
     * 查询“我的”订单列表新版
     *
     * @param array $arrInput
     *        string expireFlag 查询的订单类型,0表示已过期,1表示未过期,空表示所有类型
     *
     * @return array
     */
    public function queryOrderList($arrInput = [])
    {
        $url = JAVA_API_ORDER_LIST;
        $params['sMethod'] = 'post';
        $params['arrData'] = [
            'openId' => self::getParam($arrInput, 'openId'),
            'page' => self::getParam($arrInput, 'page', 1),
            'num' => self::getParam($arrInput, 'num', 10),
            'channelId' => self::getParam($arrInput, 'channelId'),  //前端一般对应publicSignalShort参数
            'salePlatformType' => self::getParam($arrInput, 'salePlatformType'),//售卖平台
            'appId' => self::getParam($arrInput, 'appId'),      //前端对应iAppId
            'userId' => self::getParam($arrInput, 'userId'),    //前端对应sUserId
            'expireFlag' => self::getParam($arrInput, 'expireFlag'),    //expireFlag获取订单的类型
            'types' => self::getParam($arrInput, 'types', "2,24"),    //过滤订单类型只保留订单和订单+小吃
        ];
        $res = $this->http($url, $params);
        if ($res['ret'] == 0 && $res['sub'] == 0) {
            foreach ($res['orderList'] as $strKey => $arr) {
                $res['orderList'][$strKey]['total_fee'] = @$arr['totalPrice'] / 100;
                $res['orderList'][$strKey]['cinema_address'] = @$arr['cinemaAddr'];
                $res['orderList'][$strKey]['show_date_has_weekday'] = @$arr['show_date'];
                #$keys_arr = explode('|', $res['orderList'][$strKey]['cd_key']);
                //if (isset($res['orderList'][$strKey]['refundFlg'])) {
                // $res['orderList'][$strKey]['refundMsg'] = $this->model('order')->getRefundContent((string)$res['orderList'][$strKey]['refundFlg']);
                //}
            }
            //过滤已删除订单
            if (!empty($res['orderList'])) {
                self::__FormatDeleteOrder($arrInput['channelId'], $res['orderList']);
            }

            $arrReturn = [
                'ret' => 0,
                'sub' => 0,
                'orderList' => $res['orderList'],
                'total_row' => $res['total_row'],
                'page' => $res['page'],
                'num' => $res['num'],
            ];

        } else {
            $arrReturn = $res;
        }

        return $arrReturn;
    }

    /**
     * 【新版】订单列表接口
     * 新版和旧版(queryOrderList)之所以要区别,是因为改签功能上了之后,老订单列表会有改签订单,这样旧版APP会崩溃。而且新版订单返回内容也有变化。
     * 另外,接口之所以叫V1,也是因为订单中心的此版本接口也是V1版
     * 此订单列表,包含成功改签后的订单
     *
     * @param  string openId        用户openId
     * @param  string channelId     渠道id(枚举值{3:微信电影票, 8:ios, 9:android, 28:手Q})
     * @param  string types         要产线的订单类型(枚举值{2:电影票, 4:卖品, 24:电影票+卖品}, 查询多中类型用,分隔)
     * @param  int    expireFlag    枚举值{0:已过期, 1:未过期}, 默认返回全部
     * @param  int    page          页码
     * @param  int    num           每页返回的个数
     *
     * @return array
     */
    public function queryOrderListV1($arrInput = [])
    {
        $arrReturn = self::getStOut();
        $url = JAVA_API_ORDER_LIST_V1;
        $params['sMethod'] = 'POST';
        $params['arrData'] = [
            'openId' => self::getParam($arrInput, 'openId'),
            'channelId' => self::getParam($arrInput, 'channelId'),
            'types' => self::getParam($arrInput, 'types', '2,4,24'),
            'expireFlag' => self::getParam($arrInput, 'expireFlag'),
            'page' => self::getParam($arrInput, 'page', 1),
            'num' => self::getParam($arrInput, 'num', 10),
        ];
        $res = $this->http($url, $params);
        if (isset($res['ret']) && !empty($res['data'])) {//添加评论标记
            //添加影片是否评论过字段
            self::__FormatCommented($params['arrData']['openId'], $params['arrData']['channelId'], $res['data']);
            //添加影片是否评论过字段
            self::__FormatMoviePoster($params['arrData']['channelId'],$res['data']);
        }
        return $res;
    }

    /**
     * 添加订单列表页“影片是否评论过”字段commented，0未评论1已评论
     * @param $openId
     * @param $channelId
     * @param array $OrderList
     */
    private function __FormatMoviePoster($channelId,&$orderList = [])
    {
        foreach($orderList as &$val){
            //获取影片海报
            $movieInfo = $this->service("movie")->readMovieInfo(['channelId' => $channelId, 'movieId' => $val['movieInfo']['id'], 'cityId' => 10]);
            if ($movieInfo['ret'] == 0 AND $movieInfo['sub'] == 0) {
                $val['poster_url'] = isset($movieInfo['data']['poster_url'])?$movieInfo['data']['poster_url']:'';
            } else {
                $val['poster_url'] = "";
            }
        }
    }

    /**
     *
     * @note 新版订单中心接口，与旧版的(上方queryOrderInfo)区别仅在于请求地址不一样，以及返回格式不一样
     *
     * @param array $arrInput *
     *
     * @return array
     */
    public function queryOrderInfoNew($arrInput = [])
    {
        $url = JAVA_API_ORDER_INFO;
        $params['sMethod'] = 'post';
        $params['arrData'] = [
            'openId' => self::getParam($arrInput, 'openId'),     //第三方帐号，必须
            'orderId' => self::getParam($arrInput, 'orderId'),    //订单号，必须
            'publicSignalShort' => self::getParam($arrInput, 'channelId'),  //前端一般对应publicSignalShort参数
            'salePlatformType' => self::getParam($arrInput, 'salePlatformType'),//售卖平台
            'appId' => self::getParam($arrInput, 'appId'),      //前端对应iAppId
            'userId' => self::getParam($arrInput, 'userId'),     //前端对应sUserId
            'yupiaoRefundShow' => self::getParam($arrInput, 'yupiaoRefundShow', 'true'),
        ];
        if (isset($arrInput['payStatus'])) {
            $params['arrData']['payStatus'] = $arrInput['payStatus'];
        }
        $res = $this->http($url, $params);
        if ($res['ret'] == 0 && $res['sub'] == 0) {
            //APP文案内容处理
            $res = $this->msgControl($res, $params['arrData'], 1);
        }

        return $res;
    }

    /**
     * 获取电影节订单列表（产品叫“我的日程”）
     *
     * @param string openId 用户openId
     * @param int    page 页码
     * @param int    num 每页条数
     * @param int    channelId 渠道id
     * @param int    salePlatformType 售卖平台
     * @param int    appId 登录方式，认证体系APPID：0：通用，1为微信公众号，2为手机号...
     * @param intval userId             用户Id,如果没有,可传0, 需要打通账号体系来支撑
     *
     * @return array|bool|mixed
     */
    public function queryFilmOrderList($arrInput = [])
    {
        $url = JAVA_API_FILM_ORDER_LIST;
        $params['sMethod'] = 'post';
        $params['arrData'] = [
            'openId' => self::getParam($arrInput, 'openId'),
            'page' => self::getParam($arrInput, 'page', 1),
            'num' => self::getParam($arrInput, 'num', 10),
            'channelId' => self::getParam($arrInput, 'channelId'),
            'salePlatformType' => self::getParam($arrInput, 'salePlatformType'),
            'appId' => self::getParam($arrInput, 'appId'),
            'userId' => self::getParam($arrInput, 'userId'),
            'startTime' => self::getParam($arrInput, 'startTime'),
        ];
        if (isset($arrInput['bisServerId']) && !empty($arrInput['bisServerId'])) {
            $params['arrData']['bisServerId'] = $arrInput['bisServerId'];
        }
        $res = $this->http($url, $params);
        if ($res['ret'] == 0 && $res['sub'] == 0) {
            if (!empty($res['orderList'])) {
                foreach ($res['orderList'] as $strKey => $arr) {
                    $res['orderList'][$strKey]['total_fee'] = !empty($arr['totalPrice']) ? ($arr['totalPrice'] / 100) : 0;
                    $res['orderList'][$strKey]['cinema_address'] = !empty($arr['cinemaAddr']) ? $arr['cinemaAddr'] : '';
                    $res['orderList'][$strKey]['show_date_has_weekday'] = !empty($arr['show_date']) ? $arr['show_date'] : '';
                    #$keys_arr = explode('|', $res['orderList'][$strKey]['cd_key']);
                    //if (isset($res['orderList'][$strKey]['refundFlg'])) {
                    //    $res['orderList'][$strKey]['refundMsg'] = $this->model('order')->getRefundContent((string)$res['orderList'][$strKey]['refundFlg']);
                    //}
                }
            }
            //格式化订单列表
            $arrFormatedOrderList = [];
            if (!empty($res['orderList']) && is_array($res['orderList'])) {
                $arrFormatedOrderList = $this->formatFilmOrderList(self::getParam($arrInput, 'channelId'), $res['orderList']);
            }
            $arrReturn = [
                'ret' => 0,
                'sub' => 0,
                'data' => $arrFormatedOrderList,
                'total_row' => $res['total_row'],
                'page' => $res['page'],
                'num' => $res['num'],
            ];

        } elseif ($res['ret'] == -1) {  //没有订单
            $arrReturn = [
                'ret' => $res['ret'],
                'sub' => $res['sub'],
                'orderList' => [],
            ];
        } else {
            $arrReturn = $res;
        }

        return $arrReturn;
    }

    /**
     * 按照日期格式化订单列表
     *
     * @param array $arrOrderList
     *
     * @return array
     */
    private function formatFilmOrderList($channelId = '', $arrOrderList = [])
    {
        $arrWeekDay = [
            1 => '周一',
            2 => '周二',
            3 => '周三',
            4 => '周四',
            5 => '周五',
            6 => '周六',
            7 => '周日',
        ];
        $arrNeedFields = [
            'movie_id',
            'seatInfo',
            'cinema_id',
            'cinema_name',
            'cinema_address',
            'movie_name',
            'expired_time',
            'poster_url',
        ];
        $return = [];
        if (!empty($arrOrderList) && is_array($arrOrderList)) {
            //获取所有订单的影片id
            $arrMovieIds = Utils::array_column($arrOrderList, 'movie_id');
            $strMovieIds = implode(',', $arrMovieIds);
            //获取所有影片id对应的影片详情
            $arrMovieImages = [];
            if (!empty($arrMovieIds)) {
                $arrParams = [
                    'channelId' => $channelId,
                    'movieIds' => $strMovieIds,
                    'fields' => 'poster_url',
                ];
                $res = $this->service('Movie')->getMovieFieldByMovieIds($arrParams);
                $arrMoviePosterUrls = (isset($res['ret']) && ($res['ret'] == 0)) ? $res['data'] : [];
            }
            foreach ($arrOrderList as &$arrOrderInfo) {
                //获取上映日期
                if (empty($arrOrderInfo['show_date']) || empty($arrOrderInfo['movie_id'])) {
                    continue;
                }
                $showDateDay = date('Y-m-d', strtotime($arrOrderInfo['show_date']));
                $showWeekDayNum = date('N', strtotime($arrOrderInfo['show_date']));
                $showWeekDayStr = $arrWeekDay[$showWeekDayNum];
                $iMovieId = $arrOrderInfo['movie_id'];
                $strMoviePosterUrl = !empty($arrMoviePosterUrls[$iMovieId]['poster_url']) ? $arrMoviePosterUrls[$iMovieId]['poster_url'] : '';
                $arrOrderInfo['poster_url'] = $strMoviePosterUrl;
                if (!isset($return[$showDateDay])) {
                    $return[$showDateDay] = [
                        'id' => $showDateDay,
                        'show_date' => $showDateDay . ' ' . $showWeekDayStr,
                        'tickets' => [],
                    ];
                }
                //只取前端用的字段
                $arrFormatOrderFields = [
                    'movie_id' => !empty($arrOrderInfo['movie_id']) ? $arrOrderInfo['movie_id'] : '',
                    'seatInfo' => !empty($arrOrderInfo['seatInfo']) ? $arrOrderInfo['seatInfo'] : '',
                    'cinema_id' => !empty($arrOrderInfo['cinema_id']) ? $arrOrderInfo['cinema_id'] : '',
                    'cinema_name' => !empty($arrOrderInfo['cinema_name']) ? $arrOrderInfo['cinema_name'] : '',
                    'cinema_address' => !empty($arrOrderInfo['cinema_address']) ? $arrOrderInfo['cinema_address'] : '',
                    'movie_name' => !empty($arrOrderInfo['movie_name']) ? $arrOrderInfo['movie_name'] : '',
                    'expired_time' => !empty($arrOrderInfo['expired_time']) ? $arrOrderInfo['expired_time'] : '',
                    'poster_url' => !empty($arrOrderInfo['poster_url']) ? $arrOrderInfo['poster_url'] : '',
                ];
                $arrOrderInfo = null;
                $return[$showDateDay]['tickets'][] = $arrFormatOrderFields;
            }
        }
        //重新索引
        if (!empty($return)) {
            $return = array_values($return);
        }

        return $return;
    }

    /**
     * 获取已支付订单(新版多码)
     *
     * @param string openId             用户openId
     * @param string channelId          公众号缩写 电影票:3, IOS:8, Android:9
     * @param string salePlatformType   售卖平台类型 枚举 1 公众号、2渠道, 除了智慧影院,都传2
     * @param intval appId              登录方式，认证体系APPID：0：通用，1为微信公众号，2为手机号...
     * @param intval userId             用户Id,如果没有,可传0, 需要打通账号体系来支撑
     * @param intval page               页码, 默认取第一页
     * @param intval num                单页的数量, 默认取全部, 设置1000条
     *
     * @return array
     */
    public function queryPaidOrderNew(array $arrInput = [])
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['channelId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['page'] = self::getParam($arrInput, 'page', 1);
        $arrSendParams['types'] = self::getParam($arrInput, 'types', "2,24");
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $return = static::getStOut();
        //Java接口有问题，如果没有订单，返回结果是ret=-1，所以需要将这种状况处理一下
        $arrData = $this->http(JAVA_API_QUERY_ORDER_NEW, $httpParams);
        if ($arrData['ret'] == 0) {
            $return = $arrData;
            //格式化已经删除的订单
            //self::__FormatDeleteOrder($arrSendParams['channelId'], $return['orderList']);
        }

        return $return;
    }


    /**
     * 查询历史订单【新结构】
     *
     * @param string openId             用户openId
     * @param string channelId          渠道号 电影票:3, IOS:8, Android:9
     * @param string orderId            订单号
     *
     * @return array
     */
    public function queryHistoryOrderDetail(array $arrInput = [])
    {
        //参数处理
        $openid = self::getParam($arrInput, 'openId');
        $orderId = self::getParam($arrInput, 'orderId');
        //查询关系树得到所有openid的集合
        $openidAll = [];
        $resOpenid = $this->service('user')->getIdRelation(['id' => $openid]);
        if ($resOpenid['ret'] == 0 && $resOpenid['sub'] == 0) {
            if ($resOpenid['data']['idRelation']['idType'] == 0) {
                $openidAll[] = $resOpenid['data']['idRelation']['id'];
            } else {
                foreach ($resOpenid['data']['idRelation']['idUnderBound'] as $item) {
                    if (in_array($item['idType'], ['11', '12', '13'])) { //微信、手Q、手机号
                        $openidAll[] = $item['id'];
                    } elseif ($item['idType'] == 30) {  //是unionid时取其下的openid
                        foreach ($item['idUnderBound'] as $wxitem) {
                            $openidAll[] = $wxitem['id'];
                        }
                    }
                }

            }
        }
        $response = $this->model('order')->getHistoryDetail($openidAll, $orderId);

        if ($response['ret'] != 0) {
            $response = self::getErrorOut(ERRORCODE_ORDER_NOT_FOUND);
        }

        return $response;
    }

    /**
     * 新版获取未支付订单服务
     * 主要用于替换queryUnPayOrder这个服务。两个的区别在于，这个版本Java可以返回多个未支付订单,判断是否在订单多于2个的时候,只有>2个未支付订单,才返回所有
     *
     * @note
     *
     * @param string openId             用户openId
     * @param string channelId          公众号缩写 电影票:3, IOS:8, Android:9, ...
     * @param intval limit              预期返回未支付订单的条数
     * @param intval forceGet           是否在未支付订单数<3的时候,也要拿数据。默认为0,表示订单数<3的时候,不要数据(也即是要空数据)
     *
     * @return array ['ret'=>0,'sub'=>0,'lockinfo'=>[...]]
     */
    public function queryUnPayOrderMulti(array $arrInput = [])
    {
        $arrReturn = $this->getStOut();
        //参数处理
        $arrSendParams = [];
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['publicSignalShort'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['limit'] = self::getParam($arrInput, 'limit', 3);
        $iForceGet = self::getParam($arrInput, 'forceGet', 0);
        if (empty($arrSendParams['openId'])) {
            return $this->getErrorOut(ERRORCODE_OPEN_ID_ERROR);
        }
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $arrLockInfos = $this->http(JAVA_API_UNPAY_ORDER_MULTI, $httpParams);
        if (isset($arrLockInfos['ret']) && ($arrLockInfos['ret'] == 0) && !empty($arrLockInfos['lockinfos'])) {
            if (!empty($arrLockInfos['lockinfos']) && is_array($arrLockInfos['lockinfos'])) {
                if (($iForceGet == 1) || (($iForceGet == 0) && (count($arrLockInfos['lockinfos']) > 2))) {
                    $arrReturn['lockinfo'] = $arrLockInfos['lockinfos'];
                }
            } else {
                $arrReturn['lockinfo'] = [];
            }
        }

        return $arrReturn;
    }

    /**
     * 【新版】获取未支付订单服务
     * 主要用于替换 queryUnPayOrder、queryUnPayOrderMulti 两个查询未支付订单的接口
     * 相比于queryUnPayOrderMulti, 此接口支持获取改签之后的未支付订单。之所以单独提出来,也是为了不影响原有接口
     * 注意:正常来说,只有未支付订单数量>=3的时候,才会返回未支付订单(获取可售座位的时候,需要>=3的时候,发生全部解锁),但是呢,如果未支付订单需要重新发起支付
     * 那么,就需要订单数不满足3的时候,也获取结果了,这个时候需要传入forceGet=1。其实这块儿逻辑需要优化
     *
     * @note
     *
     * @param string openId             用户openId
     * @param string channelId          公众号缩写 电影票:3, IOS:8, Android:9, ...
     * @param intval limit              查询Java预期返回未支付订单的条数
     * @param intval forceGet           是否在未支付订单数<3的时候,也要拿数据。默认为0,表示订单数<3的时候,不要数据(也即是要空数据)
     *
     * @return array ['ret'=>0,'sub'=>0,'lockinfo'=>[...]]
     */
    public function queryUnPayOrderMultiV1(array $arrInput = [])
    {
        $arrReturn = $this->getStOut();
        $arrReturn['lockinfo'] = [];
        //参数处理
        $arrSendParams = [];
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['publicSignalShort'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['limit'] = self::getParam($arrInput, 'limit', 3);
        $iForceGet = self::getParam($arrInput, 'forceGet', 0);
        if (empty($arrSendParams['openId'])) {
            return $this->getErrorOut(ERRORCODE_OPEN_ID_ERROR);
        }
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $arrLockInfos = $this->http(JAVA_API_UNPAY_ORDER_MULTI_V1, $httpParams);
        if (isset($arrLockInfos['ret']) && ($arrLockInfos['ret'] == 0) && !empty($arrLockInfos['lockinfos'])) {
            //获取影片图片
            foreach ($arrLockInfos['lockinfos'] as &$value){
                if(!empty($value['movieNo'])) {
                    $movieInfo = $this->service('Movie')->readMovieInfo(['channelId' => $arrSendParams['publicSignalShort'], 'movieId' => $value['movieNo'], 'needbuyflag' => 0]);
                    $value['poster_url_size3'] = !empty($movieInfo['data']['poster_url_size3']) ? $movieInfo['data']['poster_url_size3'] : '';
                }
            }
            if (($iForceGet == 1) || (($iForceGet == 0) && (count($arrLockInfos['lockinfos']) > 2))) {
                $arrReturn['lockinfo'] = $arrLockInfos['lockinfos'];
            }
        }
        return $arrReturn;
    }

    /**
     * 查询手机号接口
     * 这个查询手机号,是从订单中心查
     *
     * @param string openId             用户openId
     * @param string channelId          公众号缩写 电影票:3, IOS:8, Android:9
     * @param string salePlatformType   售卖平台类型 枚举 1 公众号、2渠道, 除了智慧影院,都传2
     * @param intval appId              登录方式，认证体系APPID：0：通用，1为微信公众号，2为手机号...
     * @param intval userId             用户Id,如果没有,可传0, 需要打通账号体系来支撑
     *
     * @return array
     */
    public function queryOrderMobile(array $arrInput = [])
    {
        //参数处理
        $arrReturn = self::getStOut();
        $arrReturn['data']['mobileNo'] = '';
        $arrSendParams = [];
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['publicSignalShort'] = self::getParam($arrInput, 'channelId', '');
        $arrSendParams['salePlatformType'] = self::getParam($arrInput, 'salePlatformType');
        $arrSendParams['appId'] = self::getParam($arrInput, 'appId');
        $arrSendParams['userId'] = self::getParam($arrInput, 'userId');

        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $arrMobileInfo = $this->http(JAVA_API_QUERY_ORDER_MOBILE, $httpParams);
        if ($arrMobileInfo['ret'] == 0 && $arrMobileInfo['sub'] == 0 && !empty($arrMobileInfo['data']['mobileNo'])) {
            $arrReturn['data']['mobileNo'] = $arrMobileInfo['data']['mobileNo'];
        } else {
            $arrReturn['ret'] = $arrMobileInfo['ret'];
            $arrReturn['sub'] = $arrMobileInfo['sub'];
            $arrReturn['msg'] = $arrMobileInfo['msg'];
        }

        return $arrReturn;
    }
    /**
     * 添加订单列表页“影片是否评论过”字段commented，0未评论1已评论
     * @param $openId
     * @param $channelId
     * @param array $OrderList
     */
    private function __FormatCommented($openId, $channelId, &$OrderList = [])
    {
        //获取评论过的影片ID集合
        $movieIdList = [];
        $data = [
            'openId' => $openId,
            'channelId' => $channelId,
        ];
        $httpParams = [
            'arrData' => $data,
            'sMethod' => 'GET',
        ];
        $response = $this->http(COMMENT_USER_COMMENT_MOVIES,$httpParams);
        if ($response['ret'] == 0 && !empty($response['data'])) {
            $movieIdList = $response['data']; //['6746','6181',..]
        }
        //添加是否评论过字段
        foreach ($OrderList as &$item) {
            //新版支持改签的订单列表接口queryOrderListV1中使用
            if (!empty($item['movieInfo']['id'])) {
                $item['movieInfo']['commented'] = in_array($item['movieInfo']['id'], $movieIdList) ? 1 : 0;
            } //旧版订单列表接口queryPaidOrderNew中使用
            elseif (!empty($item['movie_id'])) {
                $item['commented'] = in_array($item['movie_id'], $movieIdList) ? 1 : 0;
            }
        }
    }

    /**
     * @param $arrOrderInfo 订单详情内容
     * @param $arrSendParams 前端请求接口带的参数
     * @param $ver 订单详情的版本1 :/order/orderInfo 2: /order/v1/orderInfo
     * @return $arrOrderInfo 处理文案后的订单详情
     */
    private function msgControl($arrOrderInfo, $arrSendParams, $ver=1)
    {
        //处理微信，手Q，小程序（支付页和订单详情页文案稍有不同 yupiaoRefundShow=false代表支付页）
        if (isset($arrOrderInfo['data']['movieInfo']['refundFlag'])) {
            if ($arrSendParams['yupiaoRefundShow'] == 'false' && in_array($arrSendParams['channelId'], [3, 28, 63, 66, 67, 68])) {
                if ($arrOrderInfo['data']['movieInfo']['refundFlag'] == 1 || $arrOrderInfo['data']['movieInfo']['refundFlag'] == 15) {
                    $arrOrderInfo['data']['movieInfo']['refundMsg'] = $this->model('order')->getMergeContent((string)$arrOrderInfo['data']['movieInfo']['refundMsg']);
                }
                if ($arrOrderInfo['data']['movieInfo']['changeFlag'] == 1 || $arrOrderInfo['data']['movieInfo']['changeFlag'] == 15) {
                    $arrOrderInfo['data']['movieInfo']['changeMsg'] = $this->model('order')->getMergeContent((string)$arrOrderInfo['data']['movieInfo']['changeMsg']);
                }
            }
        }
        if((isset($arrSendParams['publicSignalShort']) &&
            in_array($arrSendParams['publicSignalShort'], [8, 9, 80, 84])) ||
            (isset($arrSendParams['channelId']) &&
                in_array($arrSendParams['channelId'], [8, 9, 80, 84]))){
            if($ver == 1) {
                $this->_formateV1RefundMsg($arrOrderInfo, $arrSendParams);
            } else if($ver==2){
                $this->_formateV2RefundMsg($arrOrderInfo, $arrSendParams);
            }
        }
        return $arrOrderInfo;
    }

    private function _formateV2RefundMsg(&$arrOrderInfo, $arrSendParams)
    {
        //APP（不支持改签  非有偿影院每月有俩次退票机会 不能直接用晓波返回的文案
        // 当退款标识 refundFlag=1的时候写死文案） APP还在用 queryOrderInfoNew 这个接口跟 微信手Q不统一
        if (isset($arrOrderInfo['data']['movieInfo']['refundFlag']) &&
            $arrOrderInfo['data']['movieInfo']['refundFlag'] == 1 &&
            in_array($arrOrderInfo['data']['cinemaId'], $this->model('Resource')->getAppRefundCinema($arrSendParams['channelId']))
        ) {
            $refundMsgArr =explode('，',$arrOrderInfo['data']['movieInfo']['refundMsg']);
            $arrOrderInfo['data']['movieInfo']['refundMsg'] = '开场前2小时可免费退票，'.end($refundMsgArr);
        }
    }

    private function _formateV1RefundMsg(&$arrOrderInfo, $arrSendParams)
    {
        //APP（不支持改签  非有偿影院每月有俩次退票机会 不能直接用晓波返回的文案
        // 当退款标识 refundFlag=1的时候写死文案） APP还在用 queryOrderInfoNew 这个接口跟 微信手Q不统一
        if (isset($arrOrderInfo['data']['refundFlg']) &&
            $arrOrderInfo['data']['refundFlg'] == 1 &&
            in_array($arrOrderInfo['data']['iCinemaID'], $this->model('Resource')->getAppRefundCinema($arrSendParams['publicSignalShort']))
        ) {
            $refundMsgArr =explode('，',$arrOrderInfo['data']['refundMsg']);
            $arrOrderInfo['data']['refundMsg'] = '开场前2小时可免费退票，'.end($refundMsgArr);
        }
    }


    /**
     * 判断是否搜藏过此影院
     * @param $arrOrderInfo
     * @param $arrSendParams
     */
    private function isFavorite($arrOrderInfo, $arrSendParams)
    {
        $iFavor = 0;
        if (!empty($arrOrderInfo['data']['cinemaId'])) {
            $arrFavorCinemaRes = $this->service('favorite')->getFavoriteCinema([
                'channelId' => $arrSendParams['channelId'],
                'openId' => $arrSendParams['openId'],
            ]);
            if (!empty($arrFavorCinemaRes['data']['cinemaList']) && is_array($arrFavorCinemaRes['data']['cinemaList']) && in_array($arrOrderInfo['data']['cinemaId'],
                    $arrFavorCinemaRes['data']['cinemaList'])
            ) {
                $iFavor = 1;
            }
        }
        return $iFavor;
    }
    /**
     * 查询订单票纸文案
     * @param array $arrInput
     * @return string
     */
    public function paperQueryOrderPrintMsg(array $arrInput = [])
    {
        $arrSendParams['tradeNo'] = self::getParam($arrInput, 'orderId');
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'GET',
        ];
        $paperInfo = $this->http(PAPER_QUERY_ORDER_PRINT_MSG, $httpParams);
        return isset($paperInfo['content']) ? $paperInfo['content'] : '';
    }

    /**
     * 获取影片的购票数量
     * @param string $iMovieId
     * @return array
     */
    public function getMovieBuyNum($arrInput=[])
    {
        $return = self::getStOut();
        $return['data']['buy_num'] = 0;
        $iMovieId = self::getParam($arrInput, 'movieId');
        if (!empty($iMovieId)) {
            //获取影片列表
            $httpParams = []; //http请求信息
            $httpParams['sMethod'] = 'GET';
            $httpParams['iTryTimes'] = 3;
            $httpParams['arrData'] = [];
            $strUrl = sprintf(ORDDER_CENTER_MOVIE_BUY_NUM, $iMovieId);
            $response = $this->http($strUrl, $httpParams);
            if (isset($response['ret']) && ($response['ret'] == 0) && !empty($response['data'])) {
                $return['data']['buy_num'] = $response['data'] + 0;
            }
        }

        return $return;
    }

}