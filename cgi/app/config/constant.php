<?php

/**
 * 常量的定义, 最好在定义之前, 先和组内成员沟通是否有定义规则
 */

//定义默认redis渠道
defined("DEFAULT_REDIS_CHANNEL_ID") or define("DEFAULT_REDIS_CHANNEL_ID", 11);
//定义cdn地址
defined("CDN_APPNFS") or define("CDN_APPNFS", "https://appnfs.wepiao.com");

//静态数据
defined('STATIC_MOVIE_DATA') or define('STATIC_MOVIE_DATA', 'static_movie_data');
defined('STATIC_MOVIE_INFO') or define('STATIC_MOVIE_INFO', 'static_movie_info');
defined('STATIC_MOVIE_INFO_CHANNEL') or define('STATIC_MOVIE_INFO_CHANNEL', 'static_movie_info_channel');
defined('STATIC_MOVIE_PREVUE') or define('STATIC_MOVIE_PREVUE', 'static_movie_prevue');
defined('STATIC_MOVIE_PREVUE_CHANNEL') or define('STATIC_MOVIE_PREVUE_CHANNEL', 'static_movie_prevue_channel');
defined('DELETE_ORDER') or define('DELETE_ORDER', 'delete_order');
//User Generated Content 用户原创内容
defined('USER_GENERATED_CONTENT') or define('USER_GENERATED_CONTENT', 'user_generated_content');
defined('WEI_XIN_TOKEN') or define('WEI_XIN_TOKEN', 'wei_xin_token');
defined('USER_COMMENT_CACHE') or define('USER_COMMENT_CACHE', 'user_comment_cache');//用户评论评分相关缓存
defined('USER_MOVIE_PEE') or define('USER_MOVIE_PEE', 'user_movie_pee');//影片-尿点-用户尿点相关缓存
defined('VERIFY_CODE_DATA') or define('VERIFY_CODE_DATA', 'verify_code_data');
defined('IP_LIMIT') or define('IP_LIMIT', 'ip_limit');
//用户标签
defined('USER_TAG') or define('USER_TAG', 'user_tag');
//IP库
defined('IP_DATABASE') or define('IP_DATABASE', 'ip_database');
//媒资库redis
defined('MOVIE_SHOW_DB') or define('MOVIE_SHOW_DB', 'movie_show_db');
//红包新老用户
defined('BONUS_NEW_USER') or define('BONUS_NEW_USER', 'mobile_set');
//各种限制所用的redis
defined('COMMON_LIMIT') or define('COMMON_LIMIT', 'common_limit');
//emoji小程序
defined('EMOJI_XIAOCHENGXU') or define('EMOJI_XIAOCHENGXU', 'emoji_xiaochengxu');
//组内共用redis，适合容量小但请求频繁的数据
defined('GROUP_SHARE_FREQUENT') or define('GROUP_SHARE_FREQUENT', 'group_share_frequent');

//观影秘籍
defined('MOVIE_GUIDE') or define('MOVIE_GUIDE', 'movie_guide');
//消息中心
defined('MESSAGE_CENTER') or define('MESSAGE_CENTER', 'message_center');

//wxapp accesstoken
defined('WXAPP_TOKEN') or define('WXAPP_TOKEN', 'wxapp_token');

//影片片单
defined('FILM_LIST') or define('FILM_LIST', 'film_list');


// redis 静态数据 key, 静态数据的Key, 都以 KEY+具体信息开头, 比如: KEY_CITY, KEY_CINEMA
defined('KEY_CITY') or define('KEY_CITY', 'cities');//城市列表redis key
defined('KEY_CITY_V2') or define('KEY_CITY_V2', 'cities_v2');//城市列表redis key
defined('KEY_CITY_MOVIE_LIST') or define('KEY_CITY_MOVIE_LIST', 'city_movie_list');//在映影片
defined('KEY_CITY_MOVIE_WILL') or define('KEY_CITY_MOVIE_WILL', 'city_movie_will');//即将上映影片
defined('KEY_CITY_CINEMA_LIST') or define('KEY_CITY_CINEMA_LIST', 'city_cinema_list');//城市下影院列表
defined('KEY_CINEMA_IDS') or define('KEY_CINEMA_IDS', 'cinema_ids');//所有影院ID
defined('KEY_CINEMA_CITY_IDS') or define('KEY_CINEMA_CITY_IDS', 'cinema_ids_city_');//城市下所有影院ID
defined('KEY_MOVIE_IDS') or define('KEY_MOVIE_IDS', 'movie_ids');//所有影片ID
defined('KEY_CINEMA_INFO') or define('KEY_CINEMA_INFO', 'cinema_info');
defined('KEY_MOVIE_INFO') or define('KEY_MOVIE_INFO', 'movie_info');
defined('KEY_CINEMA_SCHE') or define('KEY_CINEMA_SCHE', 'cinema_sche');
defined('KEY_MOVIE_SCHE') or define('KEY_MOVIE_SCHE', 'movie_sche');
defined('KEY_MOVIE_SCHE_MAP') or define('KEY_MOVIE_SCHE_MAP', 'movie_sche_date_map_');//每个影片排期对应的影院
defined('KEY_CINEMA_ROOM_IDS') or define('KEY_CINEMA_ROOM_IDS', 'cinema_room_');//影院所有影厅ID
defined('KEY_CINEMA_ROOM_INFO') or define('KEY_CINEMA_ROOM_INFO', 'cinema_room_info');//影院影厅信息
defined('KEY_CINEMA_DIS_DES') or define('KEY_CINEMA_DIS_DES', 'cinema_discount_desc_');//影院优惠信息
defined('KEY_CINEMA_ID_DIS_DES') or define('KEY_CINEMA_ID_DIS_DES', 'cinema_id_discount_desc_');//影院优惠信息，非hash结构版
defined('KEY_ALL_HAS_SCHE_MOVIE_IDS') or define('KEY_ALL_HAS_SCHE_MOVIE_IDS', 'all_has_sche_movie_ids');//所有有排期的影片ID
defined('KEY_CINEMA_ROOM_SPECIAL') or define('KEY_CINEMA_ROOM_SPECIAL', 'cinema_room_special');//影院影厅信息，缓存时间为5分钟
defined('KEY_CINEMA_REFUND') or define('KEY_CINEMA_REFUND', 'cinema_refund');//支持退票的影院信息，缓存时间为5分钟
defined('KEY_QQ_CINEMA_DATA') or define('KEY_QQ_CINEMA_DATA', 'qq_cinema_data');//腾讯影院ID: [城市][区][商圈]=>腾讯影院id
defined('KEY_QQ_CINEMA_PRICE') or define('KEY_QQ_CINEMA_PRICE', 'qq_cinema_price');//腾讯影院价格,[影院id]=>[价格]
defined('KEY_QQ_CINEMA_MAP') or define('KEY_QQ_CINEMA_MAP', 'qq_cinema_map');//腾讯影院id与我们影院id的映射，key为腾讯影院id,value为我们影院id
defined('KEY_CINEMA_CITY_MAP') or define('KEY_CINEMA_CITY_MAP', 'cinema_city_map');//将我们影院id与城市id的映射
defined('KEY_CINEMA_SCHE_PRICE') or define('KEY_CINEMA_SCHE_PRICE', 'cinema_movie_price'); //影院价格场次[新增]
defined('KEY_CITY_MOVIE_LIST_SORT') or define('KEY_CITY_MOVIE_LIST_SORT', 'city_movie_list_sort_');//在映影片之影片id的有序集合
defined('KEY_CITY_MOVIE_LIST_HASH') or define('KEY_CITY_MOVIE_LIST_HASH', 'city_movie_list_hash_');//在映影片之影片信息的哈希结构
defined('KEY_CITY_MOVIE_LIST_V2') or define('KEY_CITY_MOVIE_LIST_V2', 'city_movie_list_v2_');//在映影片
defined('KEY_CITY_MOVIE_LIST_V3') or define('KEY_CITY_MOVIE_LIST_V3', 'city_movie_list_v3_');//在映影片V3大数据版本
defined('KEY_BANK_PRIVILEGE') or define('KEY_BANK_PRIVILEGE', 'bank_privilege');//在映影片之影片信息的哈希结构
defined('KEY_MOVIE_RECOMMENDED_NEWS') or define('KEY_MOVIE_RECOMMENDED_NEWS', 'movie_recommended_news'); //单品推荐信息
defined('KEY_MOVIE_CAMP') or define('KEY_MOVIE_CAMP', 'movie_camp_'); //阵营购票计数
defined('KEY_CITY_MOVIE_WILL_WITH_DATE_DATA') or define('KEY_CITY_MOVIE_WILL_WITH_DATE_DATA', 'city_movie_will_with_date_data_');//即将上映影片（按日期分组）的数据源，哈希结构
defined('KEY_CITY_MOVIE_WILL_WITH_DATE_DIMENSION') or define('KEY_CITY_MOVIE_WILL_WITH_DATE_DIMENSION', 'city_movie_will_with_date_demension');//即将上映影片（按日期分组）的差异数据
defined('KEY_MOVIE_CITY_SCHE_FLAG') or define('KEY_MOVIE_CITY_SCHE_FLAG', 'movie_city_sche_flag_'); //影片维度下的某个城市是否有排期（用于在info_movie中，添加是否有排期的标识，前端就不用读影片排期了）
defined('KEY_MOVIE_CUSTOM_SEAT') or define('KEY_MOVIE_CUSTOM_SEAT', 'movie_custom_seats_info'); //影片定制化选座数据
defined('KEY_PRICE_NOTIFY') or define('KEY_PRICE_NOTIFY', 'price_notify');//定价通知的存储字段
defined('KEY_BANK_PRIVILEGE_NEW') or define('KEY_BANK_PRIVILEGE_NEW', 'static_new_bank_privilege_data_');//新版银行卡优惠redis key

