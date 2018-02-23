<?php

require("sdk.class.php");

$sdk = sdk::Instance();
//$sdk->call("verify-code/create-code-back",['channelId'=>28]);
//$res = $sdk->call("test/hello", ['aa' => 'aa']);
//$res = $sdk->call("pdo/test", ['channelId' => '3']);

//$res = $sdk->call("city/read-city", ['channelId' => '3']);

//$res = $sdk->call("movie/read-movie-info", ['channelId' => '3', 'movieId' => '1745']);
//$res = $sdk->call("movie/read-city-movie", ['channelId' => '3','cityId' => '10']);
//$res = $sdk->call("movie/read-movie-will", ['channelId' => '3', 'cityId' => '10']);

//$res = $sdk->call("cinema/read-cinema-info", ['channelId' => '3', 'cinemaId' => '1000103', 'openId' => '']);
//$res = $sdk->call("cinema/read-cinemas-city", ['channelId' => '3', 'cityId' => '10', 'movieId' => '1774']);
//$res = $sdk->call("cinema/read-cinema-room", ['channelId' => '3', 'cinemaId' => '1000103', 'roomId' => '05']);

//$res = $sdk->call("sche/read-cinema-sche", ['channelId' => '3', 'cinemaId' => '1000103', 'cityId' => '10']);
//$res = $sdk->call("sche/read-movie-sche", ['channelId' => '3', 'movieId' => '5052', 'cityId' => '10']);

//$res = $sdk->call("favorite/favorite-cinema", ['channelId' => '8', 'cinemaId' => '1001046', 'openId' => 'weiying_19843735', 'action' => 'favorite']);
//$res = $sdk->call("favorite/get-favorite-cinema", ['channelId' => '3', 'openId' => 'o0aT-d4fFsvUIPujdGLk8LFl-_Io']);
//获取融合版可售座位
/*$res = $sdk->call("ticket/query-seat",
    [
        'channelId' => '3',
        'salePlatformType' => '2',
        'openId' => 'o0aT-d4fFsvUIPujdGLk8LFl-_Io',
        'appId' => 1,
        'userId' => 0,
        'subChannelId' => '1234567890',
        'roomId' => '05',
        'cinemaId' => '1000103',
        'scheduleId' => '10979',
    ]);*/

//锁座接口
/*$res = $sdk->call("ticket/lock-seat",
    [
        'channelId' => '3',
        'schedulePricingId' => '42867586',
        'openId' => 'o0aT-d3u6OylwhgJ3zbbHOKeqqPc',
        'phone' => '',
        'cinemaNo' => '1000088',
        'seatlable' => '01:7:15',
        'publicSignalShort' => '3',
        'salePlatformType' => '2',
        'saleObjType' => '100',
        'ticket' => '1',
        'appId' => 1,
        'userId' => '',
        'bisServerId' => '54604a3faf2b983fbbbd6a5d',
    ]);*/

/*$res = $sdk->call("ticket/lock-seat",
    [
        'channelId' => '3',
        'salePlatformType' => '2',  //锁座接口,居然这个值不能传1, 需要找书健确认
        'openId' => 'o0aT-d4fFsvUIPujdGLk8LFl-_Io',
        'appId' => 1,
        'userId' => '',
        'subChannelId' => '1234567890',
        'scheduleId' => '10159',
        'ticket' => '1',
        'seatlable' => '01:4:8',
    ]);*/


//$res = $sdk->call("wechat/create-wechat-access-token");
//$res = $sdk->call("wechat/create-js-api-ticket",['url'=>'http://wx.wepiao.com']);
//$res = $sdk->call("common/encrypt",['str'=>'o0aT-d4fXY2Y253iGTeOX0UzOX84']);
//$res = $sdk->call("common/decrypt", ['str' => 'QzhBRTYzODlGN0M4QzE2Q0E5OTI1QUFCNTcxNzIzREIxNDQyNTc5MjE0bzBhVC1kNGZYWTJZMjUzaUdUZU9YMFV6T1g4NA==']);

//获取优惠接口
/*$res = $sdk->call("bonus/query-bonus",
    [
        'channelId' => '3',
        'salePlatformType' => '1',
        'openId' => 'o0aT-d4fFsvUIPujdGLk8LFl-_Io',
        'appId' => 1,
        'userId' => 0,
        'subChannelId' => '1234567890',
        'scheduleId' => '',

        'page' => '1',
        'num' => '3',
        'status' => '1',
        'orderId' => '',
    ]);*/


