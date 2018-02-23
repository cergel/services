<?php

/**
 * SDK发出的错误, 为了避免和java的冲突, ret从- 10001开始
 * 另外注意, 不同类型的错误号之间,最好隔开20个数字, 比如退票错误信息和影院收藏之间就隔了20,
 * 这样的话, 以后退票增加错误号, 中间20个足够用了
 */


//静态数据部分
define('ERROR_RET_STATIC_DATA_CINEMA_ROOM_EMPTY', -1001);
define('ERROR_MSG_STATIC_DATA_CINEMA_ROOM_EMPTY', '未获取到影厅座位图数据');


/**
 * JAVA接口部分
 */


/**
 * 自己的处理
 */

define('ERROR_RET_JAVA_AVAILABLE_SEAT', -2001);
define('ERROR_MSG_JAVA_AVAILABLE_SEAT', '获取可售座位失败');
//座位图问题
define('ERROR_RET_SDK_MERFE_EEQUEST_EMPTY', -2002);
define('ERROR_MSG_SDK_MERFE_EEQUEST_EMPTY', '座位图为空,或无法获取可售座位信息');
//参数不全
define('ERROR_RET_PARAM_LOACK', -2003);
define('ERROR_MSG_PARAM_LOACK', '参数不全');
//有空参数
define('ERROR_RET_PARAM_EMPTY', -2004);
define('ERROR_MSG_PARAM_EMPTY', '参数信息不完整');
//requestId
define('ERROR_RET_LACK_OF_REQUESTID', -2005);
define('ERROR_MSG_LACK_OF_REQUESTID', '当前缺少requestId参数');

//定位匹配问题——匹配城市错误 (调用定位接口成功,但是无法匹配到电影票侧cityId的情况)
define('ERROR_RET_LOCATE_CITY_FIND_ERROR', -2021);
define('ERROR_MSG_LOCATE_CITY_FIND_ERROR', '未根据定位接口匹配到电影票侧的城市信息');
//定位匹配问题——调用腾讯接口根据经纬度定位,没有调用成功
define('ERROR_RET_LOCATE_TENCENT_INTERFACE_ERROR', -2022);
define('ERROR_MSG_LOCATE_TENCENT_INTERFACE_ERROR', '调用Tencent定位不成功');
//定位匹配问题——调用腾讯接口根据经纬度定位成功,但是调用方法有问题,比如经度传得不对,这种情况不定义自己的msg,用腾讯的就可以了
define('ERROR_RET_LOCATE_TENCENT_INTERFACE_TYPE_ERROR', -2022);

//退票接口问题——用户Id为空
define('ERROR_RET_ORDER_REFUND_OPENID_ERROR', -2041);
define('ERROR_MSG_ORDER_REFUND_OPENID_ERROR', '用户id非法');
//退票接口问题——退票订单号或退票原因为空
define('ERROR_RET_ORDER_REFUND_PARAM_ERROR', -2042);
define('ERROR_MSG_ORDER_REFUND_PARAM_ERROR', '退票订单号或退票原因为空');

//影院收藏
define('ERROR_RET_CINEMA_FAVORITE_ERROR', -2061);
define('ERROR_MSG_CINEMA_FAVORITE_ERROR', '收藏影院失败');

//评分评论相关,请预留50个值
define('ERROR_RET_COMMENT_ERROR', -2101);
define('ERROR_MSG_COMMENT_ERROR', '发表评论失败');

//演出
define('ERROR_RET_SHOW_PARAM_ID_ERROR', -2081);
define('ERROR_MSG_SHOW_PARAM_ID_ERROR', 'unionId或openId为空');
define('ERROR_RET_SHOW_PARAM_ORDERID_ERROR', -2082);
define('ERROR_MSG_SHOW_PARAM_ORDERID_ERROR', '演出订单编号为空');

define('ERROR_RET_DECRYPT', -3001);
define('ERROR_MSG_DECRYPT', 'decrypt str error');

define('ERROR_RET_WECHAT_LOGIN', -4001);
define('ERROR_MSG_WECHAT_LOGIN', 'get OpenId error!');
define('ERROR_RET_WXAPP', -4002);
define('ERROR_MSG_WXAPP', 'get token error!');