defined('KEY_WXAPP_TOKEN') or define('KEY_WXAPP_TOKEN', 'mpaccesstoken');;//wxapp accesstoken key

//大数据ip修正redis
defined('BIGDATA_IP_LIST') or define('BIGDATA_IP_LIST', 'big_data_ip');
////大数据ip修正redis key
defined('BIGDATA_IP_REDIS_KEY') or define('BIGDATA_IP_REDIS_KEY', 'ip_list');

//CronTaskRestruct的数据常量
// redis 静态数据 key, 静态数据的Key, 都以 KEY+具体信息开头, 比如: KEY_CITY, KEY_CINEMA
defined('STATIC_KEY_CITY') or define('STATIC_KEY_CITY', 'city_info');//城市列表redis key
defined('STATIC_KEY_CITY_V2') or define('STATIC_KEY_CITY_V2', 'city_info_v2');//城市列表redis key
defined('STATIC_KEY_CITY_MOVIE_LIST') or define('STATIC_KEY_CITY_MOVIE_LIST', 'city_movie_list_');//在映影片
defined('STATIC_KEY_CITY_MOVIE_WILL') or define('STATIC_KEY_CITY_MOVIE_WILL', 'city_movie_will_');//即将上映影片
defined('STATIC_KEY_CITY_MOVIE_WILL_WITH_DATE') or define('STATIC_KEY_CITY_MOVIE_WILL_WITH_DATE', 'city_movie_will_with_date_');//即将上映影片（按日期分组）
defined('STATIC_KEY_CITY_CINEMA_LIST') or define('STATIC_KEY_CITY_CINEMA_LIST', 'city_cinema_list');//城市下影院列表
defined('STATIC_KEY_CINEMA_INFO') or define('STATIC_KEY_CINEMA_INFO', 'cinema_info');
defined('STATIC_KEY_CINEMA_SCHE') or define('STATIC_KEY_CINEMA_SCHE', 'cinema_sche_');
defined('STATIC_KEY_MOVIE_INFO') or define('STATIC_KEY_MOVIE_INFO', 'movie_info');
defined('STATIC_KEY_MOVIE_SCHE') or define('STATIC_KEY_MOVIE_SCHE', 'movie_sche');
defined('STATIC_KEY_CINEMA_ROOM_INFO') or define('STATIC_KEY_CINEMA_ROOM_INFO', 'cinema_room_info_');//影院影厅信息
defined('STATIC_KEY_CINEMA_ID_DIS_DES') or define('STATIC_KEY_CINEMA_ID_DIS_DES', 'cinema_id_discount_desc');//影院优惠信息，非hash结构版
defined('STATIC_KEY_CINEMA_SCHE_PRICE') or define('STATIC_KEY_CINEMA_SCHE_PRICE', 'cinema_movie_price'); //影院价格场次[新增]
defined('STATIC_KEY_MOVIE_WILL_SORT_WANT') or define('STATIC_KEY_MOVIE_WILL_SORT_WANT', 'city_movie_will_sort_want');//即将上映影片之影片id的有序集合
defined('STATIC_KEY_MOVIE_WILL_SORT_SEEN') or define('STATIC_KEY_MOVIE_WILL_SORT_SEEN', 'city_movie_will_sort_seen');//即将上映影片之影片id的有序集合
defined('STATIC_KEY_MOVIE_WILL_SORT_DATE') or define('STATIC_KEY_MOVIE_WILL_SORT_DATE', 'city_movie_will_sort_date');//即将上映影片之影片id的有序集合
defined('STATIC_KEY_MOVIE_WILL_DIFF') or define('STATIC_KEY_MOVIE_WILL_DIFF', 'city_movie_will_diff_');//即将上映影片之影片信息的哈希结构
defined('STATIC_KEY_MOVIE_WILL_COMMON_LIST') or define('STATIC_KEY_MOVIE_WILL_COMMON_LIST', 'city_movie_will_common_list');//即将上映影片之影片信息的哈希结构
defined('STATIC_KEY_MOVIE_WILL_WITH_DATE_DATA') or define('STATIC_KEY_MOVIE_WILL_WITH_DATE_DATA', 'city_movie_will_with_date_data_');//即将上映影片（按日期分组）的数据源，哈希结构
defined('STATIC_KEY_MOVIE_WILL_WITH_DIFF_DATA') or define('STATIC_KEY_MOVIE_WILL_WITH_DIFF_DATA', 'city_movie_will_with_diff_data_');//即将上映影片（按日期分组）的差异数据
defined('STATIC_KEY_MOVIE_CITY_SCHE_FLAG') or define('STATIC_KEY_MOVIE_CITY_SCHE_FLAG', 'movie_city_sche_flag_'); //影片维度下的某个城市是否有排期（用于在info_movie中，添加是否有排期的标识，前端就不用读影片排期了）
defined('STATIC_KEY_MOVIE_CITY_SCHE_FLAG_V3') or define('STATIC_KEY_MOVIE_CITY_SCHE_FLAG_V3', 'movie_city_sche_flag_v3_'); //影片维度下的某个城市是否有排期（用于在info_movie中，添加是否有排期的标识，前端就不用读影片排期了）
defined('STATIC_KEY_MOVIE_CUSTOM_SEAT') or define('STATIC_KEY_MOVIE_CUSTOM_SEAT', 'movie_custom_seats_info'); //影片定制化选座数据


