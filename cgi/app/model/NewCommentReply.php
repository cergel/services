<?php

namespace sdkService\model;

/**
 * 新版的评论采用缓存去生成
 * @tutorial 评论相关功能
 * @author liulong
 *#
 */
class NewCommentReply extends BaseNew
{
    const LAST_REPLY_CACHE_EXPIRE = 60;//回复时间为60秒
    const REPLY_CACHE_EXPIRE = 172800;//评论回复详情的缓存时间
    const DB_CONFIG_KEY = 'dbApp';
    protected  $dbConfig = 'dbApp';
    const TABLE_NAME = 't_comment_reply';
    protected $tableName = 't_comment_reply';

    public function __construct()
    {
        $this->redis = $this->redis(\wepiao::getChannelId(), USER_COMMENT_CACHE);
    }


    //db对评论进行回复 【测通】
    public function insertReply($arrInput)
    {
        $mustFields = ['commentId', 'ucid', 'content'];
        $arrFields = ['commentId', 'ucid', 'uid', 'channelId', 'fromId', 'content', 'favorCount', 'status', 'checkstatus', 'created', 'updated'];
        $arrData = $this->formatInputArray($arrInput, $arrFields, $mustFields);
        $insertRe = $this->getPdoHelper()->Nadd($arrData, array_keys($arrData));
        if ($insertRe) {
            $return = true;
            $commentId = $arrData['commentId'];
            $time = $arrData['created'];
            $ucid = $arrData['ucid'];
            $replyId = $insertRe;

            $fetchRe = $this->getPdoHelper()->fetchOne('id=:id',[':id'=>$replyId]);
            if($fetchRe){
                $replyInfo = json_encode($fetchRe);
                //cache回复内容
                $this->setReplyContentCache($replyId, $replyInfo);
                //cache增加回复列表
                $this->addReplyList($commentId, $time, $replyId);
                //修改最后回复时间
                $this->setLastTime($ucid, $time);
            }
        } else {
            $return = false;
        }
        return $return;
    }

    //cache回复内容
    protected function setReplyContentCache($replyId, $replyInfo)
    {
        $input = ['replyId' => $replyId];
        $keyTemplate = NEWCOMMENT_REPLY_CONTENT;
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        $redisRe = $this->redis->WYset($redisKey, $replyInfo);
        $this->redis->WYexpire($redisKey,self::REPLY_CACHE_EXPIRE);
        return $redisRe;
    }

    //cache增加回复列表
    protected function addReplyList($commentId, $time, $replyId)
    {
/*        $input = ['commentId' => $commentId];
        $keyTemplate = NEWCOMMENT_REPLY_LIST;
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        $redisRe = $this->redis->WYzAdd($redisKey, $time, $replyId);
        return $redisRe;*/

        $arrKey = ['commentId' => $commentId];
        $arrKeyTemplate = NEWCOMMENT_REPLY_LIST;
        $arrCountKey = ['commentId' => $commentId];
        $arrCountKeyTemplate = NEWCOMMENT_REPLY_LIST_COUNT;
        $expire = self::REPLY_CACHE_EXPIRE;
        $score = $time;
        $value = $replyId;
        return $this->addZsetCache($arrKey,$arrKeyTemplate, $arrCountKey,$arrCountKeyTemplate, $score, $value,$expire);
    }

    //设置此用户最后回复时间
    protected function setLastTime($ucid, $time)
    {
        $input = ['ucid' => $ucid];
        $keyTemplate = NEWCOMMENT_REPLY_LAST_TIME;
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        $redisRe = $this->redis->WYset($redisKey, $time);//todo
        $this->redis->WYexpire($redisKey,self::LAST_REPLY_CACHE_EXPIRE);
        return $redisRe;
    }



    //db删除回复 【测通】
    public function delReply($replyId)
    {
        $where = "id = :id";
        $params['id'] = $replyId;
        $fetchRe = $this->getPdoHelper()->fetchOne($where, $params);
        if ($fetchRe) {
            $delRe = $this->getPdoHelper()->remove($where, $params);
            if ($delRe) {
                $return = true;
                $commentId = $fetchRe['commentId'];
                //回复内容（删除当前内容）
                $this->delReplyContentCache($replyId);
                //回复列表（在有序集合内删除当前条记录）
                $this->remReplyList($commentId, $replyId);
            } else {
                $return = false;
            }
        } else {
            $return = false;
        }
        return $return;
    }


    //回复内容（删除当前内容）
    protected function delReplyContentCache($replyId)
    {
        $input = ['replyId' => $replyId];
        $keyTemplate = NEWCOMMENT_REPLY_CONTENT;
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        $redisRe = $this->redis->WYdelete($redisKey);
        return $redisRe;
    }