//获取支付串接口
/*$res = $sdk->call("pay/pay-order-weixin",
    [
        'openId' => 'o0aT-d4fFsvUIPujdGLk8LFl-_Io',
        'bank' => 0,
        'visitor' => 'dianying_web',
        'cardNo' => '',
        'channelId' => '3',
        'userId' => '',
        'salePlatformType' => '2',
        'subChannelId' => '1234567890',
        'appId' => '1',
        'tempOrderId' => '150918153145497132',
        'bonusId' => '',
        'discountId' => '',
        'presellId' => '',
        'payType' => 'weixin_h5',
        'subsrc' => '30610000',
        'phone' => '15652673019',

    ]);*/

//获取我的订单
/*$res = $sdk->call("order/query-paid-order",
    [
        'openId' => 'o0aT-d8-Y6YKQWOsPm2opwkvxQ4E',
        'channelId' => '3',
        'userId' => '',
        'salePlatformType' => '2',
        'appId' => '1',
        'page' => '1',
        'num' => '2',

    ]);*/


//经纬度定位
/*$res = $sdk->call("geo/nearby-city",
    [
        'longitude' => 116.407526,
        'latitude' => 39.90403,
    ]);*/


//IP定位
/*$res = $sdk->call("geo/ip-locate",
    [
        'latitude' => '118.194.194.106',
        'type' => 2,
    ]);*/


//根据订单编号查询订单详情
/*$res = $sdk->call("order/query-orderinfo",
    [
        'orderId' => '150519194243394791',
    ]);*/


//查询兑换券详情
/*$res = $sdk->call("exchange/query",
    [
        'openId' => 'o0aT-d4fFsvUIPujdGLk8LFl-_Io',
        'channelId' => '3',
        'grouponId' => '000008D2BACB0D0A227200051FABFBFF000006E4',
    ]);*/


//获取兑换券购买支付串
/*$res = $sdk->call("pay/pay-exchange-weixin",
    [
        'channelId' => '3',
        'openId' => 'o0aT-d4fFsvUIPujdGLk8LFl-_Io',
        'phone' => '',
        'bank' => '0',
        'subsrc' => '30610000',
        'ticketType' => '2',
        'category' => '1',
        'userType' => '1',
        'payItem' => '000008D2BACB0D0A227200051FABFBFF000006E4*1',
        'paySource' => '115',
        'salePlatformType' => '2',
        'userId' => '',
        'appId' => '1',
        'cinemaId' => '1000103',
        'isNew' => '0',
    ]);*/

//查询礼品卡
/*$res = $sdk->call("giftcard/query-giftcard",
    [
        'status' => 1,
        'scheduleId' => '10083',
        'openId' => 'o0aT-dyypwl8XPM2pRCgT9WOYS-k',
        'userId' => '',
        'subChannelId' => '100',
        'channelId' => '3',
        'page' => 1,
        'num' => 1000,
    ]);*/

//定位
//$res = $sdk->call("locate/ip",['ip'=>'101.36.79.82','channelId'=>500]);
//$res = $sdk->call("locate/nearby-city",['latitude'=>'39.90403','longitude'=>'116.407526','channelId'=>500]);

//获取影院优惠文案
//$res = $sdk->call("cinema/get-search-cinema-info", ['channelId' => '3', 'cinemaIds' => '1000103|1012406']);

//收藏或取消收藏影院
/*$res = $sdk->call("favorite/favorite-cinema",
    ['openId' => 'o0aT-d4fFsvUIPujdGLk8LFl-_Io', 'cinemaId' => '1000105', 'channelId' => 3, 'action' => 'favorite']
);*/
//$res = $sdk->call("search/search-cinema", ['channelId' => 3,'keyword' => '金逸','city'=>'北京','longitude'=> '116.53496469532767','latitude'=>'39.92943893163622']);

//获取所有收藏的影院
/*$res = $sdk->call("favorite/get-favorite-cinema",
    ['openId' => 'o0aT-d_TaOXbs_jl8Jwz4iRinogg', 'channelId' => 3]
);*/

//验证码
//$res = $sdk->call("verify-code/create-verify-code", ['channelId' => 3, 'id' => 'djsjdjsj']);

//获取演出票订单列表
/*$res = $sdk->call("show/order-list",
    [
        'channelId' => 3, 'openId' => 'oSHt2twS_sbypmIoEmlJG8KTGmeM', 'unionId' => 'owJ__t2R9Ri2iFSqo0ydwySYhzWE',
        'pageNum' => 1, 'pageSize' => 1,
    ]
);*/

