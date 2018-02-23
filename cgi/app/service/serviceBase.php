<?php

namespace sdkService\service;

use sdkService\helper;
use sdkService\model\Limit;
use sdkService\model\BankPrivilege;
use sdkService\model\Movie;
use sdkService\model\RedSpot;
use sdkService\base\Base;
use sdkService\model\SmallRoutine;

/**
 * Created by PhpStorm.
 * User: syj
 * Date: 16/3/10
 * Time: 下午2:52
 */
class serviceBase extends Base
{
    
    
    private $model = [];
    private $service = [];
    
    public $arrBanOfLevelDownFun = [];
    
    /*
     * 检测限制
     */
    public function limitCheck($strServiceName = '', $strActionName = '', $arrParams = [])
    {
        $iChannelId = !empty($arrParams['channelId']) ? $arrParams['channelId'] : '';
        $strKey = strtolower($strServiceName) . '_' . strtolower($strActionName);
        $return = [];
        //先检测IP黑名单
        $ipBlackLimitRes = $this->ipBlackLimit($iChannelId, $strKey);
        if (empty($ipBlackLimitRes)) {
            //检测IP黑名单通过，再检测次数限制
            $limitTimesCheckRes = $this->limitTimesCheck($iChannelId, $strKey);
            if ( !empty($limitTimesCheckRes)) {
                $return = $limitTimesCheckRes;
            }
        } else {
            $return = $ipBlackLimitRes;
        }
        
        return $return;
    }
    
    /**
     * 检测IP黑名单
     * 操作redis1次
     *
     * @param string $iChannelId
     * @param string $strKey
     *
     * @return array
     */
    public function ipBlackLimit($iChannelId = '', $strKey = '')
    {
        $return = [];
        //获取配置
        $config = !empty(\wepiao::$config['blackip']) ? \wepiao::$config['blackip'] : [];
        //融合配置
        $apiConfig = !empty($config[$strKey]) ? $config[$strKey] : [];
        $allConfig = !empty($config['all']) ? $config['all'] : [];
        $mergeConfig = array_merge($allConfig, $apiConfig);
        if (empty($mergeConfig) || ( !empty($mergeConfig['channelIds']) && !in_array($iChannelId, $mergeConfig['channelIds']))) {
            return $return;
        }
        //查询是否受限
        $limitClass = '\sdkService\service\serviceLimit';
        $objClass = new $limitClass();
        $res = $objClass->checkIfInIpBlack(['channelId' => $iChannelId, 'ip' => helper\Net::getRemoteIp()]);
        if ( !empty($res) && isset($res['ret']) && !empty($res['data'])) {
            if ( !empty($mergeConfig['interuptContinue'])) {
                $errorcode = !empty($mergeConfig['return']['errorcode']) ? $mergeConfig['return']['errorcode'] : '';
                if ( !empty($errorcode)) {
                    $return = $this->getErrorOut($errorcode);
                    $data = !empty($mergeConfig['return']['res']) ? $mergeConfig['return']['res'] : [];
                    $return = array_merge($return, $data);
                }
            } //如果不中断执行（有些场景下，我们希望在IP黑名单的话，不直接返回错误）
            else {
                \wepiao::$config['isInIpBlack'] = 1;
            }
        }
        
        return $return;
    }
    
