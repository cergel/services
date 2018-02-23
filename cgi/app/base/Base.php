<?php
/**
 * Created by PhpStorm.
 * User: xiangli
 * Date: 2016/6/30
 * Time: 14:57
 */

namespace sdkService\base;
use sdkService\helper;
use vendor\logger\Logger;

class Base
{
    /**
     * @param string $sUrl 要访问的地址
     * @param array 可配置的参数，也可用默认值
     * @param bool 是否传递X-request-id, 默认开启
     * @return array|bool|mixed
     */
    public function http($sUrl, $params = [], $isPostRequestId = true)
    {
        if (empty($sUrl)) {
            return false;
        }

        $arrData = isset($params['arrData']) ? $params['arrData'] : [];//要发送的数据
        $sMethod = isset($params['sMethod']) ? $params['sMethod'] : 'GET';//访问方法
        $iTryTimes = isset($params['iTryTimes']) ? $params['iTryTimes'] : 1;//尝试次数
        $iTimeout = isset($params['iTimeout']) ? $params['iTimeout'] : 5;//超时时间
        $initJson = isset($params['initJson']) ? $params['initJson'] : true;//结果是否进行json_decode
        $sendType = isset($params['sendType']) ? $params['sendType'] : 'form';//使用何种方式进访问(from,json....)
        $isHttps = isset($params['isHttps']) ? true : false;//
        $jsonUnicode = isset($params['jsonUnicode']) ? $params['jsonUnicode'] : false;
        $strCookies = !empty($params['arrCookies']) ? $this->_formatCurlCookie($params['arrCookies']) : '';


        //wepiao::info('[http_request] url:' . $sUrl . ' params :' . $strJson);

        $requestId = $this->getReqiuseId();

        $http = new helper\Http();

        //设置cookie
        if (!empty($strCookies)) {
            $http->setCookie($strCookies);
        }

        //增加request header头信息
        $arrHeader = [
            'requestId: ' . $requestId,//在头部也传requestId
            'X-Request-Id: ' . $requestId,//在头部也传requestId,以后都得用这个
            'charset=utf-8',
            'User-Ip: ' . helper\Net::getRemoteIp(), //用户ip，后端记录用
        ];
        //使用传参判断请求渠道获取相关的信息以及设备号
        $this->getDeviceFromParams($arrHeader);
        //增加需要传到后端的Header信息(如果值为空则不传 app目前放到请求体中了)
        $arrParts = [
            //'X-User-IDFA',
            //'X-User-IMEI',
            'X-User-MAC',
        ];
        $arrHeader = array_merge($arrHeader, $this->getHeaderParts($arrParts));
        //过滤重复的设备值
        $arrHeader = array_values(array_unique($arrHeader));
        //设置curl调用别人接口的时候最大等待时间
        $http->setTimeout($iTimeout);
        if ($isPostRequestId) {
            $arrData['requestId'] = $requestId;//在参数里也传requestId
        }
        if ($sMethod == 'GET' && !empty($arrData)) {
            if (!empty($arrData)) {
                $strData = $http->_urlType($arrData);
                //如果原来的URL中,已经有了?,则参数追加,如果原来没有,则追加参数的时候,带上?
                if (strpos($sUrl, '?') !== false) {
                    $sUrl .= "&" . $strData;
                } else {
                    $sUrl .= '?' . $strData;
                }
            }
        } else {
            //contentType
            switch ($sendType) {
                case 'form':
                    $strData = $http->_formType($arrData);
                    break;
                case 'json':
                    $arrHeader[] = 'Content-Type:application/json';
                    if ($jsonUnicode) {
                        $strData = $http->_jsonUnicodeType($arrData);
                    } else {
                        $strData = $http->_jsonType($arrData);
                    }
                    break;
                default :
                    $strData = $http->_formType($arrData);
            }
        }
        $http->setHttpHeader($arrHeader);

        if ($isHttps == false) {
            $http->setHttpsRequest();//设置此项，不去检查https网站证书安全性
        }

        // 尝试
        $intTryTimes = 0;
        $response = [];
        for ($i = 0; $i < $iTryTimes; $i++) {
            ++$intTryTimes;
            if (strtoupper($sMethod) == 'GET') {
                $response = $http->get($sUrl);
            } else {
                $response = $http->post($sUrl, $strData);
            }
            if ($response['response'] !== false) {
                break;
            }
        }
        if ($initJson) {
            $response_data = json_decode($response['response'], true);
            $response_data = empty($response_data) ? $response['response'] : $response_data;
        } else {
            $response_data = $response['response'];
        }
        Logger::getInstance()->addRequestOtherNode([
            'url' => $sUrl,
            'method' => $sMethod,
            'data' => $strData,
            'cookie' => $strCookies,
            'http_time_spend' => isset($response['total_time']) ? $response['total_time'] : null,
            'http_code' => $response['http_code'],
            'curl_error_no' => isset($response['http_errno']) ? $response['http_errno'] : null,
            'curl_error_msg' => isset($response['http_errmsg']) ? $response['http_errmsg'] : null,
            'return' => $response_data,
        ]);
        if ($response['http_code'] == '200') {
            return $response_data;
        } else {
            $arrReturn = array('ret' => '50401', 'sub' => '50401', 'msg' => $response['http_code'] . $response['http_errmsg']);
            return $arrReturn;
        }
    }

