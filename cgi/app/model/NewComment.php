<?php

namespace sdkService\model;

/**
 * 新版的评论采用缓存去生成
 * @tutorial 评论相关功能
 * @author liulong
 *#
 */
class NewComment extends BaseNew
{
    const CACHE_EXPIRE = ''; //现在不用缓存时间

    const DB_CONFIG_KEY = 'dbApp';
    protected  $dbConfig = 'dbApp';
    const TABLE_NAME = 't_comment';
    protected $tableName = 't_comment';

    const COMMENT_INFO_CACHE_EXPIRE = 86400;//评论信息缓存时间,1天
    const USER_CACHE_EXPIRE = 172800;//用户维度的缓存时间，2天
    const MOVIE_CACHE_EXPIRE = 864000;//影片维度的缓存时间,10天

    const STEP_COMMENTED_USERS = 2000;//评论列表的步长值
    const STEP_COMMENTED_MOVIES = 1000;//用户评论过的影片的步长值

    public function __construct()
    {
        $this->redis = $this->redis(\wepiao::getChannelId(), USER_COMMENT_CACHE);
    }

    ###################评论相关#######################
    /**
     * 【测通】
     * @tutorial增加评论:只是新增
     * @param array $arrData
     * @return boolean
     * 修改：返回commentid
     */
    public function insertComment($inputArrData)
    {
        $arrFields = ['movieId', 'movieName', 'ucid', 'uid', 'channelId', 'fromId', 'content', 'baseFavorCount', 'created', 'updated', 'comment_order', 'checkstatus', 'status'];
        $mustFields = ['movieId', 'ucid', 'content'];
        $arrData = $this->formatInputArray($inputArrData, $arrFields, $mustFields);
        $time = time();
        $arrData['created'] = $time;
        $arrData['updated'] = $time;
        $commentId = $this->getPdoHelper()->Nadd($arrData, array_keys($arrData));
        if ($commentId) {
            $where = 'id = ' . $commentId;
            $dbFetchResult = $this->getPdoHelper()->fetchOne($where);
            $ucid = $dbFetchResult['ucid'];
            $commentInfo = json_encode($dbFetchResult);
            $movieId = $dbFetchResult['movieId'];
            $time = time();
            $score = '0';
            //影片的最新评论
            $this->addMovieNewListCache($movieId, $time, $commentId);
            //影片的热门（时间）评论
            $this->addMovieHotListCache($movieId, $score, $commentId);
            //评论内容
            $this->setMovieCommentCache($commentId, $commentInfo);
            //保存用户对某个影片的commentId
            $this->addUserCommentId($ucid, $movieId, $commentId);
            //保存用户维度：用户评论过的影片
            $this->addUserCommentMovie($ucid,$time,$movieId);
            $return = $commentId;
        } else {
            $return = false;
        }
        return $return;
    }

    /**
     * 增加、修改用户评论过的影片
     * @param $ucid
     * @param $score
     * @param $movieId
     * @return int
     */
    protected function addUserCommentMovie($ucid,$score,$movieId)
    {
        $input = ['ucid' => $ucid];
        $keyTemplate = NEWCOMMENT_COMMENTED_MOVIES;
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        $redisRe = $this->redis->WYzAdd($redisKey, $score, $movieId);
        return $redisRe;
    }

    /**
     * 删除：用户维度：用户评论过的影片
     * @param $ucid
     * @param $movieId
     * @return int
     */
    protected function delUserCommentMovie($ucid,$movieId)
    {
        $input = ['ucid' => $ucid];
        $keyTemplate = NEWCOMMENT_COMMENTED_MOVIES;
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        $redisRe = $this->redis->WYzRem($redisKey, $movieId);
        return $redisRe;
    }

    //设置评论内容（string内容）。
    protected function setMovieCommentCache($commentId, $commentInfo)
    {
        $input = ['commentId' => $commentId];
        $keyTemplate = NEWCOMMENT_CONTENT;
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        $redisRe = $this->redis->WYset($redisKey, $commentInfo);
        $this->redis->WYexpire($redisKey,self::COMMENT_INFO_CACHE_EXPIRE);
        return $redisRe;
       }

    //内网调用方法，更新热门评论的score值
    public function setHotCommentOrder($movieId, $score, $commentId)
    {
        return $this->addMovieHotListCache($movieId, $score, $commentId);
    }

