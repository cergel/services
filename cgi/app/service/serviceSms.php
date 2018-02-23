<?php
/**
 * 短信、验证码相关
 */
namespace sdkService\service;

use sdkService\helper\Utils;

class serviceSms extends serviceBase
{
    /**
     * 发送短信验证码
     * @param array $arrInput
     * @return mixed
     */
    public function sendSmsCode($arrInput = [])
    {
        //参数处理
        $phoneNumber = self::getParam($arrInput, 'phoneNumber');
        if (empty($phoneNumber)) {
            $phoneNumber = self::getParam($arrInput, 'phone_number');
        }
        $data = [
            'phone_number' => $phoneNumber,    //手机号
            'msg_kind' => self::getParam($arrInput, 'msg_kind', 'auth'),    //非必须
            'client_id' => self::getParam($arrInput, 'client_id', 'app_login'),  //非必须
        ];

        $response = $this->model('sms', $arrInput)->sendSmsCode($data);
        return $response;
    }

    /**
     * 验证短信验证码
     * @param array $arrInput
     * @return mixed
     */
    public function verifySmsCode($arrInput = [])
    {
        //参数处理
        $phoneNumber = self::getParam($arrInput, 'phoneNumber');
        if (empty($phoneNumber)) {
            $phoneNumber = self::getParam($arrInput, 'phone_number');
        }
        $data = [
            'phone_number' => $phoneNumber,
            'code' => self::getParam($arrInput, 'code'),                    //短信验证码
            'publicSignalShort' => self::getParam($arrInput, 'publicSignalShort', $arrInput['channelId']),  //渠道ID
        ];

        $response = $this->model('sms', $arrInput)->verifySmsCode($data);
        return $response;
    }

    /**
     * 手机号加密和解密
     *
     * @param array $arrInput
     *
     * @return mixed
     */
    public function encryptPhone($arrInput = [])
    {
        $return = static::getStOut();
        $strPhone = self::getParam($arrInput, 'phoneNumber');
        $iChannelId = self::getParam($arrInput, 'channelId');
        if (empty($strPhone) || empty($iChannelId)) {
            //获取座位图失败的情况
            $return['ret'] = $return['sub'] = '-22';
            $return['msg'] = 'failure';

            return $return;
        }
        $return['data']['token'] = Utils::encryptStr($strPhone, '57a2b89c66389');

        return $return;
    }

    /**
     * 手机号加密和解密
     *
     * @param array $arrInput
     *
     * @return mixed
     */
    public function decryptPhone($arrInput = [])
    {
        $return = static::getStOut();
        $strToken = self::getParam($arrInput, 'token');
        $iChannelId = self::getParam($arrInput, 'channelId');
        if (empty($strToken) || empty($iChannelId)) {
            //获取座位图失败的情况
            $return['ret'] = $return['sub'] = '-23';
            $return['msg'] = 'failure';

            return $return;
        }
        $return['data']['phone'] = Utils::decryptStr($strToken, '57a2b89c66389');

        return $return;
    }

    /**
     * 内部发送短信接口
     *
     * @param array $smsArr
     * @param bool $isUrlEncode 是否要对 $smsArr['content']进行urlencode
     * @return array|bool|mixed
     */
    public function sendInnerSms($smsArr = [])
    {
        $arrHttpSend = [];
        $arrHttpSend['phone_numbers'] = $smsArr['phone_numbers'];
        $arrHttpSend['content'] = urlencode($smsArr['content']);
        //因为PC等渠道的短信也是调用的APP的短信接口，所以需要判断msg_kind参数，做兼容处理
        $arrHttpSend['msg_kind'] = 'auth';
        $arrHttpSend['client_id'] = 'app_login';
        $params['sMethod'] = 'post';
        $params['arrData'] = $arrHttpSend;

        $data = $this->http(SDK_SMS_SEND, $params);
        return $data;
    }

}
