<?php
/**
 * 消息中心相关Service
 * Created by PhpStorm.
 * User: bbq
 * Date: 17/4/20
 * Time: 下午1:20
 */

namespace sdkService\service;


class serviceMessage extends serviceBase
{

    /**
     * @param array $arrInput
     * @return array
     */
    public function getMessageList(array $arrInput = [])
    {
        $return = $this->getStOut();
        $openId = self::getParam($arrInput, 'openId');
        $channelId = $this->switchChannelId(self::getParam($arrInput, 'channelId'));
        $allMsg = $this->model("Message")->getRecentMsg($channelId, $openId);
        $return['data'] = $allMsg;
        return $return;
    }

    /**
     * 获取指定类型的消息列表（把某类型消息全部置成已读状态）
     * @param array $arrInput
     * @return array
     */
    public function getMessageTypeList(array $arrInput = [])
    {
        $return = $this->getStOut();
        $openId = self::getParam($arrInput, 'openId');
        $channelId = $this->switchChannelId(self::getParam($arrInput, 'channelId'));
        $msgType = self::getParam($arrInput, 'type');
        $page = self::getParam($arrInput, 'page', 1);
        $pageSize = self::getParam($arrInput, 'pageSize', 10);
        $this->model("Message")->clearRedPoint($channelId, $openId, $msgType, '');//清空某类型消息未读数  该类型消息置为已读
        $allMsg = $this->model("Message")->getTypeMsg($channelId, $openId, $msgType, $page, $pageSize);
        $return['data'] = $allMsg;
        return $return;
    }

    /**
     * 阅读某条消息
     * @param array $arrInput
     * @return array
     */
    public function viewMessage(array $arrInput = [])
    {
        $return = $this->getStOut();
        $channelId = self::getParam($arrInput, 'channelId');
        $msgId = self::getParam($arrInput, 'msgId');
        $msg = $this->model("Message")->getMessageInfo($channelId, $msgId);
        if ($msg === false) {
            $return['data'] = new \stdClass();
        } else {
            $return['data'] = $msg;
        }
        return $return;
    }


    /*
     * 获取是否最新红包/选座卷ID （要排除掉已经消费掉 和 已经过期的红包 或者 选座卷）
     * @param array $arrInput
     * @return array
     */
    public function getRedPoint(array $arrInput = [])
    {
        $return = $this->getStOut();
        $openId = self::getParam($arrInput, 'openId');
        $channelId = self::getParam($arrInput, 'channelId');
        $cityId = self::getParam($arrInput, 'cityId');  //即将上映使用
        $redisChannelId = $this->switchChannelId($channelId);
        $return['data'] = $this->model("Message")->getPositionHash($redisChannelId, $openId);
        //首页toast弹出层
        $return['data']['upTime'] = isset($return['data']['upTime']) ? $return['data']['upTime'] : time();
        //获取是否有未过期的红包 或者 选座券推送消息(包含新到账 或者 即将过期)
        $return['data'] = $this->getBonusStatus($return['data']) + $return['data'];
        //查询可用和即将到期的红包数量和选座券数量
        $returnBonusData = $this->queryBonusList($channelId, $openId);
        $return['data'] = $returnBonusData + $return['data'];//合并返回数据
        //未读消息总数

        $return['data']['unread'] = $this->model("Message")->getTotalUnReadMsg($redisChannelId, $openId);
        //是否 "我的"位置上存在红点
        $return['data']['my'] = $this->checkMyRedPoint($return['data']);
        //即将上映红点
        $return['data']['will_preview'] = 0;
        $arrData = $this->model('Movie')->getMovieWillPreview($channelId, $cityId);
        if(!empty($arrData['star_meet'])){
            //cookie
            $arrShowedStarMeet = !empty($_COOKIE['wepiao_star_meet_showed']) ? unserialize($_COOKIE['wepiao_star_meet_showed']) : [];
            $lastShowScheDate = '';
            $lastShowScheDateMeet = [];
            //1、获取最近的场次日期和对应的明星见面会
            foreach ($arrData['star_meet'] as $key => $arrStarMeet) {
                $scheDate = date('Ymd', $arrStarMeet['sche_time']);
                if (empty($lastShowScheDate) || ($scheDate <= $lastShowScheDate)) {
                    $lastShowScheDate = $scheDate;
                }
                if (empty($lastShowScheDateMeet[$lastShowScheDate])) {
                    $lastShowScheDateMeet[$lastShowScheDate] = [];
                }
                $lastShowScheDateMeet[$lastShowScheDate][] = $arrStarMeet;
            }
            $arrValidMeet = !empty($lastShowScheDateMeet[$lastShowScheDate]) ? $lastShowScheDateMeet[$lastShowScheDate] : [];
            if (!empty($arrValidMeet)) {
                foreach ($arrValidMeet as $arrStarMeet) {
                    if (!in_array($arrStarMeet['sche_id'], $arrShowedStarMeet)) {
                        $return['data']['will_preview'] = 1;
                        break;
                    }
                }
            }
        }

        return $return;
    }

