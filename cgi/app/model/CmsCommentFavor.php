<?php

namespace sdkService\model;

/**
 * 新版的评论采用缓存去生成
 * @tutorial 评论相关功能
 * @author liulong
 *#
 */
class CmsCommentFavor extends BaseNew
{
    const DB_CONFIG_KEY = 'dbApp';

    const TABLE_NAME = 't_cms_comment_favor';

    protected $dbConfig = 'dbApp';
    protected $tableName = 't_cms_comment_favor';

    const COMMENT_INFO_CACHE_EXPIRE = 86400;//评论信息缓存时间,1天
    const USER_CACHE_EXPIRE = 172800;//用户维度的缓存时间，2天
    const CMS_CACHE_EXPIRE = 864000;//影片维度的缓存时间,10天

    const STEP_COMMENTED_USERS = 2000;//评论列表的步长值
    const STEP_COMMENTED_CMS = 1000;//用户评论过的影片的步长值

    public function __construct()
    {
//        $this->pdo(self::DB_CONFIG_KEY, self::TABLE_NAME);
        $this->redis = $this->redis(\wepiao::getChannelId(), USER_COMMENT_CACHE);
    }

    //给评论点赞
    /**
     *
     * @param $arrInput=>[
     *  "comment_id"=>评论id,
     *  "open_id"=>用户openid,
     *  "channel_id"=>渠道id,
     *  "from"=>来源,
     *  "created"=>创建时间
     * ]
     *
     * @return bool
     */
    //返回：bool
    public function favor($inputArrData){
        $arrFields = ['comment_id','open_id','channel_id','from','created'];
        $mustFields = ['comment_id','open_id','created'];
        $arrInput = $this->formatInputArray($inputArrData, $arrFields, $mustFields);
        $this->pdo(self::DB_CONFIG_KEY, self::TABLE_NAME);
        $favorRe = $this->getPdoHelper()->Nadd($arrInput, array_keys($arrInput));
        if($favorRe){
            $commentId = $arrInput["comment_id"];
            $openId = $arrInput['open_id'];
            $time = $arrInput['created'];
            //添加进用户点赞列表
            $this->addUserFavorList($openId,$time,$commentId);
            //评论的点赞数+1
            $this->incrCommentFavorNum($commentId);
            //将有改变的评论添加进集合
            $this->addChangeComment($commentId);
            $return = true;
        }else{
            $return = false;
        }
        return $return;
    }

    //添加进用户点赞列表
    protected function addUserFavorList($openId,$time,$commentId){
        $arrKey = ['open_id' => $openId];
        $keyTemplate = CMS_COMMENT_USER_FAVOR_LIST;
        $countKey = ['open_id' => $openId];
        $countKeyTemplate = CMS_COMMENT_USER_FAVOR_LIST_COUNT;
        $score = $time;
        $value = $commentId;
        $expire = self::CMS_CACHE_EXPIRE;
        return $this->addExistZsetCache($arrKey, $keyTemplate, $countKey, $countKeyTemplate, $score, $value, $expire);
    }

    //评论的点赞数+1
    protected function incrCommentFavorNum($commentId){
        $input = ['commentId' => $commentId];
        $keyTemplate = CMS_COMMENT_FAVOR_REALNUM;
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        return $this->redis->WYincr($redisKey);
    }

    //取消对评论的点赞
    //返回：bool
    public function unFavor($commentId,$openId){
        $fetchWhere = 'comment_id = :comment_id and open_id = :open_id';
        $fetchParams[':comment_id'] = $commentId;
        $fetchParams[':open_id'] = $openId;
        $this->pdo(self::DB_CONFIG_KEY, self::TABLE_NAME);
        $fetchRe = $this->getPdoHelper()->fetchOne($fetchWhere, $fetchParams);
        if($fetchRe){
            $removeRe = $this->getPdoHelper()->remove($fetchWhere, $fetchParams);
            if($removeRe){
                $return = true;
                $openId = $fetchRe['open_id'];
                //从用户的点赞列表中移除
                $this->delUserFavorList($openId,$commentId);
                //评论的点赞数-1
                $this->decrCommentFavorNum($commentId);
                //将有改变的评论添加进集合
                $this->addChangeComment($commentId);
            }else{
                $return = false;
            }
        }else{
            $return = false;
        }
        return $return;
    }

    //从用户的点赞列表中移除
    protected function delUserFavorList($openId,$commentId){
        $arrKey = ['open_id'=>$openId];
        $keyTemplate = CMS_COMMENT_USER_FAVOR_LIST;
        $countKey = ['open_id'=>$openId];
        $countKeyTemplate = CMS_COMMENT_USER_FAVOR_LIST_COUNT;
        $value = $commentId;
        return $this->delZsetCache($arrKey, $keyTemplate, $countKey, $countKeyTemplate, $value);

    }

    //评论的点赞数-1
    protected function decrCommentFavorNum($commentId){
        $input = ['commentId' => $commentId];
        $keyTemplate = CMS_COMMENT_FAVOR_REALNUM;
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        $num = $this->redis->WYget($redisKey);
        if($num>0){
            $return =  $this->redis->WYdecr($redisKey);
        }else{
            $return = false;
        }
        return $return;
    }

    //获取评论的点赞数量
    //返回：int
    public function getFavorNum($commentId){
        $input = ['commentId' => $commentId];
        $keyTemplate = CMS_COMMENT_FAVOR_REALNUM;
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        $num = $this->redis->WYget($redisKey);
        if(empty($num)){
            $return = 0;
        }else{
            $return = $num;
        }
        return $return;
    }

    //此openid对commentid是否点过赞
    //返回：bool
    public function isFavor($openId,$commentId){
        $arrRedisInfo = [
            'redisKey'=>['open_id' => $openId],
            'redisKeyTemplate'=>CMS_COMMENT_USER_FAVOR_LIST,
            'redisCountKey'=>['open_id' => $openId],
            'redisCountKeyTemplate'=>CMS_COMMENT_USER_FAVOR_LIST_COUNT,
            'scoreField'=>"created",
            'valueField'=>"comment_id",
            'start'=>0,
            'end'=>1000,
            'expire'=>self::CMS_CACHE_EXPIRE,
        ];
        $arrSqlInfo = [
            'table'=>"t_cms_comment_favor",
            'where'=>"open_id = :open_id ",
            'params'=>[':open_id'=>$openId],
            'orderBy'=>"created desc",
            'step'=> self::STEP_COMMENTED_USERS,
        ];
        $arr=$this->queryZsetCache($arrRedisInfo, $arrSqlInfo);
        if($arr!==false && in_array($commentId,$arr)){
            $return = true;
        }else{
            $return = false;
        }
        return $return;
    }


    //将有改变的评论id加入到集合中
    protected function addChangeComment($commentId){
        $redisKey = CMS_COMMENT_CHANAGE_SET;
        return $this->redis->WYsAdd($redisKey,$commentId);
    }
}