    //回复列表（在有序集合内删除当前条记录）
    protected function remReplyList($commentId, $replyId)
    {
//        $input = ['commentId' => $commentId];
//        $keyTemplate = NEWCOMMENT_REPLY_LIST;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//        $redisRe = $this->redis->WYzRem($redisKey, $replyId);
//        return $redisRe;

        $arrKey = ['commentId' => $commentId];
        $arrKeyTemplate = NEWCOMMENT_REPLY_LIST;
        $arrCountKey=['commentId' => $commentId];
        $arrCountKeyTemplate = NEWCOMMENT_REPLY_LIST_COUNT;
        $value = $replyId;
        return $this->delZsetCache($arrKey, $arrKeyTemplate,$arrCountKey, $arrCountKeyTemplate,$value);
    }

    //管理员屏蔽【测通】
    public function managerScreen($replyId)
    {
        $fetchWhere = 'id = :id';
        $fetchParams = [':id' => $replyId];
        $fetchRe = $this->getPdoHelper()->fetchOne($fetchWhere, $fetchParams);
        $commentId = $fetchRe['commentId'];
        $return = true;
        //回复列表
        $this->remReplyList($commentId, $replyId);
        $replyInfo = $fetchRe;
        $replyInfo['status'] = '0';
        //修改缓存内容
        $this->setReplyContentCache($replyId, json_encode($replyInfo));
        return $return;
    }

    //管理员修改【测通】
    public function mamagerUpdate($replyId)
    {
        $fetchWhere = 'id = :id';
        $fetchParams = [':id' => $replyId];
        $fetchRe = $this->getPdoHelper()->fetchOne($fetchWhere, $fetchParams);
        if ($fetchRe) {
            $return = $this->setReplyContentCache($replyId, json_encode($fetchRe));
        } else {
            $return = false;
        }
        return $return;
    }

    //传入ucid，获取用户最后一次回复的时间
    public function getUserLastReplyTime($ucid)
    {
        $input = ['ucid' => $ucid];
        $keyTemplate = NEWCOMMENT_REPLY_LAST_TIME;
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        return $this->redis->WYget($redisKey);
    }

    //接受commentId返回回复列表
    public function getReplyList($commentId, $start = 0, $end = 10)
    {
//        $input = ['commentId' => $commentId];
//        $keyTemplate = NEWCOMMENT_REPLY_LIST;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//        return $this->redis->WYzRange($redisKey, $start, $end);

        $arrRedisInfo = [
            'redisKey'=>['commentId' => $commentId],
            'redisKeyTemplate'=>NEWCOMMENT_REPLY_LIST,
            'redisCountKey'=>['commentId'=>$commentId],
            'redisCountKeyTemplate'=>NEWCOMMENT_REPLY_LIST_COUNT,
            'scoreField'=>'created',
            'valueField'=>'id',
            'start'=>$start,
            'end'=>$end,
            'expire'=>self::REPLY_CACHE_EXPIRE,
        ];
        $arrSqlInfo=[
            'table'=>'t_comment_reply',
            'where'=>'commentId = :commentId and `status`=1',
            'params'=>['commentId'=>$commentId],
            'orderBy'=>'created desc',
            'step'=> 1000,
        ];
        return $this->queryZsetCache($arrRedisInfo,$arrSqlInfo,false);
    }

    //接受replyId返回回复内容
    public function getReplyInfo($replyId)
    {
//        $input = ['replyId' => $replyId];
//        $keyTemplate = NEWCOMMENT_REPLY_CONTENT;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//        return $this->commentReplyRedis->WYget($redisKey);

        $input = ['replyId' => $replyId];
        $keyTemplate = NEWCOMMENT_REPLY_CONTENT;
        $where="id = :id";
        $whereParams = [':id'=>$replyId];
        return $this->queryStringCache($input,$keyTemplate,self::DB_CONFIG_KEY,self::TABLE_NAME,self::REPLY_CACHE_EXPIRE,$where,$whereParams);
    }

    //接受commentId返回有多少条回复
    public function getReplyCount($commentId)
    {
        $input = ['commentId' => $commentId];
        $keyTemplate = NEWCOMMENT_REPLY_LIST_COUNT;
        $table = "t_comment_reply";
        $where = "commentId = :commentId  and `status`=1";
        $params = [
            ':commentId'=>$commentId
        ];
        return $this->queryZsetCacheCount($input,$keyTemplate,$table,$where,$params,self::REPLY_CACHE_EXPIRE);
    }
}