    /**
     * 获取是否有未过期的红包 或者 选座券推送消息(包含新到账 或者 即将过期)
     * @param array $hashData hash数据
     * @return array 是否有新红包 或者 选座券 1有 0 无 或 已过期
     */
    private function getBonusStatus($hashData)
    {
        $return = array('red' => 0, 'seat' => 0, 'redWill' => 0, 'seatWill' => 0);
        foreach ($return as $field => $value) {
            if (isset($hashData[$field]) && $hashData[$field] > time()) {
                $return[$field] = 1;
            }
        }
        return $return;
    }


    /**
     * 查询红包优惠列表
     * @param $channelId
     * @param $openId
     */
    private function queryBonusList($channelId, $openId)
    {
        $return = [
            'redNum' => 0,
            'seatNum' => 0,
            'redNumExpire' => 0,
            'seatNumExpire' => 0,
            'redWill' => 0,
            'seatWill' => 0,
            'red' => 0,
            'seat' => 0
        ];
        //查询可用和即将到期的红包/选座券数量
        $params['channelId'] = $channelId;
        $params['openId'] = $openId;
        $params['invalidFlg'] = 1;//是否返回不可用优惠，默认返回 1返回可用红包 选座券
        $params['num'] = 999;
        $data = $this->service("Bonus")->queryBonus($params);//获取红包列表

        if (!empty($data) && $data['ret'] == 0) {
            $return = $this->totalCount($data['data'], $return);
        }
        return $return;
    }

    /**
     * 由于接口提供的选座券数量不是可用选座券数量    单独计算可用选座券数量  之后出新接口再切换新接口
     * @param array $data 红包优惠列表
     * @param array $return 格式化返回
     * @return array
     */
    private function totalCount($data, $return)
    {
        $totalArr = $return;
        if (isset($data['bonus_list'])) {
            foreach ($data['bonus_list'] as $row) {
                if ($row['iStatus'] == 1) {
                    $totalArr['redNum']++; //可用红包数量统计
                    if (date('Y-m-d', strtotime("+2 days")) >= $row['sDueTime']) {
                        $totalArr['redNumExpire']++;
                    }//即将到期红包数量统计
                }
            }
        }
        if (isset($data['presell_list'])) {
            foreach ($data['presell_list'] as $row) {
                if ($row['iStatus'] == 1) {
                    $totalArr['seatNum']++;//可用选座券数量统计
                    if (date('Y-m-d', strtotime("+2 days")) >= $row['sDueTime']) {
                        $totalArr['seatNumExpire']++;
                    }//即将到期选座券数量统计
                }
            }
        }
        //如果列表中不存在可用红包 或者即将到期的红包  则对应的位置上也不会出现红点
        if ($totalArr['redNumExpire'] == 0) {
            $totalArr['redWill'] = 0;
        } else {
            unset($totalArr['redWill']);
        }
        if ($totalArr['seatNumExpire'] == 0) {
            $totalArr['seatWill'] = 0;
        } else {
            unset($totalArr['seatWill']);
        }
        if ($totalArr['redNum'] == 0) {
            $totalArr['red'] = 0;
        } else {
            unset($totalArr['red']);
        }
        if ($totalArr['seatNum'] == 0) {
            $totalArr['seat'] = 0;
        } else {
            unset($totalArr['seat']);
        }
        return $totalArr;
    }

    /**
     * 功能一：根据位置清除红点状态接口
     * 功能二：根据位置清除消息盒子提示数量接口
     */
    public function clearRedPoint(array $arrInput = [])
    {
        $return = $this->getStOut();
        $openId = self::getParam($arrInput, 'openId');
        $channelId = $this->switchChannelId(self::getParam($arrInput, 'channelId'));
        $position = self::getParam($arrInput, 'position');
        //得到所有账户关联openid     *********优化3  28渠道不需要查询用户关系树
        if ($channelId == 89) {
            $openIdList = $this->service('User')->getAllOpenIds($openId);
        } else {
            $openIdList = [];
        }
        $return['data'] = $this->model("Message")->clearRedPoint($channelId, $openId, $position, $openIdList);
        return $return;
    }

    /**
     * 根据各种数据判断 my的位置上是否有红点
     * @param $data
     */
    public function checkMyRedPoint($data)
    {
        if ($data['seat'] == 0 && $data['red'] == 0 && $data['redWill'] == 0 && $data['seatWill'] == 0 && $data['unread'] == 0) {
            return 0;
        } else {
            return $data['my'];
        }
    }

    /**
     * 一次性消息  折扣卡促销
     * @param array $arrInput
     */
    public function getDiscountCardMessageOnce(array $arrInput = [])
    {
        $return = $this->getStOut();
        $openId = self::getParam($arrInput, 'openId');
        $channelId = $this->switchChannelId(self::getParam($arrInput, 'channelId'));
        $return['data'] = $this->model("Message")->getDiscountCardMessageOnce($channelId, $openId);
        return $return;
    }

    //换算redis channelId   由于IOS 安卓 = 89
    private function switchChannelId($channelId)
    {
        return ($channelId == 8 || $channelId == 9) ? 89 : $channelId;
    }
}