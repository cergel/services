<?php

namespace sdkService\model;

/**
 * 新版的评论采用缓存去生成
 * @tutorial 评论相关功能
 * @author liulong
 *#
 */
class NewCommentFavor extends BaseNew
{
    const CACHE_EXPIRE_COMMENT_LIST = 172800;//用户点赞的评论列表的缓存
    const CACHE_EXPIRE_USER_LIST = 172800;//评论点赞的用户的缓存时间
    const USER_CACHE_EXPIRE = 172800;//用户维度的缓存过期时间

    const DB_CONFIG_KEY = 'dbApp';
    protected  $dbConfig = 'dbApp';
    const TABLE_NAME = 't_favor_comment';
    protected $tableName = 't_favor_comment';

    public function __construct()
    {
        $this->redis = $this->redis(\wepiao::getChannelId(), USER_COMMENT_CACHE);
    }


    //插入点赞 todo 【测通】
    public function insertFavor($arrInput)
    {
        $arrFields = ['ucid', 'uid', 'commentId', 'channelId', 'fromId', 'created'];
        $mustFields = ['ucid', 'commentId', 'created'];
        $arrData = $this->formatInputArray($arrInput, $arrFields, $mustFields);
        $insertRe = $this->getPdoHelper()->Nadd($arrData, array_keys($arrData));
        if ($insertRe) {
            $ucid = $arrData['ucid'];
            $time = $arrData['created'];
            $commentId = $arrData['commentId'];
            $return = true;
            //将某用户添加进点赞列表
            $this->addFavorUserList($ucid, $time, $commentId);
            //添加进改变过的集合
            $this->addChangeSet($commentId);
        } else {
            $return = false;
        }
        return $return;
    }

    //将某用户添加进点赞列表
    protected function addFavorUserList($ucid, $time, $commentId)
    {
//        $input = ['commentId' => $commentId];
//        $keyTemplate = NEWCOMMENT_FAVOR_USER_LIST;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//        $redisRe = $this->commentRedis->WYzAdd($redisKey, $time, $ucid);
//        return $redisRe;
        //todo 被动缓存

        $arrKey = ['commentId' => $commentId];
        $keyTemplate = NEWCOMMENT_FAVOR_USER_LIST;
        $countKey = ['commentId' => $commentId];
        $countKeyTemplate = NEWCOMMENT_FAVOR_USER_LIST_COUNT;
        $score = $time;
        $value = $ucid;
        $expire = self::CACHE_EXPIRE_USER_LIST;
        return $this->addZsetCache($arrKey, $keyTemplate, $countKey, $countKeyTemplate, $score, $value, $expire);
    }

    public function addChangeSet($commentId){
        $redisKey = NEWCOMMENT_COMMENT_FAVOR_CHANGE_SET;
        return $this->redis->WYsAdd($redisKey,$commentId);
    }



    //取消点赞 【测通】
    public function deleteFavor($arrInput)
    {
        $arrFields = ['ucid', 'commentId'];
        $mustFields = ['ucid', 'commentId'];
        $arrData = $this->formatInputArray($arrInput, $arrFields, $mustFields);
        $ucid = $arrData['ucid'];
        $commentId = $arrData['commentId'];
        $where = 'ucid=:ucid and commentId = :commentId';
        $params = [
            'ucid' => $ucid,
            'commentId' => $commentId
        ];
        $deleteRe = $this->getPdoHelper()->remove($where, $params);
        if ($deleteRe) {
            $return = true;
            //将某用户在点赞列表中删除
            $this->remFavorUserList($ucid, $commentId);
            //添加进改变过的集合
            $this->addChangeSet($commentId);
        } else {
            $return = false;
        }
        return $return;
    }

    //将某用户在点赞列表中删除
    protected function remFavorUserList($ucid, $commentId)
    {
//        $input = ['commentId' => $commentId];
//        $keyTemplate = NEWCOMMENT_FAVOR_USER_LIST;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//        $redisRe = $this->commentRedis->WYzRem($redisKey, $ucid);
//        return $redisRe;

        $arrKey = ['commentId' => $commentId];
        $keyTemplate = NEWCOMMENT_FAVOR_USER_LIST;
        $countKey = ['commentId' => $commentId];
        $countKeyTemplate = NEWCOMMENT_FAVOR_USER_LIST_COUNT;
        $value = $ucid;
        return $this->delZsetCache($arrKey, $keyTemplate, $countKey, $countKeyTemplate, $value);
    }


    //获取用户全部的点赞评论【没有地方用到】
    public function getUserFavorComment($ucid)
    {
//        $input = ['ucid' => $ucid];
//        $keyTemplate = NEWCOMMENT_FAVOR_COMMENT_LIST;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//        $redisRe = $this->commentRedis->WYzRevRange($redisKey, 0, -1);
//        return $redisRe;

        return false;
    }

    //获取指定评论的点赞数
    public function getCommentNumber($commentId)
    {
//        $input = ['commentId' => $commentId];
//        $keyTemplate = NEWCOMMENT_FAVOR_USER_LIST;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//        $redisRe = $this->commentRedis->WYzCard($redisKey);
//        return $redisRe;

        $arrInputKey = ['commentId' => $commentId];
        $keyTemplate = NEWCOMMENT_FAVOR_USER_LIST_COUNT;
        $table = "t_favor_comment";
        $where = "commentId = :commentId";
        $params = [":commentId" => $commentId];
        $expire = self::CACHE_EXPIRE_USER_LIST;
        return $this->queryZsetCacheCount($arrInputKey, $keyTemplate, $table, $where, $params, $expire);
    }

    //用户对此评论是否点过赞
    public function isFavor($commentId, $ucid)
    {
        $input = ['commentId' => $commentId];
        $keyTemplate = NEWCOMMENT_FAVOR_USER_LIST;
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        $keyExists = $this->redis->WYexists($redisKey);
        if($keyExists){
            $redisRe = $this->redis->WYzRank($redisKey, $ucid);
            $return = $redisRe===false ? false : true;
        }else{
            $sql = "select ucid,created from t_favor_comment where commentId = :commentId";
            $params = [":commentId"=>$commentId];
            $dbRe = $this->getPdoHelper()->fetchArrayBySql($sql,$params);
            if($dbRe){
                $return = false;
                foreach($dbRe as $v){
                    $this->redis->WYzAdd($redisKey,$v['created'],$v['ucid']);
                    if($v['ucid']==$ucid){
                        $return = true;
                    }
                }
            }else{
                $this->redis->WYzAdd($redisKey,0,'');
                $return = false;
            }
        }
        $this->redis->WYexpire($redisKey,self::CACHE_EXPIRE_COMMENT_LIST);
        return $return;
    }
}