    //增加影片的最新评论列表（有序集合）
    protected function addMovieNewListCache($movieId, $time, $commentId)
    {
//        $input = ['movieId' => $movieId];
//        $keyTemplate = NEWCOMMENT_COMMENT_SORT_NEW;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//        $redisRe = $this->redis->WYzAdd($redisKey, $time, $commentId);
//        return $redisRe;

        $arrKey = ['movieId' => $movieId];
        $keyTemplate = NEWCOMMENT_COMMENT_SORT_NEW;
        $countKey = ['movieId' => $movieId];
        $countKeyTemplate = NEWCOMMENT_COMMENT_SORT_NEW_COUNT;
        $score = $time;
        $value = $commentId;
        $expire = self::MOVIE_CACHE_EXPIRE;
        return $this->addZsetCache($arrKey, $keyTemplate, $countKey, $countKeyTemplate, $score, $value, $expire);
    }

    // 增加影片的热门评论（有序集合）
    protected function addMovieHotListCache($movieId, $score, $commentId)
    {
//        $input = ['movieId' => $movieId];
//        $keyTemplate = NEWCOMMENT_COMMENT_SORT_HOT;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//        $redisRe = $this->redis->WYzAdd($redisKey, $score, $commentId);
//        return $redisRe;

        $arrKey = ['movieId' => $movieId];
        $keyTemplate = NEWCOMMENT_COMMENT_SORT_HOT;
        $countKey = ['movieId' => $movieId];
        $countKeyTemplate = NEWCOMMENT_COMMENT_SORT_HOT_COUNT;
        $value = $commentId;
        $expire = self::MOVIE_CACHE_EXPIRE;
        return $this->addZsetCache($arrKey, $keyTemplate, $countKey, $countKeyTemplate, $score, $value, $expire);
    }


    //添加用户对于某影片的commentId
    protected function addUserCommentId($ucid, $movieId, $commentId)
    {
        $input = ['ucid' => $ucid];
        $hashKey = $movieId;
        $keyTemplate = NEWCOMMENT_COMMENTID_USER;
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        $redisRe = $this->redis->WYhSet($redisKey, $hashKey, $commentId);
        //$this->redis->WYexpire($redisKey,self::USER_CACHE_EXPIRE);
        return $redisRe;
    }

    //去掉用户对于某影片的commentId
    protected function delUserCommentId($ucid, $movieId)
    {
        $input = ['ucid' => $ucid];
        $hashKey = $movieId;
        $keyTemplate = NEWCOMMENT_COMMENTID_USER;
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        $redisRe = $this->redis->WYhDel($redisKey, $hashKey);
        return $redisRe;
    }

    /**
     * 【测通】
     * @tutorial修改评论
     * @param array $arrData
     * @return boolean|number
     * @todo 增加缓存 222
     */
    public function updateComment($id, $inputArrData)
    {
        $arrFields = ['movieId', 'movieName', 'ucid', 'uid', 'channelId', 'fromId', 'content', 'created', 'updated', 'comment_order', 'checkstatus', 'status'];
        $mustFields = ['movieId', 'ucid', 'content'];
        $arrData = $this->formatInputArray($inputArrData, $arrFields, $mustFields);
        $fields = array_keys($arrData);
        $where = 'id = :id';
        $params = $arrData;
        $params[':id'] = $id;
        $dbRe = $this->getPdoHelper()->update($fields, $params, $where);
        if ($dbRe) {
            $return = true;
            $fetchWhere = 'id = :id';
            $fetchParams[':id'] = $id;
            $fetchRe = $this->getPdoHelper()->fetchOne($fetchWhere, $fetchParams);
            if ($fetchRe) {
                $this->setMovieCommentCache($id, json_encode($fetchRe));
            }
        } else {
            $return = false;
        }
        return $return;
    }

