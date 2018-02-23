<?php
namespace sdkService\service;

/**
 *大促活动
 */
class serviceBigPromotion extends serviceBase{

    /**获取大促详细信息
     * @param $id  int    活动id 【必须】
     * @param $channelId  int  渠道id   【必须】
     * @return array
     */
    public function getBigPromotion(array $arrInput){
        //参数整理
        $arrReturn = self::getStOut();
        $id = self::getParam($arrInput, 'id');
        $channelId = self::getParam($arrInput, 'channelId');
        if( empty($channelId)){
            $arrReturn=self::getErrorOut(ERRORCODE_APPLY_ACTIVE_ERROR);
            return $arrReturn;
        }
        $arrData=$this->model('BigPromotion')->getBigPromotion($channelId,$id);
        $arrReturn['data'] = $arrData;
        return $arrReturn;
    }
}