//系统级错误有01开头
defined('ERRORCODE_SYS_REDIS_CONNECTION') or define('ERRORCODE_SYS_REDIS_CONNECTION', '0100001002');

//模块级错误用02开头
defined('ERRORCODE_COMMON_PARAMS_ERROR') or define('ERRORCODE_COMMON_PARAMS_ERROR', '0200001001');//参数错误

//用户相关
defined('ERRORCODE_OPEN_ID_ERROR') or define('ERRORCODE_OPEN_ID_ERROR', '0210131001');//评论数据不合法

//以下为评论相关模板使用，
defined('ERRORCODE_COMMENT_NO_DATA') or define('ERRORCODE_COMMENT_NO_DATA', '0210021001');//评论数据不合法
defined('ERRORCODE_COMMENT_SCORE_EDIT') or define('ERRORCODE_COMMENT_SCORE_EDIT', '0210021002');//评分修改失败
defined('ERRORCODE_COMMENT_SCORE_ADD') or define('ERRORCODE_COMMENT_SCORE_ADD', '0210021003');//评分插入失败
defined('ERRORCODE_COMMENT_SCORE_ALL') or define('ERRORCODE_COMMENT_SCORE_ALL', '0210021004');//评分计算失败
defined('ERRORCODE_COMMENT_SCORE_ADD_ERROR') or define('ERRORCODE_COMMENT_SCORE_ADD_ERROR', '0210021004');//评分人数增加失败
defined('ERRORCODE_COMMENT_ADD') or define('ERRORCODE_COMMENT_ADD', '0210021005');//新增评论失败
defined('ERRORCODE_COMMENT_EDIT') or define('ERRORCODE_COMMENT_EDIT', '0210021006');//修改评论失败
defined('ERRORCODE_COMMENT_DELETE') or define('ERRORCODE_COMMENT_DELETE', '0210021007');//已被删除的评论
defined('ERRORCODE_COMMENT_EDIT_PARAM_COMMENT') or define('ERRORCODE_COMMENT_EDIT_PARAM_COMMENT', '0210021008');//评论：影片评论数修改失败
defined('ERRORCODE_BLACK_USER_COMMENT') or define('ERRORCODE_BLACK_USER_COMMENT', '0210021009');//评论、回复：黑名单文案
defined('ERRORCODE_SHIELDING_1_WORD') or define('ERRORCODE_SHIELDING_1_WORD', '0210021010');//敏感词文案1
defined('ERRORCODE_SHIELDING_2_WORD') or define('ERRORCODE_SHIELDING_2_WORD', '0210021011');//敏感词文案2
defined('ERRORCODE_SHIELDING_3_WORD') or define('ERRORCODE_SHIELDING_3_WORD', '0210021012');//敏感词文案3
defined('ERRORCODE_SHIELDING_4_WORD') or define('ERRORCODE_SHIELDING_4_WORD', '0210021013');//敏感词文案4
defined('ERRORCODE_SHIELDING_5_WORD') or define('ERRORCODE_SHIELDING_5_WORD', '0210021014');//敏感词文案5

