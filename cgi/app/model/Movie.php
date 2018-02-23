<?php

namespace sdkService\model;


class Movie extends BaseNew
{
    
    /**
     * 读取正在上映影片信息
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId    城市id
     *
     * @return array
     */
    public function readCityMovie($iChannelId, $iCityId)
    {
        if ($this->isRestructChannel($iChannelId)) {
            $data = $this->readCityMovieOnRestruct($iChannelId, $iCityId);
        } else {
            $data = $this->readCityMovieOnCronTask($iChannelId, $iCityId);
        }
        
        return $this->helper('OutPut')->jsonConvert($data);
    }
    
    /**
     * 读取即将上映影片信息
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId    城市id
     *
     * @return array
     */
    public function readMovieWill($iChannelId, $iCityId)
    {
        if ($this->isRestructChannel($iChannelId)) {
            $data = $this->readMovieWillOnRestruct($iChannelId, $iCityId);
        } else {
            $data = $this->readMovieWillOnCronTask($iChannelId, $iCityId);
        }
        
        return $this->helper('OutPut')->jsonConvert($data);
    }
    
    /**
     * 影片详情信息
     *
     * @param string $iChannelId 渠道编号
     * @param string $iMovieId   影片id
     *
     * @return array
     */
    public function readMovieInfo($iChannelId, $iMovieId)
    {
        if ($this->isRestructChannel($iChannelId)) {
            $data = $this->readMovieInfoOnRestruct($iChannelId, $iMovieId);
        } else {
            $data = $this->readMovieInfoOnCronTask($iChannelId, $iMovieId);
        }
        
        return $this->helper('OutPut')->jsonConvert($data);
    }
    
    
    //获取全部movieId,脚本使用
    public function getAllMovieIdCommand($start, $end)
    {
        $this->pdo('dbApp', 't_score');
        $limit = $start . ',' . $end;
        $sql = 'select id from t_movie order by wantCount desc limit ' . $limit;
        $dbRe = $this->pdohelper->fetchArrayBySql($sql);
        
        return $dbRe;
    }
    
    /**
     * 根据影片id，读取影片详情信息
     *
     * @param string $iChannelId 渠道编号
     * @param string $iMovieId   影片id
     *
     * @return array
     */
    public function readMovieInfos($iChannelId, $arrMovieIds = [])
    {
        if ($this->isRestructChannel($iChannelId)) {
            $data = $this->readMovieInfosOnRestruct($iChannelId, $arrMovieIds);
        } else {
            $data = $this->readMovieInfosOnCronTask($iChannelId, $arrMovieIds);
        }
        
        return $data;
    }
    
    
    //——————————————private——————————————//
    
    
    /**
     * 从原版CronTask读取正在上映影片信息
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId    城市id
     *
     * @return array
     */
    private function readCityMovieOnCronTask($iChannelId, $iCityId)
    {
        $strKey = KEY_CITY_MOVIE_LIST;
        $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhGet($strKey, $iCityId);
        
        return $data;
    }
    
    /**
     * 从重构版CronTask读取正在上映影片信息
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId    城市id
     *
     * @return array
     */
    private function readCityMovieOnRestruct($iChannelId, $iCityId)
    {
        $strKey = STATIC_KEY_CITY_MOVIE_LIST . $iCityId;
        $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYget($strKey);
        
        return $data;
    }
    
    /**
     * 从原版CronTask读取正在上映影片信息
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId    城市id
     *
     * @return array
     */
    private function readMovieWillOnCronTask($iChannelId, $iCityId)
    {
        $strKey = KEY_CITY_MOVIE_WILL;
        $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhGet($strKey, $iCityId);
        
        return $data;
    }
    
    /**
     * 从重构版CronTask读取正在上映影片信息
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId    城市id
     *
     * @return array
     */
    private function readMovieWillOnRestruct($iChannelId, $iCityId)
    {
        $strKey = STATIC_KEY_CITY_MOVIE_WILL . $iCityId;
        $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYget($strKey);
        
        return $data;
    }
    
    /**
     * 从原版CronTask读取影片详情信息
     *
     * @param string $iChannelId 渠道编号
     * @param string $iMovieId   影片id
     *
     * @return array
     */
    private function readMovieInfoOnCronTask($iChannelId, $iMovieId)
    {
        $strKey = KEY_MOVIE_INFO;
        $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhGet($strKey, $iMovieId);
        
        return $data;
    }
    
    /**
     * 从重构版CronTask读取影片详情信息
     *
     * @param string $iChannelId 渠道编号
     * @param string $iMovieId   影片id
     *
     * @return array
     */
    private function readMovieInfoOnRestruct($iChannelId, $iMovieId)
    {
        $strKey = STATIC_KEY_MOVIE_INFO;
        $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhGet($strKey, $iMovieId);
        
        return $data;
    }
    
    /**
     * 根据影片id，从原版CronTask读取影片详情信息
     *
     * @param string $iChannelId  渠道编号
     * @param array  $arrMovieIds 影片id组成的数组
     *
     * @return array
     */
    private function readMovieInfosOnCronTask($iChannelId, $arrMovieIds)
    {
        $strKey = KEY_MOVIE_INFO;
        $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhMGet($strKey, $arrMovieIds);
        if (is_array($data) && !empty($data)) {
            foreach ($data as &$value) {
                if (empty($value)) {
                    continue;
                }
                $value = json_decode($value, true);
            }
        }
        
        return $data;
    }
    
    /**
     * 根据影片id，从重构版CronTask读取影片详情信息
     *
     * @param string $iChannelId  渠道编号
     * @param array  $arrMovieIds 影片id组成的数组
     *
     * @return array
     */
    private function readMovieInfosOnRestruct($iChannelId, $arrMovieIds)
    {
        $strKey = STATIC_KEY_MOVIE_INFO;
        $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhMGet($strKey, $arrMovieIds);
        $data = array_filter($data);
        if (is_array($data) && !empty($data)) {
            foreach ($data as &$value) {
                if (empty($value)) {
                    continue;
                }
                $value = json_decode($value, true);
            }
        }
        
        return $data;
    }
    
    //—————————————————————————————————— 读取正在上映影片信息，支持分页 begin ——————————————————————————————————//
    
    /**
     * 读取正在上映影片信息，支持分页
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId    城市id
     * @param int    $page       页码
     * @param int    $num        每页条数
     *
     * @return array
     */
    public function readCityMovieByPage($iChannelId, $iCityId, $page = 1, $num = 10)
    {
        if ($this->isRestructChannel($iChannelId)) {
            $data = $this->readCityMovieByPageOnRestruct($iChannelId, $iCityId, $page, $num);
        } else {
            $iFromBigData = !empty(\wepiao::$config['movieListAndFlagBigData']) ? \wepiao::$config['movieListAndFlagBigData'] : 0;
            if ($iFromBigData) {
                $data = $this->readCityMovieByPageOnCronTaskV3($iChannelId, $iCityId, $page, $num);
            } else {
                if ($iChannelId == 3) {
                    $data = $this->readCityMovieByPageOnCronTaskV2($iChannelId, $iCityId, $page, $num);
                } else {
                    $data = $this->readCityMovieByPageOnCronTask($iChannelId, $iCityId, $page, $num);
                }
            }
        }
        
        return $data;
    }
    