    //db中删除评论
    public function deleteComment($commentId)
    {
        $fetchWhere = 'id = :id';
        $fetchParams[':id'] = $commentId;
        $fetchRe = $this->getPdoHelper()->fetchOne($fetchWhere, $fetchParams);
        if ($fetchRe) {
            $removeRe = $this->getPdoHelper()->remove($fetchWhere, $fetchParams);
            if ($removeRe) {
                $return = true;
                $commentId = $fetchRe['id'];
                $movieId = $fetchRe['movieId'];
                $ucid = $fetchRe['ucid'];
                //删除评论内容
                $this->delMovieCommentCache($commentId);
                //删除影片的最新评论
                $this->delMovieNewListCache($movieId, $commentId);
                //删除影片的热门评论
                $this->delMovieHotListCache($movieId, $commentId);
                //去掉用户对某个影片的评论
                $this->delUserCommentId($movieId, $ucid);
                //删除好评
                $this->remGoodCommentList($movieId,$commentId);
                //删除差评
                $this->remBadCommentList($movieId,$commentId);
                //删除已购票
                $this->remBuyCommentList($movieId,$commentId);
                //删除用户评论过的影片
                $this->delUserCommentMovie($ucid,$movieId);
            } else {
                $return = false;
            }
        } else {
            $return = false;
        }
        return $return;
    }

    //删除评论内容
    protected function delMovieCommentCache($commentId)
    {
        $input = ['commentId' => $commentId];
        $keyTemplate = NEWCOMMENT_CONTENT;
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        $redisRe = $this->redis->WYdelete($redisKey);
        return $redisRe;
    }

    //删除影片的最新评论
    protected function delMovieNewListCache($movieId, $commentId)
    {
//        $input = ['movieId' => $movieId];
//        $keyTemplate = NEWCOMMENT_COMMENT_SORT_NEW;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//        $redisRe = $this->redis->WYzRem($redisKey, $commentId);
//        return $redisRe;

        $arrKey = ['movieId' => $movieId];
        $keyTemplate = NEWCOMMENT_COMMENT_SORT_NEW;
        $countKey = ['movieId' => $movieId];
        $countKeyTemplate = NEWCOMMENT_COMMENT_SORT_NEW_COUNT;
        $value = $commentId;
        return $this->delZsetCache($arrKey, $keyTemplate, $countKey, $countKeyTemplate, $value);
    }

    //删除影片的热门评论。
    protected function delMovieHotListCache($movieId, $commentId)
    {
//        $input = ['movieId' => $movieId];
//        $keyTemplate = NEWCOMMENT_COMMENT_SORT_HOT;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//        $redisRe = $this->redis->WYzRem($redisKey, $commentId);
//        return $redisRe;

        $arrKey = ['movieId' => $movieId];
        $keyTemplate = NEWCOMMENT_COMMENT_SORT_HOT;
        $countKey = ['movieId' => $movieId];
        $countKeyTemplate = NEWCOMMENT_COMMENT_SORT_HOT_COUNT;
        $value = $commentId;
        return $this->delZsetCache($arrKey, $keyTemplate, $countKey, $countKeyTemplate, $value);
    }


    //管理员屏蔽
    public function managerScreen($commentId)
    {
        $fetchWhere = 'id = :id';
        $fetchParams[':id'] = $commentId;
        $fetchRe = $this->getPdoHelper()->fetchOne($fetchWhere, $fetchParams);
        if ($fetchRe) {
            $return = true;
            $ucid = $fetchRe['ucid'];
            $movieId = $fetchRe['movieId'];
            //删除影片的最新评论
            $this->delMovieNewListCache($movieId, $commentId);
            //影片的热门（时间）评论
            $this->delMovieHotListCache($movieId, $commentId);
            //删除好评列表
            $this->remGoodCommentList($movieId,$commentId);
            //删除差评列表
            $this->remBadCommentList($movieId,$commentId);
            //删除已购票列表
            $this->remBuyCommentList($movieId,$commentId);
            //清理评论缓存
            $this->delMovieCommentCache($commentId);
        } else {
            $return = false;
        }
        return $return;
    }

    //管理员修改评论内容【测通】
    public function managerUpdateComment($commentId)
    {
        $fetchWhere = 'id = :id';
        $fetchParams[':id'] = $commentId;
        $fetchRe = $this->getPdoHelper()->fetchOne($fetchWhere, $fetchParams);
        if ($fetchRe) {
            $return = $this->setMovieCommentCache($commentId, json_encode($fetchRe));
        } else {
            $return = false;
        }
        return $return;
    }


