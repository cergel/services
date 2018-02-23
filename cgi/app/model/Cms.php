<?php

namespace sdkService\model;

use \sdkService\helper\RedisManager;
use wepiao\wepiao;
use DI\Definition\ArrayDefinition;

/**
 * @tutorial 发现相关内容[CMS]
 * @author liulong
 *
 */
class Cms extends BaseNew
{
    const CMS_CMS_INFO_TIME = -1;//缓存时间
    const CMS_READ_NUM = 'cms_read_num_';
    const DB_NAME = 'dbActive';
    const  DB_TABLE_NAME = 't_active';

    const CMS_CACHE_REDIS = MOVIE_SHOW_DB;


    /**
     * 获取cms的内容---主要用于资讯
     * @param $id
     * @return array|mixed
     */
    public function getNewsInfoById($id)
    {
        $arrData = $this->redis(\wepiao::getChannelId(),self::CMS_CACHE_REDIS)->WYget(CMS_CMS_INFO.$id);
        return empty($arrData)?[]:json_decode($arrData,true);
    }

    /**
     * 获取指定文章的真实阅读数
     * @param $id
     * @return bool|int|string
     */
    public function getCmsReadNumById($id)
    {
        $num = $this->redis->WYget(self::CMS_READ_NUM.$id);
        return empty($num)?0:$num;
    }

    /**
     * 获取喜欢数
     * @param $id
     */
    public function getCmsLikeNumById($id)
    {

    }


    ############  CMS 改造之前的代码 暂时保留 ###############
    public function __construct()
    {
        $this->redis = $this->redis(\wepiao::getChannelId(), MOVIE_SHOW_DB);
    }
    /**
     * 获取CMS的内容
     * @param $id
     * @return array|bool|mixed|string
     */
    public function getOneCmsInfo($id)
    {
        $arrData = [];
        if(empty($id))return $arrData;
        if(!$this->redis->WYexists(CMS_CMS_INFO.$id)){//被动 mysql
            $this->pdo(self::DB_NAME, self::DB_TABLE_NAME);//链接数据库
            $where = "iActive_id = '$id' AND iStatus ='1'";
            $dbRe = $this->pdohelper->fetchOne($where);
            if($dbRe){//存在，则查询出资讯的内容并且储存
                $sql = "SELECT * FROM t_active_news WHERE a_id='$id' AND `status` ='1' limit 1";
                $arrChannel = $this->pdohelper->fetchArrayBySql($sql);
                $arrData = self::getCmsByFind($dbRe,$arrChannel);
                $this->redis->WYset(CMS_CMS_INFO.$id,json_encode($arrData));
            }else{
                $this->redis->WYset(CMS_CMS_INFO.$id,json_encode([]));
            }
        }else{//redis get
            $arrData = $this->redis->WYget(CMS_CMS_INFO.$id);
            $arrData = json_decode($arrData,true);
        }
        $this->redis->WYexpire(CMS_CMS_INFO.$id,self::CMS_CMS_INFO_TIME);
        return $arrData;
    }

    /**
     * 获取cms的真实阅读数
     * @param $id
     * @param string $arrInfo
     */
    public function getCmsReadNum($id,$arrInfo='')
    {
        $read = 0;
        if(empty($arrInfo))
            $arrInfo = $this->getOneCmsInfo($id);
        $read += !empty($arrInfo['base_read'])?$arrInfo['base_read']:0;
        //获取真实的喜欢数
        $read += self::getCmsReadNow($id,$arrInfo);
        return $read;
    }
    /**
     * 获取cms的真实阅读数
     * @param $id
     * @param string $arrInfo
     */
    public function addCmsReadNum($id,$arrInfo='')
    {
        $read = self::getCmsReadNow($id);//确定有key
        $this->redis->WYincr(self::CMS_READ_NUM.$id);
        return $read+1;
    }

    /**
     * 获取注水喜欢数
     * @param $id
     * @param string $arrInfo
     * @return int
     */
    public function getBaseLikeNum($id,$arrInfo='')
    {
        if(empty($arrInfo))
            $arrInfo = $this->getOneCmsInfo($id);
        $like = !empty($arrInfo['base_fill'])?$arrInfo['base_fill']:0;
        return $like;
    }

    /**
     * 获取真实阅读
     * @param $id
     * @param string $arrInfo
     */
    private function getCmsReadNow($id,$arrInfo='')
    {
        $read = 0;
        if($this->redis->WYexists(self::CMS_READ_NUM.$id)){//如果有key，就直接用
            $read = $this->redis->WYget(self::CMS_READ_NUM.$id);
        }else{//如果没有，用数据库中的
            if(empty($arrInfo))
                $arrInfo = $this->getOneCmsInfo($id);
            $read += !empty($arrInfo['reads'])?$arrInfo['reads']:0;
            $this->redis->WYset(self::CMS_READ_NUM.$id,$read);
        }
        return $read;
    }
    /**
     * 整合需要的字段
     * @param $dbRe
     * @param array $arrChannel
     * @return array
     */
    private function getCmsByFind($dbRe,$arrChannel=[])
    {
        if(empty($dbRe))return [];
        $arrData = [];
        $arrData['short_title'] = $dbRe['ShortTitle']; //短标题
        $arrData['a_id'] = $dbRe['iActive_id'];//cms id
        $arrData['cover'] =!empty($dbRe['sCover'])? CDN_APPNFS.$dbRe['sCover']:'';//cms 封面图
        $arrData['title'] = $dbRe['sTitle'];//cms 标题
        $arrData['base_read'] = $dbRe['iFillRead'];// cms 阅读注水数
        $arrData['base_fill'] = $dbRe['iFill']; //cms点赞注水数
        $arrData['likes'] = $dbRe['iLikes']; //cms点赞注水数
        $arrData['reads'] = $dbRe['iReads']; //cms点赞注水数
        if (is_array($arrChannel)){
            foreach($arrChannel as $result) {
                $arrData['n_photo'] = !empty($result['n_photo']) ? CDN_APPNFS . $result['n_photo']:'';
                $arrData['up_time'] = $result['up_time'];
            }
        }
        return $arrData;
    }
}
