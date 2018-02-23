<?php
/**
 * 手Q用户接口
 */
namespace sdkService\service;

use sdkService\helper\Utils;

class serviceMqq extends serviceBase
{
    private $appId = '101233410'; // APP ID
    private $appSecret = '51f9730e667903f8399360236c8e9bcf'; // APP KEY
    private $getTokenURL = 'https://graph.qq.com/oauth2.0/token?client_id=%s&client_secret=%s&code=%s&redirect_uri=%s&grant_type=authorization_code';
    private $getOpenIdURL = 'https://graph.qq.com/oauth2.0/me?access_token=%s';
    private $getUserInfo = 'https://graph.qq.com/user/get_user_info?access_token=%s&oauth_consumer_key=%s&openid=%s';
    //手Q公众号-获取公众号accessToken
    const MPAPPID = '101125056';
    const MPSECRET = '0e3f39b513a703d072d1edb8c78b6c9c';
    const MPEXPIRETIME = 5400; //access_token本身过期时间为2小时，此处保存1.5个小时
    private $getMqqMqToken = 'https://api.mp.qq.com/cgi-bin/token?appid=%s&secret=%s';
    //手Q公众号-模版消息
    private $sendTemplateUrlOpenid = 'https://api.mp.qq.com/cgi-bin/message/template/send?access_token=%s';
    private $sendTemplateUrlQQ = 'https://api.mp.qq.com/cgi-bin/message/template/sendqq?access_token=%s';
    private $sendTemplateUrlMobile = 'https://api.mp.qq.com/cgi-bin/message/template/sendmob?access_token=%s';
    //手Q公众号-开发者模式
    private $sendMenuCreate = 'https://api.mp.qq.com/cgi-bin/menu/create?access_token=%s';
    private $sendMenuGet = 'https://api.mp.qq.com/cgi-bin/menu/get?access_token=%s';
    private $sendMenuDelete = 'https://api.mp.qq.com/cgi-bin/menu/delete?access_token=%s';

    private $refreshURL = 'https://graph.qq.com/oauth2.0/token?client_id=%s&client_secret=%s&grant_type=refresh_token&refresh_token=%s';
    private $getCodeURL = 'http://open.show.qq.com/cgi-bin/login_state_auth_redirect?appid=%s&redirect_uri=%s';

    const MQQOPENID = 'MqqOpenId';//加密后的openid，返回名
    const OPENID = 'openid';//未加密的openid，返回名
    const REFRESH_TOKEN_KEY = '_SESSION_ID_PIAO_QQ_COM_'; //refresh token
    const OPEN_ID_EXPIRE = 3600; //openid 一小时
    const STATE_KEY = '_di_nepo_'; //给JS 统计PV UV 使用 不知道有没有用
    const WX_CHANNEL_ID = 28;        //公众账号缩写 或者渠道ID
    const REFRESH_TOKEN_EXPIRE = 604800; //7天
    const UUID_KEY = 'uuid'; //用户一次流程中唯一编号的key
    private $needUserinfo = 0; //是否需要用户信息，默认不需要
    private $saveToken = 1; //是否保存accessToken
    private $saveCookie = 0; //是否保存用户cookie，只在手Q项目调用时保存

    public function __construct($arrInput = [])
    {
        $isFromMqq = $this->getParam($arrInput, 'fromMqq', 0); //代表是否是手Q项目的调用
        $this->saveCookie = $this->getParam($arrInput, 'saveCookie', $isFromMqq);
        $this->saveToken = $this->getParam($arrInput, 'saveToken', $this->saveToken);

        $this->needUserinfo = $this->getParam($arrInput, 'needUserinfo');
    }

