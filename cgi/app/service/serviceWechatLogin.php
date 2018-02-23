<?php
namespace sdkService\service;

use sdkService\helper\Utils;


class serviceWechatLogin extends serviceBase
{
    const WXOPENID = 'WxOpenId';    //加密openid名
    const WXUNIONID = 'WxUnionId';  //加密unionid名
    const UNIONID_KEY = 'unionid';    //用户明文unionid
    const REFRESH_TOKEN_KEY = '_SESSION_ID_PIAO_QQ_COM_';   //refresh token
    const STATE_KEY = '_di_nepo_';  //给JS 统计PV UV 使用 不知道有没有用
    const OPEN_ID_EXPIRE = 3600;    //openid 一小时
    const REFRESH_TOKEN_EXPIRE = 604800;    //7天
    const UNIONID_EXPIRE = 864000;  //10天，确保从cookie里面能娶到unionid，设置的时间比refreshtoken还长
    const UUID_KEY = 'uuid';    //用户一次流程中唯一编号的key
    const COOKIE_PATH = '/';
    const COOKIE_DOMAIN = '.wepiao.com';
    const SNSAPI_USERINFO = 'snsapi_userinfo';

    const WXAPPMPTOKEN_EXPIRE = 7000;//小程序acctoken 过期时间

    public $saveToken = 1; //是否保存accessToken
    private $saveCookie = 0; //是否保存用户cookie，只在微信电影票项目调用时保存

    public function __construct($arrInput = [])
    {
        $isFromWx = $this->getParam($arrInput, 'fromWx', 0); //代表是否是微信电影票项目的调用
        $this->saveCookie = $this->getParam($arrInput, 'saveCookie', $isFromWx);
        $this->saveToken = $this->getParam($arrInput, 'saveToken', $this->saveToken);

    }

