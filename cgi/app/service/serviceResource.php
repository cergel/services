<?php
/**
 * 三端静态资源Service
 * Created by PhpStorm.
 * User: bbq
 * Date: 17/4/18
 * Time: 下午1:20
 */

namespace sdkService\service;


class serviceResource extends serviceBase
{

    //获取首页图标列表
    public function getIconConfig($arrInput)
    {
        //没有数据默认返回值
        $return = ['ret' => 0, 'sub' => 0, 'data' => ['icon' => []]];
        //判断缓存中是否有信息如果由信息直接返回
        $ret = $this->model('Resource')->getIcon($arrInput['channelId']);
        $icon = [];
        if ($ret) {
            $json = json_decode($ret, true);
            $icon = $json['icon'];
        }
        $now = time();
        $out_icon = [];
        foreach ($icon as $value) {
            if ($value['start_time'] <= $now && $value['end_time'] > $now) {
                $tmpArr = [];
                $preFixIcon = 'icon';
                $preFixIconOn = '_on';
                for ($i = 1; $i <= 5; $i++) {
                    $tmpIcon = [
                        "icon" => $value[$preFixIcon . $i],
                        "click" => $value[$preFixIcon . $i . $preFixIconOn]
                    ];
                    array_push($tmpArr, $tmpIcon);
                }
                $out_icon[$value['created_time']] = $tmpArr;
            }
        }
        //选取所有可选的资源中最新创建的一个
        if ($out_icon) {
            $max = max(array_keys($out_icon));
            $return['data'] = $out_icon[$max];
        } else {
            $return['data'] = [];
        }
        return $return;
    }


    //获取首页图标列表
    public function getIconConfigV2($arrInput)
    {
        $return = self::getStOut();
        //判断缓存中是否有信息如果由信息直接返回
        $iconInfo = $this->model('Resource')->getIcon($arrInput['channelId']);
        $icons = [];
        if ($iconInfo) {
            $iconArr = json_decode($iconInfo, true);
            $icons = $iconArr['icon'];
        }
        $now = time();
        $out_icon = [];
        if(!empty($icons)){
            foreach ($icons as $value) {
                if ($value['start_time'] <= $now && $value['end_time'] > $now) {
                    $tmpArr = [];
                    $preFixIcon = 'icon';
                    $preFixIconOn = '_on';
                    for ($i = 1; $i <= 5; $i++) {
                        $tmpIcon = [
                            "icon" => $value[$preFixIcon . $i],
                            "click" => $value[$preFixIcon . $i . $preFixIconOn]
                        ];
                        array_push($tmpArr, $tmpIcon);
                    }

                    $out_icon[$value['created_time']]['icon'] = $tmpArr;
                    $out_icon[$value['created_time']]['icon_compact'] = $value['icon_compact'];
                    $out_icon[$value['created_time']]['icon_color'] = $value['icon_color'];
                }
            }
        }

        //选取所有可选的资源中最新创建的一个
        if ($out_icon) {
            $max = max(array_keys($out_icon));
            $return['data'] = $out_icon[$max];
        } else {
            $return['data'] = new \stdClass();
        }
        return $return;
    }

}