    public function login($arrInput)
    {
        //设置uuid;
        $this->setuuid();

        $channelId = $this->getParam($arrInput, 'channelId');
        $strCode = $this->getParam($arrInput, 'code');
        //取回调地址兼容两种参数名
        $redirectUrl = $this->getParam($arrInput, '_client_redirect_');
        if (empty($redirectUrl)) {
            $redirectUrl = $this->getParam($arrInput, 'redirectUrl');
        }

        //传入force时跳过读cookie
        $force = $this->getParam($arrInput, 'force');
        //有code则从腾讯取，没有则先从cookie中取，取不到再返回302地址
        $arrReturn = self::getStOut();
        if (!empty($strCode)) {
            $arrReturn = $this->getOpenidByCode($arrInput);
            if (isset($arrReturn['data']['openid']) && !isset($arrReturn['data'][self::MQQOPENID])) {
                $data = [
                    'str' => $arrReturn['data']['openid'],
                    'channelId' => $channelId,
                ];
                $response = $this->service('Common')->encrypt($data);
                $mqqOpenId = $response['data']['encryptStr'];
                $arrReturn['data'] = [
                    self::MQQOPENID => $mqqOpenId,
                    self::OPENID => $arrReturn['data'][self::OPENID],
                ];
                $this->_saveCookie([self::MQQOPENID => $mqqOpenId]);
            }
        } else {
            $arrCookieOpenid = $this->getOpenIdFromCookie();
            if (!empty($arrCookieOpenid['data'][self::OPENID]) && empty($force)) {
                $arrReturn = $arrCookieOpenid;
            } else {
                $arrReturn['data']['_client_redirect_'] = $this->getClientRedirect($redirectUrl);
            }
        }

        if (!empty($arrReturn['data'][self::OPENID])) {
            $this->_saveCookie([self::STATE_KEY => $arrReturn['data'][self::OPENID]]);
        }
        return $arrReturn;
    }

    public function setuuid()
    {
        //添加uuid，session会话丢失，则uuid丢失，下次会变更。它不是用户终身唯一id，而是用户交互行为中的id。
        if (empty($_COOKIE['uuid'])) {
            $uuid = Utils::uuid('mqq-');
            //原来是setcookie(self::UUID_KEY, $uuid, 0, '/');
            $this->_saveCookie([self::UUID_KEY => $uuid], 0);
        }
    }

    public function getOpenIdFromCookie($arrInput = [])
    {
        $channelId = $this->getParam($arrInput, 'channelId', self::WX_CHANNEL_ID);
        $arrReturn = self::getStOut();
        if (!empty($_COOKIE[self::MQQOPENID])) {
            $arrRet = $this->service('common')->decrypt([
                'str' => $_COOKIE[self::MQQOPENID],
                'channelId' => $channelId
            ]);
            if ($arrRet['ret'] == 0) {
                $arrReturn['data'][self::MQQOPENID] = $_COOKIE[self::MQQOPENID];
                $arrReturn['data'][self::OPENID] = $arrRet['data']['decryptStr'];
            }
        } elseif (!empty($_COOKIE[self::REFRESH_TOKEN_KEY])) {
            $arrReturn['data'] = $this->refreshToken($_COOKIE[self::REFRESH_TOKEN_KEY], $channelId);
        }
        if (empty($arrReturn['data'])) {
            $arrReturn = ['ret' => -1, 'sub' => -1, 'data' => []];
        }
        return $arrReturn;
    }

    public function refreshToken($strRefreshToken, $channelId)
    {
        $strRefreshUrl = sprintf($this->refreshURL, $this->appId, $this->appSecret, $strRefreshToken);
        $result = $this->http($strRefreshUrl, ['isHttps' => true, 'initJson' => false]);
        parse_str($result, $arrResult);
        $arrResult['access_token'] = self::getParam($arrResult, 'access_token');
        $openId = $this->_getOpenidByAccessToken($arrResult['access_token']);
        $arrReturn = [];
        if (isset($openId)) {
            if ($this->saveToken) {
                $this->model('Mqq')->saveMqqUserToken($openId, $arrResult['access_token']);
            }
            $strCookieValue = $this->service('common')->encrypt(['str' => $openId, 'channelId' => $channelId]);
            $strCookieValue = ($strCookieValue['ret'] == 0) ? $strCookieValue['data']['encryptStr'] : '';
            $cookieData = [
                self::MQQOPENID => $strCookieValue,
                self::OPENID => $openId,
                self::STATE_KEY => $openId,
            ];
            $this->_saveCookie($cookieData);
            $arrReturn = [
                self::MQQOPENID => $strCookieValue,
                self::OPENID => $openId,
            ];
        }
        return $arrReturn;
    }