    /**
     * login
     *
     * @param   string  code                认证码
     * @param   string  _client_redirect_   跳转地址
     * @param   string  channelId           渠道编号
     * @param   string  scope               是否弹框获取用户详细信息，需要时传入snsapi_userinfo，默认snsapi_base(静默授权)
     * @return array
     */
    public function login($arrParams)
    {
        //设置uuid;
        $this->setuuid();
        $arrParams['scope'] = $this->getParam($arrParams, 'scope', 'snsapi_base');
        //取回调地址兼容两种参数名
        $arrParams['_client_redirect_'] = $this->getParam($arrParams, '_client_redirect_');
        if (empty($arrParams['_client_redirect_'])) {
            $arrParams['_client_redirect_'] = $this->getParam($arrParams, 'redirectUrl');
        }
        //传入force时跳过读cookie
        $force = $this->getParam($arrParams, 'force');

        $arrReturn = self::getStOut();
        if (isset($arrParams['code']) && !empty($arrParams['code'])) {

            $arrRet = $this->getOpenIdFromWeChat($arrParams);
            //openid unionid 加密
            if (isset($arrRet['openid'])) {
                $arrWxOpenId = $this->service('common')->encrypt([
                    'str' => $arrRet['openid'],
                    'channelId' => $arrParams['channelId']
                ]);
                $strWxOpenId = $arrWxOpenId['data']['encryptStr'];
                $arrRet[self::WXOPENID] = $strWxOpenId;
                //增加加入unionId的逻辑
                if (!empty($arrRet['unionid'])) {
                    $arrWxUnionId = $this->service('common')->encrypt([
                        'str' => $arrRet['unionid'],
                        'channelId' => $arrParams['channelId'],
                        't' => time() + self::REFRESH_TOKEN_EXPIRE + self::REFRESH_TOKEN_EXPIRE,
                    ]);
                    $strWxUnionId = $arrWxUnionId['data']['encryptStr'];
                    $arrRet[self::WXUNIONID] = $strWxUnionId;
                } else {
                    $arrRet[self::WXUNIONID] = '';
                    $strWxUnionId = '';
                }
                // set cookie
                $arrCookie = [
                    [
                        'key' => self::WXOPENID,
                        'value' => $strWxOpenId,
                        'expire' => time() + self::OPEN_ID_EXPIRE,
                        'path' => self::COOKIE_PATH,
                        'domain' => self::COOKIE_DOMAIN
                    ],
                    //cookie中增加一个加密的unionid
                    [
                        'key' => self::WXUNIONID,
                        'value' => $strWxUnionId,
                        'expire' => time() + self::REFRESH_TOKEN_EXPIRE + self::REFRESH_TOKEN_EXPIRE,
                        'path' => self::COOKIE_PATH,
                        'domain' => self::COOKIE_DOMAIN
                    ],
                    [
                        'key' => self::REFRESH_TOKEN_KEY,
                        'value' => $arrRet['refresh_token'],
                        'expire' => time() + self::REFRESH_TOKEN_EXPIRE,
                        'path' => self::COOKIE_PATH,
                        'domain' => self::COOKIE_DOMAIN
                    ],
                    [
                        'key' => self::STATE_KEY,
                        'value' => $arrRet['openid'],
                        'expire' => time() + self::OPEN_ID_EXPIRE + 31536000,
                        'path' => self::COOKIE_PATH,
                        'domain' => self::COOKIE_DOMAIN
                    ],

                ];
                // 存unionid
                if (isset($arrRet['unionid'])) {
                    $arrItem = [
                        'key' => self::UNIONID_KEY,
                        'value' => $arrRet['unionid'],
                        'expire' => time() + self::UNIONID_EXPIRE,
                        'path' => self::COOKIE_PATH,
                        'domain' => self::COOKIE_DOMAIN
                    ];
                    array_push($arrCookie, $arrItem);
                }
                $this->batchSetCookie($arrCookie);
                unset($arrRet['access_token']);
                unset($arrRet['refresh_token']);
            }
            if (empty($arrRet)) {
                $arrReturn['ret'] = ERROR_RET_WECHAT_LOGIN;
                $arrReturn['sub'] = ERROR_RET_WECHAT_LOGIN;
                $arrReturn['msg'] = ERROR_MSG_WECHAT_LOGIN;
            } else {
                $arrReturn['data'] = $arrRet;
            }
        } else {
            //用户信息暂时不存入cookie，因此弹框授权时暂不读cookie
            if ($arrParams['scope'] != self::SNSAPI_USERINFO && empty($force)) {
                $arrReturn = $this->getOpenidFromCookie($arrParams);
            }
            if (empty($arrReturn['data'])) {
                $arrReturn = self::getStOut();
                $arrReturn['data']['_client_redirect_'] = $this->service('wechat')->get302Url($arrParams['_client_redirect_'],
                    $arrParams['scope']);
            }
        }
        #//尽量保证logincheck的最小依赖，因此先注释
        #//有openid但是没有unionid返回时，尝试从用户中心取unionid，取到则加密后放入cookie
        #if (!empty($arrReturn[self::WXOPENID]) && empty($arrReturn['data'][self::WXUNIONID])) {
        #    $unionId = $this->getUnionidFromUserCenter($arrReturn['data']['openid']);
        #    if (!empty($unionId)) {
        #        $arrWxUnionId = $this->service('common')->encrypt(['str' => $unionId, 'channelId' => $arrParams['channelId']]);
        #        $WxUnionId = $arrWxUnionId['data']['encryptStr'];
        #        // set cookie
        #        $arrCookie = [
        #            [
        #                'key' => self::WXUNIONID,
        #                'value' => $WxUnionId,
        #                'expire' => time() + self::OPEN_ID_EXPIRE,
        #                'path' => self::COOKIE_PATH,
        #                'domain' => self::COOKIE_DOMAIN
        #            ],
        #        ];
        #        $this->batchSetCookie($arrCookie);
        #    }
        #}
        return $arrReturn;
    }

