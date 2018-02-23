<?php

namespace sdkService\service;


use sdkService\helper\Utils;

class serviceMovie extends serviceBase
{

    /**
     * 获取影片详情信息
     *
     * @param string $movieId 影片Id
     * @param string $channelId 渠道编号
     * @param string $CityId 城市编号
     * @param string $openId 用户openId，这个用于判断用户是否有队这个片子的尿点
     * @param string $pee 是否获取这个片子的尿点
     * @param string $fromId 当$pee为1时有效，主要给尿点使用，其实尿点也只是记录个日志而已，为的是防止产品又加统计来源做的冗余字段
     *
     * @param int $needbuyflag 默认是1 需要buy_flag,有些场景可能只需要影片的基本信息，不需要buy_flag，可以少操作redis
     *
     * @return array
     */
    public function readMovieInfo(array $arrInput = [])
    {
        //是否走新版crontask_new数据源
        $iMovieNewStatic = !empty(\wepiao::$config['movieNewStatic']) ? \wepiao::$config['movieNewStatic'] : 0;
        if ($iMovieNewStatic) {
            return $this->readMovieInfoNewStatic($arrInput);
        }
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iMovieId = self::getParam($arrInput, 'movieId');
        $iCityId = self::getParam($arrInput, 'cityId');
        $strOpenId = self::getParam($arrInput, 'openId');
        $iPee = self::getParam($arrInput, 'pee');
        $iFromId = self::getParam($arrInput, 'fromId');
        $iNeedbuyflag = self::getParam($arrInput,'needbuyflag',1);

        $return = self::getStOut();
        if (empty($return) || empty($iChannelId)) {
            return $return;
        }

        $data = $this->model('movie')->readMovieInfo($iChannelId, $iMovieId);

        //判断是否需要buy_flag
        if ($data && $iNeedbuyflag) {
            $param = [
                'channelId' => $iChannelId,
                'movieId' => $iMovieId,
                'cityId' => $iCityId,
            ];
            //添加 buy_flag
            $data_buy_flag = $this->service('movie')->getScheFlagOfMovieCity($param);
            if ($data_buy_flag['ret'] == 0 && $data_buy_flag['sub'] == 0) {
                $data['buy_flag'] = $data_buy_flag['data']['scheFlag'];
            } else {
                //默认返回有排期
                $data['buy_flag'] = 1;
            }

            //查询手机电影信息如果有电影信息则返回相关内容
            $param = ['channelId' => $iChannelId, 'movieId' => $iMovieId];
            $playingret = $this->service("msdb")->getSvipMovie($param);
            if (!empty($playingret['data']['url'])) {
                $data['svipmovie'] = $playingret['data']['url'];
            } else {
                $data['svipmovie'] = "";
            }
        }

        if(!empty($data)) {
            //判断是否需要取尿点数据
            if ($iPee) {
                $arrPeeRes = $this->service('pee')->getMoviePeeInfo([
                    'movieId' => $iMovieId,
                    'channelId' => $iChannelId,
                    'fromId' => $iFromId,
                    'openId' => $strOpenId,
                ]);
                $peeInfo = (isset($arrPeeRes['ret']) && ($arrPeeRes['ret'] == 0) && !empty($arrPeeRes['data'])) ? $arrPeeRes['data'] : new \stdClass();
                $data['pee'] = $peeInfo;
            }else{
                $data['pee'] = new \stdClass();
            }
        }

        //增加点映标识
        $data['spot_film'] = 0;
        if ( !empty($iCityId)) {
            $isSpotFilmRes = $this->checkIsSpotFilm(['movieId' => $iMovieId, 'cityId' => $iCityId, 'channelId' => $iChannelId]);
            if ( !empty($isSpotFilmRes['data']['isSpotFilm'])) {
                $data['spot_film'] = 1;
            }
        }

        $return['data'] = $data;

        return $return;
    }

    /**
     * 获取城市下影片列表
     *
     * @param  string $cityId 城市Id
     * @param  string $channelId 渠道编号
     *
     * @return array
     */
    public function readCityMovie(array $arrInput = [])
    {
        //是否走新版crontask_new数据源
        $iMovieNewStatic = !empty(\wepiao::$config['movieNewStatic']) ? \wepiao::$config['movieNewStatic'] : 0;
        if ($iMovieNewStatic) {
            return $this->readCityMovieNewStatic($arrInput);
        }
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iCityId = self::getParam($arrInput, 'cityId');
        $uid = self::getParam($arrInput, 'uid');
        $openId = self::getParam($arrInput, 'openId');
        $unionId = self::getParam($arrInput, 'unionId');

        $return = self::getStOut();
        if (empty($iCityId) || empty($iChannelId)) {
            return $return;
        }
        $arrData = $this->model('movie')->readCityMovie($iChannelId, $iCityId);
        if (!empty($arrData)) {
            //因为正在热映的影片列表在info数组下所以格式化时格式info数组中的内容
            $this->_formatMovieList($arrData['info'], $iChannelId, $uid, $openId, $unionId, $iCityId);
        }
        $return['data'] = $arrData;

        return $return;
    }

    /**
     * 获取城市下即将上映的影片列表
     *
     * @param string $cityId 城市Id
     * @param string $channelId 渠道编号
     * @param        string     sortField 排序字段，如：wantCount、date
     * @param        string     sortOrder 排序条件，如：asc、desc
     *
     * @return array
     */
    public function readMovieWill(array $arrInput = [])
    {
        //是否走新版crontask_new数据源
        $iMovieNewStatic = !empty(\wepiao::$config['movieNewStatic']) ? \wepiao::$config['movieNewStatic'] : 0;
        if ($iMovieNewStatic) {
            return $this->readMovieWillNewStatic($arrInput);
        }
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iCityId = self::getParam($arrInput, 'cityId');
        $uid = self::getParam($arrInput, 'uid');
        $openId = self::getParam($arrInput, 'openId');
        $unionId = self::getParam($arrInput, 'unionId');
        $strSortField = self::getParam($arrInput, 'sortField');
        $strSortOrder = self::getParam($arrInput, 'sortOrder');
        $return = self::getStOut();
        if (empty($iCityId) || empty($iChannelId)) {
            return $return;
        }
        $arrData = $this->model('movie')->readMovieWill($iChannelId, $iCityId);
        //判断是否需要排序（有排序条件、且数据列表不为空，且列表中的对象，包含要排序的字段）
        if (!empty($strSortField) && !empty($arrData) && (is_array($arrData)) && isset($arrData[0][$strSortField])) {
            $sortOrder = ($strSortOrder == 'desc') ? SORT_DESC : SORT_ASC;
            array_multisort(Utils::array_column($arrData, $strSortField), $sortOrder, $arrData);
        }
        if (!empty($arrData)) {
            $this->_formatMovieList($arrData, $iChannelId, $uid, $openId, $unionId, $iCityId);
        }
        $return['data'] = $arrData;

        return $return;
    }

    /**
     * 获取影片下的影人信息
     *
     * @param int $movieId
     * @param string $channelId 渠道编号
     */
    public function getMovieActorList(array $arrInput = [])
    {

        return $this->service('msdb')->getMovieActor($arrInput);
        //        $iMovieId = self::getParam($arrInput, 'movieId');
        //        $return = self::getStOut();
        //        if ( !empty( $iMovieId )) {
        //            $url = MOVIE_DATABASES_MOVIE_ACTOR;
        //            $params['sMethod'] = 'post';
        //            $params['sendType'] = 'from';
        //            $params['arrData'] = [
        //                'movieId' => $iMovieId,
        //                'from'    => self::getParam($arrInput, 'channelId'),
        //            ];
        //            $data = $this->http($url, $params);
        //            $return['data'] = !empty( $data['data'] ) ? $data['data'] : [];
        //        }
        //
        //        return $return;
    }

    /**
     * 根据影片ids，获取这些影片对应的字段信息
     *
     * @param string  channelId  渠道id
     * @param string  movieIds   影片ids，,分割
     * @param array   fields     字段s，由,分割的字符串
     *
     * @return array 影片id为key，字段信息为value的二维数组
     */
    public function getMovieFieldByMovieIds($arrInput = [])
    {
        $return = self::getStOut();
        $arrList = [];
        $iChannelId = self::getParam($arrInput, 'channelId');
        $strMovieIds = self::getParam($arrInput, 'movieIds');
        $arrMovieIds = explode(',', $strMovieIds);
        $strFields = self::getParam($arrInput, 'fields');
        $arrFields = !empty($strFields) ? explode(',', $strFields) : [];
        if (!empty($iChannelId) && !empty($arrMovieIds) && !empty($arrFields)) {
            $arrMovieInfosRes = $this->readMovieInfos(['channelId' => $iChannelId, 'movieIds' => $arrMovieIds]);
            $arrMovieInfos = [];
            if (($arrMovieInfosRes['ret'] == 0) && !empty($arrMovieInfosRes['data']) && is_array($arrMovieInfosRes['data'])) {
                $arrMovieInfos = $arrMovieInfosRes['data'];
            }
            if (!empty($arrMovieInfos)) {
                foreach ($arrMovieInfos as $arrInfo) {
                    $iMovieId = !empty($arrInfo['id']) ? $arrInfo['id'] : '';
                    if (!isset($arrList[$iMovieId])) {
                        $arrList[$iMovieId] = [];
                    }
                    foreach ($arrFields as $field) {
                        if (isset($arrInfo[$field])) {
                            $arrList[$iMovieId][$field] = isset($arrInfo[$field]) ? $arrInfo[$field] : '';
                        }
                    }
                }
            }
        }
        $return['data'] = $arrList;

        return $return;
    }

