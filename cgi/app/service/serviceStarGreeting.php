<?php
/**
 * Created by PhpStorm.
 * User: xiangli
 * Date: 2017/1/16
 * Time: 15:45
 */

namespace sdkService\service;


class serviceStarGreeting extends serviceBase
{
    public function getGreet(array $arrInput)
    {
        $channelId=self::getParam($arrInput, 'channelId');
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        //获取线上的明星祝福id
        $id = $this->model("StarGreeting")->queryOnlineGreeting($channelId);
        $time = time();
        if (!$id) {
            return $return;
        }
        $info = $this->model("StarGreeting")->getGreetingInfo($id);
        if (!$info) {
            return $return;
        }
        $greeting = json_decode($info, true);
        //var_dump($greeting);
        $content = json_decode($greeting['content'], true);
        //var_dump($content);
        //只有在区间内的才显示
        $hostPrefix = 'https://appnfs.wepiao.com';
        if ($greeting['start_time'] <= $time && $time <= $greeting['end_time'] && $greeting['status'] == 1) {
            if ($greeting['type'] == 1) {
                $keys = $this->_getDayKeys($time);
                if($keys){
                    if($channelId==28) {
                        //头像语音是必填项
                        $mustField = ['star_img', 'voice_url', 'tips'];
                        $flag = true;
                        foreach ($mustField as $val) {
                            if (!isset($content[$keys][$val]) || empty($content[$keys][$val])) {
                                $flag = false;
                            }
                        }
                    }
                    if($channelId==3) {
                        //头像文案链接是必填项
                        $mustField = ['star_img', 'jump_url', 'tips'];
                        $flag = true;
                        foreach ($mustField as $val) {
                            if (!isset($content[$keys][$val]) || empty($content[$keys][$val])) {
                                $flag = false;
                            }
                        }
                    }
                    if ($flag) {
                        $return['data'] = array(
                            'id' => intval($id),
                            'type' => intval($greeting['type']),
                            'bg_img' => !empty($greeting['bg_img'])?$hostPrefix . $greeting['bg_img']:'',
                            'tips' => $content[$keys]['tips'],
                            'star_img' => !empty($content[$keys]['star_img'])?$hostPrefix . $content[$keys]['star_img']:'',
                            'voice_url' => !empty($content[$keys]['voice_url'])?$hostPrefix . $content[$keys]['voice_url']:'',
                            'jump_url' => $content[$keys]['jump_url'],
                        );
                    }
                }
            } else {
                if($channelId==28){
                    //头像语音是必填项
                    $mustField = ['star_img', 'voice_url', 'tips'];
                    $flag = true;
                    foreach ($mustField as $val) {
                        if (!isset($content[$val]) || empty($content[$val])) {
                            $flag = false;
                        }
                    }
                }
                if($channelId==3) {
                    //头像文案链接是必填项
                    $mustField = ['star_img', 'jump_url', 'tips'];
                    $flag = true;
                    foreach ($mustField as $val) {
                        if (!isset($content[$val]) || empty($content[$val])) {
                            $flag = false;
                        }
                    }
                }
                if ($flag) {
                    $return['data'] = array(
                        'id' => intval($id),
                        'type'=>intval($greeting['type']),
                        'bg_img' => !empty($greeting['bg_img'])?$hostPrefix . $greeting['bg_img']:'',
                        'tips' => $content['tips'],
                        'star_img' =>!empty($content['star_img'])?$hostPrefix . $content['star_img']:'',
                        'voice_url' =>!empty($content['voice_url'])?$hostPrefix . $content['voice_url']:'',
                        'jump_url'=>$content['jump_url'],
                    );
                }
            }
        }
        return $return;
    }

    /**
     * 获取当前时间所在的区间
     * @param $time
     * @return bool|int|string
     */
    private function _getDayKeys($time)
    {
        $today = date('Y-m-d');
        $keys = false;
        $arrTimeArea = array(
            'morning' => array(strtotime($today . ' 06:00:00'), strtotime($today . ' 08:59:59')),
            'forenoon' => array(strtotime($today . ' 09:00:00'), strtotime($today . ' 11:59:59')),
            'afternoon' => array(strtotime($today . ' 12:00:00'), strtotime($today . ' 17:59:59')),
            'night' => array(strtotime($today . ' 18:00:00'), strtotime($today . ' 23:59:59')),
        );
        //echo date('Y-m-d H:i:s',$time);
        foreach ($arrTimeArea as $key => $val) {
            if ($time >= $val[0] && $time <= $val[1]) {
                $keys = $key;
                break;
            }
        }
        return $keys;
    }
}