    /**
     * 从原版CronTask读取正在上映影片信息，支持分页
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId    城市id
     * @param int    $page       页码
     * @param int    $num        每页条数
     *
     * @return array
     */
    private function readCityMovieByPageOnCronTask($iChannelId, $iCityId, $page, $num)
    {
        $return = ['list' => [], 'total_row' => 0, 'total_page' => 0, 'page' => 1, 'num' => 10];
        if ( !empty($iCityId) && !empty($iChannelId)) {
            $page = ( !empty($page) && ($page > 0)) ? intval($page) : 1;
            $num = ( !empty($num) && ($num > 0)) ? intval($num) : 10;
            //拼装返回值中的页码和单页条数
            $return['page'] = $page;
            $return['num'] = $num;
            //获取分页查询的范围
            $offsetStart = ($page - 1) * $num;
            $offsetEnd = $page * $num - 1;
            $strMovieListSortedKey = KEY_CITY_MOVIE_LIST_SORT . $iCityId;
            //获取总数
            $count = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYzCard($strMovieListSortedKey);
            $return['total_row'] = intval($count);
            $return['total_page'] = ceil($count / $num);
            //影片列表id的有序集合
            $sortedMovieIds = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYzRange($strMovieListSortedKey, $offsetStart, $offsetEnd);
            //根据影片id，查询hash结构，得到影片列表
            if ( !empty($sortedMovieIds)) {
                $strMovieListDataKey = KEY_CITY_MOVIE_LIST_HASH . $iCityId;
                $arrMovieList = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhMGet($strMovieListDataKey, $sortedMovieIds);
                foreach ($arrMovieList as $strListInfo) {
                    if ( !empty($strListInfo)) {
                        $return['list'][] = json_decode($strListInfo, true);
                    }
                }
            }
        }
        
        return $return;
    }
    
    /**
     * 从原版CronTask读取正在上映影片信息，支持分页
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId    城市id
     * @param int    $page       页码
     * @param int    $num        每页条数
     *
     * @return array
     */
    private function readCityMovieByPageOnCronTaskV2($iChannelId, $iCityId, $page, $num)
    {
        $return = ['list' => [], 'total_row' => 0, 'total_page' => 0, 'page' => 1, 'num' => 10];
        if ( !empty($iCityId) && !empty($iChannelId)) {
            $page = ( !empty($page) && ($page > 0)) ? intval($page) : 1;
            $num = ( !empty($num) && ($num > 0)) ? intval($num) : 10;
            //拼装返回值中的页码和单页条数
            $return['page'] = $page;
            $return['num'] = $num;
            //获取分页查询的范围
            $offsetStart = ($page - 1) * $num;
            $offsetEnd = $page * $num - 1;
            $strMovieListSortedKey = KEY_CITY_MOVIE_LIST_V2 . $iCityId . '_' . rand(1, 10);
            //获取总数
            $count = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYzCard($strMovieListSortedKey);
            $return['total_row'] = intval($count);
            $return['total_page'] = ceil($count / $num);
            //影片列表id的有序集合
            $arrMovieList = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYzRange($strMovieListSortedKey, $offsetStart, $offsetEnd);
            //根据影片id，查询hash结构，得到影片列表
            if ( !empty($arrMovieList)) {
                foreach ($arrMovieList as $strListInfo) {
                    if ( !empty($strListInfo)) {
                        $return['list'][] = json_decode($strListInfo, true);
                    }
                }
            }
        }
        
        return $return;
    }
    
    /**
     * 从原版CronTask读取正在上映影片信息，支持分页
     * 这个方法,是走我们的redis,但是数据源是来自于苏利军
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId    城市id
     * @param int    $page       页码
     * @param int    $num        每页条数
     *
     * @return array
     */
    private function readCityMovieByPageOnCronTaskV3($iChannelId, $iCityId, $page, $num)
    {
        $return = ['list' => [], 'total_row' => 0, 'total_page' => 0, 'page' => 1, 'num' => 10];
        if ( !empty($iCityId) && !empty($iChannelId)) {
            $page = ( !empty($page) && ($page > 0)) ? intval($page) : 1;
            $num = ( !empty($num) && ($num > 0)) ? intval($num) : 10;
            //拼装返回值中的页码和单页条数
            $return['page'] = $page;
            $return['num'] = $num;
            //获取分页查询的范围
            $offsetStart = ($page - 1) * $num;
            $offsetEnd = $page * $num - 1;
            $strMovieListSortedKey = KEY_CITY_MOVIE_LIST_V3 . $iCityId . '_' . rand(1, 10);
            //获取总数
            $count = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYzCard($strMovieListSortedKey);
            $return['total_row'] = intval($count);
            $return['total_page'] = ceil($count / $num);
            //影片列表id的有序集合
            $arrMovieList = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYzRange($strMovieListSortedKey, $offsetStart, $offsetEnd);
            //根据影片id，查询hash结构，得到影片列表
            if ( !empty($arrMovieList)) {
                foreach ($arrMovieList as $strListInfo) {
                    if ( !empty($strListInfo)) {
                        $return['list'][] = json_decode($strListInfo, true);
                    }
                }
            }
        }
        
        return $return;
    }

    /**
     * 从重构版CronTask读取正在上映影片信息
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId    城市id
     *
     * @return array
     */
    private function readCityMovieByPageOnRestruct($iChannelId, $iCityId, $page, $num)
    {
        $return = ['list' => [], 'total_row' => 0, 'total_page' => 0, 'page' => 1, 'num' => 10];
        if ( !empty($iCityId) && !empty($iChannelId)) {
            $page = ( !empty($page) && ($page > 0)) ? intval($page) : 1;
            $num = ( !empty($num) && ($num > 0)) ? intval($num) : 10;
            //拼装返回值中的页码和单页条数
            $return['page'] = $page;
            $return['num'] = $num;
            //获取分页查询的范围
            $offsetStart = ($page - 1) * $num;
            $offsetEnd = $page * $num - 1;
            $strMovieListSortedKey = KEY_CITY_MOVIE_LIST_SORT . $iCityId;
            //获取总数
            $count = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYzCard($strMovieListSortedKey);
            $return['total_row'] = intval($count);
            $return['total_page'] = ceil($count / $num);
            //影片列表id的有序集合
            $sortedMovieIds = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYzRange($strMovieListSortedKey, $offsetStart, $offsetEnd);
            //根据影片id，查询hash结构，得到影片列表
            if ( !empty($sortedMovieIds)) {
                $strMovieListDataKey = KEY_CITY_MOVIE_LIST_HASH . $iCityId;
                $arrMovieList = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhMGet($strMovieListDataKey, $sortedMovieIds);
                foreach ($arrMovieList as $strListInfo) {
                    if ( !empty($strListInfo)) {
                        $return['list'][] = json_decode($strListInfo, true);
                    }
                }
            }
        }
        
        return $return;
    }
    
    //————————————————————————————————— 读取正在上映影片信息，支持分页 end ———————————————————————————————————//
    
    
    //—————————————————————————————————— 读取单片推荐信息 begin ——————————————————————————————————//
    
    /**
     * 从 redis 读取影院排期 原始信息
     *
     * @param string $iChannelId 渠道编号
     *
     * @return array
     */
    public function readMovieRecommendedNews($iChannelId)
    {
        //进程内缓存（因为当前这个方法，可能发生频繁调用，而频繁调用会频繁读取redis，是没有必要的，所以同一个请求/同一个进程内，可复用数据，进程内缓存1分钟）
        static $arrCacheData = [];  //缓存实行为：[channelId=>['data'=>xxx,'time'=>时间戳]]
        if ( !empty($arrCacheData[$iChannelId]['data']) && !empty($arrCacheData[$iChannelId]['time']) && (time() <= $arrCacheData[$iChannelId]['time'])) {
            $data = $arrCacheData[$iChannelId]['data'];
        } else {
            if ($this->isRestructChannel($iChannelId)) {
                $data = $this->readMovieRecommendedNewsOnRestruct($iChannelId);
            } else {
                $data = $this->readMovieRecommendedNewsOnCronTask($iChannelId);
            }
            //查询到数据，做进程内缓存
            if ( !empty($data)) {
                $arrCacheData[$iChannelId] = [
                    'data' => $data,
                    'time' => time() + 60,
                ];
            }
        }
        
        return $this->helper('OutPut')->jsonConvert($data);
    }
    
