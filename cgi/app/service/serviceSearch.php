<?php

namespace sdkService\service;

class serviceSearch extends serviceBase
{
    const MINIMUN_SHOULD_MATCH = '60%';

    private $km_enabled = false;
    private $cost_time_redis_cinema = 'N/A';

    private $count_cinema = 'N/A'; //影院
    private $count_show = 'N/A'; //演出票
    private $count_movie = 'N/A'; //影片


    /**
     * 影片综合搜索--苏利军接口
     * @param array $arrInput
     * @return array
     */
    public function searchMore(array $arrInput = [])
    {
        $return = self::getStOut();
        $param = [];
        $param['keys'] = self::getParam($arrInput, 'keys'); //关键字
        $param['city'] = self::getParam($arrInput, 'city'); //城市
        $param['lon'] = self::getParam($arrInput, 'lon'); //经度
        $param['lat'] = self::getParam($arrInput, 'lat'); //纬度
        $param['tpid'] = self::getParam($arrInput, 'channelId');
        $param['pindex'] = self::getParam($arrInput, 'pindex');//当前页数
        $param['pcount'] = self::getParam($arrInput, 'pcount');//每页条数
        $param['more'] = self::getParam($arrInput, 'more');//查询的分类

        $http = []; //http请求信息
        $http['sMethod'] = 'POST';
        $http['iTryTimes'] = 5;
        $http['sendType'] = 'form';
        $http['arrData'] = $param;
        $response = $this->http(JAVA_ES_SEARCH_MORE, $http, false);

        if (!empty($response['data'])) {
            //判断影院设施
            array_walk($response['data']['cinemas'], function (&$cinema) {
                $cinema['special'] = isset($cinema['special']) ? (object)$cinema['special'] : new \stdClass();
            });
            $data = $response['data'];

        //综合搜索添加资讯 获取资讯的真实阅读数

//            $read = 0;
//            if(!empty($aid))
//                $read = $this->model('Cms')->getCmsReadNum($aid);
//            return $read;



        } else {
            $data = new \stdClass();
        }
        $return['data'] = $data;
        $return['extra'] = !empty($response['extra']) ? $response['extra'] : new  \stdClass();
        return $return;
    }

    /**
     * @param array $arrInput
     * @param keyword 关键字
     * @param city 城市
     * @param longitude 经度
     * @param latitude 纬度
     * @param chain 院线
     * @param brand 品牌
     * @param feature 特色
     * @param district
     * @param page 页码数
     * @param perpage 返回的最大记录数
     * @param extra 是否查询extra_info信息
     * @param openId 用户ID
     * @param channelId 渠道ID
     * @return array
     */
    public function searchCinema(array $arrInput = [])
    {
        $return = self::getStOut();
        $param = [];
        $param['keyword'] = self::getParam($arrInput, 'keyword'); //关键字
        $param['city'] = self::getParam($arrInput, 'city'); //城市
        $param['longitude'] = self::getParam($arrInput, 'longitude'); //经度
        $param['latitude'] = self::getParam($arrInput, 'latitude'); //纬度
        $param['chain'] = self::getParam($arrInput, 'chain');
        $param['brand'] = self::getParam($arrInput, 'brand');
        $param['feature'] = self::getParam($arrInput, 'feature');
        $param['district'] = self::getParam($arrInput, 'district');
        $param['page'] = self::getParam($arrInput, 'page', 1);
        $param['perpage'] = self::getParam($arrInput, 'perpage', 50);
        $param['extra'] = self::getParam($arrInput, 'extra');
        $param['openId'] = self::getParam($arrInput, 'openId');
        $param['channelId'] = self::getParam($arrInput, 'channelId');

        $http = []; //http请求信息
        $http['sMethod'] = 'POST';
        $http['iTryTimes'] = 1;
        $http['sendType'] = 'json';
        $http['arrData'] = $this->formatPostData($param); //格式化请求数据

        $response = $this->http(JAVA_ADDR_BIG_DATA_SEARCH . '/cinema/_search', $http, false);

        $this->formatResponseData($response, $param, $return); //格式化返回结果
        return $return;
    }

    /**
     * 搜索影片信息--给commoncgi使用的
     * @param array $arrInput
     * @param int channelId 渠道id，【必须]
     * @param int page 页数，【必须]，默认从第一页开始
     * @param int num 每页条数【必须]，每页条数，默认为20条
     * @param int movieInfo 是否需要影片信息，1：需要，0：不需要。【默认为0】
     * @param int actorInfo 是否需要影人信息，1：需要，0：不需要，【默认为0】，本字段只有在movieInfo=1的时候生效
     * @param string keyWord 关键词，【必须]
     * 注：此版本是最新的，从媒资库迁移过来的
     */
    public function movieSearch(array $arrInput = [])
    {
        $arrReturn = self::getStOut();
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iPage = intval(self::getParam($arrInput, 'page'));
        $iNum = intval(self::getParam($arrInput, 'num'));
        $iMovieInfo = intval(self::getParam($arrInput, 'movieInfo'));
        $iActorInfo = intval(self::getParam($arrInput, 'actorInfo'));
        $strKeyWord = self::getParam($arrInput, 'keyWord');
        $iPage = $iPage < 1 ? 1 : $iPage;
        $iNum = $iNum < 1 ? 10 : $iNum;
        if (empty($iChannelId) || empty($strKeyWord)) {
            return self::getErrorOut(ERRORCODE_MSDB_PARAM_ERROR);
        }
        $arrData = self::_getSearchMovieId($strKeyWord, $iPage, $iNum);//ES搜索
        if (!empty($iMovieInfo) && !empty($arrData['data'])) {//获取内容
            $arrMovieId = $arrData['data'];
            $arrData['data'] = $this->model('msdb')->getMovieInfoMore($iChannelId, $arrData['data']);//获取影片信息
            if (!empty($iActorInfo) && !empty($arrData['data'])) {
                $objActorData = $this->model('msdb')->getMovieActorMore($iChannelId, $arrMovieId);//读取影人信息
                foreach ($arrData['data'] as $key => &$val) {//循环匹配数据
                    $val->actorInfo = !empty($objActorData[$key]) ? $objActorData[$key] : [];
                }
            }
        }
        $arrReturn['data'] = $arrData;
        return $arrReturn;
    }

    /**
     * 搜索影片
     * @param $strKeyWord
     * @param $iPage
     * @param $iNum
     * @return array|bool|mixed|Ambigous
     */
    private function _getSearchMovieId($strKeyWord, $iPage, $iNum)
    {
//        $arrSearchJson = self::_getSearchJson($strKeyWord,$iPage,$iNum);//已废弃
        $params['sMethod'] = 'post';
        //$params['sendType'] = 'json';
        $params['arrData'] = ['movieName' => $strKeyWord, 'page' => $iPage, 'pcount' => $iNum];
        $arrData = $this->http(JAVA_ES_SEARCH_DATABASES_MOVIE, $params, false);
        //@todo 记录日志
        if (empty($arrData['ret']) && !empty($arrData['data'])) {
            $arrData = $arrData['data'];
            $arrData = self::_getSearchJsonMovieId($arrData);//格式化
        } else {
            $arrData = ['movies' => [], 'total' => 0];
        }
        return $arrData;
    }


