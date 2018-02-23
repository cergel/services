<?php

namespace sdkService\model;

/**
 * 新版的评论采用缓存去生成
 * @tutorial 评论相关功能
 * @author liulong
 *#
 */
class CmsComment extends BaseNew
{
    const DB_CONFIG_KEY = 'dbApp';

    const TABLE_NAME = 't_cms_comment';

    protected $dbConfig = 'dbApp';
    protected $tableName = 't_cms_comment';


    const COMMENT_INFO_CACHE_EXPIRE = 86400;//评论信息缓存时间,1天
    const USER_CACHE_EXPIRE = 172800;//用户维度的缓存时间，2天
    const CMS_CACHE_EXPIRE = 864000;//影片维度的缓存时间,10天

    const STEP_COMMENTED_USERS = 2000;//评论列表的步长值
    const STEP_COMMENTED_MOVIES = 1000;//用户评论过的影片的步长值

    public function __construct()
    {
        $this->redis = $this->redis(\wepiao::getChannelId(), USER_COMMENT_CACHE);
    }

    //查询热门评论列表的Id
    //返回：索引数组
    public function queryHotCommentList($aid,$start,$end){
        $arrRedisInfo = [
            'redisKey'=>['a_id' => $aid],
            'redisKeyTemplate'=>CMS_COMMENT_HOT_LIST,
            'redisCountKey'=>['a_id' => $aid],
            'redisCountKeyTemplate'=>CMS_COMMENT_HOT_LIST_COUNT,
            'scoreField'=>"hot_order",
            'valueField'=>"id",
            'start'=>$start,
            'end'=>$end,
            'expire'=>self::CMS_CACHE_EXPIRE,
        ];
        $arrSqlInfo = [
            'table'=>"t_cms_comment",
            'where'=>"a_id = :a_id and `status`=1 ",
            'params'=>[':a_id'=>$aid],
            'orderBy'=>"hot_order desc",
            'step'=> self::STEP_COMMENTED_USERS,
        ];
        return $this->queryZsetCache($arrRedisInfo, $arrSqlInfo);
    }

    //查询最新评论列表的Id
    //返回：索引数组
    public function queryNewCommentList($aid,$start,$end){
        $arrRedisInfo = [
            'redisKey'=>['a_id' => $aid],
            'redisKeyTemplate'=>CMS_COMMENT_NEW_LIST,
            'redisCountKey'=>['a_id' => $aid],
            'redisCountKeyTemplate'=>CMS_COMMENT_NEW_LIST_COUNT,
            'scoreField'=>"created",
            'valueField'=>"id",
            'start'=>$start,
            'end'=>$end,
            'expire'=>self::CMS_CACHE_EXPIRE,
        ];
        $arrSqlInfo = [
            'table'=>"t_cms_comment",
            'where'=>"a_id = :a_id and `status`=1 ",
            'params'=>[':a_id'=>$aid],
            'orderBy'=>"created desc",
            'step'=> self::STEP_COMMENTED_USERS,
        ];
        return $this->queryZsetCache($arrRedisInfo, $arrSqlInfo);
    }

    //根据评论Id获取内容
    //返回：关联数组
    public function queryContent($commentId){
        $input = ['comment_id' => $commentId];
        $keyTemplate = CMS_COMMENT_CONTENT;
        $where = 'id = :id';
        $whereParams = [':id'=>$commentId];
        $redisRe = $this->queryStringCache($input,$keyTemplate,self::DB_CONFIG_KEY,self::TABLE_NAME,self::COMMENT_INFO_CACHE_EXPIRE,$where,$whereParams);
        return json_decode($redisRe,1);
    }

    //根据cmsId返回它的评论数
    //返回：int
    public function queryCommentNum($aid){
        $arrInputKey = ['a_id' => $aid];
        $keyTemplate = CMS_COMMENT_NEW_LIST_COUNT;
        $table = self::TABLE_NAME;
        $where = "a_id = :a_id and `status`=1";
        $params = [":a_id" => $aid];
        $expire = self::CMS_CACHE_EXPIRE;
        return $this->queryZsetCacheCount($arrInputKey, $keyTemplate, $table, $where, $params, $expire);
    }

