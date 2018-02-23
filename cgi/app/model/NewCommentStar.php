<?php

namespace sdkService\model;

/**
 * 明星相关
 * @tutorial 评论相关功能
 * @author liulong
 *#
 */
class NewCommentStar extends BaseNew
{


    public function __construct()
    {
        $this->redis = $this->redis(\wepiao::getChannelId(), USER_COMMENT_CACHE);
    }
    /**
     * 修改或新增
     * @param $ucid
     * @param $str
     * @return bool
     */
     public function saveRedis($ucid,$str)
     {
         $inpuKey = ['ucid'=>$ucid];
         $redisKey = $this->swtichRedisKey($inpuKey, NEWCOMMENT_COMMENT_STAR);
         $redisRe = $this->redis->WYset($redisKey, $str);
         return $redisRe;
     }

    /**
     * 删除
     * @param $ucid
     */
    public function delRedis($ucid)
    {
        $inpuKey = ['ucid'=>$ucid];
        $redisKey = $this->swtichRedisKey($inpuKey, NEWCOMMENT_COMMENT_STAR);
        $redisRe = $this->redis->WYdelete($redisKey);
        return $redisRe;
    }

    /**
     * @param $ucid
     * @return bool|mixed
     */
    public function getCommentStarByUcid($ucid){
        $inpuKey = ['ucid'=>$ucid];
        $redisKey = $this->swtichRedisKey($inpuKey, NEWCOMMENT_COMMENT_STAR);
        $redisRe = $this->redis->WYget($redisKey);
        return empty($redisRe)?false:json_decode($redisRe,true);
    }

}