//获取演出票订单详情
/*$res = $sdk->call("show/order-info",
    ['channelId' => 3, 'openId' => 'oSHt2twS_sbypmIoEmlJG8KTGmeM', 'unionId' => 'owJ__t2R9Ri2iFSqo0ydwySYhzWE', 'orderId' => '14346103074041']
);*/

//取消演出票订单
/*$res = $sdk->call("show/cancel-order",
    ['channelId' => 3, 'openId' => 'oSHt2twS_sbypmIoEmlJG8KTGmeM', 'unionId' => 'owJ__t2R9Ri2iFSqo0ydwySYhzWE', 'orderId' => '14346103074041']
);*/

//获取token
/*$res = $sdk->call("common/encrypt",
    ['channelId' => 3, 'str' => 'o0aT-d4fFsvUIPujdGLk8LFl-_Io', 't' => time()+7*24*60*60]
);*/

//解密token
/*$res = $sdk->call("common/decrypt",
    ['channelId' => 3, 'str' => 'MjVCMzk3OTI3NkREMTkwNEQ2MEQwMUI2REI0QjEzODYxNDQ4MDEzMjEybzBhVC1kNGZGc3ZVSVB1amRHTGs4TEZsLV9Jbw==']
);*/

//登陆
/*$_COOKIE['WxOpenId'] = 'QzQ5NzVGN0U4ODc1NjVCMzYwNUExNzRGQ0Q1QzBFNTIxNDc4MjQyNTg4bzBhVC1kNGZGc3ZVSVB1amRHTGs4TEZsLV9Jbw==';
$res = $sdk->call("wechatLogin/login", [
        'channelId' => 3,
    ]
);*/

//发评论&修改评论  2222
/*
$res = $sdk->call("comment/save",
    [
        'uid' => '60000109',
        'openId'=>'o0aT-d_Akplzr06LLS3iJ6N3cMeE',
        'unionId'=>'owJ__t2T0IzTSZ2_HW2azSLZHxb0',
        'movieId'=>'8888',
        'content'=>'郑铮测试',
        'score'=>100,
        'from'=>'12321122', 
        'channelId' => 3,
        'type'=>2,
    ]
);
var_dump($res);
*/
//添加回复

/*$res = $sdk->call("comment/saveReply",
    [
        'uid' => '60000114',
        'openId'=>'ZZZT-d5sPx443cq1ieBVz2XQqg7U',
        'unionId'=>'owJ__t3uQVPT9NoCx3pHzaKfG2uI',
        'commentId'=>'58258',
        'content'=>'58258郑铮评论的回复',
        'score'=>100,
        'from'=>'12321122',
        'channelId' => 3,
        'type'=>2,
    ]
);*/

//评分
/*
$res = $sdk->call("comment/score",
    [
        'uid' => '60000114',
        'openId'=>'ZZZT-d5sPx443cq1ieBVz2XQqg7U',
        'unionId'=>'owJ__t3uQVPT9NoCx3pHzaKfG2uI',
        'movieId'=>'8888',
        'score'=>100,
        'from'=>'12321122', 
        'channelId' => 3,
        'type'=>2,
    ]
);
*/
//想看&取消想看[want=1:想看，want=0：取消想看s]
/*
$res = $sdk->call("comment/want",
    [
        'uid' => '60000939',
        'openId'=>'o0aT-d5sPx443cq1ieBVz2XQqg7U',
        'unionId'=>'owJ__t1XQKGDqPS0VLG6dJ22qL9U',
        'movieId'=>'89',
        'want'=>1,
        'from'=>'12321122',
        'channelId' => 3,
        'type'=>2,
    ]
);
*/
//看过&取消看过[seen=1:看过，want=0：取消看过]
/*
 $res = $sdk->call("comment/seen",
 [  'uid' => '60000114',
    'openId'=>'ZZZT-d5sPx443cq1ieBVz2XQqg7U',
    'unionId'=>'owJ__t3uQVPT9NoCx3pHzaKfG2uI',
    'movieId'=>'8888',
     'seen'=>1,
     'from'=>'12321122',
     'channelId' => 3,
     'type'=>2,
 ]
 );
 */
