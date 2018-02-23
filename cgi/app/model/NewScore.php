<?php

namespace sdkService\model;
/**
 * @tutorial 评分
 * @author liulong
 *
 */
class NewScore extends BaseNew
{
    const SCORE_EXPIRE_TIME = 172800; //评分信息的被动缓存时间
    const SCORE_NUMBER_CACHE_EXPIRE = 172800;//评分人数hash的缓存时间
    const COMMENT_TABLE_NAME = 't_score'; //表名
    const DB_CONFIG_KEY = 'dbApp';
    protected $dbConfig = 'dbApp';
    const TABLE_NAME = 't_score';
    protected $tableName = 't_score';
    
    public function __construct()
    {
        //$this->pdo(self::DB_CONFIG_KEY, self::TABLE_NAME);
        $this->redis = $this->redis(\wepiao::getChannelId(), USER_COMMENT_CACHE);
    }

    //
    /**
     * 【测通】
     * db用户对一条电影进行评分
     * @param $ucid
     * @param $movieId
     * @param $inputArrData 包含['score','type','channelId','fromId','created','updated']中任意一个或多个字段
     * @return bool
     */
    public function insertScore($inputArrData){
        $limitArr=['ucid','movieId','score','type','uid','channelId','fromId','created','updated'];
        $mustFields = ['ucid','movieId'];
        $arrData = $this->formatInputArray($inputArrData,$limitArr,$mustFields);
        $insertRe = $this->getPdoHelper()->Nadd($arrData, array_keys($arrData));
        if($insertRe){
            $ucid = $inputArrData['ucid'];
            $movieId = $inputArrData['movieId'];
            $return = true;
            $arrScoreInfo = [
                'score'=>$inputArrData['score'],
            ];
            if(!empty($inputArrData['type'])){
                $arrScoreInfo['type'] = $inputArrData['type'];
            }
            $scoreInfo = json_encode($arrScoreInfo);
            //用户对电影进行评分后，增加缓存
            $this->addMovieScoreCache($ucid,$movieId,$scoreInfo);
            //影片评分人数+1 (新)
            $this->insertScoreNumberCache($movieId,$inputArrData['score']);
        }else{
            $return = false;
        }
        return $return;
    }

    //用户对电影进行评分后，增加缓存
    protected function addMovieScoreCache($ucid,$movieId,$scoreInfo){
        $input = ['ucid'=>$ucid];
        $keyTemplate = NEWCOMMENT_USER_SCORE;
        $redisKey = $this->swtichRedisKey($input,$keyTemplate);
        $redisRe = $this->redis->WYhSet($redisKey,$movieId,$scoreInfo);
        return $redisRe;
    }


    //db中用户删除一条评分
    //【测通】
    public function delScore($inputArrData){
        $limitArr=['ucid','movieId'];
        $mustFields = ['ucid','movieId'];
        $arrData = $this->formatInputArray($inputArrData,$limitArr,$mustFields);
        $ucid = $arrData['ucid'];
        $movieId = $arrData['movieId'];
        $fetchWhere = 'ucid = :ucid and movieId = :movieId';
        $fetchParams[':ucid'] = $ucid;
        $fetchParams[':movieId'] = $movieId;
        $fetchRe = $this->getPdoHelper()->fetchOne($fetchWhere,$fetchParams);

        if($fetchRe){
            $remWhere = 'id = '.$fetchRe['id'];
            $removeRe = $this->getPdoHelper()->remove($remWhere);
            if($removeRe){
                $return = true;
                //用户对电影删除评分后，增加缓存
                $this->delMovieScoreCache($ucid,$movieId);
                //影片评分人数-1(新)
                $this->delScoreNumberCache($movieId,$fetchRe['score']);
            }else{
                $return = true;
            }
        }else{
            $return = false;
        }
        return $return;
    }

    //用户对电影删除评分后，增加缓存
    protected function delMovieScoreCache($ucid,$movieId){
        $input = ['ucid'=>$ucid];
        $keyTemplate = NEWCOMMENT_USER_SCORE;
        $redisKey = $this->swtichRedisKey($input,$keyTemplate);
        $redisRe = $this->redis->WYhDel($redisKey,$movieId);
        return $redisRe;
    }

