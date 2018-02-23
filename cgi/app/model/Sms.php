<?php
/**
 * 短信、验证码相关
 */
namespace sdkService\model;


    /* 短信、验证码相关 */
/***** 暂时使用，后续要转移至apiUrl.php中 *****/
defined('SDK_SMS_APIHOST') or define('SDK_SMS_APIHOST', 'http://sms.grammy.wxmovie.com:80');
/***** 暂时使用，后续要转移至commonConstant.php中 *****/
defined('SDK_SMS_SEND') or define('SDK_SMS_SEND', SDK_SMS_APIHOST . '/sms/api/send');   //接口地址
defined('SDK_SMS_CONTENT') or define('SDK_SMS_CONTENT', '%1$d娱票儿验证码，%2$d分钟内有效。');   //短信模板内容
defined('SDK_SMS_VALIED') or define('SDK_SMS_VALIED', 30);   //短信验证码有效期：分钟

class Sms extends BaseNew
{

    //手机验证码key的后缀
    public $suffix = '_sms';
    private $iLimitCount = 3;//1分钟只能发送一条
    private $channel = [ //渠道appkey配置
        '9' => ['secret' => 'zJwaQBQ553lHr6DfnX02WcJtZF',],//android
        '8' => ['secret' => 'DFgoKYQPfJegWQN3ofcTFg6qfn',],//ios
        '10' => ['secret' => 'jsIa9jL10Vxa9HMlEb9E4Fa15f',],//pc
        '3' => ['secret' => 'jsIa9jL10Vxa9HMl1c0f517245',],//3仅供初期测试，后期会删除
    ];

    private $channelId;

    public function __construct($arrInput)
    {
        $this->channelId = self::getParam($arrInput, 'channelId');

    }

    public function initRedis()
    {
        try {
            //redis连接
            return $this->redis($this->channelId, USER_GENERATED_CONTENT);
        } catch (\Exception $e) {
            //日志记录
            //wepiao::info('[error] ' . $e);
            $rst = [
                'errorcode' => -191001,
                'msg' => $e->getMessage(),
            ];
            return $rst;
        }
    }

    /**
     * 发送验证码
     * 30分钟有效
     */
    public function sendSmsCode($arrInput = array())
    {
        $checkcode = mt_rand(1000, 9999);   // 生成随机数验证码
        $content = sprintf(SDK_SMS_CONTENT, $checkcode, SDK_SMS_VALIED);
        $arrHttpSend = [];
        $arrHttpSend['phone_number'] = $arrInput['phone_number'];
        $arrHttpSend['content'] = $content;
        //因为PC等渠道的短信也是调用的APP的短信接口，所以需要判断msg_kind参数，做兼容处理
        $arrHttpSend['msg_kind'] = $this->getParam($arrInput, 'msg_kind', 'auth');
        $arrHttpSend['client_id'] = $this->getParam($arrInput, 'client_id', 'app_login');

        //读取限制 一分钟只能发送一条
        $iFlag = $this->readLimitStatus($arrHttpSend['phone_number']);
        if ($iFlag) {
            return ['errorcode' => -191005, 'result' => []];
        }
        //

        //用户10分钟内尝试发送10次以上，冻结该手机号半小时
        $arrSmsInfo = $this->getSmsInfo($arrHttpSend['phone_number']);
        //在10分钟之内可以最多发送10条短信
        if (empty($arrSmsInfo) || $arrSmsInfo['send_count'] < 10 || $arrSmsInfo['current_at'] < (time() - 30 * 60)) {
            $params['sMethod'] = 'post';
            $params['arrData'] = $arrHttpSend;
            $data = $this->http(SDK_SMS_SEND, $params);
        } else {
            return ['errorcode' => -191005, 'result' => []];
        }
        $return = [];
        if (isset($data['ret']) && $data['ret'] == 0) {
            //10分钟之内current_at保存第一次的发送的时间，10分钟过后保存过后的第一次发送时间
            $current_at = time();
            if (empty($arrSmsInfo)) {
                $send_count = 1;
            } elseif ($arrSmsInfo['current_at'] > $current_at - 10 * 60) {
                $current_at = ($arrSmsInfo['send_count'] == 9) ? $current_at : $arrSmsInfo['current_at'];
                $send_count = $arrSmsInfo['send_count'] + 1;
            } else {
                $send_count = 1; //10分钟之后将计数器重新置1
            }
            $this->saveCheckCode($arrInput['phone_number'], $checkcode, $current_at, $send_count);
        } else if (isset($data['ret']) && $data['ret'] == 1000) { //短信接口的限制：All phone numbers had been shielded!
            return ['errorcode' => -191005, 'result' => []];
        }
        //整理返回结果，使得返回结果和更换PHP下层接口之前保持兼容
        $return['ret'] = $return['sub'] = $data['ret'];
        $return['msg'] = $data['msg'];
        return ['errorcode' => 0, 'result' => $return];
    }