    /**
     * 从cookie中获取openid
     *
     * @param string channelId 渠道编号
     *
     * @return array
     */
    public function getOpenidFromCookie(array $arrInput = [])
    {
        $iChannelId = self::getParam($arrInput, 'channelId', 0);
        $return = self::getStOut();
        $arrRet = [];
        if (!empty($_COOKIE[self::WXOPENID])) {
            $arrOpenId = $this->service('common')->decrypt([
                'str' => $_COOKIE[self::WXOPENID],
                'channelId' => $iChannelId
            ]);
            if ($arrOpenId['ret'] == 0) {
                $arrRet['openid'] = $arrOpenId['data']['decryptStr'];
                $arrRet[self::WXOPENID] = $_COOKIE[self::WXOPENID];
                if (!empty($_COOKIE[self::WXUNIONID])) {
                    $arrUnionId = $this->service('common')->decrypt([
                        'str' => $_COOKIE[self::WXUNIONID],
                        'channelId' => $iChannelId
                    ]);
                    $arrRet['unionid'] = ($arrUnionId['ret'] == 0) ? $arrUnionId['data']['decryptStr'] : '';
                    $arrRet[self::WXUNIONID] = $_COOKIE[self::WXUNIONID];
                } else {
                    $arrRet['unionid'] = $arrRet[self::WXUNIONID] = '';
                }
            }

        } elseif (!empty($_COOKIE[self::REFRESH_TOKEN_KEY])) {
            $arrRefresh = $this->service('wechat')->getOpenIdByRefreshToken($_COOKIE[self::REFRESH_TOKEN_KEY]);
            if (isset($arrRefresh['openid'])) {
                $arrRet['openid'] = $arrRefresh['openid'];
                $arrRet['unionid'] = !empty($_COOKIE[self::UNIONID_KEY]) ? $_COOKIE[self::UNIONID_KEY] : '';

                $arrWxOpenIdServiceRe = $this->service('common')->encrypt([
                    'str' => $arrRefresh['openid'],
                    'channelId' => $iChannelId
                ]);
                if ($arrWxOpenIdServiceRe['ret'] == 0) {
                    $strWxOpenId = $arrWxOpenIdServiceRe['data']['encryptStr'];
                } else {
                    $strWxOpenId = '';
                }

                if (!empty($arrRet['unionid'])) {
                    $arrWxUnionIdServiceRe = $this->service('common')->encrypt([
                        'str' => $arrRet['unionid'],
                        'channelId' => $iChannelId,
                        't' => time() + self::REFRESH_TOKEN_EXPIRE + self::REFRESH_TOKEN_EXPIRE,
                    ]);
                    if ($arrWxUnionIdServiceRe['ret'] == 0) {
                        $strWxUnionId = $arrWxUnionIdServiceRe['data']['encryptStr'];
                    } else {
                        $strWxUnionId = '';
                    }
                } else {
                    $strWxUnionId = '';
                }
                $arrRet[self::WXOPENID] = $strWxOpenId;
                $arrRet[self::WXUNIONID] = $strWxUnionId;

                //set cookie
                $arrCookieParams = [
                    [
                        'key' => self::WXOPENID,
                        'value' => $strWxOpenId,
                        'expire' => time() + self::OPEN_ID_EXPIRE,
                        'path' => self::COOKIE_PATH,
                        'domain' => self::COOKIE_DOMAIN
                    ],
                    [
                        'key' => self::WXUNIONID,
                        'value' => $strWxUnionId,
                        'expire' => time() + self::REFRESH_TOKEN_EXPIRE + self::REFRESH_TOKEN_EXPIRE,
                        'path' => self::COOKIE_PATH,
                        'domain' => self::COOKIE_DOMAIN
                    ],
                    [
                        'key' => self::STATE_KEY,
                        'value' => $arrRet['openid'],
                        'expire' => time() + self::OPEN_ID_EXPIRE,
                        'path' => self::COOKIE_PATH,
                        'domain' => self::COOKIE_DOMAIN
                    ],
                ];
                $this->batchSetCookie($arrCookieParams);
            }

        } else {
            $arrRet = [];
        }
        if (empty($arrRet)) {
            $return['ret'] = -1;
            $return['sub'] = -1;
        }
        $return['data'] = $arrRet;

        return $return;
    }

