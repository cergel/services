<?php

namespace sdkService\model;

use \sdkService\helper\RedisManager;
use wepiao\wepiao;
use DI\Definition\ArrayDefinition;

/**
 * @tutorial 发现相关内容
 * @author liulong
 *
 */
class CmsFindChannel extends BaseNew
{
    const CMS_FIND_LIST_TIME = -1; //顺延时间:一个月
    const DB_NAME = 'dbActive';
    const  DB_TABLE_NAME ='t_active_find_channel';
    const  REDIS_KEY_FIND_OTHER_NEW = 'cms_find_other_new_';
    public function __construct()
    {
        $this->redis = $this->redis(\wepiao::getChannelId(), MOVIE_SHOW_DB);
    }

    /**
     * 获取列表id
     * @param $channelId
     * @param $start
     * @param $end
     * @return array
     */
    public function getChannelFindList($channelId,$start,$end)
    {
        $arrData = $this->redis->WYzRevRange(CMS_FIND_LIST.$channelId,$start,$end);
        return !empty($arrData)?$arrData:[];
//        if($this->redis->WYexists(CMS_FIND_LIST.$channelId)){
//            $arrData = $this->redis->WYzRevRange(CMS_FIND_LIST.$channelId,$start,$end);
//        }else{
//            //被动
//            $this->pdo(self::DB_NAME, self::DB_TABLE_NAME);
//            $where = "status=:status AND channel_id=:channel_id AND up_time <='".time()."'";
//            $paramData = [':status'=>1,':channel_id'=>$channelId];
//            $dbRe = $this->pdohelper->fetchArray($where,$paramData);
//            if($dbRe){//写入redis
//                foreach($dbRe as $val){
//                    $this->redis->WYzAdd(CMS_FIND_LIST.$channelId,$val['up_time'],$val['f_id']);
//                }
//                //重新读取一遍
//                $arrData = $this->redis->WYzRevRange(CMS_FIND_LIST.$channelId,$start,$end);
//            }else{
//                $arrData = [];
//            }
//        }
//        return $arrData;
    }

    /**
     * 获取指定渠道指定分类下的数据集合
     * @param $channelId
     * @param $typeId
     * @param $start
     * @param $end
     * @return array
     */
    public function getChannelFindTypeList($channelId,$typeId,$start,$end)
    {
        $arrData = $this->redis->WYzRevRange(CMS_FIND_TYPE_LIST.$channelId.'_'.$typeId,$start,$end);
        return !empty($arrData)?$arrData:[];
    }

    /**
     * 获取指定发现下的所有渠道及其渠道链接
     * @param $fid
     * @return mixed
     */
    public function getChannelUrlByFid($fid)
    {
        $fid = intval($fid);
        $this->pdo(self::DB_NAME, self::DB_TABLE_NAME);
        $where = "f_id = '$fid'";
        return $this->pdohelper->fetchArray($where);
    }

    /**
     * 获取指定渠道的指定分类下的cms
     * @param $channelId
     * @param $typeId
     * @return array|bool|mixed|string
     */
    public function getCmsFindOtherCmsId($channelId,$typeId)
    {
        $arrData = $this->redis->WYget(self::REDIS_KEY_FIND_OTHER_NEW.$channelId.'_'.$typeId);
        if(!empty($arrData)){
            $arrData = json_decode($arrData,true);
        }else{
            $arrData =[];
        }

        return $arrData;
    }
}