    /**
     * 从原版CronTask读取正在上映影片信息，支持分页
     *
     * @param string $iChannelId 渠道编号
     *
     * @return array
     */
    private function readMovieRecommendedNewsOnCronTask($iChannelId)
    {
        $return = '';
        if ( !empty($iChannelId)) {
            $strData = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYget(KEY_MOVIE_RECOMMENDED_NEWS);
            $return = !empty($strData) ? $strData : '';
        }
        
        return $return;
    }
    
    /**
     * 从重构版CronTask读取正在上映影片信息
     *
     * @param string $iChannelId 渠道编号
     *
     * @return array
     */
    private function readMovieRecommendedNewsOnRestruct($iChannelId)
    {
        $return = '';
        if ( !empty($iChannelId)) {
            $strData = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYget(KEY_MOVIE_RECOMMENDED_NEWS);
            $return = !empty($strData) ? $strData : '';
        }
        
        return $return;
    }
    
    //————————————————————————————————— 读取单片推荐信息，支持分页 end ———————————————————————————————————//
    
    
    /**
     * 阵营购买计数，例如《魔兽》联盟/部落
     * 目前仅在service的订单详情接口(queryOrderInfoNew)传入payStatus=1时使用
     * 注意，channelId只在需要分开计数时传入，统一计数时不用传。$seatLimit为true时座位数单次最多加4！
     */
    public function incrMoiveCamp($iMovieId, $sCampName, $iSeatNum, $seatLimit = false, $iChannelId = 3)
    {
        //影片ID，仅在此列表内的让写入（临时方案）
        $arrMoiveList = [
            '3154',//测试用
            '6102',//魔兽
        ];
        if ( !in_array($iMovieId, $arrMoiveList)) {
            //TODO 记录日志
            return false;
        }
        $redisObj = $this->redis($iChannelId, GROUP_SHARE_FREQUENT);
        //movie_camp_3154 集合类型，保存各个阵营名
        $redisObj->WYsAdd(KEY_MOVIE_CAMP . $iMovieId, $sCampName);
        //movie_camp_3154_Alliance 保存该阵营购票数
        $iSeatNum = intval($iSeatNum);
        if ($seatLimit) {
            $iSeatNum = ($iSeatNum < 0) ? 0 : (($iSeatNum > 4) ? 4 : $iSeatNum); //单次最多加4
        }
        
        return $redisObj->WYincrBy(KEY_MOVIE_CAMP . $iMovieId . '_' . $sCampName, $iSeatNum);
    }
    
    /**
     * 获取影片阵营购买计数
     * 目前仅在service的订单详情接口(queryOrderInfoNew)传入payStatus=1时使用
     * 注意，channelId只在需要分开计数时传入，统一计数时不用传。
     *
     * @return ['Alliance'=> '321', 'Horde'=> '654']
     */
    public function getMovieCamp($iMovieId, $iChannelId = 3)
    {
        $return = [];
        $redisObj = $this->redis($iChannelId, GROUP_SHARE_FREQUENT);
        $campNames = $redisObj->WYsMembers(KEY_MOVIE_CAMP . $iMovieId);
        if ( !empty($campNames)) {
            foreach ($campNames as $campName) {
                $return[$campName] = $redisObj->WYget(KEY_MOVIE_CAMP . $iMovieId . '_' . $campName);
            }
        }
        
        return $return;
    }
    
    //————————————————————————————————— 读取即将上映的按月份分组的影片列表 begin ———————————————————————————————————//
    
    /**
     * 获取按日期分组的即将上映数据（相同日期，按照想看人数降序排序）
     * 支持分页（伪分页，其实就是支持获取某月、某年等，无法严格以获取多少条来分页）
     *
     * @param string $channelId 渠道id
     * @param string $cityId    城市id
     * @param string $year      年份
     * @param string $month     月份，支持8和08两种形式
     * @param string $state     状态，1标识获取全部，2标识获取某模糊年的数据，3标识获取某月（包括确定日期和不确定日期的），4标识获取某年所有（包括某年下所有月份）
     *                          5表示分页获取，默认获取第一页
     * @param string $page      页码，传state为5的时候，表示使用分页获取数据
     *
     * @return bool|string
     */
    public function getMovieWillWithDate($channelId = '', $cityId = '', $year = '', $month = '', $state = 5, $page = 1)
    {
        $return = [];
        if ( !empty($channelId) && !empty($cityId)) {
            if ($this->isRestructChannel($channelId)) {
                $return = $this->getMovieWillWithDateOnRestruct($channelId, $cityId, $year, $month, $state, $page);
            } else {
                $return = $this->getMovieWillWithDateOnCronTask($channelId, $cityId, $year, $month, $state, $page);
            }
        }
        
        return $return;
    }
    
    /**
     * 获取按日期分组的即将上映数据（相同日期，按照想看人数降序排序）
     * 支持分页（伪分页，其实就是支持获取某月、某年等，无法严格以获取多少条来分页）
     *
     * @param string $channelId 渠道id
     * @param string $cityId    城市id
     * @param string $year      年份
     * @param string $month     月份，支持8和08两种形式
     * @param string $state     状态，1标识获取全部，2标识获取某模糊年的数据，3标识获取某月（包括确定日期和不确定日期的），4标识获取某年所有（包括某年下所有月份）
     *                          5表示分页获取，默认获取第一页
     * @param string $page      页码，传state为5的时候，表示使用分页获取数据
     *
     * @return bool|string
     */
    public function getMovieWillWithDateOnCronTask($channelId = '', $cityId = '', $year = '', $month = '', $state = 1, $page = 1)
    {
        $return = ['list' => [], 'dimension' => [], 'page_info' => ['total' => 1, 'current' => 1,]];
        //月份转换
        if ( !empty($month)) {
            $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        }
        //年份处理
        $year = empty($year) ? date('Y') : $year;
        $state = empty($state) ? 1 : $state;
        $page = empty($page) ? 1 : $page;
        
        //按需取数据
        if ( !empty($channelId) && !empty($cityId)) {
            //1、获取主要数据
            $strKey = KEY_CITY_MOVIE_WILL_WITH_DATE_DATA . $cityId;
            //获取全部
            if ($state == 1) {
                $arrDateData = $this->redis($channelId, STATIC_MOVIE_DATA)->WYhGetAll($strKey);
                //排序
                array_multisort(array_keys($arrDateData), SORT_ASC, $arrDateData);
                $arrDateData = array_map([$this, 'jsondecodeMap'], $arrDateData);
                $return['list'] = array_values($arrDateData);
                $arrDateData = null;
            } //获取某个模糊年（也就是只是到某年上映，不知道具体月份和日期的情况）
            elseif (($state == 2) && !empty($year)) {
                $strSubKey = $year . '13';
                $arrDateData = $this->redis($channelId, STATIC_MOVIE_DATA)->WYhGet($strKey, $strSubKey);
                $arrDateData = json_decode($arrDateData, true);
                $return['list'][] = $arrDateData;
                $arrDateData = null;
            } //获取某月的（某个月的，包括2种，确认日期的和不确认日期的）
            elseif (($state == 3) && !empty($year) && !empty($month)) {
                $strSubKey = $year . $month;
                $arrDateData = $this->redis($channelId, STATIC_MOVIE_DATA)->WYhGet($strKey, $strSubKey);
                $arrDateData = json_decode($arrDateData, true);
                $return['list'][] = $arrDateData;
                $arrDateData = null;
            } //获取某年所有的
            elseif (($state == 4) && !empty($year)) {
                $arrSubKeys = [];
                for ($i = 1; $i <= 13; $i++) {
                    $arrSubKeys[] = $year . str_pad($i, 2, '0', STR_PAD_LEFT);
                }
                $arrDateData = $this->redis($channelId, STATIC_MOVIE_DATA)->WYhMGet($strKey, $arrSubKeys);
                $arrDateData = array_values(array_map([$this, 'jsondecodeMap'], array_filter($arrDateData)));
                $return['list'] = $arrDateData;
                $arrDateData = null;
            } //获取第一页，此种情况，用于客户端做下拉分页展示的情况。获取完第一页，客户端可以自行获取下一页
            elseif (($state == 5)) {
                //获取总页数
                $arrKeys = $this->redis($channelId, STATIC_MOVIE_DATA)->WYhKeys($strKey);
                sort($arrKeys);
                $iTotalPage = count($arrKeys);
                if (($page > $iTotalPage) && ($iTotalPage > 0)) {
                    $page = $iTotalPage;
                } elseif ($page < 1) {
                    $page = 1;
                }
                $return['page_info']['total'] = $iTotalPage;
                $return['page_info']['current'] = $page;
                $strSubKey = $arrKeys[$page - 1];
                $arrDateData = $this->redis($channelId, STATIC_MOVIE_DATA)->WYhGet($strKey, $strSubKey);
                $arrDateData = json_decode($arrDateData, true);
                $return['list'][] = $arrDateData;
                $arrDateData = null;
            }
            //2、获取分页维度数据
            $arrDimensions = [];
            $strKey = KEY_CITY_MOVIE_WILL_WITH_DATE_DIMENSION;
            $strDimensionData = $this->redis($channelId, STATIC_MOVIE_DATA)->WYget($strKey);
            if ( !empty($strDimensionData)) {
                $arrDimensions = json_decode($strDimensionData, true);
            }
            $return['dimension'] = $arrDimensions;
        }
        
        return $return;
    }
    
