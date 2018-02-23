<?php

namespace sdkService\model;


class City extends BaseNew
{
    
    /**
     * 获取城市信息
     *
     * @param string $iChannelId
     *
     * @return array
     */
    public function readCity($iChannelId = '')
    {
        if ($this->isRestructChannel($iChannelId)) {
            $data = $this->readCityOnRestruct($iChannelId);
        }
        else {
            $data = $this->readCityOnCronTask($iChannelId);
        }
        
        return $this->helper('OutPut')->jsonConvert($data);
    }
    
    /**
     * 从原版CronTask读取数据
     *
     * @param $iChannelId
     *
     * @return array
     */
    private function readCityOnCronTask($iChannelId)
    {
        $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYget(KEY_CITY);
        
        return $data;
    }
    
    /**
     * 从重构版CronTask读取数据
     *
     * @param $iChannelId
     *
     * @return array
     */
    private function readCityOnRestruct($iChannelId)
    {
        $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYget(STATIC_KEY_CITY);
        
        return $data;
    }
    
    /**
     * 获取城市信息
     *
     * @param string $iChannelId
     *
     * @return array
     */
    public function readCityV2($iChannelId = '')
    {
        if ($this->isRestructChannel($iChannelId)) {
            $data = $this->readCityOnRestructV2($iChannelId);
        }
        else {
            $data = $this->readCityOnCronTaskV2($iChannelId);
        }
        
        return $this->helper('OutPut')->jsonConvert($data);
    }
    
    /**
     * 从原版CronTask读取数据
     *
     * @param $iChannelId
     *
     * @return array
     */
    private function readCityOnCronTaskV2($iChannelId)
    {
        $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYget(KEY_CITY_V2);
        
        return $data;
    }
    
    /**
     * 从重构版CronTask读取数据
     *
     * @param $iChannelId
     *
     * @return array
     */
    private function readCityOnRestructV2($iChannelId)
    {
        $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYget(STATIC_KEY_CITY_V2);
        
        return $data;
    }

    /**
     * 经纬度精确定位写入大数据redis，进行IP修正。
     * @param $arrDara
     */
    public function insertBigDataRedisByIp($arrDara)
    {
        try {
            return $this->redis($arrDara['channelId'],BIGDATA_IP_LIST)->WYlPush(BIGDATA_IP_REDIS_KEY,json_encode($arrDara));
        }catch (\Exception $e){
            return true;
        }
    }
    
    
}