//下面的待用
defined('STATIC_KEY_CINEMA_IDS') or define('STATIC_KEY_CINEMA_IDS', 'cinema_ids');//所有影院ID
defined('STATIC_KEY_CINEMA_CITY_IDS') or define('STATIC_KEY_CINEMA_CITY_IDS', 'cinema_ids_city_');//城市下所有影院ID
defined('STATIC_KEY_MOVIE_IDS') or define('STATIC_KEY_MOVIE_IDS', 'movie_ids');//所有影片ID
defined('STATIC_KEY_MOVIE_SCHE_MAP') or define('STATIC_KEY_MOVIE_SCHE_MAP', 'movie_sche_date_map_');//每个影片排期对应的影院
defined('STATIC_KEY_CINEMA_ROOM_IDS') or define('STATIC_KEY_CINEMA_ROOM_IDS', 'cinema_room_');//影院所有影厅ID
defined('STATIC_KEY_CINEMA_DIS_DES') or define('STATIC_KEY_CINEMA_DIS_DES', 'cinema_discount_desc_');//影院优惠信息
defined('STATIC_KEY_ALL_HAS_SCHE_MOVIE_IDS') or define('STATIC_KEY_ALL_HAS_SCHE_MOVIE_IDS', 'all_has_sche_movie_ids');//所有有排期的影片ID
defined('STATIC_KEY_CINEMA_ROOM_SPECIAL') or define('STATIC_KEY_CINEMA_ROOM_SPECIAL', 'cinema_room_special');//影院影厅信息，缓存时间为5分钟
defined('STATIC_KEY_CINEMA_REFUND') or define('STATIC_KEY_CINEMA_REFUND', 'cinema_refund');//支持退票的影院信息，缓存时间为5分钟
defined('STATIC_KEY_QQ_CINEMA_DATA') or define('STATIC_KEY_QQ_CINEMA_DATA', 'qq_cinema_data');//腾讯影院ID: [城市][区][商圈]=>腾讯影院id
defined('STATIC_KEY_QQ_CINEMA_PRICE') or define('STATIC_KEY_QQ_CINEMA_PRICE', 'qq_cinema_price');//腾讯影院价格,[影院id]=>[价格]
defined('STATIC_KEY_QQ_CINEMA_MAP') or define('STATIC_KEY_QQ_CINEMA_MAP', 'qq_cinema_map');//腾讯影院id与我们影院id的映射，key为腾讯影院id,value为我们影院id
defined('STATIC_KEY_CINEMA_CITY_MAP') or define('STATIC_KEY_CINEMA_CITY_MAP', 'cinema_city_map');//将我们影院id与城市id的映射

//redis 不同功能用到的 key.
//涉及到用户的部分, 以 USER 开头
defined('USER_IS_FAVORITE_KEY') or define('USER_IS_FAVORITE_KEY', 'user_is_favorite_');
defined('USER_FAVORITE_CINEMAS') or define('USER_FAVORITE_CINEMAS', 'user_favorite_');
defined('USER_WANT_MOVIES') or define('USER_WANT_MOVIES', 'user_want_');//用户想看的影片
defined('USER_SEEN_MOVIES') or define('USER_SEEN_MOVIES', 'user_seen_');//用户看过的影片
defined('USER_FAVOR_COMMENTS') or define('USER_FAVOR_COMMENTS', 'user_favor_comments');//用户赞过的评论

//输出的json中,moviedata包得这一层需要的名称, 以 FILE 开头
defined('FILE_SCHED_CITY_CINEMA') or define('FILE_SCHED_CITY_CINEMA', 'sched_city_cinema_');
defined('FILE_INFO_CINEMA') or define('FILE_INFO_CINEMA', 'info_cinema_');
defined('FILE_CINEMAS_CITY') or define('FILE_CINEMAS_CITY', 'cinemas_city_');
defined('FILE_CINEMA_ROOM') or define('FILE_CINEMA_ROOM', 'detail_cinema_room_');
defined('FILE_CITY_INFO') or define('FILE_CITY_INFO', 'city');
defined('FILE_CITY') or define('FILE_CITY', 'city');
defined('FILE_INFO_MOVIE') or define('FILE_INFO_MOVIE', 'info_movie_');
defined('FILE_MOVIES_CITY') or define('FILE_MOVIES_CITY', 'movies_city_');
defined('FILE_MOVIES_WILL') or define('FILE_MOVIES_WILL', 'movies_will_');
defined('FILE_SCHED_CITY_MOVIE') or define('FILE_SCHED_CITY_MOVIE', 'sched_city_movie_');

//大数据搜索引擎,以BIG_DATA_SEARCH
defined('BIG_DATA_SEARCH_MATCH') or define('BIG_DATA_SEARCH_MATCH', '60%'); //搜索引擎的匹配程度
defined('BIG_DATA_SEARCH_TIE_BREAKER') or define('BIG_DATA_SEARCH_TIE_BREAKER', 0.3);

