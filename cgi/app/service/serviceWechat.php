<?php
namespace sdkService\service;


class serviceWechat extends serviceBase
{
    private $appId = "wx92cf60f7577e2d48"; //公众号:wx92cf60f7577e2d48; 测试:wx00a0784be227f661
    private $appSecret = "6347543223aa409d36108565b51edd9a"; //公众号:6347543223aa409d36108565b51edd9a; 测试:d4624c36b6795d1d99dcf0547af5443d
    private $getCodeUrl = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=%s&redirect_uri=%s&response_type=code&scope=%s&state=1#wechat_redirect';
    private $getTokenUrl = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=%s&secret=%s&code=%s&grant_type=authorization_code';
    private $refreshUrl = 'https://api.weixin.qq.com/sns/oauth2/refresh_token?appid=%s&grant_type=refresh_token&refresh_token=%s';
    private $userInfoUrl = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=%s&openid=%s&lang=zh_CN';
    private $snsapiUserInfoUrl = 'https://api.weixin.qq.com/sns/userinfo?access_token=%s&openid=%s&lang=zh_CN';
    private $accessTokenUrl = 'http://wxtoken.wepiao.com/CreateWeiXinToken.php?app_id=%s&app_secret=%s';
    private $jstiketUrl = 'http://wxtoken.wepiao.com/CreateJsApiTicket.php?url=%s';
    private $sendTemplateUrl = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=%s'; //发送模板消息
    private $sendDKFUrl = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=%s';
    private $sendPayResultUrl = 'https://api.weixin.qq.com/cgi-bin/message/template/paymsg/send?access_token=%s'; //发送支付成功结果消息


    //主小程序63
    private $wxappAppId = 'wx945c04d33ee0a815';
    private $wxappAppSecret = '35ea60316fd1278b7e9b83254a904c26';
    private $getWxappTokenUrl = 'https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code';
    //小程序获取公众号accesstoken URL
    private $wxappMptokenUrl = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s';


    //多套小程序其他渠道id对应的appid,appsecret
    private $arrAppMap = [

        66 =>['appid'=>'wx14e985148f134ffa','appsecret'=>'942eda652356be4ce6d8e1dc51163fe3'],//电影演出票+
        67 =>['appid'=>'wx7fe953082fd6abd0','appsecret'=>'9a11e38f13135bf18567e82bb11cafd2'],//电影演出赛事
        68 =>['appid'=>'wxa87e5b4aea158561','appsecret'=>'b21c92e88ea7e46113546465b6d1beb9'],//格瓦拉电影演出
        86 =>['appid'=>'wx3a754be737455c09','appsecret'=>'843fbdd9936f01a5192abf43a21e8fd8'],//变5 小程序
    ];


    //以后新增小程序 channelId都用63，通过另一个参数subWxapp 来区分 appid和appsecret,从1000开始
    private $arrSubWxapp = [
        1000 => ['appid'=>'wx7421191ce13979e7','appsecret'=>'06cc125cf369de91fecd2bb4ead66e56'],//emoji小程序

    ];




    //获取token
    public function createWechatAccessToken($arrInput = [])
    {
        $iAppId = !empty($arrInput['appId']) ? $arrInput['appId'] : $this->appId;
        $strAppSecret = !empty($arrInput['appSecret']) ? $arrInput['appSecret'] : $this->appSecret;
        $strAccessTokenUrl = sprintf($this->accessTokenUrl, $iAppId, $strAppSecret);
        return $this->http($strAccessTokenUrl);
    }

