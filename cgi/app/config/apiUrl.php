<?php
//
//用户中心相关接口
//手机号注册 
defined('JAVA_API_REGISTER') or define('JAVA_API_REGISTER', JAVA_ADDR_USERCENTER . '/uc/v1/mobileregister');
//第三方帐号注册
defined('JAVA_API_OPENIDREGISTER') or define('JAVA_API_OPENIDREGISTER', JAVA_ADDR_USERCENTER . '/uc/v1/openidregister');
//用户登录
defined('JAVA_API_LOGIN') or define('JAVA_API_LOGIN', JAVA_ADDR_USERCENTER . '/uc/v1/login');
//修改用户信息
defined('JAVA_API_UPDATEUSERINFO') or define('JAVA_API_UPDATEUSERINFO', JAVA_ADDR_USERCENTER . '/uc/v1/updateuserinfo');
//绑定手机号
defined('JAVA_API_BINDMOBILENO') or define('JAVA_API_BINDMOBILENO', JAVA_ADDR_USERCENTER . '/uc/v1/bindmobileno');
//修改手机号
defined('JAVA_API_UPDATEMOBILENO') or define('JAVA_API_UPDATEMOBILENO', JAVA_ADDR_USERCENTER . '/uc/v1/updatemobileno');
//解绑第三方用户
defined('JAVA_API_UNBINDOPENID') or define('JAVA_API_UNBINDOPENID', JAVA_ADDR_USERCENTER . '/uc/v1/unbindopenid');
//用户密码修改
defined('JAVA_API_UPDATEPASSWORD') or define('JAVA_API_UPDATEPASSWORD', JAVA_ADDR_USERCENTER . '/uc/v1/updatepassword');
//通过UID查询用户
defined('JAVA_API_GETUSERINFOBYUID') or define('JAVA_API_GETUSERINFOBYUID', JAVA_ADDR_USERCENTER . '/uc/v1/getuserinfobyuid');
//通过OpenID查询用户
defined('JAVA_API_GETUSERINFOBYOPENID') or define('JAVA_API_GETUSERINFOBYOPENID', JAVA_ADDR_USERCENTER . '/uc/v1/getuserinfobyopenid');
//通过手机号查询用户
defined('JAVA_API_GETUSERINFOBYMOBILE') or define('JAVA_API_GETUSERINFOBYMOBILE', JAVA_ADDR_USERCENTER . '/uc/v1/getuserinfobymobile');
//通过手机号查询用户openid的集合
defined('JAVA_API_GETOPENIDLISTBYMOBILE') or define('JAVA_API_GETOPENIDLISTBYMOBILE', JAVA_ADDR_USERCENTER . '/uc/v1/getopenidlistbymobile');
//通过UID查询用户个人资料
defined('JAVA_API_GETUSERPROFILEBYUID') or define('JAVA_API_GETUSERPROFILEBYUID', JAVA_ADDR_USERCENTER . '/uc/v1/getuserprofilebyuid');
//通过openid或unionid查询用户个人资料
defined('JAVA_API_GETUSERPROFILEBYOPENID') or define('JAVA_API_GETUSERPROFILEBYOPENID', JAVA_ADDR_USERCENTER . '/uc/v1/getuserinfobyopenid');
//是否为新用户
defined('JAVA_API_CHECKNEWUSER') or define('JAVA_API_CHECKNEWUSER', JAVA_ADDR_USERCENTER_UCOPEN . '/ucopen/v1/getstatictag');
//获取黑名单列表
defined('JAVA_API_GETBLACKLIST') or define('JAVA_API_GETBLACKLIST', JAVA_ADDR_USERCENTER . '/uc/v1/getblacklist');
//将用户加入黑名单
defined('JAVA_API_ADDBLACKLIST') or define('JAVA_API_ADDBLACKLIST', JAVA_ADDR_USERCENTER . '/uc/v1/addblacklist');
//将用户移除黑名单
defined('JAVA_API_REMOVEBLACKLIST') or define('JAVA_API_REMOVEBLACKLIST', JAVA_ADDR_USERCENTER . '/uc/v1/removeblacklist');
//将影院地址收藏
defined('JAVA_API_ADDCINEMATOFAVORITES') or define('JAVA_API_ADDCINEMATOFAVORITES', JAVA_ADDR_USERCENTER . '/uc/v1/addcinematofavorites');
//将收藏的影院地址删除
defined('JAVA_API_REMOVECINEMATOFAVOITES') or define('JAVA_API_REMOVECINEMATOFAVOITES', JAVA_ADDR_USERCENTER . '/uc/v1/removecinematofavorites');
//获取收藏影院地址
defined('JAVA_API_LISTCINEMAFROMFAVORITES') or define('JAVA_API_LISTCINEMAFROMFAVORITES', JAVA_ADDR_USERCENTER . '/uc/v1/listcinemafromfavorites');
//加密，解密特定文本（目前仅用于手机号）
defined('JAVA_API_CODEC') or define('JAVA_API_CODEC', JAVA_ADDR_USERCENTER . '/uc/v1/codec');
//添加用户收货地址
defined('JAVA_API_ADDPDA') or define('JAVA_API_ADDPDA', JAVA_ADDR_USERCENTER . '/uc/v1/addpda');
//删除用户收货地址
defined('JAVA_API_REMOVEPDA') or define('JAVA_API_REMOVEPDA', JAVA_ADDR_USERCENTER . '/uc/v1/removepda');
//更改用户收货地址
defined('JAVA_API_UPDATEPDA') or define('JAVA_API_UPDATEPDA', JAVA_ADDR_USERCENTER . '/uc/v1/updatepda');
//获取某一用户的收货地址列表
defined('JAVA_API_GETPDABYUID') or define('JAVA_API_GETPDABYUID', JAVA_ADDR_USERCENTER . '/uc/v1/getpdabyuid');
//获取某一用户的收货地址数量
defined('JAVA_API_GETPDACOUNTBYUID') or define('JAVA_API_GETPDACOUNTBYUID', JAVA_ADDR_USERCENTER . '/uc/v1/getpdacountbyuid');
//获取某一用户的默认收货地址
defined('JAVA_API_GETDEFAULTPDABYUID') or define('JAVA_API_GETDEFAULTPDABYUID', JAVA_ADDR_USERCENTER . '/uc/v1/getdefaultpdabyuid');
//获取某一用户的某一条收货地址
defined('JAVA_API_GETPDABYID') or define('JAVA_API_GETPDABYID', JAVA_ADDR_USERCENTER . '/uc/v1/getpdabyid');
//评论使用：根据uid获取unionId或openid
defined('JAVA_API_GETUCIDBYUID') or define('JAVA_API_GETUCIDBYUID', JAVA_ADDR_USERCENTER . '/uc/v1/getopenidbyuid4dragon');
//评论使用：根据unionId或openid获取头像昵称
defined('JAVA_API_GETUCIDBYINFO') or define('JAVA_API_GETUCIDBYINFO', JAVA_ADDR_USERCENTER . '/uc/v1/getopenidinfo4dragon');
//评论使用：根据openid获取unionId
defined('JAVA_API_GETUCIDBYOPENID') or define('JAVA_API_GETUCIDBYOPENID', JAVA_ADDR_USERCENTER . '/uc/v1/getuniqueidbyopenid4dragon');
//通过OpenID查询用户最早注册时间
defined('JAVA_API_GETREGISTERTIME') or define('JAVA_API_GETREGISTERTIME', JAVA_ADDR_USERCENTER . '/uc/v1/getuserfirstregisttime');
//通过任一id查询关系树
defined('JAVA_API_GETIDRELATION') or define('JAVA_API_GETIDRELATION', JAVA_ADDR_USERCENTER . '/uc/v1/getidrelation');
//检测手机号码是否和当前设备的历史记录匹配
defined('JAVA_API_MOBILENODEVICE') or define('JAVA_API_MOBILENODEVICE', JAVA_ADDR_USERCENTER . '/uc/v1/isdevicechanged');
//给手机号添加可信设备
defined('JAVA_API_MOBILENOADDDEVICE') or define('JAVA_API_MOBILENOADDDEVICE', JAVA_ADDR_USERCENTER . '/uc/v1/adddevice');
//通过多个OPENID批量查询用户关系树
defined('JAVA_API_BATCHGETIDRELATION') or define('JAVA_API_BATCHGETIDRELATION', JAVA_ADDR_USERCENTER . '/uc/v1/batchgetidrelation');

