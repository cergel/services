<?php
/**
 * 开关控制
 * 1、1为开启，0为关闭。分为all与exceptChannelIds两项，all表示所有渠道，exceptChannelIds代表例外的渠道，与all相反，默认为空数组。
 * 2、示例：all置为1时，exceptChannelIds修改为[3,28]代表3和28渠道为0；all置为0时，exceptChannelIds修改为[3,28]代表3和28渠道为1
 * 3、想把某开关设为全部开启或关闭时，一定记得清空exceptChannelIds！！
 * 4、最终结果等同于全局$config中['roomSeat'=>1,'demote'=>0,..]，程序中使用时可以使用 \wepiao::$config['roomSeat'] 取到开关值。
 */

$arrLimit = [
    //key为"service名_action名"，value为规则，规则可以为多个，注意：limit下的key必须全部小写（因为API项目中的调用方式写的不一样，PHP的方法又不分区大小写...）
    //关于限制规则，目前支持IP和openid，有一个达到限制，都会认为限制成功
    'limit'   => [
        //全局规则
        'all'       => [],
        //指定接口规则（优先级大于全局规则）
        //'sche_qryschev2' => [
        'test_test' => [
            //哪些渠道受限
            'channelIds' => [3, 28],
            //规则
            'rules'      => [
                //ip限制中，limit为单位时间内限制次数，time为单位，比如60秒。
                'ip' => ['limit' => 5000, 'time' => 60],
                //'openid' => ['limit' => 5000, 'time' => 60], //限制openid的话，你调用service，必须把openid传过来
            ],
            //如果被限制了，返回什么（比如有些接口data是对象，有些是数组，或者其他，或者有些ret是字符串，有些是数字，总之，这里你可以自定义）
            //下面的示例是，转换为最终给用户的返回值，就是 ['ret'=>'02','sub'=>'10081002','msg'=>'达到了限制次数','data'=>[]]
            'return'     => ['errorcode' => ERRORCODE_LIMIT_OVER_ERROR, 'res' => ['data' => []]],
        ],
    ],
    //哪些接口限制IP黑名单（注意：这个会全局限制）
    'blackip' => [
        //全局规则
        'all'                          => [],
        //指定接口规则（优先级大于全局规则）
        'sche_qryschev2'               => [
            //哪些渠道受限（如果为空，那就是不限渠道了）
            'channelIds'       => [6],
            //规则（目前这个没有实际用户，拓展用）
            'rules'            => [],
            //是否中断后续执行，如果是1，那么检测到黑名单，直接就return错误信息了，如果是0，会给全局config设置一个isIpBlack为1的值，后续怎么操作就是你的事情了
            'interuptContinue' => 0,
            //下面的示例是，转换为最终给用户的返回值，就是 ['ret'=>'02','sub'=>'10081002','msg'=>'达到了限制次数','data'=>[]]
            'return'           => ['errorcode' => ERRORCODE_LIMIT_IP_BLACK, 'res' => ['data' => []]],
        ],
        //指定接口规则（优先级大于全局规则）
        'sche_qryschechannel'               => [
            //哪些渠道受限（如果为空，那就是不限渠道了）
            'channelIds'       => [6],
            //规则（目前这个没有实际用户，拓展用）
            'rules'            => [],
            //是否中断后续执行，如果是1，那么检测到黑名单，直接就return错误信息了，如果是0，会给全局config设置一个isIpBlack为1的值，后续怎么操作就是你的事情了
            'interuptContinue' => 0,
            //下面的示例是，转换为最终给用户的返回值，就是 ['ret'=>'02','sub'=>'10081002','msg'=>'达到了限制次数','data'=>[]]
            'return'           => ['errorcode' => ERRORCODE_LIMIT_IP_BLACK, 'res' => ['data' => []]],
        ],
        'sche_readcinemascheandformat' => [
            //哪些渠道受限（如果为空，那就是不限渠道了）
            'channelIds'       => [6],
            //规则（目前这个没有实际用户，拓展用）
            'rules'            => [],
            //是否中断后续执行，如果是1，那么检测到黑名单，直接就return错误信息了，如果是0，会给全局config设置一个isIpBlack为1的值，后续怎么操作就是你的事情了
            'interuptContinue' => 0,
            //下面的示例是，转换为最终给用户的返回值，就是 ['ret'=>'02','sub'=>'10081002','msg'=>'达到了限制次数','data'=>[]]
            'return'           => ['errorcode' => ERRORCODE_LIMIT_IP_BLACK, 'res' => ['data' => []]],
        ],
        'sche_readcinemasche'          => [
            //哪些渠道受限（如果为空，那就是不限渠道了）
            'channelIds'       => [6],
            //规则（目前这个没有实际用户，拓展用）
            'rules'            => [],
            //是否中断后续执行，如果是1，那么检测到黑名单，直接就return错误信息了，如果是0，会给全局config设置一个isIpBlack为1的值，后续怎么操作就是你的事情了
            'interuptContinue' => 0,
            //下面的示例是，转换为最终给用户的返回值，就是 ['ret'=>'02','sub'=>'10081002','msg'=>'达到了限制次数','data'=>[]]
            'return'           => ['errorcode' => ERRORCODE_LIMIT_IP_BLACK, 'res' => ['data' => []]],
        ],
    ],
];

return $arrLimit;
?>