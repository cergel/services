<?php
/**
 * Created by PhpStorm.
 * User: bbq
 * Date: 17/4/18
 * Time: 下午1:44
 */

namespace sdkService\model;


class Message extends BaseNew
{
    private $allow_types = [1, 2];
    private $message_config = [
        1 => [
            'name' => "小票儿送福利",
            'icon' => "https://baymax-cos.wepiao.com/message-center/20170609/1709/fuli.png"
        ],
        2 => [
            'name' => "系统通知",
            'icon' => "https://baymax-cos.wepiao.com/message-center/20170609/1709/system.png"
        ],
    ];

    /**
     * 获取用户的消息列表(包含最近一条信息)
     * @param $channelId
     * @param $openId
     * @return array
     */
    public function getRecentMsg($channelId, $openId)
    {
        $return = [];
        $redis = $this->redis($channelId, MESSAGE_CENTER);
        $hashTotalData = $redis->WYhGetAll("hash:{$channelId}:{$openId}"); //获取某类型消息未读数HASH
        foreach ($this->message_config as $key => $message_type_info) {

            //获取最近的一条信息pushId
            $setkey = "set:{$channelId}:{$openId}:{$key}";
            $recent = $redis->WYzRevRange($setkey, 0, 1);
            if (!empty($recent)) {
                $getRecentMsg = json_decode($recent[0], true);
            } else {
                $getRecentMsg = [];
            }
            //通过pushId兑换最近一条消息
            if ($getRecentMsg) {
                $_return = [];
                if (isset($getRecentMsg['template_id']) && !empty($getRecentMsg['template_id'])) {
                    $template = $this->getMessageTemplate($channelId, $getRecentMsg['template_id']);
                    if (isset($getRecentMsg['user_data'])) {
                        $template['content'] = sprintf($template['content'], $getRecentMsg['user_data']);
                    }
                    $template['sendtime'] = isset($getRecentMsg['t']) ? $getRecentMsg['t'] : time();
                    if (isset($getRecentMsg['card_no'])) {
                        $template['card_no'] = $getRecentMsg['card_no'];
                    }
                    $msgData = $template;
                } else {
                    if (isset($getRecentMsg['msg_id']) && !empty($getRecentMsg['msg_id'])) {
                        $msgData = $this->getMessageInfo($channelId, $getRecentMsg['msg_id']);
                    } else {
                        $msgData = $getRecentMsg;
                    }
                }
                $_return['recent'] = $msgData;
                $_return['msgtype'] = $key;
                $_return['icon'] = $message_type_info['icon'];
                $_return['name'] = $message_type_info['name'];
                //获取该类型未读消息数
                $_return['unread'] = isset($hashTotalData[$key]) ? $hashTotalData[$key] : 0;
                $_return['uptime'] = isset($hashTotalData['uptime' . $key]) ? $hashTotalData['uptime' . $key] : 0;
                $return[] = $_return;
            } else {
                $_return['recent'] = new \stdClass();
                $_return['msgtype'] = $key;
                $_return['icon'] = $message_type_info['icon'];
                $_return['name'] = $message_type_info['name'];
                //获取该类型未读消息数
                $_return['unread'] = isset($hashTotalData[$key]) ? $hashTotalData[$key] : 0;
                $_return['uptime'] = isset($hashTotalData['uptime' . $key]) ? $hashTotalData['uptime' . $key] : 0;
                $return[] = $_return;
            }
        }
        //消息类型根据每种类型消息的最新PUSH时间进行消息类型展示排序  现在只上一种类型 暂不需要
        return $return;
    }