    /**
     * @tutorial 读取搜索接口返回的json内的数据::格式化数据 -- 影片搜索
     * @param unknown $arrData
     * @return Ambigous <multitype:, number>
     */
    private static function _getSearchJsonMovieId($arrData)
    {
//        echo json_encode($arrData['movies']);exit;
        $arrResData['total'] = !empty($arrData['total']) ? $arrData['total'] : 0;
        if (!empty($arrData['movies']))
            foreach ($arrData['movies'] as $movie) {
                $movie = (array)$movie;
                $arrResData['data'][] = $movie['movie_id'];
            }
        else $arrResData['data'] = [];
        return $arrResData;
    }

    /**
     * @tutorial 获取要post的json-影片搜索  已废弃
     * @param unknown $keyWord
     * @param number $iStart
     * @param unknown $iEnd
     */
//    private static function _getSearchJson($strKeyWord,$iPage,$iNum)
//    {
//        $arrSearch = [
//            //"min_score"=> 1.0001,   //最小得分
//            "query"=> [
//                "multi_match"=> [
//                    "query"=> $strKeyWord,                #查询关键字,支持拼音，中文，繁体
//                    "type"=> "best_fields",
//                    "minimum_should_match"=>"40%",    //最小匹配度
//                    "tie_breaker"=>0.3,        //
//                    "fields"=> [                      #查询字段
//                        "movie_name_eng^1.5",
//                        "movie_name_chs^2.5",
//                        "movie_name_chs.pinyin^1",
//                        "movie_name_chs.fanti"
//                    ]
//                ]
//            ],
//            "fields"=> [           #返回字段
//                "movie_id"
//            ],
//            "from"=>$iPage * $iNum,                  #分页
//            "size"=>$iNum
//        ];
//        return $arrSearch;
//    }

    /**
     * 格式化请求搜索引擎数据
     * @param $param
     * @return array
     */
    private function formatPostData($param)
    {
        $cinema_post_data_array = [
            'fields' => [
                'CinemaNo',
                'CinemaName',
                'Address',
                'AreaName',
                'CityName'
            ],
            'highlight' => [
                'fields' => [
                    'CinemaName' => [
                        'CinemaName' => ''
                    ]
                ]
            ],
            'min_score' => 3.001,
            'query' => [
                'multi_match' => [
                    'query' => $param['keyword'],
                    'type' => 'best_fields',
                    'minimum_should_match' => BIG_DATA_SEARCH_MATCH,
                    "tie_breaker" => BIG_DATA_SEARCH_TIE_BREAKER,
                    'fields' => [
                        'CinemaName^2',
                        'CinemaName.pinyin',
                        'CinemaName.fanti',
                        'Address.pinyin',
                        'Address.fanti',
                        'Address'
                    ]
                ]],
        ];

        if (!empty($param['city'])) { //城市
            $cinema_post_data_array['filter']['and'][]['term'] = array(
                "CityName" => $param['city']
            );
        }

        if (!empty($param['district'])) { //区县
            $cinema_post_data_array['filter']['and'][]['term'] = array(
                "AreaName" => $param['district']
            );
        }

        if ($param['latitude'] !== '0' && $param['longitude'] !== '0') { //提供了经纬度的话，增加距离排序
            $this->km_enabled = true;
            $cinema_post_data_array['sort'] = array(
                0 => array(
                    '_geo_distance' => array(
                        'Coordinate' => array(
                            'lat' => (string)$param['latitude'],
                            'lon' => (string)$param['longitude']
                        ),
                        'order' => 'asc',
                        'unit' => 'km'
                    )
                ),
                1 => '_score',
            );
        }

        $cinema_post_data_array['size'] = $param['perpage'];
        $cinema_post_data_array['from'] = ($param['page'] - 1) * $param['perpage'];
        return $cinema_post_data_array;
    }

    /**
     * @param $return_array
     * @param $param
     * @param $return ret|sub|msg|data
     * @return array
     */
    private function formatResponseData($return_array, $param, &$return)
    {
        $this->count_cinema = 0;
        // 正常返回的情况下
        if (isset($return_array['took']) && $return_array['took'] > 0) {
            $this->count_cinema = $return_array['hits']['total'];

            $return['data']['msg'] = array(
                'total' => $return_array['hits']['total'],
                'page' => $param['page'],
                'perpage' => $param['perpage'],
                'timeout' => $return_array['timed_out'],
                'took' => intval($return_array['took'])
            );

            if (empty($return_array['hits']['hits'])) {
                return;
            }

            $cinema_id_array = array(); //影院id集合，应用于获取影院信息

            if (isset($param['hl']) && $param['hl'] === 1) { //是否高亮
                foreach ($return_array['hits']['hits'] as $key => $value) {
                    $distance = 'N/A';
                    if ($this->km_enabled) {
                        $distance = number_format($value['sort'][0], 3, '.', '');
                    }
                    $cinema_id_array[] = $value['fields']['CinemaNo'][0];

                    $return['data']['cinemas'][] = array(
                        'cinema_id' => $value['fields']['CinemaNo'][0],
                        'cinema_name' => isset($value['highlight']['CinemaName'][0]) ? $value['highlight']['CinemaName'][0] : $value['fields']['CinemaName'][0],
                        'address' => $value['fields']['Address'][0],
                        'distance' => $distance,
                        'area_name' => $value['fields']['AreaName'][0],
                        'city_name' => $value['fields']['CityName'][0],
                        'extra_info' => array(
                            'flag_seat_status' => 'N/A',
                            'flag_seat_ticket' => 'N/A',
                            'flag_groupon' => 'N/A',
                            'flag_elec_ticket' => 'N/A',
                            'discount_des' => 'N/A',
                        )
                    );
                }
            } else {
                foreach ($return_array['hits']['hits'] as $key => $value) {
                    $distance = 'N/A';
                    if ($this->km_enabled) {
                        $distance = number_format($value['sort'][0], 3, '.', '');
                    }
                    $cinema_id_array[] = $value['fields']['CinemaNo'][0];

                    $return['data']['cinemas'][] = array(
                        'cinema_id' => $value['fields']['CinemaNo'][0],
                        'cinema_name' => $value['fields']['CinemaName'][0],
                        'address' => $value['fields']['Address'][0],
                        'distance' => $distance,
                        'area_name' => $value['fields']['AreaName'][0],
                        'city_name' => $value['fields']['CityName'][0],
                        'extra_info' => array(
                            'flag_seat_status' => 'N/A',
                            'flag_seat_ticket' => 'N/A',
                            'flag_groupon' => 'N/A',
                            'flag_elec_ticket' => 'N/A',
                            'discount_des' => 'N/A',
                        )
                    );
                }
            }

            // extra=0 可以禁止掉访问获得优惠信息等
            if ($param['extra'] === 1) {
                $strOpenId = $param['openId'];
                $strOpenId = !empty($strOpenId) ? $strOpenId : '';
                $extra_info = $this->service('cinema')->getSearchCinemaInfo(['cinemaIds' => implode('|', $cinema_id_array), 'openId' => $strOpenId, 'channelId' => $param['channelId']]);

                $this->cost_time_redis_cinema = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
                if (!empty($extra_info)) {
                    foreach ($return['data']['cinemas'] as $key => $value) {
                        if (isset($extra_info[($value['cinema_id'])])) {
                            if (isset($extra_info[($value['cinema_id'])]['flag_seat_status'])) {
                                $return['data']['cinemas'][$key]['extra_info']['flag_seat_status'] = $extra_info[($value['cinema_id'])]['flag_seat_status'];
                            }
                            if (isset($extra_info[($value['cinema_id'])]['flag_seat_ticket'])) {
                                $return['data']['cinemas'][$key]['extra_info']['flag_seat_ticket'] = $extra_info[($value['cinema_id'])]['flag_seat_ticket'];
                            }
                            if (isset($extra_info[($value['cinema_id'])]['flag_groupon'])) {
                                $return['data']['cinemas'][$key]['extra_info']['flag_groupon'] = $extra_info[($value['cinema_id'])]['flag_groupon'];
                            }
                            if (isset($extra_info[($value['cinema_id'])]['flag_elec_ticket'])) {
                                $return['data']['cinemas'][$key]['extra_info']['flag_elec_ticket'] = $extra_info[($value['cinema_id'])]['flag_elec_ticket'];
                            }
                            if (isset($extra_info[($value['cinema_id'])]['discount_des'])) {
                                $return['data']['cinemas'][$key]['extra_info']['discount_des'] = $extra_info[($value['cinema_id'])]['discount_des'];
                            }
                            if (isset($extra_info[($value['cinema_id'])]['favor'])) {
                                $return['data']['cinemas'][$key]['extra_info']['favor'] = $extra_info[($value['cinema_id'])]['favor'];
                            }
                            if (isset($extra_info[($value['cinema_id'])]['refund'])) {
                                $return['data']['cinemas'][$key]['extra_info']['refund'] = $extra_info[($value['cinema_id'])]['refund'];
                            }
                            if (isset($extra_info[($value['cinema_id'])]['snack_status'])) {
                                $return['data']['cinemas'][$key]['extra_info']['snack_status'] = $extra_info[($value['cinema_id'])]['snack_status'];
                            }
                        }
                    }
                }
            }
        }

        // ES错误的情况下
        if (isset($return_array['status'])) {
            $return['ret'] = -1;
            $return['sub'] = __LINE__;
            $return['msg'] = 'search system error.';
        }

        // 网络错误的情况下
        if (isset($return_array['ret'])) { //正常的返回值，没有ret
            $return['ret'] = $return_array['ret'];
            $return['sub'] = __LINE__;
            $return['msg'] = $return_array['msg'];
        }
    }

