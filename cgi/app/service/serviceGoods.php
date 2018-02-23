<?php


/**
 * 该服务, 主要是和商品中心交互
 */

namespace sdkService\service;


class serviceGoods extends serviceBase
{

    private $isExp = 0;//是否开启分离，0-不开启，1-开启

    private $arrGoodsServerMap = [
    ];

    //需要隔离部署的接口uri
    private $arrNeedExpUri = [
        '/seats/info',
    ];

    /**
     * 获取不可售座位+自由库的可售座位
     * 该接口，包含了不可售座位和我们的自由库存的可售座位
     *
     * @param  string       channelId   渠道编号
     * @param  string       scheduleId  排期编号
     * @param  string       cinemaNo    影院编号
     * @param  string       bisServerId 影院售票系统编号
     *
     * @return array|bool|mixed
     */
    public function qryUnsoldSeatsAndLocalSeats($arrInput = [])
    {
        //参数拼装
        $arrSendParams = [];
        $arrSendParams['scheduleId'] = self::getParam($arrInput, 'scheduleId');
        $arrSendParams['channelId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['cinemaNo'] = self::getParam($arrInput, 'cinemaId');
        $arrSendParams['bisServerId'] = self::getParam($arrInput, 'bisServerId');
        //调用接口
        $arrReturn = self::getStOut();
        $httpParams = [
            'arrData'   => $arrSendParams,
            'sMethod'   => 'GET',
            'iTimeout'  => 20,
            'iTryTimes' => 1,
        ];
        //判断是否开启隔离部署
        $strUrl = JAVA_API_UNSOLDED_SEATS_LOCAL_SEATS;
        if ($this->isExp && !empty( $arrSendParams['bisServerId'] ) && in_array('/seats/info',
                $this->arrNeedExpUri) && !empty( $this->arrGoodsServerMap[$arrSendParams['bisServerId']] )
        ) {
            $strUrl = $this->arrGoodsServerMap[$arrSendParams['bisServerId']];
        }
        $arrReturn = $this->http($strUrl, $httpParams);

        return $arrReturn;
    }

    /**
     * 做不可售座位+自有库存的可售座位，和座位图的融合，得到可售座位
     * 如果融合失败，返回空数组
     *
     * @param array $arrRoomInfo                座位图信息
     * @param array $arrUnsoldSeatAndLocalSeats 不可售座位+自由库存座位。此内容由Java接口得来，如：['unAvailableSeats'=>["01:1:3","01:1:4"],'localSeats'=>[ "01:1:3","01:1:2"]
     *
     * @return ['strAvaliableSeats'=>'...','leftSeatNum'=>22] 返回字符串类型的可售座位及剩余座位数量。没有合法结果，则返回空数组
     */
    public function getAvaliableSeatsByRoomInfoAndUnsold($arrRoomInfo = [], $arrUnsoldSeatAndLocalSeats = [])
    {
        $return = ['strAvaliableSeats' => '', 'leftSeatNum' => 0, 'totalSeatNum' => 0];
        $arrSeatNumbers = [];

        //格式化处理
        if ( !empty( $arrRoomInfo ) && !empty( $arrUnsoldSeatAndLocalSeats )) {
            //遍历座位图，剔除无法售卖的座位，以及不可售座位，并将黄金座位标识出来
            if (isset( $arrRoomInfo['sSeatInfo'] ) && !empty( $arrRoomInfo['sSeatInfo'] )) {
                //初始化座位总数和剩余座位数
                $iAllSeatNum = $iLeftSeatNum = 0;
                //遍历排
                foreach ($arrRoomInfo['sSeatInfo'] as $arrRowSeats) {
                    if (isset( $arrRowSeats['detail'] ) && !empty( $arrRowSeats['detail'] )) {
                        //遍历行
                        foreach ($arrRowSeats['detail'] as $arrColSeatInfo) {
                            //如果是情侣座、走廊、损坏的座位等，是的话，跳过
                            //if (( $arrColSeatInfo['damagedFlg'] != 'N' ) || ( $arrColSeatInfo['loveInd'] != 0 ) || ( $arrColSeatInfo['n'] == 'Z' )) {
                            if (( $arrColSeatInfo['damagedFlg'] != 'N' ) || ( $arrColSeatInfo['n'] == 'Z' )) {
                                continue;
                            }
                            $iAllSeatNum++;
                            //如果当前座位在不可售座位中，跳过
                            //$strArea = $arrRowSeats['area'];
                            $strArea = '01';
                            $strSeat = $strArea . ':' . $arrRowSeats['desc'] . ':' . $arrColSeatInfo['n'];
                            if (in_array($strSeat, $arrUnsoldSeatAndLocalSeats['unAvailableSeats'])) {
                                continue;
                            }
                            $iLeftSeatNum++;
                            //如果当前座位，在自由库存中，标识为gold
                            $gold = 1;  //1表示非自有库存，0表示自有库存
                            if (in_array($strSeat, $arrUnsoldSeatAndLocalSeats['localSeats'])) {
                                $gold = 0;
                            }
                            if ( !isset( $arrSeatNumbers[$strArea . ':' . $arrRowSeats['desc'] . ':'] )) {
                                $arrSeatNumbers[$strArea . ':' . $arrRowSeats['desc'] . ':'] = [];
                            }
                            $arrSeatNumbers[$strArea . ':' . $arrRowSeats['desc'] . ':'][] = $arrColSeatInfo['n'] . '-' . $gold;
                        }
                    }
                }
                //将结果，拼接成字符串，最终格式化如：01:1:2-0,3-0,4-0,7-1,8-1|01:2:2-0,3-0,4-0,7-1,8-1|01:3:2-0,3-0,4-0,7-1,8-1
                if ( !empty( $arrSeatNumbers )) {
                    $data = [];
                    foreach ($arrSeatNumbers as $strKey => $arrNumbers) {
                        $strRows = implode(',', $arrNumbers);
                        $data[] = $strKey . $strRows;
                    }
                    $return['strAvaliableSeats'] = implode('|', $data);
                }
                $return['leftSeatNum'] = $iLeftSeatNum;
                $return['totalSeatNum'] = $iAllSeatNum;
            }
        }

        return $return;
    }
    
    /**
     * @param array $arrInput
     */
    public function qryMergedSeats($arrInput = [])
    {
        $arrReturn = self::getStOut();
        $arrSendParams = [];
        $arrSendParams['channelId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['cinemaNo'] = self::getParam($arrInput, 'cinemaNo');
        $arrSendParams['scheduleId'] = self::getParam($arrInput, 'scheduleId');
        $arrSendParams['requestId'] = $this->getReqiuseId();
        if (empty( $arrSendParams['requestId'] )) {
            $arrReturn['ret'] = $arrReturn['sub'] = ERROR_RET_LACK_OF_REQUESTID;
            $arrReturn['msg'] = ERROR_MSG_LACK_OF_REQUESTID;
        
            return $arrReturn;
        }
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'GET',
            'iTimeout'  => 20,
            'iTryTimes' => 1,
        ];
        $arrReturn = $this->http(JAVA_API_MERGED_SEATS, $httpParams);
        //添加明星选座数据
        if (isset( $arrReturn['ret'] ) && ( $arrReturn['ret'] == 0 )) {
            $arrReturn['data']['customizationSeats'] = new \stdClass();
            if ( !empty( $arrInput['movieId'] )) {
                $arrReturn['data']['customizationSeats'] = $this->model('movie')->readMovieCustomSeatNewStatic($arrSendParams['channelId'],
                    $arrInput['movieId']);
            }
        }
        if (empty( $arrReturn['data'] )) {
            $arrReturn['data'] = new  \stdClass();
        }
    
        return $arrReturn;
    }

    /**
     * 保存排期的座位总数和剩余座位数
     * 此方法仅给获取可售座位接口使用
     *
     * @param null   totalSeatNum 影厅总的座位数
     * @param null   leftSeatNum  排期场次剩余可售座位数
     * @param string channelId    渠道编号
     * @param string cinemaId     影院编号
     * @param string schduleId    排期编号
     * @param string roomId       影厅编号
     *
     * @return bool
     */
    public function saveSeatNuForWithLeftSeat($arrInput = [])
    {
        $iTotalSeatNum = self::getParam($arrInput, 'totalSeatNum');
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iLeftSeatNum = self::getParam($arrInput, 'leftSeatNum');
        $iCinemaNo = self::getParam($arrInput, 'cinemaId');
        $schduleId = self::getParam($arrInput, 'schduleId');
        $iBestSeats = self::getParam($arrInput,'bestSeats',0);
        //参数判断
        $return = false;
        if (( $iTotalSeatNum <= 0 ) || is_null($iLeftSeatNum) || empty( $iCinemaNo ) || empty( $schduleId )) {
            return $return;
        }
        try {
            //获取影厅的全部座位图数目
            if ($iTotalSeatNum && ( $iTotalSeatNum >= $iLeftSeatNum )) {
                //原来的获取座位图总数的方法，是要读取redis的某个key的，现在这一版不需要，是因为格式化可售的时候，已经能拿到座位总数了
                /*$strKey = 'cinema_room_seats_count_' . $iCinemaNo;
                $arrDataRedisConfig = Config_DB::$redis_conf['data'];
                $objDataRedis = Data_Redis_RedisManager::getInstance($arrDataRedisConfig, $strKey);
                $allRoomSeat = $objDataRedis->hashGet($strKey, $mixRoomId);*/
                //存储影厅座位数
                $strRoomSeatKey = "room_seat_mpid_" . $iCinemaNo;
                $arrSaveData = [$schduleId => ['total' => $iTotalSeatNum, 'left' => $iLeftSeatNum,'bestSeats'=>$iBestSeats]];
                $return = $this->model('Goods')->saveSeatsLeft($strRoomSeatKey, $arrSaveData, $iChannelId);
            }
        }
        catch (\Exception $ex) {
        }

        return $return;
    }
    
    /**
     * 从商品中心，获取座位图（仅仅是获取座位图，不包含可售不可售座位）
     * 这个接口，接口格式和 cinema/read-cinema-room 相同，这是，这个方法，数据是走商品中心，而 cinema/read-cinema-room 是取om数据
     *
     * @param array $arrInput
     */
    public function readCinemaRoom($arrInput = [])
    {
        $arrReturn = self::getStOut();
        $arrSendParams = [];
        $arrSendParams['channelId'] = self::getParam($arrInput, 'channelId');
        $iCinemaId = self::getParam($arrInput, 'cinemaId');
        $iRoomId = self::getParam($arrInput, 'roomId');
        $arrSendParams['requestId'] = $this->getReqiuseId();
        if (empty($arrSendParams['channelId']) || empty($iCinemaId) || empty($iRoomId)) {
            return self::getErrorOut(ERRORCODE_COMMON_PARAMS_ERROR);
        }
        //调用接口
        $httpParams = [
            'arrData'   => $arrSendParams,
            'sMethod'   => 'GET',
            'iTimeout'  => 5,
            'iTryTimes' => 1,
        ];
        $strUrl = sprintf(JAVA_API_CORE_CENTER_CINEMA_SEATS, $iCinemaId, $iRoomId);
        $arrReturn = $this->http($strUrl, $httpParams);
        
        return $arrReturn;
    }
    
}