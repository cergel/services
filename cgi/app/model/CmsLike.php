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
class CmsLike extends BaseNew
{
    const CMS_LIKE_LIST_TIME = -1;//缓存一个月
    const DB_NAME = 'dbActive';
    const DB_TABLE_NAME = 't_active_like_user';
    public function __construct()
    {
        $this->redis = $this->redis(\wepiao::getChannelId(), MOVIE_SHOW_DB);
    }

    /**
     * 获取真实点赞数
     * @param $id
     */
    public function getCmsLikeNum($id)
    {
        if($this->redis->WYexists(CMS_LIKE_LIST.$id)){
            $like = $this->redis->WYsCard(CMS_LIKE_LIST.$id);
        }else{  //被动
            $like = self::saveCacheByMysql($id);
        }
        $this->redis->WYexpire(CMS_LIKE_LIST.$id,self::CMS_LIKE_LIST_TIME);
        return $like;
    }

    /**
     * 当前用户是否点赞过当前内容
     * @param $id
     * @param $openId
     */
    public function isCmsLikeUser($id,$openId)
    {
        if(!$this->redis->WYexists(CMS_LIKE_LIST.$id)){//被动
            $like = self::saveCacheByMysql($id);
        }
        $like = $this->redis->WYsIsMember(CMS_LIKE_LIST.$id,$openId);
        return $like?1:0;
    }
    /**
     * 当前用户是否点赞过当前内容
     * @param $id
     * @param $openId
     */
    public function addLike($arrData)
    {
        $arrData = self::saveInsert($arrData);
        $this->pdo(self::DB_NAME, self::DB_TABLE_NAME);
        $insertRe = $this->pdohelper->Nadd($arrData, array_keys($arrData));
        if($insertRe){
            //插入集合
            $this->redis->WYsAdd(CMS_LIKE_LIST.$arrData['a_id'],$arrData['openId']);
        }
        return $insertRe;
    }
    /**
     * 当前用户是否点赞过当前内容
     * @param $id
     * @param $openId
     */
    public function delLike($id,$openId)
    {
        $fetchWhere = 'a_id=:a_id and openId = :openId';
        $fetchParams = [
            'a_id' => $id,
            'openId' => $openId
        ];
        $this->pdo(self::DB_NAME, self::DB_TABLE_NAME);
        $removeRe = $this->pdohelper->remove($fetchWhere, $fetchParams);
        if($removeRe){
            $remRe = $this->redis->WYsRem(CMS_LIKE_LIST.$id, $openId);//删除集合内容
        }
    }

    /**
     * 被动数据
     * @param $id
     */
    private function saveCacheByMysql($id)
    {
        $this->pdo(self::DB_NAME, self::DB_TABLE_NAME);
        $like = 0;
        $where = "a_id = '$id'";
        $dbRe = $this->pdohelper->fetchAll($where);
        if($dbRe){
            foreach($dbRe as $val){
                $this->redis->WYsAdd(CMS_LIKE_LIST.$id,$val->openId);
            }
            $like = count($dbRe);
        }else{
            $this->redis->WYsAdd(CMS_LIKE_LIST.$id,'');
            $this->redis->WYsRem(CMS_LIKE_LIST.$id,'');//删除集合内容
        }
        $this->redis->WYexpire(CMS_LIKE_LIST.$id,self::CMS_LIKE_LIST_TIME);
        return $like;
    }

    private function saveInsert($arrData)
    {
        $arrInsert = [];
        $arr=['a_id','openId','channelId','from'];
        foreach($arrData as $key=>$val){
            if(in_array($key,$arr)){
                $arrInsert[$key] = $val;
            }
        }
        if(empty($arrInsert))return false;
        $arrInsert['created'] = time();
        return $arrInsert;
    }
}