    /**
     * 获取某类型消息列表 并清空未读消息数
     * @param int $channelId
     * @param string $openId
     * @param int $type
     * @param int $page
     * @return array
     */
    public function getTypeMsg($channelId, $openId, $type, $page, $pageSize = 10)
    {
        $return = [];

        $redis = $this->redis($channelId, MESSAGE_CENTER);

        $hashkey = "hash:{$channelId}:{$openId}";
        //本类目下所有的未读消息标记为已读
        $redis->WYhSet($hashkey, $type, 0);//清空未读消息数
        //统计出消息的条数与分页的参数
        $typeMsgKey = "set:{$channelId}:{$openId}:{$type}";
        $msgTotal = (int)$redis->WYzCard($typeMsgKey);
        $pageNum = ceil($msgTotal / $pageSize);
        $start = ($page - 1) * $pageSize;
        $offset = (($page - 1) * $pageSize) + ($pageSize - 1);
        $msg = $redis->WYzRevRange($typeMsgKey, $start, $offset);
        if (!$msg) {
            $msg = [];
        }
        $msgData = [];
        foreach ($msg as $item) {
            $item = json_decode($item, true);
            if (isset($item['template_id']) && !empty($item['template_id'])) {
                $template = $this->getMessageTemplate($channelId, $item['template_id']);
                $template['sendtime'] = isset($item['t']) ? $item['t'] : time();
                if (isset($item['user_data'])) {
                    $template['content'] = sprintf($template['content'], $item['user_data']);
                }
                if (isset($item['card_no'])) {
                    $template['card_no'] = $item['card_no'];
                }
                if (isset($item['url'])) {
                    $template['url'] = $item['url'];
                }
                $msgData[] = $template;
            } else {
                if (isset($item['msg_id']) && !empty($item['msg_id'])) {
                    $tmpData = $this->getMessageInfo($channelId, $item['msg_id']);
                    $msgData[] = $tmpData;
                } else {
                    $msgData[] = $item;
                }
            }
        }
        $return['totalPage'] = $pageNum;
        $return['pageSize'] = $pageSize;
        $return['current'] = $page;
        $return['total'] = $msgTotal;
        $return['icon'] = $this->message_config[$type]['icon'];
        $return['name'] = $this->message_config[$type]['name'];
        $return['message'] = $msgData;
        return $return;
    }

    /**
     * 获取消息体
     * @param int $msgid
     */
    public function getMessageInfo($channelId, $msgid)
    {
        $redis = $this->redis($channelId, MESSAGE_CENTER);
        $msg = $redis->WYhGet("hash:messagebody", $msgid);
        if ($msg) {
            $return = json_decode($msg, true);
            $return['imgurl'] = $return['img_url'];
            $return['sendtime'] = $return['send_time'];
            unset($return['send_time'], $return['img_url'], $return['push_user_type'], $return['is_push'], $return['push_content']);
            return $return;
        } else {
            return false;
        }
    }

    public function getMessageTemplate($channelId, $templateId)
    {
        $redis = $this->redis($channelId, MESSAGE_CENTER);
        $template = $redis->WYhGet("template", $templateId);
        if ($template) {
            $tmpArr = explode('|', $template);
            $ret['title'] = $tmpArr[0];
            $ret['content'] = $tmpArr[1];
            return $ret;
        } else {
            return false;
        }
    }

    /**
     * 功能一：去掉某个位置的红点提醒
     * 功能二：去掉消息盒子某类型位置的未读消息数提醒
     */
    public function clearRedPoint($channelId, $openId, $position, $openIdList)
    {
        switch ($position) {
            //功能一
            case 'my': //我的
            case 'red'://红包
            case 'seat'://选座券
            case 'redWill'://红包
            case 'seatWill'://选座券    
                $redis = $this->redis($channelId, MESSAGE_CENTER);
                if ($channelId == 89) { //  IOS 和安卓 特殊  清楚红点要清楚所有登录方式下对应的key的红点状态
                    foreach ($openIdList as $openId) {
                        $this->clearRedPointByOpenId($channelId, $openId, $position);
                    }
                } else { //  微信 和 手Q  清楚红点只清楚自己的红点
                    $this->clearRedPointByOpenId($channelId, $openId, $position);
                }
                break;
            //功能二 ---------------------------------------------
            case '1'://小票送福利
            case '2'://待评论
            case '3'://互动
            case '4'://电影票
            case '5'://系统通知
            case '6'://猜电影
                $redis = $this->redis($channelId, MESSAGE_CENTER);
                $key = "{$channelId}:{$openId}:redpoint";
                $redis->WYhSet($key, 'my', 0);
                $redis->WYhSet($key, $position . 'ClearTime', time());
                $key = "hash:{$channelId}:{$openId}";
                $redis->WYhSet($key, $position, 0);
                $redis->WYhSet($key, "all", 0);
                //标记队列更新MYSQL 消息关系数据 所有未读消息为已读
                $redis->WYlPush("mid_queue_key", "{$openId}:{$position}");
                break;
        }
        return true;
    }

