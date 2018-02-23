<?php

namespace sdkService\model;


class Cinema extends BaseNew
{

    /**
     * 读取城市下的影院列表
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId 城市编号
     *
     * @return array
     */
    public function readCityCinema($iChannelId, $iCityId)
    {
        if ($this->isRestructChannel($iChannelId)) {
            $data = $this->readCityCinemaOnRestruct($iChannelId, $iCityId);
        } else {
            $data = $this->readCityCinemaOnCronTask($iChannelId, $iCityId);
        }

        return $this->helper('OutPut')->jsonConvert($data);
    }

    /**
     * 获取影院影厅座位图信息
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCinemaId
     * @param string $sRoomId
     *
     * @return array
     */
    public function readCinemaRoom($iChannelId, $iCinemaId, $sRoomId)
    {
        if ($this->isRestructChannel($iChannelId)) {
            $data = $this->readCinemaRoomOnRestruct($iChannelId, $iCinemaId, $sRoomId);
        } else {
            $data = $this->readCinemaRoomOnCronTask($iChannelId, $iCinemaId, $sRoomId);
        }

        return $this->helper('OutPut')->jsonConvert($data);
    }

    /**
     * 获取影院详情信息
     *
     * @param  string $iChannelId 渠道编号
     * @param  string $iCinemaId 影院编号
     *
     * @return array
     */
    public function readCinemaInfo($iChannelId, $iCinemaId)
    {
        if ($this->isRestructChannel($iChannelId)) {
            $data = $this->readCinemaInfoOnRestruct($iChannelId, $iCinemaId);
        } else {
            $data = $this->readCinemaInfoOnCronTask($iChannelId, $iCinemaId);
        }

        return $this->helper('OutPut')->jsonConvert($data);
    }

    /**
     * 根据影院的优惠文案
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCinemaId 影院编号
     *
     * @return string
     */
    public function getCinemaDiscount($iChannelId, $iCinemaId)
    {
        if ($this->isRestructChannel($iChannelId)) {
            $strDiscountDes = $this->getCinemaDiscountOnRestruct($iChannelId, $iCinemaId);
        } else {
            $strDiscountDes = $this->getCinemaDiscountOnCronTask($iChannelId, $iCinemaId);
        }

        return $strDiscountDes;
    }




    //——————————private——————————//


    /**
     * 从原版CronTask读取城市下影院列表数据
     *
     * @param $iChannelId
     * @param $iCityId
     *
     * @return string
     */
    private function readCityCinemaOnCronTask($iChannelId, $iCityId)
    {
        $hashKey = KEY_CITY_CINEMA_LIST;
        $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhGet($hashKey, $iCityId);

        return $data;
    }

    /**
     * 从重构版CronTask读取城市下影院列表数据
     *
     * @param $iChannelId
     * @param $iCityId
     *
     * @return string
     */
    private function readCityCinemaOnRestruct($iChannelId, $iCityId)
    {
        $hashKey = STATIC_KEY_CITY_CINEMA_LIST;
        $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhGet($hashKey, $iCityId);

        return $data;
    }

    /**
     * 从原版CronTask读取城市下影院座位图数据
     *
     * @param $iChannelId
     * @param $iCinemaId
     * @param $sRoomId
     *
     * @return array
     */
    private function readCinemaRoomOnCronTask($iChannelId, $iCinemaId, $sRoomId)
    {
        $hashKey = KEY_CINEMA_ROOM_INFO;
        $subKey = $iCinemaId . '_' . $sRoomId;
        $redisManager = $this->redis($iChannelId, STATIC_MOVIE_DATA);
        $redisManager->getRouter()->setUseSubKeyOnce();
        $data = $redisManager->WYhGet($hashKey, $subKey);

        return $data;
    }

    /**
     * 从重构版CronTask读取城市下影院座位图数据
     *
     * @param $iChannelId
     * @param $iCinemaId
     * @param $sRoomId
     *
     * @return array
     */
    private function readCinemaRoomOnRestruct($iChannelId, $iCinemaId, $sRoomId)
    {
        $hashKey = STATIC_KEY_CINEMA_ROOM_INFO . $iCinemaId;
        $subKey = $sRoomId;
        $redisManager = $this->redis($iChannelId, STATIC_MOVIE_DATA);
        $data = $redisManager->WYhGet($hashKey, $subKey);

        return $data;
    }

    /**
     * 从原版CronTask获取影院详情信息
     *
     * @param  string $iChannelId 渠道编号
     * @param  string $iCinemaId 影院编号
     *
     * @return array
     */
    private function readCinemaInfoOnCronTask($iChannelId, $iCinemaId)
    {
        $hashKey = KEY_CINEMA_INFO;
        $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhGet($hashKey, $iCinemaId);

        return $data;
    }

    /**
     * 从重构版CronTask获取影院详情信息
     *
     * @param  string $iChannelId 渠道编号
     * @param  string $iCinemaId 影院编号
     *
     * @return array
     */
    private function readCinemaInfoOnRestruct($iChannelId, $iCinemaId)
    {
        $hashKey = STATIC_KEY_CINEMA_INFO;
        $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhGet($hashKey, $iCinemaId);

        return $data;
    }

    /**
     * 从原版CronTask根据影院的优惠文案
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCinemaId 影院编号
     *
     * @return string
     */
    private function getCinemaDiscountOnCronTask($iChannelId, $iCinemaId)
    {
        $strKey = KEY_CINEMA_ID_DIS_DES . $iCinemaId;
        $strDiscountDes = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYget($strKey);
        $strDiscountDes = !empty($strDiscountDes) ? $strDiscountDes : '';

        return $strDiscountDes;
    }

    /**
     * 从重构版CronTask根据影院的优惠文案
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCinemaId 影院编号
     *
     * @return string
     */
    private function getCinemaDiscountOnRestruct($iChannelId, $iCinemaId)
    {
        $strKey = STATIC_KEY_CINEMA_ID_DIS_DES;
        $strDiscountDes = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhGet($strKey, $iCinemaId);
        $strDiscountDes = !empty($strDiscountDes) ? $strDiscountDes : '';

        return $strDiscountDes;
    }

}