defined('ERRORCODE_COMMENT_UPDATE_TIME_WORE') or define('ERRORCODE_COMMENT_UPDATE_TIME_WORE', '0210021015');//短时间发多条
defined('ERRORCODE_COMMENT_REPLY_ADD_ERROR') or define('ERRORCODE_COMMENT_REPLY_ADD_ERROR', '0210021016');//回复增加失败
defined('ERRORCODE_COMMENT_REPLY_EDIT_ERROR') or define('ERRORCODE_COMMENT_REPLY_EDIT_ERROR', '0210021017');//回复修改失败
defined('ERRORCODE_COMMENT_REPLY_COMMENT_ERROR') or define('ERRORCODE_COMMENT_REPLY_COMMENT_ERROR', '0210021018');//回复-增加回复数失败
defined('ERRORCODE_COMMENT_WANT_ADD_ERROR') or define('ERRORCODE_COMMENT_WANT_ADD_ERROR', '0210021019');//想看：增加失败
defined('ERRORCODE_COMMENT_WANT_COUNT_ERROR') or define('ERRORCODE_COMMENT_WANT_COUNT_ERROR', '0210021020');//想看数+1失败
defined('ERRORCODE_COMMENT_SEEN_ADD_ERROR') or define('ERRORCODE_COMMENT_SEEN_ADD_ERROR', '0210021021');//看过增加
defined('ERRORCODE_COMMENT_SEEN_DEL_ERROR') or define('ERRORCODE_COMMENT_SEEN_DEL_ERROR', '0210021022');//看过删除
defined('ERRORCODE_COMMENT_SEEN_COUNT_ERROR') or define('ERRORCODE_COMMENT_SEEN_COUNT_ERROR', '0210021023');//看过数+-1失败
defined('ERRORCODE_COMMENT_SCORE_DELTTE_ERROR') or define('ERRORCODE_COMMENT_SCORE_DELTTE_ERROR', '0210021024');//评分删除失败
defined('ERRORCODE_COMMENT_WANT_DELETE_ERROR') or define('ERRORCODE_COMMENT_WANT_DELETE_ERROR', '0210021025');//删除想看失败
defined('ERRORCODE_COMMENT_FAVOR_COUNT_ERROR') or define('ERRORCODE_COMMENT_FAVOR_COUNT_ERROR', '0210021026');//赞数+-1失败
defined('ERRORCODE_COMMENT_FAVOR_ADD_ERROR') or define('ERRORCODE_COMMENT_FAVOR_ADD_ERROR', '0210021027');//赞失败
defined('ERRORCODE_COMMENT_FAVOR_DEL_ERROR') or define('ERRORCODE_COMMENT_FAVOR_DEL_ERROR', '0210021028');//赞删除失败
defined('ERRORCODE_COMMENT_MOVIE_COMMENT_INFO_ERROR') or define('ERRORCODE_COMMENT_MOVIE_COMMENT_INFO_ERROR', '0210021029');//赞删除失败
defined('ERRORCODE_COMMENT_LENGTH_MAX_ERROR') or define('ERRORCODE_COMMENT_COMMENT_LENGTH_MAX_ERROR', '0210021030');//最大评论字数
defined('ERRORCODE_COMMENT_LENGTH_MIN_ERROR') or define('ERRORCODE_COMMENT_COMMENT_LENGTH_MIN_ERROR', '0210021031');//最小评论字数
defined('ERRORCODE_COMMENT_REPLY_LENGTH_MAX_ERROR') or define('ERRORCODE_COMMENT_REPLY_LENGTH_MAX_ERROR', '0210021032');//最大回复字数
defined('ERRORCODE_COMMENT_REPLY_LENGTH_MIN_ERROR') or define('ERRORCODE_COMMENT_REPLY_LENGTH_MIN_ERROR', '0210021033');//最小回复字数
defined('ERRORCODE_COMMENT_DEL_NO_USER_ERROR') or define('ERRORCODE_COMMENT_DEL_NO_USER_ERROR', '0210021034');//删除评论：当前评论不属于当前用户
defined('ERRORCODE_COMMENT_DEL_ERROR') or define('ERRORCODE_COMMENT_DEL_ERROR', '0210021035');//删除评论：失败
defined('ERRORCODE_COMMENT_SCORE_DEL_ERROR') or define('ERRORCODE_COMMENT_SCORE_DEL_ERROR', '0210021036');//删除评分：失败
defined('ERRORCODE_COMMENT_NOT_EXISTS_ERROR') or define('ERRORCODE_COMMENT_NOT_EXISTS_ERROR', '0210021037');//此条评论信息不存在

defined('ERRORCODE_COMMENT_LEVEL_DOWN') or define('ERRORCODE_COMMENT_LEVEL_DOWN', '0210022001');//评论降级

