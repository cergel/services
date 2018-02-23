<?php
namespace sdkService\service;


class serviceBankPrivilege extends serviceBase
{
    
    /**
     * 获取银行优惠活动
     * 需要注意这里的逻辑。因为后台创建活动的时候，可能的类型比较多，所以这里需要组合判断，拿到所有的可能性
     *
     * @param string cinemaId   影院id
     * @param string movieId    影片id
     * @param string cityId     城市id
     * @param string channelId  渠道id
     *
     * @return array
     */
    public function readBankPrivilege($arrInput = [])
    {
        $arrReturn = static::getStOut();
        $arrReturn['data']['list'] = [];
        //参数处理
        $iCinemaId = self::getParam($arrInput, 'cinemaId');
        $iMovieId = self::getParam($arrInput, 'movieId');
        $iCityId = self::getParam($arrInput, 'cityId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        //数据处理
        if ( !empty( $iChannelId ) && !empty( $iCinemaId ) && !empty( $iMovieId ) && !empty( $iCityId )) {
            //读取银行信息
            $arrData = $this->model('BankPrivilege')->readBankPrivilege($iChannelId, $iCityId);
            if ( !empty( $arrData['dataList'] ) && is_array($arrData['dataList']) && !empty( $arrData['bankList'] )) {
                $arrReturn['data']['list'] = [];
                //组合各种可能的情况
                $arrData1 = $arrData2 = $arrData3 = $arrData4 = $arrData5 = $arrData6 = $arrData7 = $arrData8 = [];
                //1、判断不限地区、不限影院、不限影片的情况（第一层是城市，第二层是影院，第三层是影片）
                $arrData1 = !empty( $arrData['dataList'][0][0][0] ) ? $arrData['dataList'][0][0][0] : [];
                //2、判断不限影片的情况
                $arrData2 = !empty( $arrData['dataList'][$iCityId][$iCinemaId][0] ) ? $arrData['dataList'][$iCityId][$iCinemaId][0] : [];
                //3、判断不限影院的情况
                $arrData3 = !empty( $arrData['dataList'][$iCityId][0][$iMovieId] ) ? $arrData['dataList'][$iCityId][0][$iMovieId] : [];
                //4、判断不限城市的情况
                $arrData4 = !empty( $arrData['dataList'][0][$iCinemaId][$iMovieId] ) ? $arrData['dataList'][0][$iCinemaId][$iMovieId] : [];
                //5、不限城市、不限影院、限影片
                $arrData5 = !empty( $arrData['dataList'][0][0][$iMovieId] ) ? $arrData['dataList'][0][0][$iMovieId] : [];
                //6、不限城市、限影院、不限影片
                $arrData6 = !empty( $arrData['dataList'][0][$iCinemaId][0] ) ? $arrData['dataList'][0][$iCinemaId][0] : [];
                //7、限城市、不限影院、不限影片
                $arrData7 = !empty( $arrData['dataList'][$iCityId][0][0] ) ? $arrData['dataList'][$iCityId][0][0] : [];
                //8、正常情况
                $arrData8 = !empty( $arrData['dataList'][$iCityId][$iCinemaId][$iMovieId] ) ? $arrData['dataList'][$iCityId][$iCinemaId][$iMovieId] : [];
                //合并有效数据
                $arrAllPrivileges = $arrData1 + $arrData2 + $arrData3 + $arrData4 + $arrData5 + $arrData6 + $arrData7 + $arrData8;
                //排序
                $arrSortBankSort = [];
                $arrSortCreateTime = [];
                $arrAllPrivilegeIds = array_keys($arrAllPrivileges);
                foreach ($arrAllPrivileges as $arrSortInfo) {
                    $arrSortBankSort[] = !empty( $arrSortInfo['bank_sort'] ) ? $arrSortInfo['bank_sort'] : 0;
                    $arrSortCreateTime[] = !empty( $arrSortInfo['create_time'] ) ? $arrSortInfo['create_time'] : 0;
                }
                if ( !empty( $arrSortBankSort ) && !empty( $arrSortCreateTime ) && !empty( $arrAllPrivilegeIds )) {
                    array_multisort($arrSortBankSort, SORT_DESC, $arrSortCreateTime, SORT_DESC, $arrAllPrivilegeIds);
                }
                //最后整合数据
                $arrPrivilegeInfos = [];
                foreach ($arrAllPrivilegeIds as $iBankId) {
                    if ( !empty( $arrData['bankList'][$iBankId] )) {
                        $arrPrivilegeInfos[] = $arrData['bankList'][$iBankId];
                    }
                }
                $arrReturn['data']['list'] = $arrPrivilegeInfos;
            }
        }
        
        return $arrReturn;
    }

    //V2版查询银行卡优惠活动，只有城市维度
    //channelId,cityId
    public function qryBankPrivilegeV2($arrInput = [])
    {
        $arrReturn = static::getStOut();
        $retData = [];
        $iCityId = self::getParam($arrInput,'cityId');
        $iChannelId = self::getParam($arrInput,'channelId');
        $arrData = $this->model('BankPrivilege')->qryBankPrivilege($iCityId,$iChannelId);
        if(empty($arrData[$iCityId])) {
            if (!empty($arrData[0])) {
                $retData = json_decode($arrData[0], 1);
            }
        }else{//合并单个城市到0
            if(empty($arrData[0])){
                $retData = json_decode($arrData[$iCityId],1);
            }else{
                $arrBanksort = [];
                $arrCreatetime = [];
                $arrCity = json_decode($arrData[$iCityId],1);
                $arrAll = json_decode($arrData[0],1);
                $retData = array_merge($arrAll,$arrCity);
                foreach($retData as $arr){
                    $arrBanksort[] = $arr['bank_sort'];
                    $arrCreatetime[] = $arr['create_time'];
                }
                array_multisort($arrBanksort,SORT_DESC,$arrCreatetime,SORT_DESC,$retData);
                //array_multisort($retData,SORT_DESC,$arrBanksort,SORT_DESC,$arrCreatetime);
            }

        }

        $arrReturn['data'] = $retData;
        return $arrReturn;

    }
    
    
}