    //查询指定id的评论内容
    public function queryComment($commentId)
    {
        $input = ['commentId' => $commentId];
        $keyTemplate = NEWCOMMENT_CONTENT;
        $where = 'id = :id';
        $whereParams = [':id'=>$commentId];
        return $this->queryStringCache($input,$keyTemplate,self::DB_CONFIG_KEY,self::TABLE_NAME,self::COMMENT_INFO_CACHE_EXPIRE,$where,$whereParams);
    }

    //查询指定影片的热门评论内容
    public function queryMovieHotComment($movieId, $start = 0, $end = -1)
    {

        $arrRedisInfo = [
            'redisKey'=>['movieId' => $movieId],
            'redisKeyTemplate'=>NEWCOMMENT_COMMENT_SORT_HOT,
            'redisCountKey'=>['movieId' => $movieId],
            'redisCountKeyTemplate'=>NEWCOMMENT_COMMENT_SORT_HOT_COUNT,
            'scoreField'=>"comment_order",
            'valueField'=>"id",
            'start'=>$start,
            'end'=>$end,
            'expire'=>self::MOVIE_CACHE_EXPIRE,
        ];
        $arrSqlInfo = [
            'table'=>"t_comment",
            'where'=>"movieId = :movieId and `status`=1 ",
            'params'=>[':movieId'=>$movieId],
            'orderBy'=>"comment_order desc",
            'step'=> self::STEP_COMMENTED_USERS,
        ];
        return $this->queryZsetCache($arrRedisInfo, $arrSqlInfo);
    }

    //返回指定影片的最新评论总数
    public function queryMovieHotCommentCount($movieId)
    {
        $arrInputKey = ['movieId' => $movieId];
        $keyTemplate = NEWCOMMENT_COMMENT_SORT_NEW_COUNT;
        $table = "t_comment";
        $where = "movieId = :movieId and `status`=1";
        $params = [":movieId"=>$movieId];
        $expire = self::MOVIE_CACHE_EXPIRE;
        return $this->queryZsetCacheCount($arrInputKey, $keyTemplate, $table, $where, $params, $expire);
    }

    //查询指定影片的最新评论内容
    public function queryMovieNewComment($movieId, $start = 0, $end = -1)
    {

        $arrRedisInfo = [
            'redisKey'=>['movieId' => $movieId],
            'redisKeyTemplate'=>NEWCOMMENT_COMMENT_SORT_NEW,
            'redisCountKey'=>['movieId' => $movieId],
            'redisCountKeyTemplate'=>NEWCOMMENT_COMMENT_SORT_NEW_COUNT,
            'scoreField'=>"created",
            'valueField'=>"id",
            'start'=>$start,
            'end'=>$end,
            'expire'=>self::MOVIE_CACHE_EXPIRE,
        ];
        $arrSqlInfo=[
            'table'=>"t_comment",
            'where'=>" movieId = :movieId and `status`=1 ",
            'params'=>[":movieId"=>$movieId],
            'orderBy'=>"created desc ",
            'step'=> self::STEP_COMMENTED_USERS,
        ];
        return $this->queryZsetCache($arrRedisInfo, $arrSqlInfo);
    }

    //返回指定影片的热门评论总数
    public function queryMovieNewCommentCount($movieId)
    {
//        $input = ['movieId' => $movieId];
//        $keyTemplate = NEWCOMMENT_COMMENT_SORT_NEW;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//        return $this->redis->WYzCard($redisKey);

        $arrInputKey = ['movieId' => $movieId];
        $keyTemplate = NEWCOMMENT_COMMENT_SORT_NEW_COUNT;
        $table = "t_comment";
        $where = "movieId = :movieId and `status`=1 ";
        $params = [":movieId"=>$movieId];
        $expire = self::MOVIE_CACHE_EXPIRE;
        return $this->queryZsetCacheCount($arrInputKey, $keyTemplate, $table, $where, $params, $expire);
    }