    /**
     * 搜索影片接口 - app使用的
     * @param array $arrInput
     */
    public function searchMovie(array $arrInput = [])
    {
        $keyWord = self::getParam($arrInput, 'keyWord');
        $page = self::getParam($arrInput, 'page', 1);
        $num = self::getParam($arrInput, 'num', 20);
        $movieInfo = self::getParam($arrInput, 'movieInfo', 1);
        $actorInfo = self::getParam($arrInput, 'actorInfo', 0);
        $cityId = self::getParam($arrInput, 'cityId', 10);
        $channelId = self::getParam($arrInput, 'channelId');
        $return = self::getStOut();
        if (!empty($keyWord)) {
//            $params = [];
//            $params['sMethod'] = 'post';
//            $params['sendType'] = 'form';
            $params = [
                'keyWord' => $keyWord,
                'page' => $page,
                'num' => $num,
                'movieInfo' => $movieInfo,
                'actorInfo' => $actorInfo,
                'channelId' => self::getParam($arrInput, 'channelId'),
            ];

            $data = $this->movieSearch($params);
            if ($data['ret'] == 0 && $data['ret'] == 0) {
                $arrSearchRet = $data['data'];
            }
            //格式化影片评分、字段等信息
            self::__FormatSearchResult($arrSearchRet['data'], $channelId, $cityId);
            $arrSearchRet['data'] = array_values($arrSearchRet['data']);
            $return['data'] = $arrSearchRet;
        }
        return $return;
    }

    /**格式化数据
     * @param $arrSearchRet
     * @param $channelId
     */
    private function __FormatSearchResult(&$arrSearchRet, $channelId, $cityId = '10')
    {
        if (count($arrSearchRet) > 0) {
//            $movieIds = array_keys($arrSearchRet);
            //获取影片评分等信息
//            $arrMovieDynamicData = $this->model("Movie")->readMovieInfos($channelId,$movieIds);
            foreach ($arrSearchRet as $key => &$value) {
                $value = (array)$value;
                self::__FormatSearchParam($value);//格式化字段、剧照等信息
                if (!empty($value['om_movie_id'])) {
                    $value['is_new_msdb'] = 0;
                    $value['buy_flag'] = $this->model("Movie")->getScheFlagOfMovieCity($channelId, $value['id'], $cityId);
                } else {
                    $value['is_new_msdb'] = 1;
                    $value['buy_flag'] = 0;
                }
//                if(isset($arrMovieDynamicData[$key])){
//                    if(isset($value['actorInfo']))
//                        $arrMovieDynamicData[$key]['actorInfo'] = $value['actorInfo'];
//                    $value = $arrMovieDynamicData[$key];
//                    $value['is_new_msdb'] = 0;
//                }else{
//                    self::__FormatSearchParam($value);//格式化字段、剧照等信息
//                    $value['is_new_msdb'] = 1;
//                }
                //echo json_encode($value);exit;
                //self::__FormatSearchParam($value);//格式化字段、剧照等信息
                //$value = array_merge($value, $arrMovieDynamicData[$key]);
            }
        }
    }