//评论点赞、取消点赞[favor点赞状态：1：点赞，0：取消点赞]
/*
$res = $sdk->call("comment/favor",
    [
        'uid' => '60000939',
        'openId'=>'o0aT-d5sPx443cq1ieBVz2XQqg7U',
        'unionId'=>'owJ__t1XQKGDqPS0VLG6dJ22qL9U',
        'commentId'=>'58256',
        'favor'=>0,
        'from'=>'12321122',
        'channelId' => 3,
        'type'=>2,
    ]
);
*/
//获取用户对某个影片的评分、评论信息
/*
$res = $sdk->call("comment/getScoreAndComment",
    [
        'uid' => '60000939',
        'openId'=>'o0aT-d5sPx443cq1ieBVz2XQqg7U',
        'unionId'=>'owJ__t1XQKGDqPS0VLG6dJ22qL9U',
        'movieId'=>'88',
        'from'=>'12321122',
        'channelId' => 3,
        'type'=>2,
    ]
);
*/
//获取某个影片的评论列表- 最新、热评等[sortBy 排序方式（new:最新评论，reply：回复数，favor：喜欢数，time:时间维度） 都是DESC排序]
/*
$res = $sdk->call("comment/getMovieComment",
    [
        'uid' => '60000939',
        'openId'=>'o0aT-d5sPx443cq1ieBVz2XQqg7U',
        'unionId'=>'owJ__t1XQKGDqPS0VLG6dJ22qL9U',
        'movieId'=>'89',
        'page'=>1,
        'num'=>5,
        'sortBy'=>'new',
        'from'=>'12321122',
        'channelId' => 3,
        'type'=>2,
    ]
);
*/
//获取指定评论的回复列表
/*
$res = $sdk->call("comment/getMovieCommentReply",
    [
        'uid' => '60000939',
        'openId'=>'o0aT-d5sPx443cq1ieBVz2XQqg7U',
        'unionId'=>'owJ__t1XQKGDqPS0VLG6dJ22qL9U',
        'commentId'=>'58256',
        'page'=>1,
        'num'=>5,
        'from'=>'12321122',
        'channelId' => 3,
        'type'=>2,
    ]
);
*/
////获取看过指定电影的用户
//$res = $sdk->call("comment/getMovieSeenUser",
//    [
//        'movieId'=>'5000',
//        'page'=>1,
//        'num'=>5,
//        'from'=>'12321122',
//        'channelId' => 3,
//        'type'=>2,
//    ]
//);
//
//获取看过指定影片的用户信息
/*
$res = $sdk->call("comment/getMovieSeenUser",
    [
       // 'uid' => '40000782',
       'movieId'=>'5000',
        //'unionId'=>'owJ__t1XQKGDqPS0VLG6dJ22qL9U',
        //'commentId'=>'58256',
        'page'=>1,
        'num'=>100,
        'from'=>'12321122',
        'channelId' => 3,
        'type'=>2,
    ]
);
*/
//获取获取我想看的电影
/*
$res = $sdk->call("comment/getUserWantMovie",
    [
        'uid' => '60000939',
        //'unionId'=>'owJ__t1XQKGDqPS0VLG6dJ22qL9U',
        //'commentId'=>'58256',
        'page'=>1,
        'num'=>100,
        'from'=>'12321122',
        'channelId' => 3,
    ]
);
*/

//获取获取我看过的电影
/*
$res = $sdk->call("comment/getUserSeenMovie",
    [
        'uid' => '60000939',
        //'unionId'=>'owJ__t1XQKGDqPS0VLG6dJ22qL9U',
        //'commentId'=>'58256',
        'page'=>1,
        'num'=>100,
        'from'=>'12321122',
        'channelId' => 3,
    ]
);
*/
//print_r($res);exit;

//获取看过指定影片的用户信息
/*$res = $sdk->call("search/search-movie",
    [
        'keyWord'=>'一',
        //'unionId'=>'owJ__t1XQKGDqPS0VLG6dJ22qL9U',
        //'commentId'=>'58256',
        'page'=>1,
        'num'=>100,
        'from'=>'12321122',
        'channelId' => 8,
        'movieInfo'=>1,
        'actorInfo'=>0,
        'cityId'=>'10',
    ]
);*/

