<?php

//通用配置

//用户中心(靳松)
defined('JAVA_ADDR_USERCENTER_WX') or define('JAVA_ADDR_USERCENTER_WX', 'http://10.66.154.209:80');
defined('JAVA_ADDR_USERCENTER_CHANNEL') or define('JAVA_ADDR_USERCENTER_CHANNEL', 'http://10.66.154.209:80');
defined('JAVA_ADDR_USERCENTER_UCOPEN') or define('JAVA_ADDR_USERCENTER_UCOPEN', 'http://10.66.106.195:80');
//defined('JAVA_ADDR_INTRA_USERCENTER') or define('JAVA_ADDR_INTRA_USERCENTER', 'http://10.66.154.209:80');

//商品中心(齐磊)
defined('JAVA_ADDR_TICKET_WX') or define('JAVA_ADDR_TICKET_WX', 'http://192.168.101.51:8080');
defined('JAVA_ADDR_TICKET_CHANNEL') or define('JAVA_ADDR_TICKET_CHANNEL', 'http://192.168.101.51:8080');
defined('JAVA_ADDR_SCHEDULE') or define('JAVA_ADDR_SCHEDULE', 'http://10.66.139.148:80');

//营销相关——红包(郭朝伟)
defined('JAVA_ADDR_BONUS_WX') or define('JAVA_ADDR_BONUS_WX', 'http://10.66.149.229:80');
defined('JAVA_ADDR_BONUS_CHANNEL') or define('JAVA_ADDR_BONUS_CHANNEL', 'http://10.66.149.229:80');
//营销相关——兑换券(郭朝伟)
defined('JAVA_ADDR_EXCHANGE_WX') or define('JAVA_ADDR_EXCHANGE_WX', 'http://10.66.100.90:80');
defined('JAVA_ADDR_EXCHANGE_CHANNEL') or define('JAVA_ADDR_EXCHANGE_CHANNEL', 'http://10.66.100.90:80');
//营销相关——礼品卡(郭朝伟)
defined('JAVA_ADDR_GIFT_CARD_WX') or define('JAVA_ADDR_GIFT_CARD_WX', 'http://10.66.149.229:80');
defined('JAVA_ADDR_GIFT_CARD_CHANNEL') or define('JAVA_ADDR_GIFT_CARD_CHANNEL', 'http://10.66.149.229:80');
//排期接口（郭朝伟）
defined('JAVA_ADDR_SCHE_WX') or define('JAVA_ADDR_SCHE_WX', 'http://10.66.139.144:80');
defined('JAVA_ADDR_SCHE_CHANNEL') or define('JAVA_ADDR_SCHE_CHANNEL', 'http://10.66.139.144:80');

//订单相关(童书键)
defined('JAVA_ADDR_ORDER_WX') or define('JAVA_ADDR_ORDER_WX', 'http://order-center-gray.wepiao.com');
defined('JAVA_ADDR_ORDER_CHANNEL') or define('JAVA_ADDR_ORDER_CHANNEL', 'http://order-center-gray.wepiao.com');
defined('JAVA_WATCH_SAME_MOVIES') or define('JAVA_WATCH_SAME_MOVIES', 'http://10.3.12.132:2019/v2/usertag/movie/getCommonMoives');//共同看过的电影

//
defined('JAVA_ADDR_BIG_DATA_SEARCH_WX') or define('JAVA_ADDR_BIG_DATA_SEARCH_WX', 'http://search.grammy.wxmovie.com:9200');
defined('JAVA_ADDR_BIG_DATA_SEARCH_CHANNEL') or define('JAVA_ADDR_BIG_DATA_SEARCH_CHANNEL', 'http://search.grammy.wxmovie.com:9200');
//大数据-明星配对
defined('JAVA_ADDR_BIG_DATA_STAR_PAIR') or define('JAVA_ADDR_BIG_DATA_STAR_PAIR', 'http://searchcollect.intra.wepiao.com/star/pairing');
defined('JAVA_ADDR_BIG_DATA_STAR_PAIR_PV') or define('JAVA_ADDR_BIG_DATA_STAR_PAIR_PV', 'http://searchcollect.intra.wepiao.com/star/pv');
defined('PEOMOTION_CDN_URL') or define('PEOMOTION_CDN_URL', "https://promotion.wepiao.com");//promotion地址
//媒资库接口（刘龙）//预上线接口
defined('MOVIE_DATABASES_WX') or define('MOVIE_DATABASES_WX', 'http://10.66.148.179');
defined('MOVIE_DATABASES_CHANNEL') or define('MOVIE_DATABASES_CHANNEL', 'http://10.66.148.179');
//ES影片搜索接口,不用区分渠道，媒资库
defined('JAVA_ES_MOVIE_DATABASES_SEARCH') or define('JAVA_ES_MOVIE_DATABASES_SEARCH', 'http://search.grammy.wxmovie.com:9200');
//新版ES搜索地址-影片搜索和影院搜索都可以使用(苏利军)
defined('JAVA_ES_SEARCH_DATABASES') or define('JAVA_ES_SEARCH_DATABASES', 'http://searchcminfo.intra.wepiao.com');
//新综合搜索(苏利军)包含影片、影院、排期,因为是根据端口号来区分的，所以就直接写在这了
defined('JAVA_ES_SEARCH_MORE') or define('JAVA_ES_SEARCH_MORE', 'http://searchintegrate.intra.wepiao.com/v2/search/integrate');