//redis 不同功能用到的 key.
//涉及到评分评论相关的部分, 以 COMMENT 开头
defined('COMMENT_BLACKLIST_DATA') or define('COMMENT_BLACKLIST_DATA', 'db_wxmovieadmin_t_black_list'); //黑名单信息
defined('COMMENT_SHIELDING_DATA') or define('COMMENT_SHIELDING_DATA', 'db_wxmovieadmin_t_shielding_words'); //屏蔽词信息
defined('COMMENT_SENSITIVEWORDS_DATA') or define('COMMENT_SENSITIVEWORDS_DATA', 'db_wxmovieadmin_t_sensitive_words'); //敏感词信息
defined('COMMENT_TIME_LIST') or define('COMMENT_TIME_LIST', 'comment_time_list_'); //时间维度热评
defined('COMMENT_REPLY_LIST') or define('COMMENT_REPLY_LIST', 'comment_reply_list_'); //回复数热评
defined('COMMENT_FAVOR_LIST') or define('COMMENT_FAVOR_LIST', 'comment_favor_list_'); //喜欢数热评
defined('COMMENT_NEW_LIST') or define('COMMENT_NEW_LIST', 'comment_new_list_'); //最新评论
defined('COMMENT_REPLY_LIST_KEY') or define('COMMENT_REPLY_LIST_KEY', 'comment_reply_list_Key_'); //评论下回复列表（id desc）
defined('COMMENT_REPLY_UCIDSET_KEY') or define('COMMENT_REPLY_UCIDSET_KEY', 'comment_reply_user_set_'); //回复评论的用户集合,key为commentId
defined('COMMENT_KEY') or define('COMMENT_KEY', 'comment_Key_'); //评论内容
defined('REPLY_KEY') or define('REPLY_KEY', 'reply_Key_'); //回复内容
defined('COMMENT_MOVIE_INFO') or define('COMMENT_MOVIE_INFO', 'comment_movie_info_'); //影片内容（基于评论库t_movie使用的）【此key在movieDBApp.php中会用到，不可随意删除】
defined('COMMENT_FAVOR_SET') or define('COMMENT_FAVOR_SET', 'comment_favor_set_'); //评论的点赞集合(每个元素：ucid) 无法用COMMENT_FAVOR_LIST代替
defined('COMMENT_LIST_USER') or define('COMMENT_LIST_USER', 'comment_list_user_');//一个用户的评论过的影片的列表列表

//评分相关
defined('SCORE_TOTAL_SCORE') or define('SCORE_TOTAL_SCORE', 'score_total_score_');//指定影片的总评分数,key为movieId
defined('SCORE_TOTAL_PERSON') or define('SCORE_TOTAL_PERSON', 'score_total_person_');//指定影片的总评分人数,key为movieId
//defined('SCORE_INFO_ROW') or define('SCORE_INFO_ROW','score_info_ucid_{$ucid}_movieId_{$movieId}'); 由于此key用了2个变量，不方便定义在常量中，在score中用到
//想看相关
defined('MOVIE_WANT_USER_LIST') or define('MOVIE_WANT_USER_LIST', 'movie_want_user_list_'); //影片-想看的用户
defined('MOVIE_WANT_MOVIE_LIST') or define('MOVIE_WANT_MOVIE_LIST', 'movie_want_movie_list_');//用户-想看的影片
//看过相关
defined('MOVIE_SEEN_USER_LIST') or define('MOVIE_SEEN_USER_LIST', 'movie_seen_user_list_');
defined('MOVIE_SEEN_MOVIE_LIST') or define('MOVIE_SEEN_MOVIE_LIST', 'movie_seen_movie_list_');

//评分评论相关：非redis key
defined('COMMENT_MOVIE_INFO_TIME') or define('COMMENT_MOVIE_INFO_TIME', 24 * 3600); //影片内容缓存时间
defined('COMMENT_UPDATE_TIME') or define('COMMENT_UPDATE_TIME', 60); //评论发布间隔时间
defined('COMMENT_REPLY_UPDATE_TIME') or define('COMMENT_REPLY_UPDATE_TIME', 30); //回复发布间隔时间
defined('MOVIE_BASE_SCORE_NUM') or define('MOVIE_BASE_SCORE_NUM', 100); //影片基础分数
defined('MOVIE_BASE_SCORE_COUNT_NUM') or define('MOVIE_BASE_SCORE_COUNT_NUM', 10); //影片基础评分人数
defined('MOVIE_BASE_WANT_COUNT_NUM') or define('MOVIE_BASE_WANT_COUNT_NUM', 80); //影片初试想看人数

//微信小工具
defined("ACCESS_TOKEN_KEY") or define("ACCESS_TOKEN_KEY", "weixin_token_"); //redis access token
defined("TICKET_JS_API_KEY") or define("TICKET_JS_API_KEY", "weixin_js_api_ticket_"); //redis ticket key
defined("TICKET_WX_CARD_KEY") or define("TICKET_WX_CARD_KEY", "weixin_card_ticket_"); //redis card ticket key

//手Q公众号
defined("MQQ_MP_ACCESS_TOKEN") or define("MQQ_MP_ACCESS_TOKEN", "mqq_mp_access_token"); //redis access token

//用户access_token
defined("MQQ_USER_ACCESS_TOKEN") or define("MQQ_USER_ACCESS_TOKEN", "mqq_user_token_"); //手Q用户access_token
defined("WX_USER_ACCESS_TOKEN") or define("WX_USER_ACCESS_TOKEN", "wx_user_token_"); //微信用户access_token
defined("WXAPP_USER_ACCESS_TOKEN") or define("WXAPP_USER_ACCESS_TOKEN", "wxapp_user_token_"); //微信用户access_token

//电影原声音乐
defined("MOVIE_MUSIC_LIST") or define("MOVIE_MUSIC_LIST", "movie_music_list:{#movieId}");//原声音乐
//新版评分评论key
defined("NEWCOMMENT_COMMENTED_MOVIES") or define("NEWCOMMENT_COMMENTED_MOVIES", "newcomment_commented_movies_user:{#ucid}");//用户评论过的影片
defined("NEWCOMMENT_COMMENTED_MOVIES_COUNT") or define("NEWCOMMENT_COMMENTED_MOVIES_COUNT", "newcomment_commented_movies_count_user:{#ucid}");//用户评论过的影片【弃用】
defined("NEWCOMMENT_COMMENTED_USERS") or define("NEWCOMMENT_COMMENTED_USERS", "newcomment_commented_users_movie:{#movieId}");//评论过影片的用户
defined("NEWCOMMENT_COMMENTED_USERS_COUNT") or define("NEWCOMMENT_COMMENTED_USERS_COUNT", "newcomment_commented_users_count_movie:{#movieId}");//评论过影片的用户-总数
defined("NEWCOMMENT_COMMENT_SORT_NEW") or define("NEWCOMMENT_COMMENT_SORT_NEW", "newcomment_comment_sort_new_movie:{#movieId}");//最新评论
defined("NEWCOMMENT_COMMENT_SORT_NEW_COUNT") or define("NEWCOMMENT_COMMENT_SORT_NEW_COUNT", "newcomment_comment_sort_new_count_movie:{#movieId}");//最新评论--总数
defined("NEWCOMMENT_COMMENT_SORT_HOT") or define("NEWCOMMENT_COMMENT_SORT_HOT", "newcomment_comment_sort_hot_movie:{#movieId}");//热门评论
defined("NEWCOMMENT_COMMENT_SORT_HOT_COUNT") or define("NEWCOMMENT_COMMENT_SORT_HOT_COUNT", "newcomment_comment_sort_hot_count_movie:{#movieId}");//热门评论
defined("NEWCOMMENT_COMMENT_SORT_GOOD") or define("NEWCOMMENT_COMMENT_SORT_GOOD", "newcomment_comment_sort_good_movie:{#movieId}");//好评排序（评分为80，100）
defined("NEWCOMMENT_COMMENT_SORT_GOOD_COUNT") or define("NEWCOMMENT_COMMENT_SORT_GOOD_COUNT", "newcomment_comment_sort_good_count_movie:{#movieId}");//好评排序（评分为80，100）--总数
defined("NEWCOMMENT_COMMENT_SORT_BAD") or define("NEWCOMMENT_COMMENT_SORT_BAD", "newcomment_comment_sort_bad_movie:{#movieId}");//差评评排序（评分为0，20）
defined("NEWCOMMENT_COMMENT_SORT_BAD_COUNT") or define("NEWCOMMENT_COMMENT_SORT_BAD_COUNT", "newcomment_comment_sort_bad_count_movie:{#movieId}");//差评评排序（评分为0，20）--总数
defined("NEWCOMMENT_COMMENT_SORT_BUY") or define("NEWCOMMENT_COMMENT_SORT_BUY", "newcomment_comment_sort_buy_movie:{#movieId}");//购票排序（评分为0，20）
defined("NEWCOMMENT_COMMENT_SORT_BUY_COUNT") or define("NEWCOMMENT_COMMENT_SORT_BUY_COUNT", "newcomment_comment_sort_buy_count_movie:{#movieId}");//购票排序（评分为0，20）--总数
defined("NEWCOMMENT_COMMENT_SORT_RECOMMEND") or define("NEWCOMMENT_COMMENT_SORT_RECOMMEND", "newcomment_comment_recommend_new_movie:{#movieId}");//推荐的评论
defined("NEWCOMMENT_COMMENT_SORT_RECOMMEND_NEW") or define("NEWCOMMENT_COMMENT_SORT_RECOMMEND_NEW", "newcomment_comment_recommend_new_str_movie:{#movieId}");//推荐的评论