//获取看过指定影片的用户信息
//$res = $sdk->call("film-festival/read-movies-data", ['channelId' => 3,]);
//$res = $sdk->call("film-festival/read-cinemas-data", ['channelId' => 3,]);
//$res = $sdk->call("film-festival/process-user-movie-list",['channelId' => 3, 'openId' => 'o0aT-d4fFsvUIPujdGLk8LFl-_Io', 'movieId' => '6666', 'type' => 1]);
//$res = $sdk->call("film-festival/process-user-movie-list",['channelId' => 3, 'openId' => 'o0aT-d4fFsvUIPujdGLk8LFl-_Io', 'movieId' => '6666', 'type' => 2]);
//$res = $sdk->call("film-festival/get-user-movie-list", ['channelId' => 3, 'openId' => 'o0aT-d4fFsvUIPujdGLk8LFl-_Io',]);
//$res = $sdk->call("film-festival/movie-search", ['channelId' => 3, 'keyWord' => '崛起',]);
//影片分页接口
//$res = $sdk->call("movie/read-city-movie-by-page", ['channelId' => 3, 'cityId' => 10]);

/**
 * 小吃接口
 */
/*$res = $sdk->call("snack/get-cinema-snacks", [
        'channelId' => 3,
        'cinemaNo'  => 1013059,
    ]);*/

/**
 * 获取银行卡优惠活动信息
 */
/*$res = $sdk->call("bank-privilege/read-bank-privilege", [
    'channelId' => 3,
    'cinemaId'  => 1000088,
    'cityId'    => 10,
    'movieId'   => 6321,
]);*/

//获取单品推荐信息
/*$res = $sdk->call("movie/read-movie-recommended-news", [
    'channelId' => 3,
    'cityId'    => 10,
    'movieId'   => 1713,
]);*/

//获取影院搜索列表
/*$res = $sdk->call("search/search-cinema-list-from-es", [
    'channelId' => 3,
    'cityId'    => 10,
]);*/

//获取影院搜索列表
/*$res = $sdk->call("movie/get-movie-will-with-date", [
    'channelId' => 3,
    'cityId'    => 10,
]);*/

/**
 * 分页版本的即将上映
 */
/*$res = $sdk->call("movie/read-movie-will-by-page", [
    'channelId' => 3,
    'cityId'    => 10,
    'sortField' => 'wantCount',
    'order'     => 'desc',
]);*/

/*$res = $sdk->call("movie/get-sche-flag-of-movie-city", [
    'channelId' => 3,
    'cityId'    => 10,
    'movieId'   => '6102',
]);*/

/*$res = $sdk->call("search/search-cinema-v2", [
    'channelId' => 3,
    'cityName'      => '北京',
    'keyword'   => '朝阳大悦城',
]);*/

/*$res = $sdk->call("pay/pay", [
    "openId"    => "DC542F53A74BDE0F78A46E46DABA9F41",
    "orderId"   => "1608031732159371900228",
    "phone"     => "18300043951",
    "channelId" => 28,
    "tradeType" => "JSAPI",
    "payType"   => 7,
    "snackId"   => "",
    "snackNum"  => "",
    "smsCode"   => "",
    "disInfo"   => '{"bnsId":"1608021657490247114","reduId":"","presellId":""}',
]);*/

/*$res = $sdk->call("sms/decrypt-phone", [
    'channelId' => 3,
    'token'      => 'BA9WAlMKCFMDAQY=',
]);*/

/*$res = $sdk->call("cinema/read-cinema-info-v2", [
    'channelId' => 3,
    'cinemaId'  => '1000103',
]);*/

/*$res = $sdk->call("message/get-red-point", [
    'channelId'        => 3,
    'openId'           => 'o0aT-d4fFsvUIPujdGLk8LFl-_Io',
]);*/

/*$res = $sdk->call("movie/read-movie-info-new-static", [
    'channelId' => 3,
    'movieId'  => '237258',
    'cityId'=>10,
    ''
]);*/

/*$res = $sdk->call("movie/read-city-movie-by-page-new-static", [
    'channelId' => 3,
    'cityId'=>10,
]);*/

/*$res = $sdk->call("movie/get-movie-will-with-date-new-static", [
    'channelId' => 3,
    'cityId'=>10,
]);*/

/*$res = $sdk->call("movie/get-movie-custom-seat-new-static", [
    'channelId' => 3,
    'movieId'   => '6917',
]);*/

/*$res = $sdk->call("movie/read-movie-will-new-static", [
    'channelId' => 9,
    'cityId'   => '10',
]);*/

/*$res = $sdk->call("movie/read-bank-privilege-new-static", [
    'channelId' => 9,
    'cityId'   => '10',
]);*/

/*$res = $sdk->call("movie/read-movie-and-score-new-static", [
    'channelId' => 9,
    'movieIds'  => ['205064','237258'],
]);*/

