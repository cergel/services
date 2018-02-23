<?php

namespace sdkService\helper;

/**
 * Created by PhpStorm.
 * User: syj
 * Date: 14-9-26
 * Time: 下午4:45
 */

class Utils
{

    public static function makeRandStr($iLength, $str = '', $strPrefix = '1.0')
    {
        if (!$str) {
            $str = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        $randString = '';
        $len = strlen($str) - 1;
        for ($i = 0; $i < $iLength; $i++) {
            $str = str_shuffle($str);
            $num = mt_rand(0, $len);
            $randString .= $str[$num];
        }
        if ($strPrefix) {
            return $strPrefix . $randString;
        } else {
            return $randString;
        }
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

    public static function outPutJson($iRet = 0, $iSub = 0, $strMsg = '', $arrData = array(), $strKey = 'data')
    {
        $arrRet = array();
        $arrRet['ret'] = $iRet;
        $arrRet['sub'] = $iSub;
        $arrRet['msg'] = $strMsg;
        $arrRet[$strKey] = empty($arrData) ? new \stdClass() : $arrData;
        echo json_encode($arrRet);
        exit;
    }

    public static function go($strUrl)
    {
        header('Location:' . $strUrl);
        exit;
    }

    public static function redirectMessage($message = '成功', $status = 'success', $url = false, $time = 3)
    {

        $back_color = '#ff0000';

        if ($status == 'success') {
            $back_color = 'blue';
        }

        if ($url) {
            $url = "window.location.href='{$url}'";
        } else {
            $url = "history.back();";
        }
        echo <<<HTML
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <div>
            <div style="background:#C9F1FF; margin:0 auto; height:100px; width:600px; text-align:center;">
                        <div style="margin-top:50px;">
                        <h5 style="color:{$back_color};font-size:14px; padding-top:20px;" >{$message}</h5>
                        Waiting to redirect:&nbsp;&nbsp;<span id="sec" style="color:blue;">{$time}</span>
                        </div>
            </div>
        </div>
        <script type="text/javascript">
        function run(){
            var s = document.getElementById("sec");
            if(s.innerHTML == 0){
            {$url}
                return false;
            }
            s.innerHTML = s.innerHTML * 1 - 1;
        }
        window.setInterval("run();", 1000);
        </script>
HTML;
        exit;
    }

    /*
     * 数组按照key字典 自然排序
     */
    public static function natksort($arrData)
    {
        $r = array();
        foreach ($arrData as $k => $v) {
            $r[strtolower($k)] = $k;
        }

        $arrData = array_change_key_case($arrData);
        ksort($arrData);
        foreach ($arrData as $nk => $nv) {
            unset($arrData[$nk]);
            $arrData[$r[$nk]] = $nv;
        }

        return $arrData;
    }

    /*
     * 拼接参数
     */
    public static function makeLocationParams($arrData = array(), $strSecret = '')
    {
        if ($strSecret) {
            $arrData['sign'] = self::makeSign($arrData, $strSecret);
        }

        return urldecode(http_build_query($arrData));
    }

    /*
     * $strFlag 1 -减少  2-增加 3-转换（场区设置为01，否则Java那边锁座一个还行，锁座2个以上无法处理）
     * in ->1:5:07|1:6:05 out->5:07|6:05
     * in -> 5:07|6:05    out->1:5:07|1:6:05
     */
    public static function formatStr($str, $strFlag = 2, $note = "|")
    {
        $arrTmp = explode($note, $str);
        $arr = array();//1:4:08 去掉1  C++那边不要 区
        foreach ($arrTmp as $strV) {
            $arrTmp_2 = explode(":", $strV);
            if ($strFlag == 1) {
                unset($arrTmp_2[0]);
            } elseif ($strFlag == 2) {
                array_unshift($arrTmp_2, "01");
            } elseif ($strFlag == 3) {
                $arrTmp_2[0] = '01';
            }
            $arr[] = implode(":", $arrTmp_2);
        }

        return implode($note, $arr);
    }

    // 拼装提交跳转form
    public static function buildRequestForm($url, $para, $method = 'post', $button_name = 'submit')
    {
        $sHtml = "<form id='request-form' name='request-form' action='" . $url . "' method='" . $method . "'>";
        while (list ($key, $val) = each($para)) {
            $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
        }
        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml . "<input type='submit' value='" . $button_name . "' style='visibility: hidden;'></form>";
        $sHtml = $sHtml . "<script>document.forms['request-form'].submit();</script>";

        return $sHtml;
    }

    /**
     * 处理接口返回数据，用于处理一个Key的内容，有值为对象，没有值为空数组的情况
     * @param array $arrData
     * @param array $arrMap ['data.seafInfo'=>'arr=>obj']表示二维数组
     */
    static public function processReturn(&$arrData = array(), $arrMap = array())
    {
        $tmpVar = null;
        //['info'=>['aaa'=1,'bbbb'=>2]]这种数组，为空的时候，转换为空对象
        foreach ($arrMap as $key => $val) {
            $arrKeyParams = explode('.', $key);
            $strEval = '$tmpVar = $arrData';
            foreach ($arrKeyParams as $keyIn) {
                $strEval .= "['$keyIn']";
            }
            $strEvalPre = '';
            $strEvalPre .= $strEval . ';';
            @eval($strEvalPre);
            //将空数组转换空对象
            if ($val === 1) {
                if (is_array($tmpVar) && empty($tmpVar)) {
                    $strEvalFianl = $strEval . ' = new \stdClass();';
                    @eval($strEvalFianl);
                }
            }
        }
    }

    /**
     * 自定义的，类似pHP5.5才有的 array_column函数的简易版
     * @param array $input 原始数组
     * @param string $column_key 取字数组的列名
     * @param string $index_key 拓展：因为这个函数是简易版，所有未做这个参数的处理
     * @return array
     */
    public static function array_column(array $input = array(), $column_key = '', $index_key = '')
    {
        $arrReturn = array();
        if (empty($input) || empty($column_key)) {
            return $arrReturn;
        }
        foreach ($input as $k => $v) {
            if (isset($v[$column_key])) {
                $arrReturn[] = $v[$column_key];
            }
        }
        return $arrReturn;
    }

    /**
     * 生成唯一uuid
     */
    public static function uuid($prefix = '')
    {
        $chars = md5(uniqid(mt_rand(), true));
        $uuid = substr($chars, 0, 8) . '-';
        $uuid .= substr($chars, 8, 4) . '-';
        $uuid .= substr($chars, 12, 4) . '-';
        $uuid .= substr($chars, 16, 4) . '-';
        $uuid .= substr($chars, 20, 12);
        return $prefix . $uuid;
    }


    /*
     * 获取HTTP Header
     */
    public static function getAllHeaders()
    {
        $headers = array();
        foreach ($_SERVER as $key => $value) {
            if ('HTTP_' == substr($key, 0, 5)) {
                $headers[str_replace('_', '-', substr($key, 5))] = $value;
            }
        }

        if (isset($_SERVER['PHP_AUTH_DIGEST'])) {
            $header['AUTHORIZATION'] = $_SERVER['PHP_AUTH_DIGEST'];
        } elseif (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
            $header['AUTHORIZATION'] = base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $_SERVER['PHP_AUTH_PW']);
        }

        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $header['CONTENT-LENGTH'] = $_SERVER['CONTENT_LENGTH'];
        }

        if (isset($_SERVER['CONTENT_TYPE'])) {
            $header['CONTENT-TYPE'] = $_SERVER['CONTENT_TYPE'];
        }

        return $headers;
    }