    public function get302URL($strRedirectURL, $strScope = 'get_user_info')
    {
        $strWeChatCodeUrl = sprintf($this->getCodeURL, $this->appId, urlencode($strRedirectURL));
        return $strWeChatCodeUrl;
    }

    public function getClientRedirect($redirectUrl)
    {
        $strScope = isset($arrParams['scope']) ? $arrParams['scope'] : 'get_user_info';
        if (isset($redirectUrl)) {
            if (!filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
                $redirectUrl = '';
            } else {
                $temp_host = parse_url($redirectUrl, PHP_URL_HOST);
                if (strrchr($temp_host, 'wepiao.com') !== 'wepiao.com') {
                    $redirectUrl = '';
                }
            }
            $redirectUrl = $this->get302URL($redirectUrl, $strScope);
        } else {
            $redirectUrl = '';
        }
        return $redirectUrl;
    }

    /**
     * 通过code获取手Q的用户openid（用户信息可选），并存入cookie
     * @param $arrInput ['channelId'] 渠道编号
     * @param $arrInput ['code'] 授权码code
     * @param $arrInput ['_client_redirect_'] 回调地址_client_redirect_
     * @return 示例 ['ret'=>0,'sub'=>0,'data'=>['MqqOpenId'=>'xxx',]]
     */
    public function getOpenidByCode($arrInput)
    {
        $channelId = $this->getParam($arrInput, 'channelId');
        $strCode = $this->getParam($arrInput, 'code');

        $arrReturn = self::getStOut();
        //换取access_token
        $output = $this->_getAccessTokenByCode($strCode);
        if (!empty($output['access_token'])) {
            $this->_saveCookie([self::REFRESH_TOKEN_KEY => $output['refresh_token']], self::REFRESH_TOKEN_EXPIRE);
            if (empty($this->needUserinfo)) {
                //判断是否需要用户信息，不需要时通过access_token获取openid
                $strOpenId = $this->_getOpenidByAccessToken($output['access_token']);
                if (!empty($strOpenId)) {
                    if ($this->saveToken) {
                        $this->model('Mqq')->saveMqqUserToken($strOpenId, $output['access_token']);
                    }
                    $arrReturn['data'] = [
                        self::OPENID => $strOpenId,
                    ];
                    return $arrReturn;
                }
                $arrReturn = ['ret' => -1, 'sub' => -1, 'msg' => 'access_token error!'];
                return $arrReturn;
            } else {
                //判断是否需要用户信息，需要时通过access_token额外获取用户信息
                $data = [
                    'access_token' => $output['access_token'],
                    'channelId' => $channelId,
                ];
                $resData = $this->getUserinfoByAccessToken($data);
                if (isset($resData['ret']) && $resData['ret'] == 0) {
                    $arrReturn = ['ret' => 0, 'sub' => 0, 'msg' => 'success', 'data' => $resData['data']];
                    return $arrReturn;
                }
                $arrReturn = ['ret' => -1, 'sub' => -1, 'msg' => 'access_token error!'];
                return $arrReturn;
            }
        } else {
            $arrReturn = ['ret' => -1, 'sub' => -1, 'msg' => 'code error!'];
        }
        return $arrReturn;
    }

