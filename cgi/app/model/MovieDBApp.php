<?php

namespace sdkService\model;

use \sdkService\helper\RedisManager;
use wepiao\wepiao;
use DI\Definition\ArrayDefinition;

/**
 * @tutorial 影片的相关统计信息（数据库）
 * @author liulong
 *
 */
class MovieDBApp extends BaseNew
{
    const CACHE_EXPIRE = 600; //被动缓存-缓存时间
    const BASE_SCORE = 80; //基础评分
    const BASE_SCORE_COUNT = 10; //基础评分人数
    const BASE_WANT_COUNT = 100;  //起始想看人数
    const BASE_SEEN_COUNT = 10;  //起始看过人数
    const KEY_MOVIE_DYNAMIC = "MOVIE_DYNAMIC_"; //影片评分类动态数据
    const TTL_MOVIE_DYNAMIC = 3600;//影片评分TTL

    public function __construct()
    {
        $this->pdo('dbApp', 't_movie');
    }

    /**
     * @tutorial 获取一条影片的对象
     * @param int $iMovieId
     * @return object
     */
    public function getOneMovieObject($iMovieId)
    {
        //先从缓存读，
        $jsonMovieData = $this->redis(\wepiao::getChannelId(), USER_COMMENT_CACHE)->WYget(COMMENT_MOVIE_INFO . $iMovieId);

        if (empty($jsonMovieData)) {
            $where = 'id = :id';
            $params = [':id'=>$iMovieId];
            $objData = $this->pdohelper->fetchObject($where,$params);
            if (!empty($objData)) {
                $this->redis(\wepiao::getChannelId(), USER_COMMENT_CACHE)->WYset(COMMENT_MOVIE_INFO . $iMovieId, json_encode($objData));
                $this->redis(\wepiao::getChannelId(), USER_COMMENT_CACHE)->WYexpire(COMMENT_MOVIE_INFO . $iMovieId, self::CACHE_EXPIRE);
            }
        } else {
            $objData = json_decode($jsonMovieData);
        }
        return $objData;
    }

    /**
     * @tutorial 插入一条影片数据
     * @param array $arrInstallData
     * @return int
     */
    public function insert($arrInstallData)
    {
        $arrInstallData['created'] = time();
        return $this->pdohelper->Nadd($arrInstallData, array_keys($arrInstallData));
    }

    /**
     * @tutorial 修改一条影片数据
     * @param array $arrInstallData
     * @param int $iMovieId
     * @return int
     */
    public function update($arrInstallData, $iMovieId)
    {
        $arrInstallData['updated'] = time();
        return $this->pdohelper->update(array_keys($arrInstallData), $arrInstallData, "id='$iMovieId'");
    }

    /**
     * @tutorial 更新指定字段加减，
     * @param array $arrParam EG:['saveCount'=>-1]表示saveCount字段减1
     * @param int $iMovieId
     * @return boolean
     */
    public function saveParamNum($arrParam, $iMovieId)
    {
        $boolRes = false;
        $iCount = $this->pdohelper->newupdate($arrParam, "id='$iMovieId'");
        if (is_numeric($iCount)) {
            $boolRes = true;
            //todo 缓存 突然发现：：：不用缓存也行。。。。
        }
        return $boolRes;
    }

    /**
     * @tutorial  批量获取影片的动态评分
     * @param array $MovieIds
     * @return array
     */
    public function getMovieDynamicData(array $MovieIds = [])
    {
        $returnArr = [];
        //获取PDO实例
        $PDO = $this->pdohelper->getPdo();
        foreach ($MovieIds as $MovieId) {
            $cacheKey = self::KEY_MOVIE_DYNAMIC . $MovieId;
            $cacheString = $this->redis(\wepiao::getChannelId(), USER_GENERATED_CONTENT)->WYget($cacheKey);
            if ($cacheString) {
                $returnArr[$MovieId] = json_decode($cacheString, true);
            } else {
                $dbArr = self::getDBMovieDynamicData($MovieId);
                if ($dbArr) {
                    $DynamicData = [];
                    $DynamicData['seenCount'] = $dbArr['seenCount'];
                    $DynamicData['score'] = $dbArr['score'];
                    $DynamicData['wantCount'] = $dbArr['wantCount'];
                } else {
                    $DynamicData = [];
                    $DynamicData['seenCount'] = self::BASE_SEEN_COUNT;
                    $DynamicData['score'] = self::BASE_SCORE;
                    $DynamicData['wantCount'] = self::BASE_WANT_COUNT;
                }
                $this->redis(\wepiao::getChannelId(), USER_GENERATED_CONTENT)->WYset($cacheKey, json_encode($DynamicData), self::TTL_MOVIE_DYNAMIC);
                $returnArr[$MovieId] = $DynamicData;
            }
        }
        return $returnArr;
    }

    /**
     * @tutorial 数据库获取影片动态评分等数据
     * @param $MovieId
     * @return array
     */
    private function getDBMovieDynamicData($MovieId)
    {
        $where = 'id = :MovieId';
        $params = [
            ':MovieId' => $MovieId,
        ];
        $fields = "score,baseWantCount+wantCount as wantCount,seenCount";
        $dbRe = $this->pdohelper->fetchOne($where, $params, $fields, null);
        return $dbRe;
    }

    public function saveMovieBaseScore($movieId,$arrData)
    {
        $redisKey = "newcomment_score_fill_number_movie:".$movieId;
        return $this->redis(\wepiao::getChannelId(), USER_GENERATED_CONTENT)->WYhMset($redisKey,$arrData);
    }
}
