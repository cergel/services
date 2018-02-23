<?php
namespace sdkService\service;


class serviceFilmFestival extends serviceBase
{

    /**
     * 获取电影节首页的影片数据
     *
     * @param string $channelId 渠道编号
     * @param string $openId    用户openId
     *
     * @return array
     */
    public function readMoviesData($arrInput = [])
    {
        $iChannelId = self::getParam($arrInput, 'channelId');
        $strOpenId = self::getParam($arrInput, 'openId');
        $return = self::getStOut();
        if (empty( $iChannelId )) {
            return $return;
        }
        $data = $this->model('FilmFestival')->readMoviesData($iChannelId);
        //根据openId格式化影片列表数据（格式化是否已加入清单）
        if ( !empty( $data )) {
            //获取用户想看的影片id
            $arrWantMovieIds = $this->model('FilmFestival')->getUserWantMovieIds($iChannelId, $strOpenId);
            $data = json_decode($data, true);
            $data = $this->formatMovieListWithWant($data, $arrWantMovieIds);
        }
        $return['data'] = !empty( $data ) ? $data : new \stdClass();

        return $return;
    }

    /**
     * 获取电影节影院列表页数据
     *
     * @param string channelId 渠道编号
     *
     * @return array|bool|mixed
     */
    public function readCinemasData($arrInput = [])
    {
        $iChannelId = self::getParam($arrInput, 'channelId');
        $return = self::getStOut();
        if (empty( $iChannelId )) {
            return $return;
        }
        $data = $this->model('FilmFestival')->readCinemasData($iChannelId);
        $return['data'] = !empty( $data ) ? json_decode($data) : new \stdClass();

        return $return;
    }

    /**
     * 将某个影片加入用户的电影节清单中
     *
     * @param string channelId  渠道编号
     * @param string openId     用户openId
     * @param string movieId    影片id
     * @param int    type       操作类型，1表示添加，1表示移除
     *
     * @return array
     */
    public function processUserMovieList($arrInput = [])
    {
        $return = self::getStOut();
        $iChannelId = self::getParam($arrInput, 'channelId');
        $strOpenId = self::getParam($arrInput, 'openId');
        $strMovieId = self::getParam($arrInput, 'movieId');
        $iType = self::getParam($arrInput, 'type');
        if ( !empty( $iChannelId ) && !empty( $strOpenId ) && !empty( $strMovieId )) {
            $res = 1;
            if ($iType == 1) {
                $res = $this->model('FilmFestival')->addToUserMovieList($iChannelId, $strOpenId, $strMovieId);
                if ( !$res) {
                    $errorCode = ERRORCODE_FILM_FESTIVAL_ADD_LIST_ERROR;
                }
            }
            else {
                $res = $this->model('FilmFestival')->removieFromUserMovieList($iChannelId, $strOpenId, $strMovieId);
                if ( !$res) {
                    $errorCode = ERRORCODE_FILM_FESTIVAL_REM_LIST_ERROR;
                }
            }
            //统一错误输出
            if ( !$res) {
                $return = self::getErrorOut($errorCode);
            }
        }

        return $return;
    }

    /**
     * 获取电影节“我的-我的清单”
     *
     * @param string channelId  渠道编号
     * @param string openId     用户openId
     *
     * @return array|bool|mixed
     */
    public function getUserMovieList($arrInput = [])
    {
        $return = self::getStOut();
        $iChannelId = self::getParam($arrInput, 'channelId');
        $strOpenId = self::getParam($arrInput, 'openId');
        if (empty( $iChannelId ) || empty( $strOpenId )) {
            return $return;
        }
        $data = $this->model('FilmFestival')->readUserMovieList($iChannelId, $strOpenId);
        $return['data'] = !empty( $data ) ? $data : new \stdClass();

        return $return;
    }

    /**
     * 影片搜索，搜索电影节
     *
     * @return array
     */
    public function movieSearch($arrInput = [])
    {
        $arrReturn = self::getStOut();
        $arrParams = [];
        $arrParams['channelId'] = self::getParam($arrInput, 'channelId');
        $arrParams['page'] = 1;
        $arrParams['num'] = 999;
        $arrParams['movieInfo'] = 0;
        $arrParams['actorInfo'] = 0;
        $arrParams['keyWord'] = self::getParam($arrInput, 'keyWord');
        $strOpenId = self::getParam($arrInput, 'openId');
        $arrRes = $this->service('Search')->movieSearch($arrParams);
        $arrSearchedMovieIds = !empty( $arrRes['data']['data'] ) ? $arrRes['data']['data'] : [];
        //print_r($arrSearchedMovieIds);exit;
        if ( !empty( $arrParams['channelId'] ) && !empty( $arrSearchedMovieIds )) {
            //获取电影节
            $res = $this->model('FilmFestival')->getSomeFilmFestivalMovieInfo($arrParams['channelId'], $arrSearchedMovieIds);
            //print_r($res);exit;
            //格式化isWant
            if(!empty($strOpenId)) {
                $arrWantMovieIds = $this->model('FilmFestival')->getUserWantMovieIds($arrParams['channelId'], $strOpenId);
                $res = $this->__formatSearchData($res,$arrWantMovieIds);
            }

            $arrReturn['data'] = $res;
        }

        return $arrReturn;
    }


    /**
     * 格式化搜索出来的数据
     */
    private function __formatSearchData($arrSearch,$arrWantMovieIds)
    {
        foreach($arrSearch as &$value)
        {
            if(in_array($value['id'],$arrWantMovieIds)){
                $value['isWant'] = 1;
            }
        }
        return $arrSearch;
    }

    /**
     * 格式化影片列表，增加标识：是否用户已加入了清单
     *
     * @param array $arrMovieList
     * @param array $arrWantMovieIds
     *
     * @return array
     */
    private function formatMovieListWithWant($arrMovieList = [], $arrWantMovieIds = [])
    {
        $arrMovieBlock = [];
        if ( !empty( $arrMovieList['movies'] ) && is_array($arrMovieList)) {
            foreach ($arrMovieList['movies'] as $key => $arrMovieBrief) {
                $isWant = 0;
                if ( !empty( $arrMovieBrief['id'] ) && in_array($arrMovieBrief['id'], $arrWantMovieIds)) {
                    $isWant = 1;
                }
                $arrMovieBrief['isWant'] = $isWant;
                $arrMovieBlock[] = $arrMovieBrief;
            }
        }
        if ( !empty( $arrMovieBlock )) {
            $arrMovieList['movies'] = $arrMovieBlock;
        }

        return $arrMovieList;
    }


}