//营销相关接口
define('JAVA_API_UNLOCK_BONUS', JAVA_ADDR_BONUS . '/back_bonus_presell'); //解锁座位
define('JAVA_API_QUERY_BONUS', JAVA_ADDR_BONUS . '/querybonus'); //解锁座位
define('JAVA_API_QUERY_GEWARA_BONUS', JAVA_ADDR_BONUS . '/qryDisListCnter'); //格瓦拉可用优惠
define('JAVA_API_QUERY_GEWARA_PAY_BONUS', JAVA_ADDR_BONUS . '/qryDisListPay'); //格瓦拉可用优惠
define('JAVA_API_EXCHANGE_GEWARA_CODE', JAVA_ADDR_BONUS . '/discountexcode'); //格瓦拉兑换码兑换
define('JAVA_API_GEWARA_VCARD_LIST', JAVA_ADDR_BONUS . '/giftcard/qrycard '); //格瓦拉查询V卡
define('JAVA_API_GEWARA_VCARD_ACTIVE', JAVA_ADDR_BONUS . '/giftcard/activatecard '); //格瓦拉v卡激活
define('JAVA_API_GEWARA_VCARD_INFO', JAVA_ADDR_BONUS . '/giftcard/qryonecard'); //格瓦拉v卡激活
define('JAVA_API_TASK_DISCOUNT', JAVA_ADDR_BONUS . '/snddismsg');//发送营销任务，优惠到人使用
define('JAVA_API_GET_DISCOUNT', JAVA_ADDR_BONUS . '/qryreducelim');//查询可用的优惠活动ID
define('JAVA_API_POINT_CARD', JAVA_ADDR_BONUS . '/qryPointCard');//查询可用的优惠活动ID
define('JAVA_API_GET_BONUS_COUNT', JAVA_ADDR_BONUS . '/qryValidDisCnt');//查询有效红包个数
define('JAVA_API_GET_BONUS', JAVA_ADDR_BONUS . '/getbonus');//领取或系统下发红包
define('JAVA_API_GET_SUIT_BONUS', JAVA_ADDR_BONUS . '/getbnssuit');//领取或系统下发套装红包
define('JAVA_API_SUIT_BONUS_INFO', JAVA_ADDR_BONUS . '/qrySuitInfo');//后台获取红包套装实时数据
define('JAVA_API_QUERY_SCHE', JAVA_ADDR_SCHE . '/qryScheInfo');//排期接口
define('JAVA_ADDR_SCHEDULE_EXT', JAVA_ADDR_SCHEDULE . '/schedule-ext/%s');//排期扩展属性接口
define('JAVA_API_QUERY_SCHE_CHANNEL', JAVA_ADDR_SCHE . '/channelQryScheInfo');//排期接口渠道版