    /**
     * 通过access_token获取手Q用户信息与openid，并写入cookie
     * @param $arrInput ['channelId']   渠道编号
     * @param $arrInput ['access_token']
     * @param $arrInput ['openid']   非必须，不传时会去腾讯接口获取openid
     * @return array
     */
    public function getUserinfoByAccessToken($arrInput)
    {
        $channelId = $this->getParam($arrInput, 'channelId');
        $accessToken = $this->getParam($arrInput, 'access_token');
        $strOpenId = $this->getParam($arrInput, 'openid');  //非必须
        if (empty($strOpenId)) {
            //获取openid
            $strOpenId = $this->_getOpenidByAccessToken($accessToken);
            if (empty($strOpenId)) {
                $arrReturn = ['ret' => -1, 'sub' => -1, 'msg' => 'access_token error!'];
                return $arrReturn;
            } elseif (!empty($this->saveToken)) {
                $this->model('Mqq')->saveMqqUserToken($strOpenId, $accessToken);
            }
        }

        //获取手Q用户信息，加入返回值中，并写入cookie
        $arrUserinfo = $this->_getUserinfoByAccessToken($accessToken, $strOpenId);
        if (!empty($arrUserinfo)) {
            //获取加密后openid，并写入cookie
            $mqqOpenId = '';
            $data = [
                'str' => $strOpenId,
                'channelId' => $channelId,
            ];
            $response = $this->service('Common')->encrypt($data);
            if ($response['ret'] == 0 && $response['sub'] == 0) {
                $mqqOpenId = $response['data']['encryptStr'];
            }

            $nickname = $this->getParam($arrUserinfo, 'nickname');
            $headImg = $this->getParam($arrUserinfo, 'figureurl_qq_2'); //有多种大小的头像，目前取100*100的
            $cookieData = [
                self::MQQOPENID => $mqqOpenId,
                'MqqNickname' => $nickname,
                'MqqHeadimg' => $headImg,
            ];
            $this->_saveCookie($cookieData);
            $resData = [
                self::MQQOPENID => $mqqOpenId,
                'MqqNickname' => $nickname,
                'MqqHeadimg' => $headImg,
                self::OPENID => $strOpenId,
            ];
            $arrReturn = ['ret' => 0, 'sub' => 0, 'msg' => 'success', 'data' => $resData];
        } else {
            $arrReturn = ['ret' => -1, 'sub' => -1, 'msg' => 'get userinfo error!'];
        }
        return $arrReturn;
    }

    /**
     * 通过code换取AccessToken
     * @param $code
     * @return array ['access_token'=>'','refresh_token'=>'','expires_in'=>'']
     */
    public function _getAccessTokenByCode($code = '')
    {
        $arrReturn = [];
        #date_default_timezone_set('Asia/Shanghai');
        if (empty($code)) {
            return $arrReturn;
        }
        $strCode = $code;
        $getTokenURL = sprintf($this->getTokenURL, $this->appId, $this->appSecret, $strCode,
            urlencode('https://mqq.wepiao.com'));

        $params['isHttps'] = true;
        $params['initJson'] = false;
        $result = $this->http($getTokenURL, $params);
        if (is_array($result)) { //数组说明返回了50401
            return $arrReturn;
        }
        parse_str($result, $output);
        if (!empty($output['access_token'])) {
            $arrReturn = $output;
        }
        return $arrReturn;
    }

    /**
     * 通过AccessToken获取openid
     * @param $accessToken
     * @return string 'C0A8A8839CB8A39CA437E76XXXXXXX'
     */
    public function _getOpenidByAccessToken($accessToken)
    {
        $strOpenId = '';
        if (empty($accessToken)) {
            return $strOpenId;
        }
        $getOpenIdURL = sprintf($this->getOpenIdURL, $accessToken);
        //通过access_token获取openid
        $params['isHttps'] = true;
        $params['initJson'] = false;
        $jsonObj = $this->http($getOpenIdURL, $params);
        //腾讯返回示例：callback( {"client_id":"101233410","openid":"42C4E611A0651923D005895D887EF1EA"} )

        if (!empty($jsonObj)) {
            $arrTmp = explode('callback(', $jsonObj);
            $arrCallback = explode(")", $arrTmp[1]);
            $arr = json_decode($arrCallback[0], 1);
            $strOpenId = isset($arr['openid']) ? $arr['openid'] : '';
        }
        return $strOpenId;
    }