    //格式化基础数据（字段）
    private static function __FormatSearchParam(&$arrData)
    {
        $arrData = (array)$arrData;
        $arrParam = [
            'MovieNo' => 'id',             //影片ID
            'MovieNameChs' => 'name',          //影片中文名称
            'Director' => 'director',      //导演
            'Starring' => 'actor',         //主演
            'CoverId' => 'coverid',       //视频组ID
            'FirstTime' => 'date',          //首映时间
            'Contry' => 'country',       //出品国家
            //    'InShortRemark'  => 'remark',        //一句话点评
            'MovieType' => 'tags',             //标签
            'Version' => 'version',       //版本
            'Duration' => 'longs',         //时常
            'InShortRemark' => 'simple_remarks',//一句话点评（怎么俩？）
            'MovieDetail' => 'detail',        //详细介绍
            'MovieNameEng' => 'en_name',
        ];
        foreach ($arrParam as $key => $val) {
            $arrData[$val] = !empty($arrData[$key]) ? $arrData[$key] : '';
            if (isset($arrData[$key]))
                unset($arrData[$key]);
        }
        $arrData['date'] = $arrData['date'] ? date('Ymd', $arrData['date']) : '';
        $arrData['remark'] = $arrData['simple_remarks'];//重复性增加一个一句话点评
        if (isset($arrData['IMG_COVER'][0]))
            $arrData['IMG_COVER'][0] = (array)$arrData['IMG_COVER'][0];
        if (isset($arrData['IMG_BACKGROUND'][0]))
            $arrData['IMG_BACKGROUND'][0] = (array)$arrData['IMG_BACKGROUND'][0];
        $arrData['poster_url'] = isset($arrData['IMG_COVER'][0]['180X252']) ? $arrData['IMG_COVER'][0]['180X252'] : '';
        $arrData['poster_url_size3'] = isset($arrData['IMG_COVER'][0]['350X490']) ? $arrData['IMG_COVER'][0]['350X490'] : '';
        $arrData['poster_url_size21'] = isset($arrData['IMG_BACKGROUND'][0]['800X560']) ? $arrData['IMG_BACKGROUND'][0]['800X560'] : '';
        $arrData['poster_url_size22'] = isset($arrData['poster_url_size22']) ? $arrData['poster_url_size22'] : '';
        $arrData['poster_url_size23'] = isset($arrData['poster_url_size23']) ? $arrData['poster_url_size23'] : '';
        $arrData['initscore'] = 0;
        $arrData['will_flag'] = 0;
//        $arrData['wantCount'] = 0;
//        $arrData['seenCount'] = 0;
        $arrData['initScore'] = 0;
        $arrData['date_des'] = $arrData['date'] ? date('Y年m月d日', strtotime($arrData['date'])) : '';
        self::__FormatSearchStill($arrData);//剧照
    }

    /**
     * 剧照
     * @param $arrData
     */
    private static function __FormatSearchStill(&$arrData)
    {
        if (!empty($arrData['IMG_STILL'])) {
            foreach ($arrData['IMG_STILL'] as $val) {
                $val = (array)$val;
                $arrStill = ['original_url' => '', 'display_url' => '', 'thumbnail_url' => ''];
                if (!empty($val['ORIGIN']))
                    $arrStill['original_url'] = $val['ORIGIN'];
                if (!empty($val['300X300']))
                    $arrStill['display_url'] = $val['300X300'];
                if (!empty($val['100X100']))
                    $arrStill['thumbnail_url'] = $val['100X100'];
                $arrData['still_list'][] = $arrStill;
            }
        } else {
            $arrData['still_list'] = [];
        }
        self::_UnsetMovieParam($arrData);
    }

    //格式化数据：删除不必要的字段
    private static function _UnsetMovieParam(&$arrData)
    {
        $arrParam = ['IMG_POSTER',
            'IMG_COVER',
            'IMG_BACKGROUND',
            'Language',
            'MovieCodes',
            'FirstShow',
            'MovieNameInitPinyin',
            'MovieNamePinyin',
            'Score',
            'IMG_STILL',
            'IMG_NEWS',
            'IMG_OTHER',
        ];
        foreach ($arrParam as $val) {
            if (isset($arrData[$val]))
                unset($arrData[$val]);
        }
    }