    /**
     * 获取请求头的requeseId ,目前自己用随机数模拟
     */
    public function getReqiuseId()
    {
        $requestId = helper\Utils::getRequestId();
        $serverId = isset($_SERVER['SERVER_ADDR']) ? ip2long($_SERVER['SERVER_ADDR']) : gethostname();
        $requestId = !empty($requestId) ? $requestId : $serverId . '_' . time() . '_' . uniqid();
        if (isset(\wepiao::$input['X-Request-Id'])) {
            $requestId = \wepiao::$input['X-Request-Id'];
        }
        return $requestId;
    }

    /**
     * 发送HTTP请求之前判断参数是否是APP以及APP是否携带设备请求信息
     * @param $arrHeader
     */
    public function getDeviceFromParams(&$arrHeader)
    {
        if ((!empty($_REQUEST['appkey'])) && $_REQUEST['appkey'] == 8) {
            $idfa = empty($_REQUEST['idfa']) ? '' : $_REQUEST['idfa'];
            $arrHeader[] = 'X-User-IDFA: ' . $idfa;
        }

        if ((!empty($_REQUEST['appkey'])) && $_REQUEST['appkey'] == 9) {
            $deviceid = empty($_REQUEST['deviceid']) ? '' : $_REQUEST['deviceid'];
            $arrHeader[] = 'X-User-DeviceId: ' . $deviceid;
        }
    }

    /**
     * 根据需要透传的header名，拼接为标准header形式
     * @param array $arrInput 包含需要透传的header名的数组，如['x-cip','x-forwarded-for']
     * @return array 包含标准header字符串形式的数组，如['x-cip: xxx','x-for: ooo']
     */
    public function getHeaderParts($arrInput = [])
    {
        $arrRes = [];
        foreach ($arrInput as $nameMiddle) {
            $nameDown = strtoupper(str_replace('-', '_', $nameMiddle)); //形如 X_FORWARDED_FOR
            $HeaderDown = 'HTTP_' . $nameDown; //形如 HTTP_X_FORWARDED_FOR
            if (!empty($_SERVER[$HeaderDown])) {
                //拼接为形如'Content-Type: text/html'的字符串
                $arrRes[] = $nameMiddle . ': ' . $_SERVER[$HeaderDown];
            }
        }
        return $arrRes;
    }

    /**
     * 判断一个数组中某个元素是否存在,如果存在,返回这个元素的值,否则返回默认类型
     * @param array $input
     * @param string $key
     * @param string $default
     */
    public static function getParam(array $input, $key = '', $default = '')
    {
        return isset($input[$key]) ? $input[$key] : $default;
    }

    //完全冗余方法，http就不应该在model调用
    private function _formatCurlCookie($arrCookie)
    {
        $strCookie = http_build_query($arrCookie);
        return str_replace('&', ';', $strCookie);
    }

