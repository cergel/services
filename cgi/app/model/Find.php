<?php

namespace sdkService\model;

use \sdkService\helper\RedisManager;

/**
 * @tutorial 发现相关内容[CMS]
 * @author liulong
 * 属性只有在本文件使用
 * 另：本方仅访问redis
 */
class Find extends BaseNew
{
    const FIND_NEW_INFO = 'find_new_info_';//find_new_info_{id}  // 发现详情
    const FIND_NEW_LIST = 'find_new_list_';//find_new_list_{channelId}_{分类id}  // 发现列表
    const FIND_NEW_AUTHOR_LIST = 'find_new_author_list_';//find_new_author_list_{channelId}_{作者id}  // 作者发现列表
    const FIND_NEW_RECOMMEND_LIST = 'find_new_recommend_list_';//find_new_recommend_list_{channelId}  // 推荐发现列表
    const FIND_NEW_TOPIC = 'find_new_topic_list_';
    const FIND_CACHE = MOVIE_SHOW_DB;
    const FIND_CACHE_LIST = 'find_new_cache_list_';//缓存列表
    const FIND_CACHE_HEAD = 'find_new_cache_head_';//缓存列表
    const FIND_CACHE_LIST_TIME = 60;//缓存列表时间
    //头部数据
    const FIND_NEW_TOPIC_INFO = 'find_new_topic';//find_new_topic 热门话题
    const FIND_NEW_HORIZONTAL = 'find_new_horizontal_';//find_new_horizontal_{channelId}  发现横栏
    const FIND_NEW_BANNER = 'find_new_banner_';//find_new_banner_{channelId}_{cityId}  // banner
    const FIND_NEW_MOVIE_REVIEW = 'find_new_movie_review_'; //find_new_movie_review_{channelId}  // 发现观影指南
    const FIND_NEW_TAG_LIST = 'find_new_tag_list';// 标签分类
    const FIND_NEW_TAG_INFO = 'find_new_tag_info';//标签详情

    const FIND_INFOMATION_TOPIC_LIST= 'fin_infomation_topic_list_';//FIND_INFOMATION_TOPIC_LIST{id}  // 专题数据
    const FIND_INFOMATION_TOPIC_LIST_CACHE= 'fin_infomation_topic_list_cache';//fin_infomation_topic_list_cache{id}  // 专题缓存
    const FIND_GUIDE_INFO = "find_guide_info";



    /**
     * 获取发现新详情
     * @param $channelId
     * @param $typeId
     * @param $star
     * @param $end
     * @return array
     */
    public function getFindInfo($channelId,$typeId)
    {
        $arrData = $this->redis($channelId,self::FIND_CACHE)->WYget(self::FIND_NEW_INFO.$typeId);
        return empty($arrData)?[]:json_decode($arrData,true);
    }
    /**
     * 获取指定分类下的发现列表
     * @param $channelId
     * @param $typeId
     * @param $star
     * @param $end
     * @return array
     */
    public function getFindList($channelId,$typeId,$star,$end)
    {
        return $this->redis($channelId,self::FIND_CACHE)->WYzRevRange(self::FIND_NEW_LIST.$channelId.'_'.$typeId,$star,$end);
    }
    /**
     * 获取指定分类下的发现总数
     * @param $channelId
     * @param $typeId
     * @return int
     */
    public function getFindListCount($channelId,$typeId)
    {
        return $this->redis($channelId,self::FIND_CACHE)->WYzCard(self::FIND_NEW_LIST.$channelId.'_'.$typeId);
    }
    /**
     * 获取指定作者下的发现列表
     * @param $channelId
     * @param $typeId
     * @param $star
     * @param $end
     * @return array
     */
    public function getFindAuthorList($channelId,$typeId,$star,$end)
    {
        return $this->redis($channelId,self::FIND_CACHE)->WYzRevRange(self::FIND_NEW_AUTHOR_LIST.$channelId.'_'.$typeId,$star,$end);
    }
    /**
     * 获取指定作者下的发现总数
     * @param $channelId
     * @param $typeId
     * @return int
     */
    public function getFindAuthorCount($channelId,$typeId)
    {
        return $this->redis($channelId,self::FIND_CACHE)->WYzCard(self::FIND_NEW_AUTHOR_LIST.$channelId.'_'.$typeId);
    }
    /**
     * 获取渠道下推荐列表
     * @param $channelId
     * @param $typeId
     * @param $star
     * @param $end
     * @return array
     */
    public function getFindRecommendList($channelId,$star,$end)
    {
        return $this->redis($channelId,self::FIND_CACHE)->WYzRevRange(self::FIND_NEW_RECOMMEND_LIST.$channelId,$star,$end);
    }
    /**
     * 获取渠道下推荐列表发现总数
     * @param $channelId
     * @param $typeId
     * @return int
     */
    public function getFindRecommendListCount($channelId)
    {
        return $this->redis($channelId,self::FIND_CACHE)->WYzCard(self::FIND_NEW_RECOMMEND_LIST.$channelId);
    }
    /**
     * 获取热门话题下的发现列表
     * @param $channelId
     * @param $typeId
     * @param $star
     * @param $end
     */
    public function getTopicList($channelId,$typeId)
    {
        $arrData = $this->redis($channelId,self::FIND_CACHE)->WYget(self::FIND_NEW_TOPIC.$channelId.'_'.$typeId);
        return empty($arrData)?[]:json_decode($arrData,true);
    }

