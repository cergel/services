<?php
require("sdk.class.php");
$sdk = sdk::Instance();

//获取单条观影秘籍以及用户领取状态
/*
$params = [
	'openId' => "test_210000061",
    'channelId' => 3,
    'movieId' => 1234,
];
$r = $sdk->call('movie-guide/get-movie-guide',$params);


//领取观影秘籍
$params = [
	'openId' => "test_210000061",
    'channelId' => 3,
    'movieId' => 1239,
];
$r = $sdk->call('movie-guide/take-movie-guide',$params);
*/
/*
//领取观影秘籍
$params = [
	'openId' => "test_210000061",
    'channelId' => 3,
    'movieId' => 1234,
];
$r = $sdk->call('movie-guide/remove-movie-guide',$params);


//移除观影秘籍
$params = [
	'openId' => "test_210000061",
    'channelId' => 3,
    'movieId' => 1234,
];
$r = $sdk->call('movie-guide/remove-movie-guide',$params);
*/

//移除观影秘籍
$params = [
    'openId' => "test_210000061",
    'channelId' => 3,
    'page' => 1,
];
$r = $sdk->call('movie-guide/get-movie-guide-list', $params);
var_dump($r);