    /**
     * 获取requestId
     * @return string
     */
    public static function getRequestId()
    {
        $strRequestId = '';
        $arrHeaders = Utils::getAllHeaders();
        if( !empty($arrHeaders['X-REQUEST-ID']) ) {
            $_REQUEST['X-Request-Id'] = $arrHeaders['X-REQUEST-ID'];
            $strRequestId = $arrHeaders['X-REQUEST-ID'];
        }
        return $strRequestId;
    }

    //base64加密字符串
    /**
     * base64加密字符串
     * @param   string $str 需要加密的str
     * @param   string $key 秘钥
     * @return  string $return  如果加密成功，返回加密后的字符串，加密失败返回空字符串
     */
    public static function encryptStr($str = '', $key = '')
    {
        //参数判断
        $strCrypt = '';
        if (empty($str)) return $strCrypt;
        //选择秘钥
        if (!empty($key)) {
            $strKey = $key;
        } else {
            $strKey = 'GX6101DAV2020W98';
        }
        //加密
        $strCrypt = static::base64Enc($str, $strKey);
        return $strCrypt;
    }

    /**
     * 解密，对应的加密为 CryptStr
     * @param   string $str 需要解密的字符串
     * @param   string $key 秘钥
     * @return  string 如果解密成功，返回解密后的字符串，否则返回空字符串
     */
    public static function decryptStr($str = '', $key = '')
    {
        //参数判断
        $strDeCrypt = '';
        if (empty($str)) return $strDeCrypt;
        //选择秘钥
        if (!empty($key)) {
            $strKey = $key;
        } else {
            $strKey = 'GX6101DAV2020W98';
        }
        //解密
        $strDeCrypt = static::base64Des($str, $strKey);
        return $strDeCrypt;
    }

    /**
     * 解密算法，原算法为动态key异或加密后再base64编码，关于这个加密解密算法，可百度搜索“discuz加密解密算法”
     * @param 	string $str 加密
     * @param   string $key 动态秘钥
     * @return 	string $return 解密后的明文
     */
    public static function base64Des($str,$key)
    {
        if( empty($str) || empty($key) ){
            return NULL;
        }
        $decodeStr = base64_decode($str);
        $qlen      = strlen($decodeStr);
        $klen      = strlen($key);
        $return    = '';
        $k         = 0;
        for($i = 0; $i < $qlen; $i++)
        {
            if($k == $klen) $k = 0;
            $return .= $decodeStr[$i] ^ $key[$k++];

        }
        return $return;
    }

    /**
     * Base64加密算法，根据str和key得到密文
     * @param 	string $str 加密
     * @param   string $key 动态秘钥
     * @return 	string $return 解密后的明文
     */
    public static function base64Enc($str,$key)
    {
        if( empty($str) || empty($key) ){
            return NULL;
        }
        $qlen      = strlen($str);
        $klen      = strlen($key);
        $encodeStr = '';
        $return    = '';
        $k         = 0;
        for($i     = 0; $i < $qlen; $i++)
        {
            if($k == $klen) $k = 0;
            $encodeStr .= $str[$i] ^ $key[$k++];

        }
        $return = base64_encode($encodeStr);
        return $return;
    }
    //获取当前HOST地址
    public static function getHost()
    {
        if (!empty($_SERVER['HTTPS'])) {
            $scheme = "https://";
        } else {
            $scheme = "http://";
        }
        return $scheme . $_SERVER['HTTP_HOST'];
    }
}