//尿点使用
defined('ERRORCODE_PEE_NO_DATA') or define('ERRORCODE_PEE_NO_DATA', '0210041001');//尿点数据不合法
defined('ERRORCODE_PEE_NO_MOVIE') or define('ERRORCODE_PEE_NO_MOVIE', '0210041002');//不存在的影片
defined('ERRORCODE_PEE_ADD_ERROR') or define('ERRORCODE_PEE_ADD_ERROR', '0210041003');//添加失败
defined('ERRORCODE_PEE_DEL_ERROR') or define('ERRORCODE_PEE_DEL_ERROR', '0210041004');//删除失败
//电影原创音乐使用
defined('ERRORCODE_MOVIE_MUSIC_NO_DATA') or define('ERRORCODE_MOVIE_MUSIC_NO_DATA', '0210091001');//参数缺少
defined('ERRORCODE_MOVIE_MUSIC_NO_MOVIE') or define('ERRORCODE_MOVIE_MUSIC_NO_MOVIE', '0210091002');//不存在的影片
defined('ERRORCODE_MOVIE_MUSIC_NO_MOVIEINFO') or define('ERRORCODE_MOVIE_MUSIC_NO_MOVIEINFO', '0210091003');//不存在的影片
defined('ERRORCODE_MOVIE_MUSIC_DEL_ERROR') or define('ERRORCODE_MOVIE_MUSIC_DEL_ERROR', '0210091004');//删除失败
defined('ERRORCODE_MOVIE_MUSIC_SET_ERROR') or define('ERRORCODE_MOVIE_MUSIC_SET_ERROR', '0210091005');//设置电影缓存失败
//微信小工具使用
defined('ERRORCODE_WECHAT_PARAM_ERROR') or define('ERRORCODE_WECHAT_PARAM_ERROR', '0210031001'); //参数异常
defined('ERRORCODE_WECHAT_SEND_TEMPLATE_ERROR') or define('ERRORCODE_WECHAT_SEND_TEMPLATE_ERROR', '0210031005'); //发送模板文件失败
defined('ERRORCODE_WECHAT_TEMPLATE_TYPE_ERROR') or define('ERRORCODE_WECHAT_TEMPLATE_TYPE_ERROR', '0210031006'); //发送模板文件失败


//sendmessage小工具使用
defined('ERRORCODE_WECHAT_TOOL_PARAM_EMPTY') or define('ERRORCODE_WECHAT_TOOL_PARAM_EMPTY', '0210031001');
defined('ERRORCODE_CREATE_TOKEN_EXPIRE_ERROR') or define('ERRORCODE_CREATE_TOKEN_EXPIRE_ERROR', '0210031002');//请求生成token超时
defined('ERRORCODE_CREATE_TOKEN_ERROR') or define('ERRORCODE_CREATE_TOKEN_ERROR', '0210031003');//生成token失败
defined('ERRORCODE_CREATE_JSAPI_TICKET_ERROR') or define('ERRORCODE_CREATE_JSAPI_TICKET_ERROR', '0210031004');//生成jsapiticket失败
defined("ERRORCODE_GET_ACCESS_TOKEN_FROM_REDIS_ERROR") or define("ERRORCODE_GET_ACCESS_TOKEN_FROM_REDIS_ERROR", '0210031007'); //redis获取token错误

//订单、锁座、支付相关
defined("ERRORCODE_DEL_ORDER_ORDERID_ERROR") or define("ERRORCODE_DEL_ORDER_ORDERID_ERROR", '0210051001'); //删除订单缺少orderId
defined("ERRORCODE_DEL_ORDER_OPENID_ERROR") or define("ERRORCODE_DEL_ORDER_OPENID_ERROR", '0210051002'); //删除订单缺少openId
defined("ERRORCODE_DEL_ORDER_REDIS_ERROR") or define("ERRORCODE_DEL_ORDER_REDIS_ERROR", '0210051003'); //删除订单redis服务器错误

//电影节
defined("ERRORCODE_FILM_FESTIVAL_ADD_LIST_ERROR") or define("ERRORCODE_FILM_FESTIVAL_ADD_LIST_ERROR", '0210061001'); //添加电影节清单失败
defined("ERRORCODE_FILM_FESTIVAL_REM_LIST_ERROR") or define("ERRORCODE_FILM_FESTIVAL_REM_LIST_ERROR", '0210061002'); //移除电影节清单失败

