<?php
namespace sdkService\service;



class serviceSnack extends serviceBase
{

    /**
     * 此方法和 getSnackDiscountList 比, 没有优惠内容, 基本上此方法已不再使用
     * @param string cinemaNo       影院id
     * @param string suitableNumber 几人套餐，如果穿2表示用户优惠获取2人套餐的小吃列表
     *
     * @return array|bool|mixed
     */
    public function getCinemaSnacks($arrInput = [])
    {
        //参数处理
        $arrSendParams = [];
        $arrSendParams['cinemaNo'] = self::getParam($arrInput, 'cinemaNo');
        $arrSendParams['suitableNumber'] = self::getParam($arrInput, 'suitableNumber');

        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'GET',
        ];
        $arrReturn = $this->http(JAVA_ADDR_SNACK_LIST, $httpParams);
        return $arrReturn;
    }

    /**
     *
     * @param string cinemaNo       影院id
     * @param string suitableNumber 几人套餐，如果传2表示用户优惠获取2人套餐的小吃列表
     * @param string orderId 临时订单号
     * @param int tktCnt 购票张数
     * @param int mpId 排期ID
     * @param string openId 用户唯一标识
     * @param string channelId 用户渠道ID
     */
    public function getSnackDiscountList($arrInput = [])
    {
        $return = self::getStOut();
        //获取影院原本的小吃列表
        $arrSendParams = [];
        $arrSendParams['cinemaNo'] = self::getParam($arrInput, 'cinemaNo');
        $arrSendParams['suitableNumber'] = self::getParam($arrInput, 'suitableNumber');
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'GET',
        ];
        $snackListReturn = $this->http(JAVA_ADDR_SNACK_LIST, $httpParams);
        if ($snackListReturn['ret'] == 0) {
            $snackList = $snackListReturn['data'];
        } else {
            $snackList = [];
        }
        //如果没设置排期、订单号、用户信息则不走优惠逻辑
        if (empty($arrInput['mpId']) || empty($arrInput['openId']) || empty($arrInput['orderId'])) {
            $discountList = [];
        } else {
            $discountList = [];
            //获取对应的小吃优惠信息
            $arrSendParams = [];
            $arrSendParams['cinId'] = self::getParam($arrInput, 'cinemaNo');
            $arrSendParams['ordId'] = self::getParam($arrInput, 'orderId');
            $arrSendParams['tktCnt'] = self::getParam($arrInput, 'suitableNumber');
            $arrSendParams['chanId'] = self::getParam($arrInput, 'channelId');
            $arrSendParams['mpId'] = self::getParam($arrInput, 'mpId');
            $arrSendParams['suin'] = self::getParam($arrInput, 'openId');
            $httpParams = [
                'arrData' => $arrSendParams,
                'sMethod' => 'POST',
                'sendType' => 'json',
            ];
            $snackDiscountListReturn = $this->http(JAVA_API_SNACK_DISCOUNT, $httpParams);
            //匹配相关优惠信息
            if ($snackDiscountListReturn['ret'] == 0 && $snackDiscountListReturn['sub'] == 0) {
                $discountList = $snackDiscountListReturn['data']['resList'];
            }
        }

        $this->model("Snack")->formatSnackDiscount($snackList, $discountList);
        $return['data'] = $snackList;
        return $return;
    }


    /**
     *
     * @param string cinemaNo       影院id
     * @param string suitableNumber 几人套餐，如果传2表示用户优惠获取2人套餐的小吃列表
     * @param string orderId 临时订单号
     * @param int tktCnt 购票张数
     * @param int mpId 排期ID
     * @param string openId 用户唯一标识
     * @param string channelId 用户渠道ID
     */
    public function getSnackDiscountListV2($arrInput = [])
    {
        $return = self::getStOut();
        //获取影院原本的小吃列表
        $arrSendParams = [];
        $arrSendParams['cinemaNo'] = self::getParam($arrInput, 'cinemaNo');
        $arrSendParams['suitableNumber'] = $suitNumber = self::getParam($arrInput, 'suitableNumber');
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'GET',
        ];
        $snackListReturn = $this->http(JAVA_ADDR_SNACK_LIST, $httpParams);
        if ($snackListReturn['ret'] == 0) {
            $snackList = $snackListReturn['data'];
        } else {
            $snackList = [];
        }
//        $arrInput['openId'] = 'MzY0Mjc2ODAyRkY1OTQxMjgzQjU4RUU5QkVCMEM5MUQxNDk5ODQ1MzM4bzBhVC1kMnBncU9xWXg1M0pFOVROTlFnTGdoYw==';
        //如果没设置排期、订单号、用户信息则不走优惠逻辑
        if (!empty($arrInput['mpId']) && !empty($arrInput['openId']) && !empty($arrInput['orderId'])) {
            //获取对应的小吃优惠信息
            $arrSendParams = [];
            $arrSendParams['cinId'] = self::getParam($arrInput, 'cinemaNo');
            $arrSendParams['ordId'] = self::getParam($arrInput, 'orderId');
            $arrSendParams['tktCnt'] = self::getParam($arrInput, 'suitableNumber');
            $arrSendParams['chanId'] = self::getParam($arrInput, 'channelId');
            $arrSendParams['mpId'] = self::getParam($arrInput, 'mpId');
            $arrSendParams['suin'] = self::getParam($arrInput, 'openId');
            $arrSendParams['cinGdsList'] = $snackList;
            $httpParams = [
                'arrData' => $arrSendParams,
                'sMethod' => 'POST',
                'sendType' => 'json',
            ];
            //返回处理附带优惠后的小吃列表
            $snackDiscountListReturn = $this->http(JAVA_API_SNACK_DISCOUNT, $httpParams);
            if ($snackDiscountListReturn['ret'] == 0 && $snackDiscountListReturn['sub'] == 0) {
                $snackList = $snackDiscountListReturn['data']['gdsList'];
            }
        }
        $finalSnack=$this->model("Snack")->formatSnackDiscountV2($snackList,$suitNumber);
        $return['data'] = array(
            'recommended'=>$finalSnack,
            'snackList'=>$snackList,
        );
        return $return;
    }


}