    //查询指定影片的好评评论内容
    public function queryMovieGoodComment($movieId, $start = 0, $end = -1)
    {
//        $input = ['movieId' => $movieId];
//        $keyTemplate = NEWCOMMENT_COMMENT_SORT_GOOD;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//        return $this->redis->WYzRevRange($redisKey, $start, $end);

        $arrRedisInfo = [
            'redisKey'=>['movieId' => $movieId],
            'redisKeyTemplate'=>NEWCOMMENT_COMMENT_SORT_GOOD,
            'redisCountKey'=>['movieId' => $movieId],
            'redisCountKeyTemplate'=>NEWCOMMENT_COMMENT_SORT_GOOD_COUNT,
            'selectScoreField'=>"(t_comment.baseFavorCount+t_comment.favorCount) as totalFavor",
            'selectValueField'=>'t_comment.id',
            'scoreField'=>"totalFavor",
            'valueField'=>"id",
            'start'=>$start,
            'end'=>$end,
            'expire'=>self::MOVIE_CACHE_EXPIRE,
        ];
        $arrSqlInfo=[
            'table'=>"	t_score LEFT JOIN t_comment ON t_score.ucid = t_comment.ucid AND t_score.movieId = t_comment.movieId ",
            'where'=>"t_comment.`status`=1  and t_comment.movieId = :movieId and t_score.score in (100,80)",
            'params'=>[":movieId"=>$movieId],
            'orderBy'=>"totalFavor desc",
            'step'=> self::STEP_COMMENTED_USERS,
        ];
        return $this->queryZsetCache($arrRedisInfo, $arrSqlInfo);
    }

    //返回指定影片的好评评论总数
    public function queryMovieGoodCommentCount($movieId)
    {
//        $input = ['movieId' => $movieId];
//        $keyTemplate = NEWCOMMENT_COMMENT_SORT_GOOD;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//        return $this->redis->WYzCard($redisKey);

        $arrInputKey = ['movieId' => $movieId];
        $keyTemplate = NEWCOMMENT_COMMENT_SORT_GOOD_COUNT;
        $table = "	t_score LEFT JOIN t_comment ON t_score.ucid = t_comment.ucid AND t_score.movieId = t_comment.movieId";
        $where = "t_comment.`status`=1  and t_comment.movieId = :movieId and t_score.score in (100,80)";
        $params = [":movieId" => $movieId];
        $expire = self::MOVIE_CACHE_EXPIRE;
        return $this->queryZsetCacheCount($arrInputKey, $keyTemplate, $table, $where, $params, $expire);
    }


    //查询指定影片的差评评论内容
    public function queryMovieBadComment($movieId, $start = 0, $end = -1)
    {
//        $input = ['movieId' => $movieId];
//        $keyTemplate = NEWCOMMENT_COMMENT_SORT_BAD;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//        return $this->redis->WYzRevRange($redisKey, $start, $end);

        $arrRedisInfo = [
            'redisKey'=>['movieId' => $movieId],
            'redisKeyTemplate'=>NEWCOMMENT_COMMENT_SORT_BAD,
            'redisCountKey'=>['movieId' => $movieId],
            'redisCountKeyTemplate'=>NEWCOMMENT_COMMENT_SORT_BAD_COUNT,
            'selectScoreField'=>"(t_comment.baseFavorCount+t_comment.favorCount) as totalFavor",
            'selectValueField'=>"t_comment.id",
            'scoreField'=>"totalFavor",
            'valueField'=>"id",
            'start'=>$start,
            'end'=>$end,
            'expire'=>self::MOVIE_CACHE_EXPIRE,
        ];
        $arrSqlInfo=[
            'table'=>"	t_score LEFT JOIN t_comment ON t_score.ucid = t_comment.ucid AND t_score.movieId = t_comment.movieId",
            'where'=>"t_comment.`status`=1  and t_comment.movieId = :movieId and t_score.score in (0,20)",
            'params'=>[":movieId"=>$movieId],
            'orderBy'=>"totalFavor desc",
            'step'=> self::STEP_COMMENTED_USERS,
        ];
        return $this->queryZsetCache($arrRedisInfo, $arrSqlInfo);
    }

    //返回指定影片的差评评论总数
    public function queryMovieBadCommentCount($movieId)
    {
//        $input = ['movieId' => $movieId];
//        $keyTemplate = NEWCOMMENT_COMMENT_SORT_BAD;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//        return $this->redis->WYzCard($redisKey);

        $arrInputKey = ['movieId' => $movieId];
        $keyTemplate = NEWCOMMENT_COMMENT_SORT_BAD_COUNT;
        $table = "	t_score LEFT JOIN t_comment ON t_score.ucid = t_comment.ucid AND t_score.movieId = t_comment.movieId";
        $where = "t_comment.`status`=1  and t_comment.movieId = :movieId and t_score.score in (0,20)";
        $params = [":movieId" => $movieId];
        $expire = self::MOVIE_CACHE_EXPIRE;
        return $this->queryZsetCacheCount($arrInputKey, $keyTemplate, $table, $where, $params, $expire);
    }