    /**
     * 获取按日期分组的即将上映数据（相同日期，按照想看人数降序排序）
     * 支持分页（伪分页，其实就是支持获取某月、某年等，无法严格以获取多少条来分页）
     *
     * @param string $channelId 渠道id
     * @param string $cityId    城市id
     * @param string $year      年份
     * @param string $month     月份，支持8和08两种形式
     * @param string $state     状态，1标识获取全部，2标识获取某模糊年的数据，3标识获取某月（包括确定日期和不确定日期的），4标识获取某年所有（包括某年下所有月份）
     *                          5表示分页获取，默认获取第一页
     * @param string $page      页码，传state为5的时候，表示使用分页获取数据
     *
     * @return bool|string
     */
    public function getMovieWillWithDateOnRestruct($channelId = '', $cityId = '', $year = '', $month = '', $state = 1, $page = 1)
    {
        $return = ['list' => [], 'dimension' => [], 'page_info' => ['total' => 1, 'current' => 1,]];
        //月份转换
        if ( !empty($month)) {
            $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        }
        //年份处理
        $year = empty($year) ? date('Y') : $year;
        $state = empty($state) ? 1 : $state;
        $page = empty($page) ? 1 : $page;
        
        //按需取数据
        if ( !empty($channelId) && !empty($cityId)) {
            //1、获取主要数据
            $strKey = KEY_CITY_MOVIE_WILL_WITH_DATE_DATA . $cityId;
            //获取全部
            if ($state == 1) {
                $arrDateData = $this->redis($channelId, STATIC_MOVIE_DATA)->WYhGetAll($strKey);
                //排序
                array_multisort(array_keys($arrDateData), SORT_ASC, $arrDateData);
                $arrDateData = array_map([$this, 'jsondecodeMap'], $arrDateData);
                $return['list'] = array_values($arrDateData);
                $arrDateData = null;
            } //获取某个模糊年（也就是只是到某年上映，不知道具体月份和日期的情况）
            elseif (($state == 2) && !empty($year)) {
                $strSubKey = $year . '13';
                $arrDateData = $this->redis($channelId, STATIC_MOVIE_DATA)->WYhGet($strKey, $strSubKey);
                $arrDateData = json_decode($arrDateData, true);
                $return['list'][] = $arrDateData;
                $arrDateData = null;
            } //获取某月的（某个月的，包括2种，确认日期的和不确认日期的）
            elseif (($state == 3) && !empty($year) && !empty($month)) {
                $strSubKey = $year . $month;
                $arrDateData = $this->redis($channelId, STATIC_MOVIE_DATA)->WYhGet($strKey, $strSubKey);
                $arrDateData = json_decode($arrDateData, true);
                $return['list'][] = $arrDateData;
                $arrDateData = null;
            } //获取某年所有的
            elseif (($state == 4) && !empty($year)) {
                $arrSubKeys = [];
                for ($i = 1; $i <= 13; $i++) {
                    $arrSubKeys[] = $year . str_pad($month, 2, '0', STR_PAD_LEFT);
                }
                $arrDateData = $this->redis($channelId, STATIC_MOVIE_DATA)->WYhMGet($strKey, $arrSubKeys);
                $arrDateData = array_values(array_map([$this, 'jsondecodeMap'], array_filter($arrDateData)));
                $return['list'] = $arrDateData;
                $arrDateData = null;
            } //获取第一页，此种情况，用于客户端做下拉分页展示的情况。获取完第一页，客户端可以自行获取下一页
            elseif (($state == 5)) {
                //获取总页数
                $arrKeys = $this->redis($channelId, STATIC_MOVIE_DATA)->WYhKeys($strKey);
                sort($arrKeys);
                $iTotalPage = count($arrKeys);
                if ($page > $iTotalPage) {
                    $page = $iTotalPage;
                } elseif ($page < 1) {
                    $page = 1;
                }
                $return['page_info']['total'] = $iTotalPage;
                $return['page_info']['current'] = $page;
                $strSubKey = $arrKeys[$page - 1];
                $arrDateData = $this->redis($channelId, STATIC_MOVIE_DATA)->WYhGet($strKey, $strSubKey);
                $arrDateData = json_decode($arrDateData, true);
                $return['list'][] = $arrDateData;
                $arrDateData = null;
            }
            //2、获取分页维度数据
            $arrDimensions = [];
            $strKey = KEY_CITY_MOVIE_WILL_WITH_DATE_DIMENSION;
            $strDimensionData = $this->redis($channelId, STATIC_MOVIE_DATA)->WYget($strKey);
            if ( !empty($strDimensionData)) {
                $arrDimensions = json_decode($strDimensionData, true);
            }
            $return['dimension'] = $arrDimensions;
        }
        
        return $return;
    }
    
    private function jsondecodeMap($item)
    {
        $return = [];
        if ( !empty($item) && is_string($item)) {
            $return = json_decode($item, true);
        }
        
        return $return;
    }
    
    //————————————————————————————————— 读取即将上映的按月份分组的影片列表 end ———————————————————————————————————//
    
    
    //————————————————————————————————— 读取即将上映的影片列表（支持分页和排序版本） begin ———————————————————————————————————//
    
    
    /**
     * 获取即将上映的影片列表
     * 仅Restruct数据才支持分页和排序
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId    城市id
     * @param string $page       页码
     * @param string $num        单页条数
     * @param string $sortField  排序字段，目前支持：wantCount、seenCount、date 三种
     * @param string $order      排序方式，可是是 desc、asc
     *
     * @return array
     */
    public function readMovieWillByPage($iChannelId = '', $iCityId = '', $page = '', $num = '', $sortField = '', $order = '')
    {
        $return = [];
        if ( !empty($iChannelId) && !empty($iCityId) && !empty($page) && !empty($num) && !empty($sortField) && !empty($order)) {
            if ($this->isRestructChannel($iChannelId)) {
                $return = $this->readMovieWillByPageOnRestruct($iChannelId, $iCityId, $page, $num, $sortField, $order);
            } else {
                $return = $this->readMovieWillByPageOnCronTask($iChannelId, $iCityId, $page, $num, $sortField, $order);
            }
        }
        
        return $return;
    }
    
