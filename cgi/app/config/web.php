<?php

require_once(__DIR__ . '/constant.php');
require_once(__DIR__ . '/commonConstant.php');  //获得通用配置
require_once(__DIR__ . '/apiUrl.php');  //获得apiurl
require_once(__DIR__ . '/errorConstant.php');   //获得错误信息定义常量
require_once(__DIR__ . '/userErrorConstant.php');

return array_merge([
    //容器配置
    'container' => [
        //日志记录器`
        'logger' => [
            'class' => "\\sdkService\\helper\\Log",
            'path'  => '/data/logs/service/%s/%s.log',   //第一个%s为时间,第二个%s为服务名
        ],
        'db'     => [],
    ],
    //其他配置
    'params'    => [
        'db'              => require(__DIR__ . '/db.php'),
        'memcached'       => require(__DIR__ . '/memcached.php'),
        'redis'           => require(__DIR__ . '/redis.php'),
        'mongodb'         => require(__DIR__ . '/mongodb.php'),
        'thirdKeys'       => require(__DIR__ . '/thirdKeys.php'),
        'secret'          => require(__DIR__ . '/secret.php'),
        'errorCode'       => require(__DIR__ . '/errorCode.php'),
        'channelRestruct' => ['11', '6', '14', '27', '2', '18', '30', '45', '8', '9', '60', '47'],
        'fileCacheData'   => 1,
    ],
    'timezone'  => 'PRC',
], require_once(__DIR__ . '/switches.php'), require_once(__DIR__ . '/limit.php')); //开关控制, array_merge是为了保持与原结构相同
?>