    //插入评论内容
    /**
     * @param $arrInput=>[
     * "aid"=>cms的id,
     * "openId"=>用户的openId,
     * "channelId"=>渠道ID,
     * "content"=>评论内容,
     * "from"=>来源,
     * "status"=>后台删除状态,
     * "checkstatus"=>是否含敏感词,
     * "baseFavorCount"=>注水点赞数,
     * "favorCount"=>真实点赞数,
     * "baseFavorCount"=>注水点赞数,
     *
     * ]
     */
    //返回int
    public function insertContent($inputArrData,$spaceTime){
        $arrFields = ['a_id','open_id','channel_id','content','from','status','checkstatus','base_favor_count','favor_count','created','hot_order'];
        $mustFields = ['a_id','open_id','channel_id','content','created'];
        $arrInput = $this->formatInputArray($inputArrData, $arrFields, $mustFields);
        $commentId = $this->getPdoHelper()->Nadd($arrInput, array_keys($arrInput));
        if($commentId){
            $return = $commentId;
            $aid = $arrInput['a_id'];
            $time = $arrInput['created'];
            $openId = $arrInput['open_id'];
            //添加热评
            $this->addHotList($aid,$commentId);
            //添加最新
            $this->addNewList($aid,$time,$commentId);
            //添加间隔时间
            $this->addSpacingTime($openId,$spaceTime);
        }else{
            $return = 0;
        }
        return $return;
    }

    //将评论添加进热评列表
    protected function addHotList($aid,$commentId){
        $arrKey = ['a_id' => $aid];
        $keyTemplate = CMS_COMMENT_HOT_LIST;
        $countKey = ['a_id' => $aid];
        $countKeyTemplate = CMS_COMMENT_HOT_LIST_COUNT;
        $score = 0;
        $value = $commentId;
        $expire = self::CMS_CACHE_EXPIRE;
        return $this->addExistZsetCache($arrKey, $keyTemplate, $countKey, $countKeyTemplate, $score, $value, $expire);
    }

    //将评论添加进最新评论列表
    protected function addNewList($aid,$time,$commentId){
        $arrKey = ['a_id' => $aid];
        $keyTemplate = CMS_COMMENT_NEW_LIST;
        $countKey = ['a_id' => $aid];
        $countKeyTemplate = CMS_COMMENT_NEW_LIST_COUNT;
        $score = $time;
        $value = $commentId;
        $expire = self::CMS_CACHE_EXPIRE;
        return $this->addExistZsetCache($arrKey, $keyTemplate, $countKey, $countKeyTemplate, $score, $value, $expire);
    }

    //将用户发评论添加到间隔时间中
    protected function addSpacingTime($openId,$spaceTime){
        $arrKey = ['open_id' => $openId];
        $keyTemplate = CMS_COMMENT_TIMESPACING;
        $redisKey = $this->swtichRedisKey($arrKey,$keyTemplate);
        return $this->redis->WYset($redisKey,1,$spaceTime);
    }

    //用户删除评论内容
    //返回：bool
    public function delContent($commentId){
        $fetchWhere = 'id = :id';
        $fetchParams[':id'] = $commentId;
        $fetchRe = $this->getPdoHelper()->fetchOne($fetchWhere, $fetchParams);
        if($fetchRe){
            $removeRe = $this->getPdoHelper()->remove($fetchWhere, $fetchParams);
            if($removeRe){
                $return = true;
                $aid = $fetchRe['a_id'];
                //删除热门评论列表缓存
                $this->delHotList($aid,$commentId);
                //删除最新评论列表缓存
                $this->delNewList($aid,$commentId);
            }else{
                $return = false;
            }
        }else{
            $return = false;
        }
        return $return;
    }

