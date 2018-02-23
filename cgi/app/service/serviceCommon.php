<?php
namespace sdkService\service;

use sdkService\helper\Utils;


class serviceCommon extends serviceBase
{

    private $strHash = 'WxOpenId';
    private $iExpire = 3600;

    /**
     * @param $arrInput str 要加密的字符串
     * @param $arrInput t   失效时间
     * @param $arrInput channelId   渠道编号
     * @return string
     */
    //加密字符串
    public function encrypt($arrInput)
    {
        $str = $arrInput['str'];
        $iTime = !empty($arrInput['t']) ? $arrInput['t'] : 0;
        $strSecret = $this->getSecret($arrInput['channelId']);
        $t = $iTime ? $iTime : time();
        $strSign = Utils::makeSign(array('openid' => $str, 't' => $t), $strSecret . $this->strHash);
        $strEncrypt = base64_encode($strSign . $t . $str);
        $arrReturn = self::getStOut();
        $arrReturn['data'] = ['encryptStr' => $strEncrypt];
        return $arrReturn;
    }

    /**
     * @param $arrInput str         要解密的字符串
     * @param $arrInput channelId   渠道编号
     * @param $arrInput ignoreWx  如果为1，忽略对wx.wepiao.com域名的校验
     * @return bool|string
     */
    public function decrypt($arrInput)
    {
        $arrReturn = self::getStOut();

        $strEncrypt = $arrInput['str'];
        $strDecode = base64_decode($strEncrypt);
        if (!$strDecode) {
            $arrReturn['ret'] = ERROR_RET_DECRYPT;
            $arrReturn['sub'] = ERROR_RET_DECRYPT;
            $arrReturn['msg'] = ERROR_MSG_DECRYPT;
            return $arrReturn;
        }

        $strSign = substr($strDecode, 0, 32);
        $t = (int)substr($strDecode, 32, 10);
        $str = substr($strDecode, 42);

        //验证时间
        if (time() - $t > $this->iExpire) {
            $arrReturn['ret'] = ERROR_RET_DECRYPT;
            $arrReturn['sub'] = ERROR_RET_DECRYPT;
            $arrReturn['msg'] = ERROR_MSG_DECRYPT;
            return $arrReturn;
        }
        //end

        //增加一行判断，当有ignoreWx为1，并且渠道为3的时候，使域名强制变成 wx.wepiao.com
        $secretHost = ($arrInput['channelId']==3 && !empty($arrInput['ignoreWx'])) ? "wx.wepiao.com" : "";
        $strSecret = self::getSecret($arrInput['channelId'],$secretHost);
        $strNewSign = Utils::makeSign(array('openid' => $str, 't' => $t), $strSecret . $this->strHash);

        if ($strNewSign == $strSign) {
            $arrReturn['data'] = ['decryptStr' => $str];
        } else {
            $arrReturn['ret'] = ERROR_RET_DECRYPT;
            $arrReturn['sub'] = ERROR_RET_DECRYPT;
            $arrReturn['msg'] = ERROR_MSG_DECRYPT;
        }
        return $arrReturn;
    }

    public function getSecret($iChannelId = 0,$strHost='')
    {
        $arrValiadHost = [
            'wx.wepiao.com',
            'pre.wxapi.wepiao.com',
            'dev.wxapi.wepiao.com',
            'wxapi.wepiao.com',
            'wxapi-pre.wepiao.com',
            'wxapi-dev.wepiao.com',
            'wx-api.wepiao.com',
            'dev-wx-api.wepiao.com',
            'pre-wx-api.wepiao.com',
            'pre-app-api.wepiao.com',
            'app-api.wepiao.com',
            'wx-api-dev.wepiao.com',
        ];
        $arrSecret = \wepiao::$config['params']['secret']['encrypt'];
        //为了保护电影票，即使传了3也要判断域名
        if($iChannelId == 3){
            $strHost = $strHost ? $strHost : (isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'');
            if (in_array($strHost, $arrValiadHost)) {
                $strSecret = $arrSecret[$iChannelId];
            }else{
                $strSecret = $arrSecret[0];
            }
        }else {
            if (isset($arrSecret[$iChannelId])) {
                $strSecret = $arrSecret[$iChannelId];
            } else {
                $strSecret = $arrSecret[0];
            }
        }

        return $strSecret;
    }

}