    public function readMovieWillByPageOnCronTask($iChannelId = '', $iCityId = '', $page = '', $num = '', $sortField = '', $order = '')
    {
        $return = [];
        if ( !empty($iChannelId) && !empty($iCityId) && !empty($page) && !empty($num) && !empty($sortField) && !empty($order)) {
        }
        
        return $return;
    }
    
    /**
     * @param string $iChannelId
     * @param string $iCityId
     * @param string $page
     * @param string $num
     * @param string $sortField
     * @param string $order
     *
     * @return array
     */
    public function readMovieWillByPageOnRestruct($iChannelId = '', $iCityId = '', $page = '', $num = '', $sortField = '', $order = '')
    {
        $return = [];
        if ( !empty($iChannelId) && !empty($iCityId) && !empty($page) && !empty($num) && !empty($sortField) && !empty($order)) {
            switch ($sortField) {
                case "seenCount":
                    $key = STATIC_KEY_MOVIE_WILL_SORT_SEEN;
                    break;
                case 'date':
                    $key = STATIC_KEY_MOVIE_WILL_SORT_DATE;
                    break;
                case "wantCount":
                default:
                    $key = STATIC_KEY_MOVIE_WILL_SORT_WANT;
                    break;
            }
            $return = $this->_getWillListByPage($iChannelId, $iCityId, $key, $page, $num, $order);
        }
        
        return $return;
    }
    
    /**
     * @param string $iChannelId
     * @param string $iCityId
     * @param string $strKey
     * @param string $page
     * @param string $num
     * @param string $order
     *
     * @return array
     */
    private function _getWillListByPage($iChannelId = '', $iCityId = '', $strKey = '', $page = '', $num = '', $order = '')
    {
        $return = ['list' => [], 'totalNum' => 0, 'totalPage' => 0, 'page' => $page, 'num' => $num];
        if ( !empty($iChannelId) && !empty($iCityId) && !empty($strKey) && !empty($page) && !empty($num) && !empty($order)) {
            $iTotal = 0;
            //1、获取有序集合的分页区间
            $arrMovieIds = [];
            //获取分页查询的范围
            $offsetStart = ($page - 1) * $num;
            $offsetEnd = $page * $num - 1;
            switch ($order) {
                case 'asc':
                    $arrMovieIds = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYzRevRange($strKey, $offsetStart, $offsetEnd);
                    break;
                case 'desc':
                    $arrMovieIds = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYzRange($strKey, $offsetStart, $offsetEnd);
                    break;
            }
            //获取总数
            if ( !empty($arrMovieIds)) {
                $iTotal = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYzCard($strKey);
                $return['totalNum'] = $iTotal;
                $return['totalPage'] = ceil($iTotal / $num);
            }
            //2、获取城市的差异数据
            if ( !empty($arrMovieIds)) {
                $arrCityWillDiff = [];
                $arrCityWillDiff = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhMGet(STATIC_KEY_MOVIE_WILL_DIFF . $iCityId, $arrMovieIds);
                $arrCityWillDiff = array_filter($arrCityWillDiff);
            }
            //3、获取具体的movie信息
            if ( !empty($arrMovieIds)) {
                $arrMovieInfos = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhMGet(STATIC_KEY_MOVIE_WILL_COMMON_LIST, $arrMovieIds);
                $arrMovieInfos = array_filter($arrMovieInfos);
                foreach ($arrMovieInfos as $iMovieId => $strMovieInfo) {
                    $arrMovieInfo = json_decode($strMovieInfo, true);
                    $arrMovieInfoDiff = !empty($arrCityWillDiff[$iMovieId]) ? json_decode($arrCityWillDiff[$iMovieId], true) : [];
                    $arrMovieInfoDiff = ( !empty($arrMovieInfoDiff) && is_array($arrMovieInfoDiff)) ? $arrMovieInfoDiff : [];
                    $arrMovieInfo = array_merge($arrMovieInfo, $arrMovieInfoDiff);
                    $return['list'][] = $arrMovieInfo;
                }
            }
        }
        
        return $return;
    }
    
    //————————————————————————————————— 读取即将上映的影片列表（支持分页和排序版本） end  ———————————————————————————————————//
    
    
    //————————————————————————————————— 获取某个影片在某个城市，是否有排期的标识 begin ———————————————————————————————————//
    
    
    /**
     * 从新版CronTask中，获取某个影片在某个城市是否有排期的标识
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId    城市id
     * @param string $iMovieId   影片id
     *
     * @return array
     */
    public function getScheFlagOfMovieCity($iChannelId = '', $iMovieId = '', $iCityId = '')
    {
        $return = [];
        if ( !empty($iChannelId) && !empty($iCityId) && !empty($iMovieId)) {
            if ($this->isRestructChannel($iChannelId)) {
                $return = $this->getScheFlagOfMovieCityOnRestruct($iChannelId, $iMovieId, $iCityId);
            }
            else {
                $iFromBigData = !empty(\wepiao::$config['movieListAndFlagBigData']) ? \wepiao::$config['movieListAndFlagBigData'] : 0;
                //是否走大数据的数据源
                if($iFromBigData){
                    $return = $this->getScheFlagOfMovieCityOnCronTaskV3($iChannelId, $iMovieId, $iCityId);
                }else{
                    $return = $this->getScheFlagOfMovieCityOnCronTask($iChannelId, $iMovieId, $iCityId);
                }
            }
        }
        
        return $return;
    }
    
    /**
     * 从CronTask中，获取某个影片在某个城市是否有排期的标识
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId    城市id
     * @param string $iMovieId   影片id
     *
     * @return array
     */
    public function getScheFlagOfMovieCityOnCronTask($iChannelId = '', $iMovieId = '', $iCityId = '')
    {
        //默认返回有排期
        $return = 1;
        if ( !empty($iChannelId) && !empty($iCityId) && !empty($iMovieId)) {
            $strKey = STATIC_KEY_MOVIE_CITY_SCHE_FLAG . rand(0, 9);
            $return = $this->redis($iChannelId)->WYhGet($strKey, $iMovieId . '_' . $iCityId);
        }
        $return = intval($return);
        
        return $return;
    }
    
    /**
     * 从CronTask中，获取某个影片在某个城市是否有排期的标识
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId    城市id
     * @param string $iMovieId   影片id
     *
     * @return array
     */
    public function getScheFlagOfMovieCityOnCronTaskV3($iChannelId = '', $iMovieId = '', $iCityId = '')
    {
        //默认返回有排期
        $return = 1;
        if ( !empty( $iChannelId ) && !empty( $iCityId ) && !empty( $iMovieId )) {
            $strKey = STATIC_KEY_MOVIE_CITY_SCHE_FLAG_V3 . rand(0, 9);
            $return = $this->redis($iChannelId)->WYhGet($strKey, $iMovieId . '_' . $iCityId);
        }
        $return = intval($return);
        
        return $return;
    }

    /**
     * @param string $iChannelId
     * @param string $iMovieId
     * @param string $iCityId
     *
     * @return int
     */
    public function getScheFlagOfMovieCityOnRestruct($iChannelId = '', $iMovieId = '', $iCityId = '')
    {
        //默认返回有排期
        $return = 1;
        if ( !empty($iChannelId) && !empty($iCityId) && !empty($iMovieId)) {
            $strKey = STATIC_KEY_MOVIE_CITY_SCHE_FLAG . rand(0, 9);
            $return = $this->redis($iChannelId)->WYhGet($strKey, $iMovieId . '_' . $iCityId);
        }
        $return = intval($return);
        
        return $return;
    }
    
