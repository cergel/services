<?php

/**
 * 第三方秘钥等细心
 */

return [

    'baidu' => [
        'baidu_lbs_keys' => [
            '67Eiq5WIRqlkhBNCZsKWejTA'
        ],
        'baidu_lbs_ip_locate_url' => 'http://api.map.baidu.com/location/ip?ak=%s&ip=%s&coor=bd09ll',
    ],

    'tencent' => [
        // 腾讯地图的key
        'tencent_lbs_keys' => [
            'XUBBZ-OYN3O-HWRWZ-SXHEC-BQJ6J-CHB2I',
            /*'ZCBBZ-2IZR4-D5ZU7-XKCCB-PIY7O-YVBY7',
            'BFGBZ-3X43U-UJDVD-23KWJ-V7QC6-2HFB4',
            'Y6ZBZ-RZ5RJ-3Z3FO-FPUAL-IMKJQ-4MBHE',
            'XUBBZ-OYN3O-HWRWZ-SXHEC-BQJ6J-CHB2I',*/

        ],
        'tencent_lbs_ip_locate_url' => 'http://apis.map.qq.com/ws/location/v1/ip?key=%s&ip=%s',
        // 腾讯地图根据经纬度获取地区信息的api地址
        'tencent_lbs_url' => 'http://apis.map.qq.com/ws/geocoder/v1/',
    ],

];
?>