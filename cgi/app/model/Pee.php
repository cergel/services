<?php

namespace sdkService\model;

use \sdkService\helper\RedisManager;
use wepiao\wepiao;

/**
 * @tutorial 尿点相关功能
 * @author   liulong
 *#
 */
class Pee extends BaseNew
{
    const CACHE_EXPIRE = 864000; //被动缓存-缓存时间
    const MOVIE_CACHE_KEY = "movie_info";
    const MOVIE_PEE_CACHE_KEY = "movie_pee_";
    const DB_NAME = 'dbPee';
    const  DB_TABLE_NAME = 't_pee';

    private $peeRedis;//redis对象，存放redis

    public function __construct()
    {
//        $this->pdo(self::DB_NAME, self::DB_TABLE_NAME);//链接数据库
        $this->peeRedis = $this->redis(\wepiao::getChannelId(), USER_MOVIE_PEE);
    }
    /*
     * 获取指定的多个影片的尿点报告信息
     */
    public function getMoreMoviePee($arrData)
    {
        $arrData = $this->peeRedis->WYhMGet(self::MOVIE_CACHE_KEY, $arrData);
        $arrData = !empty( $arrData ) ? array_map('json_decode', $arrData) : new \stdClass();

        return $arrData;
    }
    /**
     * 获取单个影片的尿点报告信息
     * @param $iMovieId
     */
    public function getOneMoviePee($iMovieId)
    {
        $arrData = $this->peeRedis->WYhGet(self::MOVIE_CACHE_KEY, $iMovieId);
        $arrData = !empty( $arrData ) ? json_decode($arrData) : new \stdClass();

        return $arrData;
    }

    public function isMoviePee($pid, $movieId)
    {
        $redisRe = $this->peeRedis->WYzRank(self::MOVIE_PEE_CACHE_KEY . $movieId, $pid);

        return is_numeric($redisRe) ? true : false;
    }


}
