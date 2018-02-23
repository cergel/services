<?php
/**
 * Created by PhpStorm.
 * User: liulong
 * Date: 16/9/7
 * Time: 下午5:50
 */
namespace sdkService\model;
class Actor extends BaseNew{
    const DB_NAME = 'dbActive';
    const DB_TABLE_NAME = 't_actor';
    const ACTOR_BASE_READ = 'actor_base_count_';
    const ACTOR_READ = 'actor_count_';

    public function __construct()
    {
        //都会使用redis
        $this->redis = $this->redis(\wepiao::getChannelId(), USER_COMMENT_CACHE);
    }

    /**
     * 获取指定影人的注水喜欢数
     * @param $actorId
     */
    public function getActorBaseLike($actorId)
    {
        $retBaseLike = 0;
        if($this->redis->WYexists(self::ACTOR_BASE_READ.$actorId)){
            $retBaseLike = $this->redis->WYget(self::ACTOR_BASE_READ.$actorId);
        }else{
            $retBaseLike = $this->getMysqlBaseLike($actorId);
        }
        return $retBaseLike;
    }

    /**
     * 从数据库中读取，若没有，就插入一条
     * @param $actorId
     * @return int
     */
    private function getMysqlBaseLike($actorId)
    {
        $this->pdo(self::DB_NAME, self::DB_TABLE_NAME);
        $actorArray = $this->pdohelper->fetchOne('id=:id',[':id'=>$actorId]);
        $baseLike = 0;
        if(!$actorArray){
            $insertData = ['id'=>$actorId,'base_like'=>0,'like'=>0];
            $insertRe = $this->pdohelper->Nadd($insertData, array_keys($insertData));
            if($insertRe){//更新缓存
                $this->redis->WYset(self::ACTOR_BASE_READ.$actorId,0);
                $this->redis->WYset(self::ACTOR_READ.$actorId,0);
            }
        }else{
            if(isset($actorArray['base_like'])){
                $this->redis->WYset(self::ACTOR_BASE_READ.$actorId,$actorArray['base_like']);
                $baseLike = $actorArray['base_like'];
            }else{
                $this->redis->WYset(self::ACTOR_BASE_READ.$actorId,0);
            }
        }
        return $baseLike;
    }
    /**
     * 获取指定影人的真实喜欢数
     * @param $actorId
     */
    public function getActorLike($actorId)
    {
        $retLike = 0;
        if($this->redis->WYexists(self::ACTOR_READ.$actorId)){
            $retLike = $this->redis->WYget(self::ACTOR_READ.$actorId);
        }
        return $retLike;
    }

    /**
     * 更新注水数
     * @param $actorId
     * @param $num
     */
    public function saveActorBaseLike($actorId,$num)
    {
        $this->redis->WYset(self::ACTOR_BASE_READ.$actorId,$num);
        if(!$this->redis->WYexists(self::ACTOR_BASE_READ.$actorId)){
            $retBaseLike = $this->getMysqlBaseLike($actorId);
        }
        $retBaseLike = $this->redis->WYset(self::ACTOR_BASE_READ.$actorId,$num);
        return $retBaseLike;
    }

    /**
     * 获取影人的总喜欢数
     * @param $actorId
     */
    public function getActorLikeCount($actorId)
    {
        $num = $this->getActorLike($actorId) + $this->getActorBaseLike($actorId);
        return $num;
    }

}