<?php
/**
 * Created by PhpStorm.
 * User: zhou di
 * Date: 2016/1/15
 * Time: 18:44
 */
namespace sdkService\model;


class Order extends BaseNew
{
    //历史订单接口，数据库表
    const DB_HISTORY_ORDER = 'dbHistoryOrder';
    const DB_HISTORY_ORDER_TABLE = 'dw_open_biz_order';

    private $refundTypeMsgMap = [
        '0' => '', //废弃
        '1' => '购票后15分钟至开场前2小时可退票', //可退票
        '2' => '请仔细核对购票信息，支付后不支持退票', //影院不可退
        '3' => '购票后15分钟至开场前2小时可退票', //开场前2小时
        '4' => '本月享有的1次退票资格已用完，不支持退票', //达到每月上线
        '5' => '该场次为万达特殊场次，不支持退票', //万达特定场次不可退
        '6' => '该场次离开场时间已不足2小时，不支持退票',
        '11'=>'拼团订单不可退',
    ];


    #####################################
    ############ 历史订单 START ##########
    #####################################
    /**
     * 查询历史订单【新结构】
     * 历史订单由于是第二日把昨日的订单写入数据库
     * 所以订单状态与退款标识可能会出现问题
     * 订单列表统一把退款标识设置为不可退票订单状态保留原样
     * @param $openId string 第三方帐号
     * @param $pageIndex int 分页页数，非必须
     * @param $pageSize int 分页条数，非必须
     * @param $daysBefore int 查询XX天前的数据，非必须
     * @return array
     */
    public function getHistoryOrderFromDBNew($openIdAll, $pageIndex = '', $pageSize = '', $orderType = '', $daysBefore = '', $orderby = '')
    {
        try {
            //初始化数据库
            $this->pdo(self::DB_HISTORY_ORDER, self::DB_HISTORY_ORDER_TABLE);

            //分页功能，不传参数则不使用，两个参数中出现0则全返回，转换后有负数均转为0
            if (!empty($pageIndex) && is_numeric($pageIndex) && !empty($pageSize) && is_numeric($pageSize)) {
                $Index = ($pageIndex - 1) * $pageSize;
                $Index = $Index > 0 ? $Index : 0;
                $Size = $pageSize;
                $Size = $Size > 0 ? $Size : 0;
                $limit = "$Index , $Size";
            } else {
                $limit = null;
            }

            $fields = '*';

            $params = [];
            if (count($openIdAll) == 1) {
                $params[':openid'] = $openIdAll[0];
                $whereOpenid = '= :openid';
            } else {
                foreach ($openIdAll as &$id) {
                    $id = "'$id'";
                }
                $whereOpenid = 'in (' . implode(',', $openIdAll) . ')';
            }

            //where条件，查询类型，查询天数，只显示正常订单（2已支付，6已发码，20已出票成功）
            #$where = "openid = :openid and status in (2,6,20)";
            $where = "openid " . $whereOpenid . " and order_status in (2,6,20)";
            $purchaseType = $this->transOrderType($orderType); //订单类型转换，兑换券 1=>4,订单 2=>3
            if (!empty($purchaseType)) {
                $where = $where . " and purchasetype = :purchasetype";
                $params[':purchasetype'] = $purchaseType;
            }
            if (!empty($daysBefore) && is_numeric($daysBefore)) {
                $where = $where . " and dt < :date";
                $dateBefore = date('Y-m-d', time() - 86400 * $daysBefore);
                $params[':date'] = $dateBefore;
            }

            //排序
            $orderby = !empty($orderby) ? $orderby : null;

            $response = $this->pdohelper->fetchArray($where, $params, $fields, $orderby, $limit);

            //计算总数
            $limit = null;
            $fields = 'count(1)';
            $count = $this->pdohelper->fetchArray($where, $params, $fields, null, $limit);
            $total = empty($count) ? 0 : $count[0][$fields];

            $rst = [
                'ret' => 0,
                'sub' => 0,
                'data' => [],
                'page' => $pageIndex,
                'num' => $pageSize,
                'total_row' => $total,
            ];

            //返回格式处理
            foreach ($response as $order) {
                $purchasetime = self::formatTime($order['purchasetime']);
                $showtime = self::formatTime($order['showtime']);
                $orderInfo = [
                    #'cd_key' => $this->formatCdKey($order),
                    #'bsId' => $this->formatBsId($order),
                    'open_id' => $order['openid'],
                    'cinema_id' => $order['cinemano'],
                    'cinema_name' => $order['cinemaname'],
                    'city_id' => $order['cityid'],
                    'show_date' => $showtime,
                    'hall_id' => $order['bisroomno'],
                    'hall_name' => $order['bisroomname'],
                    'language' => $order['language'],
                    'show_type' => $order['showtype'],
                    'movie_id' => $order['filmid'],
                    'movie_name' => $order['filmname'],
                    'order_id' => $order['orderid'],
                    'order_type' => $this->formatOrderType($order['purchasetype']),
                    'seatInfo' => $this->formatSeatinfo($order),
                    'state' => $order['order_status'],
                    'ticketCount' => $order['num'],
                    'totalPrice' => $order['paytotal'] * 100,
                    'hotline_tele' => '',
                    'refundFlg' => '2',
                    'expired_time' => $showtime,
                    'time' => $purchasetime,
                    'total_fee' => $order['paytotal'],
                    'cinema_address' => '',
                    'show_date_has_weekday' => $showtime . $this->str2weekday($showtime),
                    'refundMsg' => $this->getRefundContent('2'),
                ];
                $rst['data'][] = array_map([$this, 'null2empty'], $orderInfo); //将结果的所有null转换为空字符串''
            }
            return $rst;
        } catch (\Exception $e) {
            //日志记录
            //wepiao::info('[error] ' . $e);
            $rst = [
                'ret' => -2,
                'sub' => -2,
                'msg' => $e->getMessage(),
            ];
            return $rst;
        }
    }

