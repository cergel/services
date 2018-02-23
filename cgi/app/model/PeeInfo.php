<?php

namespace sdkService\model;


/**
 * @tutorial 尿点相关功能
 * @author liulong
 *#
 */
class PeeInfo extends BaseNew
{
    const CACHE_EXPIRE = 172800; //被动缓存-缓存时间
    const MOVIE_CACHE_KEY = "pid_user_";
    const  MOVIE_USER_KEY = "pee_movie_user_";
    const PEE_COUNT = 'pee_count_pid_';
    const DB_NAME = 'dbPee';
    const  DB_TABLE_NAME = 't_pee_user';

    private $peeRedis;//redis对象，存放redis

    public function __construct()
    {
//        $this->pdo(self::DB_NAME, self::DB_TABLE_NAME);//链接数据库
        $this->peeRedis = $this->redis(\wepiao::getChannelId(), USER_MOVIE_PEE);
    }

    /**
     * 插入：用户点击我也尿了
     * @param $arrData
     */
    public function insertPeeInfo($arrData)
    {
        $arrData = self::getInstallData($arrData);
        $this->pdo(self::DB_NAME, self::DB_TABLE_NAME);//链接数据库
        $insertRe = $this->pdohelper->Nadd($arrData, array_keys($arrData));
        if(!empty($insertRe)){
            $this->_addUserPeeCache($arrData['p_id'],$arrData['openId'],$arrData['movieId']);
            self::_savePeeCount($arrData['p_id'],'add');
        }
        return $insertRe;
    }
    /**
     * 删除：用户点击我也尿了
     * @param $arrData
     */
    public function deletePeeInfo($pid,$openId,$movieId)
    {
        $res = false;
        $fetchWhere = 'openId=:openId and p_id = :p_id';
        $fetchParams = [
            'openId' => $openId,
            'p_id' => $pid
        ];
        $this->pdo(self::DB_NAME, self::DB_TABLE_NAME);//链接数据库
        $removeRe = $this->pdohelper->remove($fetchWhere, $fetchParams);
        if($removeRe){
            $this->_delUserPeeCache($pid,$openId,$movieId);
            self::_savePeeCount($pid,'del');
            $res = true;
        }
        return $res;
    }
    /**
     * 查询当前用户是否想看此电影尿过该尿点
     * @param $openId
     * @param $pid
     * @return bool
     */
    public function queryPeeOpenId($pid,$openId,$movieId)
    {
        $redisMovieOpenKey = self::MOVIE_USER_KEY.$movieId.'_'.$openId;
        if(!$this->peeRedis->WYexists($redisMovieOpenKey)){
            //被动
            self::_getPeeMovieUserRedisByDb($openId,$movieId);
        }
        $res = $this->peeRedis->WYsIsMember($redisMovieOpenKey,$pid);
        $this->peeRedis->WYexpire($redisMovieOpenKey,self::CACHE_EXPIRE);//设置过期时间
        return empty($res)?0:1;
    }

    /**
     * 返回当前用户对当前影片下尿点点赞信息【储存的是子尿点的id】
     * @param $openId
     * @param $movieId
     * @return array
     */
    public function getAllUserPeeMovie($openId,$movieId)
    {
        $redisMovieOpenKey = self::MOVIE_USER_KEY.$movieId.'_'.$openId;
        return $this->peeRedis->WYsMembers($redisMovieOpenKey);
    }

    /**
     * 被动数据：被动 - 指定用户对指定影片的数据
     * @param $movieId
     * @param $openId
     */
    private function _getPeeMovieUserRedisByDb($movieId,$openId)
    {
        $redisMovieOpenKey = self::MOVIE_USER_KEY.$movieId.'_'.$openId;
        $strWhere= ':movieId = movieId AND :openId = openId';
        $arrWhere = ['movieId'=>$movieId,'openId'=>$openId];
        $this->pdo(self::DB_NAME, self::DB_TABLE_NAME);//链接数据库
        $arrData = $this->pdohelper->fetchArray($strWhere,$arrWhere);
        if(!empty($arrData)){
            foreach($arrData as $val){
                $this->peeRedis->WYsAdd($redisMovieOpenKey,$val['p_id']);
            }
        }else{//插入个空元素
            $this->peeRedis->WYsAdd($redisMovieOpenKey,'');
        }
        $this->peeRedis->WYexpire($redisMovieOpenKey,self::CACHE_EXPIRE);//设置过期时间
    }
    /**
     * 删除点尿
     * @param $openId
     * @param $pid
     * @return mixed
     */
    protected function _delUserPeeCache($pid,$openId,$movieId)
    {
        $redisMovieOpenKey = self::MOVIE_USER_KEY.$movieId.'_'.$openId;
        $redisRe = $this->peeRedis->WYsRem($redisMovieOpenKey, $pid);
        $this->peeRedis->WYexpire($redisMovieOpenKey,self::CACHE_EXPIRE);//设置过期时间
        return $redisRe;
    }
    /**
     * 写入集合,插入一条点尿
     * @param $openId
     * @param $time
     * @param $pid
     * @return mixed
     */
    protected function _addUserPeeCache($pid,$openId, $movieId)
    {
        $redisMovieOpenKey = self::MOVIE_USER_KEY.$movieId.'_'.$openId;
        $redisRe = $this->peeRedis->WYsAdd($redisMovieOpenKey,$pid);
        $this->peeRedis->WYexpire($redisMovieOpenKey,self::CACHE_EXPIRE);//设置过期时间
        return $redisRe;
    }



    //返回指定尿点的点尿总数
    public function queryPeeCount($pid)
    {
        $res = $this->peeRedis->WYget(self::PEE_COUNT.$pid);
        return empty($res)?0:$res;
    }

    private function _savePeeCount($pid,$strType)
    {
        $rediPeeCountKey = self::PEE_COUNT.$pid;
        if(!$this->peeRedis->WYexists($rediPeeCountKey)){
            //被动总数
            $strWhere = ':p_id=p_id';
            $arrWhere = ['p_id'=>$pid];
            $this->pdo(self::DB_NAME, self::DB_TABLE_NAME);//链接数据库
            $res = $this->pdohelper->fetchCol($strWhere,$arrWhere,'count(1)');
            if(!empty($res[0])){
                $res = $res[0];
                $this->peeRedis->WYset($rediPeeCountKey,$res);
            }else{
                $this->peeRedis->WYset($rediPeeCountKey,0);
            }
        }else{
            if($strType == 'add'){
                $this->peeRedis->WYincr($rediPeeCountKey);
            }else{
                $this->peeRedis->WYdecr($rediPeeCountKey);
            }
        }

    }


    /**
     * @tutorial 字段过滤
     * @param array $arrData
     * @return array $arrDbData
     */
    private function getInstallData($arrData)
    {
        $arrDbData = [];
        $arrMasterData = ['uid','openId','p_id','created','channelId','movieId'];
        if (is_array($arrData)){
            foreach ($arrMasterData as $val)
            {
                if (isset($arrData[$val]))
                    $arrDbData[$val] = $arrData[$val];
            }
            $arrDbData['created'] = time();
        }
        return $arrDbData;
    }


}