/**
 * 座位相关
 */
//订座相关接口
define('JAVA_API_AVAILABLE', JAVA_ADDR_TICKET . '/api/seat/salableSeats');  //获取可售座位接口

define('JAVA_API_UNAVAILABLE', JAVA_ADDR_ORDER . '/seat/unSalesQuery');  //获取不可售座位接口（新版订单中心API）
//锁座
define('JAVA_API_LOCK_SEAT', JAVA_ADDR_ORDER . '/seat/lock');
//取消锁座接口（按订单号）
define('JAVA_API_UNLOCK_SEAT_BY_ORDER', JAVA_ADDR_ORDER . '/seat/unlockByOrder');
//锁座新版(支付订单改签)
define('JAVA_API_LOCK_SEAT_V1', JAVA_ADDR_ORDER . '/seat/v1/lock');

/**
 * 订单相关(直接接入的新订单中心)
 */
//解锁订单
define('JAVA_API_UNLOCK_BY_ORDER', JAVA_ADDR_ORDER . '/seat/unlockByOrder');   //解锁座位
//获取未支付订单
define('JAVA_API_UNPAY_ORDER', JAVA_ADDR_ORDER . '/order/unpaymentQuery');//
//获取未支付订单v2
define('JAVA_API_UNPAY_ORDER_MULTI', JAVA_ADDR_ORDER . '/order/unpaymentMultiQuery');
define('JAVA_API_UNPAY_ORDER_MULTI_V1', JAVA_ADDR_ORDER . '/order/v1/unpaymentMultiQuery');
//获取微信支付串
define('JAVA_API_PAY_ORDER_WEINXIN', JAVA_ADDR_ORDER . '/order/payment');
//获取财付通支付串
define('JAVA_API_PAY_ORDER_TENPAY', JAVA_ADDR_ORDER . '/order/tenpayment');
//获取支付宝支付串
define('JAVA_API_PAY_ORDER_ALIPAY', JAVA_ADDR_ORDER . '/order/alipayment');
//获取银联苹果支付token
define('JAVA_API_PAY_ORDER_APPLEPAY', JAVA_ADDR_ORDER . '/order/unionPayment');
//获取京东支付密钥
define('JAVA_API_PAY_ORDER_JDPAYMENT', JAVA_ADDR_ORDER . '/order/jdPayment');
//获取V1微信支付串
define('JAVA_API_PAY_ORDER_WEINXIN_V1', JAVA_ADDR_ORDER . '/order/v1/wxPayment');
//获取格瓦拉支付参数 [虽然JAVA侧在V1之下,但是本接口不支持小吃卖品]
define('JAVA_API_PAY_ORDER_GEWARA', JAVA_ADDR_ORDER . '/order/gwlPayment');
//全功能支付（微信, 支付宝, 财付通, 京东, 银联, 格瓦拉）
define('JAVA_API_PAY_ALL_TYPE', JAVA_ADDR_ORDER . '/order/v1/payment');