defined("NEWCOMMENT_COMMENT_STAR") or define("NEWCOMMENT_COMMENT_STAR", "comment_star:{#ucid}");//明星信息

defined("NEWCOMMENT_CONTENT") or define("NEWCOMMENT_CONTENT", "newcomment_content_comment:{#commentId}");//评论内容
defined("NEWCOMMENT_COMMENTID_USER") or define("NEWCOMMENT_COMMENTID_USER", "newcomment_commentId_user:{#ucid}");//用户评论影片对应的commentId
defined("NEWCOMMENT_REPLY_LIST") or define("NEWCOMMENT_REPLY_LIST", "newcomment_reply_list_comment:{#commentId}");//评论的回复列表
defined("NEWCOMMENT_REPLY_LIST_COUNT") or define("NEWCOMMENT_REPLY_LIST_COUNT", "newcomment_reply_list_count_comment:{#commentId}");//评论的回复总数

defined("NEWCOMMENT_REPLY_CONTENT") or define("NEWCOMMENT_REPLY_CONTENT", "newcomment_reply_content:{#replyId}");//评论的回复内容
#defined("NEWCOMMENT_COMMENT_NUMBER") or define("NEWCOMMENT_COMMENT_NUMBER", "newcomment_comment_number_movie:{#movieId}");//影片的评论数 【已经弃用】
defined("NEWCOMMENT_REPLY_NUMBER") or define("NEWCOMMENT_REPLY_NUMBER", "newcomment_reply_number_comment:{#commentId}");//评论的回复数【弃用】
defined("NEWCOMMENT_REPLY_LAST_TIME") or define("NEWCOMMENT_REPLY_LAST_TIME", "newcomment_reply_last_time_user:{#ucid}");//评论的最后回复时间
#defined("NEWCOMMENT_USER_SCORE") or define("NEWCOMMENT_USER_SCORE", "newcomment_user_score_movie:{#movieId}");//影片下各个用户的评分【更改】
defined("NEWCOMMENT_USER_SCORE") or define("NEWCOMMENT_USER_SCORE", "newcomment_user_score:{#ucid}");//用户对各个影片的评分
defined("NEWCOMMENT_SCORE_NUMBER") or define("NEWCOMMENT_SCORE_NUMBER", "newcomment_score_number_movie:{#movieId}");//影片下各个
defined("NEWCOMMENT_SCORE_FILL_NUMBER") or define("NEWCOMMENT_SCORE_FILL_NUMBER", 'newcomment_score_fill_number_movie:{#movieId}');//影片注水评分人数  hash
defined("NEWCOMMENT_SCORE_REAL_NUMBER") or define("NEWCOMMENT_SCORE_REAL_NUMBER", 'newcomment_score_real_number_movie:{#movieId}');//影片实际评分人数  hash

defined("NEWCOMMENT_UPDATE_MOVIE") or define("NEWCOMMENT_UPDATE_MOVIE", "newcomment_update_movie");//最近有哪些影片评分被修改过
defined("NEWCOMMENT_WANT_MOVIE_LIST") or define("NEWCOMMENT_WANT_MOVIE_LIST", "newcomment_want_movie_list_user:{#ucid}");//用户想看的影片
defined("NEWCOMMENT_WANT_MOVIE_LIST_COUNT") or define("NEWCOMMENT_WANT_MOVIE_LIST_COUNT", "newcomment_want_movie_list_count_user:{#ucid}");//用户想看的影片--总数
defined("NEWCOMMENT_WANT_USER_LIST") or define("NEWCOMMENT_WANT_USER_LIST", "newcomment_want_user_list_movie:{#movieId}");//影片想看的用户
defined("NEWCOMMENT_WANT_USER_LIST_COUNT") or define("NEWCOMMENT_WANT_USER_LIST_COUNT", "newcomment_want_user_list_count_movie:{#movieId}");//影片想看的用户--总数【弃用】
defined("NEWCOMMENT_WANT_FILL") or define("NEWCOMMENT_WANT_FILL", "newcomment_want_fill_movie:{#movieId}");//想看数注水
defined("NEWCOMMENT_WANT_USER_HASH") or define("NEWCOMMENT_WANT_USER_HASH", "newcomment_want_user_hash:{#ucid}");//用户想看的影片hash,大k:ucid,小k,movieId,值:空或者是json后的信息【弃用】

defined("NEWCOMMENT_SEEN_MOVIE_LIST") or define("NEWCOMMENT_SEEN_MOVIE_LIST", "newcomment_seen_movie_list_user:{#ucid}");//用户看过的影片
defined("NEWCOMMENT_SEEN_MOVIE_LIST_COUNT") or define("NEWCOMMENT_SEEN_MOVIE_LIST_COUNT", "newcomment_seen_movie_list_count_user:{#ucid}");//用户看过的影片
defined("NEWCOMMENT_SEEN_USER_LIST") or define("NEWCOMMENT_SEEN_USER_LIST", "newcomment_seen_user_list_movie:{#movieId}");//影片看过的用户【弃用】
defined("NEWCOMMENT_SEEN_USER_LIST_COUNT") or define("NEWCOMMENT_SEEN_USER_LIST_COUNT", "newcomment_seen_user_list_count_movie:{#movieId}");//影片看过的用户【弃用】
defined("NEWCOMMENT_SEEN_USER_HASH") or define("NEWCOMMENT_SEEN_USER_HASH", "newcomment_seen_user_hash:{#ucid}");//用户看过的影片hash,大k:ucid,小k,movieId,值:空或者是json后的信息【弃用】

