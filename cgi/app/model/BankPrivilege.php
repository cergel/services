<?php

namespace sdkService\model;

class BankPrivilege extends BaseNew
{
    
    /**
     * 从 redis 读取影院排期 原始信息
     *
     * @param string $iChannelId 渠道编号
     * @param string $iCityId    城市编号
     * @param string $iCinemaId  影院编号
     *
     * @return array
     */
    public function readBankPrivilege($iChannelId, $iCityId)
    {
        return $this->readBankPrivilegeCommon($iChannelId, $iCityId);
        if ($this->isRestructChannel($iChannelId)) {
            $data = $this->readBankPrivilegeOnRestruct($iChannelId, $iCityId);
        }
        else {
            $data = $this->readBankPrivilegeOnCronTask($iChannelId, $iCityId);
        }
        
        return $this->helper('OutPut')->jsonConvert($data);
    }
    
    /**
     * 从原版CronTask读取正在上映影片信息，支持分页
     *
     * @param string $iChannelId 渠道编号
     *
     * @return array
     */
    private function readBankPrivilegeOnCronTask($iChannelId, $iCityId = '')
    {
        $return = '';
        if ( !empty( $iChannelId )) {
            $strKey = KEY_BANK_PRIVILEGE . '_' . rand(0, 99);
            $strData = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYget($strKey);
            $return = !empty( $strData ) ? $strData : '';
        }
        
        return $return;
    }
    
    /**
     * 从重构版CronTask读取正在上映影片信息
     *
     * @param string $iChannelId 渠道编号
     *
     * @return array
     */
    private function readBankPrivilegeOnRestruct($iChannelId, $iCityId = '')
    {
        $return = '';
        if ( !empty( $iChannelId )) {
            $strKey = KEY_BANK_PRIVILEGE . '_' . rand(0, 99);
            $strData = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYget($strKey);
            $return = !empty( $strData ) ? $strData : '';
        }
        
        return $return;
    }
    
    /**
     * 从原版CronTask读取正在上映影片信息，支持分页
     * readBankPrivilegeOnCronTask、readBankPrivilegeOnRestruct 的重构版, 后续 readBankPrivilegeOnCronTask 会删除
     * 重构版有进程内缓存,也就意味着, 多次调用,并不多次读取redis
     *
     * @param string $iChannelId 渠道编号
     *
     * @return array
     */
    private function readBankPrivilegeCommon($iChannelId, $iCityId = '')
    {
        //进程内缓存，缓存时间60s(或者一次请求结束释放)
        static $arrStaticData = ['data' => null, 'time' => 0];
        if (isset( $arrStaticData['data'][$iChannelId][$iCityId] ) && !is_null($arrStaticData['data'][$iChannelId][$iCityId]) && ( time() <= $arrStaticData['time'] )) {
            return $arrStaticData['data'][$iChannelId][$iCityId];
        }
        $return = ['dataList' => [], 'bankList' => []];
        if ( !empty( $iChannelId ) && !empty( $iCityId )) {
            $strKey = KEY_BANK_PRIVILEGE . '_v2_' . rand(0, 99);
            //只取当前城市以及不限城市的数据
            $arrSubKeys = ['0', $iCityId, 'bankList'];
            $arrData = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhMGet($strKey, $arrSubKeys);
            if ( !empty( $arrData )) {
                //去除空元素(因为hmget有这种取空元素的情况,原因在于某个子key可能不存在)
                foreach ($arrData as $key => $val) {
                    if (empty( $val )) {
                        continue;
                    }
                    $val = json_decode($val, true);
                    if ( !is_numeric($key) && ( $key == 'bankList' )) {
                        $return['bankList'] = $val;
                    }
                    else {
                        $return['dataList'][$key] = $val;
                    }
                }
            }
        }
        $arrStaticData['data'] = [];
        $arrStaticData['data'][$iChannelId][$iCityId] = $return;
        $arrStaticData['time'] = time() + 60;
        
        return $arrStaticData['data'][$iChannelId][$iCityId];
    }


    //新版获取银行卡优惠
    public function qryBankPrivilege($iCityId,$iChannelId)
    {
        $retData = [];
        $rand = mt_rand(1,10);
        $key = KEY_BANK_PRIVILEGE_NEW.$rand;
        $arrData = $this->redis($iChannelId,STATIC_MOVIE_DATA)->WYhMGet($key,[0,$iCityId]);
        if(!empty($arrData)){
            $retData = $arrData;
        }
        return $retData;
    }
    
    
}