//格瓦拉 影片维度影院搜索排序接口
defined('JAVA_ES_SEARCH_CINEMASMOV') or define('JAVA_ES_SEARCH_CINEMASMOV', 'http://searchcinema-sort.intra.wepiao.com/gewara/search/cinemasmov_sort');
//卖品（齐磊）
defined('JAVA_ADDR_SNACK_WX') or define('JAVA_ADDR_SNACK_WX', 'http://goods-snack.wepiao.com');
defined('JAVA_ADDR_SNACK_CHANNEL') or define('JAVA_ADDR_SNACK_CHANNEL', 'http://goods-snack.wepiao.com');
//商品中心不可售和自有库存座位接口
defined('JAVA_UNSOLD_SEATS_AND_LOCAL_SEATS_WX') or define('JAVA_UNSOLD_SEATS_AND_LOCAL_SEATS_WX', 'http://goodscenter.grammy.wxmovie.com:80');
defined('JAVA_UNSOLD_SEATS_AND_LOCAL_SEATS_CHANNEL') or define('JAVA_UNSOLD_SEATS_AND_LOCAL_SEATS_CHANNEL', 'http://goodscenter.grammy.wxmovie.com:80');

//靳松-用户中心-标签系统
defined('JAVA_USERCENTER_TAG_GET') or define('JAVA_USERCENTER_TAG_GET', 'http://10.66.106.195/ucopen/v1/getstatictag');

//调用腾讯云的敏感词接口
defined('TX_KEY_WORD') or define('TX_KEY_WORD', "http://10.3.40.23/kw.php");

//大数据影院列表接口
defined('JAVA_ADDR_BIG_DATA_CINEMA_LIST_SEARCH_WX') or define('JAVA_ADDR_BIG_DATA_CINEMA_LIST_SEARCH_WX', "http://searchcinema-sort.intra.wepiao.com");
defined('JAVA_ADDR_BIG_DATA_CINEMA_LIST_SEARCH_CHANNEL') or define('JAVA_ADDR_BIG_DATA_CINEMA_LIST_SEARCH_CHANNEL', "http://searchcinema-sort.intra.wepiao.com");

//营销相关——卖品优惠(郭朝伟)
defined('JAVA_ADDR_SNACK_DISCOUNT_WX') or define('JAVA_ADDR_SNACK_DISCOUNT_WX', 'http://promotion-yx-gray.wepiao.com');
defined('JAVA_ADDR_SNACK_DISCOUNT_CHANNEL') or define('JAVA_ADDR_SNACK_DISCOUNT_CHANNEL', 'http://promotion-yx-gray.wepiao.com');

//会员卡(杨伟平)
defined('JAVA_ADDR_CINEMA_VIP_WX') or define('JAVA_ADDR_CINEMA_VIP_WX', "http://membercard.wp.wepiao.com");
defined('JAVA_ADDR_CINEMA_VIP_CHANNEL') or define('JAVA_ADDR_CINEMA_VIP_CHANNEL', "http://membercard.wp.wepiao.com");

//大数据影院列搜索(苏利军,灰度地址暂定)
defined('JAVA_ADDR_BIG_DATA_CINEMA_INFO_SEARCH_WX') or define('JAVA_ADDR_BIG_DATA_CINEMA_INFO_SEARCH_WX', "http://searchcminfo.intra.wepiao.com");
defined('JAVA_ADDR_BIG_DATA_CINEMA_INFO_SEARCH_CHANNEL') or define('JAVA_ADDR_BIG_DATA_CINEMA_INFO_SEARCH_CHANNEL', "http://searchcminfo.intra.wepiao.com");

