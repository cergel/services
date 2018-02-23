<?php

namespace sdkService\model;

class Sche extends BaseNew
{

    /**
     * 从 redis 读取影院排期 原始信息
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId 城市编号
     * @param string $iCinemaId 影院编号
     *
     * @return array
     */
    public function readCinemaSche($iChannelId, $iCityId, $iCinemaId)
    {
        if ($this->isRestructChannel($iChannelId)) {
            $data = $this->readCinemaScheOnRestruct($iChannelId, $iCinemaId);
        } else {
            $data = $this->readCinemaScheOnCronTask($iChannelId, $iCityId, $iCinemaId);
        }

        return $this->helper('OutPut')->jsonConvert($data);
    }

    /*
     * 保存影院下所有影片的所有排期
     */
    public function saveCinemaSche($iChannelId, $iCityId, $iCinemaId,$arrData)
    {
        $strKey = KEY_CINEMA_SCHE;
        $subKey = $iCityId . "_" . $iCinemaId;
        $redisManager = $this->redis($iChannelId, STATIC_MOVIE_DATA);
        $redisManager->getRouter()->setUseSubKeyOnce();
        return $redisManager->WYhSet($strKey, $subKey,json_encode($arrData));
    }


    /**
     * 保存定价通知过来的价格，用来生成排期的时候用
     * cityid暂时没用
     */
    public function savePriceInfo($iChannelId,$iCityId,$iCinemaId,$iMpId,$iPriceDiff)
    {
        $strKey = KEY_PRICE_NOTIFY."_".$iCinemaId;
        $subKey = $iMpId;
        $redisManager = $this->redis($iChannelId, STATIC_MOVIE_DATA);
        return $redisManager->WYhSet($strKey, $subKey,json_encode(['price_diff'=>$iPriceDiff]));
    }

    /**
     * 从 redis 读取影片排期 原始信息
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId 城市Id
     * @param string $iMovieId 影片Id
     *
     * @return array
     */
    public function readMovieSche($iChannelId, $iCityId, $iMovieId)
    {
        if ($this->isRestructChannel($iChannelId)) {
            $data = $this->readMovieScheOnRestruct($iChannelId, $iCityId, $iMovieId);
        } else {
            $data = $this->readMovieScheOnCronTask($iChannelId, $iCityId, $iMovieId);
        }

        return $this->helper('OutPut')->jsonConvert($data);
    }

    /**
     * 从redis中读取指定影院的价格 支持影院数组批量传入
     * @param $iChannelId
     * @param $CinemaIdArray
     * @return array|string
     */
    public function readCinemaSchePrice($iChannelId, $CinemaIdArray)
    {
        if ($this->isRestructChannel($iChannelId)) {
            $data = $this->readCinemaSchePriceOnRestruct($iChannelId, $CinemaIdArray);
        } else {
            $data = $this->readCinemaSchePriceOnCronTask($iChannelId, $CinemaIdArray);
        }
        //确保没数据时不会报错
        if ($data === false) {
            $data = [];
        }
        return $data;
    }

    /**
     * 从redis中读取排期判断影片详情页的购买标识
     * @param $iChannelId
     * @param $iCityId
     * @param $iMovieId
     * @return int
     */
    public function BuyFlagByMovie($iChannelId, $iCityId, $iMovieId)
    {
        if ($this->isRestructChannel($iChannelId)) {
            $data = $this->BuyFlagByMovieOnRestruct($iChannelId, $iCityId, $iMovieId);
        } else {
            $data = $this->BuyFlagByMovieOnCronTask($iChannelId, $iCityId, $iMovieId);
        }
        return $data;
    }


    //——————————————private——————————————//


    /**
     * 从新版CronTask读取影院排期原始信息
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId 城市编号
     * @param string $iCinemaId 影院编号
     *
     * @return array
     */
    public function readCinemaScheOnCronTask($iChannelId, $iCityId, $iCinemaId)
    {
        $strKey = KEY_CINEMA_SCHE;
        $subKey = $iCityId . "_" . $iCinemaId;
        $redisManager = $this->redis($iChannelId, STATIC_MOVIE_DATA);
        $redisManager->getRouter()->setUseSubKeyOnce();
        $data = $redisManager->WYhGet($strKey, $subKey);

        return $data;
    }