    /**
     * 输出时间字符串对应的星期
     * @param $strDate
     * @param $type 0周几 1星期几
     * @return string
     */
    public function str2weekday($strDate, $type = 0)
    {
        $weekCN = ['日', '一', '二', '三', '四', '五', '六'];
        $prefix = empty($type) ? '周' : '星期';
        return $prefix . $weekCN[date('w', strtotime($strDate))];
    }

    /**
     * 座次信息转为中文，例如将“4:06|4:07”转为“4排06座,4排07座”
     * @param $seatinfo
     * @return string
     */
    public function formatSeatlable($seatinfo)
    {
        $seatinfoFormat = [];
        $arrSeat = explode('|', $seatinfo);
        foreach ($arrSeat as $seat) {
            //兼容“01:4:06|01:4:07”的情况
            $arrInfo = array_reverse(explode(':', $seat));
            if (isset($arrInfo[0]) && isset($arrInfo[1])) {
                $seatFormat = $arrInfo[1] . '排' . $arrInfo[0] . '座';
            } else {
                $seatFormat = '';
            }
            $seatinfoFormat[] = $seatFormat;
        }
        return implode(',', $seatinfoFormat);
    }

    /**
     * 格式化时间，返回格式 2016-01-02 16:22
     * @param $rawTime
     * @return string
     */
    public function formatTime($rawTime)
    {
        return date('Y-m-d H:i', strtotime($rawTime));
    }

    /**
     * 查询时将ordertype转换为purechasetype
     * 1兑换券 => 4; 2订单 => 3; 3全部=>不传
     * @param $orderType
     * @return int|string
     */
    public function transOrderType($orderType)
    {
        switch ($orderType) {
            case 1:
                $purchaseType = 4;
                break;
            case 2:
                $purchaseType = 3;
                break;
            default:
                $purchaseType = '';
        }
        return $purchaseType;
    }

    /**
     * 展示时原始数据purchasetype转换为order_type
     * 3订单 => 2; 4兑换券 => 1
     * @param $purchaseType
     * @return int|string
     */
    public function formatOrderType($purchaseType)
    {
        switch ($purchaseType) {
            case 3:
                $orderType = 2;
                break;
            case 4:
                $orderType = 1;
                break;
            default:
                $orderType = '';
        }
        return $orderType;
    }

    /**
     * 入参为null则转为空字符串''
     * @param string $input
     * @param string $default
     */
    public function null2empty($input, $default = '')
    {
        return is_null($input) ? $default : $input;
    }

    /**
     * cd_key获取处理
     * @param $order
     * @return string
     */
    public function formatCdKey($order)
    {
        if (!empty($order['openplatformcode'])) {
            $cdkey = $order['openplatformcode'];
            //$order['bissystemid'] = '111111111111' . '222222222222'; //cd_key处理中的一步，在formatBsid()中进行
        } else if (!empty($order['biscode'])) {
            $cdkey = $order['biscode'];
        } else if (!empty($order['tpextorderid']) || !empty($order['bisextcode'])) {
            $cdkey = $order['tpextorderid'] . '|' . $order['bisextcode'];
        } else {
            $cdkey = '';
        }
        return $cdkey;
    }

    /**
     * bsId取值处理
     * @param $order
     * @return string
     */
    public function formatBsId($order)
    {
        return (!empty($order['openplatformcode'])) ? '111111111111' . '222222222222' : $order['bissystemid'];
    }

    /**
     * 座位信息格式化
     * @param $order
     * @return array
     */
    public function formatSeatinfo($order)
    {
        $resSeatinfo = [];
        $arrCdkey = explode('|', $this->formatCdKey($order));
        $arrSeats = explode(',', $this->formatSeatlable($order['seatinfo']));
        $countCdkey = count($arrCdkey);
        $countSeats = count($arrSeats);
        for ($i = 0; $i < $countCdkey; $i++) {
            if ($i + 1 == $countCdkey && $i + 1 < $countSeats) {
                //cdkey与座位号一一对应。如果座位号多，则多出的加到最后一个cdkey对应的座位信息里
                $seats = implode(',', array_slice($arrSeats, $i));
            } elseif ($i + 1 < $countCdkey && $i + 1 == $countSeats) {
                //cdkey与座位号一一对应。如果cdKey多，则多出的加到最后一个座位对应的cdKey信息里
                $arrCdkey[$i] = implode('|', array_slice($arrCdkey, $i));
                $seats = $this->getParam($arrSeats, $i);
                $countCdkey = $i; //提前结束循环
            } else {
                $seats = $this->getParam($arrSeats, $i);
            }
            $itemSeatinfo = [
                'seatLable' => $seats,
                'cdKey' => '取票码:' . $arrCdkey[$i],
                'qrCode' => '',
            ];
            $resSeatinfo[] = $itemSeatinfo;
        }
        return $resSeatinfo;
    }