defined("NEWCOMMENT_FAVOR_USER_LIST") or define("NEWCOMMENT_FAVOR_USER_LIST", "newcomment_favor_user_list_comment:{#commentId}");//评论点赞的用户
defined("NEWCOMMENT_FAVOR_USER_LIST_COUNT") or define("NEWCOMMENT_FAVOR_USER_LIST_COUNT", "newcomment_favor_user_list_count_comment:{#commentId}");//评论点赞的用户--总数
defined("NEWCOMMENT_FAVOR_COMMENT_LIST") or define("NEWCOMMENT_FAVOR_COMMENT_LIST", "newcomment_favor_comment_list_user:{#ucid}");//用户点赞的评论【弃用】
defined("NEWCOMMENT_FAVOR_COMMENT_LIST_COUNT") or define("NEWCOMMENT_FAVOR_COMMENT_LIST_COUNT", "newcomment_favor_comment_list_count_user:{#ucid}");//用户点赞的评论--总数【弃用】
defined("DELETE_ORDER_KEY") or define("DELETE_ORDER_KEY", "del_order"); //删除订单合集
defined("NEWCOMMENT_FAVOR_USER_HASH") or define("NEWCOMMENT_FAVOR_USER_HASH", "newcomment_favor_user_hash:{#ucid}");//用户赞过的评论hash【弃用】
defined("NEWCOMMENT_COMMENT_FAVOR_CHANGE_SET") or define("NEWCOMMENT_COMMENT_FAVOR_CHANGE_SET", "newcomment_comment_favor_change_set");//评论的点赞的改变集合

//redis配置处需要用到的配置
defined("FILM_FESTIVAL_DATA") or define("FILM_FESTIVAL_DATA", "film_festival_data"); //电影节影片数据
defined("FILM_FESTIVAL_MOVIES_DATA") or define("FILM_FESTIVAL_MOVIES_DATA", "film_festival_movies_data"); //电影节影片数据
defined("FILM_FESTIVAL_CINEMAS_DATA") or define("FILM_FESTIVAL_CINEMAS_DATA", "film_festival_cinemas_data"); //电影节影院数据
defined("FILM_FESTIVAL_ALL_MOVIE_IDS") or define("FILM_FESTIVAL_ALL_MOVIE_IDS", "film_festival_all_movie_ids"); //电影节所有影片id
defined("FILM_FESTIVAL_ALL_MOVIE_INFO") or define("FILM_FESTIVAL_ALL_MOVIE_INFO", "film_festival_all_movie_info"); //电影节所有影片信息，hash结构
defined("FILM_FESTIVAL_USER_WANT_LIST") or define("FILM_FESTIVAL_USER_WANT_LIST", "film_festival_user_want_list_"); //电影节用户清单，集合

//优惠降级开关
defined("BIZ_SWITCH") or define("BIZ_SWITCH", false); //优惠降级开关(开启后无法使用选坐券红包,调起支付原价吊起)
//观影轨迹redis
defined("USER_TRACE") or define("USER_TRACE", "user_trace"); //观影轨迹REDIS名称
defined("USER_TRACE_ORDER_BUY_MOVIES") or define("USER_TRACE_ORDER_BUY_MOVIES", "buyMovies"); //用户订单中已购票(观影)影片列表
defined("USER_TRACE_UPDATE") or define("USER_TRACE_UPDATE", "updated"); //用户观影轨迹更新时间

//场次剩余座位
defined("TICKET_LEFT_OVER") or define("TICKET_LEFT_OVER", "ticket_left_over"); //剩余座位
defined("TICKET_LEFT_OVER_NONE") or define("TICKET_LEFT_OVER_NONE", 2); //剩余座位售罄
defined("TICKET_LEFT_OVER_MIN") or define("TICKET_LEFT_OVER_MIN", 1); //剩余座位紧张
defined("TICKET_LEFT_OVER_MAX") or define("TICKET_LEFT_OVER_MAX", 0); //剩余座位充裕
//报名活动相关缓存
defined('KEY_APPLY_ACTIVE_DATA') or define('KEY_APPLY_ACTIVE_DATA', 'apply_active_data_');//报名活动
defined('KEY_APPLY_USER_SET') or define('KEY_APPLY_USER_SET', 'apply_user_set_');//报名用户的集合


//滑动验证码
defined("SLIDE_ID") or define("SLIDE_ID", "slide_id:{#slideId}"); //滑动验证标识
defined("SLIDE_CREDENTIAL") or define("SLIDE_CREDENTIAL", "slide_credential:{#slideId}"); //滑动验证的加密串

//CMS-发现-活动-资讯相关key
defined("CMS_FIND_LIST") or define("CMS_FIND_LIST", 'cms_find_list_'); //发现列表有序集合key
defined("CMS_FIND_INFO") or define("CMS_FIND_INFO", 'cms_find_info_'); //发现内容k->v str
defined("CMS_CMS_INFO") or define("CMS_CMS_INFO", 'cms_cms_info_'); //发现内容k->v str
defined("CMS_LIKE_LIST") or define("CMS_LIKE_LIST", 'cms_like_list_'); //内容-喜欢的用户集合 有序集合key
defined("CMS_NEWS_LIST") or define("CMS_NEWS_LIST", 'cms_news_list_'); //资讯列表有序集合key
defined("CMS_FIND_OTHER") or define("CMS_FIND_OTHER", 'cms_find_other_'); //发现-每个分类下的其他内容;
defined("CMS_FIND_TYPE_LIST") or define("CMS_FIND_TYPE_LIST", 'cms_find_list_channel_'); //有序集合，各渠道各分类下的集合【区分分类，指定渠道指定分类下】