    #################################################
    ####### 接口优化，数据保存为文件缓存 START #######
    #################################################
    ####### 注1：仅供首页正在上映影片列表等少数性能要求高的接口使用，使用时必须一读一写配合
    ####### 注2：config中有开关控制，CacheData
    /**
     * 读取文件缓存
     * @param string $params 请求参数，需要参与文件名校验，避免不同参数的结果弄混
     * @param int $expire 有效时间，默认5分钟
     * @param int $decodetype 默认0 json_decode对象，1->数组
     * @return array
     */
    public function getCacheData($params = '', $decodetype = 0,$expire = 300)
    {
        $switch = \wepiao::$config['params']['fileCacheData'];
        if (!empty($switch)) {
            $cacheFile = self::getCacheDataFilename($params);
            if (file_exists($cacheFile)) {
                $fmtime = @filemtime($cacheFile);
                if (time() - $fmtime < $expire) {
                    if($decodetype) {
                        $cacheData = json_decode(file_get_contents($cacheFile),true);
                    }else{
                        $cacheData = json_decode(file_get_contents($cacheFile));
                    }
                    $cacheData = !empty($cacheData) ? $cacheData : [];
                    return $cacheData;
                }
            }
        }
        return [];
    }

    /**
     * 保存数据为文件缓存
     * @param string $params 请求参数，需要参与文件名校验，避免不同参数的结果弄混
     * @param $cacheData
     */
    public function setCacheData($params = '', $cacheData)
    {
        $switch = \wepiao::$config['params']['fileCacheData'];;
        if (!empty($switch)) {
            $cacheFile = self::getCacheDataFilename($params);
            self::writeCacheFile($cacheFile, $cacheData);
            //概率性触发，清除缓存文件夹中超过过期时间的文件
            self::clearCacheFolder();
        }
    }

    /**
     * 获取缓存文件名
     * @param string $params 请求参数，需要参与文件名校验，避免不同参数的结果弄混
     * @return string 形如 /tmp/cacheData/Movie_getList_3022354800
     */
    private function getCacheDataFilename($params = '')
    {
        $cacheFolder = self::getCacheFolder();
        $cacheFilename = \wepiao::$request->serviceClass . '_' . \wepiao::$request->action;
        if (!empty($params)) {
            $params = '_' . crc32(json_encode($params));
        }
        $cacheFile = $cacheFolder . $cacheFilename . $params;
        return $cacheFile;
    }

    /**
     * 获取缓存文件夹，不存在则创建
     * @return string
     */
    private function getCacheFolder()
    {
        $cacheFolder = '/tmp/cacheData/';
        if (!is_dir($cacheFolder)) {
            @mkdir($cacheFolder, 0755);
        }
        return $cacheFolder;
    }

    /**
     * 概率性触发，清除缓存文件夹中超过过期时间的文件
     * @param int $expire
     */
    private function clearCacheFolder($expire = 300)
    {
        $cacheFolder = self::getCacheFolder();
        //概率触发，数字是随便填的
        if (rand(0, 3000) == 42) {
            $filesnames = @scandir($cacheFolder);
            foreach ($filesnames as $name) {
                $cacheFile = $cacheFolder . $name;
                $fmtime = @filemtime($cacheFile);
                if (time() - $fmtime > $expire) {
                    @unlink($cacheFile);
                }
            }
        }
    }

    /**
     * 写缓存文件，独占式，非阻塞
     * @param $filename
     * @param $data
     */
    private function writeCacheFile($filename, $data)
    {
        $fp = fopen($filename, 'w');
        //尝试获取独占锁，非阻塞。获取不到则什么都不做。（Windows上只能阻塞）
        if (flock($fp, LOCK_EX | LOCK_NB)) {
            if (is_array($data) || is_object($data)) {
                $data = json_encode($data);
            }
            fwrite($fp, $data);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }
    #################################################
    ####### 接口优化，数据保存为文件缓存 END #######
    #################################################
}