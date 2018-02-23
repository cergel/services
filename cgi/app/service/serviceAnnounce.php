<?php

namespace sdkService\service;

class serviceAnnounce extends serviceBase
{
    
    /**
     * 获取影院公告
     *
     * @param array $arrInput
     * channelId 渠道ID编号
     * position 位置
     * cinemaId 影院ID
     *
     * @return array
     */
    public function getAnnounce(array $arrInput = [])
    {
        $iChannelId = self::getParam($arrInput, 'channelId');
        $position = self::getParam($arrInput, 'position');
        $cinemaId = self::getParam($arrInput, 'cinemaId', 0);
        $movieId = self::getParam($arrInput, 'movieId', 0);
        
        $return = self::getStOut();
        if (empty($iChannelId) || empty($position)) {
            return $return;
        }
        $data = $this->model('Announce')->getAnnounce($iChannelId, $position, $cinemaId, $movieId);
        $return['data'] = $data;
        
        return $return;
    }
    
    
    /**
     * 用户简略信息统一接口
     * 用于首页等展示的简略信息，如红包数量，最近观影影院等
     *
     * @params string $openId 用户第三方账户
     * @params string $channelId 渠道编号，目前支持手Q28与微信3
     * @params string $type 获取功能，用逗号分隔，如“1,2,3”
     * @params string $movieId 影片id，type为3的时候用到，查询最近观影影院中的某个片子的排期
     * 功能列表，需要的填入:
     * 1 获取最近观影影院last_cinema
     * 2 获取可用红包数量bonus
     */
    public function getUserSimpleInfo($arrParams = [])
    {
        $arrType = explode(',', $arrParams['type']);
        $openId = $arrParams['openId'];
        $channelId = $arrParams['channelId'];
        $iMovieId = self::getParam($arrParams, 'movieId', '');
        $arrReturn = self::getStOut();
        //1、获取最近观影影院
        if (in_array(1, $arrType)) {
            $orderParams['openId'] = $openId;
            $orderParams['channelId'] = $channelId;
            $orderParams['page'] = 1;
            $orderParams['num'] = 5;
            $orderParams['types'] = '2,4,24';
            $arrReturn['data']['last_cinema'] = [
                'cinema_name' => '',
                'cinema_id'   => '',
            ];
            $orderList = $this->service('Order')->queryOrderListV1($orderParams);
            if ($orderList['ret'] == 0 && !empty($orderList['data'])) {
                foreach ($orderList['data'] as $orderItem) {
                    //只取最近三个月内的成功购票记录。status：2已支付，6已出票，33改签票
                    if (strtotime($orderItem['expiredTime']) > (time() - 7776000) && in_array($orderItem['status'], [2, 6, 33])) {
                        $arrReturn['data']['last_cinema'] = [
                            'cinema_name' => $orderItem['cinemaName'],
                            'cinema_id'   => $orderItem['cinemaId'],
                        ];
                        break;
                    }
                }
            }
        }
        //2、获取可用红包数量
        if (in_array(2, $arrType)) {
            $bonusParams['openId'] = $openId;
            $bonusParams['channelId'] = $channelId;
            $bonusParams['disType'] = 0; //类型：0所有，1红包，2选座券
            $bonusParams['platId'] = 1; //平台ID，1电影票，2演出，3体育
            $bonusParams['clientIp'] = self::getParam($arrParams, 'clientIp');; //用户IP，选传
            $arrReturn['data']['bonus'] = [
                'bonus_count'   => '',
                'presell_count' => '',
            ];
            $bonusList = $this->service('Bonus')->getBonusCount($bonusParams);
            if ($bonusList['ret'] == 0 && $bonusList['sub'] == 0) {
                foreach ($bonusList['data'] as $item) {
                    if ($item['disType'] == 1) {
                        $arrReturn['data']['bonus']['bonus_count'] = $item['validCnt'];
                    }
                    if ($item['disType'] == 2) {
                        $arrReturn['data']['bonus']['presell_count'] = $item['validCnt'];
                    }
                }
            }
            
        }
        //3、获取最近观影影院，以及其某个片子的排期
        if (in_array(3, $arrType)) {
            $orderParams['openId'] = $openId;
            $orderParams['channelId'] = $channelId;
            $orderParams['page'] = 1;
            $orderParams['num'] = 5;
            $orderParams['types'] = '2,4,24';
            $arrReturn['data']['last_cinema'] = [
                'cinema_name' => '',
                'cinema_id'   => '',
                'sches'       => [],
            ];
            $orderList = $this->service('Order')->queryOrderListV1($orderParams);
            if ($orderList['ret'] == 0 && !empty($orderList['data'])) {
                foreach ($orderList['data'] as $orderItem) {
                    //只取最近三个月内的成功购票记录。status：2已支付，6已出票，33改签票
                    if (strtotime($orderItem['expiredTime']) > (time() - 7776000) && in_array($orderItem['status'], [2, 6, 33])) {
                        $arrReturn['data']['last_cinema'] = [
                            'cinema_name' => $orderItem['cinemaName'],
                            'cinema_id'   => $orderItem['cinemaId'],
                        ];
                        break;
                    }
                }
            }
            //查询这个影院的这个片子的排期
            if ( !empty($iMovieId) && !empty($arrReturn['data']['last_cinema']['cinema_id'])) {
                $arrSche = $this->service('Sche')->qryScheV2([
                    'openId'      => $openId,
                    'cinemaId'    => $arrReturn['data']['last_cinema']['cinema_id'],
                    'movieId'     => $iMovieId,
                    'channelId'   => $channelId,
                    'needMore'    => 2,
                    'payReduceId' => '',
                ]);
                $date = date('Ymd');
                if (isset($arrSche['ret']) && !empty($arrSche['data']) && !empty($arrSche['data'][0]['sche'][$date]) && is_array($arrSche['data'][0]['sche'][$date])) {
                    $schesRes = $arrSche['data'][0]['sche'][$date];
                    $sches = [];
                    if ( !empty($schesRes)) {
                        foreach ($schesRes as $sche) {
                            $data = [
                                'mpid'       => !empty($sche['mpId']) ? $sche['mpId'] : '',
                                'mem_price'  => !empty($sche['memShowPri']) ? $sche['memShowPri'] : '',
                                'show_price' => !empty($sche['showPri']) ? $sche['showPri'] : '',
                                'type'       => !empty($sche['type']) ? $sche['type'] : '',
                                'time'       => !empty($sche['time']) ? $sche['time'] : '',
                                'bis_id'     => !empty($sche['bisId']) ? $sche['bisId'] : '',
                                'dis_id'     => !empty($sche['disId']) ? $sche['disId'] : 0,
                                'dis_flag'   => !empty($sche['disFlag']) ? $sche['disFlag'] : '',
                                'lagu'       => !empty($sche['lagu']) ? $sche['lagu'] : '',
                            ];
                            $sches[] = $data;
                        }
                    }
                    $schesData = ['data' => $sches, 'date' => $date];
                    $arrReturn['data']['last_cinema']['sches'][] = $schesData;
                }
            }
        }
        
        return $arrReturn;
    }
}