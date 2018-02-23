<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/28
 * Time: 11:30
 */


return [
    
    STATIC_MOVIE_DATA      => [
        'common' => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => 'pre.rdb.grammy.wxmovie.com', 'port' => 8001, 'timeout' => 10, 'prefix' => 'wx_11_', 'database' => 4,],
                    'read'  => ['host' => 'pre.rdb.grammy.wxmovie.com', 'port' => 8001, 'timeout' => 10, 'prefix' => 'wx_11_', 'database' => 4,],
                ],
            ],
        ],
        '3'      => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => [
                        'host'    => '10.104.34.91',
                        'port'    => 8001,
                        'timeout' => 10,
                        'prefix'  => 'wp_3_',
                    ],
                    'read'  => [
                        'host'    => '10.104.34.91',
                        'port'    => 8001,
                        'timeout' => 10,
                        'prefix'  => 'wp_3_',
                    ],
                ],
            
            ],
        ],
        '8'      => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => [
                        'host'     => 'pre.rdb.grammy.wxmovie.com',
                        'port'     => 8001,
                        'timeout'  => 10,
                        "prefix"   => "wx_9_",
                        "database" => 3,
                    ],
                    'read'  => [
                        'host'     => 'pre.rdb.grammy.wxmovie.com',
                        'port'     => 8001,
                        'timeout'  => 10,
                        "prefix"   => "wx_9_",
                        "database" => 3,
                    ],
                ],
            
            ],
        ],
        '9'      => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => [
                        'host'     => 'pre.rdb.grammy.wxmovie.com',
                        'port'     => 8001,
                        'timeout'  => 10,
                        "prefix"   => "wx_9_",
                        "database" => 3,
                    ],
                    'read'  => [
                        'host'     => 'pre.rdb.grammy.wxmovie.com',
                        'port'     => 8001,
                        'timeout'  => 10,
                        "prefix"   => "wx_9_",
                        "database" => 3,
                    ],
                ],
            
            ],
        ],
        '28'     => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => [
                        'host'     => '10.104.34.91',
                        'port'     => 8001,
                        'timeout'  => 10,
                        'prefix'   => 'wx_28_',
                        'database' => 4,
                    ],
                    'read'  => [
                        'host'     => '10.104.34.91',
                        'port'     => 8001,
                        'timeout'  => 10,
                        'prefix'   => 'wx_28_',
                        'database' => 4,
                    ],
                ],
            
            ],
        ],
        '6'      => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => [
                        'host'     => 'pre.rdb.grammy.wxmovie.com',
                        'port'     => 8001,
                        'timeout'  => 10,
                        'prefix'   => 'wx_6_',
                        'database' => 6,
                    ],
                    'read'  => [
                        'host'     => 'pre.rdb.grammy.wxmovie.com',
                        'port'     => 8001,
                        'timeout'  => 10,
                        'prefix'   => 'wx_6_',
                        'database' => 6,
                    ],
                ],
            
            ],
        ],
        //招商银行(预上线临时先用线上的数据)
        '60'     => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => '10.66.134.109', 'port' => 8000, 'timeout' => 10, "prefix" => "wx_60_", "database" => 0,],
                    'read'  => ['host' => '10.66.134.109', 'port' => 8000, 'timeout' => 10, "prefix" => "wx_60_", "database" => 0,],
                ],
                [
                    'write' => ['host' => '10.66.133.78', 'port' => 8000, 'timeout' => 10, "prefix" => "wx_60_", "database" => 0,],
                    'read'  => ['host' => '10.66.133.78', 'port' => 8000, 'timeout' => 10, "prefix" => "wx_60_", "database" => 0,],
                ],
                [
                    'write' => ['host' => '10.66.133.66', 'port' => 8000, 'timeout' => 10, "prefix" => "wx_60_", "database" => 0,],
                    'read'  => ['host' => '10.66.133.66', 'port' => 8000, 'timeout' => 10, "prefix" => "wx_60_", "database" => 0,],
                ],
                [
                    'write' => ['host' => '10.66.133.65', 'port' => 8000, 'timeout' => 10, "prefix" => "wx_60_", "database" => 0,],
                    'read'  => ['host' => '10.66.133.65', 'port' => 8000, 'timeout' => 10, "prefix" => "wx_60_", "database" => 0,],
                ],
                [
                    'write' => ['host' => '10.66.133.165', 'port' => 8000, 'timeout' => 10, "prefix" => "wx_60_", "database" => 0,],
                    'read'  => ['host' => '10.66.133.165', 'port' => 8000, 'timeout' => 10, "prefix" => "wx_60_", "database" => 0,],
                ],
                [
                    'write' => ['host' => '10.66.133.139', 'port' => 8000, 'timeout' => 10, "prefix" => "wx_60_", "database" => 0,],
                    'read'  => ['host' => '10.66.133.139', 'port' => 8000, 'timeout' => 10, "prefix" => "wx_60_", "database" => 0,],
                ],
            ],
        ],
        //小程序63
        '63'     => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => '10.104.34.91', 'port' => 8001, 'timeout' => 3, "prefix" => "wx_63_", "database" => 5,],
                    'read'  => ['host' => '10.104.34.91', 'port' => 8001, 'timeout' => 3, "prefix" => "wx_63_", "database" => 5,],
                ],
            ],
        ],
        //小程序66
        '66'     => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => 'pre.rdb.grammy.wxmovie.com', 'port' => 8001, 'timeout' => 3, "prefix" => "wx_66_", "database" => 1,],
                    'read'  => ['host' => 'pre.rdb.grammy.wxmovie.com', 'port' => 8001, 'timeout' => 3, "prefix" => "wx_66_", "database" => 1,],
                ],
            ],
        ],
        //小程序67
        '67'     => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => 'pre.rdb.grammy.wxmovie.com', 'port' => 8001, 'timeout' => 3, "prefix" => "wx_66_", "database" => 1,],
                    'read'  => ['host' => 'pre.rdb.grammy.wxmovie.com', 'port' => 8001, 'timeout' => 3, "prefix" => "wx_66_", "database" => 1,],
                ],
            ],
        ],
        //小程序68
        '68'     => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => 'pre.rdb.grammy.wxmovie.com', 'port' => 8001, 'timeout' => 3, "prefix" => "wx_66_", "database" => 1,],
                    'read'  => ['host' => 'pre.rdb.grammy.wxmovie.com', 'port' => 8001, 'timeout' => 3, "prefix" => "wx_66_", "database" => 1,],
                ],
            ],
        ],
        '80'     => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => '10.104.15.195', 'port' => 8001, 'timeout' => 10, 'prefix' => 'wx_80_', 'database' => 1,],
                    'read'  => ['host' => '10.104.15.195', 'port' => 8001, 'timeout' => 10, 'prefix' => 'wx_80_', 'database' => 1,],
                ],
            ],
        ],
        '84'     => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => '10.104.15.195', 'port' => 8001, 'timeout' => 10, 'prefix' => 'wx_84_', 'database' => 2,],
                    'read'  => ['host' => '10.104.15.195', 'port' => 8001, 'timeout' => 10, 'prefix' => 'wx_84_', 'database' => 2,],
                ],
            ],
        ],
    ],
    STATIC_MOVIE_INFO      => [
        'common' => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => '10.104.34.91', 'port' => 8001, 'timeout' => 10, 'prefix' => 'movie_common_',],
                    'read'  => ['host' => '10.104.34.91', 'port' => 8001, 'timeout' => 10, 'prefix' => 'movie_common_',],
                ],
            ],
        ],
        //格瓦拉（新版android）
        '84' => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => '10.104.15.195', 'port' => 8001, 'timeout' => 10, 'prefix' => 'wx_47_', 'database' => 0,],
                    'read'  => ['host' => '10.104.15.195', 'port' => 8001, 'timeout' => 10, 'prefix' => 'wx_47_', 'database' => 0,],
                ],
            ],
        ],
    ],

    STATIC_MOVIE_INFO_CHANNEL => [
        'common' => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => '10.104.34.91', 'port' => 8001, 'timeout' => 10, 'prefix' => 'movie_third_',],
                    'read'  => ['host' => '10.104.34.91', 'port' => 8001, 'timeout' => 10, 'prefix' => 'movie_third_',],
                ],
            ],
        ],
    ],

    STATIC_MOVIE_PREVUE => [
        'common' => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => '10.104.15.195', 'port' => 8001, 'timeout' => 10, 'prefix' => 'movie_prevue_', 'database' => 3,],
                    'read'  => ['host' => '10.104.15.195', 'port' => 8001, 'timeout' => 10, 'prefix' => 'movie_prevue_', 'database' => 3,],
                ],
            ],
        ],
    ],

    STATIC_MOVIE_PREVUE_CHANNEL => [
        'common' => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => '10.104.15.195', 'port' => 8001, 'timeout' => 10, 'prefix' => 'prevue_third_', 'database' => 3,],
                    'read'  => ['host' => '10.104.15.195', 'port' => 8001, 'timeout' => 10, 'prefix' => 'prevue_third_', 'database' => 3,],
                ],
            ],
        ],
    ],

    USER_GENERATED_CONTENT => [
        'common' => [
            'type' => 'default',
            'db' => [
                [
                    'write' => ['host' => '10.104.10.206', 'port' => 8006, 'timeout' => 3, "prefix" => "wx_ugc_"],
                    'read' => ['host' => '10.104.10.206', 'port' => 8006, 'timeout' => 3, "prefix" => "wx_ugc_"],
                ],
            ],
        ],
    ],
    WEI_XIN_TOKEN          => [
        'common' => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => '10.66.100.154', 'port' => 8000, 'timeout' => 3, "prefix" => "",],
                    'read'  => ['host' => '10.66.100.154', 'port' => 8000, 'timeout' => 3, "prefix" => "",],
                ],
            ],
        ],
    ],
    DELETE_ORDER           => [
        'common' => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => '10.66.141.61', 'port' => 8000, 'timeout' => 3, "prefix" => "wx_3_"],
                    'read'  => ['host' => '10.66.141.60', 'port' => 8000, 'timeout' => 3, "prefix" => "wx_3_"],
                ],
            ],
        ],
    
    ],
    USER_COMMENT_CACHE     => [
        'common' => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => '10.104.10.206', 'port' => 8008, 'timeout' => 3, "prefix" => "wx_ucc_"],
                    'read'  => ['host' => '10.104.10.206', 'port' => 8008, 'timeout' => 3, "prefix" => "wx_ucc_"],
                ],
            ],
        ],
    
    ],
    USER_MOVIE_PEE         => [
        'common' => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => '10.104.10.206', 'port' => 8005, 'timeout' => 3, "prefix" => "pee_", "database" => 4],
                    'read'  => ['host' => '10.104.10.206', 'port' => 8005, 'timeout' => 3, "prefix" => "pee_", "database" => 4],
                ],
            ],
        ],
    
    ],
    USER_TAG               => [
        'common' => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => '10.66.120.59', 'port' => 8000, 'timeout' => 3, "prefix" => "wx_tag_", "database" => 0],
                    'read'  => ['host' => '10.66.120.58', 'port' => 8000, 'timeout' => 3, "prefix" => "wx_tag_", "database" => 0],
                ],
                [
                    'write' => ['host' => '10.66.120.52', 'port' => 8000, 'timeout' => 3, "prefix" => "wx_tag_", "database" => 0],
                    'read'  => ['host' => '10.66.120.51', 'port' => 8000, 'timeout' => 3, "prefix" => "wx_tag_", "database" => 0],
                ],
                [
                    'write' => ['host' => '10.66.152.103', 'port' => 8000, 'timeout' => 3, "prefix" => "wx_tag_", "database" => 0],
                    'read'  => ['host' => '10.66.152.117', 'port' => 8000, 'timeout' => 3, "prefix" => "wx_tag_", "database" => 0],
                ],
                [
                    'write' => ['host' => '10.66.150.226', 'port' => 8000, 'timeout' => 3, "prefix" => "wx_tag_", "database" => 0],
                    'read'  => ['host' => '10.66.152.116', 'port' => 8000, 'timeout' => 3, "prefix" => "wx_tag_", "database" => 0],
                ],
            ],
        ],
    
    ],
    FILM_FESTIVAL_DATA     => [
        'common' => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => 'pre.rdb.grammy.wxmovie.com', 'port' => 8001, 'timeout' => 3, "prefix" => ""],
                    'read'  => ['host' => 'pre.rdb.grammy.wxmovie.com', 'port' => 8001, 'timeout' => 3, "prefix" => ""],
                ],
            ],
        ],
    
    ],
    MOVIE_SHOW_DB          => [
        'common' => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => [
                        'host'    => '10.104.34.91',
                        'port'    => 8008,
                        'timeout' => 10,
                        "prefix"  => "mdb_",
                    ],
                    'read'  => [
                        'host'    => '10.104.34.91',
                        'port'    => 8008,
                        'timeout' => 10,
                        "prefix"  => "mdb_",
                    ],
                ],
            ],
        ],
    
    ],
    USER_TRACE             => [
        'common' => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => '10.66.122.135', 'port' => 8000, 'timeout' => 10, "prefix" => "wp_trace_", "database" => 0,],
                    'read'  => ['host' => '10.66.122.135', 'port' => 8000, 'timeout' => 10, "prefix" => "wp_trace_", "database" => 0,],
                ],
            ],
        ],
    ],
    
    BONUS_NEW_USER       => [
        'common' => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => [
                        'host'     => '10.3.10.107',
                        'port'     => 6379,
                        'timeout'  => 10,
                        'password' => 'crs-7i0bvt5z:IoE4I8cc',
                        "prefix"   => "",
                        "database" => 0,
                    ],
                    'read'  => [
                        'host'     => '10.3.10.107',
                        'port'     => 6379,
                        'timeout'  => 10,
                        'password' => 'crs-7i0bvt5z:IoE4I8cc',
                        "prefix"   => "",
                        "database" => 0,
                    ],
                ],
            ],
        ],
    
    ],
    TICKET_LEFT_OVER     => [
        'common' => [
            'type' => 'modulo',
            'db'   => [
                [
                    'write' => ['host' => 'pre.rdb.grammy.wxmovie.com', 'port' => 8002, 'timeout' => 3, "prefix" => "wx_3_"],
                    'read'  => ['host' => 'pre.rdb.grammy.wxmovie.com', 'port' => 8002, 'timeout' => 3, "prefix" => "wx_3_"],
                ],
                [
                    'write' => ['host' => '10.104.10.206', 'port' => 8006, 'timeout' => 3, "prefix" => "wx_3_"],
                    'read'  => ['host' => '10.104.10.206', 'port' => 8006, 'timeout' => 3, "prefix" => "wx_3_"],
                ],
                [
                    'write' => ['host' => '10.104.15.195', 'port' => 6379, 'timeout' => 3, "prefix" => "wx_3_"],
                    'read'  => ['host' => '10.104.15.195', 'port' => 6379, 'timeout' => 3, "prefix" => "wx_3_"],
                ],
                //                [
                //                    'write' => ['host' => '10.66.142.168', 'port' => 8000, 'timeout' => 10, "prefix" => "wx_3_", "database" => 0,],
                //                    'read' => ['host' => '10.66.142.168', 'port' => 8000, 'timeout' => 10, "prefix" => "wx_3_", "database" => 0,],
                //                ],
                //                [
                //                    'write' => ['host' => '10.66.142.167', 'port' => 8001, 'timeout' => 10, "prefix" => "wx_3_", "database" => 0,],
                //                    'read' => ['host' => '10.66.142.167', 'port' => 8001, 'timeout' => 10, "prefix" => "wx_3_", "database" => 0,],
                //                ],
                //                [
                //                    'write' => ['host' => '10.66.142.166', 'port' => 8000, 'timeout' => 10, "prefix" => "wx_3_", "database" => 0,],
                //                    'read' => ['host' => '10.66.142.166', 'port' => 8000, 'timeout' => 10, "prefix" => "wx_3_", "database" => 0,],
                //                ],
                //                [
                //                    'write' => ['host' => '10.66.142.165', 'port' => 8001, 'timeout' => 10, "prefix" => "wx_3_", "database" => 0,],
                //                    'read' => ['host' => '10.66.142.165', 'port' => 8001, 'timeout' => 10, "prefix" => "wx_3_", "database" => 0,],
                //                ],
                //                [
                //                    'write' => ['host' => '10.66.142.164', 'port' => 8000, 'timeout' => 10, "prefix" => "wx_3_", "database" => 0,],
                //                    'read' => ['host' => '10.66.142.164', 'port' => 8000, 'timeout' => 10, "prefix" => "wx_3_", "database" => 0,],
                //                ],
                //                [
                //                    'write' => ['host' => '10.66.142.163', 'port' => 8001, 'timeout' => 10, "prefix" => "wx_3_", "database" => 0,],
                //                    'read' => ['host' => '10.66.142.163', 'port' => 8001, 'timeout' => 10, "prefix" => "wx_3_", "database" => 0,],
                //                ],
                //                [
                //                    'write' => ['host' => '10.66.139.231', 'port' => 8000, 'timeout' => 10, "prefix" => "wx_3_", "database" => 0,],
                //                    'read' => ['host' => '10.66.139.231', 'port' => 8000, 'timeout' => 10, "prefix" => "wx_3_", "database" => 0,],
                //                ],
                //                [
                //                    'write' => ['host' => '10.66.138.96', 'port' => 8001, 'timeout' => 10, "prefix" => "wx_3_", "database" => 0,],
                //                    'read' => ['host' => '10.66.138.96', 'port' => 8001, 'timeout' => 10, "prefix" => "wx_3_", "database" => 0,],
                //                ],
                //                [
                //                    'write' => ['host' => '10.66.138.69', 'port' => 8000, 'timeout' => 10, "prefix" => "wx_3_", "database" => 0,],
                //                    'read' => ['host' => '10.66.138.69', 'port' => 8000, 'timeout' => 10, "prefix" => "wx_3_", "database" => 0,],
                //                ],
                //                [
                //                    'write' => ['host' => '10.66.138.12', 'port' => 8001, 'timeout' => 10, "prefix" => "wx_3_", "database" => 0,],
                //                    'read' => ['host' => '10.66.138.12', 'port' => 8001, 'timeout' => 10, "prefix" => "wx_3_", "database" => 0,],
                //                ],
                //                [
                //                    'write' => ['host' => '10.66.137.148', 'port' => 8000, 'timeout' => 10, "prefix" => "wx_3_", "database" => 0,],
                //                    'read' => ['host' => '10.66.137.148', 'port' => 8000, 'timeout' => 10, "prefix" => "wx_3_", "database" => 0,],
                //                ],
                //                [
                //                    'write' => ['host' => '10.66.135.86', 'port' => 8001, 'timeout' => 10, "prefix" => "wx_3_", "database" => 0,],
                //                    'read' => ['host' => '10.66.135.86', 'port' => 8001, 'timeout' => 10, "prefix" => "wx_3_", "database" => 0,],
                //                ],
            ],
        ],
    
    ],
    IP_DATABASE          => [
        'common' => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => [
                        'host'     => '10.3.10.104',
                        'port'     => 6379,
                        'timeout'  => 3,
                        'password' => 'crs-a7wq3g25:GC5IvaLS',
                        "prefix"   => "",
                        "database" => 0,
                    ],
                    'read'  => [
                        'host'     => '10.3.10.104',
                        'port'     => 6379,
                        'timeout'  => 3,
                        'password' => 'crs-a7wq3g25:GC5IvaLS',
                        "prefix"   => "",
                        "database" => 0,
                    ],
                ],
            ],
        ],
    
    ],
    BIGDATA_IP_LIST      => [
        'common' => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => [
                        'host'     => '10.3.12.168',
                        'port'     => 6379,
                        'timeout'  => 3,
                        'password' => 'crs-g5aj689v:JPSXv0dP',
                        "prefix"   => "",
                        "database" => 0,
                    ],
                    'read'  => [
                        'host'     => '10.3.12.168',
                        'port'     => 6379,
                        'timeout'  => 3,
                        'password' => 'crs-g5aj689v:JPSXv0dP',
                        "prefix"   => "",
                        "database" => 0,
                    ],
                ],
            ],
        ],
    
    ],
    COMMON_LIMIT         => [
        'common' => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => [
                        'host'     => 'pre.rdb.grammy.wxmovie.com',
                        'port'     => 8001,
                        'timeout'  => 3,
                        'password' => '',
                        "prefix"   => 'limit_',
                        "database" => 0,
                    ],
                    'read'  => [
                        'host'     => 'pre.rdb.grammy.wxmovie.com',
                        'port'     => 8001,
                        'timeout'  => 3,
                        'password' => '',
                        "prefix"   => 'limit_',
                        "database" => 0,
                    ],
                ],
            ],
        ],
    ],
    GROUP_SHARE_FREQUENT => [
        'common' => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => '10.104.10.206', 'port' => 8008, 'timeout' => 3, "prefix" => "vote_", 'database' => 1],
                    'read'  => ['host' => '10.104.10.206', 'port' => 8008, 'timeout' => 3, "prefix" => "vote_", 'database' => 1],
                ],
            ],
        ],
    ],
    
    MOVIE_GUIDE    => [
        'common' => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => '10.66.122.135', 'port' => 8000, 'timeout' => 3, "prefix" => "wp_pre_MG_", "database" => 1,],
                    'read'  => ['host' => '10.66.122.135', 'port' => 8000, 'timeout' => 3, "prefix" => "wp_pre_MG_", "database" => 1,],
                ],
            ],
        ],
    ],
    MESSAGE_CENTER => [
        'common' => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => '10.66.183.244', 'port' => 28006, 'timeout' => 3, "prefix" => "message_center:", "database" => 1,],
                    'read'  => ['host' => '10.66.183.244', 'port' => 28006, 'timeout' => 3, "prefix" => "message_center:", "database" => 1,],
                ],
                [
                    'write' => ['host' => '10.66.183.245', 'port' => 28006, 'timeout' => 3, "prefix" => "message_center:", "database" => 1,],
                    'read'  => ['host' => '10.66.183.245', 'port' => 28006, 'timeout' => 3, "prefix" => "message_center:", "database" => 1,],
                ],
                [
                    'write' => ['host' => '10.66.183.246', 'port' => 28006, 'timeout' => 3, "prefix" => "message_center:", "database" => 1,],
                    'read'  => ['host' => '10.66.183.246', 'port' => 28006, 'timeout' => 3, "prefix" => "message_center:", "database" => 1,],
                ],
                [
                    'write' => ['host' => '10.66.183.247', 'port' => 28006, 'timeout' => 3, "prefix" => "message_center:", "database" => 1,],
                    'read'  => ['host' => '10.66.183.247', 'port' => 28006, 'timeout' => 3, "prefix" => "message_center:", "database" => 1,],
                ],
            ],
        ],
    ],
    
    WXAPP_TOKEN  => [
        'common' => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => '10.66.122.135', 'port' => 8000, 'timeout' => 3, "prefix" => "wxapp_", "database" => 3,],
                    'read'  => ['host' => '10.66.122.135', 'port' => 8000, 'timeout' => 3, "prefix" => "wxapp_", "database" => 3,],
                ],
            ],
        ],
    ],
    FILM_LIST    => [
        'common' => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => '10.66.122.135', 'port' => 8000, 'timeout' => 3, "prefix" => "wp_pre_", "database" => 2,],
                    'read'  => ['host' => '10.66.122.135', 'port' => 8000, 'timeout' => 3, "prefix" => "wp_pre_", "database" => 2,],
                ],
            ],
        ],
    ],
    BONUS_STATUS => [
        'common' => [
            'type' => 'default',
            'db'   => [
                [
                    'write' => ['host' => '10.104.10.206', 'port' => 8005, 'timeout' => 3, "prefix" => "pee_", "database" => 4,],
                    'read'  => ['host' => '10.104.10.206', 'port' => 8005, 'timeout' => 3, "prefix" => "pee_", "database" => 4,],
                ],
            ],
        ],
    ],
    RESOURCE_STATIC => [
        'common' => [
            'type' => 'default',
            'db' => [
                [
                    'write' => ['host' => 'pre.rdb.grammy.wxmovie.com', 'port' => 8003, 'timeout' => 3, "prefix" => "wy_", 'database' => 1],
                    'read' => ['host' => 'pre.rdb.grammy.wxmovie.com', 'port' => 8003, 'timeout' => 3, "prefix" => "wy_", 'database' => 1],
                ],
            ],
        ],
    ],
    COMMON_LIMIT => [
        'common' => [
            'type' => 'default',
            //这个配置，是原来12组剩余座位中的6组
            'db'   => [
                [
                    'write' => ['host' => 'pre.rdb.grammy.wxmovie.com', 'port' => 8001, 'timeout' => 10, 'prefix' => 'wx_limit_', 'database' => 4,],
                    'read'  => ['host' => 'pre.rdb.grammy.wxmovie.com', 'port' => 8001, 'timeout' => 10, 'prefix' => 'wx_limit_', 'database' => 4,],
                ],
            ],
        ],
    ],
    EMOJI_XIAOCHENGXU => [
        'common' => [
            'type' => 'default',
            'db' => [
                [
                    'write' => ['host' => '10.104.10.206', 'port' => 8006, 'timeout' => 3, "prefix" => "wx_ugc_"],
                    'read' => ['host' => '10.104.10.206', 'port' => 8006, 'timeout' => 3, "prefix" => "wx_ugc_"],
                ],
            ],
        ],
    ],
];