/*$res = $sdk->call("movie/read-movie-info-by-ids-new-static", [
    'channelId' => 3,
    'movieIds'  => ['205064', '237258'],
]);*/

/*$res = $sdk->call("movie/read-movie-score-new-static", [
    'channelId' => 3,
    'movieIds'  => ['205064', '237258'],
]);*/

/*$res = $sdk->call("sche/qry-sche-v2", [
    'channelId' => 3,
    'cinemaId'  => '1000103',
    'needMore'  => 3,
]);*/

/*$res = $sdk->call("message/get-red-point", [
    'channelId'        => 3,
    'openId'           => 'o0aT-d4fFsvUIPujdGLk8LFl-_Io',
]);*/

/*$res = $sdk->call("movie/read-city-movie-by-page-new-static-v2", [
    'channelId' => 3,
    'cityId'=>10,
]);*/

/*$res = $sdk->call("user/check-black", [
    'channelId'        => 3,
    'openId'           => 'o0aT-d4fFsvUIPujdGLk8LFl-_Io',
]);*/

/*$res = $sdk->call("message/get-red-point", [
    'channelId'        => 3,
    'openId'           => 'o0aT-d4fFsvUIPujdGLk8LFl-_Io',
]);*/

/*$res = $sdk->call("user/check-black", [
    'channelId'        => 3,
    'openId'           => 'o0aT-d4fFsvUIPujdGLk8LFl-_Io',
]);*/

/*$res = $sdk->call("user/pay-vip-card", [
    'channelId' => 3,
    'openId'    => 'o0aT-d_lYI68NKqfYka0JVF3xKZQ',
]);*/

/*$res = $sdk->call("movie/read-city-movie", [
    'channelId' => 6,
    'cityId'    => '10',
]);*/

/*$res = $sdk->call("sche/qry-sche-v2", [
    'channelId' => 3,
    'openId'    => 'o0aT-d4fFsvUIPujdGLk8LFl-_Io',
    'needMore'  => 3,
    'cinemaId'  => 1000103,
]);*/

/*$res = $sdk->call("movie/get-spot-befor-days", [
    'channelId' => 3,
    'movieId'   => '50205',
]);*/

/*$res = $sdk->call("movie/getMovieWillPreview", [
    'channelId' => 3,
    'cityId'   => '10',
]);*/

/*$res = $sdk->call("goods/read-cinema-room", ['channelId' => '6', 'cinemaId' => '1002149', 'roomId' => '1']);*/

//$res = $sdk->call("small-routine/get-a-problem-item", ['channelId' => '66', 'repoId' => '1', 'itemId' => '1']);

//$res = $sdk->call("small-routine/get-problem-repos", ['channelId' => '66']);

/*$res = $sdk->call("small-routine/get-random-problem-item", [
    'channelId' => '66',
    'openId' => 'o0aT-d4fFsvUIPujdGLk8LFl-_Io',
    'repoId' => '1',
]);*/

/*$res = $sdk->call("small-routine/check-problem-answer", [
    'channelId' => '66',
    'openId' => 'o0aT-d4fFsvUIPujdGLk8LFl-_Io',
    'repoId' => '1',
    'itemId' => '1',
    'answer' => '一路向北',
]);*/

/*$res = $sdk->call("small-routine/get-emojies-by-text", [
    'channelId' => '66',
    'openId' => 'o0aT-d4fFsvUIPujdGLk8LFl-_Io',
    'text' => '我很开心',
]);*/

/*$res = $sdk->call("user/get-userinfo-by-openid", [
    'channelId' => '3',
    'openId' => 'o0aT-d4fFsvUIPujdGLk8LFl-_Io',
]);*/

/*$res = $sdk->call("small-routine/create-emojies-problem", [
    'channelId' => '66',
    'openId' => 'o0aT-d4fFsvUIPujdGLk8LFl-_Io',
    'text' => '我很开心',
    'emojiToken' => 'FEE6C2D633C291BCDACD217BEE6D0945',
    'emojiIds' => 'A_1980,12,1',
    'awardType' => 0,
]);*/

$res = $sdk->call("small-routine/check-problem-answer", [
    'channelId' => '63',
    'openId' => 'o0aT-d4fFsvUIPujdGLk8LFl-_Io',
    'repoId' => '9999',
    'itemId' => '60',
    'answer' => '我很开心',
    'reward' => 1,
]);

var_dump($res);
//\wepiao\wepiao::dump($res);