    //————————————————————————————————— 获取某个影片在某个城市，是否有排期的标识  end  ———————————————————————————————————//
    
    
    /**
     * 从 redis 获取明星选座数据
     *
     * @param string $iChannelId 渠道编号
     * @param string $iMovieId   影片id
     *
     * @return object|nul
     */
    public function readMovieCustomSeat($iChannelId, $iMovieId)
    {
        if ($this->isRestructChannel($iChannelId)) {
            $data = $this->readMovieCustomSeatOnRestruct($iChannelId, $iMovieId);
        } else {
            $data = $this->readMovieCustomSeatOnCronTask($iChannelId, $iMovieId);
        }
        
        return $data;
    }
    
    /**
     * 从重构版CronTask读取影片定制化数据
     *
     * @param string $iChannelId 渠道编号
     *
     * @return array
     */
    private function readMovieCustomSeatOnCronTask($iChannelId, $iMovieId = '')
    {
        $return = new \stdClass();
        if ( !empty($iChannelId) && !empty($iMovieId)) {
            $strKey = KEY_MOVIE_CUSTOM_SEAT;
            $strData = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhGet($strKey, $iMovieId);
            if ( !empty($strData)) {
                $return = json_decode($strData);
            }
        }
        
        return $return;
    }
    
    /**
     * 从重构版CronTask读取影片定制化数据
     *
     * @param string $iChannelId 渠道编号
     *
     * @return array
     */
    private function readMovieCustomSeatOnRestruct($iChannelId, $iMovieId = '')
    {
        $return = new \stdClass();
        if ( !empty($iChannelId)) {
            $strKey = STATIC_KEY_MOVIE_CUSTOM_SEAT . '_' . rand(0, 19);
            $strData = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhGet($strKey, $iMovieId);
            if ( !empty($strData)) {
                $return = json_decode($strData);
            }
        }
        
        return $return;
    }
    
    //———————————————————————— 新版crontask_new数据源 ———————————————————————//
    
    /**
     * 获取影片详情
     * 新crontask_new中
     *
     * @param $iChannelId
     * @param $iMovieId
     */
    public function readMovieInfoNewStatic($iChannelId, $iMovieId)
    {
        $return = [];
        if ( !empty($iChannelId) && !empty($iMovieId)) {
            $strKey = STATIC_NEW_MOVIE_DATA_INFO . $iMovieId;
            $strChan = STATIC_MOVIE_INFO;
            //判断从渠道池还是微信池取影片数据
            if($this->checkThirdChannel($iChannelId)){
                $strChan = STATIC_MOVIE_INFO_CHANNEL;
            }
            $strData = $this->redis('common', $strChan)->WYget($strKey);
            if ( !empty($strData)) {
                $return = json_decode($strData, true);
                //获取评分
                $arrMovieScore = $this->readMovieScoreNewStatic($iChannelId, [$iMovieId]);
                if ( !empty($arrMovieScore[$iMovieId]['id'])) {
                    //只有微信目前开放评分不做时间判断
                    if (in_array($iChannelId, [3])) {
                        $score = !empty($arrMovieScore[$iMovieId]['score']) ? strval($arrMovieScore[$iMovieId]['score']) : '0';
                    } else {
                        $score = '0';
                        //上映前一天才能展示评分
                        if (isset($return['date_status']) && ($return['date_status'] == 0) && !empty($return['date']) && (date('Ymd',
                                    strtotime("+1 day")) >= $return['date'])
                        ) {
                            $score = !empty($arrMovieScore[$iMovieId]['score']) ? strval($arrMovieScore[$iMovieId]['score']) : '0';
                        }
                    }
                    $return['score_type'] = 1;  //默认是普通评分
                    $return['score'] = $return['initScore'] = $score;
                    $return['wantCount'] = !empty($arrMovieScore[$iMovieId]['wantCount']) ? strval($arrMovieScore[$iMovieId]['wantCount']) : '0';
                    $return['seenCount'] = !empty($arrMovieScore[$iMovieId]['seenCount']) ? strval($arrMovieScore[$iMovieId]['seenCount']) : '0';
                    $return['scoreCount'] = !empty($arrMovieScore[$iMovieId]['scoreCount']) ? strval($arrMovieScore[$iMovieId]['scoreCount']) : '0';
                    //是否展示点映评分（本来点映片子不展示评分的，因为上映前一天才展示，现在要展示，而且前端还得有特殊展示，所以必须得返回一个字段）
                    if ( !empty($arrMovieScore[$iMovieId]['isShowScore']) && ($return['date_status'] == 0) && (time() <= (strtotime($return['date'] . ' -1 day')))) {
                        $return['score_type'] = 2;  //点映评分
                    }
                }
            }
        }
        
        return $return;
    }
    
    /**
     * 从CronTask中，获取某个影片在某个城市是否有排期的标识
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId    城市id
     * @param string $iMovieId   影片id
     *
     * @return array
     */
    public function getScheFlagOfMovieCityNewStatic($iChannelId = '', $iMovieId = '', $iCityId = '')
    {
        //默认返回有排期
        $return = 0;
        if ( !empty($iChannelId) && !empty($iCityId) && !empty($iMovieId)) {
            $strKey = STATIC_NEW_KEY_MOVIE_CITY_SCHE_FLAG . rand(0, 9);
            $return = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhGet($strKey, $iMovieId . '_' . $iCityId);
        }
        $return = intval($return);
        
        return $return;
    }
    
    /**
     * 根据影片id，从原版CronTask读取影片详情信息
     *
     * @param string $iChannelId  渠道编号
     * @param array  $arrMovieIds 影片id组成的数组
     *
     * @return array
     */
    public function readMovieInfosNewStatic($iChannelId, $arrMovieIds)
    {
        $return = [];
        $strKey = STATIC_NEW_CINEMA_SCHE_MOVIE_DATA_SET . rand(0, 10);
        $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhMGet($strKey, $arrMovieIds);
        if (is_array($data) && !empty($data)) {
            foreach ($data as &$value) {
                if (empty($value)) {
                    continue;
                }
                $value = json_decode($value, true);
            }
            $return = array_filter($data);
        }
        
        return $return;
    }
    
    /**
     * 从原版crontask_new读取正在上映影片信息，支持分页
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId    城市id
     * @param int    $page       页码
     * @param int    $num        每页条数
     *
     * @return array
     */
    public function readCityMovieByPageNewStatic($iChannelId, $iCityId, $page, $num)
    {
        $return = ['list' => [], 'total_row' => 0, 'total_page' => 0, 'page' => 1, 'num' => 10];
        if ( !empty($iCityId) && !empty($iChannelId)) {
            $page = ( !empty($page) && ($page > 0)) ? intval($page) : 1;
            $num = ( !empty($num) && ($num > 0)) ? intval($num) : 10;
            //拼装返回值中的页码和单页条数
            $return['page'] = $page;
            $return['num'] = $num;
            //获取分页查询的范围
            $offsetStart = ($page - 1) * $num;
            $offsetEnd = $page * $num - 1;
            $randum = 0;
            if (in_array($iCityId, [10, 82, 210, 221, 267, 83, 96, 179, 266, 320, 52, 144])) {
                $randum = 10;
            }
            $strKeyVersion = STATIC_NEW_CITY_MOVIE_LIST_V2;
            //如果是APP或者渠道（因为APP和微信的影片列表格式不同）
            if(!in_array($iChannelId,[3, 28, 63, 66, 67, 68, 43, 80, 84])){
                $strKeyVersion = STATIC_NEW_CITY_MOVIE_LIST_V1;
            }
            $strMovieListSortedKey = $strKeyVersion . $iCityId . '_' . rand(0, $randum);
            //获取总数
            $count = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYzCard($strMovieListSortedKey);
            $return['total_row'] = intval($count);
            $return['total_page'] = ceil($count / $num);
            //影片列表id的有序集合
            $arrMovieList = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYzRange($strMovieListSortedKey, $offsetStart, $offsetEnd);
            //根据影片id，查询hash结构，得到影片列表
            if ( !empty($arrMovieList)) {
                foreach ($arrMovieList as $strListInfo) {
                    if ( !empty($strListInfo)) {
                        $return['list'][] = json_decode($strListInfo, true);
                    }
                }
            }
        }
        
        return $return;
    }
    
