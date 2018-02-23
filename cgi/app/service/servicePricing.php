<?php
namespace sdkService\service;

/**
 * Class servicePricing
 * @package sdkService\service
 * 定价相关
 */
class servicePricing extends serviceBase
{

    /*
     * $arrInput = [
     *  channelId
     *  cityId
     *  cinemaId
     *  mpId
     *  priceDiff
     * ]
     * 排期的定价如果变化了，java会调用此接口来更新
     * 逻辑：
     * 1.实时去redis读取 该影院的该排期，然后增加定价标识
     *    只要通知过来了 都要增加price_status为1，如果priceDiff为负数还要要把定价减去此值
     *
     * 2.要在redis里面增加一个存储结构来记录该价格，crontask生成排期的时候要用，否则生成排期的时候会被覆盖定价
     */

    public function notify($arrInput = [])
    {
        $iChannelId = $arrInput['channelId'];
        $iCinemaId = $arrInput['cinemaId'];
        $iMpId = $arrInput['mpId'];
        $iPriceDiff = $arrInput['priceDiff'];
        $iCityId = $arrInput['cityId'];
        $arrData = $this->model('sche')->readCinemaSche($iChannelId, $iCityId, $iCinemaId);
        if(!empty($arrData)) {
            $this->_formatSche($arrData, $iMpId, $iPriceDiff);
            $this->model('sche')->saveCinemaSche($iChannelId, $iCityId, $iCinemaId,$arrData);
            //扔出一个结构记录该信息
            $this->model('sche')->savePriceInfo($iChannelId,$iCityId,$iCinemaId,$iMpId,$iPriceDiff);

        }
        $return = self::getStOut();
        return $return;


    }


    private function _formatSche(&$arrSche = [],$iMpId,$iPriceDiff)
    {
        foreach($arrSche as &$arrSingleMovie){
            foreach ($arrSingleMovie['sche'] as $date => &$arrDateSche) {
                foreach ($arrDateSche as &$arrValue) {
                    foreach ($arrValue['seat_info'] as &$arrSeats) {
                        if($arrSeats['is_discount'] == 0) {//没有优惠才可能有定价
                            if ($arrSeats['mpid'] == $iMpId) {
                                $arrSeats['price_status'] = 1;
                                if ($iPriceDiff < 0) {
                                    $arrSeats['calculate_price'] = strval(($arrSeats['price']*100 + $iPriceDiff)/100);
                                }else{
                                    if($arrSeats['price'] != $arrSeats['calculate_price']){
                                        $arrSeats['calculate_price'] = strval($arrSeats['price']);
                                    }
                                }
                            }
                        }
                    }
                }

            }
        }
    }
}