    //查询指定影片的已购票评论内容
    public function queryMovieBuyComment($movieId, $start = 0, $end = -1)
    {
//        $input = ['movieId' => $movieId];
//        $keyTemplate = NEWCOMMENT_COMMENT_SORT_BUY;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//        return $this->redis->WYzRevRange($redisKey, $start, $end);

        $arrRedisInfo = [
            'redisKey'=>['movieId' => $movieId],
            'redisKeyTemplate'=>NEWCOMMENT_COMMENT_SORT_BUY,
            'redisCountKey'=>['movieId' => $movieId],
            'redisCountKeyTemplate'=>NEWCOMMENT_COMMENT_SORT_BUY_COUNT,
            'scoreField'=>"created",
            'valueField'=>"id",
            'start'=>$start,
            'end'=>$end,
            'expire'=>self::MOVIE_CACHE_EXPIRE,
        ];
        $arrSqlInfo=[
            'table'=>"t_comment",
            'where'=>"movieId = :movieId and `status`=1 and seen_movie = 1",
            'params'=>[":movieId"=>$movieId],
            'orderBy'=>"created desc",
            'step'=> self::STEP_COMMENTED_USERS,
        ];
        return $this->queryZsetCache($arrRedisInfo, $arrSqlInfo);
    }

    //返回指定影片的已购票评论总数
    public function queryMovieBuyCommentCount($movieId)
    {
//        $input = ['movieId' => $movieId];
//        $keyTemplate = NEWCOMMENT_COMMENT_SORT_BUY;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//        return $this->redis->WYzCard($redisKey);

        $arrInputKey = ['movieId' => $movieId];
        $keyTemplate = NEWCOMMENT_COMMENT_SORT_BUY_COUNT;
        $table = "t_comment";
        $where = "movieId = :movieId and `status`=1 and seen_movie = 1 ";
        $params = [":movieId" => $movieId];
        $expire = self::MOVIE_CACHE_EXPIRE;
        return $this->queryZsetCacheCount($arrInputKey, $keyTemplate, $table, $where, $params, $expire);
    }
    //查询指定影片的推荐评论列表
    public function queryMovieRecommendComment($movieId, $start = 0, $end = -1)
    {
        $input = ['movieId' => $movieId];
        $keyTemplate = NEWCOMMENT_COMMENT_SORT_RECOMMEND_NEW;
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        $arrData = $this->redis->WYget($redisKey);
        if(!empty($arrData)){
            $arrData = json_decode($arrData,true);
        }else $arrData = [];
        return $arrData;
//        return $this->redis->WYzRevRange($redisKey, $start, $end);
    }
    /**
     * 临时废弃
     * 查询指定影片的推荐评论总数
    */
    public function queryMovieRecommendCommentCount($movieId)
    {
        $input = ['movieId' => $movieId];
//        $keyTemplate = NEWCOMMENT_COMMENT_SORT_RECOMMEND;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//        return $this->redis->WYzCard($redisKey);
        $keyTemplate = NEWCOMMENT_COMMENT_SORT_RECOMMEND_NEW;
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        $arrData = $this->redis->WYget($redisKey);
        if(!empty($arrData)){
            $arrData = json_decode($arrData,true);
        }else $arrData = [];
        return count($arrData);
    }

    //获取用户的全部评论过的影片id
    public function queryUserAllCommentMovieId($ucid)
    {
        $input = ['ucid' => $ucid];
        $keyTemplate = NEWCOMMENT_COMMENTED_MOVIES;
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        $redisRe = $this->redis->WYzRevRange($redisKey, 0, -1);
        return $redisRe;

//        $arrRedisInfo = [
//            'redisKey'=>['ucid' => $ucid],
//            'redisKeyTemplate'=>NEWCOMMENT_COMMENTED_MOVIES,
//            'redisCountKey'=>['ucid' => $ucid],
//            'redisCountKeyTemplate'=>NEWCOMMENT_COMMENTED_MOVIES_COUNT,
//            'scoreField'=>"created",
//            'valueField'=>"id",
//            'start'=>0,
//            'end'=>3000,
//            'expire'=>self::USER_CACHE_EXPIRE,
//        ];
//        $arrSqlInfo=[
//            'table'=>"t_comment",
//            'where'=>"ucid = :ucid and `status`=1 ",
//            'params'=>[":ucid"=>$ucid],
//            'orderBy'=>"created desc",
//            'step'=> self::STEP_COMMENTED_MOVIES,
//        ];
//        return $this->queryZsetCache($arrRedisInfo, $arrSqlInfo);
    }