    private function clearRedPointByOpenId($channelId, $openId, $position)
    {
        $redis = $this->redis($channelId, MESSAGE_CENTER);
        $key = "{$channelId}:{$openId}:redpoint";
        if ($position == 'red' || $position == 'seat') {
            $redis->WYhSet($key, $position . 'Will', 0);
            $redis->WYhSet($key, 'my', 0);
            $redis->WYhSet($key, 'upTime', time());
        }
        $redis->WYhSet($key, $position, 0);
        $redis->WYhSet($key, $position . 'ClearTime', time());
    }

    //获取消息未读总数
    public function getTotalUnReadMsg($channelId, $openId)
    {
        $total = 0;
        $redis = $this->redis($channelId, MESSAGE_CENTER);
        $key = "hash:{$channelId}:{$openId}";
//        foreach ($this->allow_types as $num) {
//            $total += (int)$redis->WYhGet($key, $num);
//        }
        $total += (int)$redis->WYhGet($key, "all");
        return $total;
    }

    /**
     * 一次性大促消息
     * @params int $channelId 渠道ID   89 APP  3 微信 28 手Q
     * @params string $openId
     */
    public function getDiscountCardMessageOnce($channelId, $openId)
    {
        $redis = $this->redis($channelId, MESSAGE_CENTER);
        $strKey = "discount_card:{$channelId}:{$openId}";
        $redisData = $redis->WYget($strKey);
        if($redisData){
            $data = json_decode($redisData, true);
            if (isset($data['is_toast'])) { //此消息已读 返回空
                return "";
            } else {
                $data['is_toast'] = 1;
                $redis->WYset($strKey, json_encode($data));//设置状态已读
                $redis->WYexpire($strKey, 2592000);//设置过期时间
                $data['toast'] = "<span>" . $data['cinema_name'] . "</span><br/><span>折扣卡大促中，首单最高立减20元</span>";
                return $data;
            }
        } else {
            return "";
        }
    }


    /**
     * @param string $key redis hash key
     * @return array
     */
    public function getPositionHash($channelId, $openId)
    {
        $redis = $this->redis($channelId, MESSAGE_CENTER);
        $key = "{$channelId}:{$openId}:redpoint";
        return $redis->WYhGetAll($key);
    }


    /**
     * @param $channelId 渠道
     * @param $key  redis key
     * @param $setArr
     * @return bool
     */
    public function setPositionHash($channelId, $openId, $setArr)
    {
        $redis = $this->redis($channelId, MESSAGE_CENTER);
        $key = "{$channelId}:{$openId}:redpoint";
        return $redis->WYhMset($key, $setArr);
    }

    /**
     * 获取有序集合的数据  如果传了score  则获取>=score的数据
     * @param $key
     * @param $score
     * @return bool
     */
    public function getWillExpireSet($channelId, $openId, $scoreStart, $scoreEnd)
    {
        $redis = $this->redis($channelId, MESSAGE_CENTER);
        $key = "willexpire:{$openId}";
        return $redis->WYzRangeByScore($key, $scoreStart, $scoreEnd);
    }

    /**
     * 删除有序集合的数据  如果传了score  则删除<score的数据
     * @param $key
     * @param $score
     * @return bool
     */
    public function delWillExpireSet($channelId, $openId, $score)
    {
        $redis = $this->redis($channelId, MESSAGE_CENTER);
        $key = "willexpire:{$openId}";
        return $redis->WYzRemRangeByScore($key, 0, $score);
    }


}
