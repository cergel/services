<?php

/**
 * 说明：这里需要说明一下，数组的配置中，key通常代表数据库名称，但是并不决定，真正的数据库名称，是配置数组中的db项,
 * 之所以这样做，是因为很多时候，线上库的名字和测试库的名字，并不相同，对于同一个业务，没有必要在配置中，配置多个，来标识同一个业务
 */

$dbConn = [
    'conn1' =>
        [
            'host' => '192.168.101.77',
            'port' => '3306',
            'user' => 'test',
            'passwd' => 'test',
        ],
    'dbHistoryOrder' =>
        [
            'host' => '10.3.10.198',
            'port' => '3306',
            'user' => 'wjread',
            'passwd' => 'duvnruJHlfneal',
        ],
    'localhost' =>
        [
            'host' => '127.0.0.1',
            'port' => '3306',
            'user' => 'root',
            'passwd' => '',
        ],
];

$dbConf = [
    'db1' => [
        'conn' => $dbConn['conn1'],
        'db' => 'operation_platform',
    ],
    'dbApp' => [//评分、评论相关数据库
        'conn' => $dbConn['conn1'],
        'db' => 'db_app',
    ],
    'dbAdmin' => [//管理数据库
        'conn' => $dbConn['conn1'],
        'db' => 'db_wxmovieadmin',
    ],
    'dbHistoryOrder' => [//历史订单数据库
        'conn' => $dbConn['dbHistoryOrder'],
        #'conn' => $dbConn['localhost'],
        'db' => 'rfdata',
    ],
    'dbActive' => [//管理数据库
        'conn' => $dbConn['conn1'],
        'db' => 'db_activeuser',
    ],
    'dbPee' => [//活动相关‚
        'conn' => $dbConn['conn1'],
        'db' => 'pee',
    ],
];

return $dbConf;

?>