//媒资库
defined("ERRORCODE_MSDB_PARAM_ERROR") or define("ERRORCODE_MSDB_PARAM_ERROR", '0210071001'); //媒资库数据不合法
defined("ERRORCODE_MSDB_NO_MOVIEID_ERROR") or define("ERRORCODE_MSDB_NO_MOVIEID_ERROR", '0210071002'); //没有影片id
defined("ERRORCODE_MSDB_ACTOR_ADD_LIKE_ERROR") or define("ERRORCODE_MSDB_ACTOR_ADD_LIKE_ERROR", '0210071003'); //喜欢影人失败
defined("ERRORCODE_MSDB_ACTOR_DEL_LIKE_ERROR") or define("ERRORCODE_MSDB_ACTOR_DEL_LIKE_ERROR", '0210071004'); //取消喜欢影人失败
defined("ERRORCODE_MSDB_ACTOR_BASE_LIKE_ERROR") or define("ERRORCODE_MSDB_ACTOR_BASE_LIKE_ERROR", '0210071005'); //取消喜欢影人失败
defined("ERRORCODE_MSDB_ACTOR_DEL_APPRAISE_ERROR") or define("ERRORCODE_MSDB_ACTOR_DEL_APPRAISE_ERROR", '0210071006'); //取消喜欢影人失败
defined("ERRORCODE_MSDB_ACTOR_ADD_APPRAISE_ERROR") or define("ERRORCODE_MSDB_ACTOR_ADD_APPRAISE_ERROR", '0210071007'); //取消喜欢影人失败
defined("ERRORCODE_MSDB_ACTOR_NO_USER_APPRAISE_ERROR") or define("ERRORCODE_MSDB_ACTOR_NO_USER_APPRAISE_ERROR", '0210071007'); //当前影人不是评价影人

//限制
defined("ERRORCODE_LIMIT_OVER_ERROR") or define("ERRORCODE_LIMIT_OVER_ERROR", '0210081001'); //已经超出了限制
defined("ERRORCODE_LIMIT_PARAM_ERROR") or define("ERRORCODE_LIMIT_PARAM_ERROR", '0210081002'); //限制参数不完整
defined("ERRORCODE_LIMIT_IP_BLACK") or define("ERRORCODE_LIMIT_IP_BLACK", '0210081003'); //已经超出了限制

//滑动验证
defined("ERRORCODE_SLIDE_VERIFY_PARAMS_ERROR") or define("ERRORCODE_SLIDE_VERIFY_PARAMS_ERROR", '0210101001'); //参数不完整
defined("ERRORCODE_SLIDE_VERIFY_TIMEOUT_ERROR") or define("ERRORCODE_SLIDE_VERIFY_TIMEOUT_ERROR", '0210101002'); //验证码过期
defined("ERRORCODE_SLIDE_VERIFY_VERIFY_ERROR") or define("ERRORCODE_SLIDE_VERIFY_VERIFY_ERROR", '0210101003'); //验证码错误
defined("ERRORCODE_SLIDE_VERIFY_LONG_ERROR") or define("ERRORCODE_SLIDE_VERIFY_LONG_ERROR", '0210101004'); //传入的slideId过长
defined("ERRORCODE_SLIDE_VERIFY_CREDENTIAL_ERROR") or define("ERRORCODE_SLIDE_VERIFY_CREDENTIAL_ERROR", '0210101005'); //错误的密串