//CMS评论
defined("CMS_COMMENT_HOT_LIST") or define("CMS_COMMENT_HOT_LIST", 'cms_comment_hot_list:{#a_id}'); //热门评论列表
defined("CMS_COMMENT_HOT_LIST_COUNT") or define("CMS_COMMENT_HOT_LIST_COUNT", 'cms_comment_hot_list_count:{#a_id}'); //热门评论列表--总数
defined("CMS_COMMENT_NEW_LIST") or define("CMS_COMMENT_NEW_LIST", 'cms_comment_new_list:{#a_id}');//最近评论列表
defined("CMS_COMMENT_NEW_LIST_COUNT") or define("CMS_COMMENT_NEW_LIST_COUNT", 'cms_comment_new_list_count:{#a_id}');//最近评论列表--总数
defined("CMS_COMMENT_CONTENT") or define("CMS_COMMENT_CONTENT", 'cms_comment_content:{#comment_id}');//评论内容
defined("CMS_COMMENT_TIMESPACING") or define("CMS_COMMENT_TIMESPACING", 'cms_comment_timespacing:{#open_id}');//评论间隔
defined("CMS_COMMENT_USER_FAVOR_LIST") or define("CMS_COMMENT_USER_FAVOR_LIST", 'cms_comment_user_favor_list:{#open_id}');//用户的点赞评论
defined("CMS_COMMENT_USER_FAVOR_LIST_COUNT") or define("CMS_COMMENT_USER_FAVOR_LIST_COUNT", 'cms_comment_user_favor_list_count:{#open_id}');//用户的点赞评论
defined("CMS_COMMENT_FAVOR_REALNUM") or define("CMS_COMMENT_FAVOR_REALNUM", "cms_comment_favor_realnum:{#commentId}");//评论的真实点赞数
defined("CMS_COMMENT_CHANAGE_SET") or define("CMS_COMMENT_CHANAGE_SET", "cms_comment_chanage_set");//有过点赞改变的评论的id,集合类型


//观影秘笈
defined("MOVIE_GUIDE_ITEM") or define("MOVIE_GUIDE_ITEM", 'movie_guide_item:'); //观影秘籍key
defined("MOVIE_GUIDE_USER_LIST") or define("MOVIE_GUIDE_USER_LIST", 'movie_guide_user_list_'); //用户领取观影秘籍列表
defined("MOVIE_GUIDE_USER_LIST_EXPIRE") or define("MOVIE_GUIDE_USER_LIST_EXPIRE", 604800); //用户领取观影秘籍列表时效
defined("MOVIE_GUIDE_TAKEN_USER") or define("MOVIE_GUIDE_TAKEN_USER", 'guide_user_taken_'); //某观影秘籍已领取用户列表
defined("MOVIE_GUIDE_PV") or define("MOVIE_GUIDE_PV", 'guide_user_PV_'); //观影秘籍PV的HASH
defined("KEY_MOVIE_GUIDE_LIST") or define("KEY_MOVIE_GUIDE_LIST", 'movie_guide_list'); //观影秘笈项目列表


//qq评论发往qq空间开关s
defined("PUSH_COMMENT_TO_QQ") or define("PUSH_COMMENT_TO_QQ", false); //qq评论发往qq空间开关

//红点导流相关
defined("RED_SPOT_CHANNELS") or define("RED_SPOT_CHANNELS", 'redspot_info_channels'); //红点渠道
defined("RED_SPOT_USER") or define("RED_SPOT_USER", 'redspot_'); //是否给此用户显示红点
defined("RED_SPOT_ID") or define("RED_SPOT_ID", 'redspot_%s_id'); //获取红点ID

//搜索推荐
defined("SEARCH_RECOMMEND") or define("SEARCH_RECOMMEND", 'search_rec');

//影片片单
defined("KEY_FILM_LIST_ITEM") or define("KEY_FILM_LIST_ITEM", 'film_list_item');
defined("KEY_FILM_LIST_COUNT") or define("KEY_FILM_LIST_COUNT", 'film_list_item_count');
//人气top20片单
defined("KEY_FILM_LIST_TOP20") or define("KEY_FILM_LIST_TOP20", 'film_list_top20');
//影片归属的片单
defined("KEY_FILM_LIST_MOVIE") or define("KEY_FILM_LIST_MOVIE", 'film_list_movie:');
//片单所属的用户列表
defined("KEY_FILM_LIST_USERS") or define("KEY_FILM_LIST_USERS", 'film_list_users:');
defined("KEY_FILM_LIST_USER") or define("KEY_FILM_LIST_USER", 'film_list_user:');
defined("KEY_FILM_LIST_ALL") or define("KEY_FILM_LIST_ALL", 'film_list_all');

//短信网关域名
defined('SDK_SMS_APIHOST') or define('SDK_SMS_APIHOST', 'http://sms.grammy.wxmovie.com:80');
//短信网关发送短信接口
defined('SDK_SMS_SEND') or define('SDK_SMS_SEND', SDK_SMS_APIHOST . '/sms/api/send');   //接口地址

//小程序首页红包
defined('BONUS_STATUS') or define('BONUS_STATUS', 'bonus_status');

/*
 * 手Q相关
 */
//手q个人中心推荐位
defined("MQQ_RECOMMEND_WILL") or define("MQQ_RECOMMEND_WILL", "mqq_recommend_will");
//手Q发现文章3,4之间推荐位
defined("MQQ_RECOMMEND_FIND") or define("MQQ_RECOMMEND_FIND", "mqq_recommend_find");
//明星问候相关
defined('STAR_GREETING_INFO') or define('STAR_GREETING_INFO', 'greeting_info:{#greetingId}');//明星问候缓存
defined('STAR_GREETING_ONLINE_ID') or define('STAR_GREETING_ONLINE_ID', 'greeting_online_id:{#channelId}');//线上明星问候id

//app静态数据
defined('APP_CACHE_DATA') or define('APP_CACHE_DATA', 'app_cache_data');

//观影轨迹CACHE
defined('USER_CACHE_TRACE') or define('USER_CACHE_TRACE', 'user_cache_trace');

defined('USER_DB_APP') or define('USER_DB_APP', 'user_db_');


//明星选坐redis
defined('APP_CUSTOMIZATION_SEAT') or define('APP_CUSTOMIZATION_SEAT', 'app_customization_seat');

//日签的KEY
defined('KEY_DAY_SIGN_PAGING_CALENDAR') or define('KEY_DAY_SIGN_PAGING_CALENDAR', 'day_sign_calendar');//日签的月历
defined('KEY_DAY_SIGN_PAGING_MONTH') or define('KEY_DAY_SIGN_PAGING_MONTH', 'day_sign_month_');//某个月的日签

defined('DAY_SIGN_PAGING') or define('DAY_SIGN_PAGING', 'day_sign_paging');
//首页自定义图标redis
defined('APP_ICON_CONFIG') or define('APP_ICON_CONFIG', 'app_icon_config');
//影片商业化详情列表
defined('MOVIE_INFO_BIZ') or define('MOVIE_INFO_BIZ', 'movie_info_biz');
//影片商业化详情列表
defined('APP_MODULE_SWITCH') or define('APP_MODULE_SWITCH', 'app_module_switch');

//APP热修复补丁
defined("KEY_JSPATCH_ITEM") or define("KEY_JSPATCH_ITEM", 'jspatch_item');
defined('KEY_TINKER_ITEM') or define('KEY_TINKER_ITEM', 'tinker_item');
//资源数据
defined('RESOURCE_STATIC') or define('RESOURCE_STATIC', 'resource_static');
//三端底部icon图标redis key
defined('ICON_CONFIG') or define('ICON_CONFIG', 'icon_config_{#channelId}');
//app可退票影院维护
defined('APP_REFUND_CINEMA') or define('APP_REFUND_CINEMA', 'app_refund_cinema');