//全功能支付V2版本,支持改签（微信, 支付宝, 财付通, 京东, 银联, 格瓦拉）
define('JAVA_API_PAY_ALL_TYPE_V2', JAVA_ADDR_ORDER . '/order/v2/payment');
define('JAVA_API_QUERY_ORDER_MOBILE', JAVA_ADDR_ORDER . '/user/mobilenoquery');
//全支付V3(微信, 支付宝, 财付通, 京东, 银联, 格瓦拉(V2))(支持改签)
define('JAVA_API_PAY_ALL_TYPE_V3', JAVA_ADDR_ORDER . '/order/v3/payment');
//格瓦拉获取可用支付列表
define('JAVA_GEWARA_PAY_METHODS', JAVA_PAY_GEWARA . '/v2/gwlpay/payMethods');


//获取已支付订单
define('JAVA_API_QUERY_ORDER', JAVA_ADDR_ORDER . '/order/query');
define('JAVA_API_QUERY_ORDER_NEW', JAVA_ADDR_ORDER . '/order/orderList');

//根据订单编号, 获取订单详情
define('JAVA_API_QUERY_ORDER_BY_ID', JAVA_ADDR_ORDER . '/order/queryOrder');
define('JAVA_API_QUERY_ORDER_INFO_V1', JAVA_ADDR_ORDER . '/order/v1/orderInfo');
//退票接口
define('JAVA_API_REFUND_ORDER', JAVA_ADDR_ORDER . '/order/userRefund');
//电影节专用订单列表
define('JAVA_API_ORDER_LIST', JAVA_ADDR_ORDER . '/order/orderList');
define('JAVA_API_ORDER_LIST_V1', JAVA_ADDR_ORDER . '/order/v1/orderList');
define('JAVA_API_ORDER_INFO', JAVA_ADDR_ORDER . '/order/orderInfo');
define('JAVA_API_FILM_ORDER_LIST', JAVA_ADDR_ORDER . '/order/orderListFilmfest');
//删除订单
define('JAVA_API_DEL_ORDER', JAVA_ADDR_ORDER . '/order/orderDel');


/**
 * 兑换券业务
 */
//获取兑换券详情
define('JAVA_API_EXCHANGE_INFO', JAVA_ADDR_EXCHANGE . '/qryproduct');
define('JAVA_API_EXCHANGE_BUY', JAVA_ADDR_EXCHANGE . '/buyexchange');
define('JAVA_API_EXCHANGE_ORDER_LIST', JAVA_ADDR_ORDER . '/order/couponsquery');
define('JAVA_API_EXCHANGE_ORDER_INFO', JAVA_ADDR_ORDER . '/order/queryCoupon');

/**
 * 礼品卡业务
 */
define('JAVA_API_GIFT_CAED_QUERY_INFO', JAVA_ADDR_GIFT_CARD . '/giftcard/qrycard');

//其他
define('JAVA_API_GET_USER_MOBILE', JAVA_ADDR_ORDER . '/user/mobilenoquery');

/**
 * 搜索业务
 */
//搜索影院
define('JAVA_API_BIG_DATA_SEARCH_CINEMA', JAVA_ADDR_BIG_DATA_SEARCH . '/cinema/_search');
//影片搜索
define('JAVA_ES_MOVIE_DATABASES_SEARCH_INFO', JAVA_ES_MOVIE_DATABASES_SEARCH . '/movie/_search');
/**
 * 媒资库相关
 */
