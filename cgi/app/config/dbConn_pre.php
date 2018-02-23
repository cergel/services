<?php

$dbConn = [

    'conn1' => [
        'host' => '10.66.120.110',
        'port' => '3306',
        'user' => 'h5_user',
        'passwd' => '2014h5user',
    ],
    'conn2' => [
        'host' => '10.66.120.110',
        'port' => '3306',
        'user' => 'app_weiying',
        'passwd' => 'App_weiying',
    ],
    'conn3' => [
        'host' => '10.66.120.110',
        'port' => '3306',
        'user' => 'db_app',
        'passwd' => 'App_weiying',
    ],
    'conn4' => [
        'host' => '10.66.120.110',
        'port' => '3306',
        'user' => 'db_user',
        'passwd' => 'App_weiying',
    ],
    'conn5' => [
        'host' => '10.66.112.5',
        'port' => '3306',
        'user' => 'db_bonus',
        'passwd' => 'wxmovie.com',
    ],
    'conn6' => [
        'host' => '10.66.112.50',
        'port' => '3306',
        'user' => 'opensystem',
        'passwd' => 'wxmovie.com',
    ],
    'conn7' => [
        'host' => '10.66.112.41',
        'port' => '3306',
        'user' => 'db_presell',
        'passwd' => 'wxmovie.com',
    ],
    'conn8' => [
        'host' => '10.66.120.110',
        'port' => '3306',
        'user' => 'db_wxmovieadmin',
        'passwd' => 'db_wxmovieadmin',
    ],
    'conn9' => [
        'host' => '10.135.118.88',
        'port' => '3306',
        'user' => 'data_user',
        'passwd' => '8kaWyeBLbcXLvzmIttQLoEbs2',
    ],
    'conn10' => [
        #'host' => '10.3.10.177',
        'host' => '10.66.143.144',
        'port' => '3306',
        'user' => 'wjread',
        'passwd' => 'duvnruJHlfneal',
    ],


];

$dbConf = [
    'db1' => [
        'conn' => $dbConn['conn1'],
        'db' => 'wxmovie',
    ],
    'dbApp' => [//评论
        'conn' => $dbConn['conn9'],
        'db' => 'db_app',
    ],
    'dbAdmin' => [//baymax,
        'conn' => $dbConn['conn9'],
        'db' => 'db_wxmovieadmin',
    ],
    'dbHistoryOrder' => [//历史订单,
        'conn' => $dbConn['conn10'],
        'db' => 'rfdata',
    ],
    'dbActive' => [//活动相关
        'conn' => $dbConn['conn9'],
        'db' => 'db_activeuser',
    ],
    'dbPee' => [//活动相关‚
        'conn' => $dbConn['conn9'],
        'db' => 'pee',
    ],
];

return $dbConf;

?>