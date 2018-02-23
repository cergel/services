<?php

namespace sdkService\model;
/**
 * @tutorial 想看相关功能
 * @author liulong
 *
 */
class NewWant extends BaseNew
{
    const CACHE_EXPIRE_MOVIE = 8640000; //被动缓存-缓存时间（影片）10天
    const CACHE_EXPIRE_USER = 172800;//被动缓存-缓存时间(用户)5天
    const LAST_UPDATE_TIME = 60; //两条评论更新时间间距，即最后操作时间间隔,若为false则表示不限制

    protected $dbConfig = 'dbApp';
    protected $tableName = 't_want';


    public function __construct()
    {
        $this->redis = $this->redis(\wepiao::getChannelId(), USER_COMMENT_CACHE);
    }

    //db中插入用户想看
    //【测通】
    public function insertUserWant($inputArrData)
    {
        $limitArr = ['uid', 'channelId', 'fromId', 'created', 'movieId', 'ucid'];
        $mustFields = ['ucid', 'movieId'];
        $arrData = $this->formatInputArray($inputArrData, $limitArr, $mustFields);
        $ucid = $arrData['ucid'];
        $movieId = $arrData['movieId'];
        $insertRe = $this->getPdoHelper()->Nadd($arrData, array_keys($arrData));
        if ($insertRe) {
            $time = $inputArrData['created'];
            //cache中用户想看的影片
            $this->addUserWantMovieCache($ucid, $time, $movieId);
        }
        return $insertRe;
    }

    //cache中用户想看的影片【测通】
    protected function addUserWantMovieCache($ucid, $time, $movieId)
    {
//        $input = ['ucid' => $ucid];
//        $keyTemplate = NEWCOMMENT_WANT_MOVIE_LIST;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//        $redisRe = $this->wantRedis->WYzAdd($redisKey, $time, $movieId);
//        return $redisRe;
        // todo  被动缓存

        $arrKey = ['ucid' => $ucid];
        $keyTemplate = NEWCOMMENT_WANT_MOVIE_LIST;
        $arrCountKey = ['ucid' => $ucid];
        $countKeyTemplate = NEWCOMMENT_WANT_MOVIE_LIST_COUNT;
        $score = $time;
        $value = $movieId;
        $expire = 0;
        return $this->addZsetCache($arrKey, $keyTemplate, $arrCountKey, $countKeyTemplate, $score, $value, $expire);
    }



    //db删除用户想看的影片
    //【测通】
    public function delUserWant($ucid, $movieId)
    {
        $fetchWhere = 'ucid=:ucid and movieId = :movieId';
        $fetchParams = [
            'ucid' => $ucid,
            'movieId' => $movieId
        ];
        $removeRe = $this->getPdoHelper()->remove($fetchWhere, $fetchParams);
        if ($removeRe) {
            $return = true;
            //cache删除用户一个想看的电影
            $this->remUserWantMovieCache($ucid, $movieId);
        } else {
            $return = false;
        }
        return $return;
    }

    //cache删除用户一个想看的电影
    protected function remUserWantMovieCache($ucid, $movieId)
    {
//        $input = ['ucid' => $ucid];
//        $keyTemplate = NEWCOMMENT_WANT_MOVIE_LIST;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//        $redisRe = $this->wantRedis->WYzRem($redisKey, $movieId);
//        return $redisRe;
//todo  被动缓存

        $arrKey = ['ucid' => $ucid];
        $keyTemplate = NEWCOMMENT_WANT_MOVIE_LIST;
        $arrCountKey = ['ucid' => $ucid];
        $countKeyTemplate = NEWCOMMENT_WANT_MOVIE_LIST_COUNT;
        $value = $movieId;
        return $this->delZsetCache($arrKey, $keyTemplate, $arrCountKey, $countKeyTemplate, $value);
    }

    //查询当前用户是否想看此电影
    public function queryUserIsWantMovie($ucid, $movieId)
    {

        //被动缓存
//        $input = ['ucid' => $ucid];
//        $keyTemplate = NEWCOMMENT_WANT_MOVIE_LIST;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//
//        if(!$this->redis->WYexists($redisKey)){
//            $arrRedisInfo = [
//                'redisKey' => ['ucid' => $ucid],
//                'redisKeyTemplate' => NEWCOMMENT_WANT_MOVIE_LIST,
//                'redisCountKey' => ['ucid' => $ucid],
//                'redisCountKeyTemplate' => NEWCOMMENT_WANT_MOVIE_LIST_COUNT,
//                'scoreField' => "created",
//                'valueField' => "movieId",
//                'start' => 0,
//                'end' => 10000,
//                'expire' => self::CACHE_EXPIRE_USER,
//            ];
//            $arrSqlInfo = [
//                'table' => 't_want',
//                'where' => "ucid = :ucid",
//                'params' => [":ucid" => $ucid],
//                'orderBy' => "created desc",
//                'step' => 2000
//            ];
//            $this->queryZsetCache($arrRedisInfo, $arrSqlInfo);//生成缓存
//        }
//        $redisRe = $this->redis->WYzRank($redisKey, $movieId);
//        return is_numeric($redisRe) ? true : false;

        //主动缓存
        $input = ['ucid' => $ucid];
        $keyTemplate = NEWCOMMENT_WANT_MOVIE_LIST;
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        $redisRe = $this->redis->WYzRank($redisKey, $movieId);
        return is_numeric($redisRe) ? true : false;
    }