defined('APP_HOMEPAGE_RECOMMEND') or define('APP_HOMEPAGE_RECOMMEND', 'app_home_recommend_info');
defined('STATIC_APP_HOME_RECOMMEND_RANGE') or define('STATIC_APP_HOME_RECOMMEND_RANGE', 'app_home_recommend_range');

//新版静态数据常量
defined('STATIC_NEW_MOVIE_SCORE') or define('STATIC_NEW_MOVIE_SCORE', 'static_new_movie_score_');    //影片评分(20份)
defined('STATIC_NEW_MOVIE_CUSTOM_SEATS_INFO') or define('STATIC_NEW_MOVIE_CUSTOM_SEATS_INFO', 'static_new_movie_custom_seats_info_');    //明星选座数据(20份)
defined('STATIC_NEW_NOTIFICATION_DATA') or define('STATIC_NEW_NOTIFICATION_DATA', 'static_new_notification_data_');    //公告数据(20份)
defined('STATIC_NEW_MOVIE_SORT_CONDITION') or define('STATIC_NEW_MOVIE_SORT_CONDITION', 'static_new_movie_sort_condition');    //影片排序条件
defined('STATIC_NEW_MOVIE_RECOMMENDED_INFO') or define('STATIC_NEW_MOVIE_RECOMMENDED_INFO', 'static_new_movie_recommended_info');    //单片推荐数据
defined('STATIC_NEW_CINEMA_HALL_FAVOR_COUNT') or define('STATIC_NEW_CINEMA_HALL_FAVOR_COUNT', 'static_new_cinema_hall_favor_count_');    //影院影厅评价数量
defined('STATIC_NEW_BANK_PRIVILEGE_DATA') or define('STATIC_NEW_BANK_PRIVILEGE_DATA', 'static_new_bank_privilege_data_');    //影片评分(20份)
defined('STATIC_NEW_MOVIE_DATA_INFO') or define('STATIC_NEW_MOVIE_DATA_INFO', 'static_new_movie_data_info_');    //单片推荐数据
defined('STATIC_NEW_KEY_CITY_MOVIE_LIST_SORT') or define('STATIC_NEW_KEY_CITY_MOVIE_LIST_SORT', 'static_new_city_movie_list_sort_'); //在映影片之影片id的有序集合
defined('STATIC_NEW_KEY_CITY_MOVIE_LIST_HASH') or define('STATIC_NEW_KEY_CITY_MOVIE_LIST_HASH', 'static_new_city_movie_list_hash_'); //在映影片之影片信息的哈希结构
defined('STATIC_NEW_KEY_MOVIE_CITY_SCHE_FLAG') or define('STATIC_NEW_KEY_MOVIE_CITY_SCHE_FLAG', 'static_new_movie_city_sche_flag_'); //影片在映标识
defined('STATIC_NEW_MOVIE_CMS_NEWS_LIST') or define('STATIC_NEW_MOVIE_CMS_NEWS_LIST', 'static_new_movie_cms_news_list'); //影片cms资讯
defined('STATIC_NEW_CITY_MOVIE_WILL') or define('STATIC_NEW_CITY_MOVIE_WILL', 'static_new_city_movie_will_'); //渠道所用的即将上映
defined('STATIC_NEW_CITY_MOVIE_WILL_WITH_DATE_DATA') or define('STATIC_NEW_CITY_MOVIE_WILL_WITH_DATE_DATA', 'static_new_city_movie_will_with_date_data_'); //微信渠道即将上映数据
defined('STATIC_NEW_MOVIE_WILL_WITH_DATE_DIMENSION') or define('STATIC_NEW_MOVIE_WILL_WITH_DATE_DIMENSION', 'static_new_city_movie_will_with_date_demension');
defined('STATIC_NEW_CINEMA_SCHE_MOVIE_DATA_SET') or define('STATIC_NEW_CINEMA_SCHE_MOVIE_DATA_SET', 'static_new_cinema_sche_movie_data_set_'); //排期中需要的影片数据集合
defined('STATIC_NEW_CITY_MOVIE_LIST_V1') or define('STATIC_NEW_CITY_MOVIE_LIST_V1', 'static_new_city_movie_list_v1_');//在映影片
defined('STATIC_NEW_CITY_MOVIE_LIST_V2') or define('STATIC_NEW_CITY_MOVIE_LIST_V2', 'static_new_city_movie_list_v2_');//在映影片
defined('STATIC_NEW_CITY_MOVIE_LIST_V3') or define('STATIC_NEW_CITY_MOVIE_LIST_V3', 'static_new_city_movie_list_v3_');//在映影片

defined('FIND_NEW_CACHE_STATUS') or define('FIND_NEW_CACHE_STATUS', 1);//新版发现列表--是否启用基于缓存的缓存，1：是，0：不启用
defined('FIND_INFOMATION_CACHE_STATUS') or define('FIND_INFOMATION_CACHE_STATUS', 1);//启用专题缓存，1：是，0：不启用

defined('STATIC_NEW_MSDB_VIDEOS') or define('STATIC_NEW_MSDB_VIDEOS', 'static_new_videos_');   //msdb预告片列表
defined('STATIC_NEW_MOVIE_SPOT_FILM') or define('STATIC_NEW_MOVIE_SPOT_FILM', 'static_new_movie_spot_film_');//点映标识

//App领取红包
defined('APP_RED_PACKET_DATA_KEY') or define('APP_RED_PACKET_DATA_KEY', 'app_red_packet_data');
defined('MOVIE_GIFT_CARD_ENTRY') or define('MOVIE_GIFT_CARD_ENTRY', 'movie_gift_card_entry');//微信礼品卡

defined('STATIC_NEW_MOVIE_WILL_PREVIEW') or define('STATIC_NEW_MOVIE_WILL_PREVIEW', 'static_new_movie_will_preview_');

defined('STATIC_NEW_HOT_MOVIE_IDS') or define('STATIC_NEW_HOT_MOVIE_IDS', 'static_new_hot_movie_ids');    //上映影片的ids

//小程序Emoji
defined('XIAOCHENGXU_PROBLEM_REPO_NAME') or define('XIAOCHENGXU_PROBLEM_REPO_NAME', 'xcx_problem_repo_name_');//题库名数据
defined('XIAOCHENGXU_PROBLEM_REPO_ITEM') or define('XIAOCHENGXU_PROBLEM_REPO_ITEM', 'xcx_problem_repo_item_');//题库题目数据
defined('XIAOCHENGXU_PROBLEM_ITEM') or define('XIAOCHENGXU_PROBLEM_ITEM', 'xcx_problem_item_');//题目数据
defined('XIAOCHENGXU_USER_RANDOM_REPO_ITEM') or define('XIAOCHENGXU_USER_RANDOM_REPO_ITEM', 'xcx_pbuser_');//用户随机题库（缩写，担心太长，redis存不下）
defined('XIAOCHENGXU_USER_CRACK_ITEM') or define('XIAOCHENGXU_USER_CRACK_ITEM', 'xcx_user_crack_');//用户已答对的题记录