//商品中心——数据中心(齐磊)
defined('JAVA_ADDR_CORE_DATA_WX') or define('JAVA_ADDR_CORE_DATA_WX', "http://10.66.201.177:80");
defined('JAVA_ADDR_CORE_DATA_CHANNEL') or define('JAVA_ADDR_CORE_DATA_CHANNEL', "http://10.66.201.177:80");

//大数据——防作弊（施海彦）
defined('JAVA_BIG_DATA_ANTI_CHEATING_WX') or define('JAVA_BIG_DATA_ANTI_CHEATING_WX', "http://10.66.193.78:2003");
defined('JAVA_BIG_DATA_ANTI_CHEATING_CHANNEL') or define('JAVA_BIG_DATA_ANTI_CHEATING_CHANNEL', "http://10.66.193.78:2003");

//格瓦拉支付(佟书建)
defined('JAVA_PAY_GEWARA') or define('JAVA_PAY_GEWARA', "http://10.251.68.115:8501");
defined('JAVA_PAY_GEWARA_BUSINESS_KEY') or define('JAVA_PAY_GEWARA_BUSINESS_KEY','7wtQbiHT34rZ8i97YxN4ZkZ1LZhWzqKb');
//银河  商业化接口
defined("SYH_PHP_BONUS_URL_API") or define("SYH_PHP_BONUS_URL_API", 'api.intra.wepiao.com');