    //查询当前影片的想看用户
    //【暂无地方使用】
    public function queryMovieWantList($movieId, $start = 0, $end = -1)
    {
//        $input = ['movieId' => $movieId];
//        $keyTemplate = NEWCOMMENT_WANT_USER_LIST;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//        $redisRe = $this->wantRedis->WYzRevRange($redisKey, $start, $end);
//        return $redisRe;

        $arrRedisInfo = [
            'redisKey' => ['movieId' => $movieId],
            'redisKeyTemplate' => NEWCOMMENT_WANT_USER_LIST,
            'redisCountKey' => ['movieId' => $movieId],
            'redisCountKeyTemplate' => NEWCOMMENT_WANT_USER_LIST_COUNT,
            'scoreField' => "created",
            'valueField' => "ucid",
            'start' => $start,
            'end' => $end,
            'expire' => self::CACHE_EXPIRE_MOVIE,
        ];

        $arrSqlInfo = [
            'table' => "t_want",
            'where' => "movieId = :movieId",
            'params' => [":movieId" => $movieId],
            'orderBy' => "created desc",
            'step' => 2000,
        ];
        return $this->queryZsetCache($arrRedisInfo, $arrSqlInfo);
    }

    //查询影片的想看人数
    //【目前没有地方使用】
    public function queryMovieWantCount($movieId)
    {
//        $input = ['movieId' => $movieId];
//        $keyTemplate = NEWCOMMENT_WANT_USER_LIST;
//        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
//        $redisRe = $this->wantRedis->WYzCard($redisKey);
//        return $redisRe;
        //todo 被动缓存

        $arrInputKey = ['movieId' => $movieId];
        $keyTemplate = NEWCOMMENT_WANT_USER_LIST_COUNT;
        $table = "t_want";
        $where = "movieId = :movieId";
        $params = [':movieId' => $movieId];
        $expire = self::CACHE_EXPIRE_MOVIE;
        return $this->queryZsetCacheCount($arrInputKey, $keyTemplate, $table, $where, $params, $expire);
    }

    //查询用户的想看列表【测通】
    //【只有观影轨迹脚本使用】
    public function queryUserWantList($ucid, $start = 0, $end = -1)
    {

        $input = ['ucid' => $ucid];
        $keyTemplate = NEWCOMMENT_WANT_MOVIE_LIST;
        $redisKey = $this->swtichRedisKey($input, $keyTemplate);
        $redisRe = $this->redis->WYzRevRange($redisKey, $start, $end);
        return $redisRe;

//        $arrRedisInfo = [
//            'redisKey' => ['ucid' => $ucid],
//            'redisKeyTemplate' => NEWCOMMENT_WANT_MOVIE_LIST,
//            'redisCountKey' => ['ucid' => $ucid],
//            'redisCountKeyTemplate' => NEWCOMMENT_WANT_MOVIE_LIST_COUNT,
//            'scoreField' => "created",
//            'valueField' => "movieId",
//            'start' => $start,
//            'end' => $end,
//            'expire' => self::CACHE_EXPIRE_USER,
//        ];
//        $arrSqlInfo = [
//            'table' => 't_want',
//            'where' => "ucid = :ucid",
//            'params' => [":ucid" => $ucid],
//            'orderBy' => "created desc",
//            'step' => 2000
//        ];
//        return $this->queryZsetCache($arrRedisInfo, $arrSqlInfo);
    }

    //获取我想看的影片的总数【测通】
    public function queryUserWantCount($ucid)
    {
        //取消被动
        $arrInputKey = ['ucid' => $ucid];
        $keyTemplate = NEWCOMMENT_WANT_MOVIE_LIST;
        $redisKey = $this->swtichRedisKey($arrInputKey, $keyTemplate);
        return $this->redis->WYzCard($redisKey);
//        $table = "t_want";
//        $where = "ucid = :ucid";
//        $params = [":ucid" => $ucid];
//        $expire = 0;
//        return $this->queryZsetCacheCount($arrInputKey, $keyTemplate, $table, $where, $params, $expire);
    }
}