    /**
     * 从重构版CronTask读取影院排期原始信息
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCinemaId 影院编号
     *
     * @return array
     */
    public function readCinemaScheOnRestruct($iChannelId, $iCinemaId)
    {
        $strKey = STATIC_KEY_CINEMA_SCHE . $iCinemaId;
        $redisManager = $this->redis($iChannelId, STATIC_MOVIE_DATA);
        $data = $redisManager->WYget($strKey);

        return $data;
    }

    /**
     * 从原版CronTask读取影片排期原始信息
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId 城市Id
     * @param string $iMovieId 影片Id
     *
     * @return array
     */
    public function readMovieScheOnCronTask($iChannelId, $iCityId, $iMovieId)
    {
        $strKey = KEY_MOVIE_SCHE;
        $subKey = $iCityId . '_' . $iMovieId;
        $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhGet($strKey, $subKey);

        return $data;
    }

    /**
     * 从重构版CronTask读取影片排期原始信息
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId 城市Id
     * @param string $iMovieId 影片Id
     *
     * @return array
     */
    public function readMovieScheOnRestruct($iChannelId, $iCityId, $iMovieId)
    {
        $strKey = STATIC_KEY_MOVIE_SCHE;
        $subKey = $iCityId . '_' . $iMovieId;
        $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhGet($strKey, $subKey);

        return $data;
    }

    /**
     * 从旧版信息中读取影院排期的价格
     * @param $iChannelId 渠道
     * @param $CinemaIdArray 影院ID或者影院ID的数组
     * @return array|string
     */
    public function readCinemaSchePriceOnCronTask($iChannelId, $CinemaIdArray)
    {
        $strKey = KEY_CINEMA_SCHE_PRICE;
        if (is_array($CinemaIdArray)) {
            $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhMGet($strKey, $CinemaIdArray);
        } else {
            $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhGet($strKey, $CinemaIdArray);
        }
        return $data;
    }

    /**
     * @param $iChannelId 渠道
     * @param $CinemaIdArray 影院ID或者影院ID的数组
     * @return array|string
     */
    public function readCinemaSchePriceOnRestruct($iChannelId, $CinemaIdArray)
    {
        $strKey = STATIC_KEY_CINEMA_SCHE_PRICE;
        if (is_array($CinemaIdArray)) {
            $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhMGet($strKey, $CinemaIdArray);
        } else {
            $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhGet($strKey, $CinemaIdArray);
        }
        return $data;
    }

    /**
     * 给影片详情添加buyflag
     * @param $iChannelId
     * @param $iCityId
     * @param $iMovieId
     * @return int
     */
    public function BuyFlagByMovieOnCronTask($iChannelId, $iCityId, $iMovieId)
    {
        $strKey = KEY_MOVIE_SCHE_MAP . $iMovieId;
        $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYsMembers($strKey);
        $arrRet = [];
        foreach ($data as $k => $v) {
            $arr = explode("@", $v);
            $schecityId = $arr[0];
            $scheDate = $arr[2];
            $schetimes = explode("|", $arr[3]);
            foreach ($schetimes as $time) {
                $timestamp = strtotime($scheDate . " " . $time);
                if ($timestamp >= time() AND $schecityId == $iCityId) {
                    return 1;
                }
            }
        }
        return 0;
    }

    /**
     * 给影片详情添加buyflag
     * @param $iChannelId
     * @param $iCityId
     * @param $iMovieId
     * @return int
     */
    public function BuyFlagByMovieOnRestruct($iChannelId, $iCityId, $iMovieId)
    {
        $strKey = STATIC_KEY_MOVIE_SCHE_MAP . $iMovieId;
        $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhGetAll($strKey);
        if (!$data) {
            $data = [];
        }
        $arrRet = [];
        foreach ($data as $k => $v) {
            $arr = explode("@", $k);
            $arrRet[$arr[0]][] = $arr[1];
        }
        if (empty($arrRet[$iCityId])) {
            $buy_flag = 0;
        } else {
            $buy_flag = 1;
        }
        return $buy_flag;
    }
}