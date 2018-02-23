<?php
namespace sdkService\service;


class serviceTask extends serviceBase
{


    public function discount($arrInput = [])
    {
        $arrReturn = self::getStOut();
        $arrSendParams = array();
        $strOpenId = $arrInput['openId'];
        $strchanId = $arrInput['channelId'];
        if (!empty($strOpenId)) {
            $arrSendParams['id'] = $strOpenId;
            $arrSendParams['chanId'] = $strchanId;
        } else {
            $arrReturn = self::getErrorOut(ERRORCODE_COMMON_PARAMS_ERROR);
            return $arrReturn;
        }
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
            'iTryTimes' => 1,
        ];
        $arrRet = $this->http(JAVA_API_TASK_DISCOUNT, $httpParams);
        if ($arrRet['ret'] == 0 && $arrRet['sub'] == 0) {
            $arrReturn = $arrRet;
        } else {
            $arrReturn['ret'] = $arrRet['ret'];
            $arrReturn['sub'] = $arrRet['sub'];
            $arrReturn['msg'] = $arrRet['msg'];
        }
        return $arrReturn;
    }
}