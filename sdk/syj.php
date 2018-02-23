<?php
/**
 * Created by PhpStorm.
 * User: syj
 * Date: 16/4/7
 * Time: 上午9:59
 */
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