//以下为CMS，
defined('ERRORCODE_CMS_NO_DATA') or define('ERRORCODE_CMS_NO_DATA', '0210111001');//参数不完整
defined('ERRORCODE_CMS_NO_USER') or define('ERRORCODE_CMS_NO_USER', '0210111003');//用户不存在
defined('ERRORCODE_CMS_NO_ADD') or define('ERRORCODE_CMS_NO_ADD', '0210111004');//插入失败
defined('ERRORCODE_CMS_ADD_FAVOR') or define('ERRORCODE_CMS_ADD_FAVOR', '0210111005');//插入点赞失败
defined('ERRORCODE_CMS_DEL_FAVOR') or define('ERRORCODE_CMS_DEL_FAVOR', '0210111006');//插入点赞失败
defined('ERRORCODE_CMS_NO_COMMENT') or define('ERRORCODE_CMS_NO_COMMENT', '0210111007');//不存在的评论
defined('ERRORCODE_CMS_NO_COMMENT_USER') or define('ERRORCODE_CMS_NO_COMMENT_USER', '0210111008');//评论不属于当前用户
defined('ERRORCODE_CMS_DEL_COMMENT') or define('ERRORCODE_CMS_DEL_COMMENT', '0210111009');//评论删除失败

//以下为报名活动
defined('ERRORCODE_APPLY_ACTIVE_ERROR') or define('ERRORCODE_APPLY_ACTIVE_ERROR', '0210121001');//参数不完整
defined('ERRORCODE_APPLY_USER_ADD') or define('ERRORCODE_APPLY_USER_ADD', '0210121002');//报名用户信息添加失败
defined('ERRORCODE_DECRYPT_TOKEN_ERROR') or define('ERRORCODE_DECRYPT_TOKEN_ERROR', '0210121003');//用户未登录
defined('ERRORCODE_IS_APPLY_USER') or define('ERRORCODE_IS_APPLY_USER', '0210121004');//用户已报名

//历史订单详情
defined('ERRORCODE_ORDER_NOT_FOUND') or define('ERRORCODE_ORDER_NOT_FOUND', '0210051404');//历史订单不存在

//用户中心
defined('ERRORCODE_UCENTER_NO_DATA') or define('ERRORCODE_UCENTER_NO_DATA', '0210141001');//数据不存在
defined('ERRORCODE_UCENTER_UPDATE_FAIL') or define('ERRORCODE_UCENTER_UPDATE_FAIL', '0210141002');//更新失败
defined('ERRORCODE_UCENTER_NO_USER') or define('ERRORCODE_UCENTER_NO_USER', '0210142000');//该用户不存在
defined('ERRORCODE_UCENTER_NO_THIRD_DATA') or define('ERRORCODE_UCENTER_NO_THIRD_DATA', '0210142001');//该第三方数据不存在
defined('ERRORCODE_UCENTER_MOBILE_EXIST') or define('ERRORCODE_UCENTER_MOBILE_EXIST', '0210142002');//该手机号已经存在
defined('ERRORCODE_UCENTER_REGISTER_FAIL') or define('ERRORCODE_UCENTER_REGISTER_FAIL', '0210142003');//注册失败
defined('ERRORCODE_UCENTER_LACK_USERDATA') or define('ERRORCODE_UCENTER_LACK_USERDATA', '0210142006');//用户信息不全
defined('ERRORCODE_UCENTER_REGISTER_SUCCESS_NO_INFO') or define('ERRORCODE_UCENTER_REGISTER_SUCCESS_NO_INFO', '0210142013');//注册成功，获取信息失败
defined('ERRORCODE_UCENTER_LOGIN_FAIL') or define('ERRORCODE_UCENTER_LOGIN_FAIL', '0210142008');//用户名或密码错误
defined('ERRORCODE_UCENTER_BIND_FAIL') or define('ERRORCODE_UCENTER_BIND_FAIL', '0210142009');//绑定手机号失败
defined('ERRORCODE_UCENTER_MOBILE_CHANGE_FAIL') or define('ERRORCODE_UCENTER_MOBILE_CHANGE_FAIL', '0210142012');//手机号修改失败
defined('ERRORCODE_UCENTER_PASSWD_CHANGE_FAIL') or define('ERRORCODE_UCENTER_PASSWD_CHANGE_FAIL', '0210142014');//密码修改失败
defined('ERRORCODE_UCENTER_OLDPASSWD_ERROR') or define('ERRORCODE_UCENTER_OLDPASSWD_ERROR', '0210142016');//原密码错误
defined('ERRORCODE_UCENTER_RESET_PASSWD_FAIL') or define('ERRORCODE_UCENTER_RESET_PASSWD_FAIL', '0210142017');//重置密码失败
defined('ERRORCODE_UCENTER_OLDMOBILE_WRONG') or define('ERRORCODE_UCENTER_OLDMOBILE_WRONG', '0210142023');//旧手机号码不一致
defined('ERRORCODE_UCENTER_NO_MOBILE') or define('ERRORCODE_UCENTER_NO_MOBILE', '0210142024');//手机号不存在
defined('ERRORCODE_UCENTER_MOBILE_USED') or define('ERRORCODE_UCENTER_MOBILE_USED', '0210142025');//手机号码已被其他账号占用
defined('ERRORCODE_UCENTER_MOBILE_SELFBINDED') or define('ERRORCODE_UCENTER_MOBILE_SELFBINDED', '0210142026');//手机号码已被当前账号绑定
defined('ERRORCODE_UCENTER_MOBILE_BINDED') or define('ERRORCODE_UCENTER_MOBILE_BINDED', '0210142027');//当前账号已被绑定过
defined('ERRORCODE_UCENTER_PASSWD_EXIST') or define('ERRORCODE_UCENTER_PASSWD_EXIST', '0210142029');//用户密码已存在
defined('ERRORCODE_UCENTER_SET_PASSWD_FAIL') or define('ERRORCODE_UCENTER_SET_PASSWD_FAIL', '0210142030');//密码设置失败
defined('ERRORCODE_UCENTER_GET_UCID_ERROR') or define('ERRORCODE_UCENTER_GET_UCID_ERROR', '0210142031');//换取UcId失败


