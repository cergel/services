<?php
/**
 * 开关控制
 * 1、1为开启，0为关闭。分为all与exceptChannelIds两项，all表示所有渠道，exceptChannelIds代表例外的渠道，与all相反，默认为空数组。
 * 2、示例：all置为1时，exceptChannelIds修改为[3,28]代表3和28渠道为0；all置为0时，exceptChannelIds修改为[3,28]代表3和28渠道为1
 * 3、想把某开关设为全部开启或关闭时，一定记得清空exceptChannelIds！！
 * 4、最终结果等同于全局$config中['roomSeat'=>1,'demote'=>0,..]，程序中使用时可以使用 \wepiao::$config['roomSeat'] 取到开关值。
 */

$switches = [
    //是否做余票紧张渲染
    'roomSeat'    => [
        'all'              => 1, //所有渠道
        'exceptChannelIds' => [], //例外渠道，如[3, 28]
    ],
    //是否优惠降级
    'demote'      => [
        'all'              => 0, //所有渠道
        'exceptChannelIds' => [], //例外渠道，如[3, 28]
    ],
    //是否开启优惠到人
    'discount2p'  => [
        'all'              => 1, //所有渠道
        'exceptChannelIds' => [], //例外渠道，如[3, 28]
    ],
    //是每次进入座位图都解锁(0), 还是超过2个未支付订单才解锁(1)
    'unlockType' => [
        'all'              => 0, //所有渠道
        'exceptChannelIds' => [3,28,63,66,67,68], //例外渠道，如[3, 28]  添加微信小程序 多渠道 66 67  68  63
    ],
    //影院会员卡降级
    'demoteVipCard'      => [
        'all'              => 0, //所有渠道
        'exceptChannelIds' => [], //例外渠道，如[3, 28]
    ],
    //哪些渠道的影片信息走媒资库
    'movieNewStatic'      => [
        'all'              => 1, //所有渠道
        //'exceptChannelIds' => [3, 28, 80, 84, 63, 8, 9, 66, 67, 68], //例外渠道，如[3, 28]
        'exceptChannelIds' => [], //例外渠道，如[3, 28]
    ],
    //是否影片列表+购票标识,走大数据的数据源
    'movieListAndFlagBigData'      => [
        'all'              => 0, //所有渠道
        'exceptChannelIds' => [3, 28, 80, 84], //例外渠道，如[3, 28]
    ],
];


function switchConfig($channelId, $switches)
{
    foreach ($switches as $key => &$value) {
        $value = $value['all'] ^ in_array($channelId, $value['exceptChannelIds']);
    }
    
    return $switches;
}

return switchConfig(self::$channelId, $switches);
?>