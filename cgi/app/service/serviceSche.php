<?php

namespace sdkService\service;

use sdkService\helper\Net;
use sdkService\helper\Utils;

class serviceSche extends serviceBase
{

    /**
     * 获取影院排期信息
     * @param intval $cityId 城市id
     * @param intval $cinemaId 影院id
     * @param intval $iMovieId 影片id
     * @return array
     */
    public function readCinemaSche(array $arrInput = [])
    {
        $iCityId = self::getParam($arrInput, 'cityId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iCinemaId = self::getParam($arrInput, 'cinemaId');
        $iMovieId = self::getParam($arrInput, 'movieId');
        //判断是否在IP黑名单，如果在，就修改排期价格（IP黑名单的isInIpBlack配置，在serviceBase的ipBlackLimit设置的）
        $isInIpBlack = 0;
        if (!empty(\wepiao::$config['isInIpBlack']) && (\wepiao::$config['isInIpBlack'] == 1)) {
            $isInIpBlack = 1;
        }
        $return = self::getStOut();
        if (empty($iCinemaId)) {
            return $return;
        }
        $arrSche = $this->model('sche')->readCinemaSche($iChannelId, $iCityId, $iCinemaId);
        $arrData = [];
        //判断是否只想获取某个影片的排期
        if (!empty($iMovieId)) {
            foreach ($arrSche as $movieSche) {
                if (!empty($movieSche['id']) && ($movieSche['id'] == $iMovieId)) {
                    $arrData[] = $movieSche;
                }
            }
        } else {
            $arrData = $arrSche;
        }
        //判断影院是的座位情况
        $arrMpIds = $this->model("TicketLeftOver")->getMpIdsByCinemaId($iCinemaId);
        $arrRoomSeats = $this->model("TicketLeftOver")->getRoomSeatNumByMpIds($iCinemaId, $arrMpIds);
        //格式化具体场次信息格式化排期
        foreach ($arrData as &$Moive) {
            foreach ($Moive['sche'] as $date => &$scheDate) {
                foreach ($scheDate as &$value) {
                    foreach ($value['seat_info'] as $key => &$sche) {
                        if (isset($arrRoomSeats[$sche['mpid']])) {
                            $seatNum = $arrRoomSeats[$sche['mpid']];
                            if ($seatNum['left'] / $seatNum['total'] == 0) {
                                $sche['seatStatus'] = TICKET_LEFT_OVER_NONE; //售罄
                            } elseif ($seatNum['left'] / $seatNum['total'] < 0.3) {
                                $sche['seatStatus'] = TICKET_LEFT_OVER_MIN; //紧张
                            } else {
                                $sche['seatStatus'] = TICKET_LEFT_OVER_MAX; //正常
                            }
                        }
                        //排期价格投毒
                        //万一价格问题，导致投毒误杀，这个参数起到调试作用，可以直观看到是不是在黑名单
                        /*if (isset($_REQUEST['isInIpBlack'])) {
                            $arrFilm['isInIpBlack'] = $isInIpBlack;
                            $arrFilm['clientIp'] = Net::getRemoteIp();
                        }*/
                        //黑名单情况就修改排期价格（调价5%-20%）
                        $nowHour = 0 + date('H');
                        $randUp = 1.05 + (intval($iCinemaId) + $nowHour * pow(-1, $nowHour)) % 18 * 0.01;
                        if ($isInIpBlack && !empty($sche['calculate_price'])) {
                            $sche['price'] = strval(ceil($sche['price'] * $randUp));
                            $sche['calculate_price'] = strval(ceil($sche['calculate_price'] * $randUp));
                            $sche['discount_newbie'] = strval(ceil($sche['discount_newbie'] * $randUp));
                            $sche['discount'] = strval(ceil($sche['discount'] * $randUp));
                        }
                    }
                }
            }
        }
        $return['data'] = $arrData;
        return $return;
    }

    /**
     * 获取影片排期信息APP
     * @param intval $cityId
     * @param intval $movieId
     * @return array
     */
    public function readMovieScheApp(array $arrInput = [])
    {
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iCityId = self::getParam($arrInput, 'cityId');
        $iMovieId = self::getParam($arrInput, 'movieId');
        $openId = self::getParam($arrInput, 'openId');
        $return = self::getStOut();
        if (empty($iCityId) || empty($iMovieId)) return $return;
        $arrData = $this->model('sche')->readMovieSche($iChannelId, $iCityId, $iMovieId);
        $arrCinemaSche = [];
        foreach ($arrData as $key => $value) {
            $arrCinemaSche[$value['id']] = $value['sche'];
        }
        //获取城市下所有影院的列表
        $param = [];
        $param['cityId'] = $iCityId;
        $param['channelId'] = $iChannelId;
        $param['openId'] = $openId;
        $param['movieId'] = $iMovieId;
        $cinemasRet = $this->service('Cinema')->readCinemasCity($param);
        if ($cinemasRet['ret'] == 0 AND $cinemasRet['sub'] == 0) {
            $cinemas = $cinemasRet['data'];
        } else {
            $cinemas = [];
        }
        $cinemaIds = [];
        foreach ($cinemas as $key => $value) {
            //收集有该影片的影院
            if (array_key_exists($value['id'], $arrCinemaSche)) {
                $cinemaIds[] = $value['id'];
                $cinemas[$value['id']] = $value;
                unset($cinemas[$key]);
            }
        }

        $arrData = $this->model('sche')->readCinemaSchePrice($iChannelId, $cinemaIds);
        $cinemaMoviePrice = [];
        foreach ($arrData as $cinemaId => $value) {
            $value = json_decode($value, true);
            if (array_key_exists($iMovieId, $value)) {
                $cinemaMoviePrice[$cinemaId] = $value[$iMovieId];
            }
        }
        //循环价格信息计算某部电影剩余场次的最高价和最低价
        $schePriceBydate = [];
        foreach ($cinemaMoviePrice as $cinemaId => $value) {
            foreach ($value as $dateTime => $price) {
                $arrSche = explode('|', $dateTime);
                //保留大于此时此刻的场次与价格过滤非近期场次
                $scheTimestamp = strtotime($arrSche[0] . " " . $arrSche[1]);
                if (time() < $scheTimestamp) {
                    $schePriceBydate[$arrSche[0]][$cinemaId][$scheTimestamp] = $price;
                }
            }
        }
        $arrData = [];//准备输出数据
        foreach ($schePriceBydate as $date => $cinemaSchePrice) {
            $arrData[$date]['date'] = $date;
            $arrData[$date]['unixtime'] = strtotime($date);
            $arrData[$date]['cinema_info'] = [];
            foreach ($cinemaSchePrice as $cinemaId => $schePrice) {
                $cinemaInfo = $cinemas[$cinemaId];
                ksort($schePrice);
                $cinemaInfo['cinema_movie_max_price'] = max($schePrice);
                $cinemaInfo['cinema_movie_min_price'] = min($schePrice);
                $cinemaInfo['recent_sche'] = [];
                foreach ($schePrice as $scheTimestamp => $value) {
                    $cinemaInfo['recent_sche'][] = date("H:i", $scheTimestamp);
                }
                $arrData[$date]['cinema_info'][] = $cinemaInfo;
            }
        }
        //按照日期排列数据
        ksort($arrData);
        //获取影片名称,并且过滤掉key兼容APP不报错
        $movie = $this->model('movie')->readMovieInfo($iChannelId, $iMovieId);
        $return['data']['movie_name'] = isset($movie['name']) ? $movie['name'] : "";
        $return['data']['cinemas'] = array_values($arrData);
        return $return;
    }

    /**
     * 获取影片排期信息其他格式
     * @param intval $cityId
     * @param intval $movieId
     * @return array
     */
    public function readMovieSche(array $arrInput = [])
    {
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iCityId = self::getParam($arrInput, 'cityId');
        $iMovieId = self::getParam($arrInput, 'movieId');
        $return = self::getStOut();
        if (empty($iCityId) || empty($iMovieId)) return $return;
        $arrData = $this->model('sche')->readMovieSche($iChannelId, $iCityId, $iMovieId);
        $return['data'] = $arrData;
        return $return;
    }

    /**
     * 获取影院排期信息
     * 注意：这个方法，比 readCinemaSche 要全，后续各个业务，都需要迁移到这个方法，两个方法存在，是为了迁移和回滚方便，后续readCinemaSche会被删除
     *
     * @param int    cityId     城市id
     * @param int    cinemaId   影院id
     * @param int    movieId    影片id
     * @param string openId     用户openId
     * @param int    channelId  渠道id
     * @param int    idType     渠道对应的第三方平台id。（0->对应所有第三方平台(与20区分);10->新浪微博;11->微信;12->QQ;13->手机号;20->UID;30->UnionID）
     *
     * @return array
     */
    public function readCinemaScheAndFormat(array $arrInput = [])
    {
        $iCityId = self::getParam($arrInput, 'cityId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iCinemaId = self::getParam($arrInput, 'cinemaId');
        $strOpenId = self::getParam($arrInput, 'openId');
        $iMovieId = self::getParam($arrInput, 'movieId');
        $pay_reduce_id = self::getParam($arrInput, 'pay_reduce_id', '');
        $idType = self::getParam($arrInput, 'idType', 11);

        $return = self::getStOut();
        if (empty($iCinemaId) || empty($iCityId)) {
            return $return;
        }
        //1、获取影院排期
        $arrData = $this->model('sche')->readCinemaSche($iChannelId, $iCityId, $iCinemaId);
        //2、格式化
        $arrData = $this->_formatCinemaSche($arrData, $iChannelId, $strOpenId, $iMovieId, $iCityId, $iCinemaId, $pay_reduce_id);
        $return['data'] = $arrData;
        return $return;
    }

    /**
     * @param array $arrSche
     * @param string $iChannelId
     * @param string $strOpenId
     * @param string $iMovieId
     * @param string $iCityId
     * @param string $iCinemaId
     * @param string $pay_reduce_id
     * @param bool $isNewUser 是否为新用户,该参数主要用于格式化影院vip价格
     *
     * @return array
     */
    protected function _formatCinemaSche($arrSche = [], $iChannelId = '', $strOpenId = '', $iMovieId = '', $iCityId = '', $iCinemaId = '', $pay_reduce_id = '')
    {
        //获取格式化排期的开关
        $iRoomSeat = !empty(\wepiao::$config['roomSeat']) ? \wepiao::$config['roomSeat'] : 0;
        $iDemote = !empty(\wepiao::$config['demote']) ? \wepiao::$config['demote'] : 0;
        $iDiscount2p = !empty(\wepiao::$config['discount2p']) ? \wepiao::$config['discount2p'] : 0;
        $isVipCardDemote = !empty(\wepiao::$config['demoteVipCard']) ? \wepiao::$config['demoteVipCard'] : 0;
        $iDiscount2p = $iDemote ? 0 : $iDiscount2p;
        //判断是否在IP黑名单，如果在，就修改排期价格（IP黑名单的isInIpBlack配置，在serviceBase的ipBlackLimit设置的）
        $isInIpBlack = 0;
        $remoteIp = '';
        if (!empty(\wepiao::$config['isInIpBlack']) && (\wepiao::$config['isInIpBlack'] == 1)) {
            $isInIpBlack = 1;
            $remoteIp = Net::getRemoteIp();
        }

        //1、获取余票情况
        $arrRoomSeats = [];
        if ($iRoomSeat) {
            $arrMpIds = $this->model("TicketLeftOver")->getMpIdsByCinemaId($iCinemaId);
            $arrRoomSeats = $this->model("TicketLeftOver")->getRoomSeatNumByMpIds($iCinemaId, $arrMpIds);
        }
        //2、获取用户优惠
        $arrUserDiscount = [];
        $isNewUser = 0;
        if ($iDiscount2p && !empty($strOpenId)) {
            $arrUserDiscountRes = $this->service('Bonus')->getDiscountByOpenId(['channelId' => $iChannelId, 'openId' => $strOpenId]);
            $arrUserDiscount = (isset($arrUserDiscountRes['ret']) && ($arrUserDiscountRes['ret'] == 0) && !empty($arrUserDiscountRes['data'])) ? $arrUserDiscountRes['data'] : [];
            //判断当前用户是否为新用户
            if (isset($arrUserDiscount['isNew']) && ($arrUserDiscount['isNew'] == 1)) {
                $isNewUser = 1;
            }
        }
        //获取当前时间
        $strNowDate = date('Ymd');
        //3、格式化排期
        foreach ($arrSche as $k => &$v) {
            //如果有影片id，则筛选这个影片的排期
            if (!empty($v['id']) && !empty($iMovieId) && ($iMovieId != $v['id'])) {
                unset($arrSche[$k]);
                continue;
            }
            //只处理有优惠的
            $isDiscount = 0;//默认0是没有优惠
            foreach ($v['sche'] as $strDate => &$sche) {
                //删除小于当前时间的排期
                if ($strDate < $strNowDate) {
                    unset($v['sche'][$strDate]);
                    continue;
                }
                foreach ($sche as $num => &$seat) {

                    foreach ($seat['seat_info'] as $key => &$info) {
                        //格式化余票状态
                        if (array_key_exists($info['mpid'], $arrRoomSeats) && !empty($arrRoomSeats[$info['mpid']]['total'])) {
                            $info['seatStatus'] = self::_formatSeatStatus($arrRoomSeats[$info['mpid']]['total'],
                                $arrRoomSeats[$info['mpid']]['left']);
                        } else {
                            $info['seatStatus'] = 0;
                        }
                        //格式化优惠到人（条件：有优惠活动、有用户可用优惠、开启了优惠到人）
                        if (!empty($info['discount_id']) || !empty($pay_reduce_id)) {
                            //1、优惠降级判断
                            if ($iDemote == 1) {
                                //优惠降级打开后，优惠标签置0，优惠价格置为售卖价
                                $info['is_discount'] = 0;
                                $info['discount'] = $info['price'];
                                $info['discount_newbie'] = $info['price'];
                            }//判断银行支付营销
                            elseif (!empty($pay_reduce_id)) {
                                //获取所有的支付方式优惠
                                $scheStay = 0;
                                if (!empty($info['pay_reduce_list'])) {
                                    foreach ($info['pay_reduce_list'] as $pay_reduce_item) {
                                        $reduce_arr = explode("_", $pay_reduce_item['pay_reduce_id']);
                                        if ($reduce_arr[1] == $pay_reduce_id) {
                                            $scheStay = 1;
                                            $info['discount_des'] = $pay_reduce_item['pay_reduce_label'];
                                            $info['is_discount'] = 1;
                                            $info['discount_id'] = "";
                                            //因为type为3的是订单满减活动订单满减活动订单满减活动单条排期价格不变
                                            if ($reduce_arr[0] != "3") {
                                                $info['discount_newbie'] = $pay_reduce_item['pay_reduce_price'];
                                                $info['discount'] = $pay_reduce_item['pay_reduce_price'];
                                            }
                                        }
                                    }
                                }
                                if ($scheStay == 0) {
                                    unset($seat['seat_info'][$key]);
                                }
                            } //2、优惠到人判断
                            elseif ($iDiscount2p && !empty($info['discount_id']) && isset($arrUserDiscount['reslims'])) {
                                $arrUserDiscountTmp = explode("_", $info['discount_id']);
                                $mixDiscountId = $arrUserDiscountTmp[1];
                                //如果从Java那儿获取到了该用户的优惠到人信息
                                if (!empty($arrUserDiscount['reslims']) && in_array($mixDiscountId, $arrUserDiscount['reslims'])) {
                                    $info['is_discount'] = 1;
                                    $isDiscount = 1;
                                    if ($isNewUser) {
                                        $info['discount'] = $info['discount_newbie'];
                                        $info['vip_price'] = !empty($info['vip_price_new']) ? $info['vip_price_new'] : '';
                                    } else {
                                        $info['vip_price'] = !empty($info['vip_price_old']) ? $info['vip_price_old'] : '';
                                    }
                                } else {
                                    //优惠用完之后不仅标记要成为0价格也要回复正常的网售价
                                    $info['is_discount'] = 0;
                                    $info['discount'] = $info['price'];
                                    //$info['vip_price'] = !empty($info['member_price']) ? $info['member_price'] : '';
                                }
                            } //其他情况
                            else {
                                if (in_array($iChannelId, ['8', '9'])) {
                                    $info['discount'] = $info['discount_newbie'];
                                    $info['vip_price'] = !empty($info['vip_price_new']) ? $info['vip_price_new'] : '';
                                } else {
                                    $info['vip_price'] = !empty($info['vip_price_old']) ? $info['vip_price_old'] : '';
                                }
                                $isDiscount = 1;
                            }
                        }
                        //格式化影院vip价格(这个vip_price价格, 需要区分用户是否能享受当前优惠,因为朝伟那儿的会员价,包含了优惠,所以如果用户优惠资格用完了,就不
                        //用再格式化了)
                        if ($isVipCardDemote) {
                            $info['vip_price'] = '';
                        }
                        //方便调试, 如果请求参数有 vip_price_debug, 就不unset vip_price_new 和 vip_price_old 了。
                        if (!isset($_REQUEST['vip_price_debug'])) {
                            unset($info['vip_price_new'], $info['vip_price_old']);
                        }
                        //排期价格投毒
                        //黑名单情况就修改排期价格（调价5%-20%）
                        $nowHour = 0 + date('H');
                        $randUp = 1.05 + (intval($iCinemaId) + $nowHour * pow(-1, $nowHour)) % 18 * 0.01;
                        if ($isInIpBlack && !empty($info['calculate_price'])) {
                            $info['price'] = strval(ceil($info['price'] * $randUp));
                            $info['calculate_price'] = strval(ceil($info['calculate_price'] * $randUp));
                            $info['discount_newbie'] = strval(ceil($info['discount_newbie'] * $randUp));
                            $info['discount'] = strval(ceil($info['discount'] * $randUp));
                        }
                    }
                    $seat['seat_info'] = array_values($seat['seat_info']);
                    //如果seat_info为空,则彻底unset
                    if (empty($seat['seat_info'])) {
                        unset($v['sche'][$strDate]);
                    }
                }
            }
            //排期正常的话，排期中需要必须有当天的数据，如果没有，赋予一个空的数组进去
            if (!empty($arrSche[$k]['sche']) && !array_key_exists($strNowDate, $arrSche[$k]['sche'])) {
                $arrSche[$k]['sche'][$strNowDate] = [];
            }
            //如果开启了优惠到人，遍历优惠公告label，看是否需要设置优惠标签为空
            if ($iDemote) {
                $v['discount_label'] = [];  //其实这儿是个空对象比较好
            } //如果有银行卡活动，则整个discount_label只保留该银行卡活动的
            elseif (!empty($pay_reduce_id)) {
                foreach ($v['discount_label'] as $strDate => $arrValue) {
                    foreach ($arrValue as $key => $arrDiscountInfo) {
                        if ($pay_reduce_id != $arrDiscountInfo['discount_id']) {
                            unset($v['discount_label'][$strDate][$key]);
                        }
                    }
                    //如果日期下还有优惠，执行array_values重新索引
                    if (!empty($v['discount_label'][$strDate])) {
                        $v['discount_label'][$strDate] = array_values($v['discount_label'][$strDate]);
                    } //如果日期下没有了优惠，直接unset这个日期属性
                    else {
                        unset($v['discount_label'][$strDate]);
                    }
                }
            } //如果没有银行卡活动id,需要去除银行卡活动类型的优惠标签
            elseif ($iDiscount2p && isset($arrUserDiscount['reslims']) && !empty($v['discount_label']) && !empty($v['sche'])) {
                foreach ($v['discount_label'] as $strDate => $arrValue) {
                    foreach ($arrValue as $key => $arrDiscountInfo) {
                        if (!empty($arrUserDiscount['reslims']) && !in_array($arrDiscountInfo['discount_id'], $arrUserDiscount['reslims'])) {
                            unset($v['discount_label'][$strDate][$key]);
                        }
                        //去除银行卡活动类型的优惠标签
                        if (isset($arrDiscountInfo['for_bank_pay']) && ($arrDiscountInfo['for_bank_pay'] == 1)) {
                            unset($v['discount_label'][$strDate][$key]);
                        }
                    }
                    //如果日期下还有优惠，执行array_values重新索引
                    if (!empty($v['discount_label'][$strDate])) {
                        $v['discount_label'][$strDate] = array_values($v['discount_label'][$strDate]);
                    } //如果日期下没有了优惠，直接unset这个日期属性
                    else {
                        unset($v['discount_label'][$strDate]);
                    }
                }
            }

            //更改影片海报左上角的惠字
            $v['is_discount'] = $isDiscount;
            //银行卡优惠活动
            $arrBankPrivilege = [];
            if (!empty($v['sche']) && !empty($v['id'])) {
                $arrBankPrivilegeRes = $this->service('BankPrivilege')->readBankPrivilege([
                    'cinemaId' => $iCinemaId,
                    'movieId' => $v['id'],
                    'channelId' => $iChannelId,
                    'cityId' => $iCityId,
                ]);
                $arrBankPrivilege = (isset($arrBankPrivilegeRes['ret']) && ($arrBankPrivilegeRes['ret'] == 0) && !empty($arrBankPrivilegeRes['data']['list'])) ? $arrBankPrivilegeRes['data']['list'] : [];
            }
            $arrSche[$k]['bank_privilege'] = $arrBankPrivilege;
            //万一价格问题，导致投毒误杀，这个参数起到调试作用，可以直观看到是不是在黑名单
            if (isset($_REQUEST['isInIpBlack'])) {
                $arrSche[$k]['isInIpBlack'] = $isInIpBlack;
                $arrSche[$k]['clientIp'] = $remoteIp;
            }
            ////////////  银行卡优惠活动   //////
            //判断当前影片每天的排期都未空的，直接删除这个节点
            if (empty($v['sche'])) {
                unset($arrSche[$k]);
            }
        }

        //过滤格式化后的无效排期
        $arrSche = array_filter($arrSche);
        $arrSche = array_values($arrSche);

        return $arrSche;
    }

    /**
     * 格式化售票状态
     *
     * @param $iAllNum
     * @param $iLeftNum
     *
     * @return int 格式化seatstatus
     */
    protected static function _formatSeatStatus($iAllNum, $iLeftNum)
    {
        if (($iLeftNum / $iAllNum) == 0) {
            $iSeatStatus = 2;//卖光了 也要变成跟余票紧张的状态
        } elseif (($iLeftNum / $iAllNum) < 0.3) {
            $iSeatStatus = 1;//余票紧张
        } else {
            $iSeatStatus = 0;//正常
        }

        return $iSeatStatus;
    }


    //最新版查询排期调用朝伟接口
    //$arrInput openId,cinemaId,movieId,channelId,payReduceId
    //
    public function qryScheV2($arrInput = [])
    {
        $arrSendParams = array();
        $strOpenId = self::getParam($arrInput, 'openId');
        $iCinemaId = self::getParam($arrInput, 'cinemaId');
        $iMovieId = self::getParam($arrInput, 'movieId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iNeedmore = self::getParam($arrInput, 'needMore', 3);
        $iPayreduceId = self::getParam($arrInput, 'payReduceId');

        $arrSendParams['openId'] = $strOpenId;
        $arrSendParams['cinemaId'] = $iCinemaId;
        $arrSendParams['chanId'] = $iChannelId;
        if (!empty($iMovieId)) {
            $arrSendParams['movId'] = $iMovieId;
        }
        if (!empty($iNeedmore)) {
            $arrSendParams['needMore'] = $iNeedmore;
        }
        if (!empty($iPayreduceId)) {
            $arrSendParams['payReduId'] = $iPayreduceId;
        }
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
            'iTryTimes' => 1,
        ];

        $arrRet = $this->http(JAVA_API_QUERY_SCHE, $httpParams);
        if ($arrRet['ret'] == 0) {
            //是否走新版crontask_new数据源
            $iMovieNewStatic = !empty(\wepiao::$config['movieNewStatic']) ? \wepiao::$config['movieNewStatic'] : 0;
            if ($iMovieNewStatic) {
                $arrRet['data'] = $this->__addMovieInfoNewStatic($arrRet['data'], $iChannelId, $iCinemaId);
            } else {
                $arrRet['data'] = $this->__addMovieInfo($arrRet['data'], $iChannelId, $iCinemaId);
            }
        }
        return $arrRet;

    }


    //格式化影片添加基础信息以及银行卡优惠
    private function __addMovieInfo($arrData, $iChannelId, $iCinemaId = '')
    {
        //上来先处理速8 置顶
        $arrData = $this->__formatSort($arrData, $iChannelId);
        $arrMovieIds = [];
        //上来先格式化添加前端所需字段的默认值，保证及时没有读到redis，不报错也不影响用户进入到排期页
        foreach ($arrData as &$arrMovie) {
            $arrMovieIds[] = $arrMovie['movieId'];
            $arrMovie['movieName'] = '';
            $arrMovie['score'] = 50;
            $arrMovie['scoreCount'] = 200;
            $arrMovie['wantCount'] = 200;
            $arrMovie['seenCount'] = 200;
            $arrMovie['longs'] = '';
            $arrMovie['posterUrl'] = '';
            $arrMovie['director'] = '';
            $arrMovie['actor'] = '';
            $arrMovie['version'] = '';
            $arrMovie['date'] = '';
            $arrMovie['tags'] = '';
            $arrMovie['bankPrivilege'] = [];
        }

        if (!empty($arrMovieIds)) {

            $params = [
                'channelId' => $iChannelId,
                'movieIds' => $arrMovieIds
            ];
            //先读文件缓存
            $cacheData = $this->getCacheData($params, 1);
            if (!empty($cacheData)) {
                $movieInfo = $cacheData;
            } else {
                $movieInfo = $this->service('movie')->readMovieInfos($params);

                if ($movieInfo['ret'] == 0 && !empty($movieInfo['data'])) {
                    //设置文件缓存
                    $this->setCacheData($params, $movieInfo);
                }
            }
            //判断是否在IP黑名单，如果在，就修改排期价格（IP黑名单的isInIpBlack配置，在serviceBase的ipBlackLimit设置的）
            $isInIpBlack = 0;
            if (!empty(\wepiao::$config['isInIpBlack']) && (\wepiao::$config['isInIpBlack'] == 1)) {
                $isInIpBlack = 1;
            }
            if ($movieInfo['ret'] == 0 && !empty($movieInfo['data'])) {
                foreach ($arrData as &$arrFilm) {
                    if (!empty($movieInfo['data'][$arrFilm['movieId']])) {
                        $singleMovie = $movieInfo['data'][$arrFilm['movieId']];
                        $arrFilm['movieName'] = $singleMovie['name'];
                        $arrFilm['score'] = $singleMovie['score'];
                        $arrFilm['scoreCount'] = $singleMovie['scoreCount'];
                        $arrFilm['wantCount'] = $singleMovie['wantCount'];
                        $arrFilm['seenCount'] = $singleMovie['seenCount'];
                        $arrFilm['longs'] = $singleMovie['longs'];
                        $arrFilm['posterUrl'] = $singleMovie['poster_url'];
                        $arrFilm['director'] = $singleMovie['director'];
                        $arrFilm['actor'] = $singleMovie['actor'];
                        $arrFilm['version'] = $singleMovie['version'];
                        $arrFilm['date'] = $singleMovie['date'];
                        $arrFilm['tags'] = $singleMovie['tags'];
                    }
                    //万一价格问题，导致投毒误杀，这个参数起到调试作用，可以直观看到是不是在黑名单
                    if (isset($_REQUEST['isInIpBlack'])) {
                        $arrFilm['isInIpBlack'] = $isInIpBlack;
                        $arrFilm['clientIp'] = Net::getRemoteIp();
                    }
                    //黑名单情况就修改排期价格（调价5%-20%）
                    $nowHour = 0 + date('H');
                    $randUp = 1.05 + (intval($iCinemaId) + $nowHour * pow(-1, $nowHour)) % 18 * 0.01;
                    if ($isInIpBlack && !empty($arrFilm['sche']) && is_array($arrFilm['sche'])) {
                        foreach ($arrFilm['sche'] as $date => &$arrSches) {
                            if (!empty($arrSches)) {
                                foreach ($arrSches as &$sche) {
                                    $sche['showPri'] = strval(ceil($sche['showPri'] * $randUp));
                                    $sche['memShowPri'] = strval(ceil($sche['memShowPri'] * $randUp));
                                }
                            }
                        }
                    }
                }
            }
        }
        return $arrData;

    }

    //格式化影片添加基础信息以及银行卡优惠
    private function __addMovieInfoNewStatic($arrData, $iChannelId, $iCinemaId = '')
    {
        //上来先处理速8 置顶
        $arrData = $this->__formatSort($arrData, $iChannelId);
        $arrMovieIds = [];
        //上来先格式化添加前端所需字段的默认值，保证及时没有读到redis，不报错也不影响用户进入到排期页
        foreach ($arrData as &$arrMovie) {
            $arrMovieIds[] = $arrMovie['movieId'];
            $arrMovie['movieName'] = '';
            $arrMovie['score'] = 50;
            $arrMovie['scoreCount'] = 200;
            $arrMovie['wantCount'] = 200;
            $arrMovie['seenCount'] = 200;
            $arrMovie['longs'] = '';
            $arrMovie['posterUrl'] = '';
            $arrMovie['director'] = '';
            $arrMovie['actor'] = '';
            $arrMovie['version'] = '';
            $arrMovie['date'] = '';
            $arrMovie['tags'] = '';
            $arrMovie['bankPrivilege'] = [];
        }

        if (!empty($arrMovieIds)) {
            $params = [
                'channelId' => $iChannelId,
                'movieIds' => $arrMovieIds,
            ];
            //先读文件缓存
            $cacheData = $this->getCacheData($params, 1);
            if (!empty($cacheData)) {
                $movieInfo = $cacheData;
            } else {
                $movieInfo = $this->service('movie')->readMovieAndScoreNewStatic($params);

                if ($movieInfo['ret'] == 0 && !empty($movieInfo['data'])) {
                    //设置文件缓存
                    $this->setCacheData($params, $movieInfo);
                }
            }
            $isInIpBlack = 0;
            if (!empty(\wepiao::$config['isInIpBlack']) && (\wepiao::$config['isInIpBlack'] == 1)) {
                $isInIpBlack = 1;
            }
            if ($movieInfo['ret'] == 0 && !empty($movieInfo['data'])) {
                foreach ($arrData as &$arrFilm) {
                    if (!empty($movieInfo['data'][$arrFilm['movieId']])) {
                        $singleMovie = $movieInfo['data'][$arrFilm['movieId']];
                        $arrFilm['movieName'] = $singleMovie['name'];
                        $arrFilm['score'] = $singleMovie['initScore'];
                        $arrFilm['scoreCount'] = $singleMovie['scoreCount'];
                        $arrFilm['wantCount'] = $singleMovie['wantCount'];
                        $arrFilm['seenCount'] = $singleMovie['seenCount'];
                        $arrFilm['longs'] = $singleMovie['longs'];
                        $arrFilm['posterUrl'] = $singleMovie['poster_url'];
                        $arrFilm['director'] = $singleMovie['director'];
                        $arrFilm['actor'] = $singleMovie['actor'];
                        $arrFilm['version'] = $singleMovie['version'];
                        $arrFilm['date'] = $singleMovie['date'];
                        $arrFilm['tags'] = $singleMovie['tags'];
                    }
                    //万一价格问题，导致投毒误杀，这个参数起到调试作用，可以直观看到是不是在黑名单
                    if (isset($_REQUEST['isInIpBlack'])) {
                        $arrFilm['isInIpBlack'] = $isInIpBlack;
                        $arrFilm['clientIp'] = Net::getRemoteIp();
                    }
                    //黑名单情况就修改排期价格（调价5%-20%）
                    $nowHour = 0 + date('H');
                    $randUp = 1.05 + (intval($iCinemaId) + $nowHour * pow(-1, $nowHour)) % 18 * 0.01;
                    if ($isInIpBlack && !empty($arrFilm['sche']) && is_array($arrFilm['sche'])) {
                        foreach ($arrFilm['sche'] as $date => &$arrSches) {
                            if (!empty($arrSches)) {
                                foreach ($arrSches as &$sche) {
                                    $sche['showPri'] = strval(ceil($sche['showPri'] * $randUp));
                                    $sche['memShowPri'] = strval(ceil($sche['memShowPri'] * $randUp));
                                }
                            }
                        }
                    }
                }
            }
        }

        return $arrData;
    }


    //格式化处理速8 置顶
    private function __formatSort($arrData, $iChannelId)
    {
        //格瓦拉渠道不做排期前置
        if ($iChannelId == 80 || $iChannelId == 84) {
            return $arrData;
        }
        foreach ($arrData as $k => $arrMovie) {
            //所有影片包括速8 0点场 挪到前一天 ，当前天也会显示
            //app不处理挪排期
            if ((isset($arrMovie['isZero']) && $arrMovie['isZero'] == 1) && !in_array($iChannelId, [63, 8, 9])) {
                foreach ($arrMovie['sche'] as $date => $arrValue) {
                    foreach ($arrValue as $j => $array) {
                        $startH = str_replace(":", "", $array['time']);
                        $intH = intval($startH);
                        if ($intH >= 0 && $intH < 600) {
                            $prevUnix = strtotime("-1 day", strtotime($date));
                            $prevDate = date('Ymd', $prevUnix);
                            if (!isset($arrData[$k]['sche'][$prevDate])) {
                                $arrData[$k]['sche'][$prevDate] = [];
                            }
                            $array['showDesc'] = '次日放映';
                            array_push($arrData[$k]['sche'][$prevDate], $array);
                            //挪优惠公告
                            if (!empty($array['disId'])) {
                                if (!isset($arrData[$k]['disLabel'][$prevDate])) {
                                    $arrData[$k]['disLabel'][$prevDate] = [];
                                }
                                foreach ($arrMovie['disLabel'][$date] as $dis) {
                                    if ($dis['disId'] == $array['disId']) {
                                        $prevDisFlag = 0;
                                        foreach ($arrData[$k]['disLabel'][$prevDate] as $prevDis) {
                                            if ($prevDis['disId'] == $dis['disId']) {
                                                $prevDisFlag = 1;
                                                break;
                                            }
                                        }

                                        if (!$prevDisFlag) {
                                            array_push($arrData[$k]['disLabel'][$prevDate], $dis);
                                            break;
                                        }

                                    }
                                }
                            }
                            //挪点映标识
                            if (!isset($arrData[$k]['pointMappingLabel'][$prevDate]) && isset($arrData[$k]['pointMappingLabel'][$date])) {
                                $arrData[$k]['pointMappingLabel'][$prevDate] = [['isPointMapping' => 1]];
                            }
                        }
                    }
                }
            }


        }

        return array_values($arrData);
    }

    /**
     * 获取某排期扩展属性（是否配有3D 是否需要自带3D眼镜）
     */
    public function getScheduleExt(array $arrInput = [])
    {
        //参数整理
        $scheduleId = self::getParam($arrInput, 'scheduleId');
        $iCinemaId = self::getParam($arrInput, 'cinemaId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        $return = self::getStOut();
        if (empty($iCinemaId) || empty($iChannelId) || empty($scheduleId)) {
            return $return;
        }
        //获取影院详情
        $httpParams = [
            'arrData' => ['cinema_no' => $iCinemaId],
            'sMethod' => 'GET',
            'iTimeout' => 2,
            'iTryTimes' => 1,
        ];
        $url = sprintf(JAVA_ADDR_SCHEDULE_EXT, $scheduleId);
        $return = $this->http($url, $httpParams);
        return $return;
    }

    /**
     * 查询渠道版数据
     * @param array $arrInput
     * @return array|bool|mixed
     */
    public function qryScheChannel($arrInput = [])
    {
        $arrSendParams = array();
        $strOpenId = self::getParam($arrInput, 'openId');
        $iCinemaId = self::getParam($arrInput, 'cinemaId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        $arrSendParams['openId'] = $strOpenId;
        $arrSendParams['cinemaId'] = $iCinemaId;
        $arrSendParams['chanId'] = $iChannelId;
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
            'iTryTimes' => 1,
        ];
        $arrRet = $this->http(JAVA_API_QUERY_SCHE_CHANNEL, $httpParams);
        if ($arrRet['ret'] == 0 && !empty($arrRet['data'])) {
            //判断是否在IP黑名单，如果在，就修改排期价格（IP黑名单的isInIpBlack配置，在serviceBase的ipBlackLimit设置的）
            $isInIpBlack = 0;
            if (!empty(\wepiao::$config['isInIpBlack']) && (\wepiao::$config['isInIpBlack'] == 1)) {
                $isInIpBlack = 1;
            }
            if ($isInIpBlack == 1) {
                $backIp = Net::getRemoteIp();
                //格式化具体场次信息格式化排期
                foreach ($arrRet['data'] as &$Moive) {
                    foreach ($Moive['sche'] as $date => &$scheDate) {
                        foreach ($scheDate as &$value) {
                            foreach ($value['seat_info'] as $key => &$sche) {
                                /*if (isset($arrRoomSeats[$sche['mpid']])) {
                                    $seatNum = $arrRoomSeats[$sche['mpid']];
                                    if ($seatNum['left'] / $seatNum['total'] == 0) {
                                        $sche['seatStatus'] = TICKET_LEFT_OVER_NONE; //售罄
                                    } elseif ($seatNum['left'] / $seatNum['total'] < 0.3) {
                                        $sche['seatStatus'] = TICKET_LEFT_OVER_MIN; //紧张
                                    } else {
                                        $sche['seatStatus'] = TICKET_LEFT_OVER_MAX; //正常
                                    }
                                }*/
                                //排期价格投毒
                                //万一价格问题，导致投毒误杀，这个参数起到调试作用，可以直观看到是不是在黑名单
                                if (isset($_REQUEST['isInIpBlack'])) {
                                    $sche['isInIpBlack'] = $isInIpBlack;
                                    $sche['clientIp'] = $backIp;
                                }
                                //黑名单情况就修改排期价格（调价5%-20%）
                                $nowHour = 0 + date('H');
                                $randUp = 1.05 + (intval($iCinemaId) + $nowHour * pow(-1, $nowHour)) % 18 * 0.01;
                                if ($isInIpBlack && !empty($sche['calculate_price'])) {
                                    $sche['price'] = strval(ceil($sche['price'] * $randUp));
                                    $sche['market_price'] = strval(ceil($sche['market_price'] * $randUp));
                                    $sche['calculate_price'] = strval(ceil($sche['calculate_price'] * $randUp));
                                    $sche['discount_newbie'] = strval(ceil($sche['discount_newbie'] * $randUp));
                                    $sche['discount'] = strval(ceil($sche['discount'] * $randUp));
                                }
                            }
                        }
                    }
                }
            }
        }

        return $arrRet;
    }

}