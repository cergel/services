<?php

$dbConf = require(__DIR__ . '/dbConn.php');

return [

    'db1' => [ //"用户收藏影院" 在使用
        'dsn' => "mysql:host={$dbConf['db1']['conn']['host']};port={$dbConf['db1']['conn']['port']};dbname={$dbConf['db1']['db']}",
        'user' => $dbConf['db1']['conn']['user'],
        'passwd' => $dbConf['db1']['conn']['passwd'],
        'charset' => 'utf8',
    ],

    'dbApp' => [ //评分评论相关数据库
        'dsn' => "mysql:host={$dbConf['dbApp']['conn']['host']};port={$dbConf['dbApp']['conn']['port']};dbname={$dbConf['dbApp']['db']}",
        'user' => $dbConf['dbApp']['conn']['user'],
        'passwd' => $dbConf['dbApp']['conn']['passwd'],
        'charset' => 'utf8',
        ],
    'dbAdmin' => [ //管理数据库
        'dsn' => "mysql:host={$dbConf['dbAdmin']['conn']['host']};port={$dbConf['dbAdmin']['conn']['port']};dbname={$dbConf['dbAdmin']['db']}",
        'user' => $dbConf['dbAdmin']['conn']['user'],
        'passwd' => $dbConf['dbAdmin']['conn']['passwd'],
        'charset' => 'utf8',
    ],
    'dbHistoryOrder' => [ //历史订单数据库
        'dsn' => "mysql:host={$dbConf['dbHistoryOrder']['conn']['host']};port={$dbConf['dbHistoryOrder']['conn']['port']};dbname={$dbConf['dbHistoryOrder']['db']}",
        'user' => $dbConf['dbHistoryOrder']['conn']['user'],
        'passwd' => $dbConf['dbHistoryOrder']['conn']['passwd'],
        'charset' => 'utf8',
    ],
    'dbActive' => [ //评分评论相关数据库
        'dsn' => "mysql:host={$dbConf['dbActive']['conn']['host']};port={$dbConf['dbActive']['conn']['port']};dbname={$dbConf['dbActive']['db']}",
        'user' => $dbConf['dbActive']['conn']['user'],
        'passwd' => $dbConf['dbActive']['conn']['passwd'],
        'charset' => 'utf8',
    ],
    'dbPee' => [ //尿点相关数据库
    		'dsn' => "mysql:host={$dbConf['dbPee']['conn']['host']};port={$dbConf['dbPee']['conn']['port']};dbname={$dbConf['dbPee']['db']}",
    		'user' => $dbConf['dbPee']['conn']['user'],
    		'passwd' => $dbConf['dbPee']['conn']['passwd'],
    		'charset' => 'utf8',
    ],

];