    //db中用户更新对影片的评分
    //【测通】
    public function updateScore($ucid,$movieId,$inputArrData){
        foreach($inputArrData as $k=>$v){
            $limitArr=['score','type','channelId','fromId','created','updated'];
            if(in_array($k,$limitArr)){
                $arrData[$k]=$v;
            }
        }
        $fields = array_keys($arrData);
        $where = 'ucid = :ucid and movieId = :movieId';
        $params = $arrData;
        $params['ucid'] = $ucid;
        $params['movieId'] = $movieId;

        $whereParams=[
            'ucid'=>$ucid,
            'movieId'=>$movieId,
        ];
        $fetchRe = $this->getPdoHelper()->fetchOne($where,$whereParams);
        $oldHashKey = $fetchRe['score'];//将要-1的key
        $newHashKey = $arrData['score'];//将要+1的key
        if($fetchRe){
            $dbRe = $this->getPdoHelper()->update($fields, $params, $where);
            if($dbRe){
                $return = true;
                $arrScoreInfo = [
                    'score'=>$inputArrData['score'],
                ];
                if(!empty($inputArrData['type'])){
                    $arrScoreInfo['type'] = $inputArrData['type'];
                }
                $scoreInfo = json_encode($arrScoreInfo);
                //用户对电影进行评分后，增加缓存
                $this->addMovieScoreCache($ucid,$movieId,$scoreInfo);
                //更新用户评论数的hash集合
                $this->updateScoreNumberCache($movieId,$oldHashKey,$newHashKey);
            }else{
                $return = false;
            }
        }else{
            $return = false;
        }
        return $return;
    }

    //查询某影片下，某个用户的评分
    public function queryMovieUserScore($movieId,$ucid){
        //被动缓存
//        $input = ['ucid'=>$ucid];
//        $keyTemplate = NEWCOMMENT_USER_SCORE;
//        $kashKey = $movieId;
//        $where = "movieId = :movieId and ucid = :ucid";
//        $whereParams = [':movieId'=>$movieId,':ucid'=>$ucid];
//        $redisRe = $this->queryHashCache($input,$kashKey,$keyTemplate,self::DB_CONFIG_KEY,self::TABLE_NAME,self::SCORE_EXPIRE_TIME,$where,$whereParams);
//        return $redisRe;

        //主动缓存
        $input = ['ucid'=>$ucid];
        $keyTemplate = NEWCOMMENT_USER_SCORE;
        $redisKey = $this->swtichRedisKey($input,$keyTemplate);
        $hashKey = $movieId;
        return $this->redis->WYhGet($redisKey,$hashKey);
    }

    //更新redis的真实评分人数缓存
    protected function updateScoreNumberCache($movieId,$oldHashKey,$newHashKey){
        $input = ['movieId'=>$movieId];
        $keyTemplate = NEWCOMMENT_SCORE_REAL_NUMBER;
        $redisKey = $this->swtichRedisKey($input,$keyTemplate);
        $oldNum = $this->redis->WYhGet($redisKey,$oldHashKey);
        if($oldNum===false){
            $where = "id=:id";
            $params = [':id'=>$movieId];
            $dbRe = $this->pdo('dbApp','t_movie')->fetchOne($where,$params);
            if($dbRe){
                $hashSetInfo = [
                    '0'=>$dbRe['scoreRealNum0'],
                    '20'=>$dbRe['scoreRealNum20'],
                    '40'=>$dbRe['scoreRealNum40'],
                    '60'=>$dbRe['scoreRealNum60'],
                    '80'=>$dbRe['scoreRealNum80'],
                    '100'=>$dbRe['scoreRealNum100'],
                ];
            }else{
                $hashSetInfo = [
                    '0'=>0,
                    '20'=>0,
                    '40'=>0,
                    '60'=>0,
                    '80'=>0,
                    '100'=>0,
                ];
            }
            $this->redis->WYhMset($redisKey,$hashSetInfo);
            $this->redis->WYexpire($redisKey,self::SCORE_NUMBER_CACHE_EXPIRE);
        }
        if($oldNum>0){
            $this->redis->WYhIncrBy($redisKey,$oldHashKey,-1);//对原来的key做 -1
        }
        return $this->redis->WYhIncrBy($redisKey,$newHashKey,1);//对新的做 +1
    }

    //添加redis的评分人数缓存
    protected function insertScoreNumberCache($movieId,$hashKey){
        $input = ['movieId'=>$movieId];
        $keyTemplate = NEWCOMMENT_SCORE_REAL_NUMBER;
        $redisKey = $this->swtichRedisKey($input,$keyTemplate);
        return $this->redis->WYhIncrBy($redisKey,$hashKey,1);//+1
    }