    /**
     * 从原版crontask_new读取正在上映影片信息，支持分页
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId    城市id
     * @param int    $page       页码
     * @param int    $num        每页条数
     *
     * @return array
     */
    public function readCityMovieByPageNewStaticV2($iChannelId, $iCityId, $page, $num)
    {
        $return = ['list' => [], 'total_row' => 0, 'total_page' => 0, 'page' => 1, 'num' => 10];
        if ( !empty($iCityId) && !empty($iChannelId)) {
            $page = ( !empty($page) && ($page > 0)) ? intval($page) : 1;
            $num = ( !empty($num) && ($num > 0)) ? intval($num) : 10;
            //拼装返回值中的页码和单页条数
            $return['page'] = $page;
            $return['num'] = $num;
            //获取分页查询的范围
            $offsetStart = ($page - 1) * $num;
            $offsetEnd = $page * $num - 1;
            $randum = 0;
            if (in_array($iCityId, [10, 82, 210, 221, 267, 83, 96, 179, 266, 320, 52, 144])) {
                $randum = 10;
            }
            $strMovieListSortedKey = STATIC_NEW_CITY_MOVIE_LIST_V3 . $iCityId . '_' . rand(0, $randum);
            //获取总数
            $count = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYzCard($strMovieListSortedKey);
            $return['total_row'] = intval($count);
            $return['total_page'] = ceil($count / $num);
            //影片列表id的有序集合
            $arrMovieList = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYzRange($strMovieListSortedKey, $offsetStart, $offsetEnd);
            //根据影片id，查询hash结构，得到影片列表
            if ( !empty($arrMovieList)) {
                foreach ($arrMovieList as $strListInfo) {
                    if ( !empty($strListInfo)) {
                        $return['list'][] = json_decode($strListInfo, true);
                    }
                }
            }
        }
        
        return $return;
    }
    
    /**
     * 从原版CronTask读取正在上映影片信息，支持分页
     *
     * @param string $iChannelId 渠道编号
     *
     * @return array
     */
    public function readMovieRecommendedNewsStatic($iChannelId)
    {
        
        //进程内缓存（因为当前这个方法，可能发生频繁调用，而频繁调用会频繁读取redis，是没有必要的，所以同一个请求/同一个进程内，可复用数据，进程内缓存1分钟）
        static $arrCacheData = [];  //缓存实行为：[channelId=>['data'=>xxx,'time'=>时间戳]]
        if ( !empty($arrCacheData[$iChannelId]['data']) && !empty($arrCacheData[$iChannelId]['time']) && (time() <= $arrCacheData[$iChannelId]['time'])) {
            $data = $arrCacheData[$iChannelId]['data'];
        } else {
            $data = '';
            if ( !empty($iChannelId)) {
                $strData = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYget(STATIC_NEW_MOVIE_RECOMMENDED_INFO);
                $data = !empty($strData) ? $strData : '';
            }
            //查询到数据，做进程内缓存
            if ( !empty($data)) {
                $arrCacheData[$iChannelId] = [
                    'data' => $data,
                    'time' => time() + 60,
                ];
            }
        }
        
        return $this->helper('OutPut')->jsonConvert($data);
    }
    
    /**
     * 获取按日期分组的即将上映数据（相同日期，按照想看人数降序排序）
     * 支持分页（伪分页，其实就是支持获取某月、某年等，无法严格以获取多少条来分页）
     *
     * @param string $channelId 渠道id
     * @param string $cityId    城市id
     * @param string $year      年份
     * @param string $month     月份，支持8和08两种形式
     * @param string $state     状态，1标识获取全部，2标识获取某模糊年的数据，3标识获取某月（包括确定日期和不确定日期的），4标识获取某年所有（包括某年下所有月份）
     *                          5表示分页获取，默认获取第一页
     * @param string $page      页码，传state为5的时候，表示使用分页获取数据
     *
     * @return bool|string
     */
    public function getMovieWillWithDateNewStatic($channelId = '', $cityId = '', $year = '', $month = '', $state = 1, $page = 1)
    {
        $return = ['list' => [], 'dimension' => [], 'page_info' => ['total' => 1, 'current' => 1,]];
        //月份转换
        if ( !empty($month)) {
            $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        }
        //年份处理
        $year = empty($year) ? date('Y') : $year;
        $state = empty($state) ? 1 : $state;
        $page = empty($page) ? 1 : $page;
        
        //按需取数据
        if ( !empty($channelId) && !empty($cityId)) {
            //1、获取主要数据
            $strKey = STATIC_NEW_CITY_MOVIE_WILL_WITH_DATE_DATA . $cityId;
            //获取全部
            if ($state == 1) {
                $arrDateData = $this->redis($channelId, STATIC_MOVIE_DATA)->WYhGetAll($strKey);
                //排序
                array_multisort(array_keys($arrDateData), SORT_ASC, $arrDateData);
                $arrDateData = array_map([$this, 'jsondecodeMap'], $arrDateData);
                $return['list'] = array_values($arrDateData);
                $arrDateData = null;
            } //获取某个模糊年（也就是只是到某年上映，不知道具体月份和日期的情况）
            elseif (($state == 2) && !empty($year)) {
                $strSubKey = $year . '13';
                $arrDateData = $this->redis($channelId, STATIC_MOVIE_DATA)->WYhGet($strKey, $strSubKey);
                $arrDateData = json_decode($arrDateData, true);
                $return['list'][] = $arrDateData;
                $arrDateData = null;
            } //获取某月的（某个月的，包括2种，确认日期的和不确认日期的）
            elseif (($state == 3) && !empty($year) && !empty($month)) {
                $strSubKey = $year . $month;
                $arrDateData = $this->redis($channelId, STATIC_MOVIE_DATA)->WYhGet($strKey, $strSubKey);
                $arrDateData = json_decode($arrDateData, true);
                $return['list'][] = $arrDateData;
                $arrDateData = null;
            } //获取某年所有的
            elseif (($state == 4) && !empty($year)) {
                $arrSubKeys = [];
                for ($i = 1; $i <= 13; $i++) {
                    $arrSubKeys[] = $year . str_pad($i, 2, '0', STR_PAD_LEFT);
                }
                $arrDateData = $this->redis($channelId, STATIC_MOVIE_DATA)->WYhMGet($strKey, $arrSubKeys);
                $arrDateData = array_values(array_map([$this, 'jsondecodeMap'], array_filter($arrDateData)));
                $return['list'] = $arrDateData;
                $arrDateData = null;
            } //获取第一页，此种情况，用于客户端做下拉分页展示的情况。获取完第一页，客户端可以自行获取下一页
            elseif (($state == 5)) {
                //获取总页数
                $arrKeys = $this->redis($channelId, STATIC_MOVIE_DATA)->WYhKeys($strKey);
                sort($arrKeys);
                $iTotalPage = count($arrKeys);
                if (($page > $iTotalPage) && ($iTotalPage > 0)) {
                    $page = $iTotalPage;
                } elseif ($page < 1) {
                    $page = 1;
                }
                $return['page_info']['total'] = $iTotalPage;
                $return['page_info']['current'] = $page;
                $strSubKey = $arrKeys[$page - 1];
                $arrDateData = $this->redis($channelId, STATIC_MOVIE_DATA)->WYhGet($strKey, $strSubKey);
                $arrDateData = json_decode($arrDateData, true);
                $return['list'][] = $arrDateData;
                $arrDateData = null;
            }
            //2、获取分页维度数据
            $arrDimensions = [];
            $strKey = STATIC_NEW_MOVIE_WILL_WITH_DATE_DIMENSION;
            $strDimensionData = $this->redis($channelId, STATIC_MOVIE_DATA)->WYget($strKey);
            if ( !empty($strDimensionData)) {
                $arrDimensions = json_decode($strDimensionData, true);
            }
            $return['dimension'] = $arrDimensions;
        }
        
        return $return;
    }
    
