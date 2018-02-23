<?php

namespace sdkService\service;

class serviceCity extends serviceBase
{
    
    /**
     * 获取城市列表
     *
     * @param string $channelId 渠道编号
     *
     * @return array
     */
    public function readCity(array $arrInput = [])
    {
        $iChannelId = self::getParam($arrInput, 'channelId');
        $return = self::getStOut();
        if (empty($iChannelId)) {
            return $return;
        }
        $data = $this->model('city')->readCity($iChannelId);
        $return['data'] = $data;
        
        return $return;
    }
    
    /**
     * 新版获取城市列表数据-V2版本
     * 相比V1版本,此接口数据结构不同
     *
     * @param string $channelId 渠道编号
     *
     * @return array
     */
    public function readCityV2(array $arrInput = [])
    {
        $iChannelId = self::getParam($arrInput, 'channelId');
        $return = self::getStOut();
        if (empty($iChannelId)) {
            return $return;
        }
        $data = $this->model('city')->readCityV2($iChannelId);
        $return['data'] = $data;
        
        return $return;
    }
    
    /**
     * 新版获取城市列表数据——V3版本
     * 此版本接口, 完整透传商品中心的接口数据, 数据格式和V2完全一样
     *
     * @param   string $channelId 渠道编号
     *
     * @return array
     */
    public function readCityV3(array $arrInput = [])
    {
        //参数整理
        $iChannelId = self::getParam($arrInput, 'channelId');
        $return = self::getStOut();
        if (empty($iChannelId)) {
            return $return;
        }
        $httpParams = [
            'arrData'   => [],
            'sMethod'   => 'GET',
            'iTimeout'  => 2,
            'iTryTimes' => 1,
        ];
        $return = $this->http(JAVA_API_CORE_CENTER_CITIES, $httpParams);
        
        return $return;
    }
    
    /**
     * 新版获取城市列表数据——V4版本
     * 此版本接口, 完整透传商品中心的接口数据, 数据格式和readCity完全一样
     *
     * @param   string $channelId 渠道编号
     *
     * @return array
     */
    public function readCityV4(array $arrInput = [])
    {
        //参数整理
        $iChannelId = self::getParam($arrInput, 'channelId');
        $return = self::getStOut();
        if (empty($iChannelId)) {
            return $return;
        }
        $httpParams = [
            'arrData'   => [],
            'sMethod'   => 'GET',
            'iTimeout'  => 2,
            'iTryTimes' => 1,
        ];
        $return = $this->http(JAVA_API_CORE_CENTER_CITIES_V1_CHANNEL, $httpParams);
        
        return $return;
    }

    /**
     * 新版获取城市列表数据-V5版本 该版本基于V2增加了格瓦拉的citycode
     * 相比V1版本,此接口数据结构不同
     * @param array $arrInput
     */
    public function readCityGewala(array $arrInput = [])
    {
        $geralaCity = include  CGI_APP_PATH . 'app/config/GewaraCity.php';
        $cities = $this->readCityV3($arrInput);
        if(isset($cities['data']['hot'])){
            $this->_getGeralaCity($cities['data']['hot'],$geralaCity);
        }
        if(isset($cities['data']['list'])){
            foreach ($cities['data']['list'] as &$value){
                $this->_getGeralaCity($value,$geralaCity);
            }
        }
        return $cities;
    }

    /**
     * 增加格瓦拉cicyCode字段
     * @param $arrCity
     * @param $geralaCity
     */
    private function _getGeralaCity(&$arrCity,$geralaCity)
    {
        foreach ($arrCity as &$val){
            $val['cityCode'] = isset($geralaCity[$val['id']])?$geralaCity[$val['id']]:'';
        }
    }


    /**
     * 新版获取城市列表数据——V5版本
     * 此版本接口, 完整透传商品中心的接口数据, 数据格式和V3差不多，只是V3为pinyin，V5为pingyin
     *
     * @param   string $channelId 渠道编号
     *
     * @return array
     */
    public function readCityV5(array $arrInput = [])
    {
        //参数整理
        $iChannelId = self::getParam($arrInput, 'channelId');
        $return = self::getStOut();
        if (empty($iChannelId)) {
            return $return;
        }
        $httpParams = [
            'arrData'   => [],
            'sMethod'   => 'GET',
            'iTimeout'  => 2,
            'iTryTimes' => 1,
        ];
        $return = $this->http(JAVA_API_CORE_CENTER_CITIES_V1, $httpParams);
        
        return $return;
    }
    
}