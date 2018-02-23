<?php
date_default_timezone_set('Asia/Shanghai');

require("sdk.class.php");
$sdk = sdk::Instance();

//$res = $sdk->call('Common/encrypt',['str'=>'aaa','channelId'=>501]);
//print_R($res);
//
//
//$res = $sdk->call('Common/decrypt',['str'=>'RUM1QTg3ODcyMTdFMDJGNUQyNUE1OUY3ODk0QzMzNkQxNDU5OTk0NjMwYWFh','channelId'=>501]);
//print_R($res);


//$res = $sdk->call('Test/t',['channelId'=>3]);
//$res = $sdk->call('Comment/getMovieCommentV2',['channelId'=>3,'movieId'=>7792,'page'=>1,'num'=>10,'sortBy'=>'new']);
$res = $sdk->call('Snack/getSnackDiscountList',["channelId"=>28,"cinemaNo"=>"1012444","openId"=>"A9499B31F130A0FD7C77394065E56F3F","orderId"=>"1701041024189549500228","suitableNumber"=>"2","mpId"=>"109981675","logId"=>"a413200da1581210b67d9899965asdf23"]);
print_r($res);
//媒资库
$param =[
    'channelId'=>3,
//    'movieId'=>[6033,5118,5365,7718],
    'movieId'=>7621,
    'actorInfo'=>0,
];
//$res = $sdk->call('msdb/get-movie-info',$param);
$res = $sdk->call('msdb/get-movie-poster',$param);
//$res = $sdk->call('msdb/get-movie-actor',$param);

echo json_encode($res);exit;
//影人列表
$res = $sdk->call('msdb/get-movie-actor-list-and-appraise',['openId'=>'weiying_26336848','channelId'=>3,'movieId'=>7711]);
echo json_encode($res);
exit;
//$res = $sdk->call('Test/t',['channelId'=>3]);
//$res = $sdk->call('Comment/getMovieCommentV2',['channelId'=>3,'movieId'=>7792,'page'=>1,'num'=>10,'sortBy'=>'new']);