    //查询用户最近一次评论的时间
    public function queryUserLastCommentTime($ucid)
    {
        $input = ['ucid' => $ucid];
        $keyTemplate = NEWCOMMENT_COMMENTED_MOVIES;
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        $redisRe = $this->redis->WYzRevRange($redisKey, 0, 0, true);//加上withscore字段
        return $redisRe;

//        $arrRedisInfo = [
//            'redisKey'=>['ucid' => $ucid],
//            'redisKeyTemplate'=>NEWCOMMENT_COMMENTED_MOVIES,
//            'redisCountKey'=>['ucid' => $ucid],
//            'redisCountKeyTemplate'=>NEWCOMMENT_COMMENTED_MOVIES_COUNT,
//            'scoreField'=>"created",
//            'valueField'=>"id",
//            'start'=>0,
//            'end'=>0,
//            'expire'=>self::MOVIE_CACHE_EXPIRE,
//        ];
//        $arrSqlInfo=[
//            'table'=>"t_comment",
//            'where'=>"ucid = :ucid and `status`=1 ",
//            'params'=>[":ucid"=>$ucid],
//            'orderBy'=>"created desc",
//            'step'=> self::STEP_COMMENTED_MOVIES,
//        ];
//        $redisRe = $this->queryZsetCache($arrRedisInfo, $arrSqlInfo, true, true);
//        $return = false;
//        if ($redisRe) {
//            foreach ($redisRe as $v) {
//                $return = $v;
//            }
//        } else {
//            $return = false;
//        }
//        return $return;
    }

    //根据ucid和movieId获取用户评论的内容，没有为false
    public function queryCommentByUcidMovieId($ucid, $movieId)
    {

        $inpuKey1 = ['ucid' => $ucid];
        $keyTemplate1 = NEWCOMMENT_COMMENTID_USER;
        $hashKey1 = $movieId;
        /*
        $dbConfigKey1 = "dbApp";
        $tableName1 = "t_comment";
        $cacheTime1 = self::USER_CACHE_EXPIRE;//1天缓存时间
        $where1 = " ucid = :ucid and movieId = :movieId and `status`=1 ";
        $whereParams1 = [":ucid"=>$ucid,":movieId"=>$movieId];
        $field = 'id';
        $redisHashRe = $this->queryHashCache($inpuKey1, $hashKey1, $keyTemplate1, $dbConfigKey1, $tableName1, $cacheTime1, $where1, $whereParams1,$field);
        */
        $redisKey1 = $this->swtichRedisKey($inpuKey1,$keyTemplate1);
        $redisHashRe = $this->redis->WYhGet($redisKey1,$hashKey1);
        if($redisHashRe){
            $commentId = $redisHashRe;
            $inpuKey2 = ['commentId' => $commentId];
            $keyTemplate2 = NEWCOMMENT_CONTENT;
            $dbConfigKey2 = "dbApp";
            $tableName2 = "t_comment";
            $cacheTime2 = self::USER_CACHE_EXPIRE;
            $where2 = "id = :id";
            $whereParams2 =[":id"=>$commentId];
            $return = $this->queryStringCache($inpuKey2, $keyTemplate2, $dbConfigKey2, $tableName2, $cacheTime2, $where2, $whereParams2);
        }else{
            $return = false;
        }
        return $return;
    }