    /**
     * 通过AccessToken与openid获取用户信息，包括昵称头像
     * @param $accessToken
     * @param $openid
     * @return array ['nickname'=>'','figureurl'=>,..]
     */
    public function _getUserinfoByAccessToken($accessToken, $openid)
    {
        $arrReturn = [];
        if (empty($accessToken)) {
            return $arrReturn;
        }
        $getUserInfo = sprintf($this->getUserInfo, $accessToken, $this->appId, $openid);
        //通过access_token获取openid
        $params['isHttps'] = true;
        $params['initJson'] = true; //对结果进行json_decode
        $jsonObj = $this->http($getUserInfo, $params);
        if (isset($jsonObj['ret']) && $jsonObj['ret'] == 0) {
            $arrReturn = $jsonObj;
        }
        return $arrReturn;
    }

    /**
     * 返回appid和appkey，敏感信息，只能内网调用！
     * 目前提供给“周边商城”使用，用于获取openid以及用户地址信息
     * @return array
     */
    public function getAppidAppkey($arrInput = [])
    {
        $arrReturn = [
            'appid' => $this->appId,
            'appkey' => $this->appSecret,
        ];
        return $arrReturn;
    }

    /**
     * 保存cookie
     * @param array $arrParams 输入数组如['MqqOpenid' => 'asdasdfqws',]
     * @param int $expireTime 默认1个小时，但如果传0，则表示session过期后过期
     * @param string $cookieRoute 默认'/'
     * @param string $domain 默认'.wepiao.com'
     */
    public function _saveCookie(
        $arrParams = [],
        $expireTime = self::OPEN_ID_EXPIRE,
        $cookieRoute = '/',
        $domain = '.wepiao.com'
    ) {
        //传入了$saveCookie=1才会保存
        if ($this->saveCookie && is_array($arrParams)) {
            //当设置cookie过期时间为0或者忽略不设置的情况下，cookie将会在session过期后过期（即浏览器关闭后cookie过期）
            $expireTime = ($expireTime === 0) ? $expireTime : time() + $expireTime;
            foreach ($arrParams as $cookieName => $cookieValue) {
                setcookie($cookieName, $cookieValue, $expireTime, $cookieRoute, $domain); //设置cookie，默认超时时间1小时
            }
        }
    }

    ###########################################
    ############# 手Q公众号 START ##############
    ###########################################
    /**
     * 发送模板消息
     * @param string $tousername 推送用户的openid
     * @param string $templateid 模版ID
     * @param string $data 推送内容，json格式字符串
     * @return array $return
     */
    public function sendMqqTemplateMessage($arrInput = [])
    {
        //必传，三选一，优先取tousername（openid），其次touserqq（qq），最后tousermob（mobile）。向QQ请求的接口会不同
        if (!empty($arrInput['tousername'])) {
            $data['tousername'] = $arrInput['tousername'];
            $postUrl = $this->sendTemplateUrlOpenid;
        } elseif (!empty($arrInput['touserqq'])) {
            $data['touserqq'] = $arrInput['touserqq'];
            $postUrl = $this->sendTemplateUrlQQ;
        } elseif (!empty($arrInput['tousermob'])) {
            $data['tousermob'] = $arrInput['tousermob'];
            $postUrl = $this->sendTemplateUrlMobile;
        } else {
            $postUrl = '';
        }
        $data['templateid'] = self::getParam($arrInput, 'templateid'); //必传，模版ID
        $data['data'] = self::getParam($arrInput, 'data'); //必传，模版参数内容
        $data['msgType'] = self::getParam($arrInput, 'msgType', 'txt'); //默认txt(目前只支持文本消息)
        //type字段click/view二选一，代表点击事件或者跳转，决定后面取key或者url
        $data['type'] = self::getParam($arrInput, 'type');
        switch ($data['type']) {
            case 'click':
                $data['key'] = self::getParam($arrInput, 'key');
                break;
            case 'view':
                $data['url'] = self::getParam($arrInput, 'url');
                break;
        }
        $data['name'] = self::getParam($arrInput, 'name', '');
        $data['button'] = json_decode(self::getParam($arrInput, 'button'), true);
        $return = self::getStOut();
        unset($return['data']); //成功时不返回data
        if (empty($postUrl) || empty($data['templateid']) || empty($data['data'])) {
            $return = self::getErrorOut(ERRORCODE_WECHAT_PARAM_ERROR);
        } else {
            $accessToken = $this->getAccessToken();
            $postRealUrl = sprintf($postUrl, $accessToken);
            $arrSendParam['sMethod'] = 'post';
            $arrSendParam['isHttps'] = true;
            $arrSendParam['initJson'] = true; //返回值json解析
            $arrSendParam['sendType'] = 'json'; //以json方式发起请求
            $arrSendParam['arrData'] = $data;
            $response = $this->http($postRealUrl, $arrSendParam);
            if ($response['errcode'] !== 0) {
                if($response['errcode'] === 40001){
                    $token = $this->setAccessToken();
                    $postRealUrl = sprintf($postUrl, $token);
                    $response = $this->http($postRealUrl, $arrSendParam);
                    return $return;
                }
                $return = self::getErrorOut(ERRORCODE_WECHAT_SEND_TEMPLATE_ERROR);
                $return['data'] = $response;
            }
        }
        return $return;
    }

