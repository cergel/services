<?php

namespace sdkService\service;

use sdkService\model\ApplyActive;

/**
 *app resource
 */
class serviceAppResource extends serviceBase
{
    /**
     * 获取定制化选坐
     * @return mixed
     */
    public function getCustomizationSeat()
    {
        $picHost = CDN_APPNFS . '/uploads/Assets/';
        $Ret = $this->model('AppResource')->getCustomizationSeat();
        if ($Ret) {
            $Ret = json_decode($Ret, true);
            foreach ($Ret as $value) {
                $obj = json_decode(str_replace('#CDNPath#', $picHost, $value['Config']));
                $obj->MovieId = $value['MovieId'];
                $data[] = $obj;
            }
        }
        return $data;
    }

    /**
     * 获取日签日历
     * @param $month
     * @return mixed
     */
    public function getDaySignCalendar(array $arrInput)
    {
        return $this->model("AppResource")->getDaySignCalendar($arrInput['month']);
    }

    /**
     * 获取日签详情
     * @return mixed
     */
    public function getDaySign()
    {
        return $this->model("AppResource")->getDaySign();
    }

    /**
     * 获取当月所有日签名
     */
    public function getDaySignMonth($arrInput)
    {
        return $this->model("AppResource")->getDaySignMonth($arrInput['channelId'], $arrInput['month']);
    }

    /**
     * 获取历史日签
     */
    public function getDaySignLastYear($arrInput)
    {
        return $this->model("AppResource")->getDaySignLastYear($arrInput['channelId'], $arrInput['month'], $arrInput['day']);
    }

    /**
     * 获取可配置图标
     * @return mixed
     */
    public function getIcon()
    {
        return $this->model("AppResource")->getIcon();
    }

    //获取影片商业化列表
    public function getBizList($arrInput)
    {
        return $this->model("AppResource")->getBizList($arrInput['channelId']);
    }


    //获取客户端可用模块
    public function getAppModuleSwitch()
    {
        return $this->model("AppResource")->getAppModuleSwitch();
    }

    //获取iOS JsPatch
    public function getPatch($arrInput)
    {
        return $this->model("AppResource")->getPatch($arrInput['channelId'], $arrInput['appver'], $arrInput['openId']);
    }


    /**
     * 返回版本列表
     * @param array $arrInput
     * @return array
     */
    public function getVersionRelease($arrInput = [])
    {
        $compairData = [];
        $Obj = new \stdClass();
        if ($arrInput['appkey'] != 8 && $arrInput['appkey'] != 9) {
            return ['errorcode' => 0, 'result' => ['data' => $Obj]];
        }

        //获取被动缓存
        $verData = $this->model("AppResource")->getVersion($arrInput['appkey']);
        if (!$verData) {
            return ['errorcode' => 0, 'result' => ['data' => $Obj]];
        }

        $appver = '';
        if (empty($arrInput['versionCode'])) {
            $appver = $arrInput['appver'];
            if (empty($appver)) {
                return ['errorcode' => 0, 'result' => ['data' => $Obj]];
            }
        } else {
            $appver = $arrInput['versionCode'];
        }

        $ret = $this->_checkVersion($verData, $appver, $arrInput['appkey']);
        return ['errorcode' => 0, 'result' => ['data' => $ret]];
    }

    private function _checkVersion($verData, $versionStr, $channelId)
    {
        $updateTimestamp = 0;
        foreach ($verData as $key => $value) {
            $versionData = json_decode($value, 1);
            $ret = $this->_compare($versionData, $versionStr);
            if ($ret) {
                if ($versionData['updated'] > $updateTimestamp) {
                    $updateTimestamp = $versionData['updated'];
                    $path = '';
                    if ($channelId == 9) {
                        if (RUN_ENV == 2) {
                            $path = 'https://wxadminpre.wepiao.com/uploads/app_version/' . $versionData['path'];
                        } else {
                            $path = \Yii::$app->params['version']['path'] . $versionData['path'];
                        }
                    } else {
                        $path = $versionData['path'];
                    }
                    $verStr = '';
                    $verCode = '';
                    $retVersion = models\Version::getChannelVersion($channelId);
                    if (empty($retVersion)) {
                        $verStr = '0.0.0';
                        $verCode = 0;
                    } else {
                        $verStr = $retVersion;
                        $verCode = $retVersion;
                    }
                    $compairData = [
                        'id' => $versionData['id'],
                        'title' => $versionData['title'],
                        'img' => $versionData['img'],
                        'version' => $verStr,
                        'versionCode' => $verCode,
                        'path' => $path,
                        'forceUpdate' => $versionData['forceUpdate'],
                        'md5' => empty($versionData['md5']) ? 0 : $versionData['md5'],
                        'description' => $versionData['description'],
                    ];
                }
            }
        }
        if (empty($compairData)) {
            $compairData = new \stdClass();
        } else if (($channelId == 9) && $this->_checkAndroidVer($versionStr)) {
            $compairData['version'] = KEY_APP_VERSION_ANDROID_OLD_VERSION_UPDATE_VERSION;
            $compairData['versionCode'] = KEY_APP_VERSION_ANDROID_OLD_VERSION_UPDATE_VERSION;
        }
        return $compairData;
    }

    /**
     * app获取支付后红包数量
     * @param array $arrInput
     * @return mixed
     */
    public function getRedPacketNum(array $arrInput)
    {
        return $this->model("Resource")->getRedPacketNum($arrInput['channelId']);
    }

    /**
     * 获取某个日期下的今日推荐
     * @param array $arrInput
     * @return mixed
     */
    public function getRecommend(array $arrInput)
    {
        $return = [];
        $page = self::getParam($arrInput, 'page',1);
        $num = self::getParam($arrInput, 'num',10);
        $ret = $this->model("AppResource")->getRecommend($page, $num);
        $list = array_map(function ($value){
            return (json_decode($value,1));
        }, $ret['list']);
        $ret['list'] = array_values($list);
        return $ret;
    }
}