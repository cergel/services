<?php
/**
 * Created by PhpStorm.
 * User: liulong
 * Date: 16/9/7
 * Time: 下午5:50
 */
namespace sdkService\model;
class ActorAppraise extends BaseNew{
    const DB_NAME = 'dbActive';
    const DB_TABLE_NAME = 't_actor_appraise';

    const ACTOR_USER_MOVIE = 'actor_appraise_';//key名：actor_appraise_$open_id_$movie_id   value：$actorId
    const ACTOR_COUNT = 'actor_appraise_count_'; //key名：appraise_$movie_id   value ：actor_id=>int  哈希
    const ACTOR_USER_ACTOR = 'actor_appraise_cache_';//影片维度的缓存 key:actor_appraise_cache_$movie_id
    const ACTOR_CACHE_TIME = 60;

    public function __construct()
    {
        //都会使用redis
        $this->redis = $this->redis(\wepiao::getChannelId(), USER_COMMENT_CACHE);
    }


    /**
     * 获取当前用户对当前影片下评价的影人
     * @param $activeId
     * @param $openId
     * @return bool/int
     */
    public function getMovieActiveByUser($movieId,$openId)
    {
        $userLikeActorRedisKey  = self::ACTOR_USER_MOVIE.$openId.'_'.$movieId;
        $return = $this->redis->WYget($userLikeActorRedisKey);
        return empty($return)?0:$return;
    }

    /**
     * 获取当前影人下评价总数(一个)
     * @param $activeId
     * @param $openId
     */

    public function getMovieOneActorCount($movieId,$actorId)
    {

        $return = $this->redis->WYhGet(self::ACTOR_COUNT.$movieId,$actorId);
        return !empty($return)?$return:0;
    }

    /**
     * 获取当前影片下所有影人的评价总数
     * @param $movieId
     * @param $actorId
     */
    public function getMovieAllActorCount($movieId)
    {
        $arrData = $this->redis->WYhGetAll(self::ACTOR_COUNT.$movieId);
        return $arrData;
    }

    /**
     * 添加用户评价指定影片的指定影人
     * @param $movieId
     * @param $actorId
     * @param $openId
     * @return mixed
     */
    public function addMovieActiveByUser($movieId,$actorId,$openId)
    {
        $insertData = ['actro_id'=>$actorId,'open_id'=>$openId,'movie_id'=>$movieId,'channelId'=>\wepiao::getChannelId(),'created'=>time()];
        $this->pdo(self::DB_NAME, self::DB_TABLE_NAME);
        $insertRe = $this->pdohelper->Nadd($insertData, array_keys($insertData));
        if($insertRe){
            $this->redis->WYset(self::ACTOR_USER_MOVIE.$openId.'_'.$movieId,$actorId);//添加评价
            $this->addMovieActorNum($movieId,$actorId);
        }
        return $insertRe;
    }

    /**
     * 删除用户点赞信息
     * @param $actorId
     * @param $openId
     */
    public function delMovieActiveByUser($movieId,$actorId,$openId)
    {
        $removeRe = true;
        $userLikeActorRedisKey  = self::ACTOR_USER_MOVIE.$openId.'_'.$movieId;
        if($this->redis->WYexists($userLikeActorRedisKey)){
            $fetchWhere = 'movie_id=:movie_id and actro_id=:actro_id and open_id = :open_id';
            $fetchParams = [
                'movie_id' => $movieId,
                'open_id' => $openId,
                'actro_id' =>$actorId,
            ];
            $this->pdo(self::DB_NAME, self::DB_TABLE_NAME);
            $removeRe = $this->pdohelper->remove($fetchWhere, $fetchParams);
            if($removeRe){
                $this->redis->WYdelete(self::ACTOR_USER_MOVIE.$openId.'_'.$movieId);//删除评价记录
                $this->delMovieActorNum($movieId,$actorId);
            }
        }
        return $removeRe;
    }

    /**
     * 真实评价数+1
     * @param $actorId
     */
    public function addMovieActorNum($movieId,$actorId)
    {
        if($this->redis->WYhExists(self::ACTOR_COUNT.$movieId,$actorId)){
            $res = $this->redis->WYhIncrBy(self::ACTOR_COUNT.$movieId,$actorId,1);
        }else{
            $res = $this->redis->WYhSet(self::ACTOR_COUNT.$movieId,$actorId,1);
        }
        return $res;
    }
    /**
     * 真实评价数-1
     * @param $actorId
     */
    public function delMovieActorNum($movieId,$actorId)
    {
        if($this->redis->WYhExists(self::ACTOR_COUNT.$movieId,$actorId)){
            $res = $this->redis->WYhIncrBy(self::ACTOR_COUNT.$movieId,$actorId,-1);
        }else{
            $res = $this->redis->WYhSet(self::ACTOR_COUNT.$movieId,$actorId,0);
        }
        return $res;
    }

    /**
     * 更新缓存或写入缓存,基于缓存的缓存
     * @param $arrData
     * @param $movieId
     */
    public function isActorCache($movieId)
    {
        return $this->redis->WYexists(self::ACTOR_USER_ACTOR.$movieId);
    }

    /**
     * 获取基于缓存的缓存
     * @param $movieId
     */
    public function getActorCache($movieId)
    {
        $res = $this->redis->WYget(self::ACTOR_USER_ACTOR.$movieId);
        return !empty($res)?json_decode($res):false;
    }

    /**
     * 更新缓存或写入缓存,基于缓存的缓存
     * @param $arrData
     * @param $movieId
     */
    public function saveActorCache($arrData,$movieId)
    {
        $res = $this->redis->WYset(self::ACTOR_USER_ACTOR.$movieId,json_encode($arrData));
        $this->redis->WYexpire(self::ACTOR_USER_ACTOR.$movieId,self::ACTOR_CACHE_TIME);
        return $res;
    }


}