    /**
     * 从大数据的ES系统，获取影院搜索列表
     * 注意：这个只是获取影院列表
     *
     * @param string Y  cityId     城市id，如：10 表示北京
     * @param string N  areaId     区域id，如：1011 表示朝阳
     * @param string Y  longitude  经度
     * @param string Y  latitude   维度
     * @param string Y  sortField  排序字段，如：distance、min_price
     * @param string Y  channelId  渠道id
     * @param string Y  pageNum    页码
     * @param string Y  pageSize   单页条目数
     * @param string N  order      排序条件，如：asc、desc
     * @param string N  brand      影院品牌
     * @param string N  special    影厅特色，传特色
     * @param int    N  card       会员卡id,如果传入此参数,表示要查询支持这个会员卡的影院列表
     * @param string N  openId     用户openId
     *
     * @return array
     */
    public function searchCinemaListFromEs(array $arrInput = [])
    {
        $return = self::getStOut();
        //拼装参数
        $arrParams = [];
        $iChannelId = self::getParam($arrInput, 'channelId');
        $arrParams['area'] = self::getParam($arrInput, 'areaId', null); //区域id
        $arrParams['city'] = self::getParam($arrInput, 'cityId'); //城市id
        $arrParams['lon'] = self::getParam($arrInput, 'longitude', 999); //经度
        $arrParams['lat'] = self::getParam($arrInput, 'latitude', 999); //纬度
        $arrParams['field'] = self::getParam($arrInput, 'sortField', 'distance'); //关键字
        $arrParams['order'] = self::getParam($arrInput, 'order', 'asc');
        $arrParams['page'] = self::getParam($arrInput, 'pageNum', 1);
        $arrParams['pcount'] = self::getParam($arrInput, 'pageSize', 10);
        $arrParams['tpid'] = $iChannelId;
        $arrParams['brand'] = self::getParam($arrInput, 'brand', null);
        $arrParams['special'] = self::getParam($arrInput, 'special', null);
        $arrParams['label'] = self::getParam($arrInput, 'label', null);
        $arrParams['card'] = self::getParam($arrInput, 'card', null);
        //取cookie的一些参数(其实这个传过去他们也不用,是其他人要分析他们的日志)
        $arrParams['sid'] = self::getParam($_COOKIE, 'sid');
        $arrParams['bid'] = self::getParam($_COOKIE, 'bid');
        //openid优先从$arrInput取,没有的话,再从Cookie取,后续,都得从$arrInput取
        $strOpenId = self::getParam($arrInput, 'openId');
        if (empty($strOpenId)) {
            $strOpenId = self::getParam($_COOKIE, 'openid');
        }
        $arrParams['openid'] = $strOpenId;
        //参数校验
        if (empty($arrParams['tpid']) || empty($arrParams['city'])) {
            return $return;
        }
        //获取用户收藏的影院id
        $strUserFavoriteCinemaIds = '';
        if (!empty($strOpenId)) {
            $arrFavoriteCinemaIdsRes = $this->service('Favorite')->getFavoriteCinema(['channelId' => $iChannelId, 'openId' => $strOpenId]);
            $strUserFavoriteCinemaIds = (isset($arrFavoriteCinemaIdsRes['ret']) && ($arrFavoriteCinemaIdsRes['ret'] == 0) && !empty($arrFavoriteCinemaIdsRes['data']['cinemaList'])) ? implode(',',
                $arrFavoriteCinemaIdsRes['data']['cinemaList']) : '';
        }
        $arrParams['cnos'] = $strUserFavoriteCinemaIds;
        //值为null的，代表前端没有传，那我们也不能传给大数据（主要因为，我们传了，大数据就走条件查询了，所以，传空和不传，是不一样的）
        foreach ($arrParams as $key => $val) {
            if (is_null($val) || ($val === '')) {
                unset($arrParams[$key]);
            }
        }
        //发送请求
        $http = []; //http请求信息
        $http['sMethod'] = 'POST';
        $http['iTryTimes'] = 2;
        $http['sendType'] = 'form';
        $http['arrData'] = $arrParams; //格式化请求数据
        $response = $this->http(JAVA_API_SEARCH_CINEMA_LIST, $http, false);
        //处理返回结果
        if ((isset($response['ret'])) && ($response['ret'] == 0) && !empty($response['data'])) {
            $return['data'] = $response['data'];
            $return['extra'] = !empty($response['extra']) ? $response['extra'] : [];
            //将discount_dis, 空数组转为空对象
            foreach ($return['data'] as &$cinemaInfo) {
                if (isset($cinemaInfo['card_dis']) && empty($cinemaInfo['card_dis'])) {
                    $cinemaInfo['card_dis'] = new \stdClass();
                }
            }
        }

        return $return;
    }
    
    
    /**
     * 从大数据的ES系统，获取影院搜索列表  新版接口V2   影院维度筛选
     * 注意：这个只是获取影院列表
     *
     * @param string Y  cityId     城市id，如：10 表示北京
     * @param string N  areaId     区域id，如：1011 表示朝阳
     * @param string Y  longitude  经度
     * @param string Y  latitude   维度
     * @param string Y  sortField  排序字段，如：distance、min_price
     * @param string Y  channelId  渠道id
     * @param string Y  pageNum    页码
     * @param string Y  pageSize   单页条目数
     * @param string N  order      排序条件，如：asc、desc
     * @param string N  brand      影院品牌
     * @param string N  special    影厅特色，传特色
     * @param string N  lable      影院标签，key1:val1类型
     * @param int    N  card       会员卡id,如果传入此参数,表示要查询支持这个会员卡的影院列表
     * @param int    N  serv       参数为服务标识，多个服务之间用英文逗号（,）分隔 snack 小吃 refund 可退票 discount 有优惠
                                    如要筛选有小吃、有优惠的影院，输入参数为 "snack,discount"
     * @param float  N  maxPrice     实际售卖价的最小值且小于或者等于maxPrice的价格
     * @param float  N  minPrice     实际售卖价的最小值且大于或者等于minPrice的价格
     * @param string N  openId     用户openId
     *
     * @return array
     */
    public function searchCinemaListFromEsV2(array $arrInput = [])
    {
        $return = self::getStOut();
        //拼装参数
        $arrParams = [];
        $iChannelId = self::getParam($arrInput, 'channelId');
        $arrParams['area'] = self::getParam($arrInput, 'areaId', null); //区域id
        $arrParams['city'] = self::getParam($arrInput, 'cityId'); //城市id
        $arrParams['lon'] = self::getParam($arrInput, 'longitude', 999); //经度
        $arrParams['lat'] = self::getParam($arrInput, 'latitude', 999); //纬度
        $arrParams['field'] = self::getParam($arrInput, 'sortField', 'distance'); //关键字
        $arrParams['order'] = self::getParam($arrInput, 'order', 'asc');
        $arrParams['page'] = self::getParam($arrInput, 'pageNum', 1);
        $arrParams['pcount'] = self::getParam($arrInput, 'pageSize', 10);
        $arrParams['tpid'] = $iChannelId;
        $arrParams['brand'] = self::getParam($arrInput, 'brand', null);
        $arrParams['special'] = self::getParam($arrInput, 'special', null);
        $arrParams['label'] = self::getParam($arrInput, 'label', null);
        $arrParams['card'] = self::getParam($arrInput, 'card', null);
        $arrParams['serv'] = self::getParam($arrInput, 'serv', null);  //与老版不同点 添加影院服务筛选
        //取cookie的一些参数(其实这个传过去他们也不用,是其他人要分析他们的日志)
        //(影院维度 影院搜索  价格区间 前端不传则 不传递此参数)
        if(self::getParam($arrInput, 'minPrice', null)){
            $arrParams['minPrice'] =self::getParam($arrInput, 'minPrice', null);
        }
        if(self::getParam($arrInput, 'maxPrice', null)){
            $arrParams['maxPrice'] =self::getParam($arrInput, 'maxPrice', null);
        }
        $arrParams['sid'] = self::getParam($_COOKIE, 'sid');
        $arrParams['bid'] = self::getParam($_COOKIE, 'bid');
        //openid优先从$arrInput取,没有的话,再从Cookie取,后续,都得从$arrInput取
        $strOpenId = self::getParam($arrInput, 'openId');
        if (empty($strOpenId)) {
            $strOpenId = self::getParam($_COOKIE, 'openid');
        }
        $arrParams['openid'] = $strOpenId;
        //参数校验
        if (empty( $arrParams['tpid'] ) || empty( $arrParams['city'] )) {
            return $return;
        }
        //获取用户收藏的影院id
        $strUserFavoriteCinemaIds = '';
        if ( !empty( $strOpenId )) {
            $arrFavoriteCinemaIdsRes = $this->service('Favorite')->getFavoriteCinema(['channelId' => $iChannelId, 'openId' => $strOpenId]);
            $strUserFavoriteCinemaIds = ( isset( $arrFavoriteCinemaIdsRes['ret'] ) && ( $arrFavoriteCinemaIdsRes['ret'] == 0 ) && !empty( $arrFavoriteCinemaIdsRes['data']['cinemaList'] ) ) ? implode(',',
                $arrFavoriteCinemaIdsRes['data']['cinemaList']) : '';
        }
        $arrParams['cnos'] = $strUserFavoriteCinemaIds;
        //值为null的，代表前端没有传，那我们也不能传给大数据（主要因为，我们传了，大数据就走条件查询了，所以，传空和不传，是不一样的）
        foreach ($arrParams as $key => $val) {
            if (is_null ($val) || ( $val === '' )) {
                unset( $arrParams[$key] );
            }
        }
        //发送请求
        $http = []; //http请求信息
        $http['sMethod'] = 'POST';
        $http['iTryTimes'] = 2;
        $http['sendType'] = 'form';
        $http['arrData'] = $arrParams; //格式化请求数据
        $response = $this->http(JAVA_API_SEARCH_CINEMA_LIST_V2, $http, false);
        //处理返回结果
        if (( isset( $response['ret'] ) ) && ( $response['ret'] == 0 ) && !empty( $response['data'] )) {
            $return['data'] = $response['data'];
            $return['extra'] = !empty( $response['extra'] ) ? $response['extra'] : [];
            //将discount_dis, 空数组转为空对象
            foreach ($return['data'] as &$cinemaInfo) {
                if (isset($cinemaInfo['card_dis']) && empty($cinemaInfo['card_dis'])) {
                    $cinemaInfo['card_dis'] = new \stdClass();
                }
            }
        }

        return $return;
    }

