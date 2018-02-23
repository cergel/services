<?php
/**
 * create by bbq
 * 退改签手续费
 */

namespace sdkService\service;

class serviceChangesFee extends serviceBase
{

    /**
     * 影院退改签手续费规则
     *
     * @param   string  cinemaNo    影院编号
     * @param   string  channelId   渠道编号
     *
     * @return array
     */
    public function getChangesFeeByCinemaNo(array $arrInput = [])
    {
        //参数整理
        $cinemaNo = self::getParam($arrInput, 'cinemaNo');
        $iChannelId = self::getParam($arrInput, 'channelId');
        $return = self::getStOut();
        if (empty($cinemaNo)) {
            return $return;
        }
        //获取影院详情
        $httpParams = [
            'arrData'   => ['cinemaNo'=>$cinemaNo],
            'sMethod'   => 'GET',
            'iTimeout'  => 2,
            'iTryTimes' => 1,
        ];
        $return = $this->http(JAVA_API_CORE_CENTER_CHANGESFEE, $httpParams);
        return $return;
    }

    /**
     * 影院退改签当前费用
     *
     * @param   int  cinemaNo    影院编号
     * @param   int  channelId   渠道编号
     * @param   string  showDate   开场时间
     * @return array
     */
    public function getCurrentChangesFee(array $arrInput = [])
    {
        //参数整理
        $cinemaNo = self::getParam($arrInput, 'cinemaNo');
        $showDate = self::getParam($arrInput, 'showDate');
        $iChannelId = self::getParam($arrInput, 'channelId');
        $return = self::getStOut();
        if (empty($cinemaNo) || empty($showDate)) {
            return $return;
        }
        //获取影院详情
        $httpParams = [
            'arrData'   => ['cinemaNo'=>$cinemaNo  , 'showDate'=>$showDate ],
            'sMethod'   => 'GET',
            'iTimeout'  => 2,
            'iTryTimes' => 1,
        ];
        $return = $this->http(JAVA_API_CORE_CENTER_CHANGESFEE_CALC, $httpParams);
        return $return;
    }
}