    /**
     * @param $code
     * @return mixed
     */
    public function getOpenIdFromWeChat($arrParams)
    {
        $code = self::getParam($arrParams, 'code');
        $scope = self::getParam($arrParams, 'scope');
        $arrReturn = $this->service('wechat')->getOpenIdByCode($code);
        //获取用户信息，头像昵称unionid
        if ($scope == self::SNSAPI_USERINFO) {
            $arrUserinfo = $this->service('wechat')->getUserInfoBySnsapi($arrReturn);
            $arrUserinfo = [
                'nickname' => $arrUserinfo['nickname'],
                'headimgurl' => $arrUserinfo['headimgurl'],
                'unionid' => $arrUserinfo['unionid'],
            ];
            $arrReturn = array_merge($arrReturn, $arrUserinfo);
        }
        return $arrReturn;
    }

    /**
     * @param array $arrParams
     * 批量setcookie
     * [
     *  ['key'=>'test','value'=>'ttt','expire'=>3600,'path'=>'/','domain'=>'']
     * ]
     */
    public function batchSetCookie($arrParams = [])
    {
        //有保存标志时才保存cookie
        if (!empty($this->saveCookie)) {
            foreach ($arrParams as $arrCookie) {
                setcookie($arrCookie['key'], $arrCookie['value'], $arrCookie['expire'], $arrCookie['path'],
                    $arrCookie['domain']);
            }
        }
    }

    public function setuuid()
    {
        //添加uuid，session会话丢失，则uuid丢失，下次会变更。它不是用户终身唯一id，而是用户交互行为中的id。
        if (empty($_COOKIE['uuid'])) {
            $uuid = Utils::uuid('weixin-');
            $this->batchSetCookie([
                [
                    'key' => self::UUID_KEY,
                    'value' => $uuid,
                    'expire' => 0,
                    'path' => self::COOKIE_PATH,
                    'domain' => self::COOKIE_DOMAIN
                ]
            ]);
        }
    }

    /**
     * 从用户中心获取用户unionId
     */
    public function getUnionidFromUserCenter($openId)
    {
        $unionId = '';
        $response = $this->service('user')->getIdRelation(['id' => $openId]);
        if ($response['ret'] == 0 && $response['sub'] == 0) {
            foreach ($response['data']['idRelation']['idUnderBound'] as $item) {
                if ($item['idType'] == 30) {  //取对应的unionid
                    $unionId = $item['id'];
                    return $unionId;
                }
            }
        }
        return $unionId;
    }

    /**
     * 微信小程序login
     */
    public function wxappLogin($arrInput = [])
    {
        $arrReturn = self::getStOut();
        $strCode = $arrInput['code'];
        $subWxapp = !empty($arrInput['subWxapp']) ? $arrInput['subWxapp'] : 0;
        $arrRet = $this->service('wechat')->getOpenIdByWxappCode($strCode,$arrInput['channelId'],$subWxapp);
        if (isset($arrRet['openid'])) {
            $arrWxOpenId = $this->service('common')->encrypt([
                'str' => $arrRet['openid'],
                'channelId' => $arrInput['channelId']
            ]);
            $strWxOpenId = $arrWxOpenId['data']['encryptStr'];
            $arrRet[self::WXOPENID] = $strWxOpenId;
        }
        if (!empty($arrRet['session_key']) && !empty($this->service('WeChatLogin')->saveToken)) {
            $this->model('WeChat')->saveWxappUserToken($arrRet['openid'], $arrRet['session_key']);
        }

        if (empty($arrRet)) {
            $arrReturn['ret'] = ERROR_RET_WECHAT_LOGIN;
            $arrReturn['sub'] = ERROR_RET_WECHAT_LOGIN;
            $arrReturn['msg'] = ERROR_MSG_WECHAT_LOGIN;
        } else {
            $arrReturn['data'] = $arrRet;
        }
        return $arrReturn;
    }