    /**
     * 获取影片详情信息，根据影片ids
     *
     * @param string movieIds   影片Id
     * @param string channelId 渠道编号
     *
     * @return array
     */
    public function readMovieInfos(array $arrInput = [])
    {
        //是否走新版crontask_new数据源
        $iMovieNewStatic = !empty(\wepiao::$config['movieNewStatic']) ? \wepiao::$config['movieNewStatic'] : 0;
        if ($iMovieNewStatic) {
            return $this->readMovieInfosNewStatic($arrInput);
        }
        $iChannelId = self::getParam($arrInput, 'channelId');
        $arrMovieIds = self::getParam($arrInput, 'movieIds');
        $return = self::getStOut();
        if (empty($iChannelId) || empty($iChannelId)) {
            return $return;
        }
        $data = $this->model('movie')->readMovieInfos($iChannelId, $arrMovieIds);
        $return['data'] = $data;

        return $return;
    }

    /**
     * 格式化影片列表 [正在热映\即将上映中添加用户想看标识 APP用]
     *
     * @param        Array   arrData  需要格式化的数组
     * @param        int     channelId 渠道号
     * @param        int     uid  用户的uid
     * @param        string  openId 用户的openId
     * @param        string  unionId 用户的unionId
     * @param        string  cityId 城市Id
     */
    private function _formatMovieList(&$arrData, $channelId, $uid, $openId, $unionId, $iCityId = '')
    {
        ### 注：#号注释，2016-05-18修改，由于该字段目前没用到，为防止评论崩溃导致拖慢静态数据接口，先将其写死为0
        #//获取用户想看列表
        #$params = [
        #    'uid' => $uid,
        #    'openId' => $openId,
        #    'unionId' => $unionId,
        #    'channelId' => $channelId,
        #    'page' => 1,
        #    'num' => 99999,
        #];
        #$wantMovies = [];
        #$retSDK = $this->service('comment')->getUserWantMovie($params);
        #if ($retSDK['ret'] == 0 AND $retSDK['sub'] == 0) {
        #    if (!empty($retSDK['data'])) {
        #        $wantMovies = $retSDK['data']['movies'];
        #    }
        #}

        foreach ($arrData as $k => $v) {
            $arrData[$k]['user_want'] = 0;
            #if (in_array($v['id'], $wantMovies)) {
            #    $arrData[$k]['user_want'] = 1;
            #} else {
            #    $arrData[$k]['user_want'] = 0;
            #}
            //获取单片推荐资讯信息
            //$arrMovieRecommendedNews = $this->readMovieRecommendedNews(['channelId' => $channelId, 'cityId' => $iCityId, 'movieId' => $v['id']]);
            //$arrData[$k]['recommended_news'] = ( ( $arrMovieRecommendedNews['ret'] == 0 ) && !empty( $arrMovieRecommendedNews['data']['list'] ) ) ? $arrMovieRecommendedNews['data']['list'] : [];
        }
    }

    /**
     * 获取城市下影片列表
     *
     * @param  string $cityId 城市Id
     * @param  string $channelId 渠道编号
     * @param  int $page 页码
     * @param  int $num 每页的数量
     * @param  int $uid 用户id
     * @param  string $openId 用户OpenId
     * @param  string $unionId 用户unionid
     * @param  int $formatMovieWant 是否关闭格式化用户想看字段，默认是格式化，如果需要关闭，请传数字0。
     *
     * @return array
     */
    public function readCityMovieByPage(array $arrInput = [])
    {
        //是否走新版crontask_new数据源
        $iMovieNewStatic = !empty(\wepiao::$config['movieNewStatic']) ? \wepiao::$config['movieNewStatic'] : 0;
        if ($iMovieNewStatic) { 
            return $this->readCityMovieByPageNewStatic($arrInput);
        }
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iCityId = self::getParam($arrInput, 'cityId');
        $uid = self::getParam($arrInput, 'uid');
        $openId = self::getParam($arrInput, 'openId');
        $unionId = self::getParam($arrInput, 'unionId');
        $page = self::getParam($arrInput, 'page');
        $num = self::getParam($arrInput, 'num');
        $formatMovieWant = self::getParam($arrInput, 'formatMovieWant', 1);
        $return = self::getStOut();

        $arrData = $this->model('movie')->readCityMovieByPage($iChannelId, $iCityId, $page, $num);
        //看是否需要格式化影片中的看过标识
        if (($formatMovieWant == 1) && !empty($arrData['list']) && (!empty($uid) || !empty($unionId) || !empty($openId))) {
            //因为正在热映的影片列表在info数组下所以格式化时格式info数组中的内容
            $this->_formatMovieList($arrData['list'], $iChannelId, $uid, $openId, $unionId, $iCityId);
        }
        $return['data'] = $arrData;

        return $return;
    }