//判断取用何种配置,如果是电影票,用WX后缀的配置, 如果是渠道,用 CHANNEL 后缀的配置
$channelId = !empty(\wepiao::$input['channelId']) ? \wepiao::$input['channelId'] : DEFAULT_REDIS_CHANNEL_ID;
if (in_array(intval($channelId), [3, 28, 8, 9, 6])) {
    defined('JAVA_ADDR_USERCENTER') or define('JAVA_ADDR_USERCENTER', JAVA_ADDR_USERCENTER_WX);
    defined('JAVA_ADDR_BONUS') or define('JAVA_ADDR_BONUS', JAVA_ADDR_BONUS_WX);
    defined('JAVA_ADDR_ORDER') or define('JAVA_ADDR_ORDER', JAVA_ADDR_ORDER_WX);
    defined('JAVA_ADDR_TICKET') or define('JAVA_ADDR_TICKET', JAVA_ADDR_TICKET_WX);
    defined('JAVA_ADDR_EXCHANGE') or define('JAVA_ADDR_EXCHANGE', JAVA_ADDR_EXCHANGE_WX);
    defined('JAVA_ADDR_GIFT_CARD') or define('JAVA_ADDR_GIFT_CARD', JAVA_ADDR_GIFT_CARD_WX);
    defined('JAVA_ADDR_BIG_DATA_SEARCH') or define('JAVA_ADDR_BIG_DATA_SEARCH', JAVA_ADDR_BIG_DATA_SEARCH_WX);
    defined('MOVIE_DATABASES') or define('MOVIE_DATABASES', MOVIE_DATABASES_WX);
    defined('JAVA_ADDR_SNACK') or define('JAVA_ADDR_SNACK', JAVA_ADDR_SNACK_WX);
    defined('JAVA_ADDR_UNSOLDED_SEATS_LOCAL_SEATS') or define('JAVA_ADDR_UNSOLDED_SEATS_LOCAL_SEATS', JAVA_UNSOLD_SEATS_AND_LOCAL_SEATS_WX);
    defined('JAVA_ADDR_BIG_DATA_CINEMA_LIST_SEARCH') or define('JAVA_ADDR_BIG_DATA_CINEMA_LIST_SEARCH', JAVA_ADDR_BIG_DATA_CINEMA_LIST_SEARCH_WX);
    defined('JAVA_ADDR_SNACK_DISCOUNT') or define('JAVA_ADDR_SNACK_DISCOUNT', JAVA_ADDR_SNACK_DISCOUNT_WX);
    defined('JAVA_ADDR_CINEMA_VIP') or define('JAVA_ADDR_CINEMA_VIP', JAVA_ADDR_CINEMA_VIP_WX);
    defined('JAVA_ADDR_BIG_DATA_CINEMA_INFO_SEARCH') or define('JAVA_ADDR_BIG_DATA_CINEMA_INFO_SEARCH', JAVA_ADDR_BIG_DATA_CINEMA_INFO_SEARCH_WX);
    defined('JAVA_ADDR_CORE_DATA') or define('JAVA_ADDR_CORE_DATA', JAVA_ADDR_CORE_DATA_WX);
    defined('JAVA_ADDR_SCHE') or define('JAVA_ADDR_SCHE', JAVA_ADDR_SCHE_WX);
    defined('JAVA_BIG_DATA_ANTI_CHEATING') or define('JAVA_BIG_DATA_ANTI_CHEATING', JAVA_BIG_DATA_ANTI_CHEATING_WX);
}
else {
    //定义用户中心
    defined('JAVA_ADDR_USERCENTER') or define('JAVA_ADDR_USERCENTER', JAVA_ADDR_USERCENTER_CHANNEL);
    defined('JAVA_ADDR_BONUS') or define('JAVA_ADDR_BONUS', JAVA_ADDR_BONUS_CHANNEL);
    defined('JAVA_ADDR_ORDER') or define('JAVA_ADDR_ORDER', JAVA_ADDR_ORDER_CHANNEL);
    defined('JAVA_ADDR_TICKET') or define('JAVA_ADDR_TICKET', JAVA_ADDR_TICKET_CHANNEL);
    defined('JAVA_ADDR_EXCHANGE') or define('JAVA_ADDR_EXCHANGE', JAVA_ADDR_EXCHANGE_CHANNEL);
    defined('JAVA_ADDR_GIFT_CARD') or define('JAVA_ADDR_GIFT_CARD', JAVA_ADDR_GIFT_CARD_CHANNEL);
    defined('JAVA_ADDR_BIG_DATA_SEARCH') or define('JAVA_ADDR_BIG_DATA_SEARCH', JAVA_ADDR_BIG_DATA_SEARCH_CHANNEL);
    defined('MOVIE_DATABASES') or define('MOVIE_DATABASES', MOVIE_DATABASES_CHANNEL);
    defined('JAVA_ADDR_SNACK') or define('JAVA_ADDR_SNACK', JAVA_ADDR_SNACK_CHANNEL);
    defined('JAVA_ADDR_UNSOLDED_SEATS_LOCAL_SEATS') or define('JAVA_ADDR_UNSOLDED_SEATS_LOCAL_SEATS', JAVA_UNSOLD_SEATS_AND_LOCAL_SEATS_CHANNEL);
    defined('JAVA_ADDR_BIG_DATA_CINEMA_LIST_SEARCH') or define('JAVA_ADDR_BIG_DATA_CINEMA_LIST_SEARCH', JAVA_ADDR_BIG_DATA_CINEMA_LIST_SEARCH_CHANNEL);
    defined('JAVA_ADDR_SNACK_DISCOUNT') or define('JAVA_ADDR_SNACK_DISCOUNT', JAVA_ADDR_SNACK_DISCOUNT_CHANNEL);
    defined('JAVA_ADDR_CINEMA_VIP') or define('JAVA_ADDR_CINEMA_VIP', JAVA_ADDR_CINEMA_VIP_CHANNEL);
    defined('JAVA_ADDR_BIG_DATA_CINEMA_INFO_SEARCH') or define('JAVA_ADDR_BIG_DATA_CINEMA_INFO_SEARCH', JAVA_ADDR_BIG_DATA_CINEMA_INFO_SEARCH_CHANNEL);
    defined('JAVA_ADDR_CORE_DATA') or define('JAVA_ADDR_CORE_DATA', JAVA_ADDR_CORE_DATA_CHANNEL);
    defined('JAVA_ADDR_SCHE') or define('JAVA_ADDR_SCHE', JAVA_ADDR_SCHE_CHANNEL);
    defined('JAVA_BIG_DATA_ANTI_CHEATING') or define('JAVA_BIG_DATA_ANTI_CHEATING', JAVA_BIG_DATA_ANTI_CHEATING_CHANNEL);
}

//滑动验证H5页面的url
defined('SLIDE_H5_URL') or define('SLIDE_H5_URL', "http://res.wxmovie.com/modules/captcha");
//判断当前运行环境
defined('SERVICE_ENV') or define('SERVICE_ENV', "gray");
//评论中心
defined('COMMENT_CENTER_URL') or define('COMMENT_CENTER_URL', 'http://comment.intra.wepiao.com');
//NFS地址
defined('NFS_HOST') or define('NFS_HOST', 'https://wxadminpre.wepiao.com/');


//大数据--查询openid对movieId是否购票过
defined("JAVA_ADDR_BIG_DATA_MOVIE_TICKET") or define("JAVA_ADDR_BIG_DATA_MOVIE_TICKET",
    'http://10.3.10.26/v1/usertag/movieticket');
//格瓦拉侧内部接口地址
defined('GEWARA_URL') or define('GEWARA_URL', 'http://gwtm.gewara.com');
