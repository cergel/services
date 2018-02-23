<?php

namespace sdkService\model;


/**
 * @tutorial 看过相关功能
 * @author liulong
 *
 */
class NewSeen extends BaseNew
{
    const CACHE_EXPIRE_MOVIE = 864000; //被动缓存缓存时间--影片,10天
    const CACHE_EXPIRE_USER = 172800;//被动缓存缓存时间--用户,5天
    const LAST_UPDATE_TIME = 60; //两条评论更新时间间距，即最后操作时间间隔,若为false则表示不限制

    protected $dbConfig = 'dbApp';
    protected $tableName = 't_seen';


    public function __construct()
    {
        $this->redis = $this->redis(\wepiao::getChannelId(), USER_COMMENT_CACHE);
    }



    //db中插入用户看过
    //【测通】
    public function insertUserSeen($inputArrData)
    {
        $limitArr = ['ucid', 'movieId', 'uid', 'channelId', 'fromId', 'created'];
        $mustFields = ['ucid', 'movieId'];
        $arrData = $this->formatInputArray($inputArrData, $limitArr, $mustFields);
        $ucid = $arrData['ucid'];
        $movieId = $arrData['movieId'];
        $insertRe = $this->getPdoHelper()->Nadd($arrData, array_keys($arrData));
        if ($insertRe) {
            $time = $arrData['created'];
            //cache中用户看过的影片
            $this->addUserSeenMovieCache($ucid, $time, $movieId);
            $return = true;
        } else {
            $return = false;
        }
        return $return;
    }

    //cache中用户看过的影片【测通】
    protected function addUserSeenMovieCache($ucid, $time, $movieId)
    {
        $arrKey = ['ucid' => $ucid];
        $keyTemplate = NEWCOMMENT_SEEN_MOVIE_LIST;
        $arrCountKey = ['ucid' => $ucid];
        $countKeyTemplate = NEWCOMMENT_SEEN_MOVIE_LIST_COUNT;
        $score = $time;
        $value = $movieId;
        $expire = 0;//不过期
        return $this->addZsetCache($arrKey,$keyTemplate, $arrCountKey,$countKeyTemplate, $score, $value,$expire);
    }


    //db删除用户看过的影片
    //【测通】
    public function delUserSeen($ucid, $movieId)
    {
        $fetchWhere = 'ucid=:ucid and movieId = :movieId';
        $fetchParams = [
            'ucid' => $ucid,
            'movieId' => $movieId
        ];
        $removeRe = $this->getPdoHelper()->remove($fetchWhere, $fetchParams);
        if ($removeRe) {
            $return = true;
            //cache删除用户一个看过的电影
            $this->remUserSeenMovieCache($ucid, $movieId);
        } else {
            $return = false;
        }
        return $return;
    }

    //cache删除用户一个看过的电影
    protected function remUserSeenMovieCache($ucid, $movieId)
    {
        $arrKey = ['ucid' => $ucid];
        $keyTemplate = NEWCOMMENT_SEEN_MOVIE_LIST;
        $arrCountKey = ['ucid' => $ucid];
        $countKeyTemplate = NEWCOMMENT_SEEN_MOVIE_LIST_COUNT;
        $value = $movieId;
        return $this->delZsetCache($arrKey, $keyTemplate,$arrCountKey, $countKeyTemplate,$value);
    }


    //查询当前用户是否看过此电影
    public function queryUserIsSeenMovie($ucid, $movieId)
    {

        //被动缓存
//        $input = ['ucid' => $ucid];
//        $keyTemplate = NEWCOMMENT_SEEN_MOVIE_LIST;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//        if(!$this->redis->WYexists($redisKey)){
//            $arrRedisInfo = [
//                'redisKey'=>['ucid' => $ucid],
//                'redisKeyTemplate'=>NEWCOMMENT_SEEN_MOVIE_LIST,
//                'redisCountKey'=>['ucid' => $ucid],
//                'redisCountKeyTemplate'=>NEWCOMMENT_SEEN_MOVIE_LIST_COUNT,
//                'scoreField'=>"created",
//                'valueField'=>"movieId",
//                'start'=>0,
//                'end'=>10000,
//                'expire'=>self::CACHE_EXPIRE_USER,
//            ];
//            $arrSqlInfo = [
//                'table'=>"t_seen",
//                'where'=>"ucid = :ucid",
//                'params'=>[":ucid"=>$ucid],
//                'orderBy'=> "created desc",
//                'step'=> 2000,
//            ];
//            $this->queryZsetCache($arrRedisInfo, $arrSqlInfo);
//        }
//        $redisRe = $this->redis->WYzRank($redisKey, $movieId);
//        return is_numeric($redisRe) ? true : false;

        //主动缓存
        $input = ['ucid' => $ucid];
        $keyTemplate = NEWCOMMENT_SEEN_MOVIE_LIST;
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        $redisRe = $this->redis->WYzRank($redisKey, $movieId);
        return is_numeric($redisRe) ? true : false;
    }