    /**
     * 从大数据的ES系统，获取影院搜索列表
     * 注意：这个是获取某个影片的影院列表
     *
     * @param string Y  cityId     城市id，如：10 表示北京
     * @param string N  areaId     区域id，如：1011 表示朝阳
     * @param string Y  longitude  经度
     * @param string Y  latitude   维度
     * @param string Y  sortField  排序字段，如：distance、min_price
     * @param string Y  channelId  渠道id
     * @param string Y  pageNum    页码
     * @param string Y  pageSize   单页条目数
     * @param string N  order      排序条件，如：asc、desc
     * @param string N  brand      影院品牌
     * @param string N  special    影厅特色，传特色
     * @param string N  openId     用户openId
     * @param string N  recent     上映时间段，值可以为：0、6、12、18
     * @param string Y  movieId    影片id
     * @param string N  date       日期，也就是获取这个影片哪一天的在映的影片列表，默认今天
     *
     * @return array
     */
    public function searchMovieCinemaListFromEs(array $arrInput = [])
    {
        $return = self::getStOut();
        //拼装参数
        $arrParams = [];
        $iChannelId = self::getParam($arrInput, 'channelId');
        $arrParams['area'] = self::getParam($arrInput, 'areaId', null); //区域id
        $arrParams['city'] = self::getParam($arrInput, 'cityId'); //城市id
        $arrParams['lon'] = self::getParam($arrInput, 'longitude', 999); //经度
        $arrParams['lat'] = self::getParam($arrInput, 'latitude', 999); //纬度
        $arrParams['field'] = self::getParam($arrInput, 'sortField', 'distance'); //关键字
        $arrParams['order'] = self::getParam($arrInput, 'order', 'asc');
        $arrParams['page'] = self::getParam($arrInput, 'pageNum', 1);
        $arrParams['pcount'] = self::getParam($arrInput, 'pageSize', 10);
        $arrParams['tpid'] = $iChannelId;
        $arrParams['brand'] = self::getParam($arrInput, 'brand', null);
        $arrParams['special'] = self::getParam($arrInput, 'special', null);
        $arrParams['recent'] = self::getParam($arrInput, 'recent', null);
        $arrParams['movieid'] = self::getParam($arrInput, 'movieId', null);
        $arrParams['date'] = self::getParam($arrInput, 'date', date('Y-m-d'));
        //取cookie的一些参数(其实这个传过去他们也不用,是其他人要分析他们的日志)
        $arrParams['sid'] = self::getParam($_COOKIE, 'sid');
        $arrParams['bid'] = self::getParam($_COOKIE, 'bid');
        //openid优先从$arrInput取,没有的话,再从Cookie取,后续,都得从$arrInput取
        $strOpenId = self::getParam($arrInput, 'openId');
        if (empty($strOpenId)) {
            $strOpenId = self::getParam($_COOKIE, 'openid');
        }
        $arrParams['openid'] = $strOpenId;
        //参数校验
        if (empty($arrParams['movieid']) || empty($arrParams['tpid']) || empty($arrParams['city'])) {
            return $return;
        }
        //获取用户收藏的影院id
        $strUserFavoriteCinemaIds = '';
        if (!empty($strOpenId)) {
            $arrFavoriteCinemaIdsRes = $this->service('Favorite')->getFavoriteCinema(['channelId' => $iChannelId, 'openId' => $strOpenId]);
            $strUserFavoriteCinemaIds = (isset($arrFavoriteCinemaIdsRes['ret']) && ($arrFavoriteCinemaIdsRes['ret'] == 0) && !empty($arrFavoriteCinemaIdsRes['data']['cinemaList'])) ? implode(',',
                $arrFavoriteCinemaIdsRes['data']['cinemaList']) : '';
        }
        $arrParams['cnos'] = $strUserFavoriteCinemaIds;
        //值为null的，代表前端没有传，那我们也不能传给大数据（主要因为，我们传了，大数据就走条件查询了，所以，传空和不传，是不一样的）
        foreach ($arrParams as $key => $val) {
            if (is_null($val) || ($val === '')) {
                unset($arrParams[$key]);
            }
        }
        //发送请求
        $http = []; //http请求信息
        $http['sMethod'] = 'POST';
        $http['iTryTimes'] = 2;
        $http['sendType'] = 'form';
        $http['arrData'] = $arrParams; //格式化请求数据
        $response = $this->http(JAVA_API_SEARCH_MOVIE_CINEMA_LIST, $http, false);
        //处理返回结果
        if ((isset($response['ret'])) && ($response['ret'] == 0) && !empty($response['data'])) {
            $return['data'] = $response['data'];
            $return['extra'] = !empty($response['extra']) ? $response['extra'] : [];
            //将discount_dis, 空数组转为空对象
            foreach ($return['data'] as &$cinemaInfo) {
                if (isset($cinemaInfo['card_dis']) && empty($cinemaInfo['card_dis'])) {
                    $cinemaInfo['card_dis'] = new \stdClass();
                }
            }
        }

        return $return;
    }
    
    
    /**
     * 从大数据的ES系统，获取影院搜索列表  新版影片维度筛选V2
     * 注意：这个是获取某个影片的影院列表   
     *
     * @param string Y  cityId     城市id，如：10 表示北京
     * @param string N  areaId     区域id，如：1011 表示朝阳
     * @param string Y  longitude  经度
     * @param string Y  latitude   维度
     * @param string Y  sortField  排序字段，如：distance、min_price
     * @param string Y  channelId  渠道id
     * @param string Y  pageNum    页码
     * @param string Y  pageSize   单页条目数
     * @param string N  order      排序条件，如：asc、desc
     * @param string N  brand      影院品牌
     * @param string N  special    影厅特色，传特色
     * @param string N  openId     用户openId
     * @param string N  recent     上映时间段，值可以为：0、6、12、18
     * @param string Y  movieId    影片id
     * @param string N  date       日期，也就是获取这个影片哪一天的在映的影片列表，默认今天
     * @param string N  lable       影院标签，key1:val1类型
     * @param int    N  card       会员卡id,如果传入此参数,表示要查询支持这个会员卡的影院列表
     * @param int    N  serv       参数为服务标识，多个服务之间用英文逗号（,）分隔 snack 小吃 refund 可退票 discount 有优惠
                                    如要筛选有小吃、有优惠的影院，输入参数为 "snack,discount"
     * @param float  N  maxPrice     实际售卖价的最小值且小于或者等于maxPrice的价格
     * @param float  N  minPrice     实际售卖价的最小值且大于或者等于minPrice的价格
     * @return array
     */
    public function searchMovieCinemaListFromEsV2(array $arrInput = [])
    {
        $return = self::getStOut();
        //拼装参数
        $arrParams = [];
        $iChannelId = self::getParam($arrInput, 'channelId');
        $arrParams['area'] = self::getParam($arrInput, 'areaId', null); //区域id
        $arrParams['city'] = self::getParam($arrInput, 'cityId'); //城市id
        $arrParams['lon'] = self::getParam($arrInput, 'longitude', 999); //经度
        $arrParams['lat'] = self::getParam($arrInput, 'latitude', 999); //纬度
        $arrParams['field'] = self::getParam($arrInput, 'sortField', 'distance'); //关键字
        $arrParams['order'] = self::getParam($arrInput, 'order', 'asc');
        $arrParams['page'] = self::getParam($arrInput, 'pageNum', 1);
        $arrParams['pcount'] = self::getParam($arrInput, 'pageSize', 10);
        $arrParams['tpid'] = $iChannelId;
        $arrParams['brand'] = self::getParam($arrInput, 'brand', null);
        $arrParams['special'] = self::getParam($arrInput, 'special', null);
        $arrParams['recent'] = self::getParam($arrInput, 'recent', null);
        $arrParams['movieid'] = self::getParam($arrInput, 'movieId', null);
        $arrParams['date'] = self::getParam($arrInput, 'date', date('Y-m-d'));
        
        $arrParams['label'] = self::getParam($arrInput, 'label', null);
        $arrParams['card'] = self::getParam($arrInput, 'card', null);
        $arrParams['serv'] = self::getParam($arrInput, 'serv', null);  //与老版不同点 添加影院服务筛选
        //(电影维度 影院搜索  价格区间 前端不传则 不传递此参数)
        if(self::getParam($arrInput, 'minPrice', null)){
            $arrParams['minPrice'] =self::getParam($arrInput, 'minPrice', null);
        }
        if(self::getParam($arrInput, 'maxPrice', null)){
            $arrParams['maxPrice'] =self::getParam($arrInput, 'maxPrice', null);
        }
        $arrParams['price'] = self::getParam($arrInput, 'price', null);  //2017 6 6苏利军新增
        $arrParams['sid'] = self::getParam($_COOKIE, 'sid');
        $arrParams['bid'] = self::getParam($_COOKIE, 'bid');
        //openid优先从$arrInput取,没有的话,再从Cookie取,后续,都得从$arrInput取
        $strOpenId = self::getParam($arrInput, 'openId');
        if (empty($strOpenId)) {
            $strOpenId = self::getParam($_COOKIE, 'openid');
        }
        $arrParams['openid'] = $strOpenId;
        //参数校验
        if (empty( $arrParams['movieid'] ) || empty( $arrParams['tpid'] ) || empty( $arrParams['city'] )) {
            return $return;
        }
        //获取用户收藏的影院id
        $strUserFavoriteCinemaIds = '';
        if ( !empty( $strOpenId )) {
            $arrFavoriteCinemaIdsRes = $this->service('Favorite')->getFavoriteCinema(['channelId' => $iChannelId, 'openId' => $strOpenId]);
            $strUserFavoriteCinemaIds = ( isset( $arrFavoriteCinemaIdsRes['ret'] ) && ( $arrFavoriteCinemaIdsRes['ret'] == 0 ) && !empty( $arrFavoriteCinemaIdsRes['data']['cinemaList'] ) ) ? implode(',',
                $arrFavoriteCinemaIdsRes['data']['cinemaList']) : '';
        }
        $arrParams['cnos'] = $strUserFavoriteCinemaIds;
        //值为null的，代表前端没有传，那我们也不能传给大数据（主要因为，我们传了，大数据就走条件查询了，所以，传空和不传，是不一样的）
        foreach ($arrParams as $key => $val) {
            if (is_null($val) || ( $val === '' )) {
                unset( $arrParams[$key] );
            }
        }
        //发送请求
        $http = []; //http请求信息
        $http['sMethod'] = 'POST';
        $http['iTryTimes'] = 2;
        $http['sendType'] = 'form';
        $http['arrData'] = $arrParams; //格式化请求数据
        $response = $this->http(JAVA_API_SEARCH_MOVIE_CINEMA_LIST_V2, $http, false);
        //处理返回结果
        if (( isset( $response['ret'] ) ) && ( $response['ret'] == 0 ) && !empty( $response['data'] )) {
            $return['data'] = $response['data'];
            $return['extra'] = !empty( $response['extra'] ) ? $response['extra'] : [];
            //将discount_dis, 空数组转为空对象
            foreach ($return['data'] as &$cinemaInfo) {
                if (isset($cinemaInfo['card_dis']) && empty($cinemaInfo['card_dis'])) {
                    $cinemaInfo['card_dis'] = new \stdClass();
                }
            }
        }

        return $return;
    }

