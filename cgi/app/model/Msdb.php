<?php

namespace sdkService\model;


class Msdb extends BaseNew
{
    const KEY_MOVIE_WILL = 'movie_will';  //即将上映集合  即将废弃
    const KEY_MOVIE_INFO = 'movie_info';  //影片详情集合  即将废弃
    const KEY_MOVIE_ACTOR_OART = 'movie_actor_oart';//影片-影人信息包含角色等（hash）-- 已废弃

    const KEY_ACTOR_INFO = 'actor_info_'; //影人信息
    const KEY_MOVIE_POSTER = 'movie_poster_';//影片图片信息
    const KEY_ACTOR_MOVIE = 'actor_movie_'; //影人所出演的影片信息
    const KEY_SVIP_MOVIE = 'svip_movie';  //手机电影   即将废弃
    const KEY_ACTOR_IMG = 'actor_photo_';//影人图片


    const MSDB_KEY_MOVIE_ACTOR ='movie_actor_';   //影片-影人信息 影片id


    /**
     * 获取即将上映集合（返回的是影片id，带排序）
     * @param $iChannelId
     * @param $iPage
     * @param $iNum
     * @return array
     */
    public function getWillMovieIdList($iChannelId, $iSatrt, $iEnd)
    {
        return $this->redis($iChannelId, MOVIE_SHOW_DB)->WYzRange(self::KEY_MOVIE_WILL, $iSatrt, $iEnd);
    }

    /**
     * 获取即将上映集合总数
     * @param $iChannelId
     * @return int
     */
    public function getWillMovieCount($iChannelId)
    {
        return $this->redis($iChannelId, MOVIE_SHOW_DB)->WYzCard(self::KEY_MOVIE_WILL);
    }

    /**
     * 获取单条影片信息
     * @param $iChannelId
     * @return int
     */
    public function getMovieInfoOne($iChannelId, $iMovieId)
    {
        $objJson = $this->redis($iChannelId, MOVIE_SHOW_DB)->WYhGet(self::KEY_MOVIE_INFO, $iMovieId);
        return !empty($objJson) ? json_decode($objJson) : [];
    }

    /**
     * 批量获取影片信息
     * @param $iChannelId
     * @return int
     */
    public function getMovieInfoMore($iChannelId, $arrMovieId)
    {

        $objData = $this->redis($iChannelId, MOVIE_SHOW_DB)->WYhMGet(self::KEY_MOVIE_INFO, $arrMovieId);
        $objData = array_map('json_decode', $objData);
        $objData = array_filter($objData);//去掉没有的数据
        return $objData;
    }

    /**
     * 获取单挑影片的演员信息--已修改
     * @param $iChannelId
     * @return int
     */
    public function getMovieActorOne($iChannelId, $iMovieId)
    {
//        $objData = $this->redis($iChannelId, MOVIE_SHOW_DB)->WYhMGet(self::KEY_MOVIE_ACTOR_OART, $arrMovieId);
        $objJson = $this->redis($iChannelId, MOVIE_SHOW_DB)->WYget(self::MSDB_KEY_MOVIE_ACTOR.$iMovieId);
        return !empty($objJson) ? json_decode($objJson) : [];
    }

    /**
     * 批量获取影片的演员信息--已修改
     * @param $iChannelId
     * @return int
     */
    public function getMovieActorMore($iChannelId, $arrMovieId)
    {
        $objData = [];
        foreach($arrMovieId as $val){
            $objData[$val] = $this->redis($iChannelId, MOVIE_SHOW_DB)->WYget(self::MSDB_KEY_MOVIE_ACTOR.$val);
        }
//        $objData = $this->redis($iChannelId, MOVIE_SHOW_DB)->WYhMGet(self::KEY_MOVIE_ACTOR_OART, $arrMovieId);
        if (!empty($objData)) {
            $objData = array_map('json_decode', $objData);
            $objData = array_filter($objData);//去掉没有的数据
        } else {
            $objData = [];
        }

        return $objData;
    }

    /**
     * 获取影人详细信息
     * @param $iChannelId
     * @param $iActorId
     * @return array|mixed
     */
    public function getActorOneInfo($iChannelId, $iActorId)
    {
        $objData = $this->redis($iChannelId, MOVIE_SHOW_DB)->WYget(self::KEY_ACTOR_INFO . $iActorId);
        return !empty($objData) ? json_decode($objData, true) : [];
    }
    /**
     * 获取影人图片
     * @param $iChannelId
     * @param $iActorId
     * @return array|mixed
     */
    public function getActorPhoto($iChannelId, $iActorId)
    {
        $objData = $this->redis($iChannelId, MOVIE_SHOW_DB)->WYget(self::KEY_ACTOR_IMG . $iActorId);
        return !empty($objData) ? json_decode($objData, true) : [];
    }

    /**
     * 获取影人所出演的影片信息
     * @param $iChannelId
     * @param $iActorId
     * @return array|mixed
     */
    public function getActorMovie($iChannelId, $iActorId)
    {
        $objData = $this->redis($iChannelId, MOVIE_SHOW_DB)->WYget(self::KEY_ACTOR_MOVIE . $iActorId);
        return !empty($objData) ? json_decode($objData, true) : [];
    }

    /**
     * 获取影片图片
     * @param $iChannelId
     * @param $iMovieId
     * @return array|mixed
     */
    public function getMoviePosterOne($iChannelId, $iMovieId)
    {
        $objData = $this->redis($iChannelId, MOVIE_SHOW_DB)->WYget(self::KEY_MOVIE_POSTER . $iMovieId);
        return !empty($objData) ? json_decode($objData, true) : [];
    }

    /**
     * 获取手机电影的url
     * @param $iChannelId
     * @param $iMovieId
     * @return array|mixed
     */
    public function getSvipMovieUrl($iChannelId, $iMovieId)
    {
        $objData = $this->redis($iChannelId, MOVIE_SHOW_DB)->WYhGet(self::KEY_SVIP_MOVIE, $iMovieId);
        return !empty($objData) ? json_decode($objData, true) : [];
    }


}