<?php

namespace sdkService\service;


class serviceCinemaVip extends serviceBase
{
    
    /**
     * 获取用户已开通的会员卡列表
     * 这个是用到影院详情页(也就是影院排期的), 不能分页, 且仅支持通过影院id来查用户在这个影院下的会员卡列表
     *
     * @param   int  openId      用户openId
     * @param   int  cardNo      会员卡id(查询用户名下的特定的会员卡)
     * @param   int  cinemaId    影院id(查询某个用户在某个影院下的会员卡列表)。当cinemaId和cardNo都传的时候,cardNo优先级最高
     * @param   int  channelId   渠道编号
     *
     * @return array
     */
    public function getUserCardList(array $arrInput = [])
    {
        if (\wepiao::$config['demoteVipCard']) {
            return $this->getErrorOut(ERRORCODE_DEMOTE_VIP_CARD);
        }
        //参数整理
        $arrSendParams = [];
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['channelId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['cardNo'] = self::getParam($arrInput, 'cardNo');
        $arrSendParams['cinemaNo'] = self::getParam($arrInput, 'cinemaId');
        if (empty( $arrSendParams['cardNo'] )) {
            unset( $arrSendParams['cardNo'] );
        }
        $return = self::getStOut();
        $return['data']['list'] = [];
        $return['data']['tipText'] = '';
        if (empty( $arrSendParams['channelId'] ) || empty( $arrSendParams['openId'] )) {
            return $return;
        }
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'GET',
        ];
        $res = $this->http(JAVA_API_VIPCARD_USER_CARD_LIST, $httpParams);
        if (isset( $res['ret'] ) && ( $res['ret'] == 0 )) {
            if ( !empty( $res['data']['list'] )) {
                $return['data'] = $res['data'];
            }
            else {
                //如果传入了cardNo,却没有查到结果,则返回错误码,告知前端用户没有可用会员卡
                if ( !empty( $arrSendParams['cardNo'] )) {
                    $return['ret'] = $return['sub'] = "-2";
                    $return['msg'] = "您目前没有可用的会员卡";
                }
            }
        }
        else {
            $return['ret'] = $res['ret'];
            $return['sub'] = $res['sub'];
            $return['msg'] = "您目前没有可用的会员卡";
        }
        
        return $return;
    }
    
    /**
     * 获取用户已开通的会员卡列表
     * 这个相比getUserCardList, 是要按分页获取用户的会员卡列表, 用于"我的-会员卡"页面调用
     *
     * @param   int  openId      用户openId
     * @param   int  pageNo      会员卡id(查询用户名下的特定的会员卡)
     * @param   int  pageSize    影院id(查询某个用户在某个影院下的会员卡列表)。当cinemaId和cardNo都传的时候,cardNo优先级最高
     * @param   int  channelId   渠道编号
     *
     * @return array
     */
    public function getUserCardListByPage(array $arrInput = [])
    {
        if (\wepiao::$config['demoteVipCard']) {
            return $this->getErrorOut(ERRORCODE_DEMOTE_VIP_CARD);
        }
        //参数整理
        $arrSendParams = [];
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['pageNo'] = self::getParam($arrInput, 'pageNum', '1');
        $arrSendParams['pageSize'] = self::getParam($arrInput, 'pageSize', '10');
        $arrSendParams['channelId'] = self::getParam($arrInput, 'channelId');
        //openid参数判断
        if (empty($arrSendParams['openId'])) {
            return $this->getErrorOut(ERRORCODE_OPEN_ID_ERROR);
        }
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'GET',
        ];
        $return = $this->http(JAVA_API_VIPCARD_USER_CARD_LIST_PAGE, $httpParams);
        