    //将评论id添加进影片的好评列表
    public function addGoodCommentList($movieId,$time,$commentId){
//        $keyTemplate = NEWCOMMENT_COMMENT_SORT_GOOD;
//        $input = ['movieId'=>$movieId];
//        $realKey = $this->swtichRedisKey($input,$keyTemplate);
//        return $this->redis->WYzAdd($realKey,$time,$commentId);

        $arrKey = ['movieId'=>$movieId];
        $keyTemplate = NEWCOMMENT_COMMENT_SORT_GOOD;
        $countKey = ['movieId'=>$movieId];
        $countKeyTemplate = NEWCOMMENT_COMMENT_SORT_GOOD_COUNT;
        $score = $time;
        $value = $commentId;
        $expire = self::MOVIE_CACHE_EXPIRE;
        return $this->addZsetCache($arrKey, $keyTemplate, $countKey, $countKeyTemplate, $score, $value, $expire);
    }

    //将评论id从影片的好评列表中移除
    public function remGoodCommentList($movieId,$commentId){
//        $keyTemplate = NEWCOMMENT_COMMENT_SORT_GOOD;
//        $input = ['movieId'=>$movieId];
//        $realKey = $this->swtichRedisKey($input,$keyTemplate);
//        return $this->redis->WYzRem($realKey,$commentId);

        $arrKey = ['movieId'=>$movieId];
        $keyTemplate = NEWCOMMENT_COMMENT_SORT_GOOD;
        $countKey = ['movieId'=>$movieId];
        $countKeyTemplate = NEWCOMMENT_COMMENT_SORT_GOOD_COUNT;
        $value = $commentId;
        return $this->delZsetCache($arrKey, $keyTemplate, $countKey, $countKeyTemplate, $value);
    }

    //将评论id添加进影片的差评列表
    public function addBadCommentList($movieId,$time,$commentId){
//        $keyTemplate = NEWCOMMENT_COMMENT_SORT_BAD;
//        $input = ['movieId'=>$movieId];
//        $realKey = $this->swtichRedisKey($input,$keyTemplate);
//        return $this->redis->WYzAdd($realKey,$time,$commentId);

        $arrKey = ['movieId'=>$movieId];
        $keyTemplate = NEWCOMMENT_COMMENT_SORT_BAD;
        $countKey = ['movieId'=>$movieId];
        $countKeyTemplate = NEWCOMMENT_COMMENT_SORT_BAD_COUNT;
        $score = $time;
        $value = $commentId;
        $expire = self::MOVIE_CACHE_EXPIRE;
        return $this->addZsetCache($arrKey, $keyTemplate, $countKey, $countKeyTemplate, $score, $value, $expire);
    }

    //将评论id从影片的差评列表中移除
    public function remBadCommentList($movieId,$commentId){
//        $keyTemplate = NEWCOMMENT_COMMENT_SORT_BAD;
//        $input = ['movieId'=>$movieId];
//        $realKey = $this->swtichRedisKey($input,$keyTemplate);
//        return $this->redis->WYzRem($realKey,$commentId);

        $arrKey = ['movieId'=>$movieId];
        $keyTemplate = NEWCOMMENT_COMMENT_SORT_BAD;
        $countKey = ['movieId'=>$movieId];
        $countKeyTemplate = NEWCOMMENT_COMMENT_SORT_BAD_COUNT;
        $value = $commentId;
        return $this->delZsetCache($arrKey, $keyTemplate, $countKey, $countKeyTemplate, $value);
    }

    //将评论id从影片的已购票列表中移除
    public function remBuyCommentList($movieId,$commentId){
//        $keyTemplate = NEWCOMMENT_COMMENT_SORT_BUY;
//        $input = ['movieId'=>$movieId];
//        $realKey = $this->swtichRedisKey($input,$keyTemplate);
//        return $this->redis->WYzRem($realKey,$commentId);

        $arrKey = ['movieId'=>$movieId];
        $keyTemplate = NEWCOMMENT_COMMENT_SORT_BUY;
        $countKey = ['movieId'=>$movieId];
        $countKeyTemplate = NEWCOMMENT_COMMENT_SORT_BUY_COUNT;
        $value = $commentId;
        return $this->delZsetCache($arrKey, $keyTemplate, $countKey, $countKeyTemplate, $value);
    }

    /**
     * @param $ucid
     * @return bool|mixed
     * @todo  去掉
     */

    public function getCommentStarByUcid($ucid){
        $inpuKey = ['ucid'=>$ucid];
        $redisKey = $this->swtichRedisKey($inpuKey, NEWCOMMENT_COMMENT_STAR);
        $redisRe = $this->redis->WYget($redisKey);
        return empty($redisRe)?false:json_decode($redisRe,true);
    }
}