    /**
     * 获取缓存的列表
     * @param $channelId
     * @param $arrData
     * @return array|mixed
     */
    public function getFindListCache($channelId,$arrData)
    {
        $str = implode(',',$arrData);
        $arrCacheData = $this->redis($channelId,self::FIND_CACHE)->WYget(self::FIND_CACHE_LIST.$str);
        return empty($arrCacheData)?[]:json_decode($arrCacheData,true);
    }

    /**
     * @param $channelId
     * @param $arrData
     * @param $arrDataInfo
     * @return bool
     */
    public function setFindListCache($channelId,$arrData,$arrDataInfo)
    {
        $str = implode(',',$arrData);
        $arrCacheData = $this->redis($channelId,self::FIND_CACHE)->WYset(self::FIND_CACHE_LIST.$str,json_encode($arrDataInfo));
        $this->redis($channelId,self::FIND_CACHE)->WYexpire(self::FIND_CACHE_LIST.$str,self::FIND_CACHE_LIST_TIME);
        return $arrCacheData;
    }

    /**
     * 获取热门话题内容
     * @param $channelId
     * @return array|mixed
     */
    public function getTopicInfoCache($channelId)
    {
        $arrCacheData = $this->redis($channelId,self::FIND_CACHE)->WYget(self::FIND_NEW_TOPIC_INFO);
        return empty($arrCacheData)?[]:json_decode($arrCacheData,true);
    }

    /**发现观影指南
     * @param $channelId
     * @return array|mixed
     */
    public function getMovieReviewCache($channelId)
    {
        $arrCacheData = $this->redis($channelId,self::FIND_CACHE)->WYget(self::FIND_NEW_MOVIE_REVIEW.$channelId);
        return empty($arrCacheData)?[]:json_decode($arrCacheData,true);
    }

    /**
     * 发现横栏入口
     * @param $channelId
     * @return array|mixed
     */
    public function getHorizontalCache($channelId)
    {
        $arrCacheData = $this->redis($channelId,self::FIND_CACHE)->WYget(self::FIND_NEW_HORIZONTAL.$channelId);
        return empty($arrCacheData)?[]:json_decode($arrCacheData,true);
    }

    /**
     * 获取指定城市的banner列表
     * @param $channelId
     * @param $cityId
     * @return array|mixed
     */
    public function getBannerCache($channelId,$cityId)
    {
        $arrCacheData = $this->redis($channelId,self::FIND_CACHE)->WYget(self::FIND_NEW_BANNER.$channelId.'_'.$cityId);
        return empty($arrCacheData)?[]:json_decode($arrCacheData,true);
    }

    /**
     * 获取头部缓存
     * @param $channelId
     * @return array
     */
    public function getFindHeadCache($channelId,$cityId)
    {
        $arrData =  $this->redis($channelId,self::FIND_CACHE)->WYget(self::FIND_CACHE_HEAD.$channelId.'_'.$cityId);
        return empty($arrData)?[]:json_decode($arrData.true);
    }
    /**
     * 写入头部缓存
     * @param $channelId
     * @return  true|false
     */
    public function setFindHeadCache($channelId,$cityId,$arrData)
    {
        $res =  $this->redis($channelId,self::FIND_CACHE)->WYset(self::FIND_CACHE_HEAD.$channelId.'_'.$cityId,json_encode($arrData));
        $this->redis($channelId,self::FIND_CACHE)->WYexpire(self::FIND_CACHE_HEAD.$channelId,self::FIND_CACHE_LIST_TIME);
        return $res;
    }
    /**
     * 获取标签列表
     */
    public function getTagList($channelId)
    {
        $arrData = $this->redis($channelId,self::FIND_CACHE)->WYget(self::FIND_NEW_TAG_LIST);
        return empty($arrData)?[]:json_decode($arrData,true);
    }
    /**
     * 获取标签详情
     */
    public function getTagInfo($channelId)
    {
        $arrData = $this->redis($channelId,self::FIND_CACHE)->WYget(self::FIND_NEW_TAG_INFO);
        return empty($arrData)?[]:json_decode($arrData,true);
    }
    /**
     * 获取标签详情
     */
    public function getInfomationInfo($channelId,$id)
    {
        $arrData = $this->redis($channelId,self::FIND_CACHE)->WYget(self::FIND_INFOMATION_TOPIC_LIST.$id);
        return empty($arrData)?[]:json_decode($arrData,true);
    }
    /**
     * 获取标签详情
     */
    public function getInfomationInfoCache($channelId,$id)
    {
        $arrData = $this->redis($channelId,self::FIND_CACHE)->WYget(self::FIND_INFOMATION_TOPIC_LIST_CACHE.$id);
        return empty($arrData)?[]:json_decode($arrData,true);
    }
    /**
     * 写入专题缓存
     * @param $channelId
     * @return  true|false
     */
    public function setInfomationInfoCache($channelId,$id,$arrData)
    {
        $res =  $this->redis($channelId,self::FIND_CACHE)->WYset(self::FIND_INFOMATION_TOPIC_LIST_CACHE.$id,json_encode($arrData));
        $this->redis($channelId,self::FIND_CACHE)->WYexpire(self::FIND_INFOMATION_TOPIC_LIST_CACHE.$id,self::FIND_CACHE_LIST_TIME);
        return $res;
    }

    /**
     * 读取发现导流
     */
    public function getFindGuide($channelId)
    {
        $data = $this->redis($channelId,self::FIND_CACHE)->WYget(self::FIND_GUIDE_INFO);
        return empty($data)? [] : json_decode($data);
    }



}
