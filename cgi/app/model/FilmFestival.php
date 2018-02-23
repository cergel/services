<?php

namespace sdkService\model;


class FilmFestival extends BaseNew
{


    public function __construct()
    {
    }

    /**
     * 获取电影节影片数据
     *
     * @param $iChannelId
     */
    public function readMoviesData($iChannelId)
    {
        $data = $this->redis($iChannelId, FILM_FESTIVAL_DATA)->WYget(FILM_FESTIVAL_MOVIES_DATA);

        return $data;
    }

    /**
     * 获取电影节影片数据
     *
     * @param $iChannelId
     */
    public function readCinemasData($iChannelId)
    {
        $data = $this->redis($iChannelId, FILM_FESTIVAL_DATA)->WYget(FILM_FESTIVAL_CINEMAS_DATA);

        return $data;
    }

    /**
     * 获取某个用户电影节清单中所有影片id
     *
     * @param int    $iChannelId
     * @param string $strOpenId
     *
     * @return array
     */
    public function getUserWantMovieIds($iChannelId, $strOpenId = '')
    {
        $data = [];
        if ( !empty( $strOpenId )) {
            $strUserWantKey = FILM_FESTIVAL_USER_WANT_LIST . $strOpenId;
            $data = $this->redis($iChannelId, FILM_FESTIVAL_DATA)->WYzRange($strUserWantKey,0,-1);
        }

        return ( !empty( $data ) && is_array($data) ) ? $data : [];
    }

    /**
     * 添加影片Id到用户的电影节清单
     *
     * @param string $iChannelId 渠道编号
     * @param string $strOpenId  用户openId
     * @param string $strMovieId 影片id
     *
     * @return bool|int
     */
    public function addToUserMovieList($iChannelId, $strOpenId, $strMovieId)
    {
        $return = false;
        if ( !empty( $iChannelId ) && !empty( $strOpenId ) && !empty( $strMovieId )) {
            $strUserWantKey = FILM_FESTIVAL_USER_WANT_LIST . $strOpenId;
            //$return = $this->redis($iChannelId, FILM_FESTIVAL_DATA)->WYsAdd($strUserWantKey, $strMovieId);
            $t = time();
            $return = $this->redis($iChannelId, FILM_FESTIVAL_DATA)->WYzAdd($strUserWantKey, 100/$t,$strMovieId);
            $this->redis($iChannelId, FILM_FESTIVAL_DATA)->WYexpire($strUserWantKey, 3456000);


        }

        return $return;
    }

    /**
     * 添加影片Id到用户的电影节清单
     *
     * @param string $iChannelId 渠道编号
     * @param string $strOpenId  用户openId
     * @param string $strMovieId 影片id
     *
     * @return bool|int
     */
    public function removieFromUserMovieList($iChannelId, $strOpenId, $strMovieId)
    {
        $return = false;
        if ( !empty( $iChannelId ) && !empty( $strOpenId ) && !empty( $strMovieId )) {
            $strUserWantKey = FILM_FESTIVAL_USER_WANT_LIST . $strOpenId;
            $return = $this->redis($iChannelId, FILM_FESTIVAL_DATA)->WYzRem($strUserWantKey, $strMovieId);
        }

        return $return;
    }

    /**
     * 获取用户的电影节清单
     *
     * @param int    $iChannelId
     * @param string $strOpenId
     */
    public function readUserMovieList($iChannelId, $strOpenId)
    {
        $res = [];
        if ( !empty( $iChannelId ) && !empty( $strOpenId )) {
            $strUserWantKey = FILM_FESTIVAL_USER_WANT_LIST . $strOpenId;
            //获取用户的电影节清单的所有影片id
            //$arrUserMovieListIds = $this->redis($iChannelId, FILM_FESTIVAL_DATA)->WYsMembers($strUserWantKey);
            $arrUserMovieListIds = $this->redis($iChannelId, FILM_FESTIVAL_DATA)->WYzRange($strUserWantKey,0,-1);
            if ( !empty( $arrUserMovieListIds )) {
                //rsort($arrUserMovieListIds);
                //获取所有电影节影片信息
                $res = $this->getSomeFilmFestivalMovieInfo($iChannelId, $arrUserMovieListIds);
                if ( !empty( $res ) && is_array($res)) {
                    foreach ($res as &$val) {
                        $val['isWant'] = 1;
                    }
                }
            }
        }

        return ( !empty( $res ) && is_array($res) ) ? $res : [];
    }

    /**
     * 获取特定电影节的影片信息
     *
     * @param int   $iChannelId
     * @param array $arrMovieIds
     */
    public function getSomeFilmFestivalMovieInfo($iChannelId, $arrMovieIds = [])
    {
        $return = [];
        if ( !empty( $arrMovieIds )) {
            $arrRes = $this->redis($iChannelId, FILM_FESTIVAL_DATA)->WYhMGet(FILM_FESTIVAL_ALL_MOVIE_INFO, $arrMovieIds);
            if ( !empty( $arrRes ) && is_array($arrRes)) {
                foreach ($arrRes as $key => $data) {
                    //hmget有可能拿到元素为false的情况，这里做一下过滤
                    if (empty( $data )) {
                        continue;
                    }
                    $arrMovieInfo = json_decode($data, true);
                    $return[] = $arrMovieInfo;
                }
            }
        }

        return $return;
    }

}