define('MOVIE_DATABASES_MOVIE_ACTOR', MOVIE_DATABASES . '/movie/movie-actor');
define('MOVIE_DATABASES_MOVIE_SEARCH', MOVIE_DATABASES . '/movie/search');

//卖品
define('JAVA_ADDR_SNACK_LIST', JAVA_ADDR_SNACK . '/api/snacks');
define('JAVA_API_UNSOLDED_SEATS_LOCAL_SEATS', JAVA_ADDR_UNSOLDED_SEATS_LOCAL_SEATS . '/seats/info');
define('JAVA_API_MERGED_SEATS', JAVA_ADDR_UNSOLDED_SEATS_LOCAL_SEATS . '/seats/mergeInfo');  //获取融合版座位图

//影院列表搜索（苏利军）
define('JAVA_API_SEARCH_CINEMA_LIST', JAVA_ADDR_BIG_DATA_CINEMA_LIST_SEARCH . '/search/cinemas_sort');  //影院列表
define('JAVA_API_SEARCH_MOVIE_CINEMA_LIST', JAVA_ADDR_BIG_DATA_CINEMA_LIST_SEARCH . '/search/cinemasmov_sort'); //某个影片下在映的影院列表
define('JAVA_API_SEARCH_CINEMA_FILTER', JAVA_ADDR_BIG_DATA_CINEMA_LIST_SEARCH . '/search/filter'); //某个影片下在映的影院列表
define('JAVA_API_SEARCH_CARD_CINEMA_LIST', JAVA_ADDR_BIG_DATA_CINEMA_LIST_SEARCH . '/search/cinemascard_sort');  //支持某个会员卡的影院列表
define('JAVA_API_GET_CINEMAS_LIST', JAVA_ADDR_BIG_DATA_CINEMA_LIST_SEARCH . '/gewara/search/cinemas_sort');  //城市下影院列表 格瓦拉渠道

//影院交叉筛选列表搜索（苏利军） 新版V2
define('JAVA_API_SEARCH_CINEMA_LIST_V2', JAVA_ADDR_BIG_DATA_CINEMA_LIST_SEARCH . '/v2/search/cinemas_sort');  //影院列表
define('JAVA_API_SEARCH_MOVIE_CINEMA_LIST_V2', JAVA_ADDR_BIG_DATA_CINEMA_LIST_SEARCH . '/v2/search/cinemasmov_sort'); //某个影片下在映的影院列表
define('JAVA_API_SEARCH_CINEMA_FILTER_V2', JAVA_ADDR_BIG_DATA_CINEMA_LIST_SEARCH . '/v2/search/filter'); //某个影片下在映的影院列表
define('JAVA_API_CITY_CINEMA_LIST_CHANNEL', JAVA_ADDR_BIG_DATA_CINEMA_LIST_SEARCH . '/gewara/search/cinemas_sort'); //渠道影院列表

//影片搜索接口（苏利军）
define('JAVA_ES_SEARCH_DATABASES_MOVIE', JAVA_ES_SEARCH_DATABASES . '/search/movieinfo');
//影院搜索接口新版（苏利军）
define('JAVA_API_SEARCH_CINEMA_INFO', JAVA_ADDR_BIG_DATA_CINEMA_INFO_SEARCH . '/search/cinemas_info');

//卖品优惠(郭朝伟)
define('JAVA_API_SNACK_DISCOUNT', JAVA_ADDR_SNACK_DISCOUNT . '/qryIncrBuyList');

//卖品支付
define('JAVA_API_SNACKPAY',JAVA_ADDR_ORDER . '/snack/snackPayment');