    //redis的评分人数缓存 -1
    protected function delScoreNumberCache($movieId,$hashKey){
        $input = ['movieId'=>$movieId];
        $keyTemplate = NEWCOMMENT_SCORE_REAL_NUMBER;
        $redisKey = $this->swtichRedisKey($input,$keyTemplate);
        $oldNum = $this->redis->WYhGet($redisKey,$hashKey);
        if($oldNum===false){
            $where = "id=:id";
            $params = [':id'=>$movieId];
            $dbRe = $this->pdo('dbApp','t_movie')->fetchOne($where,$params);
            if($dbRe){
                $hashSetInfo = [
                    '0'=>$dbRe['scoreRealNum0'],
                    '20'=>$dbRe['scoreRealNum20'],
                    '40'=>$dbRe['scoreRealNum40'],
                    '60'=>$dbRe['scoreRealNum60'],
                    '80'=>$dbRe['scoreRealNum80'],
                    '100'=>$dbRe['scoreRealNum100'],
                ];
            }else{
                $hashSetInfo = [
                    '0'=>0,
                    '20'=>0,
                    '40'=>0,
                    '60'=>0,
                    '80'=>0,
                    '100'=>0,
                ];
            }
            $this->redis->WYhMset($redisKey,$hashSetInfo);
            $this->redis->WYexists($redisKey,self::SCORE_NUMBER_CACHE_EXPIRE);
        }
        if($oldNum>0){
            $this->redis->WYhIncrBy($redisKey,$hashKey,-1);//对原来的key做 -1
        }
    }

    //获取指定影片下各个档次评分人数(实际+注水)  返回值为整形
    public function getMovieScoreNumbers($movieId){
        $return = [
            '0'=>0,
            '20'=>0,
            '40'=>0,
            '60'=>0,
            '80'=>0,
            '100'=>0,
        ];
        $input = ['movieId'=>$movieId];
        //从缓存中拿到真实人数
        $keyTemplate = NEWCOMMENT_SCORE_REAL_NUMBER;
        $realKey = $this->swtichRedisKey($input,$keyTemplate);
        $realResult = $this->redis->WYhGetAll($realKey);
        //从缓存中拿到注水人数
        $keyTemplate = NEWCOMMENT_SCORE_FILL_NUMBER;
        $fillKey = $this->swtichRedisKey($input,$keyTemplate);
        $fillResult = $this->redis->WYhGetAll($fillKey);

        //如果真实人数和注水人数有任何一个从缓存中娶不到，那么要被动生成
        if($realResult===false || $fillResult===false){
            //真实人数的被动缓存
            $where = "id = :id";
            $params = [':id'=>$movieId];
            $dbRe = $this->pdo('dbApp','t_movie')->fetchOne($where,$params);
            if($dbRe){
                $realHashSetInfo = [
                    '0'=>$dbRe['scoreRealNum0'],
                    '20'=>$dbRe['scoreRealNum20'],
                    '40'=>$dbRe['scoreRealNum40'],
                    '60'=>$dbRe['scoreRealNum60'],
                    '80'=>$dbRe['scoreRealNum80'],
                    '100'=>$dbRe['scoreRealNum100'],
                ];
                $fillHashSetInfo = [
                    '0'=>$dbRe['scoreFillNum0'],
                    '20'=>$dbRe['scoreFillNum20'],
                    '40'=>$dbRe['scoreFillNum40'],
                    '60'=>$dbRe['scoreFillNum60'],
                    '80'=>$dbRe['scoreFillNum80'],
                    '100'=>$dbRe['scoreFillNum100'],
                ];
            }else{
                $realHashSetInfo = [
                    '0'=>0,
                    '20'=>0,
                    '40'=>0,
                    '60'=>0,
                    '80'=>0,
                    '100'=>0,
                ];
                $fillHashSetInfo = [
                    '0'=>0,
                    '20'=>0,
                    '40'=>0,
                    '60'=>0,
                    '80'=>0,
                    '100'=>0,
                ];
            }
            $this->redis->WYhMset($realKey,$realHashSetInfo);
            $this->redis->WYexists($realKey,self::SCORE_NUMBER_CACHE_EXPIRE);
            $this->redis->WYhMset($fillKey,$fillHashSetInfo);
            $this->redis->WYexists($fillKey,self::SCORE_NUMBER_CACHE_EXPIRE);
            $realResult = $realHashSetInfo;
            $fillResult = $fillHashSetInfo;
        }

        foreach($return as $k=>&$v){
            $tmpScore = null;
            if($realResult){
                $tmpScore = isset($realResult[$k])? $realResult[$k] : 0;
                $v+=$tmpScore;
            }
            if($fillResult){
                $tmpScore = isset($fillResult[$k]) ? $fillResult[$k] : 0 ;
                $v+=$tmpScore;
            }
            $v = (int)$v;
        }
        return $return;
    }
}