    /**
     * 获取手Q公众号accessToken
     * @return string $access_token
     */
    public function getAccessToken()
    {
        $strAccesstoken = $this->model('Mqq')->getMptoken();
        if (empty($strAccesstoken)) {
            $getAccessTokenUrl = sprintf($this->getMqqMqToken, self::MPAPPID, self::MPSECRET);
            $params['isHttps'] = true;
            $params['initJson'] = true; //对结果进行json_decode
            $arrRet = $this->http($getAccessTokenUrl, $params);
            if (!empty($arrRet['access_token'])) {
                $strAccesstoken = $arrRet['access_token'];
                $this->model('Mqq')->setMptoken($strAccesstoken, self::MPEXPIRETIME);
            } else {
                $strAccesstoken = '';
            }
        }
        return $strAccesstoken;
    }

    /**
     * 主动获取手Q公众号的accesToken并保存到redis中
     * @return string 返回获取的accessToken
     */
    public function setAccessToken()
    {
        $getAccessTokenUrl = sprintf($this->getMqqMqToken, self::MPAPPID, self::MPSECRET);
        $params['isHttps'] = true;
        $params['initJson'] = true; //对结果进行json_decode
        $arrRet = $this->http($getAccessTokenUrl, $params);
        if (!empty($arrRet['access_token'])) {
            $strAccesstoken = $arrRet['access_token'];
            $this->model('Mqq')->setMptoken($strAccesstoken, self::MPEXPIRETIME);
        } else {
            $strAccesstoken = '';
        }
        return $strAccesstoken;
    }

    /**
     * 手Q公众号-获取自定义菜单
     */
    public function mpMenuGet()
    {
        $accessToken = $this->getAccessToken();
        $postUrl = sprintf($this->sendMenuGet, $accessToken);
        $params['isHttps'] = true;
        $params['initJson'] = false;
        $response = $this->http($postUrl, $params);
        var_dump($response);
    }

    /**
     * 获取用户手Q公众号推送开关
     * 思路：使用一个字符串表示所有开关，如0010表示只有第三个打开。
     * 主要是为存储结构修改为hash做准备，利于存储。如 大KEY => [openid => 0010, openid => 1110 ...]
     */
    public function getMqqPushSwitch($arrInput = [])
    {
        $strDefault = '11'; //默认值，新增时需要加到这里
        $return = self::getStOut();
        $strOpenId = self::getParam($arrInput, 'openId');
        $dataSwitch = $this->model('Mqq')->getMqqPushSwitch($strOpenId);
        if ($dataSwitch === false) {
            $dataSwitch = $strDefault;
        }
        $return['data'] = [
            //开关名，新增时需要加到这里
            'pushAfterShowed' => substr($dataSwitch, 0, 1),
            'replyOnComment' => substr($dataSwitch, 1, 1),
        ];
        return $return;
    }