    //获取jsapiticket
    public function createJsApiTicket($arrInput)
    {
        $strUrl = $arrInput['url'];
        $strJsApiTicketUrl = sprintf($this->jstiketUrl, $strUrl);
        if (!empty($arrInput['callback'])) {
            $strJsApiTicketUrl .= "&callback=" . $arrInput['callback'];
        }
        return $this->http($strJsApiTicketUrl, ['initJson' => 0]);
    }
    /**
     * 获取token
     * @param array $arrInput
     * @return array
     * @throws \Exception
     */
    public function createWyWechatAccessToken($arrInput = [])
    {
        $return = $this->getStOut();
        if (empty($arrInput['channelId'])) {
            $return = $this->getErrorOut(ERRORCODE_WECHAT_TOOL_PARAM_EMPTY);
        } else {
            try {
                $accessToken = $this->getAccessToken();
                $return['data'] = $accessToken;
            } catch (\Exception $e) { //
                $return = $this->getErrorOut(ERRORCODE_GET_ACCESS_TOKEN_FROM_REDIS_ERROR);
            }
        }
        return $return;
    }

    /**
     * 获取jsapiticket新方法
     * @param $arrInput
     * @return array
     */
    public function createWyJsApiTicket($arrInput)
    {
        $return = $this->getStOut();
        $iChannelId = self::getParam($arrInput, 'channelId');
        $sUrl = self::getParam($arrInput, 'url');
        if (empty($iChannelId) || empty($sUrl)) {
            $return = $this->getErrorOut(ERRORCODE_WECHAT_TOOL_PARAM_EMPTY);
        }
        $jsApiTicket = $this->model("WeChat")->getWechatRedis(TICKET_JS_API_KEY . $this->appId);
        $data = $this->buildJsApiTicketArr($sUrl, $jsApiTicket);
        $return['data'] = $data;

        return $return;
    }

    /**
     * 获取卡券ticket凭证
     * @param $arrInput
     * @return array
     * @throws \Exception
     */
    public function createWyCardTicket($arrInput)
    {
        $return = $this->getStOut();
        $iChannelId = self::getParam($arrInput, 'channelId');
        if (empty($iChannelId)) {
            $return = $this->getErrorOut(ERRORCODE_WECHAT_TOOL_PARAM_EMPTY);
        }
        $params['cardId'] = self::getParam($arrInput, 'card_id', '');
        $params['code'] = self::getParam($arrInput, 'code', '');
        $params['openid'] = self::getParam($arrInput, 'openid', '');
        $wxCardTicket = $this->model("WeChat")->getWechatRedis(TICKET_WX_CARD_KEY . $this->appId);
        $data = $this->buildCardExt($wxCardTicket, $params);
        $return['data'] = $data;

        return $return;
    }

    /**
     * @param $ticket
     * @param $params
     * @return array
     */
    private function buildCardExt($ticket, $params) {
        $signArr = array(
            'ticket' => $ticket,
            'timestamp' => time(),
            'card_id' => $params['cardId'],
            'code' => $params['code'],
            'openid' => $params['openid'],
            'nonce_str' => $this->createNoncestr(),
        );
        asort($signArr, SORT_STRING);
        //$str = implode('', $signArr);
        $str = '';
        foreach ($signArr as $ar) {
            $str .= $ar;
        }
        $sign = sha1($str);
        $cardExt = array(
            'code' => $params['code'],
            'openid' => $params['openid'],
            'timestamp' => $signArr['timestamp'],
            'nonce_str' => $signArr['nonce_str'],
            'signature' => $sign,
        );
        return $cardExt;
    }

