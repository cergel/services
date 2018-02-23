<?php
/**
 * Created by PhpStorm.
 * User:
 * Date:
 * Time:
 */

namespace sdkService\model;

class AppResource extends BaseNew
{
    protected $redisType = RESOURCE_STATIC;

    public function __construct()
    {
        //都会使用redis
        $this->redis = $this->redis('8', RESOURCE_STATIC);
    }

    /**
     * 获取定制化选坐
     * @return mixed
     */
    public function getCustomizationSeat()
    {
        $key = APP_CUSTOMIZATION_SEAT;
        return $this->redis->WYget($key);

    }

    /**
     * 获取日签日历
     * @param $month
     * @return mixed
     */
    public function getDaySignCalendar($month)
    {
        $key = KEY_DAY_SIGN_PAGING_CALENDAR;
        return $this->redis->WYhGet($key, $month);
    }

    /**
     * 获取日签详情
     * @return mixed
     */
    public function getDaySign()
    {
        $key = DAY_SIGN_PAGING;
        return $this->redis->WYget($key);

    }

    /**
     * 获取可配置图标
     * @return mixed
     */
    public function getIcon()
    {
        $key = APP_ICON_CONFIG;
        return $this->redis->WYget($key);
    }

    //获取影片商业化列表
    public function getBizList($channelId)
    {
        $key = $key = MOVIE_INFO_BIZ;
        $return = ['ret' => 0, 'sub' => 0, 'data' => []];

        $list = $this->redis->WYhGetAll($key);
        $now = time();
        $data = [];
        if ($list) {
            //判断当前渠道是否符合相关内容以及时间
            foreach ($list as $strItem) {
                $item = json_decode($strItem, 1);
                $start = strtotime($item['start']);
                $end = strtotime($item['end']);
                if (in_array($channelId, $item['platform']) && $now >= $start && $now < $end) {
                    $data[] = $item;
                }
            }
            $return['data'] = $data;
        }
        return $return;
    }


    //获取客户端可用模块
    public function getAppModuleSwitch()
    {
        $key = APP_MODULE_SWITCH;
        $config = [];
        $daySignStatus = $this->redis->WYhGet($key, "daySign");
        $config['daySign'] = $daySignStatus === false ? 1 : (int)$daySignStatus;
        $super8Status = $this->redis->WYhGet($key, "super8");
        $config['super8'] = $super8Status === false ? 1 : (int)$super8Status;
        $commentStatus = $this->redis->WYhGet($key, "comment");
        $config['comment'] = $commentStatus === false ? 1 : (int)$commentStatus;
        $shopStatus = $this->redis->WYhGet($key, "shop");
        $config['shop'] = $shopStatus === false ? 1 : (int)$shopStatus;
        $return = ['ret' => 0, 'sub' => 0, 'data' => $config];
        return $return;
    }

    //获取iOS JsPatch
    public function getPatch($channelId, $appver, $openId = "")
    {
        //没有数据默认返回值
        $return = ['ret' => 0, 'sub' => 0, 'data' => new \stdClass()];
        if ($channelId == 8) {
            $key = KEY_JSPATCH_ITEM;
        } elseif ($channelId == 9) {
            $key = KEY_TINKER_ITEM;
        } else {
            $key = "";
        }
        $item = $this->redis->WYhGet($key, $appver);
        if ($item) {
            $patch = json_decode($item, true);
            //如果为空则返回所有
            if (empty($patch['openId'])) {
                $return['data'] = $patch;
            } else {
                //判断是否有对象
                $openIdList = explode(",", $patch['openId']);
                if (in_array($openId, $openIdList)) {
                    $return['data'] = $patch;
                } else {
                    $return['data'] = new \stdClass();
                }
            }
        } else {
            $return['data'] = new \stdClass();
        }
        return $return;
    }

    /**
     * 获取当月所有日签名
     */
    public function getDaySignMonth($channelId, $month)
    {
        $data = $this->redis->WYhGetAll(KEY_DAY_SIGN_PAGING_MONTH . $month);
        ksort($data);
        $return = [];
        if ($data) {
            foreach ($data as $value) {
                $return[] = json_decode($value);
            }
        }
        return $return;
    }

    public static function getVersion($appkey = 8)
    {
        $arrRedisConfig = \Yii::$app->params['redis']['version']['read'];
        $objRedis = RedisManager::getInstance($arrRedisConfig);
        $hashKey = KEY_APP_VERSION_CHANNEL . $appkey;
        return $objRedis->hashFindAll($hashKey);
    }

    public function getDaySignLastYear($channelId, $month, $day)
    {
        $return = new \stdClass();
        $data = $this->redis->WYhGet(KEY_DAY_SIGN_PAGING_MONTH . $month, $day);
        if ($data) {
                $return = json_decode($data);
                $return->year = substr($return->iID,0,4);
                $return->month = substr($return->iID,4,2);
                $return->day = substr($return->iID,6,2);
                switch ($return->month)
            {
                case '01':
                    $return->month='Jan.';
                    break;
                case '02':
                    $return->month='Feb.';
                    break;
                case '03':
                    $return->month='Mar.';
                    break;
                case '04':
                    $return->month='Apr.';
                    break;
                case '05':
                    $return->month='May.';
                    break;
                case '06':
                    $return->month='Jun.';
                    break;
                case '07':
                    $return->month='Jul.';
                    break;
                case '08':
                    $return->month='Aug.';
                    break;
                case '09':
                    $return->month='Sep.';
                    break;
                case '10':
                    $return->month='Oct.';
                    break;
                case '11':
                    $return->month='Nov.';
                    break;
                case '12':
                    $return->month='Dec.';
                    break;
                default:
                    break;

            }
        }
        return $return;
    }


    /**
     * 获取当日的推荐
     * @param $date
     * @return string
     */
    public function getRecommend($page,$num)
    {
        $total = $this->redis->WYzCard(STATIC_APP_HOME_RECOMMEND_RANGE);
        if($num>$total || $num < 0){
            $num = 3;
        }
        $totalPage = intval($total/$num);
        if($page>$total){
            $page = $totalPage;
        } else if($page<0){
            $page = 1;
        }
        $ret = $this->redis->WYzRevRange(STATIC_APP_HOME_RECOMMEND_RANGE, ($page-1)*$num, $num, true);
        $recommendIds = array_map(function ($value){
           return intval($value);
        }, $ret);
        $list = $this->redis->WYhMGet(APP_HOMEPAGE_RECOMMEND,$recommendIds);
        return compact('list', 'total');
    }

}