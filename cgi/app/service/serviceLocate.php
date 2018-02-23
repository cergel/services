<?php

namespace sdkService\service;

use \sdkService\helper;

/**
 * 地理位置相关的服务
 * @package app\service
 */
class serviceLocate extends serviceBase
{
    /**
     * 根据IP,获取地理位置
     * @note    该接口返回结果, 和Java返回的结果格式一样, 没有自己再封装, 另外该接口最多调用Java 1 秒钟
     * @param   int channelId             渠道ID
     * @param   string ip                 需要定位的IP地址
     * @param   string type               使用哪种定位服务: 1表示百度, 2表示腾讯
     * @return  array {"ret":0,"sub":0,"msg":"success","data":{"recommend":"\u5317\u4eac\u5e02\u5317\u4eac\u5e02","name"
     *          :"\u5317\u4eac","long":116.407526,"lat":39.90403,"id":"10","ip":"118.194.194.106"}}
     */
    public function ip(array $arrInput = [])
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['ip'] = self::getParam($arrInput, 'ip',helper\Net::getRemoteIp());
        $arrSendParams['channelId'] = self::getParam($arrInput, 'channelId');
        $iType = !empty($arrInput['type']) ? $arrInput['type'] : 3;
        $force = (isset($arrInput['force']) && $arrInput['force'] == 0) ? $arrInput['force'] : 1;
        $arrSendParams['force'] = $force;
        //根据第三方服务类型,获取响应的服务
        $arrReturn = [];
        switch ($iType) {
            case 1:
                $arrReturn = $this->baiduIpLocate($arrSendParams);
                break;
            case 2:
                $arrReturn = $this->tencentIpLocate($arrSendParams);
                break;
            case 3:
                $arrReturn = $this->wyIpLocate($arrSendParams);
                break;
            default:
                $arrReturn['ret'] = -2;
                $arrReturn['sub'] = -2;
                $arrReturn['msg'] = 'params type error!';
        }
        return $arrReturn;
    }


    /*
     * 微影自有IP库
     */
    public function wyIpLocate(array $arrInput = [])
    {
        $arrReturn = self::getStOut();
        $strIp = self::getParam($arrInput, 'ip');
        $arrReturn['data']['ip'] = $strIp;
        $iChannelId = self::getParam($arrInput,'channelId');
        //IP 取前三段
        $arrIp = explode('.',$strIp);
        if(isset($arrIp[3])){
            unset($arrIp[3]);
        }
        $strIp = implode('.',$arrIp);
        try {
            $locate = true;
            $arrRes = $this->model('Geo')->wyIpLocate($strIp, $iChannelId);
            if (!empty($arrRes) && !empty($arrRes['cityid']) && $arrRes['cityid'] !== 'None') {
                $provname = (!empty($arrRes['provname']) && $arrRes['provname'] != 'None') ? $arrRes['provname'] : '';
                $cityname = (!empty($arrRes['cityname']) && $arrRes['cityname'] != 'None') ? $arrRes['cityname'] : '';
                $areaname = (!empty($arrRes['areaname']) && $arrRes['areaname'] != 'None') ? $arrRes['areaname'] : '';
                $arrReturn['data']['recommend'] = $provname . $cityname . $areaname;
                $arrReturn['data']['name'] = !empty($cityname) ? $cityname : $provname;
                $arrReturn['data']['id'] = (int)$arrRes['cityid'];
                $arrReturn['data']['long'] = $arrReturn['data']['lat'] = '';
            }else{
                $locate = false;
                $arrReturn['ret'] = -1;
                $arrReturn['sub'] = -1;
                $arrReturn['msg'] = '定位失败';
            }
        }catch (\Exception $ex){
            $locate = false;
            $arrReturn['ret'] = -1;
            $arrReturn['sub'] = -1;
            $arrReturn['msg'] = '定位失败';
        }
        //自有IP库失败 再走腾讯
        if(!$locate && !empty($arrInput['force'])){
            $arrReturn = $this->tencentIpLocate($arrInput);
        }

        return $arrReturn;
    }

    /*
     * 判断一个IP是否是国内IP
     */
    public function checkIpIsCn($arrInput)
    {
        $arrReturn = self::getStOut();
        $strIp = self::getParam($arrInput, 'ip');
        $iChannelId = self::getParam($arrInput,'channelId');
        //IP 取前三段
        $arrIp = explode('.',$strIp);
        if(isset($arrIp[3])){
            unset($arrIp[3]);
        }
        $strIp = implode('.',$arrIp);
        try {
            $arrRes = $this->model('Geo')->wyIpLocate($strIp, $iChannelId);
            if (isset($arrRes['iscn'])) {
                $arrReturn['data']['isCn'] = $arrRes['iscn'];
            }else{
                $arrReturn['ret'] = -1;
                $arrReturn['sub'] = -1;
                $arrReturn['msg'] = '检测失败';
            }
        }catch (\Exception $ex){
            $arrReturn['ret'] = -1;
            $arrReturn['sub'] = -1;
            $arrReturn['msg'] = '检测失败';
        }

        return $arrReturn;
    }



    /**
     * 使用腾讯的服务定位
     * @param   string ip   需要定位的IP地址
     * @return array
     */
    protected function tencentIpLocate(array $arrInput = [])
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['ip'] = self::getParam($arrInput, 'ip');
        $strTencentKeys = \wepiao::$config['params']['thirdKeys']['tencent']['tencent_lbs_keys'];
        // 随机选择一个key
        $k = array_rand($strTencentKeys);
        $strKey = $strTencentKeys[$k];
        $strTencentIpLocateURL = sprintf(\wepiao::$config['params']['thirdKeys']['tencent']['tencent_lbs_ip_locate_url'], $strKey, $arrSendParams['ip']);
        //调用接口
        $httpParams = [
            'arrData' => [],
            'sMethod' => 'GET',
            'iTryTimes' => 2,
        ];
        $arrRet = $this->http($strTencentIpLocateURL, $httpParams);
        //格式化腾讯定位接口返回数据
        $arrFormated = [];
        $arrReturn = self::getStOut();
        if (isset($arrRet['status']) && $arrRet['status'] == 0 && (!empty($arrRet['result']['ad_info']['province']) || !empty($arrRet['result']['ad_info']['city']))) {
            $arrFormated = static::_formatTencentIpLocate($arrRet);
            $arrFormated['ip'] = $arrSendParams['ip'];
            if (empty($arrFormated['id'])) {
                $arrReturn['data'] = $arrFormated;
                $arrReturn['ret'] = ERROR_RET_LOCATE_CITY_FIND_ERROR;
                $arrReturn['sub'] = ERROR_RET_LOCATE_CITY_FIND_ERROR;
                $arrReturn['msg'] = ERROR_MSG_LOCATE_CITY_FIND_ERROR;
            } else {
                $arrReturn['data'] = $arrFormated;
            }
        } else {
            $arrReturn['data']['ip'] = $arrSendParams['ip'];
            $arrReturn['ret'] = -1;
            $arrReturn['sub'] = -1;
            $arrReturn['msg'] = $arrRet['message'];
        }
        return $arrReturn;
    }

    /**
     * 格式化腾讯定位数据
     * @param $arrData
     * @return array
     */
    protected static function _formatTencentIpLocate($arrData)
    {
        $arrRet = array();
        $arrRet['recommend'] = $arrData['result']['ad_info']['province'] . $arrData['result']['ad_info']['city'];
        // 去除腾讯接口返回的城市名后的市字
        if (empty($arrData['result']['ad_info']['city'])) {
            $arrRet['name'] = preg_replace('/省$/', '', $arrData['result']['ad_info']['province']);
        } else {
            $arrRet['name'] = preg_replace('/市$/', '', $arrData['result']['ad_info']['city']);
        }
        $arrRet['long'] = $arrData['result']['location']['lng'];
        $arrRet['lat'] = $arrData['result']['location']['lat'];
        $id = self::pregWYCityId($arrRet['name']);
        if (!$id) return array();
        $arrRet['id'] = $id;
        return $arrRet;
    }

    /**
     * 使用百度的服务定位
     * @param   string ip   需要定位的IP地址
     * @return array
     */
    protected function baiduIpLocate(array $arrInput = [])
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['ip'] = self::getParam($arrInput, 'ip');
        $strBaiduKeys = \wepiao::$config['params']['thirdKeys']['baidu']['baidu_lbs_keys'];
        // 随机选择一个key
        $k = array_rand($strBaiduKeys);
        $strKey = $strBaiduKeys[$k];
        $strBaiduIpLocateURL = sprintf(\wepiao::$config['params']['thirdKeys']['baidu']['baidu_lbs_ip_locate_url'], $strKey, $arrSendParams['ip']);
        //调用接口
        $httpParams = [
            'arrData' => [],
            'sMethod' => 'GET',
            'iTryTimes' => 2,
        ];
        $arrRet = $this->http($strBaiduIpLocateURL, $httpParams);
        //格式化百度定位接口返回数据
        $arrFormated = [];
        $arrReturn = self::getStOut();
        $arrFormated['ip'] = $arrSendParams['ip'];
        if (isset($arrRet['address'])) {
            $arrFormated = static::_formatBaiduIpLocateData($arrRet);
            if (empty($arrFormated['id'])) {
                $arrReturn['data'] = $arrFormated;
                $arrReturn['ret'] = ERROR_RET_LOCATE_CITY_FIND_ERROR;
                $arrReturn['sub'] = ERROR_RET_LOCATE_CITY_FIND_ERROR;
                $arrReturn['msg'] = ERROR_MSG_LOCATE_CITY_FIND_ERROR;
            } else {
                $arrReturn['data'] = $arrFormated;
            }
        } else {
            $arrReturn['data'] = $arrFormated;
            $arrReturn['ret'] = -1;
            $arrReturn['sub'] = -1;
            $arrReturn['msg'] = $arrRet['message'];
        }
        return $arrReturn;

    }

    /**
     * 格式化
     * @access  格式化百度定位数据
     * @param $arrData
     * @return array
     */
    protected static function _formatBaiduIpLocateData($arrData)
    {
        $arrRet = array();
        $arrRet['recommend'] = $arrData['content']['address'];
        // 去除腾讯接口返回的城市名后的市字
        if (empty($arrData['content']['address_detail']['city'])) {
            $arrRet['name'] = preg_replace('/省$/', '', $arrData['content']['address_detail']['province']);
        } else {
            $arrRet['name'] = preg_replace('/市$/', '', $arrData['content']['address_detail']['city']);
        }
        $arrRet['long'] = $arrData['content']['point']['x'];
        $arrRet['lat'] = $arrData['content']['point']['y'];
        $id = self::pregWYCityId($arrRet['name']);
        if (!$id) return array();
        $arrRet['id'] = $id;
        return $arrRet;
    }

    /**
     * 根据经纬度精确定位
     * @note    该接口返回结果, 和Java返回的结果格式一样, 没有自己再封装, 另外该接口最多调用Java 1 秒钟
     * @params  int    channelId    渠道ID
     * @param   string latitude     纬度
     * @param   string longitude    经度
     * @return  array {"ret":0,"sub":0,"msg":"success","data":{"name":"\u5317\u4eac","recommend":
     *                "\u4e1c\u57ce\u533a\u5317\u4eac\u5e02\u653f\u5e9c(\u6b63\u4e49\u8def\u4e1c)","id":"10"}}
     */
    public function nearbyCity(array $arrInput = [])
    {
        //参数处理
        $arrSendParams = [];
        $arrReturn = self::getStOut();
        $arrSendParams['latitude'] = floatval(self::getParam($arrInput, 'latitude'));
        $arrSendParams['longitude'] = floatval(self::getParam($arrInput, 'longitude'));
        $iChannelId = self::getParam($arrInput,'channelId');
        if (empty($arrSendParams['latitude']) || empty($arrSendParams['longitude'])) {
            $arrReturn['ret'] = $arrReturn['sub'] = ERROR_RET_PARAM_EMPTY;
            $arrReturn['msg'] = ERROR_MSG_PARAM_EMPTY;
            return $arrReturn;
        }
        // 调用腾讯地图接口获取经纬度
        $arrRes = $this->getCityFromTencentLbs($arrSendParams['latitude'], $arrSendParams['longitude']);
        //处理返回结果
        // 如果调用出错，直接返回（调用出错时会在httpsend中组合出ret）
        if (isset($arrRes['ret'])) {
            $arrReturn['ret'] = $arrReturn['sub'] = ERROR_RET_LOCATE_TENCENT_INTERFACE_ERROR;
            $arrReturn['msg'] = ERROR_MSG_LOCATE_TENCENT_INTERFACE_ERROR;
            return $arrReturn;
        }
        //如果没有ret
        //没有ret,表示拿到的是腾讯接口的返回数据,而不是自己调用腾讯封装的错误ret码
        else {
            // 判断腾讯接口返回值
            if (isset($arrRes['status']) && $arrRes['status'] != 0) {
                $arrReturn['ret'] = $arrReturn['sub'] = ERROR_RET_LOCATE_TENCENT_INTERFACE_TYPE_ERROR;
                $arrReturn['msg'] = $arrRes['message'];
                return $arrReturn;
            }
        }
        //腾讯接口返回数据正常, 从中提取我们需要的城市信息
        $arrCityData = $this->ExtractCityDataFromTencentGeo($arrRes);
        $arrReturn['data'] = $arrCityData;
        if (!count($arrCityData)) {
            // 国外经纬度
            $arrReturn['ret'] = $arrReturn['sub'] = ERROR_RET_LOCATE_CITY_FIND_ERROR;
            $arrReturn['msg'] = ERROR_MSG_LOCATE_CITY_FIND_ERROR;
        }elseif(isset($arrReturn['data']['id'])){
            $arrBigData = ['latitude'=>$arrSendParams['latitude'],
                        'longitude'=>$arrSendParams['longitude'],
                        'channelId'=>$iChannelId,
                        'cityId'=>isset($arrReturn['data']['id'])?$arrReturn['data']['id']:'',
                        'cityName'=>isset($arrReturn['data']['name'])?$arrReturn['data']['name']:'',
                        'clientIp'=>\sdkService\helper\Net::getRemoteIp(),
            ];
            //$this->model('City')->insertBigDataRedisByIp($arrBigData);
        }
        return $arrReturn;
    }

    /**
     * 根据城市名查出城市id和recommend
     * @param
     * @return array
     */
    protected function ExtractCityDataFromTencentGeo(array $tentcentGeoInfo = [])
    {
        $arrCityInfo = $tentcentGeoInfo['result'];
        $data = array();
        // 如果是国外经纬度，则返回空数组
        if (!isset($arrCityInfo['address_component']['city'])) return array();
        // 去除腾讯接口返回的城市名后的市字
        if (!$arrCityInfo['address_component']['city']) {
            $data['name'] = preg_replace('/省$/', '', $arrCityInfo['address_component']['province']);
        } else {
            $data['name'] = preg_replace('/市$/', '', $arrCityInfo['address_component']['city']);
        }
        if (isset($arrCityInfo['formatted_addresses'])) {
            $data['recommend'] = $arrCityInfo['formatted_addresses']['recommend'];
        }
        //根据微影的城市ID对照表，找到相应城市ID
        $cities = file_get_contents(CGI_APP_PATH . 'app/config/WYcity.txt');
        preg_match('/^(\d+)(\s+)' . $data['name'] . '/m', $cities, $match);
        // 如果未匹配到城市，返回空数组
        if (!count($match)) {
            return array();
        }
        $data['id'] = $match[1];
        return $data;
    }



    // 调用腾讯地图接口，根据经纬度获取城市信息
    protected function getCityFromTencentLbs($lat, $lng)
    {
        // 获取腾讯LBS接口信息
        $lbs_url = \wepiao::$config['params']['thirdKeys']['tencent']['tencent_lbs_url'];
        $lbs_keys = \wepiao::$config['params']['thirdKeys']['tencent']['tencent_lbs_keys'];
        // 随机选择一个key
        $k = array_rand($lbs_keys);
        $lbs_key = $lbs_keys[$k];
        $query_params = array(
            'key' => $lbs_key,
            'location' => "$lat,$lng"
        );
        //调用接口, 最多发起重试次数为5
        $httpParams = [
            'arrData' => $query_params,
            'sMethod' => 'GET',
            'iTryTimes' => 5,
        ];
        $arrRet = $this->http($lbs_url, $httpParams);
        return $arrRet;
    }

    /**
     * 匹配城市
     * @param string $strOutCityName
     * @return bool
     */
    protected static function pregWYCityId($strOutCityName = '')
    {
        $cities = file_get_contents(CGI_APP_PATH . 'app/config/WYcity.txt');
        preg_match('/^(\d+)(\s+)' . $strOutCityName . '/m', $cities, $match);
        if (!count($match)) {
            return false;
        }
        return $match[1];
    }

    public function getWyCityId($arrInput = [])
    {
        $strCityName = $arrInput['cityName'];
        $strCityName = preg_replace('/省$/', '', $strCityName);
        $strCityName = preg_replace('/市$/', '', $strCityName);
        $arrReturn = self::getStOut();
        $iWyCityId = $this->pregWYCityId($strCityName);
        if($iWyCityId){
            $arrReturn['data']['cityId'] = $iWyCityId;
        }else{
            $arrReturn['ret'] = $arrReturn['sub'] = -1;
            $arrReturn['msg'] = 'preg WY cityId fail';
        }
        return $arrReturn;
    }

}