    /**
     * 获取即将上映列表
     * 新版crontask__new数据源
     * 这个主要是给渠道用的, 因为微信+手Q用的结构, 和渠道的还不一样
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId    城市id
     *
     * @return array
     */
    public function readMovieWillNewStatic($iChannelId, $iCityId)
    {
        $strKey = STATIC_NEW_CITY_MOVIE_WILL . $iCityId;
        $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYget($strKey);
        if ( !empty($data)) {
            $data = json_decode($data, 1);
        }
        
        return $data;
    }
    
    /**
     * 获取明星选座数据
     * 新版crontask_new数据源
     *
     * @param string $iChannelId 渠道编号
     *
     * @return array
     */
    public function readMovieCustomSeatNewStatic($iChannelId, $iMovieId = '')
    {
        $return = new \stdClass();
        if ( !empty($iChannelId)) {
            $strKey = STATIC_NEW_MOVIE_CUSTOM_SEATS_INFO . rand(0, 19);
            $strData = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhGet($strKey, $iMovieId);
            if ( !empty($strData)) {
                $return = json_decode($strData);
            }
        }
        
        return $return;
    }
    
    /**
     * 获取影片基本数据和评分数据
     * 注意: 这个本来是只给排期接口用的, 因为它的底层逻辑, 是只生成热映+即将上映的数据的
     *
     * @param       $iChannelId
     * @param array $arrMovieIds
     */
    public function readMovieAndScoreNewStatic($iChannelId, $arrMovieIds = [])
    {
        $return = [];
        if ( !empty($iChannelId) && !empty($arrMovieIds)) {
            $strKey = STATIC_NEW_CINEMA_SCHE_MOVIE_DATA_SET . rand(0, 20);
            $arrData = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhMGet($strKey, $arrMovieIds);
            if ( !empty($arrData)) {
                $arrData = array_filter($arrData);
                $return = array_map(function ($v) {
                    return json_decode($v, 1);
                }, $arrData);
            }
        }
        
        return $return;
    }
    
    /**
     * 获取影片基本数据和评分数据
     * 注意: 这个本来是只给排期接口用的, 因为它的底层逻辑, 是只生成热映+即将上映的数据的
     *
     * @param       $iChannelId
     * @param array $arrMovieIds
     */
    public function readMovieScoreNewStatic($iChannelId, $arrMovieIds = [])
    {
        $return = [];
        if ( !empty($iChannelId) && !empty($arrMovieIds)) {
            $strKey = STATIC_NEW_MOVIE_SCORE . rand(1, 20);
            $arrData = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhMGet($strKey, $arrMovieIds);
            if ( !empty($arrData)) {
                $arrData = array_filter($arrData);
                $return = array_map(function ($v) {
                    return json_decode($v, 1);
                }, $arrData);
            }
        }
    
        return $return;
    }
    
    /**
     * 获取预告片列表
     * 新版crontask_new数据源
     *
     * @param string $iChannelId 渠道编号
     * @param string $iMovieId   影片id
     * @param string $page       页码
     * @param string $num        分页条数
     *
     * @return array
     */
    public function readMovieVideosNewStatic($iChannelId, $iMovieId = '', $page = 1, $num = 10)
    {
        $return = ['list' => [], 'total_row' => 0, 'total_page' => 0, 'page' => 1, 'num' => 10];
        if ( !empty($iChannelId) && !empty($iMovieId)) {
            $page = 0 + $page;
            $num = 0 + $num;
            $page = ( !empty($page) && ($page > 0)) ? intval($page) : 1;
            $num = ( !empty($num) && ($num > 0)) ? intval($num) : 10;
            //拼装返回值中的页码和单页条数
            $return['page'] = $page;
            $return['num'] = $num;
            //获取分页查询的范围
            $offsetStart = ($page - 1) * $num;
            $offsetEnd = $page * $num - 1;
            $strKey = STATIC_NEW_MSDB_VIDEOS . $iMovieId;
            //获取总数
            $count = $this->redis($iChannelId, STATIC_MOVIE_PREVUE)->WYzCard($strKey);
            $return['total_row'] = intval($count);
            $return['total_page'] = ceil($count / $num);
            //影片列表id的有序集合
            $arrData = $this->redis($iChannelId, STATIC_MOVIE_PREVUE)->WYzRange($strKey, $offsetStart, $offsetEnd);
            if ( !empty($arrData)) {
                $arrData = array_filter($arrData);
                $arrData = !empty($arrData) ? array_map('json_decode', $arrData) : [];
            }
            $return['list'] = $arrData;
        }
        
        return $return;
    }
    
    /**
     * 获取某个片子，在哪些城市有点映
     *
     * @param string $iChannelId 渠道编号
     *
     * @return array 返回城市ids
     */
    public function readSpotFilmNewStatic($iChannelId, $iMovieId)
    {
        $return = [];
        if ( !empty($iChannelId) && !empty($iMovieId)) {
            $strKey = STATIC_NEW_MOVIE_SPOT_FILM . rand(1, 20);
            $strData = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhGet($strKey, $iMovieId);
            if ( !empty($strData)) {
                $return = json_decode($strData, true);
            }
        }
        
        return $return;
    }
    
    //———————————————————————— 新版crontask_new数据源 ———————————————————————//
    public function gewaraGetWxMovieIdFromDb($gewaraId)
    {
        $hashKey = 'static_gewara_yp_movieId_hash';
        return $this->redis('common', USER_MOVIE_PEE)->WYhGet($hashKey, $gewaraId);
    }

    public function gewaraSetWxMovieIdToDb($gewaraMovieId, $ypMovieId)
    {
        $return = [];
        $hashKey = 'static_gewara_yp_movieId_hash';
        return $this->redis('common', USER_MOVIE_PEE)->WYhSet($hashKey, $gewaraMovieId, $ypMovieId);
    }
    public function gewaraGetAllWxMovieIdFromDb()
    {
        $hashKey = 'static_gewara_yp_movieId_hash';
        return $this->redis('common', USER_MOVIE_PEE)->WYhGetAll($hashKey);
    }

    /**
     * 礼品卡入口
     * @param $iChannelId 渠道编号
     * @param $iMovieId   电影id
     * @return array|mixed
     */
    public function GetGiftCardEntry($iChannelId, $iMovieId)
    {
        $return = [];
        if ( !empty($iChannelId) && !empty($iMovieId)) {
            $strKey = MOVIE_GIFT_CARD_ENTRY . rand(1, 20);
            $strData = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhGet($strKey, $iMovieId);
            if ( !empty($strData)) {
                $return = json_decode($strData, true);
            }
        }

        return $return;
    }

    /**
     * 获取即将上映的预览数据（包括：明星见面会、新片抢先、最热推荐）
     *
     * @param string $iChannelId 渠道编号
     *
     * @return array
     */
    public function getMovieWillPreview($iChannelId, $iCityId)
    {
        $return = [];
        if ( !empty($iChannelId) && !empty($iCityId)) {
            $strKey = $strKey = STATIC_NEW_MOVIE_WILL_PREVIEW . $iCityId;
            $strData = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYget($strKey);
            if ( !empty($strData)) {
                $return = json_decode($strData, true);
            }
        }

        return $return;
    }

    /**
     * 从原版CronTask读取影片详情信息
     *
     * @param string $iChannelId 渠道编号
     * @param string $iMovieId   影片id
     *
     * @return array
     */
    public function getHotMovieIds($iChannelId)
    {
        $strKey = STATIC_NEW_HOT_MOVIE_IDS;
        $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYget($strKey);

        return $data;
    }
}