    //删除热门评论列表缓存
    protected function delHotList($aid,$commentId){
        $arrKey = ['a_id'=>$aid];
        $keyTemplate = CMS_COMMENT_HOT_LIST;
        $countKey = ['a_id'=>$aid];
        $countKeyTemplate = CMS_COMMENT_HOT_LIST_COUNT;
        $value = $commentId;
        return $this->delZsetCache($arrKey, $keyTemplate, $countKey, $countKeyTemplate, $value);
    }

    //删除最新评论列表缓存
    protected function delNewList($aid,$commentId){
        $arrKey = ['a_id'=>$aid];
        $keyTemplate = CMS_COMMENT_NEW_LIST;
        $countKey = ['a_id'=>$aid];
        $countKeyTemplate = CMS_COMMENT_NEW_LIST_COUNT;
        $value = $commentId;
        return $this->delZsetCache($arrKey, $keyTemplate, $countKey, $countKeyTemplate, $value);
    }

    //获取用户最后一次评论时间
    //返回：bool
    public function commentSpacing($openId){
        $arrKey = ['open_id' => $openId];
        $keyTemplate = CMS_COMMENT_TIMESPACING;
        $redisKey = $this->swtichRedisKey($arrKey,$keyTemplate);
        return $this->redis->WYexists($redisKey);
    }

    ############以下为后台管理员操作##############
    /**
     * 管理员修改评论
     * @param $commentId
     * @param $arrInfo ["content"=>"","base_favor_count"=>""]
     */
    //返回：bool
    public function managerUpdateComment($commentId,$inputArrData){
        $arrFields = ['a_id','open_id','channel_id','content','from','status','checkstatus','base_favor_count','favor_count','created','hot_order'];
        $mustFields = ['content','base_favor_count'];
        $arrInput = $this->formatInputArray($inputArrData, $arrFields, $mustFields);
        $fields = ['content','base_favor_count'];
        $params = [
            ':content'=>$arrInput['content'],
            ':base_favor_count'=>$arrInput['base_favor_count'],
            ':id'=>$commentId
        ];
        $where = "id = :id";
        $updateRe = $this->getPdoHelper()->update($fields, $params, $where);
        if($updateRe){
            $return = true;
            $whereParams=[':id'=>$commentId];
            $dbRe = $this->getPdoHelper()->fetchOne($where, $whereParams);
            if($dbRe){
                $input = ['comment_id' => $commentId];
                $keyTemplate = CMS_COMMENT_CONTENT;
                $redisKey = $this->swtichRedisKey($input, $keyTemplate);
                $value = json_encode($dbRe);
                $redisRe = $this->redis->WYset($redisKey,$value,self::USER_CACHE_EXPIRE);
                //将此加入到hot_order
                $chanageKey = CMS_COMMENT_CHANAGE_SET;
                $this->redis->WYsAdd($chanageKey,$commentId);
            }
        }else{
            $return = false;
        }
        return $return;
    }

    //管理员屏蔽评论
    //返回：bool
    public function managerScreenComment($commentId){
        $fields = ['status'];
        $params = [
            ':status'=>0,
            ':id'=>$commentId,
        ];
        $where = "id = :id";
        $updateRe = $this->getPdoHelper()->update($fields, $params, $where);
        if($updateRe){
            //修改评论内容缓存
            $whereParams=[":id"=>$commentId];
            $dbRe = $this->getPdoHelper()->fetchOne($where, $whereParams);
            if($dbRe){
                $return = true;
                $aid = $dbRe['a_id'];
                $input = ['comment_id' => $commentId];
                $keyTemplate = CMS_COMMENT_CONTENT;
                $redisKey = $this->swtichRedisKey($input,$keyTemplate);
                $value = json_encode($dbRe);
                $this->redis->WYset($redisKey,$value,self::CMS_CACHE_EXPIRE);

                //删除热门评论列表缓存
                $this->delHotList($aid,$commentId);
                //删除最新评论列表缓存
                $this->delNewList($aid,$commentId);
            }else{
                $return = false;
            }
        }else{
            $return = false;
        }
        return $return;
    }
}