        return $return;
    }
    
    /**
     * 获取会员卡类型列表(和用户无关)
     *
     * @param   int   cityId      城市id
     * @param   int   channelId   渠道编号
     * @param   int   cinemaId    影院id
     */
    public function getCardList(array $arrInput = [])
    {
        if (\wepiao::$config['demoteVipCard']) {
            return $this->getErrorOut(ERRORCODE_DEMOTE_VIP_CARD);
        }
        //参数整理
        $arrSendParams = [];
        $arrSendParams['cityNo'] = self::getParam($arrInput, 'cityId');
        $arrSendParams['channelId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['cinemaNo'] = self::getParam($arrInput, 'cinemaId');
        $return = self::getStOut();
        if (empty( $arrSendParams['channelId'] )) {
            return $return;
        }
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'GET',
        ];
        $return = $this->http(JAVA_API_VIPCARD_CARD_LIST, $httpParams);
        if ( !empty($return['data'])) {
            foreach ($return['data'] as &$arrInfo) {
                if (empty($arrInfo['discount'])) {
                    $arrInfo['discount'] = new \stdClass();
                }
            }
        }
        
        return $return;
    }
    
    /**
     * 获取某个城市下, 支持的会员卡类型列表(和用户无关)
     * 注意: 此接口不再支持 cityId 查看, 查看某个城市下的影院会员卡列表, 用 getCityCardListByPage 接口。
     *
     * @param   int                 cityId        城市id
     * @param   int                 channelId     渠道编号
     * @param   string              openId        用户openId
     * @param   int                 pageNum       分页页码
     * @param   int                 pageSize      单页条数
     * @param   int                 more          是否是开通更多。如果为1, 表示用户已经开通了一些会员卡了, 这个时候开通更多, 就需要把openid传给Java
     *                                            而如果用户一个都没有开通过,就传0, 我们就不会传openid给Java, 他们也就无需再查询这个用户下哪些没有开通
     */
    public function getCityCardListByPage(array $arrInput = [])
    {
        if (\wepiao::$config['demoteVipCard']) {
            return $this->getErrorOut(ERRORCODE_DEMOTE_VIP_CARD);
        }
        //参数整理
        $arrSendParams = [];
        $arrSendParams['cityNo'] = self::getParam($arrInput, 'cityId');
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['pageNo'] = self::getParam($arrInput, 'pageNum', '1');
        $arrSendParams['pageSize'] = self::getParam($arrInput, 'pageSize', '10');
        $arrSendParams['channelId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['promotionId'] = self::getParam($arrInput, 'promotionId');
        $openMore = self::getParam($arrInput, 'more');
        /*if ($openMore !== 1) {
            $arrSendParams['openId'] = '';
        }*/
        $return = self::getStOut();
        if (empty($arrSendParams['channelId']) || empty($arrSendParams['cityNo'])) {
            return $return;
        }
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'GET',
        ];
        $return = $this->http(JAVA_API_VIPCARD_CITY_CARD_LIST, $httpParams);
        if ( !empty($return['data']['cardTypeList']) && is_array($return['data']['cardTypeList'])) {
            foreach ($return['data']['cardTypeList'] as &$cardInfo) {
                if (empty($cardInfo['discount'])) {
                    $cardInfo['discount'] = new \stdClass();
                }
            }
        }
        
        return $return;
    }
    
    
    /**
     * 获取会员卡详情
     *
     * @param   int   typeId      卡类型id
     * @param   int   channelId   渠道编号
     * @param   int   subTypeId   卡子类型id
     */
    public function getCardInfo(array $arrInput = [])
    {
        //参数整理
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $arrSendParams = [];
        $arrSendParams['typeId'] = self::getParam($arrInput, 'typeId');
        $arrSendParams['channelId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['subTypeId'] = self::getParam($arrInput, 'subTypeId');
        if (empty( $arrSendParams['typeId'] ) || empty( $arrSendParams['channelId'] )) {
            return $return;
        }
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'GET',
        ];
        $return = $this->http(JAVA_API_VIPCARD_CARD_INFO, $httpParams);
        if (isset($return['ret']) && !empty($return['data']['cardSubTypeDtoList'])) {
            foreach ($return['data']['cardSubTypeDtoList'] as &$arrInfo) {
                if (empty($arrInfo['discount'])) {
                    $arrInfo['discount'] = new \stdClass();
                }
            }
        }
        
        return $return;
    }
    
    /**
     * 检测用户是否开过某个会员卡
     *
     * @param   int      typeId      卡类型id
     * @param   int      channelId   渠道编号
     * @param   string   openId       用户openId
     */
    public function checkUserBy(array $arrInput = [])
    {
        //参数整理
        $return = self::getStOut();
        $return['data'] = false;
        $arrSendParams = [];
        $arrSendParams['typeId'] = self::getParam($arrInput, 'typeId');
        $arrSendParams['channelId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        if (empty($arrSendParams['typeId']) || empty($arrSendParams['openId'])) {
            return $return;
        }
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'GET',
        ];
        $return = $this->http(JAVA_API_VIPCARD_CARD_CHECK_USER_BY, $httpParams);
        
        return $return;
    }
    
    /**
     * 获取用户在某个影院下的，融合版折扣卡信息
     * 这个比较杂，主要是前端在排期页以及支付页用，前端之前是在排期页，调用2个接口（）获取想要的信息，现在我一个接口提供了
     *
     * @param array $arrInput
     */
    public function getMergeVipcardInfo(array $arrInput = [])
    {
        //参数整理
        $return = self::getStOut();
        $return['data'] = [
            'tipText'        => '',  //展示文案
            'priceText'      => '',
            'typeId'         => '',  //主卡id
            'userSubTypeId'  => '',  //最优的子卡id
            'userCardStatus' => 0,   //已开通的卡状态（默认0，表示"开通后购票享超值优惠价"）
            'userCardNo'     => '',  //已开通的折扣卡编号
            'isDiscount'     => 0,  //已开通的折扣卡编号
            'userCardLimit'  => 0,  //用户权益剩余次数（如果剩余总数>当日剩余，返回当日的。如果当日剩余>总数，返回总数，总之返回最小那个）
        ];
        $arrSendParams = [];
        $arrSendParams['channelId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['cinemaId'] = self::getParam($arrInput, 'cinemaId');
        $arrSendParams['cinemaNo'] = self::getParam($arrInput, 'cinemaId');
        if (empty($arrSendParams['channelId']) || empty($arrSendParams['cinemaNo'])) {
            return $return;
        }
        //1、获取用户开通的折卡（因为有可能用户开卡了，但是影院下架了，这个时候，用户比得还得能用折扣卡）
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'GET',
        ];
        $res = [];
        if ( !empty($arrSendParams['openId'])) {
            $res = $this->http(JAVA_API_VIPCARD_USER_CARD_LIST, $httpParams);
        }
        $iOpenCard = 0; //是否已开通折扣卡
        if ( !empty($res['data']['list'])) {
            $iOpenCard = 1;
        }
        //2、获取这个影院下的折扣卡信息
        $cardList = $this->getCardList($arrSendParams);
        $return['data']['typeId'] = !empty($cardList['data'][0]['typeId']) ? strval($cardList['data'][0]['typeId']) : '';
        //如果未开通
        if (empty($iOpenCard)) {
            //1、影院不支持折扣卡
            if (empty($cardList['data'])) {
                $return['data']['tipText'] = '';
            } //2、影院支持折扣卡
            else {
                //折扣卡有开通优惠
                $priceText = isset($cardList['data'][0]['avgPrice']) ? "¥" . $cardList['data'][0]['avgPrice'] . '/月开卡' : '';
                $return['data']['priceText'] = $priceText;
                $return['data']['isDiscount'] = !empty($cardList['data'][0]['discount']) ? 1 : 0;
                if ( !empty($cardList['data'][0]['discount']) && is_array($cardList['data'][0]['discount'])) {
                    $return['data']['tipText'] = !empty($cardList['data'][0]['discount']['discountName']) ? $cardList['data'][0]['discount']['discountName'] : '';
                } //无开通优惠
                else {
                    $return['data']['tipText'] = '开通后购票享超值优惠价';
                }
            }
        }//已开通
        elseif ( !empty($iOpenCard)) {
            //如果用户开通了，则赋值文案
            $return['data']['tipText'] = !empty($res['data']['tipText']) ? $res['data']['tipText'] : '';
            //一个用户只能开通一个影院的折扣卡一个折扣卡（所以，可以取第0个）
            $return['data']['userCardStatus'] = !empty($res['data']['list'][0]['tipStatus']) ? 0 + $res['data']['list'][0]['tipStatus'] : 1;
            //用户的折扣卡编号
            $return['data']['userCardNo'] = !empty($res['data']['list'][0]['cardNo']) ? strval($res['data']['list'][0]['cardNo']) : 1;
            //用户开通的子卡类型
            $return['data']['userSubTypeId'] = !empty($res['data']['list'][0]['subTypeId']) ? strval($res['data']['list'][0]['subTypeId']) : 1;
            $return['data']['userCardLimit'] = 0;
            //用户权益剩余次数（如果剩余总数>当日剩余，返回当日的。如果当日剩余>总数，返回总数，总之返回最小那个）
            if (isset($res['data']['list'][0]['intervalLimit'])) {
                $return['data']['userCardLimit'] = ($res['data']['list'][0]['totalLimit'] > $res['data']['list'][0]['intervalLimit']) ? $res['data']['list'][0]['intervalLimit'] : $res['data']['list'][0]['totalLimit'];
            }
        }
        
        return $return;
    }
    
    
}