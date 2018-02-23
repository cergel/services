<?php
/**
 * Created by PhpStorm.
 * User: liulong
 * Date: 16/9/7
 * Time: 下午5:50
 */
namespace sdkService\model;
class ActorUser extends BaseNew{
    const DB_NAME = 'dbActive';
    const DB_TABLE_NAME = 't_actor_user';

    const ACTOR_BASE_READ = 'actor_base_count_';
    const ACTOR_READ = 'actor_count_';
    const ACTOR_USER_ACTOR = 'actor_user_likes_';//用户维度的缓存

    public function __construct()
    {
        //都会使用redis
        $this->redis = $this->redis(\wepiao::getChannelId(), USER_COMMENT_CACHE);
    }


    /**
     * 当前用户是否赞（喜欢过当前明星）
     * @param $activeId
     * @param $openId
     * @return bool
     */
    public function isActiveUser($actorId,$openId)
    {
        $return = false;
        $userLikeActorRedisKey  = self::ACTOR_USER_ACTOR.$openId;
        if($this->redis->WYexists($userLikeActorRedisKey)){
            $res = $this->redis->WYzRank($userLikeActorRedisKey,$actorId);
            if($res !== false){
                $return = true;
            }
        }
        return $return;
    }

    /**
     * 添加用户喜欢的明星
     * @param $activeId
     * @param $openId
     */
    public function addActiveUser($actorId,$openId)
    {
        $time = time();
        $insertData = ['actro_id'=>$actorId,'open_id'=>$openId,'channelId'=>\wepiao::getChannelId(),'created'=>$time];
        $this->pdo(self::DB_NAME, self::DB_TABLE_NAME);
        $insertRe = $this->pdohelper->Nadd($insertData, array_keys($insertData));
        if($insertRe){
            $this->redis->WYzAdd(self::ACTOR_USER_ACTOR.$openId,$time,$actorId);
            $this->addActorLikeNum($actorId);
        }
        return $insertRe;
    }

    /**
     * 删除用户点赞信息
     * @param $actorId
     * @param $openId
     */
    public function delActiveUser($actorId,$openId)
    {
        $userLikeActorRedisKey  = self::ACTOR_USER_ACTOR.$openId;
        if($this->redis->WYexists($userLikeActorRedisKey)){
            $fetchWhere = 'actro_id=:actro_id and open_id = :open_id';
            $fetchParams = [
                'actro_id' => $actorId,
                'open_id' => $openId
            ];
            $this->pdo(self::DB_NAME, self::DB_TABLE_NAME);
            $removeRe = $this->pdohelper->remove($fetchWhere, $fetchParams);
            if($removeRe){
                $this->redis->WYzRem($userLikeActorRedisKey,$actorId);
                $this->delActorLikeNum($actorId);
            }
        }
    }
    /**
     * 添加用户喜欢的明星
     * @param $activeId
     * @param $openId
     */
    public function getActiveUserList($openId,$star,$end)
    {
        $arrData = $this->redis->WYzRevRange(self::ACTOR_USER_ACTOR.$openId,$star,$end);
        return $arrData;
    }
    /**
     * 添加用户喜欢的明星
     * @param $activeId
     * @param $openId
     */
    public function getActiveUserListCount($openId)
    {
        $count = $this->redis->WYzCard(self::ACTOR_USER_ACTOR.$openId);
        return $count?$count:0;
    }
    /**
     * 真实点赞数+1
     * @param $actorId
     */
    public function addActorLikeNum($actorId)
    {
        if($this->redis->WYexists(self::ACTOR_READ.$actorId)){
            $retLike = $this->redis->WYincr(self::ACTOR_READ.$actorId);
        }else{
            $retLike = $this->redis->WYset(self::ACTOR_READ.$actorId,1);
        }
        return $retLike;
    }
    /**
     * 真实点赞数-1
     * @param $actorId
     */
    public function delActorLikeNum($actorId)
    {
        $ret = false;
        if($this->redis->WYexists(self::ACTOR_READ.$actorId)){
            $ret = $this->redis->WYdecr(self::ACTOR_READ.$actorId);
        }
        return $ret;
    }


}