    //查询当前影片的看过用户
    public function queryMovieSeenList($movieId, $start = 0, $end = -1)
    {
        $arrRedisInfo = [
            'redisKey'=>['movieId' => $movieId],
            'redisKeyTemplate'=>NEWCOMMENT_SEEN_USER_LIST,
            'redisCountKey'=>['movieId' => $movieId],
            'redisCountKeyTemplate'=>NEWCOMMENT_SEEN_USER_LIST_COUNT,
            'scoreField'=>"created",
            'valueField'=>"ucid",
            'start'=>$start,
            'end'=>$end,
            'expire'=>self::CACHE_EXPIRE_MOVIE,
        ];
        $arrSqlInfo = [
            'table'=>"t_seen",
            'where'=>"movieId = :movieId",
            'params'=>[':movieId'=>$movieId],
            'orderBy'=>"created desc",
            'step'=> 2000,
        ];
        return $this->queryZsetCache($arrRedisInfo, $arrSqlInfo);
    }

    //查询用户的看过列表【测通】
    public function queryUserSeenList($ucid,$start = 0,$end = -1,$withScores=false){

        $input = ['ucid' => $ucid];
        $keyTemplate = NEWCOMMENT_SEEN_MOVIE_LIST;
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        $redisRe = $this->redis->WYzRevRange($redisKey, $start, $end,$withScores);
        return $redisRe;
//        $arrRedisInfo = [
//            'redisKey'=>['ucid' => $ucid],
//            'redisKeyTemplate'=>NEWCOMMENT_SEEN_MOVIE_LIST,
//            'redisCountKey'=>['ucid' => $ucid],
//            'redisCountKeyTemplate'=>NEWCOMMENT_SEEN_MOVIE_LIST_COUNT,
//            'scoreField'=>"created",
//            'valueField'=>"movieId",
//            'start'=>$start,
//            'end'=>$end,
//            'expire'=>self::CACHE_EXPIRE_USER,
//        ];
//        $arrSqlInfo = [
//            'table'=>"t_seen",
//            'where'=>"ucid = :ucid",
//            'params'=>[":ucid"=>$ucid],
//            'orderBy'=> "created desc",
//            'step'=> 2000,
//        ];
//        return $this->queryZsetCache($arrRedisInfo, $arrSqlInfo,true,$withScores);
    }

    //查询当前影片的看过用户的总数
    public function queryMovieSeenCount($movieId)
    {

        $arrInputKey = ['movieId' => $movieId];
        $keyTemplate = NEWCOMMENT_SEEN_USER_LIST_COUNT;
        $table = 't_seen';
        $where = "movieId = :movieId";
        $params = [":movieId"=>$movieId];
        $expire = self::CACHE_EXPIRE_MOVIE;
        return $this->queryZsetCacheCount($arrInputKey,$keyTemplate,$table,$where,$params,$expire);
    }

    //查询用户看过的影片总数【测通】
    public function queryUserSeenCount($ucid)
    {
        //取消被动
        $input = ['ucid' => $ucid];
        $keyTemplate = NEWCOMMENT_SEEN_MOVIE_LIST;
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        return $this->redis->WYzCard($redisKey);
//        $arrInputKey = ['ucid' => $ucid];
//        $keyTemplate = NEWCOMMENT_SEEN_MOVIE_LIST_COUNT;
//        $table = "t_seen";
//        $where = "ucid = :ucid";
//        $params = [":ucid"=>$ucid];
//        $expire = self::CACHE_EXPIRE_USER;
//        return $this->queryZsetCacheCount($arrInputKey,$keyTemplate,$table,$where,$params,$expire);
    }
}