    /**
     * 获取单片推荐资讯信息
     * 需要注意这里的逻辑。因为后台创建活动的时候，可能的类型比较多，所以这里需要组合判断，拿到所有的可能性
     *
     * @param string movieId    影片id
     * @param string cityId     城市id
     * @param string channelId  渠道id
     *
     * @return array
     */
    public function readMovieRecommendedNews($arrInput = [])
    {
        //是否走新版crontask_new数据源
        $iMovieNewStatic = !empty(\wepiao::$config['movieNewStatic']) ? \wepiao::$config['movieNewStatic'] : 0;
        if ($iMovieNewStatic) {
            return $this->readMovieRecommendedNewsStatic($arrInput);
        }
        $arrReturn = static::getStOut();
        $arrReturn['data']['list'] = [];
        //参数处理
        $iMovieId = self::getParam($arrInput, 'movieId');
        $iCityId = self::getParam($arrInput, 'cityId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        //数据处理
        if (!empty($iChannelId) && !empty($iMovieId) && !empty($iCityId)) {
            //读取单品推荐信息
            $arrData = $this->model('Movie')->readMovieRecommendedNews($iChannelId);
            if (!empty($arrData) && is_array($arrData)) {
                $arrReturn['data']['list'] = [];
                //组合各种可能的情况
                $arrData1 = $arrData2 = [];
                //1、判断地区不限地区的情况
                $arrData1 = !empty($arrData[$iChannelId][0][$iMovieId]) ? $arrData[$iChannelId][0][$iMovieId] : [];
                //2、判断正常情况
                $arrData2 = !empty($arrData[$iChannelId][$iCityId][$iMovieId]) ? $arrData[$iChannelId][$iCityId][$iMovieId] : [];
                //3、合并，并去重，并且同一个影片的推荐信息，只取前2个
                $arrList = [];
                $arrMergeData = array_merge($arrData1, $arrData2);
                foreach ($arrMergeData as $arrRecommendInfo) {
                    $arrList[$arrRecommendInfo['id']] = $arrRecommendInfo;
                    if (count($arrList) == 2) {
                        break;
                    }
                }
                $arrMergeData = null;
                $arrList = array_values($arrList);
                //4、排序
                array_multisort(Utils::array_column($arrList, 'order'), SORT_DESC, $arrList);
                //5、合并有效数据
                $arrReturn['data']['list'] = $arrList;
            }
        }

        return $arrReturn;
    }

    /**
     * 增加影片阵营购票数
     *
     * @param int $movieId 影片ID
     * @param string $campName 阵营名称
     * @param int $seatNum 阵营计数，默认为1，最大为4
     */
    public function incrMovieCamp($arrInput = [])
    {
        $arrReturn = static::getStOut();
        $iMovieId = self::getParam($arrInput, 'movieId');
        $sCampName = self::getParam($arrInput, 'campName');
        $iSeatNum = !empty($arrInput['seatNum']) ? $arrInput['seatNum'] : 1;
        if (!empty($iMovieId) && !empty($sCampName)) {
            $campCount = $this->model('movie')->incrMoiveCamp($iMovieId, $sCampName, $iSeatNum, true);
            if (!empty($campCount)) {
                $arrReturn['data'] = $campCount;
            }
        }

        return $arrReturn;
    }

    /**
     * 查询影片阵营购票数
     *
     * @param int $movieId 影片ID
     */
    public function getMovieCamp($arrInput = [])
    {
        $arrReturn = static::getStOut();
        $iMovieId = self::getParam($arrInput, 'movieId');
        $campCount = $this->model('movie')->getMovieCamp($iMovieId);
        if (!empty($campCount)) {
            $arrReturn['data'] = $campCount;
        }

        return $arrReturn;
    }

    /**
     * 按条件，获取即将上映的影片列表
     * 目前有2个条件，一个是按照日期分组，一种是按照想看人数降序排序
     *
     * @param int              channelId 渠道id
     * @param int              cityId    城市id
     * @param int              uid       用户中心id
     * @param string           openId    用户openId
     * @param string           unionId   用户unionId
     * @param int              year      年份
     * @param int              month     月份
     * @param int              state     状态，1标识获取全部，2标识获取某模糊年的数据，3标识获取某月（包括确定日期和不确定日期的），4标识获取某年所有（包括某年下所有月份）
     *                         5表示分页获取，默认获取第一页
     * @param int              page      页码
     * @param string           sortField 排序字段,如:wantCount、initScore等,这些字段必须是影片详情中有的
     * @param string           sortType  排序类型,可以为:asc、desc
     *
     * @param string
     */
    public function getMovieWillWithDate($arrInput = [])
    {
        //是否走新版crontask_new数据源
        $iMovieNewStatic = !empty(\wepiao::$config['movieNewStatic']) ? \wepiao::$config['movieNewStatic'] : 0;
        if ($iMovieNewStatic) {
            return $this->getMovieWillWithDateNewStatic($arrInput);
        }
        $arrReturn = static::getStOut();
        $arrReturn['data']['list'] = [];
        //获取数据
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iCityId = self::getParam($arrInput, 'cityId');
        $uid = self::getParam($arrInput, 'uid');
        $strOpenId = self::getParam($arrInput, 'openId');
        $strUnionId = self::getParam($arrInput, 'unionId');
        $strYear = self::getParam($arrInput, 'year');
        $strMonth = self::getParam($arrInput, 'month');
        $iState = self::getParam($arrInput, 'state');
        $iPage = self::getParam($arrInput, 'page', 1);
        $strSortField = self::getParam($arrInput, 'sortField');
        $strSortType = self::getParam($arrInput, 'sortType');
        $arrMovieWillWithDate = $this->model('Movie')->getMovieWillWithDate($iChannelId, $iCityId, $strYear, $strMonth, $iState, $iPage);
        //格式化，添加user_want
        if (!empty($arrMovieWillWithDate['list']) && is_array($arrMovieWillWithDate['list'])) {
            //格式化
            foreach ($arrMovieWillWithDate['list'] as &$arrMovieWillList) {
                if (!empty($arrMovieWillList['list'])) {
                    foreach ($arrMovieWillList['list'] as &$arrListData) {
                        if (!empty($arrListData['list']) && is_array($arrListData['list'])) {
                            $this->_formatMovieList($arrListData['list'], $iChannelId, $uid, $strOpenId, $strUnionId, $iCityId);
                            //排序(确保要排序的字段是影片信息中的字段)
                            if (!empty($arrListData['list']) && isset($arrListData['list'][0][$strSortField]) && !empty($strSortField) && !empty($strSortType)) {
                                $sortT = ($strSortType == 'asc') ? SORT_ASC : SORT_DESC;
                                array_multisort(Utils::array_column($arrListData['list'], $strSortField), $sortT, $arrListData['list']);
                            }
                        }
                    }
                }
            }
            $arrReturn['data'] = $arrMovieWillWithDate;
        }

        return $arrReturn;
    }

    /**
     * 获取城市即将上映的影片列表
     * 支持排序和分页，比如按照想看人数降序分页
     *
     * @param  string $cityId 城市Id
     * @param  string $channelId 渠道编号
     * @param  int $page 页码
     * @param  int $num 每页的数量
     * @param  int $uid 用户id
     * @param  string $openId 用户openId，非必传
     * @param  string $unionId 用户unionid，非必传
     * @param  int $formatMovieWant 是否关闭格式化用户想看字段，默认是格式化，如果需要关闭，请传数字0（需要参数：openId、unionId）。
     * @param  string $sortField 排序字段。目前支持：wantCount、seenCount、date 三种
     * @param  string $order 排序方式，可是是 desc、asc
     *
     * @return array
     */
    public function readMovieWillByPage(array $arrInput = [])
    {
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iCityId = self::getParam($arrInput, 'cityId');
        $uid = self::getParam($arrInput, 'uid');
        $openId = self::getParam($arrInput, 'openId');
        $unionId = self::getParam($arrInput, 'unionId');
        $page = self::getParam($arrInput, 'page', 1);
        $num = self::getParam($arrInput, 'num', 10);
        $sortField = self::getParam($arrInput, 'sortField', 'wantCount');
        $order = self::getParam($arrInput, 'order', 'desc');
        $formatMovieWant = self::getParam($arrInput, 'formatMovieWant', 1);
        $return = self::getStOut();

        $arrData = $this->model('movie')->readMovieWillByPage($iChannelId, $iCityId, $page, $num, $sortField, $order);
        //看是否需要格式化影片中的看过标识
        if (($formatMovieWant == 1) && !empty($arrData) && (!empty($uid) || !empty($unionId) || !empty($openId))) {
            //因为正在热映的影片列表在info数组下所以格式化时格式info数组中的内容
            $this->_formatMovieList($arrData, $iChannelId, $uid, $openId, $unionId, $iCityId);
        }
        $return['data'] = $arrData;

        return $return;
    }

    /**
     * 获取某个影片在某个城市是否有排期的标识
     *
     * @param int    movieId    渠道id
     * @param int    cityId     城市id
     * @param int    channelId  城市id
     *
     * @param array  返回结果如：{"ret":0,"sub":0,"msg":"success","data":{"scheFlag":0}}，scheFlag为1表示有排期，0表示没有
     */
    public function getScheFlagOfMovieCity($arrInput = [])
    {
        //是否走新版crontask_new数据源
        $iMovieNewStatic = !empty(\wepiao::$config['movieNewStatic']) ? \wepiao::$config['movieNewStatic'] : 0;
        if ($iMovieNewStatic) {
            return $this->getScheFlagOfMovieCityNewStatic($arrInput);
        }
        $arrReturn = static::getStOut();
        //默认返回有排期
        $arrReturn['data']['scheFlag'] = 1;
        //获取数据
        $iMovieId = self::getParam($arrInput, 'movieId');
        $iCityId = self::getParam($arrInput, 'cityId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        if (!empty($iChannelId) && !empty($iMovieId) && !empty($iCityId)) {
            $iScheFlag = $this->model('Movie')->getScheFlagOfMovieCity($iChannelId, $iMovieId, $iCityId);
            $arrReturn['data']['scheFlag'] = $iScheFlag;
        }

        return $arrReturn;
    }


    /**
     * 获取某个影片的明星选座数据
     *
     * @param string movieId   影片编号
     * @param string channelId  渠道编号
     *
     * @return array
     */
    public function getMovieCustomSeat(array $arrInput = [])
    {
        //是否走新版crontask_new数据源
        $iMovieNewStatic = !empty(\wepiao::$config['movieNewStatic']) ? \wepiao::$config['movieNewStatic'] : 0;
        if ($iMovieNewStatic) {
            return $this->getMovieCustomSeatNewStatic($arrInput);
        }
        //参数整理
        $iMovieId = self::getParam($arrInput, 'movieId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        $return = self::getStOut();
        //参数判断
        if (empty($iMovieId) || empty($iChannelId)) {
            $return['data']['customization_seats'] = new \stdClass();
        } else {
            $objMovieCustomSeat = $this->model('movie')->readMovieCustomSeat($iChannelId, $iMovieId);
            $return['data']['customization_seats'] = $objMovieCustomSeat;
        }

        return $return;
    }

    /**
     * 通过msdb获取影片信息
     * @param $arrInput
     */
    public function getMovieInfoMsdb($arrInput)
    {
        //参数整理
        $iMovieId = self::getParam($arrInput, 'movieId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        $cityId = self::getParam($arrInput, 'cityId', '10');
        $return = self::getStOut();
        if (empty($iMovieId) || empty($iChannelId)) {
            $return['data'] = new \stdClass();
        } else {
            $params = ['movieId' => $iMovieId, 'channelId' => $iChannelId, 'cityId' => $cityId];
            $arrMovie = $this->readMovieInfo($params);
            if (empty($arrMovie['data'])) {
                $arrmSdb = $this->service("msdb")->getMovieInfo($params);
                $arrData = (array)$arrmSdb['data'];
                if (empty($arrData)) {
                    $return['data'] = [];
                } else {
                    $arrData = $this->_formatMsdbMovieInfo($arrData);

                    $return['data'] = $arrData;
                }
            } else {
                $return['data'] = $arrMovie['data'];
            }

            //查询手机电影信息如果有电影信息则返回相关内容
            if (!empty($return['data']['id'])) {
                $param = ['channelId' => $iChannelId, 'movieId' => $iMovieId];
                $playingret = $this->service("msdb")->getSvipMovie($param);
                if (!empty($playingret['data']['url'])) {
                    $return['data']['svipmovie'] = $playingret['data']['url'];
                } else {
                    $return['data']['svipmovie'] = "";
                }
            }

        }
        return $return;
    }

    /**
     * 格式化MSDB影片信息
     * @param $arrData
     * @return mixed
     */
    private function _formatMsdbMovieInfo($arrData)
    {
        //重命名字段
        $rename = function ($before, $after) use (&$arrData) {
            if (isset($arrData[$before])) {
                $arrData[$after] = $arrData[$before];
                unset($arrData[$before]);
            }
        };
        //删除不必要的字段
        $unset = function ($key) use (&$arrData) {
            if (isset($arrData[$key])) {
                unset($arrData[$key]);
            }
        };

        //设置海报地址
        $setPoster = function () use (&$arrData) {
            //格式化封面图
            if (isset($arrData['IMG_COVER'][0])) {
                $posterImages = (array)$arrData['IMG_COVER'][0];
                $arrData['poster_url'] = $posterImages['180X252'];
            } else {
                $arrData['poster_url'] = "";
            }
            //格式化封面
            if (isset($arrData['IMG_COVER'][0])) {
                $posterImages = (array)$arrData['IMG_COVER'][0];
                $arrData['poster_url_size3'] = $posterImages['350X490'];
            } else {
                $arrData['poster_url_size3'] = "";
            }
            //格式化背景图
            if (isset($arrData['IMG_BACKGROUND'][0])) {
                $posterImages = (array)$arrData['IMG_BACKGROUND'][0];
                $arrData['poster_url_size21'] = $posterImages['800X560'];
            } else {
                $arrData['poster_url_size21'] = "";
            }

            $arrData['poster_url_size22'] = isset($arrData['poster_url_size22']) ? $arrData['poster_url_size22'] : '';
            $arrData['poster_url_size23'] = isset($arrData['poster_url_size23']) ? $arrData['poster_url_size23'] : '';
            //格式化静态剧照
        };

        //重命名key
        $rename("MovieNo", "id");
        $rename("MovieNameChs", "name");
        $rename("MovieNameEng", "en_name");
        $rename("Director", "director");
        $rename("Starring", "actor");
        $rename("Contry", "country");
        $rename("MovieType", "tags");
        $rename("Duration", "longs");
        $rename("Version", "version");
        $rename("InShortRemark", "remark");
        $rename("MovieDetail", "detail");
        $rename("CoverId", "coverid");
        //设置海报地址
        $setPoster();
        //设置其它字段
        $arrData['simple_remarks'] = $arrData['remark'];
        $arrData['initscore'] = 0;
        $arrData['will_flag'] = 0;
        $arrData['date_status'] = 0;
        $arrData['date_des'] = date("Y年m月d日", strtotime($arrData['FirstShow']));
        $arrData['date'] = date("Ymd", strtotime($arrData['FirstShow']));
        $arrData['year'] = date("Y", strtotime($arrData['FirstShow']));
        $arrData['month'] = date("m", strtotime($arrData['FirstShow']));
        $arrData['date_stamp'] = strtotime($arrData['FirstShow']) ? strtotime($arrData['FirstShow']) : 0;
        $arrData['buy_flag'] = 0;
        $arrData['wantCount'] = isset($arrData['wantcount']) ? $arrData['wantcount'] : "0";
        $arrData['initScore'] = isset($arrData['score']) ? $arrData['score'] : "0";
        $arrData['seenScore'] = isset($arrData['seencount']) ? $arrData['seencount'] : "0";

        //unset掉不用的key
        $unset("MovieNamePinyin");
        $unset("MovieNameInitPinyin");
        $unset("MovieCodes");
        $unset("Score");
        $unset("IMG_COVER");
        $unset("IMG_BACKGROUND");
        $unset("Language");
        $unset("FirstShow");
        $unset("IMG_STILL");
        $unset("IMG_POSTER");
        $unset("IMG_NEWS");
        $unset("IMG_OTHER");
        $unset("IMG_OTHER");

        return $arrData;
    }

    /**
     * 获取城市下即将上映栏目的推荐预售电影
     * @param int              channelId 渠道id
     * @param int              cityId    城市id
     * @param int              uid       用户中心id
     * @param string           openId    用户openId
     * @param string           unionId   用户unionId
     * @param int $pageNum 展示数量
     * @return array
     */
    public function getMovieWillRecommend(array $arrInput = [])
    {
        $resData = [];//返回的即将上映可预售推荐数组（最后返回的数组）
        $wantMovies = []; //即将上映  想看 可预售 的电影数组
        $wantMovieIds = [];//想看电影列表ID
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iCityId = self::getParam($arrInput, 'cityId');
        $uid = self::getParam($arrInput, 'uid');
        $openId = self::getParam($arrInput, 'openId');
        $unionId = self::getParam($arrInput, 'unionId');
        $numShow = self::getParam($arrInput, 'pageNum');
        $return = self::getStOut();
        if (empty($iCityId) || empty($iChannelId)) {
            return $return;
        }

        //获取想看电影
        $params = [
            'uid' => $uid,
            'openId' => $openId,
            'unionId' => $unionId,
            'channelId' => $iChannelId,
            'page' => 1,
            'num' => 99999,
        ];

        $retSDK = $this->service('comment')->getUserWantMovie($params);
        if ($retSDK['ret'] == 0 AND $retSDK['sub'] == 0) {
            if (!empty($retSDK['data'])) {
                $wantMovieIds = $retSDK['data']['movies'];
            }
        }
        //读取即将上映列表
        $arrParams = [
            'cityId' => $iCityId,
            'channelId' => $iChannelId,
            'state' => 1, //获取全部
        ];
        $willDataRaw = $this->getMovieWillWithDate($arrParams);
        $num = 0;
        foreach ($willDataRaw['data']['list'] as $key=>$monlist) {
            if($key>=3)break; //只取即将上映邻近3个月的数据
            $willData = $monlist['list'];
            foreach ($willData as $k) {
                foreach ($k['list'] as $movieinfo) {
                    if ($num < $numShow && $movieinfo['will_flag'] == 1 && $movieinfo['buy_flag'] == 1
                    ) {
                        //读取影片详情，主要为获取预告片地址
                        $arrParams = [
                            'movieId' => $movieinfo['id'],
                            'channelId' => $iChannelId,
                        ];
                        $movieDetail = $this->service('Movie')->readMovieInfo($arrParams);
                        if (in_array($movieinfo['id'], $wantMovieIds)) { //即将上映的想看电影
                            $wantMovies[] = [
                                'id' => $movieinfo['id'],
                                'name' => $movieinfo['name'],
                                'date' => $movieinfo['date'],
                                'poster_url' => $movieinfo['poster_url'],
                                'name' => $movieinfo['name'],
                                'wantCount'=>$movieinfo['wantCount'],
                                'want'=>1,
                            ];
                        } else {            //即将上映的电影
                            $resData[] = [
                                'id' => $movieinfo['id'],
                                'name' => $movieinfo['name'],
                                'date' => $movieinfo['date'],
                                'poster_url' => $movieinfo['poster_url'],
                                'name' => $movieinfo['name'],
                                'wantCount'=>$movieinfo['wantCount'],
                            ];
                        }

                        $num++;
                    }
                }
            }
        }
        $return['data'] = array_merge($wantMovies , $resData);
        return $return;
    }

    /**
     * 判断一个影片是否为点映的片子
     *
     * @param array $arrInput
     *
     * @return array
     */
    public function checkIsSpotFilm($arrInput = [])
    {
        $arrReturn = static::getStOut();
        //默认返回有排期
        $arrReturn['data']['isSpotFilm'] = 0;
        //获取数据
        $iMovieId = self::getParam($arrInput, 'movieId');
        $iCityId = self::getParam($arrInput, 'cityId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        if ( !empty($iChannelId) && !empty($iMovieId) && !empty($iCityId)) {
            $arrCityIds = $this->model('movie')->readSpotFilmNewStatic($iChannelId, $iMovieId);
            if (!empty($arrCityIds) && is_array($arrCityIds) && in_array($iCityId, $arrCityIds)) {
                $arrReturn['data']['isSpotFilm'] = 1;
            }
        }

        return $arrReturn;
    }

    //——————————————————————————————————————— 新版crontask_new方法 begin ——————————————————————————————————//

    /**
     * 获取影片详情信息
     * crontask_new版本数据源
     *
     * @param string $movieId   影片Id
     * @param string $channelId 渠道编号
     * @param string $CityId    城市编号
     * @param string $openId    用户openId，这个用于判断用户是否有队这个片子的尿点
     * @param string $pee       是否获取这个片子的尿点
     * @param string $fromId    当$pee为1时有效，主要给尿点使用，其实尿点也只是记录个日志而已，为的是防止产品又加统计来源做的冗余字段
     *
     * @return array
     */
    public function readMovieInfoNewStatic(array $arrInput = [])
    {
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iMovieId = self::getParam($arrInput, 'movieId');
        $iCityId = self::getParam($arrInput, 'cityId');
        $strOpenId = self::getParam($arrInput, 'openId');
        $iPee = self::getParam($arrInput, 'pee');
        $iFromId = self::getParam($arrInput, 'fromId');

        $return = self::getStOut();
        if (empty($return) || empty($iChannelId)) {
            return $return;
        }
        $data = $this->model('movie')->readMovieInfoNewStatic($iChannelId, $iMovieId);
        //给APP的影片详情页添加上可购买标签
        if ($data) {
            $param = [
                'channelId' => $iChannelId,
                'movieId'   => $iMovieId,
                'cityId'    => $iCityId,
            ];
            //添加 buy_flag
            $data_buy_flag = $this->getScheFlagOfMovieCityNewStatic($param);
            if ($data_buy_flag['ret'] == 0 && $data_buy_flag['sub'] == 0) {
                $data['buy_flag'] = $data_buy_flag['data']['scheFlag'];
            } else {
                //默认返回有排期
                $data['buy_flag'] = 1;
            }
        }
        //判断是否需要取尿点数据
        $peeInfo = new \stdClass();
        if ($iPee) {
            $arrPeeRes = $this->service('pee')->getMoviePeeInfo([
                'movieId'   => $iMovieId,
                'channelId' => $iChannelId,
                'fromId'    => $iFromId,
                'openId'    => $strOpenId,
            ]);
            $peeInfo = (isset($arrPeeRes['ret']) && ($arrPeeRes['ret'] == 0) && !empty($arrPeeRes['data'])) ? $arrPeeRes['data'] : new \stdClass();
        }
        //有影片信息，再设置尿点信息
        if ( !empty($data['id'])) {
            $data['pee'] = $peeInfo;
        }
        //点映处理
        if ( !empty($iCityId) && !empty($iMovieId)) {
            $arrCityIds = $this->model('movie')->readSpotFilmNewStatic($iChannelId, $iMovieId);
            if ( !empty($arrCityIds) && is_array($arrCityIds) && in_array($iCityId, $arrCityIds)) {
                $data['spot_film'] = 1;
            }
        }
        //微信礼品卡入口
        if (!empty($iChannelId) && !empty($iMovieId) && ($iChannelId == 3)) {
            $arrGiftCard = $this->model('movie')->GetGiftCardEntry($iChannelId, $iMovieId);
            $data['gift_card_entry'] = $arrGiftCard ? $arrGiftCard : new \stdClass();
        }
        $return['data'] = $data;
        return $return;
    }

    /**
     * 获取影片列表，未分页版本
     *
     * @param array $arrInput
     */
    public function readCityMovieNewStatic(array $arrInput = [])
    {
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iCityId = self::getParam($arrInput, 'cityId');
        $uid = self::getParam($arrInput, 'uid');
        $openId = self::getParam($arrInput, 'openId');
        $unionId = self::getParam($arrInput, 'unionId');
        $params = [
            'channelId'       => $iChannelId,
            'cityId'          => $iCityId,
            'openId'          => $openId,
            'unionId'         => $unionId,
            'page'            => 1,
            'num'             => 1000,
            'formatMovieWant' => 0,
        ];
        $return = self::getStOut();
        if (empty($iCityId) || empty($iChannelId)) {
            return $return;
        }
        $return = $this->readCityMovieByPageNewStatic($params);
        $return['data']['comm']['path'] = '';
        $return['data']['info'] = !empty($return['data']['list']) ? $return['data']['list'] : [];
        unset($return['data']['list'], $return['data']['num'], $return['data']['page'], $return['data']['total_page'], $return['data']['total_row']);

        return $return;
    }

    /**
     * 获取某个影片在某个城市是否有排期的标识
     * 新版数crontask数据源
     *
     * @param int    movieId    渠道id
     * @param int    cityId     城市id
     * @param int    channelId  城市id
     *
     * @param array  返回结果如：{"ret":0,"sub":0,"msg":"success","data":{"scheFlag":0}}，scheFlag为1表示有排期，0表示没有
     */
    public function getScheFlagOfMovieCityNewStatic($arrInput = [])
    {
        $arrReturn = static::getStOut();
        //默认返回有排期
        $arrReturn['data']['scheFlag'] = 1;
        //获取数据
        $iMovieId = self::getParam($arrInput, 'movieId');
        $iCityId = self::getParam($arrInput, 'cityId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        if ( !empty($iChannelId) && !empty($iMovieId) && !empty($iCityId)) {
            $iScheFlag = $this->model('Movie')->getScheFlagOfMovieCityNewStatic($iChannelId, $iMovieId, $iCityId);
            $arrReturn['data']['scheFlag'] = $iScheFlag;
        }

        return $arrReturn;
    }

    /**
     * 获取影片详情信息，根据影片ids
     * 新版crontask_new数据源
     * 注意: 只能获取部分影片详情数据
     *
     * @param string movieIds   影片Id
     * @param string channelId 渠道编号
     *
     * @return array
     */
    public function readMovieInfosNewStatic(array $arrInput = [])
    {
        $iChannelId = self::getParam($arrInput, 'channelId');
        $arrMovieIds = self::getParam($arrInput, 'movieIds');
        $return = self::getStOut();
        if (empty($iChannelId) || empty($iChannelId)) {
            return $return;
        }
        $data = $this->model('movie')->readMovieInfosNewStatic($iChannelId, $arrMovieIds);
        $return['data'] = $data;

        return $return;
    }

    /**
     * 获取城市下影片列表
     * 新版crontask_new数据源
     *
     * @param  string $cityId 城市Id
     * @param  string $channelId 渠道编号
     * @param  int $page 页码
     * @param  int $num 每页的数量
     * @param  int $uid 用户id
     * @param  string $openId 用户OpenId
     * @param  string $unionId 用户unionid
     * @param  int $formatMovieWant 是否关闭格式化用户想看字段，默认是格式化，如果需要关闭，请传数字0。
     *
     * @return array
     */
    public function readCityMovieByPageNewStatic(array $arrInput = [])
    {
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iCityId = self::getParam($arrInput, 'cityId');
        $uid = self::getParam($arrInput, 'uid');
        $openId = self::getParam($arrInput, 'openId');
        $unionId = self::getParam($arrInput, 'unionId');
        $page = self::getParam($arrInput, 'page');
        $num = self::getParam($arrInput, 'num');
        $formatMovieWant = self::getParam($arrInput, 'formatMovieWant', 1);
        $return = self::getStOut();

        $arrData = $this->model('movie')->readCityMovieByPageNewStatic($iChannelId, $iCityId, $page, $num);
        //看是否需要格式化影片中的看过标识
        if (($formatMovieWant == 1) && !empty($arrData['list']) && (!empty($uid) || !empty($unionId) || !empty($openId))) {
            //因为正在热映的影片列表在info数组下所以格式化时格式info数组中的内容
            $this->_formatMovieListStatic($arrData['list'], $iChannelId, $uid, $openId, $unionId, $iCityId);
        }
        $return['data'] = $arrData;

        return $return;
    }

    /**
     * 获取单片推荐资讯信息
     * 需要注意这里的逻辑。因为后台创建活动的时候，可能的类型比较多，所以这里需要组合判断，拿到所有的可能性
     *
     * @param string movieId    影片id
     * @param string cityId     城市id
     * @param string channelId  渠道id
     *
     * @return array
     */
    public function readMovieRecommendedNewsStatic($arrInput = [])
    {
        $arrReturn = static::getStOut();
        $arrReturn['data']['list'] = [];
        //参数处理
        $iMovieId = self::getParam($arrInput, 'movieId');
        $iCityId = self::getParam($arrInput, 'cityId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        //数据处理
        if (!empty($iChannelId) && !empty($iMovieId) && !empty($iCityId)) {
            //读取单品推荐信息
            $arrData = $this->model('Movie')->readMovieRecommendedNewsStatic($iChannelId);
            if (!empty($arrData) && is_array($arrData)) {
                $arrReturn['data']['list'] = [];
                //组合各种可能的情况
                $arrData1 = $arrData2 = [];
                //1、判断地区不限地区的情况
                $arrData1 = !empty($arrData[$iChannelId][0][$iMovieId]) ? $arrData[$iChannelId][0][$iMovieId] : [];
                //2、判断正常情况
                $arrData2 = !empty($arrData[$iChannelId][$iCityId][$iMovieId]) ? $arrData[$iChannelId][$iCityId][$iMovieId] : [];
                //3、合并，并去重，并且同一个影片的推荐信息，只取前2个
                $arrList = [];
                $arrMergeData = array_merge($arrData1, $arrData2);
                foreach ($arrMergeData as $arrRecommendInfo) {
                    $arrList[$arrRecommendInfo['id']] = $arrRecommendInfo;
                    if (count($arrList) == 2) {
                        break;
                    }
                }
                $arrMergeData = null;
                $arrList = array_values($arrList);
                //4、排序
                array_multisort(Utils::array_column($arrList, 'order'), SORT_DESC, $arrList);
                //5、合并有效数据
                $arrReturn['data']['list'] = $arrList;
            }
        }

        return $arrReturn;
    }

    /**
     * 按条件，获取即将上映的影片列表
     * 目前有2个条件，一个是按照日期分组，一种是按照想看人数降序排序
     *
     * @param int              channelId 渠道id
     * @param int              cityId    城市id
     * @param int              uid       用户中心id
     * @param string           openId    用户openId
     * @param string           unionId   用户unionId
     * @param int              year      年份
     * @param int              month     月份
     * @param int              state     状态，1标识获取全部，2标识获取某模糊年的数据，3标识获取某月（包括确定日期和不确定日期的），4标识获取某年所有（包括某年下所有月份）
     *                         5表示分页获取，默认获取第一页
     * @param int              page      页码
     *
     * @param string
     */
    public function getMovieWillWithDateNewStatic($arrInput = [])
    {
        $arrReturn = static::getStOut();
        $arrReturn['data']['list'] = [];
        //获取数据
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iCityId = self::getParam($arrInput, 'cityId');
        $uid = self::getParam($arrInput, 'uid');
        $strOpenId = self::getParam($arrInput, 'openId');
        $strUnionId = self::getParam($arrInput, 'unionId');
        $strYear = self::getParam($arrInput, 'year');
        $strMonth = self::getParam($arrInput, 'month');
        $iState = self::getParam($arrInput, 'state');
        $iPage = self::getParam($arrInput, 'page', 1);
        $arrMovieWillWithDate = $this->model('Movie')->getMovieWillWithDateNewStatic($iChannelId, $iCityId, $strYear, $strMonth, $iState, $iPage);
        //格式化，添加user_want
        if (!empty($arrMovieWillWithDate['list']) && is_array($arrMovieWillWithDate['list'])) {
            //格式化
            foreach ($arrMovieWillWithDate['list'] as &$arrMovieWillList) {
                if (!empty($arrMovieWillList['list'])) {
                    foreach ($arrMovieWillList['list'] as &$arrListData) {
                        if (!empty($arrListData['list']) && is_array($arrListData['list'])) {
                            $this->_formatMovieListStatic($arrListData['list'], $iChannelId, $uid, $strOpenId, $strUnionId, $iCityId);
                        }
                    }
                }
            }
            $arrReturn['data'] = $arrMovieWillWithDate;
        }

        return $arrReturn;
    }

    /**
     * 获取某个影片的明星选座数据
     *
     * @param string movieId   影片编号
     * @param string channelId  渠道编号
     *
     * @return array
     */
    public function getMovieCustomSeatNewStatic(array $arrInput = [])
    {
        //参数整理
        $iMovieId = self::getParam($arrInput, 'movieId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        $return = self::getStOut();
        //参数判断
        if (empty($iMovieId) || empty($iChannelId)) {
            $return['data']['customization_seats'] = new \stdClass();
        } else {
            $objMovieCustomSeat = $this->model('movie')->readMovieCustomSeatNewStatic($iChannelId, $iMovieId);
            $return['data']['customization_seats'] = $objMovieCustomSeat;
        }

        return $return;
    }

    /**
     * 获取城市下即将上映的影片列表
     * 新版crontask_new数据源
     *
     * @param string $cityId 城市Id
     * @param string $channelId 渠道编号
     * @param        string     sortField 排序字段，如：wantCount、date
     * @param        string     sortOrder 排序条件，如：asc、desc
     *
     * @return array
     */
    public function readMovieWillNewStatic(array $arrInput = [])
    {
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iCityId = self::getParam($arrInput, 'cityId');
        $uid = self::getParam($arrInput, 'uid');
        $openId = self::getParam($arrInput, 'openId');
        $unionId = self::getParam($arrInput, 'unionId');
        $strSortField = self::getParam($arrInput, 'sortField');
        $strSortOrder = self::getParam($arrInput, 'sortOrder');
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        if (empty($iCityId) || empty($iChannelId)) {
            return $return;
        }
        $arrData = $this->model('movie')->readMovieWillNewStatic($iChannelId, $iCityId);
        //判断是否需要排序（有排序条件、且数据列表不为空，且列表中的对象，包含要排序的字段）
        if ( !empty($strSortField) && !empty($arrData) && (is_array($arrData)) && isset($arrData[0][$strSortField])) {
            $sortOrder = ($strSortOrder == 'desc') ? SORT_DESC : SORT_ASC;
            array_multisort(Utils::array_column($arrData, $strSortField), $sortOrder, $arrData);
        }
        if ( !empty($arrData)) {
            $this->_formatMovieListStatic($arrData, $iChannelId, $uid, $openId, $unionId, $iCityId);
        }
        $return['data'] = $arrData;

        return $return;
    }

    /**
     * 获取影片的评分数据集基本影片数据
     * 新版crontask_new数据源
     *
     * @param array $arrInput
     *
     * @return array
     */
    public function readMovieAndScoreNewStatic(array $arrInput = [])
    {
        $iChannelId = self::getParam($arrInput, 'channelId');
        $arrMovieIds = self::getParam($arrInput, 'movieIds');
        $return = self::getStOut();
        $return['data'] = [];
        if (empty($arrMovieIds) || empty($iChannelId)) {
            return $return;
        }
        $arrMovieScores = $this->model('movie')->readMovieAndScoreNewStatic($iChannelId, $arrMovieIds);
        $return['data'] = $arrMovieScores;

        return $return;
    }

    /**
     * 仅仅获取影片评分数据
     * 新版crontask_new数据源
     *
     * @param array $arrInput
     *
     * @return array
     */
    public function readMovieScoreNewStatic(array $arrInput = [])
    {
        $iChannelId = self::getParam($arrInput, 'channelId');
        $arrMovieIds = self::getParam($arrInput, 'movieIds');
        $return = self::getStOut();
        $return['data'] = [];
        if (empty($arrMovieIds) || empty($iChannelId)) {
            return $return;
        }
        $arrMovieScores = $this->model('movie')->readMovieScoreNewStatic($iChannelId, $arrMovieIds);
        $return['data'] = $arrMovieScores;

        return $return;
    }

    /**
     * 根据影片ids, 获取这些片子的详情信息
     * 新版crontask_new数据源
     *
     * @param array $arrInput
     *
     * @return array
     */
    public function readMovieInfoByIdsNewStatic(array $arrInput = [])
    {
        $iChannelId = self::getParam($arrInput, 'channelId');
        $arrMovieIds = self::getParam($arrInput, 'movieIds');
        $return = self::getStOut();
        $return['data'] = [];
        if (empty($arrMovieIds) || empty($iChannelId)) {
            return $return;
        }
        foreach ($arrMovieIds as $iMovieId) {
            $data = $this->model('movie')->readMovieInfoNewStatic($iChannelId, $iMovieId);
            if ( !empty($data)) {
                $return['data'][] = $data;
            }
        }

        return $return;
    }

    /**
     * 格式化影片列表 [正在热映\即将上映中添加用户想看标识 APP用]
     *
     * @param        Array   arrData  需要格式化的数组
     * @param        int     channelId 渠道号
     * @param        int     uid  用户的uid
     * @param        string  openId 用户的openId
     * @param        string  unionId 用户的unionId
     * @param        string  cityId 城市Id
     */
    private function _formatMovieListStatic(&$arrData, $channelId)
    {
        static $arrWantMovieIds = null;
        if (is_null($arrWantMovieIds)) {
            //获取用户想看的影片列表
            $arrWantMovieIds = $this->getUserWantMovieIds($channelId);
            if (empty($arrWantMovieIds)) {
                $arrWantMovieIds = [];
            }
        }

        ### 注：#号注释，2016-05-18修改，由于该字段目前没用到，为防止评论崩溃导致拖慢静态数据接口，先将其写死为0
        #//获取用户想看列表
        #$params = [
        #    'uid' => $uid,
        #    'openId' => $openId,
        #    'unionId' => $unionId,
        #    'channelId' => $channelId,
        #    'page' => 1,
        #    'num' => 99999,
        #];
        #$wantMovies = [];
        #$retSDK = $this->service('comment')->getUserWantMovie($params);
        #if ($retSDK['ret'] == 0 AND $retSDK['sub'] == 0) {
        #    if (!empty($retSDK['data'])) {
        #        $wantMovies = $retSDK['data']['movies'];
        #    }
        #}
        foreach ($arrData as $k => $v) {
            $arrData[$k]['user_want'] = 0;
            if ( !empty($arrData[$k]['id']) && in_array($arrData[$k]['id'], $arrWantMovieIds)) {
                $arrData[$k]['user_want'] = 1;
            }
            #if (in_array($v['id'], $wantMovies)) {
            #    $arrData[$k]['user_want'] = 1;
            #} else {
            #    $arrData[$k]['user_want'] = 0;
            #}
            //获取单片推荐资讯信息
            //$arrMovieRecommendedNews = $this->readMovieRecommendedNews(['channelId' => $channelId, 'cityId' => $iCityId, 'movieId' => $v['id']]);
            //$arrData[$k]['recommended_news'] = ( ( $arrMovieRecommendedNews['ret'] == 0 ) && !empty( $arrMovieRecommendedNews['data']['list'] ) ) ? $arrMovieRecommendedNews['data']['list'] : [];
        }
    }

    /**
     * 获取用户想看的影片列表
     *
     * @param string $channelId 渠道id
     * @param string $token     token，微信和手Q的，可以传进来也可以不传，至于APP，只能在自己项目里格式化，因为token传不进来
     *
     * @return array
     */
    public function getUserWantMovieIds($channelId = '', $token = '')
    {
        $return = [];
        //参数处理
        $arrSendParams = [];
        $arrSendParams['token'] = $token;
        $arrSendParams['channelId'] = $channelId;
        $arrSendParams['page'] = 1;
        $arrSendParams['num'] = 1000;
        if (empty($token)) {
            if ($channelId == '3') {
                $arrSendParams['token'] = self::getParam($_COOKIE, serviceWechatLogin::WXOPENID);
            } elseif ($channelId == '28') {
                $arrSendParams['token'] = self::getParam($_COOKIE, serviceMqq::MQQOPENID);
            }
        }
        //
        if (empty($arrSendParams['token']) || empty($channelId)) {
            return $return;
        }
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'GET',
        ];
        //因为微信渠道特殊对待
        if ($channelId == 3) {
            $httpParams['arrCookies'] = [serviceWechatLogin::WXOPENID => $arrSendParams['token']];
        }
        $res = $this->http(COMMENT_CENTER_USER_WANT_MOVIES, $httpParams);
        if ($res['ret'] == 0 && $res['sub'] == 0 && !empty($res['data'])) {
            $return = $res['data']['movies'];
        }

        return $return;
    }

    /**
     * 获取城市下影片列表
     * V2相比V1, 增加了flags字段, 之所以采用新增方法, 而不是在原来上改, 主要是因为底层字段虽然差不多, 但是生成方式, 差很多
     *
     * @param  string $cityId 城市Id
     * @param  string $channelId 渠道编号
     * @param  int $page 页码
     * @param  int $num 每页的数量
     * @param  int $uid 用户id
     * @param  string $openId 用户OpenId
     * @param  string $unionId 用户unionid
     * @param  int $formatMovieWant 是否关闭格式化用户想看字段，默认是格式化，如果需要关闭，请传数字0。
     *
     * @return array
     */
    public function readCityMovieByPageNewStaticV2(array $arrInput = [])
    {
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iCityId = self::getParam($arrInput, 'cityId');
        $uid = self::getParam($arrInput, 'uid');
        $openId = self::getParam($arrInput, 'openId');
        $unionId = self::getParam($arrInput, 'unionId');
        $page = self::getParam($arrInput, 'page');
        $num = self::getParam($arrInput, 'num');
        $formatMovieWant = self::getParam($arrInput, 'formatMovieWant', 1);
        $return = self::getStOut();

        $arrData = $this->model('movie')->readCityMovieByPageNewStaticV2($iChannelId, $iCityId, $page, $num);
        //看是否需要格式化影片中的看过标识
        if (($formatMovieWant == 1) && !empty($arrData['list']) && (!empty($uid) || !empty($unionId) || !empty($openId))) {
            //因为正在热映的影片列表在info数组下所以格式化时格式info数组中的内容
            $this->_formatMovieListStatic($arrData['list'], $iChannelId, $uid, $openId, $unionId, $iCityId);
        }
        $return['data'] = $arrData;

        return $return;
    }

    //——————————————————————————————————————— 新版crontask_new方法 end ——————————————————————————————————//
    /**
     * 格瓦拉影片id获取娱票儿影片id
     * @param array $arrInput
     * @return mixed|string
     */
    public function GewaraGetWxMovieId(array $arrInput = [])
    {
        $return = self::getStOut();
        $movieId = self::getParam($arrInput, 'movieId');
        $GewaraMovieIds = include CGI_APP_PATH . 'app/config/GewaraMovieId.php';
        $WxMovieId = isset($GewaraMovieIds[$movieId]) ? $GewaraMovieIds[$movieId] : '';
        $return['data']['movieId'] = $WxMovieId;
        return $return;
    }

    /**
     * 格瓦拉影片id获取娱票儿影片id
     * @param array $arrInput
     * @return mixed|string
     */
    public function GewaraGetWxActorId(array $arrInput = [])
    {
        $return = self::getStOut();
        $actorId = self::getParam($arrInput, 'actorId');
        $GewaraActorIds = include CGI_APP_PATH . 'app/config/GewaraActor.php';
        $WxActorId = isset($GewaraActorIds[$actorId]) ? $GewaraActorIds[$actorId] : '';
        $return['data']['actorId'] = $WxActorId;
        return $return;
    }

    /**
     *  格瓦拉影片id获取娱票儿影片id(redis)
     * @param array $arrInput
     * @return mixed|string
     */
    public function GewaraGetWxMovieIdFromDb(array $arrInput = [])
    {
        $return = self::getStOut();
        $movieId = self::getParam($arrInput, 'movieId');
        $WxMovieId = $this->model('movie')->gewaraGetWxMovieIdFromDb($movieId);
        $WxMovieId = $WxMovieId ? $WxMovieId : '';
        $return['data']['movieId'] = $WxMovieId;
        return $return;
    }
    /**
     *  通知格瓦拉影片id获取娱票儿影片id(redis)
     * @param array $arrInput
     * @return mixed|string
     */
    public function gewaraSetWxMovieIdToDb(array $arrInput = [])
    {
        $return = self::getStOut();
        $gewaraMovieId = self::getParam($arrInput, 'gewaraMovieId');
        $ypMovieId = self::getParam($arrInput, 'ypMovieId');
        $this->model('movie')->gewaraSetWxMovieIdToDb($gewaraMovieId, $ypMovieId);
        return $return;
    }
    /**
     * 格瓦拉影片id获取娱全量票儿影片id(redis)
     * @return array
     */
    public function GewaraGetAllWxMovieIdFromDb()
    {
        $return = self::getStOut();
        $WxMovieIds = $this->model('movie')->gewaraGetAllWxMovieIdFromDb();
        $return['data'] = $WxMovieIds ? $WxMovieIds : new \stdClass();
        return $return;
    }

    /**
     * 获取MSDB预告片列表
     *
     * @param  string $movieId   影片id
     * @param  string $channelId 渠道编号
     * @param  int    $page      页码
     * @param  int    $num       每页的数量
     *
     * @return array
     */
    public function readMovieVideosNewStatic(array $arrInput = [])
    {
        $return = self::getStOut();
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iMovieId = self::getParam($arrInput, 'movieId');
        $iCityId = self::getParam($arrInput, 'cityId');
        $page = self::getParam($arrInput, 'page');
        $num = self::getParam($arrInput, 'num');
        $return['data'] = ['list' => [], 'total_row' => 0, 'total_page' => 0, 'page' => 1, 'num' => 10, 'movie_name' => '', 'score' => 0];
        if ( !empty($iChannelId) && !empty($iMovieId) && !empty($page) && !empty($num)) {
            $return['data'] = $this->model('movie')->readMovieVideosNewStatic($iChannelId, $iMovieId, $page, $num);
        }
        //添加点赞次数
        $arrMovieVideoFavors = [];
        //调用接口
        $httpParams = [
            'arrData' => [],
            'sMethod' => 'GET',
        ];
        $strUrl = sprintf(COMMENT_MOVIR_VIDEO_FAVORS, $iMovieId);
        $res = $this->http($strUrl, $httpParams);
        if ($res['ret'] == 0 && $res['sub'] == 0 && !empty($res['data']) && !empty($return['data']['list'])) {
            foreach ($return['data']['list'] as &$arrVideo) {
                if ( !empty($res['data'][$arrVideo->vid])) {
                    $arrVideo->favor_count = intval($res['data'][$arrVideo->vid]);
                }
            }
        }
        //添加影片信息
        $movieInfo = $this->readMovieAndScoreNewStatic(['channelId' => $iChannelId, 'movieIds' => [$iMovieId]]);
        if ( !empty($movieInfo['data'])) {
            $return['data']['movie_id'] = $iMovieId;
            $return['data']['movie_name'] = !empty($movieInfo['data'][$iMovieId]['name']) ? $movieInfo['data'][$iMovieId]['name'] : '';
            $return['data']['score'] = !empty($movieInfo['data'][$iMovieId]['initScore']) ? 0 + $movieInfo['data'][$iMovieId]['initScore'] : 0;
        }

        //添加该影片多少家影院，多少场次在售卖
        $return['data']['sche_statistics']=['cinema_count' => 0];
        if ( !empty($iMovieId) && !empty($iCityId)) {
            $arrParams = ['channelId' => 8, 'cityId' => $iCityId, 'movieId' => $iMovieId];
            $soldStaticRes = $this->service('search')->searchCinemaFiltersV2($arrParams);
            if ( !empty($soldStaticRes['data']['cinema_movie_count']) && is_numeric($soldStaticRes['data']['cinema_movie_count'])) {
                $return['data']['sche_statistics']['cinema_count'] = $soldStaticRes['data']['cinema_movie_count'];
            }
        }

        return $return;
    }

    /**
     *  微信礼品卡入口
     * @param array $arrInput
     * @return mixed|string
     */
    public function GetGiftCardEntry(array $arrInput = [])
    {
        $return = self::getStOut();
        $ChannelId = self::getParam($arrInput, 'channelId');
        $MovieId = self::getParam($arrInput, 'movieId');
        $res = $this->model('movie')->GetGiftCardEntry($ChannelId,$MovieId);
        if(!empty($res) && is_array($res) ){
            $return['data'] = $res;
        }
        return $return;
    }

    /**
     * 获取即将上映的预览数据（包括：明星见面会、新片抢先、最热推荐）
     * @param array $arrInput
     * @return array
     */
    public function getMovieWillPreview(array $arrInput = [])
    {
        $return = self::getStOut();
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iCityId = self::getParam($arrInput, 'cityId');
        $arrData = $this->model('Movie')->getMovieWillPreview($iChannelId, $iCityId);
        if (!empty($arrData)) {
            /**
             * 格式化明星见面会
             * ①：取最近的一天的
             * ②：如果最近一天的明星见面会有多个，则展示未展示过的场次最近的。如果都展示过了，当天的随机
             */
            $arrShowedStarMeet = !empty($_COOKIE['wepiao_star_meet_showed']) ? unserialize($_COOKIE['wepiao_star_meet_showed']) : [];
            if (!empty($arrData['star_meet'])) {
                $starMeetLeft = $arrData['star_meet'];
                //如果有当前的，且一个，只展示当天的，如果当天有多个，则展示未展示过的，如果当天的多的且都展示过了，随机
                $lastShowScheDate = '';
                $lastShowScheDateMeet = [];
                $nowDate = date('Ymd');
                //1、获取最近的场次日期和对应的明星见面会
                foreach ($arrData['star_meet'] as $key => $arrStarMeet) {
                    $scheDate = date('Ymd', $arrStarMeet['sche_time']);
                    if (empty($lastShowScheDate) || ($scheDate <= $lastShowScheDate)) {
                        $lastShowScheDate = $scheDate;
                    }
                    if (empty($lastShowScheDateMeet[$lastShowScheDate])) {
                        $lastShowScheDateMeet[$lastShowScheDate] = [];
                    }
                    $lastShowScheDateMeet[$lastShowScheDate][] = $arrStarMeet;
                }
                //2、获取最近场次日期，对应的明星见面会
                $arrValidMeet = !empty($lastShowScheDateMeet[$lastShowScheDate]) ? $lastShowScheDateMeet[$lastShowScheDate] : [];
                if (!empty($arrValidMeet)) {
                    $cookies = [];
                    //只有一个的情况
                    if (count($arrValidMeet) == 1) {
                        $arrData['star_meet'] = [$arrValidMeet[0]];
                        //重写cookie，把这个即将展示的cookie，写入到已展示的数据中
                        $arrShowedStarMeet[] = $arrData['star_meet'][0]['sche_id'];
                        $cookies = [[
                            'key' => 'wepiao_star_meet_showed',
                            'value' => serialize($arrShowedStarMeet),
                            'expire' => time() + serviceWechatLogin::UNIONID_EXPIRE,
                            'path' => serviceWechatLogin::COOKIE_PATH,
                            'domain' => serviceWechatLogin::COOKIE_DOMAIN,
                        ]];
                    } //多个的情况
                    else {
                        $starMeetLeft = $arrValidMeet;
                        //明星见面会中，去除已经展示过的
                        if (!empty($arrShowedStarMeet)) {
                            foreach ($arrValidMeet as $key => $arrStarMeet) {
                                if (in_array($arrStarMeet['sche_id'], $arrShowedStarMeet)) {
                                    unset($starMeetLeft[$key]);
                                }
                            }
                        }
                        //如果有没给用户展示过的，取第一个（第一个排期时间最近）
                        if (!empty($starMeetLeft)) {
                            $arrData['star_meet'] = [array_shift($starMeetLeft)];
                            //重写cookie，把这个即将展示的cookie，写入到已展示的数据中
                            $arrShowedStarMeet[] = $arrData['star_meet'][0]['sche_id'];
                            $cookies = [[
                                'key' => 'wepiao_star_meet_showed',
                                'value' => serialize($arrShowedStarMeet),
                                'expire' => time() + serviceWechatLogin::UNIONID_EXPIRE,
                                'path' => serviceWechatLogin::COOKIE_PATH,
                                'domain' => serviceWechatLogin::COOKIE_DOMAIN,
                            ]];
                        } else {
                            shuffle($arrValidMeet);
                            $arrData['star_meet'] = [array_shift($arrValidMeet)];
                        }
                    }
                    //写cookie
                    if (!empty($cookies)) {
                        foreach ($cookies as $arrCookie) {
                            setcookie($arrCookie['key'], $arrCookie['value'], $arrCookie['expire'], $arrCookie['path'], $arrCookie['domain']);
                        }
                    }
                }
            }
            /**
             * 格式化新片抢先（随机展示一个）
             */
            if (!empty($arrData['movie_forestall'])) {
                shuffle($arrData['movie_forestall']);
                $arrData['movie_forestall'] = [array_shift($arrData['movie_forestall'])];
                //获取实时购票数
                if (!empty($arrData['movie_forestall'])) {
                    $iMovieId = !empty($arrData['movie_forestall'][0]['id']) ? $arrData['movie_forestall'][0]['id'] : '';
                    if (!empty($iMovieId)) {
                        $arrMovieBuyRes = $this->service('order')->getMovieBuyNum(['channelId' => $iCityId, 'movieId' => $iMovieId]);
                        $arrData['movie_forestall'][0]['buy_num'] = !empty($arrMovieBuyRes['data']['buy_num']) ? $arrMovieBuyRes['data']['buy_num'] : 0;
                    }
                }
            }
            /**
             * 用户"看过"格式化
             */
            if (!empty($arrData['movie_hot'])) {
                $this->_formatMovieListStatic($arrData['movie_hot'], $iChannelId);
            }
            $return['data'] = $arrData;
        }

        return $return;
    }


    /**
     * 获取正在上映的影片ids
     * 目前仅腾讯方需要用
     * @param array $arrInput
     * @return array
     */
    public function getHotMovieIds($arrInput = []){
        $return = self::getStOut();
        $iChannelId = self::getParam($arrInput, 'channelId',28);
        $WxMovieIds = $this->model('movie')->getHotMovieIds($iChannelId);
        $movieids=$WxMovieIds ? array_values(json_decode($WxMovieIds,true)) : [];
        $return['data']['movieIdList'] = $movieids;
        return $return;
    }

    /**
      * app新版主页中获取即将上映列表(20条)
      * @param array $arrInput
      * @return array
     */
    public function getMovieWillPreviewList(array $arrInput = [])
    {
        $return = self::getStOut();
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iCityId = self::getParam($arrInput, 'cityId');
        $num = self::getParam($arrInput, 'num', 20);
        $ret = $this->model('movie')->getMovieWillPreview($iChannelId, $iCityId);
        if (!empty($ret) && !empty($ret['movie_hot'])) {
            $return['data'] = array_slice($ret['movie_hot'], 0, $num);
        }
        return $return;
    }
}