    /**
     * 获取影院搜索列表需要的过滤信息
     * 注意：这个是获取某个影片的影院列表
     *
     * @param string Y  cityId     城市id，如：10 表示北京
     * @param string Y  channelId  渠道id
     * @param string Y  movieId    影片id
     *
     * @return array
     */
    public function searchCinemaFilters(array $arrInput = [])
    {
        $return = self::getStOut();
        //拼装参数
        $arrParams = [];
        $iChannelId = self::getParam($arrInput, 'channelId');
        $arrParams['city'] = self::getParam($arrInput, 'cityId'); //城市id
        $arrParams['tpid'] = $iChannelId;
        $arrParams['movieid'] = self::getParam($arrInput, 'movieId', null);
        //参数校验
        if (empty($arrParams['tpid']) || empty($arrParams['city'])) {
            return $return;
        }
        //值为null的，代表前端没有传，那我们也不能传给大数据（主要因为，我们传了，大数据就走条件查询了，所以，传空和不传，是不一样的）
        foreach ($arrParams as $key => $val) {
            if (is_null($val) || ($val === '')) {
                unset($arrParams[$key]);
            }
        }
        //发送请求
        $http = []; //http请求信息
        $http['sMethod'] = 'POST';
        $http['iTryTimes'] = 2;
        $http['sendType'] = 'form';
        $http['arrData'] = $arrParams; //格式化请求数据
        $response = $this->http(JAVA_API_SEARCH_CINEMA_FILTER, $http, false);
        //处理返回结果
        if ((isset($response['ret'])) && ($response['ret'] == 0) && !empty($response['data'])) {
            $return['data'] = $response['data'];
        }

        return $return;
    }

    /**
     * 新版影院搜索接口
     * 注意：该接口，用于替换旧版本的影院搜索接口。
     * 个人建议，带v的接口，都是用于替换以前的某个接口。
     *
     * @param string Y  cityId      城市id，如：10 表示北京
     * @param string Y  channelId   渠道id
     * @param string N  latitude    维度，这个必须得传给Java，如果没有，传999
     * @param string N  longitude   经度，同上
     * @param string N  keyword     查询内容
     *
     * @return array
     */
    public function searchCinemaV2(array $arrInput = [])
    {
        $return = self::getStOut();
        $return['data']['list'] = [];
        //拼装参数
        $arrParams = [];
        $arrParams['cityName'] = self::getParam($arrInput, 'cityName'); //城市名
        $arrParams['cityId'] = self::getParam($arrInput, 'cityId', null); //城市id
        //cityId参数没有, 就不要传
        if (empty($arrParams['cityId'])) {
            unset($arrParams['cityId']);
        }
        $arrParams['lat'] = self::getParam($arrInput, 'latitude', 999);
        $arrParams['lon'] = self::getParam($arrInput, 'longitude', 999);
        $arrParams['pindex'] = self::getParam($arrInput, 'pageNum', 1);
        $arrParams['pcount'] = self::getParam($arrInput, 'pageSize', 20);
        $arrParams['query'] = self::getParam($arrInput, 'keyword');
        $strChannelId = self::getParam($arrInput, 'channelId');
        $arrParams['tpId'] = $strChannelId;
        $strOpenId = self::getParam($arrInput, 'openId');
        //参数校验
        if (empty($arrParams['cityName']) || empty($arrParams['query'])) {
            return $return;
        }
        //如果有openId,则获取用户收藏的影院id,传给后端
        if (!empty($strOpenId)) {
            $arrRet = $this->service('Cinema')->getFavCinemaIds(['channelId' => $strChannelId, 'openId' => $strOpenId]);
            if (isset($arrRet['ret']) && ($arrRet['ret'] == 0) && !empty($arrRet['data'])) {
                $arrParams['cnos'] = implode(',', $arrRet['data']);
            }
        }
        //发送请求
        $httpParams = []; //http请求信息
        $httpParams['sMethod'] = 'POST';
        $httpParams['iTryTimes'] = 2;
        $httpParams['sendType'] = 'form';
        $httpParams['arrData'] = $arrParams; //格式化请求数据
        $response = $this->http(JAVA_API_SEARCH_CINEMA_INFO, $httpParams, false);
        //处理返回结果
        if ((isset($response['ret'])) && ($response['ret'] == 0) && !empty($response['data'])) {
            $return['data']['list'] = $response['data'];
            $return['data']['extra'] = !empty($response['extra']) ? $response['extra'] : new \stdClass();
        }

        return $return;
    }