    // 生成16位随机字符串
    private function createNoncestr()
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $res = "";
        for ($i = 0; $i < 16; $i++) {
            $index = mt_rand(0, strlen($chars) - 1);
            $res .= $chars[$index];
        }
        return $res;
    }

    /**
     * 发送模板消息
     * @param array $arrInput
     * @return array $return
     */
    public function sendWeiXinTemplateMessage($arrInput = [])
    {
        $iChannelId = self::getParam($arrInput, 'channelId');
        $toUser = self::getParam($arrInput, 'to_user');
        $templateId = self::getParam($arrInput, 'template_id');
        $sendData = self::getParam($arrInput, 'data');
        $url = self::getParam($arrInput, 'url', '');
        $msgType = self::getParam($arrInput, 'msgType', 'template');

        $return = self::getStOut();
        if (empty($iChannelId) || empty($toUser) || empty($templateId) || empty($sendData)) {
            $return = self::getErrorOut(ERRORCODE_WECHAT_PARAM_ERROR);
        } elseif ($msgType !== "template" && $msgType !== "template_trade") { //模板类型错误
            $return = self::getErrorOut(ERRORCODE_WECHAT_TEMPLATE_TYPE_ERROR);
        } else {
            $postUrl = "";
            if ($msgType === "template") { //模板消息
                $postUrl = $this->sendTemplateUrl;
            } elseif ($msgType === "template_trade") { //购买成功消息
                $postUrl = $this->sendPayResultUrl;
            }
            $accessToken = $this->getAccessToken();
            $postUrl = sprintf($postUrl, $accessToken);
            $postData = $this->formatSendData($toUser, $templateId, $url, $sendData);
            $response = $this->http($postUrl, $postData);
            if ($response['errcode'] !== 0) {
                $return = self::getErrorOut(ERRORCODE_WECHAT_SEND_TEMPLATE_ERROR);
                $return['data'] = $response;
            }
        }
        return $return;
    }

    /**
     * @param array $arrInput
     * @return array
     * @throws \Exception
     * @throws \RedisException
     */
    public function sendDKFMessage($arrInput = [])
    {
        $iChannelId = self::getParam($arrInput, 'channelId');
        $toUser = self::getParam($arrInput, 'toUser');
        $msgType = self::getParam($arrInput, 'msgType', 'text');
        $content = self::getParam($arrInput, 'content');
        $return = self::getStOut();
        if( empty($iChannelId) || empty($toUser) || empty($content))
        {
            $return = self::getErrorOut(ERRORCODE_WECHAT_PARAM_ERROR);
        } else{
            //todo 新版上线放开注释
            $postUrl = sprintf($this->sendDKFUrl, $this->getAccessToken());
//            $postUrl = sprintf($this->sendDKFUrl, $this->getOldAccessToken());
            $postData = $this->formatSendDKFData($toUser, $msgType, $content);
            $response = $this->http($postUrl, $postData);
            if ($response['errcode'] !== 0) {
                $return = self::getErrorOut(ERRORCODE_WECHAT_SEND_TEMPLATE_ERROR);
                $return['data'] = $response;
            }
        }
        return $return;
    }

    /**
     * 构造js ticket返回数据
     * @param $requestUrl
     * @param $jsApiTicket
     * @return array
     */
    private function buildJsApiTicketArr($requestUrl, $jsApiTicket)
    {
        $signArr = array(
            'noncestr' => $this->createNoncestr(),
            'jsapi_ticket' => $jsApiTicket,
            'timestamp' => time(),
            'url' => $requestUrl
        );
        // 生成sign
        ksort($signArr);
        $str = $this->createLinkstring($signArr);
        $sign = sha1($str);
        // 前端config需要的参数
        $ret = array(
            'appId' => $this->appId,
            'timestamp' => $signArr['timestamp'],
            'nonceStr' => $signArr['noncestr'],
            'signature' => $sign
        );
        return $ret;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param $para array 需要拼接的数组
     * @return string 拼接完成以后的字符串
     */
    private function createLinkstring($para)
    {
        $arg = "";
        while (list ($key, $val) = each($para)) {
            $arg .= $key . "=" . $val . "&";
        }
        //去掉最后一个&字符
        $arg = substr($arg, 0, count($arg) - 2);
        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }
        return $arg;
    }

    /**
     * @return bool|string
     * @throws \RedisException 抛出redis异常
     */
    public function getAccessToken()
    {
        try {
            $accessToken = $this->model('WeChat')->getWechatRedis(ACCESS_TOKEN_KEY . $this->appId);
        } catch (\RedisException $e) {
            throw $e;
        }
        return $accessToken;
    }

    /**
     * 用老的方式获取token，@todo 新版上线弃用
     * @return mixed
     * @throws \Exception
     * @throws \RedisException
     */

    public function getOldAccessToken()
    {
        try {
            $result = $this->createWechatAccessToken();
            $accessToken = $result['data'];
        } catch (\RedisException $e) {
            throw $e;
        }
        return $accessToken;
    }

    /**
     * 格式化参数，返回http的param
     * @param $toUser
     * @param $templateId
     * @param $url
     * @param $sendData
     * @return array
     */
    private function formatSendData($toUser, $templateId, $url, $sendData)
    {
        //判断data某个值的颜色
        $bodyColor = '#173177';
        $topColor = '#FF0000';
        foreach ($sendData as $key => $item) {
            if (!isset($item['color']))
                $sendData[$key]['color'] = $bodyColor;
        }
        $body = ['touser' => $toUser, 'template_id' => $templateId, 'url' => $url, 'topcolor' => $topColor, 'data' => $sendData];
        $postParam = [
            'arrData' => $body,
            'sMethod' => 'POST',
            'sendType' => 'json',
            'isHttps' => true,
        ];
        return $postParam;
    }

    /**
     * 格式化发送客服消息的数据（目前只支持文本，其他类型可根据需要再添加）
     * @param $toUser
     * @param $msgType
     * @param $content
     * @return array
     */
    private function formatSendDKFData($toUser, $msgType, $content)
    {
        $sendData = ['touser' => $toUser, 'msgtype' => $msgType];
        if($msgType === 'text')
        {
            $sendData['text'] = [
                'content' => $content,
            ];
        }
        $postParam = [
            'arrData' => $sendData,
            'sMethod' => 'POST',
            'sendType' => 'json',
            'jsonUnicode' => true,
            'isHttps' => true,
        ];
        return $postParam;
    }

    //根据code获取openid
    public function getOpenIdByCode($strCode)
    {
        $arrReturn = [];
        $strWeChatTokenUrl = sprintf($this->getTokenUrl, $this->appId, $this->appSecret, $strCode);
        $params['isHttps'] = true;//设置它，就不去检查https网站的证书了
        $params['iTimeout'] = 5;//微信调用接口，设置5秒超时
        $arr = $this->http($strWeChatTokenUrl, $params);
        if (isset($arr['openid'])) {
            $arrReturn['openid'] = $arr['openid'];
            $arrReturn['access_token'] = $arr['access_token'];
            $arrReturn['refresh_token'] = $arr['refresh_token'];
            $arrReturn['unionid'] = isset($arr['unionid']) ? $arr['unionid'] : '';
        }
        if (!empty($arr['access_token']) && !empty($this->service('WeChatLogin')->saveToken)) {
            $this->model('WeChat')->saveWxUserToken($arr['openid'], $arr['access_token']);
        }
        return $arrReturn;
    }

    //根据refreshtoken获取openid
    public function getOpenIdByRefreshToken($strRefreshToken)
    {
        $arrRet = [];
        $strRefreshUrl = sprintf($this->refreshUrl, $this->appId, $strRefreshToken);
        $params['iTimeout'] = 5;//微信调用接口，设置5秒超时
        $arr = $this->http($strRefreshUrl,$params);
        if (isset($arr['openid'])) {
            $arrRet['openid'] = $arr['openid'];
            $arrRet['access_token'] = $arr['access_token'];
            $arrRet['refresh_token'] = $arr['refresh_token'];
            return $arrRet;
        }
        if (!empty($arr['access_token']) && !empty($this->service('WeChatLogin')->saveToken)) {
            $this->model('WeChat')->saveWxUserToken($arr['openid'], $arr['access_token']);
        }
        return $arrRet;
    }

    //获取302Url
    public function get302Url($strRedirectURL, $strScope = 'snsapi_base')
    {
        $strWeChatCodeUrl = sprintf($this->getCodeUrl, $this->appId, urlencode($strRedirectURL), $strScope);
        return $strWeChatCodeUrl;
    }

    //其实等价于get302Url,埋坑的方法
    public function getRedirectUrl($arrInput = [])
    {
        $redirectUrl = self::getParam($arrInput, 'redirectUrl');
        $strScope = self::getParam($arrInput, 'scope');
        $arrReturn = static::getStOut();
        $arrReturn['ret'] = $arrReturn['sub'] = 302;
        if (empty( $strScope ) || empty( $redirectUrl )) {
            return static::getErrorOut(ERRORCODE_WECHAT_REDIRECT_URL_PARAMS_ERROR);
        }
        $arrReturn['data']['redirectUrl'] = $this->get302Url($redirectUrl, $strScope);

        return $arrReturn;
    }

    /**
     * 通过snsapi_userinfo方式弹授权页获取用户信息
     * @param $arrInput array $access_token|$openid
     * @return array|bool|mixed
     */
    public function getUserInfoBySnsapi($arrInput)
    {
        $access_token = self::getParam($arrInput, 'access_token');
        $openid = self::getParam($arrInput, 'openid');
        $userInfoUrl = sprintf($this->snsapiUserInfoUrl, $access_token, $openid);
        $params['isHttps'] = true;//设置它，就不去检查https网站的证书了
        return $this->http($userInfoUrl, $params);
    }

    //公共号获取用户信息
    public function getUserInfo($arrInput)
    {
        $params['access_token'] = self::getParam($arrInput, 'access_token');
        $params['openid'] = self::getParam($arrInput, 'openid');
        $userInfoUrl = sprintf($this->userInfoUrl, $params['access_token'], $params['openid']);
        $params['isHttps'] = true;//设置它，就不去检查https网站的证书了
        return $this->http($userInfoUrl, $params);
    }

    /**
     * 返回appId与appSecret
     * 注：需要与 http://wx.wepiao.com/cgi/bonus_proxy.php?url=返回code的url 配合使用，供内部其他域名使用微信授权。
     */
    public function getAppsecInfo($arrInput = [])
    {
        $return = [
            'appId' => $this->appId,
            'appSecret' => $this->appSecret,
        ];
        return $return;
    }


    //根据微信小程序的code获取openid
    public function getOpenIdByWxappCode($strCode,$iChannelId = 63,$subWxapp = 0)
    {
        $this->setWxAppIdByChannelId($iChannelId,$subWxapp);
        $arrReturn = [];
        $strWeChatTokenUrl = sprintf($this->getWxappTokenUrl, $this->wxappAppId, $this->wxappAppSecret, $strCode);
        $params['isHttps'] = true;//设置它，就不去检查https网站的证书了
        $params['iTimeout'] = 5;//微信调用接口，设置5秒超时
        $arr = $this->http($strWeChatTokenUrl, $params);
        if (isset($arr['openid'])) {
            $arrReturn['openid'] = $arr['openid'];
            $arrReturn['session_key'] = $arr['session_key'];
        }
        return $arrReturn;
    }

    //获取小程序公众号accesstoken
    public function getWxappMptoken($iChannelId = 63)
    {
        $this->setWxAppIdByChannelId($iChannelId);
        $strWxappMpTokenUrl = sprintf($this->wxappMptokenUrl,$this->wxappAppId,$this->wxappAppSecret);
        $params['isHttps'] = true;//设置它，就不去检查https网站的证书了
        $params['iTimeout'] = 5;//微信调用接口，设置5秒超时
        $arr = $this->http($strWxappMpTokenUrl, $params);
        return $arr;
    }

    private function setWxAppIdByChannelId($iChannelId = 63,$subWxapp = 0 )
    {
        //后续如果有subwxapp参数就优先用此参数找appid
        if(!empty($subWxapp) && !empty($this->arrSubWxapp[$subWxapp])){
            $this->wxappAppId = $this->arrSubWxapp[$subWxapp]['appid'];
            $this->wxappAppSecret = $this->arrSubWxapp[$subWxapp]['appsecret'];
        }else {
            if (!empty($this->arrAppMap[$iChannelId])) {
                $this->wxappAppId = $this->arrAppMap[$iChannelId]['appid'];
                $this->wxappAppSecret = $this->arrAppMap[$iChannelId]['appsecret'];
            }
        }
    }

}