    /**
     * 设置用户手Q公众号推送开关
     * 思路：使用一个字符串表示所有开关，如0010表示只有第三个打开。
     * 主要是为存储结构修改为hash做准备，利于存储。如 大KEY => [openid => 0010, openid => 1110 ...]
     * 开关顺序：( 11 ) => ( 放映后引导评论，评论有回复 )
     */
    public function setMqqPushSwitch($arrInput = [])
    {
        $strDefault = '11'; //默认值，新增时需要加到这里
        $return = self::getStOut();
        $strOpenId = self::getParam($arrInput, 'openId');
        //获取现有数据，并在数据缺失时填充默认值
        $dataSwitch = $this->model('Mqq')->getMqqPushSwitch($strOpenId);
        if (strlen($dataSwitch) < strlen($strDefault)) {
            $dataSwitch = $dataSwitch . substr($strDefault, strlen($dataSwitch) - strlen($strDefault));
        }
        $arrSwitches = [
            //开关名，新增时需要加到这里
            '0' => self::getParam($arrInput, 'pushAfterShowed', null), //放映后引导评论
            '1' => self::getParam($arrInput, 'replyOnComment', null), //评论有回复
        ];
        foreach ($arrSwitches as $k => $v) {
            //传入了1或0时，依次进行修改
            if ($v !== null && $v !== false && $v !== '') {
                $v = substr(intval($v), 0, 1);//转化为数字并只取一位
                $dataSwitch = substr_replace($dataSwitch, $v, $k, 1);
            }
        }
        $res = $this->model('Mqq')->setMqqPushSwitch($strOpenId, $dataSwitch);
        if (empty($res)) {
            $return = self::getErrorOut(ERRORCODE_SYS_REDIS_CONNECTION);
        } else {
            $return['data'] = [
                //开关名，新增时需要加到这里
                'pushAfterShowed' => substr($dataSwitch, 0, 1),
                'replyOnComment' => substr($dataSwitch, 1, 1),
            ];
        }
        return $return;
    }
    ###########################################
    ############# 发手Q公众号 END ##############
    ###########################################