//短信相关
defined('ERRORCODE_SMS_SYSERROR') or define('ERRORCODE_SMS_SYSERROR', '0210151001');//系统异常，验证失败
defined('ERRORCODE_SMS_MOBILE_NOT_FOUND') or define('ERRORCODE_SMS_MOBILE_NOT_FOUND', '0210151002');//查找手机号验证信息失败
defined('ERRORCODE_SMS_CODE_WRONG') or define('ERRORCODE_SMS_CODE_WRONG', '0210151003');//验证码错误
defined('ERRORCODE_SMS_CODE_OVERTIME') or define('ERRORCODE_SMS_CODE_OVERTIME', '0210151004');//验证码已过期，请重试
//微信登陆模块
defined('ERRORCODE_WECHAT_REDIRECT_URL_PARAMS_ERROR') or define('ERRORCODE_WECHAT_REDIRECT_URL_PARAMS_ERROR', '0210211001');//302跳转码

//折扣卡降级
defined('ERRORCODE_DEMOTE_VIP_CARD') or define('ERRORCODE_DEMOTE_VIP_CARD', '0210281001');//折扣卡降级

//
defined('ERRORCODE_DELETE_USER_TRACE_PATH') or define('ERRORCODE_DELETE_USER_TRACE_PATH', '0210161002');//删除观影轨迹失败

//token
defined('ERRORCODE_MQQ_TOKEN_INVALIAD') or define('ERRORCODE_MQQ_TOKEN_INVALIAD', '0210331001');//手Q无效的token

//小程序emoji
defined('ERRORCODE_XCX_EMOJI_OPENID_ERROR') or define('ERRORCODE_XCX_EMOJI_OPENID_ERROR', '0210381001');//无效用户
defined('ERRORCODE_XCX_EMOJI_INNER_ERROR') or define('ERRORCODE_XCX_EMOJI_INNER_ERROR', '0210381002');//内部题目数据错误
defined('ERRORCODE_XCX_EMOJI_ANSWER_ERROR') or define('ERRORCODE_XCX_EMOJI_ANSWER_ERROR', '0210381003');//答案回答错误
defined('ERRORCODE_XCX_EMOJI_ANSWER_VALIDATE_ERROR') or define('ERRORCODE_XCX_EMOJI_ANSWER_VALIDATE_ERROR', '0210381004');//答案回答错误
defined('ERRORCODE_XCX_EMOJI_EMOJI_VALIDATE_ERROR') or define('ERRORCODE_XCX_EMOJI_EMOJI_VALIDATE_ERROR', '0210381005');//答案回答错误