<?php

namespace sdkService\service;


use sdkService\resources\staticGewara;

class serviceCinema extends serviceBase
{

    /**
     * 获取影院详情信息
     * @param   string  cinemaId    影院编号
     * @param   string  channelId   渠道编号
     * @param   string  openId      用户openId，非必须参数，如果传入，则可以在返回结果中，标识用户是否收藏过此影院
     * @return array
     */
    public function readCinemaInfo(array $arrInput = [])
    {
        //参数整理
        $iCinemaId = self::getParam($arrInput, 'cinemaId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        $strOpenId = self::getParam($arrInput, 'openId');
        $return = self::getStOut();
        if (empty($iCinemaId) || empty($iChannelId)) {
            return $return;
        }
        //获取影院详情
        $arrData = $this->model('cinema')->readCinemaInfo($iChannelId, $iCinemaId);
        //为当前影院详情信息，添加用户是否收藏的标识
        if (!empty($strOpenId) && !empty($arrData['info'])) {
            $arrData['info'] = $this->addFavorToCinemaInfo($iChannelId, $arrData['info'], $strOpenId);
        }
        //format $data here
        $return['data'] = $arrData;
        return $return;
    }

    /**
     * 获取影院详情信息——V2版本
     * 此版本接口, 除了收藏标识外, 完整透传商品中心的接口数据
     *
     * @param   string  cinemaId    影院编号
     * @param   string  channelId   渠道编号
     * @param   string  openId      用户openId，非必须参数，如果传入，则可以在返回结果中，标识用户是否收藏过此影院
     *
     * @return array
     */
    public function readCinemaInfoV2(array $arrInput = [])
    {
        //参数整理
        $iCinemaId = self::getParam($arrInput, 'cinemaId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        $strOpenId = self::getParam($arrInput, 'openId');
        $return = self::getStOut();
        if (empty($iCinemaId) || empty($iChannelId)) {
            return $return;
        }
        //获取影院详情
        $httpParams = [
            'arrData'   => [],
            'sMethod'   => 'GET',
            'iTimeout'  => 2,
            'iTryTimes' => 1,
        ];
        $url = sprintf(JAVA_API_CORE_CENTER_CINEMA_INFO, $iCinemaId);
        $return = $this->http($url, $httpParams);
        //为当前影院详情信息，添加用户是否收藏的标识
        if ( !empty($return['data'])) {
            $return['data']['favor'] = 0;
            if ( !empty($strOpenId)) {
                $return['data'] = $this->addFavorToCinemaInfo($iChannelId, $return['data'], $strOpenId);
            }
        }

        return $return;
    }

    /**
     * 获取影院详情信息——V3版本
     * 此接口的主要作用, 是替代 readCinemaInfo 接口, 后续, 会由 readCinemaInfoV2 替换此接口, 也就是说, 以后最终要用的, 是 readCinemaInfoV2 接口。
     * 此接口, 和 readCinemaInfo 相比, 格式完全相同, 只是数据由商品中心提供
     * 此接口, 和V2都是商品中心提供数据, 但是数据格式不同。
     *
     * @param   string  cinemaId    影院编号
     * @param   string  channelId   渠道编号
     * @param   string  openId      用户openId，非必须参数，如果传入，则可以在返回结果中，标识用户是否收藏过此影院
     *
     * @return array
     */
    public function readCinemaInfoV3(array $arrInput = [])
    {
        //参数整理
        $iCinemaId = self::getParam($arrInput, 'cinemaId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        $strOpenId = self::getParam($arrInput, 'openId');
        $return = self::getStOut();
        if (empty($iCinemaId) || empty($iChannelId)) {
            return $return;
        }
        //获取影院详情
        $httpParams = [
            'arrData'   => [],
            'sMethod'   => 'GET',
            'iTimeout'  => 2,
            'iTryTimes' => 1,
        ];
        $url = sprintf(JAVA_API_CORE_CENTER_CINEMA_INFO_V1, $iCinemaId);
        $return = $this->http($url, $httpParams);
        //为当前影院详情信息，添加用户是否收藏的标识
        if ( !empty($return['data'])) {
            $return['data']['favor'] = 0;
            if ( !empty($strOpenId)) {
                $return['data'] = $this->addFavorToCinemaInfo($iChannelId, $return['data'], $strOpenId);
            }
        }

        return $return;
    }

    /**
     * 获取 城市下影院列表 基础信息
     * @param   intval   cityId      城市编号
     * @param   intval   channelId   渠道编号
     * @param   string   openId      用户openId，非必须参数，如果传入，则可以在返回结果中，标识用户是否收藏过此影院
     * @param   string   movieId     影片id，该字段用途是：假如传入此字段，则影院列表中的影院在这个影片有优惠的时候，才显示优惠文案
     */
    public function readCinemasCity(array $arrInput = [])
    {
        $iCityId = self::getParam($arrInput, 'cityId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        $strOpenId = self::getParam($arrInput, 'openId');
        $strMovieId = self::getParam($arrInput, 'movieId', 0);
        $return = self::getStOut();
        if (empty($iCityId) || empty($iChannelId)) {
            return $return;
        }
        $arrData = $this->model('cinema')->readCityCinema($iChannelId, $iCityId, $iChannelId);
        //格式化影院列表优惠标签，合并大于三个的优惠标签 如果传入影片ID则过滤非相关影片标签
        $this->_formatCinemaMovieDiscount($arrData, $strMovieId, $iChannelId);

        //为当前影院详情信息，添加用户是否收藏的标识
        if (!empty($strOpenId) && !empty($arrData)) {
            $arrData = $this->addFavorToCinemaList($iChannelId, $arrData, $strOpenId);
        }
        //format $data here
        $return['data'] = $arrData;
        return $return;
    }

    /**
     * 获取 城市下影院列表 基础信息
     * @param   intval   cityId      城市编号
     * @param   intval   channelId   渠道编号
     * @param   string   openId      用户openId，非必须参数，如果传入，则可以在返回结果中，标识用户是否收藏过此影院
     * @param   string   movieId     影片id，该字段用途是：假如传入此字段，则影院列表中的影院在这个影片有优惠的时候，才显示优惠文案
     */
    public function readCinemasCityChannel(array $arrInput = [])
    {
        $iCityId = self::getParam($arrInput, 'cityId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        $return = self::getStOut();
        if (empty($iCityId) || empty($iChannelId)) {
            return $return;
        }
        $arrSendParams = ['cityId' => $iCityId, 'tpid' => $iChannelId];
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
            'iTryTimes' => 1,
        ];

        $return = $this->http(JAVA_API_CITY_CINEMA_LIST_CHANNEL, $httpParams);
        return $return;
    }

    public function getCinemaListByCity(array $arrInput = [])
    {

        $iCityId = self::getParam($arrInput, 'cityId');
        $return = self::getStOut();
        if (empty($iCityId)) {
            return $return;
        }
        $arrSendParams = [];
        $arrSendParams['cityId'] = $iCityId;
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
            'iTryTimes' => 1,
        ];

        $arrRet = $this->http(JAVA_API_GET_CINEMAS_LIST, $httpParams);
        return $arrRet;
    }


    /**
     * 获取 影院影厅座位图 信息
     * @param intval cinemaId   影院编号
     * @param strval roomId    影厅编号
     * @param strval channelId  渠道编号
     */
    public function readCinemaRoom(array $arrInput = [])
    {
        //走商品中心
        return $this->service('goods')->readCinemaRoom($arrInput);
        
        $iCinemaId = self::getParam($arrInput, 'cinemaId');
        $sRoomId = self::getParam($arrInput, 'roomId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        $return = self::getStOut();
        if (empty($iCinemaId) || empty($sRoomId) || empty($iChannelId)) {
            return $return;
        }
        $arrData = $this->model('cinema')->readCinemaRoom($iChannelId, $iCinemaId, $sRoomId);
        $return['data'] = $arrData;
        return $return;
    }

    /**
     * 根据影院编号，获取搜索影院时，应该有的字段信息
     * 适用于动静分离项目，如果无法根据影院Id，从redis中获取影院详情数据，则返回对应这个影院的数据为空数组
     * @note 该接口需要调用其他服务, 比如: 获取用户收藏的影院
     *
     * @param string cinemaIds  影片id的字符串,如: 1000103|1012406
     * @param string channelId  渠道编号
     * @param string openId     用户openId
     * @return array
     */
    public function getSearchCinemaInfo(array $arrInput = [])
    {
        //参数整理
        $strCinemaId = self::getParam($arrInput, 'cinemaIds');
        $strOpenId = self::getParam($arrInput, 'openId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        $arrCinemaNo = [];
        $return = array();
        if (!empty($strCinemaId)) {
            $arrCinemaNo = explode('|', $strCinemaId);
        }
        //获取用户喜欢的影院列表id 拿到影院id组成的数组
        $arrData = $this->service('favorite')->getFavoriteCinema(['openId' => $strOpenId, 'channelId' => $iChannelId]);
        $arrFavCinemaIds = (is_array($arrData['data']['cinemaList']) && !empty($arrData['data']['cinemaList'])) ? $arrData['data']['cinemaList'] : [];
        //遍历影院id, 获取每个影院的优惠文案
        foreach ($arrCinemaNo as $cinemaNo) {
            $data = array();
            //获取影院的优惠文案
            $arrDiscountDes = $this->getCinemaDiscount(['cinemaId' => $cinemaNo, 'channelId' => $iChannelId]);
            $strDiscountDes = $arrDiscountDes['data']['discountDes'];
            //获取影院列表信息
            $arrCinemaDetailInfo = $this->readCinemaInfo(['cinemaId' => $cinemaNo, 'channelId' => $iChannelId]);
            $arrCinemaData = isset($arrCinemaDetailInfo['data']['info']) ? $arrCinemaDetailInfo['data']['info'] : [];

            if (empty($arrCinemaData)) {
                $return[$cinemaNo] = [];
                continue;
            }
            //需要的字段
            $needField = array(
                'id',
                'agentId',
                'name',
                'addr',
                'area_name',
                'tele',
                'longitude',
                'latitude',
                'flag_groupon',
                'flag_elec_ticket',
                'flag_seat_status',
                'flag_seat_ticket',
                'discount_info',
                'min_price',
                'max_price',
                'sched_num_today',
                'movie_num_today',
                'sched_num_tomorrow',
                'movie_num_tomorrow',
                'discount_des',
                'refund',
                'snack_status'
            );
            foreach ($needField as $key => $field) {
                $value = isset($arrCinemaData[$field]) ? $arrCinemaData[$field] : '';
                $data[$field] = $value;
            }
            //设置优惠信息，以及其他目前不需要字段的固定值
            if (!empty($data)) {
                $data['min_price'] = $data['max_price'] = 0;
                $data['sched_num_today'] = $data['movie_num_today'] = $data['sched_num_tomorrow'] = $data['movie_num_tomorrow'] = '0';
                $data['discount_des'] = $strDiscountDes;
            }
            //设置用户是否收藏字段
            $intFavorStatus = 0;
            if (in_array($data['id'], $arrFavCinemaIds)) {
                $intFavorStatus = 1;
            }
            $data['favor'] = $intFavorStatus;
            //设置退票字段，没有值得情况，用数字0
            $data['refund'] = !empty($data['refund']) ? (int)$data['refund'] : 0;
            //
            $return[$cinemaNo] = $data;
        }
        return $return;
    }

    /**
     * 获取影院优惠信息
     * @todo 可考虑是否优化, 目前来说, 影院优惠信息, 为k-v结构, 如果做成hash, 就能支持一次查询多个了，如果要优化，也是从crontask项目
     * 来优化
     *
     * @param string cinemaId   影院编号
     * @param string channelId  渠道编号
     * @return string 返回优惠文案, 如果没有,则为空字符串
     */
    public function getCinemaDiscount(array $arrInput = [])
    {
        //参数整理
        $iCinemaId = self::getParam($arrInput, 'cinemaId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        $strDiscountDes = '';
        $return = self::getStOut();
        //参数判断
        $return['data']['cinemaId'] = $iCinemaId;
        if (empty($iCinemaId) || empty($iChannelId)) {
            $return['data']['discountDes'] = $strDiscountDes;
            return $return;
        } else {
            $strDiscountDes = $this->model('cinema')->getCinemaDiscount($iChannelId, $iCinemaId);
            $return['data']['discountDes'] = $strDiscountDes;
        }

        return $return;
    }

    /**
     * 为单个影院添加用户是否收藏这个标识
     *
     * @access protected
     * @param array $arrCinemaInfo 具体的info信息，如：['id'=>'1000103','name'=>'传奇时代影院'...]
     * @param string $strOpenId
     * @return array $arrCinemas 返回影院列表,里边加好了字段:favor
     */
    protected function addFavorToCinemaInfo($iChannelId, array $arrCinemaInfo, $strOpenId = '')
    {
        if (empty($arrCinemaInfo)) {
            return [];
        }
        //获取用户喜欢的影院列表id 拿到影院id组成的数组
        $arrFavCinemaIdsRes = $this->getFavCinemaIds(['openId' => $strOpenId, 'channelId' => $iChannelId]);
        $arrFavCinemaIds = $arrFavCinemaIdsRes['data'];
        $intFavorStatus = 0;
        $iCinemaId = isset($arrCinemaInfo['id']) ? $arrCinemaInfo['id'] : (isset($arrCinemaInfo['cinemaNo']) ? $arrCinemaInfo['cinemaNo'] : '');
        if (in_array($iCinemaId, $arrFavCinemaIds)) {
            $intFavorStatus = 1;
        }
        if (in_array($iChannelId, ["8", "9"])) {
            $arrCinemaInfo['favourite'] = $intFavorStatus;
        } else {
            $arrCinemaInfo['favor'] = $intFavorStatus;
        }

        return $arrCinemaInfo;
    }

    /**
     * 为影院列表,添加当前用户是否对某个影院做了收藏标识
     *
     * @param array $arrCinemas 影院的列表
     * @param string $openId 用户openId
     * @return array $arrCinemas 返回影院列表,里边加好了字段:favor
     */
    protected function addFavorToCinemaList($iChannelId, array $arrCinemas, $strOpenId = '')
    {
        if (empty($arrCinemas)) {
            return [];
        }
        //获取用户喜欢的影院列表id 拿到影院id组成的数组
        $arrFavCinemaIdsRes = $this->getFavCinemaIds(['openId' => $strOpenId, 'channelId' => $iChannelId]);
        $arrFavCinemaIds = $arrFavCinemaIdsRes['data'];
        foreach ($arrCinemas as &$cinemaInfo) {
            $intFavorStatus = 0;
            if (in_array($cinemaInfo['id'], $arrFavCinemaIds)) {
                $intFavorStatus = 1;
            }

            if (in_array($iChannelId, ["8", "9"])) {
                $cinemaInfo['favourite'] = $intFavorStatus;
            } else {
                $cinemaInfo['favor'] = $intFavorStatus;
            }
        }
        return $arrCinemas;
    }

    /**
     * 获取用户收藏的影院Id列表
     * @note 该方法目前主要用户内部调用，如需外部调用，只需将protected改为public即可，protected只是标识没有外部调用的情况而已
     *
     * @param string openId     用户openId
     * @param string channelId  渠道编号
     * @return array ['ret'=>'0','sub'=>'0','msg'=>'success','data'=>['1000103','1012406']]
     */
    public function getFavCinemaIds(array $arrInput = [])
    {
        //参数整理
        $strOpenId = self::getParam($arrInput, 'openId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        $return = self::getStOut();
        //获取用户喜欢的影院列表id 拿到影院id组成的数组
        $getRes = $this->service('favorite')->getFavoriteCinema(['channelId' => $iChannelId, 'openId' => $strOpenId]);
        $arrFavCinemaIds = (is_array($getRes['data']['cinemaList']) && !empty($getRes['data']['cinemaList'])) ?
            $getRes['data']['cinemaList'] : [];
        $return['data'] = $arrFavCinemaIds;

        return $return;
    }

    /**
     * 根据影片id，格式化影院列表，只有这个影院的优惠中，包含这个影片的时候，优惠文案才会显示
     * 如果优惠标签大于三个则合并为一个多优惠标签
     * @param array $arrCinemas 影院列表
     * @param string $movieId 影片id
     */
    private function _formatCinemaMovieDiscount(array &$arrCinemas, $movieId = 0, $iChannelId)
    {
        foreach ($arrCinemas as $Cinemakey => &$CinemaValue) {
            //以下逻辑为动态计算影院列表页价格
            $strJson = $this->model('sche')->readCinemaSchePrice($iChannelId, $CinemaValue['id']);
            if ($strJson) {
                $arrData = json_decode($strJson, true);
                $strJson = null;
            } else {
                $arrData = [];
            }
            $price = [];
            $today_left_sche_count = 0;
            foreach ($arrData as $MovieschePrice) {
                foreach ($MovieschePrice as $sche => $schePrice) {
                    $arrScheTime = explode('|', $sche);
                    $timestamp = strtotime($arrScheTime[0] . " " . $arrScheTime[1]);
                    if (time() < $timestamp) {
                        $price[] = $schePrice;
                    }

                    if ($timestamp >= time() AND $timestamp < strtotime("Tomorrow")) {
                        $today_left_sche_count++;
                    }
                }
            }
            $arrData = null;
            if ($price) {
                $CinemaValue['cinema_ticket_max_price'] = max($price);
                $CinemaValue['cinema_ticket_min_price'] = min($price);
                $CinemaValue['cinema_group_max_price'] = 0;
                $CinemaValue['cinema_group_min_price'] = 0;
                $CinemaValue['today_left_sche_count'] = $today_left_sche_count;
            } else {
                $CinemaValue['cinema_ticket_max_price'] = 0;
                $CinemaValue['cinema_ticket_min_price'] = 0;
                $CinemaValue['cinema_group_max_price'] = 0;
                $CinemaValue['cinema_group_min_price'] = 0;
                $CinemaValue['today_left_sche_count'] = $today_left_sche_count;
            }
            //以下逻辑为优惠标签合并处理用
            if ($CinemaValue['discount_all']) {
                //过滤非本电影的标签
                if ($movieId != 0) {
                    foreach ($CinemaValue['discount_all'] as $key => $value) {
                        if (!in_array($movieId, $value['movies'])) {
                            unset($CinemaValue['discount_all'][$key]);
                        }
                    }
                    $CinemaValue['discount_all'] = array_values($CinemaValue['discount_all']);
                }
                //合并3个以上的标签
                if (count($CinemaValue['discount_all']) >= 3) {
                    $item = [];
                    $item['id'] = "0";
                    $item['des'] = "多优惠";
                    $item['type'] = "0";
                    $item['movies'] = [];
                    if ($movieId != 0) {
                        $item['movies'] = ["{$movieId}"];
                    }
                    $CinemaValue['discount_all'] = [];
                    $CinemaValue['discount_all'][] = $item;
                }

            }
        }
    }

    /**
     * 获取格瓦拉影院id
     * @param array $arrInput
     * @return int
     */
    public function GetGewalaCinemaId(array $arrInput = [])
    {
        $cinemaId = self::getParam($arrInput, 'cinemaId');
        $gewaraCinemaIds = include CGI_APP_PATH . 'app/config/GewaraCinema.php';
        return isset($gewaraCinemaIds[$cinemaId]) ? $gewaraCinemaIds[$cinemaId] : $cinemaId;
    }

    /**
     * 根据id获取是否支持可定义票纸
     * @param array $arrInput
     * @return int
     */
    public function IsGewalaPaper(array $arrInput = [])
    {
        $cinemaId = self::getParam($arrInput, 'cinemaId');
        $staticGewaraObj = new staticGewara('GewaraCinemaPaper');
        return $staticGewaraObj->gewaraInArray($cinemaId) ? 1 : 0;
    }

    /**
     * 影片维度影院搜索排序接口
     * @param array $arrInput
     * @return array|bool|mixed
     */
    public function GewaraSearchCinemasmovSort(array $arrInput = [])
    {
        //参数整理
        $movieid = self::getParam($arrInput, 'movieId');
        $city = self::getParam($arrInput, 'city');
        $tpid = self::getParam($arrInput, 'channelId');
        $return = self::getStOut();
        if (empty($movieid)) {
            return $return;
        }
        //获取影院详情
        $httpParams = [
            'arrData' => compact('movieid','city','tpid'),
            'sMethod' => 'POST',
        ];
        $response = $this->http(JAVA_ES_SEARCH_CINEMASMOV, $httpParams);
        if ($return['ret']==0) {
            return $return['data'] = $response['data'];
        }
        return $return;
    }

    /**
     * 获取全量格瓦拉影院id
     * @param array $arrInput
     * @return int
     */
    public function GetGewalaCinemaIdAll(array $arrInput = [])
    {
        $gewaraCinemaIds = include CGI_APP_PATH . 'app/config/GewaraCinema.php';
        return $gewaraCinemaIds ? $gewaraCinemaIds : new \stdClass();
    }

    /**
     * App获取全部可退票的影院
     * @author CHAIYUE
     * @param array $arrInput
     * @return mixed
     */
    public function getAppRefundCinema(array $arrInput = [])
    {
        $channelId = self::getParam($arrInput, 'channelId');
        return $this->model('Resource')->getAppRefundCinema($channelId);
    }
}