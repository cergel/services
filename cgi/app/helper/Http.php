<?php


namespace sdkService\helper;

/**
 * Created by PhpStorm.
 * User: syj
 * Date: 14-10-13
 * Time: 下午4:04
 */
class Http
{

    private $mixHandle;

    private $iTimeout = 10;

    private static $objInstance = null;

    public function __construct()
    {

        $this->mixHandle = curl_init();
        curl_setopt($this->mixHandle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->mixHandle, CURLOPT_TIMEOUT, $this->iTimeout);
        curl_setopt($this->mixHandle, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($this->mixHandle, CURLOPT_HEADER, 0);
        //curl_setopt($this->mixHandle, CURLOPT_VERBOSE, 1);
        curl_setopt($this->mixHandle, CURLOPT_RETURNTRANSFER, TRUE);
    }

    public static function instance()
    {

        if (empty(self::$objInstance)) {
            self::$objInstance = new Http();
        }
        return self::$objInstance;
    }

    public function setRefer($strRefer)
    {
        curl_setopt($this->mixHandle, CURLOPT_REFERER, $strRefer);
    }

    public function setTimeout($iTime = 10)
    {
        $this->iTimeout = $iTime;
        curl_setopt($this->mixHandle, CURLOPT_TIMEOUT, $this->iTimeout);
    }

    public function setCookie($strCookie)
    {
        curl_setopt($this->mixHandle, CURLOPT_COOKIE, $strCookie);
    }

    public function setHttpsRequest()
    {
        curl_setopt($this->mixHandle, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
        curl_setopt($this->mixHandle, CURLOPT_SSL_VERIFYHOST, 0); // 检查证书中是否设置域名
    }

    /*
     * $arrProxy = array('host'=>'127.0.0.1','port'=>123,'username'=>'oauth','password'=>'aa')
     */
    public function setProxy($arrProxy = array())
    {
        curl_setopt($this->mixHandle, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        curl_setopt($this->mixHandle, CURLOPT_PROXY, $arrProxy['host']);
        curl_setopt($this->mixHandle, CURLOPT_PROXYPORT, $arrProxy['port']);
        if (isset($arrProxy['username'])) {
            curl_setopt($this->mixHandle, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
            $user = "{$arrProxy['username']}:{$arrProxy['password']}";
            curl_setopt($this->mixHandle, CURLOPT_PROXYUSERPWD, $user);
        }
    }

    public function setHttpHeader($arrHeader = array())
    {
        curl_setopt($this->mixHandle, CURLOPT_HTTPHEADER, $arrHeader);
    }

    public function get($strUrl)
    {
        curl_setopt($this->mixHandle, CURLOPT_URL, $strUrl);
        $startTime = microtime(true);
        $mixResponse = curl_exec($this->mixHandle);
        $endTime = microtime(true);
        //$iHttpCode = curl_getinfo($this->mixHandle, CURLINFO_HTTP_CODE);
        $info = curl_getinfo($this->mixHandle);
        $httpCode = $info['http_code'];
        $httpErrMsg = ($httpCode!=200) ? curl_error($this->mixHandle) : '';
        $httpErrNo = ($httpCode!=200) ? curl_errno($this->mixHandle) : '';
        $totalTime = $endTime - $startTime;
        $totalTime = round($totalTime*1000).'ms';
        return array('http_code' => $httpCode, 'response' => $mixResponse , 'total_time'=>$totalTime ,'http_errmsg'=>$httpErrMsg,'http_errno'=>$httpErrNo);
    }

    public function post($strUrl, $strData)
    {
        curl_setopt($this->mixHandle, CURLOPT_POST, true);
        curl_setopt($this->mixHandle, CURLOPT_URL, $strUrl);
        curl_setopt($this->mixHandle, CURLOPT_POSTFIELDS, $strData);
        $startTime = microtime(true);
        $mixResponse = curl_exec($this->mixHandle);
        $endTime = microtime(true);
        $info = curl_getinfo($this->mixHandle);
        $httpCode = $info['http_code'];
        $httpErrMsg = ($httpCode!=200) ? curl_error($this->mixHandle) : '';
        $httpErrNo = ($httpCode!=200) ? curl_errno($this->mixHandle) : '';
        $totalTime = $endTime - $startTime;
        $totalTime = round($totalTime*1000).'ms';
        //\Logger::getInstance()->addNode('requestOther', ['url' => $strUrl, 'params'=>$strData, 'http_code' => $httpCode, 'response' => $mixResponse , 'total_time'=>$totalTime ,'http_errmsg'=>$httpErrMsg ]);
        return array('http_code' => $httpCode, 'response' => $mixResponse , 'total_time'=>$totalTime ,'http_errmsg'=>$httpErrMsg ,'http_errno'=>$httpErrNo,'data'=>$strData);
    }

    public function error()
    {
        return curl_error($this->mixHandle);
    }


    /**
     * 当传递方式为get时，拼装地址栏参数
     *
     */
    public function _urlType($arrData)
    {
        $strData = http_build_query($arrData);
        return $strData;
    }


    //当传送类型为普通的表单方式时，处理数据的方法
    /**
     * @param $arrData 待组合的数据
     * @return string
     */
    public function _formType($arrData)
    {
        $strData = http_build_query($arrData);
        return urldecode($strData);
    }

    //当传送类型为json时，处理数据的方法
    /**
     * @param $arrData 请求的数据
     */
    public function _jsonType($arrData)
    {
        return json_encode($arrData);
    }

    /**
     * 不做中文转义
     * @param $arrData
     * @return string
     */
    public function _jsonUnicodeType($arrData)
    {
        return json_encode($arrData, JSON_UNESCAPED_UNICODE);
    }

}