    /** 读取手机号发送限制情况
     * @param $strPhoneNum
     * @return bool
     */
    public function readLimitStatus($strPhoneNum)
    {
        $strKey = "msg_" . $strPhoneNum;
        $iCount = $this->initRedis()->WYincr($strKey);
        if ($iCount == 1) {
            $this->initRedis()->WYexpire($strKey, 60);
        }
        if ($iCount > $this->iLimitCount) {
            return true;
        }
        return false;
    }

    /**
     * 验证码记入 redis，默认存储30分钟
     */
    public function saveCheckCode($mobile, $checkcode, $current_at, $send_count)
    {
        $intExpired = time() + 60 * 60; //键的生存期
        $expired_at = time() + SDK_SMS_VALIED * 60; //短信的有效期
        $arrSmsInfo = [
            'mobile' => $mobile,
            'checkcode' => $checkcode,
            'current_at' => $current_at,
            'expired_at' => $expired_at,
            'send_count' => $send_count,
            'check_count' => 0, //短信验证码次数
        ];
        //将验证码保存至redis
        $key = $mobile . $this->suffix;
        $pattern = json_encode($arrSmsInfo);
        $this->initRedis()->WYset($key, $pattern, $intExpired);
    }

    /**
     * 验证手机号与验证码，以及图片验证码
     *
     * @param   array $arrInput 发送参数，有3个Key，phone_number、code、piccode，分别代表手机号、验证码和图片验证码
     *
     * @return  array  $data        返回内容
     */
    public function verifySmsCode(array $arrInput = array())
    {
        //定义返回内容
        $data = ['errorcode' => 0, 'result' => []];

        //从redis获取手机验证码信息
        $strMobileNumKey = $arrInput['phone_number'] . $this->suffix;
        $strCode = $arrInput['code'];
        $arrSmsInfo = $this->getSmsInfo($arrInput['phone_number']);
        #Yii::info('smsinfo' . json_encode($arrSmsInfo) . 'verifyinfo:' . json_encode($arrInput));

        //验证redis的短信验证信息是否有效
        if (empty($arrSmsInfo)) {
            $data['errorcode'] = -191002;
        } else {
            $strMobileNum = strval($arrInput['phone_number']);
            /*判断验证码是否正确
            *验证码验证错误超过10次,返回用户信息：验证频繁，请重新获取验证码
            *unset该验证码的键值，但是会造成发送短信那里，禁用30分钟失效的影响
            */
            if ($strCode != $arrSmsInfo['checkcode']) {
                if ($arrSmsInfo['check_count'] > 9) {
                    $data['errorcode'] = -191006;
                    //验证10次错误以上，销毁 验证码信息
                    $this->initRedis()->WYdelete($strMobileNumKey);
                } else {
                    //保存验证错误次数
                    $arrSmsInfo['check_count'] += 1;
                    $intExpired = time() + 60 * 60; //键的生存期
                    $strSmsInfo = json_encode($arrSmsInfo);
                    $this->initRedis()->WYset($strMobileNumKey, $strSmsInfo, $intExpired);
                    $data['errorcode'] = -191003;
                }
            } //判断是否过期
            elseif (time() > $arrSmsInfo['expired_at']
            ) {
                $data['errorcode'] = -191004;
            } else {
                $intTime = time();
                $result = [
                    'data' => [
                        't' => $intTime,
                        'phone_number' => $strMobileNum,
                    ]
                ];
                //此处的sign是为了让客户端知道数据确实是服务端返回的
                $signChannel = self::getParam($arrInput, 'publicSignalShort', '3');
                $signChannel = array_key_exists($signChannel, $this->channel) ? $signChannel : 3; //不存在时取3
                $strSecret = $this->channel[$signChannel]['secret'];
                $sign = $this->makeSign(['t' => $intTime, 'phone_number' => $strMobileNum], $strSecret);
                $result['data']['sign'] = $sign;
                //验证成功之后，销毁 验证码信息
                $this->initRedis()->WYdelete($strMobileNumKey);
                $data['result'] = $result;
            }
        }
        #$data['errorcode'] = 0; //TODO 测试用，始终验证正确，需要后续删除
        return $data;
    }

    /**
     * 获取短信验证码内容存储内容
     * example：{"mobile":"18701210575","checkcode":1882,"expired_at":1431598691}
     */
    public function getSmsInfo($strMobileNo = '')
    {
        //参数判断
        $arrSmsInfo = array();
        if (empty($strMobileNo)) return $arrSmsInfo;
        //从redis获取结果
        $key = $strMobileNo . $this->suffix;
        $strSmsInfo = $this->initRedis()->WYget($key);
        $arrSmsInfo = !empty($strSmsInfo) ? json_decode($strSmsInfo, true) : array();
        return $arrSmsInfo;
    }

    /*
     * make weiying提供给渠道API sign
     */
    public static function makeSign($arrData = array(), $strAppSecret = '')
    {
        //$arrData = self::natksort($arrData);
        ksort($arrData);
        $strKey = urldecode(http_build_query($arrData));
        //echo $strKey;
        $strMd5 = MD5($strAppSecret . $strKey);
        return strtoupper($strMd5);
    }
}