    /**
     * 历史订单查询详情
     * @param $openIdAll
     * @param $orderId
     * @return array
     */
    public function getHistoryDetail($openIdAll, $orderId)
    {
        $return = ['ret' => 0, 'sub' => 0, 'data' => []];
        try {
            $this->pdo(self::DB_HISTORY_ORDER, self::DB_HISTORY_ORDER_TABLE);
            $fields = '*';
            $params = [];
            $params[':orderId'] = $orderId;
            if (count($openIdAll) == 1) {
                $params[':openid'] = $openIdAll[0];
                $whereOpenid = '= :openid';
            } else {
                foreach ($openIdAll as &$id) {
                    $id = "'$id'";
                }
                $whereOpenid = 'in (' . implode(',', $openIdAll) . ')';
            }

            //where条件，只显示正常订单（2已支付，6已发码，20已出票成功）
            $where = "openid " . $whereOpenid . " and order_status in (2,6,20) and orderid = :orderId";
            $response = $this->pdohelper->fetchOne($where, $params, $fields);
            if ($response) {
                //按照新版订单详情进行格式化
                $purchasetime = self::formatTime($response['purchasetime']);
                $showtime = self::formatTime($response['showtime']);
                $orderInfo = [
                    'open_id' => $response['openid'],
                    'cinema_id' => $response['cinemano'],
                    'cinema_name' => $response['cinemaname'],
                    'city_id' => $response['cityid'],
                    'show_date' => $showtime,
                    'hall_id' => $response['bisroomno'],
                    'hall_name' => $response['bisroomname'],
                    'language' => $response['language'],
                    'show_type' => $response['showtype'],
                    'movie_id' => $response['filmid'],
                    'movie_name' => $response['filmname'],
                    'order_id' => $response['orderid'],
                    'order_type' => $this->formatOrderType($response['purchasetype']),
                    'seatInfo' => $this->formatSeatinfo($response),
                    'state' => $response['order_status'],
                    'ticketCount' => $response['num'],
                    'totalPrice' => $response['paytotal'] * 100,
                    'hotline_tele' => '',
                    'refundFlg' => '2',
                    'expired_time' => $showtime,
                    'time' => $purchasetime,
                    'total_fee' => $response['paytotal'],
                    'cinema_address' => '',
                    'show_date_has_weekday' => $showtime . $this->str2weekday($showtime),
                    'refundMsg' => $this->getRefundContent('2'),
                ];
                $orderInfo = array_map([$this, 'null2empty'], $orderInfo); //将结果的所有null转换为空字符串''
                $return['data'] = $orderInfo;
            } else {
                $return['ret'] = -1;
            }
            return $return;
        } catch (\Exception $e) {
            $rst = [
                'ret' => -2,
                'sub' => -2,
                'msg' => $e->getMessage(),
            ];
            return $rst;
        }
    }

    #####################################
    ############ 历史订单 END ############
    #####################################

    /**
     * 获取订单是否被删除
     * @param string $iChannelId
     * @param string $PrimaryKey
     * @param array $OrderIds
     * @return array
     */
    public function getOrderDelStatus($iChannelId = '', array $OrderIds = [])
    {
        try {
            $data = $this->redis($iChannelId, DELETE_ORDER)->WYhMGet(DELETE_ORDER_KEY, $OrderIds);
        } catch (\Exception $e) {
            //日志记录
            //wepiao::info('[redis error] ' . $e);
            return [];
        }
        if ($data !== false) {
            return $data;
        } else {
            return [];
        }

    }

    /**
     * 返回refundmsg
     * @param $refundType
     * @return string
     */
    function getRefundContent($refundType)
    {
        if (isset($this->refundTypeMsgMap[$refundType]))
            return $this->refundTypeMsgMap[$refundType];
        else
            return '';
    }

    /**
     * 有偿退改签项目 （bbq 新增）
     * 返回改签/退票合并文案处理（用来处理 支付页和订单详情页 不同文案）
     * @param $refundType
     * @return string
     */
    function getMergeContent($msg)
    {
        return explode('，' , $msg)[0];
    }


    /**
     * 删除订单的方法
     * @param string $iChannelId
     * @param string $PrimaryKey
     * @param string $orderId
     * @return array
     */
    public function delOrder($iChannelId = '', $orderId = "")
    {
        $data = false;
        try {
            $data = $this->redis($iChannelId, DELETE_ORDER)->WYhMset(DELETE_ORDER_KEY, [$orderId => 1]);
        } catch (\Exception $e) {
            //日志记录
            //wepiao::info('[redis error] ' . $e);
        }
        return $data;
    }

}