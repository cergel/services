<?php

namespace sdkService\helper;

class Net
{
    /**
     * 获取IP地址
     * @return string
     */
    public static function getRemoteIp()
    {
        //为了兼容微影服务器带过来的HTTP_X_FORWADED_FOR（不带”R“）
        if(!empty($_SERVER["HTTP_X_FORWADED_FOR"])){

            $cip = $_SERVER["HTTP_X_FORWADED_FOR"];
            if (FALSE === strpos($cip, ',')){
                $cip = $_SERVER["HTTP_X_FORWADED_FOR"];
            }
            else{
                //多个转发IP的，取第1个，那是用户最初的IP
                $arrIp =  explode (', ', $cip);
                $cip = $arrIp[0];
            }
        }elseif(! empty ( $_SERVER["HTTP_X_FORWARDED_FOR"] )){

            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            if (FALSE === strpos($cip, ',')) {
                $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            }
            else{
                //多个转发IP的，取第1个，那是用户最初的IP
                $arrIp =  explode (', ', $cip);
                $cip = $arrIp[0];
            }
        }elseif (! empty ( $_SERVER ["REMOTE_ADDR"] )){

            $cip = $_SERVER ["REMOTE_ADDR"];

        }elseif (! empty ( $_SERVER["HTTP_X_CIP"] )){

            $cip = $_SERVER["HTTP_X_CIP"];

        }elseif (isset($_REQUEST['x-cip'])){

            $cip = $_REQUEST['x-cip'];

        }else{

            $cip = '';
        }

        return $cip;
    }

    /**
     * IP地址转整型
     * @return string
     */
    public static function getRemoteIpLong()
    {
        return sprintf("%u", ip2long(self::getRemoteIp()));
    }


    /**
     * 检测内网 IP
     * @param string $ipString ip地址
     * @return boolean
     */
    public static function isPrivateIp($ipString)
    {
        $ip = ip2long($ipString);
        //内网 IP: 10.0.0.0 - 10.255.255.255; 172.16.0.0 - 172.31.255.255; 192.168.0.0 - 192.168.255.255
        // 在支持大整数的操作系统下 ip2long 会返回正整数,如 -1408237568 会得到 4294967296-1408237568 = 2886729728
        if (
            (167772160 <= $ip && $ip <= 184549375)
            || (-1408237568 <=$ip && $ip <= -1407188993)
            || (-1062731776 <=$ip && $ip <= -1062666241)
            || (2886729728 <=$ip && $ip <= 2887778303)
            || (3232235520 <=$ip && $ip <= 3232301055)
            // 为方便调试，把本地 IP 和办公室以及 SAE 的外网 IP 也认为是内网。
            || (in_array($ipString, array('61.135.152.207', '61.135.152.210', '61.135.152.218', '127.0.0.1', '123.125.106.107', '123.125.106.108', '180.149.134.108','61.135.152.194')))
        )
        {
            return true;
        }
        return false;
    }

}