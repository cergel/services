<?php

/**
 * 输出工具类，可输出 json、xml等
 */

namespace sdkService\helper;


class OutPut
{
    /**
     * @todo $jsonp 预留参数，暂时用request
     * 输出json格式的数据
     *
     * @param  array  $data
     * @param  string $unicode 是否编码中文
     * @param  string $exit
     *
     * @return string
     */
    public static function jsonOut($data = [], $unicode = true, $exit = true, $jsonp = false)
    {
        $jsonp = !empty( $_REQUEST['jsonp'] ) ? $_REQUEST['jsonp'] : false;
        header('Content-Type:application/json; charset=UTF-8');
        $return = '';
        if ($unicode) {
            $return = json_encode($data);
        } else {
            //其实在PHP高版本上，json_encode加第二个参数，就可以实现json_encode不对中文转unicode编码，但是低版本无法处理，因为重写一个方法
            $return = self::notUnicodeJson($data);
        }
        // [] => {} 暂时用这个方法处理返回类型不一致的问题
        //$return = str_replace(':[]', ':{}', $return);

        if ($exit) {
            $echoStr = '';
            if ($jsonp !== false) {
                $echoStr = $jsonp . "(" . $return . ")";
            } else {
                $echoStr = $return;
            }
            //记录所有接口的返回内容
            //Yii::info( 'Output Params：'.$echoStr );
            echo $echoStr;
            exit;
        }

        //Yii::info( 'Output Params：'.$return );
        return $return;
    }

    /**************************************************************
     *
     *    将数组转换为JSON字符串（兼容中文）
     *
     * @param  array $array 要转换的数组
     *
     * @return string      转换得到的json字符串
     * @access public
     *
     *************************************************************/
    public static function notUnicodeJson($array)
    {
        self::arrayRecursive($array, 'urlencode', true);
        $json = json_encode($array);

        return urldecode($json);
    }

    public static function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die( 'possible deep recursion attack' );
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                self::arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }

            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset( $array[$key] );
                }
            }
        }
        $recursive_counter--;
    }

    /**
     * 转换json字符串为数组或者对象
     * 对于转换失败的情况，返回空对象或者空数组
     *
     * @param string     $jsonStr 要json_decode的字符串
     * @param bool|false $outArr  如果为true，则返回数组类型，如果为false，则返回对象类型
     */
    public function jsonConvert($jsonStr = '', $outArr = true)
    {
        //对于$jsonStr为空的情况，解析完应该为空数组或空对象
        if (empty( $jsonStr )) {
            $return = ( $outArr == true ) ? [] : new \stdClass();
        }
        //
        //jsonStr非空为正常情况
        else {
            if ($outArr == true) {
                $decodeRes = json_decode($jsonStr, true);
                $return = !empty( $decodeRes ) ? $decodeRes : [];
            } else {
                $decodeRes = json_decode($jsonStr);
                $return = !empty( $decodeRes ) ? $decodeRes : new \stdClass();
            }
        }

        return $return;
    }

}