    /**
     * 微信小程序获取公众号accesstoken
     */
    public function getWxappMptoken($arrInput = [])
    {
        $arrReturn = self::getStOut();
        $iChannelId = $arrInput['channelId'];
        $strAccesstoken = $this->model('wxapp')->readMptoken($iChannelId);
        if (empty($strAccesstoken)) {
            $arrRet = $this->service('wechat')->getWxappMptoken($iChannelId);
            if (!empty($arrRet['access_token'])) {
                $strAccesstoken = $arrRet['access_token'];
                $this->model('wxapp')->setMptoken($iChannelId, $strAccesstoken, self::WXAPPMPTOKEN_EXPIRE);
            } else {
                $strAccesstoken = '';
            }
        }
        if (empty($strAccesstoken)) {
            $arrReturn['ret'] = ERROR_RET_WXAPP;
            $arrReturn['sub'] = ERROR_RET_WXAPP;
            $arrReturn['msg'] = ERROR_MSG_WXAPP;
        } else {
            $arrReturn['data']['access_token'] = $strAccesstoken;
        }
        return $arrReturn;
    }

    /*
     * 从redis获取微信用户access_token
     * @param string $openid 用户openid
     */
    public function getWxUserToken($arrInput = [])
    {
        $arrReturn = self::getStOut();
        $openId = $arrInput['openId'];
        $accessToken = $this->model('WeChat')->getWxUserToken($openId);
        if (empty($accessToken)) {
            $arrReturn['ret'] = ERROR_RET_WXAPP;
            $arrReturn['sub'] = ERROR_RET_WXAPP;
            $arrReturn['msg'] = ERROR_MSG_WXAPP;
        } else {
            $arrReturn['data']['access_token'] = $accessToken;
        }
        return $arrReturn;
    }
    
    /*
     * 从redis获取微信用户access_token
     * @param string $openid 用户openid
     */
    public function getWxappUserToken($arrInput = [])
    {
        $arrReturn = self::getStOut();
        $openId = $arrInput['openId'];
        $accessToken = $this->model('WeChat')->getWxappUserToken($openId);
        if (empty($accessToken)) {
            $arrReturn['ret'] = ERROR_RET_WXAPP;
            $arrReturn['sub'] = ERROR_RET_WXAPP;
            $arrReturn['msg'] = ERROR_MSG_WXAPP;
        } else {
            $arrReturn['data']['access_token'] = $accessToken;
        }
        return $arrReturn;
    }

    /**
     * 通过access_token获取用户信息
     * @param string $openid 用户openid
     * @param string $access_token 用户access_token，非必传，不传时通过openid获取
     * @return array
     */
    public function getUserinfoByAccessToken($arrInput)
    {
        $arrReturn = self::getStOut();
        $openId = self::getParam($arrInput, 'openId');
        $accessToken = self::getParam($arrInput, 'access_token');
        $accessToken = !empty($accessToken) ? $accessToken : $this->model('wechat')->getWxUserToken($openId);
        $arrParam = [
            'access_token' => $accessToken,
            'openid' => $openId,
        ];
        $arrUserinfo = $this->service('wechat')->getUserInfoBySnsapi($arrParam);
        if (!empty($arrUserinfo)) {
            $arrReturn['data'] = [
                'nickname' => $arrUserinfo['nickname'],
                'headimgurl' => $arrUserinfo['headimgurl'],
                'unionid' => $arrUserinfo['unionid'],
            ];
        } else {
            $arrReturn = [
                'ret' => -1,
                'sub' => -1,
                'msg' => 'get userinfo error!',
            ];
        }
        return $arrReturn;
    }
}
