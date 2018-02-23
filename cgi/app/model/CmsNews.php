<?php

namespace sdkService\model;

use \sdkService\helper\RedisManager;
use wepiao\wepiao;
use DI\Definition\ArrayDefinition;

/**
 * @tutorial 资讯模块
 * @author liulong
 *
 */
class CmsNews extends BaseNew
{
    const CMS_NEWS_LIST_TIME = 2592000;//缓存一个月
    const DB_NAME = 'dbActive';
    const DB_TABLE_NAME = 't_active_news';
    const MOVIE_NEWS_CACHE = MOVIE_SHOW_DB;

    const MOVIE_NEWS_LIST = 'cms_news_list_';//影片资讯
    const ACTOR_NEWS_LIST = 'actor_news_list_';//影人资讯

    /**
     * 获取影片资讯列表的id
     * @param $channelId
     * @param $movieId
     * @param $start
     * @param $end
     * @return array
     */
    public function getMovieNewsList($channelId,$movieId,$start,$end)
    {
        $resturn = $this->redis($channelId, self::MOVIE_NEWS_CACHE)->WYzRevRange(self::MOVIE_NEWS_LIST.$movieId,$start,$end);
        return empty($resturn)?[]:$resturn;
    }

    /**
     * 获取总数
     * @param $channelId
     * @param $movieId
     * @return int
     */
    public function getMovieNewsCount($channelId,$movieId)
    {
        $count = $this->redis($channelId, self::MOVIE_NEWS_CACHE)->WYzCard(self::MOVIE_NEWS_LIST.$movieId);
        return empty($count)?0:$count;
    }

    /**
     * 获取影片资讯列表的id
     * @param $channelId
     * @param $movieId
     * @param $start
     * @param $end
     * @return array
     */
    public function getActorNewsList($channelId,$actorId,$start,$end)
    {
        $resturn = $this->redis($channelId, self::MOVIE_NEWS_CACHE)->WYzRevRange(self::ACTOR_NEWS_LIST.$actorId,$start,$end);
        return empty($resturn)?[]:$resturn;
    }

    /**
     * 获取总数
     * @param $channelId
     * @param $movieId
     * @return int
     */
    public function getActorNewsCount($channelId,$actorId)
    {
        $count = $this->redis($channelId, self::MOVIE_NEWS_CACHE)->WYzCard(self::ACTOR_NEWS_LIST.$actorId);
        return empty($count)?0:$count;
    }
    ############ 以下为旧的代码 ##############

//
//    public function __construct()
//    {
//        $this->redis = $this->redis(\wepiao::getChannelId(), MOVIE_SHOW_DB);
//    }
//
//    /**
//     * 获取资讯列表
//     * @param $movieId
//     * @param $start
//     * @param $end
//     */
//    public function getCmsNewsList($movieId,$start,$end)
//    {
//        $count = $this->getCmsNewsNum($movieId);
//        $arrData['list'] = $this->redis->WYzRevRange(CMS_NEWS_LIST.$movieId,$start,$end);
//        if(empty($start) && empty($arrData['list']))
//            $arrData['totalCount'] = 0;
//        else
//            $arrData['totalCount'] = $count;
//        $this->redis->WYexpire(CMS_NEWS_LIST.$movieId,self::CMS_NEWS_LIST_TIME);//每次获取列表都会对该影片顺延
//        return $arrData;
//    }
//    /**
//     * 资讯总数
//     * @param $id
//     */
//    private function getCmsNewsNum($movieId)
//    {
//        if(!$this->redis->WYexists(CMS_NEWS_LIST.$movieId)){
//            $num = $this->saveCacheByMysql($movieId);
//        }else{
//            $num = $this->redis->WYzCard(CMS_NEWS_LIST.$movieId);
//        }
//        return $num;
//    }
//
//    /**
//     * 被动数据
//     * @param $id
//     */
//    private function saveCacheByMysql($movieId)
//    {
//        $this->pdo(self::DB_NAME, self::DB_TABLE_NAME);
//        $like = 0;
//        $where = "movie_id = :movie_id AND status=:status AND up_time <='".time()."'";
//        $paramData = [':movie_id'=>$movieId,':status'=>1];
//        $dbRe = $this->pdohelper->fetchAll($where,$paramData);
//        if($dbRe){
//            foreach($dbRe as $val){
//                $this->redis->WYzAdd(CMS_NEWS_LIST.$movieId,$val->up_time,$val->a_id);
//            }
//            $like = count($dbRe);
//        }else{
//            $this->redis->WYzAdd(CMS_NEWS_LIST.$movieId,0,'');
//            $this->redis->WYzRem(CMS_NEWS_LIST.$movieId,'');
//        }
//        $this->redis->WYexpire(CMS_NEWS_LIST.$movieId,self::CMS_NEWS_LIST_TIME);
//        return $like;
//    }


}