//影院会员卡
define('JAVA_API_VIPCARD_USER_CARD_LIST', JAVA_ADDR_CINEMA_VIP . '/v1/card/getCardList');  //用户的会员卡列表(影院详情页使用,不分页,需要cinemaId)
define('JAVA_API_VIPCARD_USER_CARD_LIST_PAGE', JAVA_ADDR_CINEMA_VIP . '/v1/card/getCardListByPage');  //用户的会员卡列表(我的会员卡页面使用,不需要cinemaId,分页)
define('JAVA_API_VIPCARD_CARD_LIST', JAVA_ADDR_CINEMA_VIP . '/v1/card/getCardTypeList');  //会员卡列表(根据 影院id,查列表)
define('JAVA_API_VIPCARD_CITY_CARD_LIST', JAVA_ADDR_CINEMA_VIP . '/v1/card/getCardTypeListByPage');  //会员卡列表(根据 城市Id,查列表)
define('JAVA_API_VIPCARD_CARD_INFO', JAVA_ADDR_CINEMA_VIP . '/v1/card/getCardInfo');  //会员卡详情
define('JAVA_API_VIPCARD_CARD_PAY', JAVA_ADDR_ORDER . '/card/memberCardPayment');  //会员卡支付
define('JAVA_API_VIPCARD_CARD_PAY_ACTIVE', JAVA_ADDR_ORDER . '/card/v1/memberCardPayment');  //会员卡支付活动使用
define('JAVA_API_VIPCARD_CARD_PAY_ACTIVE_V2', JAVA_ADDR_ORDER . '/card/v2/memberCardPayment');
define('JAVA_API_VIPCARD_CARD_CHECK_USER_BY', JAVA_ADDR_CINEMA_VIP . '/v1/card/getCardByTypeId');  //会员卡支付

//商品中心——数据中心
define('JAVA_API_CORE_CENTER_CINEMA_INFO', JAVA_ADDR_CORE_DATA . '/datacore/baseCinema/%s');  //影院详情
define('JAVA_API_CORE_CENTER_CINEMA_INFO_V1', JAVA_ADDR_CORE_DATA . '/v1/datacore/baseCinema/%s');  //影院详情V1
define('JAVA_API_CORE_CENTER_CITIES_V1', JAVA_ADDR_CORE_DATA . '/v1/datacore/cities');  //城市列表Java-V1版本
define('JAVA_API_CORE_CENTER_CITIES_V1_CHANNEL', JAVA_ADDR_CORE_DATA . '/v1/datacore/cities-for-channel');  //城市列表Java最终版
define('JAVA_API_CORE_CENTER_CITIES', JAVA_ADDR_CORE_DATA . '/datacore/cities');  //城市列表Java最终版
define('JAVA_API_CORE_CENTER_CHANGESFEE', JAVA_ADDR_CORE_DATA . '/datacore/changesFee');  //影院退改签手续费规则
define('JAVA_API_CORE_CENTER_CHANGESFEE_CALC', JAVA_ADDR_CORE_DATA . '/datacore/changesFee/calc');  //影院退改签当前费用
define('JAVA_API_CORE_CENTER_CINEMA_SEATS', JAVA_ADDR_CORE_DATA . '/cinemas/%s/halls/%s');  //静态座位图


//评论中心
define('COMMENT_CENTER_USER_WANT_MOVIES', COMMENT_CENTER_URL . '/v1/users/want-movies');  //获取用户对哪些电影想看了
define('COMMENT_USER_COMMENT_MOVIES', COMMENT_CENTER_URL . '/v1/users/comment-movies');  //获取用户评论了哪些电影
//大数据——防作弊
define('JAVA_BIG_DATA_ANTI_CHEATING_API', JAVA_BIG_DATA_ANTI_CHEATING . '/getantispaminfo/getuserblackinfo');  //城市列表Java最终版

//银河 商业化php 获取推荐会员卡
defined('SYH_PHP_BONUS_URL') or define('SYH_PHP_BONUS_URL','http://'.SYH_PHP_BONUS_URL_API.'/rightsactivity/recommendpaymentpage');

//评论中心——获取某个片子下所有预告片点赞次数
define('COMMENT_MOVIR_VIDEO_FAVORS', COMMENT_CENTER_URL . '/v1/videos/favors/movies/%s');

//获取格瓦拉侧订单对应的文案
define('PAPER_QUERY_ORDER_PRINT_MSG', GEWARA_URL . '/api/paper/queryOrderPrintMsg.xhtml');

//影片的购票数
defined("ORDDER_CENTER_MOVIE_BUY_NUM") or define("ORDDER_CENTER_MOVIE_BUY_NUM", JAVA_ADDR_ORDER . "/order/queryTicketNumByMovieId?movieId=%s");

//根据汉字，得到emoji标识
defined("EMOJI_TEXT_TO_NO") or define("EMOJI_TEXT_TO_NO", 'http://api.pr.weixin.qq.com/smartcs_mp/proxy?appname=emoji_translator');
