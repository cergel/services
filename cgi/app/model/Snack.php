<?php
/**
 * Created by PhpStorm.
 * User: panyuanxin
 * Date: 16/8/11
 * Time: 下午4:22
 */

namespace sdkService\model;


class Snack extends BaseNew
{
    /**
     * 格式化小吃列表以及优惠信息
     * @param array $snackList
     * @param array $discountList
     */
    public function formatSnackDiscount(&$snackList = [], $discountList = [])
    {
        //按照snackId为key构建小吃列表
        $snackListIndex = [];
        foreach ($snackList as $value) {
            $snackListIndex[$value['snackId']] = $value;
        }
        //遍历小吃优惠列表判断小吃列表是否包含相应的优惠
        foreach ($discountList as $key => &$discountItem) {
            foreach ($discountItem['gdsInfoList'] as $value) {
                if (array_key_exists($value['gdsId'], $snackListIndex)) {
                    //将卖品优惠信息拼接到小吃列表之上
                    $snackListIndex[$value['gdsId']]['discount'] = $discountItem;
                }
            }
        }
        foreach ($snackListIndex as &$value) {
            if (empty($value['discount'])) {
                $value['discount'] = new \stdClass();
            } else {
                foreach ($value['discount']['gdsInfoList'] as $gdsKey => &$gdsDis) {
                    if ($value['snackId'] == $gdsDis['gdsId']) {
                        $value['discount']['gdsInfo'] = $gdsDis;
                    }
                }
                unset($value['discount']['gdsInfoList']);
            }
        }
        $snackList = array_values($snackListIndex);
    }

    /**
     * 格式化小吃列表以及优惠信息
     * @param array $snackList
     * @param array $discountList  暂不需要
     */
    public function formatSnackDiscountV2(&$snackList = [],$suitNumber='')
    {
        //按照snackId为key构建小吃列表
        $disSnack=$suitSnack=$commonSnack=$finalSnack=false;
        $suitSnackPrice=$commonSnackPrice=0;
        $snackListIndex = [];
        foreach ($snackList as $value) {
            $snackListIndex[$value['snackId']] = $value;
        }
        $i=0;
        foreach ($snackListIndex as $key=>&$value) {
            //如果存在加购价
            if(isset ($value['discount']['gdsInfo']) ) {
                $disSnack = $i;
            }
            //选择适配座位最便宜的那个
            if($suitNumber!='' && $value['suitableNumber']==$suitNumber){
                if($suitSnack===false){
                    $suitSnackPrice=$value['sellPrice'];
                    $suitSnack=$i;
                }
                else{
                    if($value['sellPrice']<$suitSnackPrice){
                        $suitSnack=$i;
                    }
                }
            }
            //选择通用最便宜的那个
            if($commonSnack===false){
                $commonSnackPrice=$value['sellPrice'];
                $commonSnack=$i;
            }
            else{
                if($value['sellPrice']<$commonSnackPrice){
                    $commonSnackPrice=$value['sellPrice'];
                    $commonSnack=$i;
                }
            }
            $i++;
        }
        //var_dump($disSnack,$suitSnack,$commonSnack);
        /*
        若有加价购套餐，优先选择加价购套餐。
        若锁座单个则推荐价格最低的单人套餐，若锁座双数则推荐价格最低的双人套餐。
        选择三个座位以上，推荐家庭套餐。
        若该影院不区分单人双人套餐，则按照目前规则，默认选择价格最低的套餐。
        */
        if($disSnack!==false){
            $finalSnack=$disSnack;
        }
        else{
            if($suitSnack!==false){
                $finalSnack=$suitSnack;
            }
            else{
                $finalSnack=$commonSnack;
            }
        }
        $snackList = array_values($snackListIndex);
        $recommand = ($finalSnack===false)? false:$finalSnack;
        return $recommand;
    }
}