    /*
     * 从redis获取手Q auth2.0 accesstoken
     */
    public function getMqqUserToken($arrInput = [])
    {
        $strOpenId = $arrInput['openId'];
        $iChannelId = $arrInput['channelId'];
        return $this->model('Mqq')->getMqqUserToken($iChannelId, $strOpenId);
    }
    /**
     * 从redis获取手Q auth2.0 accesstoken
     * 调用getMqqUserToken方法，将数据标准化
     */
    public function getMqqUserTokenStandard($arrInput = [])
    {
        $return = self::getStOut();
        $return['data']['token']='';
        $res = $this->getMqqUserToken($arrInput);
        if(!empty($res) ){
            $return['data']['token'] = $res;
        }
        if(empty($return['data']['token'])){
            $return = self::getErrorOut(ERRORCODE_MQQ_TOKEN_INVALIAD);
        }
        return $return;
    }
    /**
     * 手Q个人资料页，即将上映影片推荐
     * 说明：先从baymax配置中取数据，不足时从即将上映影片中取
     */
    public function getMovieWillRecommend($arrInput = [])
    {
        $numShow = self::getParam($arrInput, 'num', 3); //展示位个数，默认3个
        $iMovieId = self::getParam($arrInput, 'movieId', ''); //从哪个片子请求过来的（过滤用）
        $iCityId = self::getParam($arrInput, 'cityId', '10'); //从哪个片子请求过来的（过滤用）
        $iChannelId = self::getParam($arrInput, 'channelId');
        $params = $arrInput;
        unset($params['logId']);
        $time = time();
        $resData = [];
        //读取文件缓存
        $resData = $this->getCacheData($params, 1);
        if (empty($resData)) {
            //1.取出baymax中配置的推荐位数据
            $baymaxData = $this->model('Mqq')->getMovieWillRecommend();
            $baymaxNum = 0;
            if (!empty($baymaxData)) {
                foreach ($baymaxData as $k) {
                    $recData = json_decode($k, 1);
                    $reMovieId = self::getParam($recData, 'movieId');
                    if ( !empty($reMovieId) && ($reMovieId == $iMovieId)) {
                        continue;
                    }
                    //只取时间段符合的数据
                    if ($baymaxNum < $numShow && ($recData['startTime'] <= $time && $recData['endTime'] >= $time)) {
                        //图片地址处理
                        if (!empty($recData['pic'])) {
                            $recData['pic'] = CDN_APPNFS . $recData['pic'];
                        }
                        $resData[] = [
                            'id' => self::getParam($recData, 'movieId'),
                            'name' => self::getParam($recData, 'movieName'),
                            'date' => self::getParam($recData, 'date'),
                            'poster_url' => self::getParam($recData, 'pic'),
                            'prevue' => '',
                            'url' => self::getParam($recData, 'link'),
                        ];
                        $baymaxNum++;
                    }
                }
            }
            $numShow = $numShow - $baymaxNum;
            //2.baymax配置不满时，拉取即将上映列表中的数据，直至填满展示位
            if ($numShow > 0) {
                //读取即将上映列表
                $arrParams = [
                    'cityId' => $iCityId,
                    'channelId' => $iChannelId,
                ];
                $willDataRaw = $this->service('Movie')->getMovieWillWithDate($arrParams);
                $num = 0;
                foreach ($willDataRaw['data']['list'] as $monlist) {
                    $willData = $monlist['list'];
                    foreach ($willData as $k) {
                        foreach ($k['list'] as $movieinfo) {
                            $willMovieId = $movieinfo['id'];
                            if ( !empty($willMovieId) && ($willMovieId == $iMovieId)) {
                                continue;
                            }
                            if ($num < $numShow && !empty($movieinfo['prevue_status'])
                                && $movieinfo['will_flag'] == 1 && $movieinfo['buy_flag'] == 1
                            ) {
                                //读取影片详情，主要为获取预告片地址
                                $arrParams = [
                                    'movieId'   => $movieinfo['id'],
                                    'channelId' => $iChannelId,
                                    'num'       => 1,
                                    'page'      => 1,
                                ];
                                $movieDetail = $this->service('Movie')->readMovieVideosNewStatic($arrParams);
                                //预告片地址，类似 n0322j0kd14 这样。先取prevue中，没有再取video中的。
                                if ( !empty($movieDetail['data']['list'])) {
                                    $firstPrevue = (array)$movieDetail['data']['list'][0];
                                    $resData[] = [
                                        'id'         => strval($willMovieId),
                                        'name'       => !empty($movieDetail['data']['movie_name']) ? $movieDetail['data']['movie_name'] : '',
                                        'date'       => !empty($movieinfo['date']) ? $movieinfo['date'] : '',
                                        'poster_url' => !empty($movieinfo['poster_url']) ? $movieinfo['poster_url'] : '',
                                        'prevue'     => !empty($firstPrevue['vid']) ? $firstPrevue['vid'] : '',
                                        'url'        => '',
                                    ];
                                    $num++;
                                }
                            }
                        }
                    }
                }
            }
            //设置文件缓存
            $this->setCacheData($params, $resData);
        }
        return $resData;
    }

    /**
     * 手Q发现页，文章3、4之间推荐
     */
    public function getDiscoveryRecommend()
    {
        $time = time();
        $data = $this->model('Mqq')->getDiscoveryRecommend();
        $res = [];
        if (!empty($data) && ($data['startTime'] <= $time && $data['endTime'] >= $time)) {
            //图片地址处理
            if (!empty($data['pic'])) {
                $data['pic'] = CDN_APPNFS . $data['pic'];
            }
            unset($data['startTime']);
            unset($data['endTime']);
            $res = $data;
        }
        return $res;
    }

}