    /**
     * 检测接口限制次数
     * 如果规则符合，会操作1-2次redis（2次的情况，是规则符合，但还没到上限）
     *
     * @param string $strServiceName
     * @param string $strActionName
     * @param array  $arrParams
     *
     * @return array 只要收到限制，返回值就不为空
     */
    public function limitTimesCheck($iChannelId = '', $strKey = '')
    {
        $return = [];
        //获取配置
        $config = !empty(\wepiao::$config['limit']) ? \wepiao::$config['limit'] : [];
        //融合配置
        $apiConfig = !empty($config[$strKey]) ? $config[$strKey] : [];
        $allConfig = !empty($config['all']) ? $config['all'] : [];
        $mergeConfig = array_merge($allConfig, $apiConfig);
        if (empty($mergeConfig) || ( !empty($mergeConfig['channelIds']) && !in_array($iChannelId, $mergeConfig['channelIds']))) {
            return $return;
        }
        /**
         * 查询限制
         */
        $limitClass = '\sdkService\service\serviceLimit';
        $objClass = new $limitClass();
        //设置规则
        $rules = [];
        foreach ($mergeConfig['rules'] as $key => $value) {
            $value['businessId'] = $strKey;
            if ($key == 'ip') {
                $strIp = helper\Net::getRemoteIp();
                if ( !empty($strIp)) {
                    $rules[$strIp] = $value;
                }
            } elseif ($key == 'openid') {
                //查询用户openid
                /*$userClass = '\sdkService\service\serviceUser';
                $strOpenId = (new $userClass)->getOpenIdFromCookie();*/
                $strOpenId = !empty($arrParams['openId']) ? $arrParams['openId'] : '';
                if ( !empty($strOpenId)) {
                    $rules[$strOpenId] = $value;
                }
            }
        }
        $res = $objClass->commonLimit(['channelId' => $iChannelId, 'config' => $rules]);
        if ( !empty($res) && isset($res['ret']) && $res['ret'] != 0) {
            $errorcode = !empty($mergeConfig['return']['errorcode']) ? $mergeConfig['return']['errorcode'] : '';
            if ( !empty($errorcode)) {
                $return = $this->getErrorOut($errorcode);
                $data = !empty($config['return']['res']) ? $config['return']['res'] : [];
                $return = array_merge($return, $data);
            }
        }
        
        //判断是否有IP限制
        return $return;
    }

    /**
     * @param  string $strClass
     * @param string $mixParams
     *
     * @return RedSpot|Limit|Movie|BankPrivilege|SmallRoutine
     */
    public function model($strClass, $mixParams = '')
    {
        $strClass = ucfirst($strClass);
        $class = 'sdkService\model\\' . $strClass;
        if (empty($this->model[$class])) {
            if ( !empty($mixParams)) {
                $this->model[$class] = new $class($mixParams);
            } else {
                $this->model[$class] = new $class;
            }
        }
        
        return $this->model[$class];
    }
    
    /**
     * @param $strClass
     *
     * @return serviceMovie|servicePee|serviceSearch|serviceGoods|serviceBonus
     */
    public function service($strClass)
    {
        $strClass = ucfirst($strClass);
        $class = 'sdkService\service\service' . $strClass;
        if (empty($this->service[$class])) {
            $this->service[$class] = new $class;
        }
        
        return $this->service[$class];
    }
    
    
    /**
     * 获取标准的返回数据
     *
     * @return array
     */
    public static function getStOut()
    {
        return ['ret' => 0, 'sub' => 0, 'msg' => 'success', 'data' => []];
    }
    
    /**
     * 传入一个error的值，比如是 '0100001001'  (字符串类型)
     * 这个值一般都是定义为常量的
     */
    public static function getErrorOut($code)
    {
        $arrConf = \wepiao::$config;
        $codeMap = $arrConf['params']['errorCode'];
        $code = str_pad($code, 10, '0', STR_PAD_LEFT);//如果不满10位，左侧补0
        $retCode = substr($code, 0, 2);
        $moduleCode = substr($code, 2, 4);
        $errorCode = substr($code, 6, 4);
        if (isset($codeMap[$retCode][$moduleCode][$errorCode])) {
            return [
                'ret' => $retCode,
                'sub' => $moduleCode . $errorCode,
                'msg' => $codeMap[$retCode][$moduleCode][$errorCode]['userMsg'],
            ];
        } else {
            throw new \Exception('error code:' . $code . ' not declare');
        }
    }
    
    
    /**
     * 从cookie取spm
     */
    public function getSpmFromCookie()
    {
        return !empty($_COOKIE['_wepiao_spm']) ? $_COOKIE['_wepiao_spm'] : '';
    }
    
}