<?php

namespace sdkService\model;

/**
 * @tutorial 大促活动相关
 * @author liulong
 *
 */
class BigPromotion extends BaseNew
{
    const BIG_PROMOTION_CACHE_KEY = 'big_promotion_info_';
    const MOVIE_SHOW_DB_CACHE = MOVIE_SHOW_DB;

    /**
     * 获取大促详情
     * @param $id
     * @return array|bool|mixed|string
     */
    public function getBigPromotion($channelId,$id)
    {
        $bigPromotionRedisKey = empty($id)?self::BIG_PROMOTION_CACHE_KEY.$channelId:self::BIG_PROMOTION_CACHE_KEY.$channelId.'_'.$id;
        $arrData =$this->redis($channelId, self::MOVIE_SHOW_DB_CACHE)->WYget($bigPromotionRedisKey);
        return empty($arrData)?[]:json_decode($arrData,true);
    }
}