    /**
     * 获取支持某个会员卡的所有影院列表(可以不区分城市)
     *
     * @param string Y  cityId     城市id，如：10 表示北京
     * @param string N  areaId     区域id，如：1011 表示朝阳
     * @param string Y  longitude  经度
     * @param string Y  latitude   维度
     * @param string Y  sortField  排序字段，如：distance、min_price
     * @param string Y  channelId  渠道id
     * @param string Y  pageNum    页码
     * @param string Y  pageSize   单页条目数
     * @param string N  order      排序条件，如：asc、desc
     * @param string N  brand      影院品牌
     * @param string N  special    影厅特色，传特色
     * @param int    N  cardTypeId 会员卡主类型id,如果传入此参数,表示要查询支持这个会员卡的影院列表
     * @param string N  openId     用户openId
     *
     * @return array
     */
    public function searchCardCinemaList(array $arrInput = [])
    {
        $return = self::getStOut();
        $return['data']['list'] = [];
        $return['data']['count'] = ['all_count' => 0, 'city_count' => 0];
        //拼装参数
        $arrParams = [];
        $iChannelId = self::getParam($arrInput, 'channelId');
        $arrParams['area'] = self::getParam($arrInput, 'areaId', null); //区域id
        $arrParams['city'] = self::getParam($arrInput, 'cityId'); //城市id
        $arrParams['lon'] = self::getParam($arrInput, 'longitude', 999); //经度
        $arrParams['lat'] = self::getParam($arrInput, 'latitude', 999); //纬度
        $arrParams['field'] = self::getParam($arrInput, 'sortField', 'distance'); //关键字
        $arrParams['order'] = self::getParam($arrInput, 'order', 'desc');
        $arrParams['page'] = self::getParam($arrInput, 'pageNum', 1);
        $arrParams['pcount'] = self::getParam($arrInput, 'pageSize', 10);
        $arrParams['tpid'] = $iChannelId;
        $arrParams['card'] = self::getParam($arrInput, 'cardTypeId', null);
        //取cookie的一些参数(其实这个传过去他们也不用,是其他人要分析他们的日志)
        $arrParams['sid'] = self::getParam($_COOKIE, 'sid');
        $arrParams['bid'] = self::getParam($_COOKIE, 'bid');
        $arrParams['openid'] = self::getParam($arrInput, 'openId');
        //参数校验
        if (empty($arrParams['tpid']) || empty($arrParams['card'])) {
            return $return;
        }
        //获取用户收藏的影院id
        $strUserFavoriteCinemaIds = '';
        $strOpenId = self::getParam($arrInput, 'openId');
        if (!empty($strOpenId)) {
            $arrFavoriteCinemaIdsRes = $this->service('Favorite')->getFavoriteCinema(['channelId' => $iChannelId, 'openId' => $strOpenId]);
            $strUserFavoriteCinemaIds = (isset($arrFavoriteCinemaIdsRes['ret']) && ($arrFavoriteCinemaIdsRes['ret'] == 0) && !empty($arrFavoriteCinemaIdsRes['data']['cinemaList'])) ? implode(',',
                $arrFavoriteCinemaIdsRes['data']['cinemaList']) : '';
        }
        $arrParams['cnos'] = $strUserFavoriteCinemaIds;
        //值为null的，代表前端没有传，那我们也不能传给大数据（主要因为，我们传了，大数据就走条件查询了，所以，传空和不传，是不一样的）
        foreach ($arrParams as $key => $val) {
            if (is_null($val) || ($val === '')) {
                unset($arrParams[$key]);
            }
        }
        //发送请求
        $http = []; //http请求信息
        $http['sMethod'] = 'POST';
        $http['iTryTimes'] = 2;
        $http['sendType'] = 'form';
        $http['arrData'] = $arrParams; //格式化请求数据
        $response = $this->http(JAVA_API_SEARCH_CARD_CINEMA_LIST, $http, false);
        //处理返回结果
        if ((isset($response['ret'])) && ($response['ret'] == 0)) {
            $return['data']['list'] = $response['data'];
            $return['data']['count']['all_count'] = !empty($response['page']['all_count']) ? $response['page']['all_count'] : 0;
            $return['data']['count']['city_count'] = !empty($response['page']['city_count']) ? $response['page']['city_count'] : 0;
        }

        return $return;
    }
    
    /**
     * 获取搜索推荐的数据
     *
     * @return array
     */
    public function getSearchRecommend(array $arrInput = [])
    {
        $iChannelId = self::getParam($arrInput, 'channelId');
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $res = $this->model('search')->getSearchRecommend($iChannelId);
        if ( !empty($res)) {
            $return['data'] = $res;
        }
        
        return $return;
    }
    
    /**
     * 获取影院搜索列表需要的过滤信息——V2版本(返回结果中,增加了服务数据)
     * 注意：这个是获取某个影片的影院列表
     *
     * @param string Y  cityId     城市id，如：10 表示北京
     * @param string Y  channelId  渠道id
     * @param string Y  movieId    影片id
     *
     * @return array
     */
    public function searchCinemaFiltersV2(array $arrInput = [])
    {
        $return = self::getStOut();
        //拼装参数
        $arrParams = [];
        $iChannelId = self::getParam($arrInput, 'channelId');
        $arrParams['city'] = self::getParam($arrInput, 'cityId'); //城市id
        $arrParams['tpid'] = $iChannelId;
        $arrParams['movieid'] = self::getParam($arrInput, 'movieId', null);
        //参数校验
        if (empty($arrParams['tpid']) || empty($arrParams['city'])) {
            return $return;
        }
        //值为null的，代表前端没有传，那我们也不能传给大数据（主要因为，我们传了，大数据就走条件查询了，所以，传空和不传，是不一样的）
        foreach ($arrParams as $key => $val) {
            if (is_null($val) || ($val === '')) {
                unset($arrParams[$key]);
            }
        }
        //发送请求
        $http = []; //http请求信息
        $http['sMethod'] = 'POST';
        $http['iTryTimes'] = 2;
        $http['sendType'] = 'form';
        $http['arrData'] = $arrParams; //格式化请求数据
        $response = $this->http(JAVA_API_SEARCH_CINEMA_FILTER_V2, $http, false);
        //处理返回结果
        if ((isset($response['ret'])) && ($response['ret'] == 0) && !empty($response['data'])) {
            $return['data'] = $response['data'];
        }
        
        return $return;
    }

}