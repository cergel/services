<?php
/**
 *
 * 此配置文件格式：
 *
 *   'ret'=>[
 *       'sub'=>['userMsg'=>'[给用户显示的错误信息]','sysMsg'=>'{我们实际的错误信息}']
 *    ]
 *   【规范】:
 *    错误码一共为10位，
 *    1-2位:何种级别的错误，1，系统级 2，模块级
 *    3-6位：模块代号
 *    7-10位：具体的错误
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/11/27
 * Time: 11:57
 */


return [
    //01代表系统级别,每一个小key就是一个系统级别错误
    '01' => [
        '0000' => [
            '1001' => ['userMsg' => ERROR_MESSAGE_SYSBUSY, 'sysMsg' => 'mysql connection error'],//mysql链接错误
            '1002' => ['userMsg' => ERROR_MESSAGE_SYSBUSY, 'sysMsg' => 'redis connection error'],//redis连接错误
        ],
    ],
    //02代表模块级别，一级小key代表模块，二级小key代表模块具体错误
    '02' => [

        '0000' => [
            '1001' => ['userMsg' => ERROR_COMMENT_NO_DATA, 'sysMsg' => 'params error'],//参数不完整
        ],
        //评论模块
        '1002' => [
            '1001' => ['userMsg' => '参数不完整', 'sysMsg' => 'uid or movieId is null'],//数据不全
            '1002' => ['userMsg' => '评分插入失败', 'sysMsg' => 'score insert error'],//评分插入失败
            '1003' => ['userMsg' => '评分修改失败', 'sysMsg' => 'score update error'],//评分修改失败
            '1004' => ['userMsg' => '评分人数增加失败', 'sysMsg' => 'movie score num error'],//评分人数增加失败
            '1005' => ['userMsg' => '新增评论失败', 'sysMsg' => 'insert comment error'],//新增评论失败
            '1006' => ['userMsg' => '修改评论失败', 'sysMsg' => 'edit comment error'],//修改评论失败
            '1007' => ['userMsg' => '已被删除的评论', 'sysMsg' => 'is delete comment'],//已被删除的评论
            '1008' => ['userMsg' => '影片评论数修改失败', 'sysMsg' => 'movie Score count num error'],//评论：影片评论数修改失败
            '1009' => ['userMsg' => '抱歉，因您之前的多次发言包含违规内容，已被禁言', 'sysMsg' => 'is blask user'],//黑名单文案
            //以下几个不是错误，是屏蔽词分类提示文案
            '1010' => ['userMsg' => '不要发小广告噢', 'sysMsg' => 'ShieldingWords 1'],//文案1
            '1011' => ['userMsg' => '发言包含政治敏感词汇，请修改后再提交', 'sysMsg' => 'ShieldingWords 2'],//文案2
            '1012' => ['userMsg' => '您的发言包含不健康词汇', 'sysMsg' => 'ShieldingWords 3'],//文案3
            '1013' => ['userMsg' => '您的发言包含不友好内容，请修改', 'sysMsg' => 'ShieldingWords 4'],//文案4
            '1014' => ['userMsg' => '发言包含违规内容，请修改后提交', 'sysMsg' => 'ShieldingWords 5'],//文案5

            '1015' => ['userMsg' => '发送过于频繁，歇会儿吧', 'sysMsg' => ' time error '],//短时间发多条
            '1016' => ['userMsg' => '增加回复失败', 'sysMsg' => 'comment reply add error'],//回复增加失败
            '1017' => ['userMsg' => '修改回复失败', 'sysMsg' => 'comment reply edit error'],//回复修改失败
            '1018' => ['userMsg' => '修改回复数失败', 'sysMsg' => 'movie replyCount num error'],//回复-增加回复数失败
            '1019' => ['userMsg' => '增加想看失败', 'sysMsg' => 'movie want add error'],//想看：增加失败
            '1020' => ['userMsg' => '想看数修改失败', 'sysMsg' => 'movie wantCount  error'],//想看数+1失败
            '1021' => ['userMsg' => '增加看过失败', 'sysMsg' => 'movie seen add error'],//看过增加
            '1022' => ['userMsg' => '删除看过失败', 'sysMsg' => 'movie seen delete error'],//看过删除
            '1023' => ['userMsg' => '看过数修改失败', 'sysMsg' => 'movie seenCount  error'],//看过数+-1失败
            '1024' => ['userMsg' => '删除评分失败', 'sysMsg' => 'movie score delete error'],//评分删除失败
            '1025' => ['userMsg' => '取消想看失败', 'sysMsg' => 'want delete error'],//删除想看失败
            '1026' => ['userMsg' => '评论赞数修改失败', 'sysMsg' => 'comment favor count error'],//赞数+-1失败
            '1027' => ['userMsg' => '添加赞失败', 'sysMsg' => 'comment favor add error'],//赞失败
            '1028' => ['userMsg' => '取消赞失败', 'sysMsg' => 'comment favor delete error'],//赞删除败
            '1029' => ['userMsg' => '获取影片评论信息失败', 'sysMsg' => 'get movie comment info error'],//获取影片评论信息失败
            '1030' => ['userMsg' => '评论内容不能超过140字', 'sysMsg' => 'comment info length error'],//最大评论字数
            '1031' => ['userMsg' => '评论内容不能小于5字', 'sysMsg' => 'comment info length error'],//最小评论字数
            '1032' => ['userMsg' => '回复内容不能超过140字', 'sysMsg' => 'reply info length error'],//最大回复字数
            '1033' => ['userMsg' => '回复内容不能小于5字', 'sysMsg' => 'reply info length error'],//最小回复字数
            '1034' => ['userMsg' => '当前评论不属于您', 'sysMsg' => 'comment id is user'],////删除评论：当前评论不属于当前用户
            '1035' => ['userMsg' => '评论删除失败', 'sysMsg' => 'comment del error'],//评论删除失败
            '1036' => ['userMsg' => '评分删除失败', 'sysMsg' => 'score del error'],//删除评分失败
            '1037' => ['userMsg' => '评论不存在', 'sysMsg' => 'comment not exists'],//评论信息不存在

            '2001' => ['userMsg' => '系统繁忙稍后重试', 'sysMsg' => 'this function closed'],
        ],
        //微信小工具模块
        '1003' => [
            '1001' => ['userMsg' => '参数不完整', 'sysMsg' => 'Parameter error'],
            '1002' => ['userMsg' => '对不起，您的请求已超时，请重试', 'sysMsg' => 'request exception'],//请求超时
            '1003' => ['userMsg' => '尿点插入失败', 'sysMsg' => 'pee insert error'],//尿点插入失败
            '1004' => ['userMsg' => '生成jsApiTicket失败', 'sysMsg' => 'jsApiTicket create error'],//生成jsApiTicket失败
            '1005' => ['userMsg' => '发送模板消息失败', 'sysMsg' => 'send template fail'],
            '1006' => ['userMsg' => '模板类型错误', 'sysMsg' => 'template type error'],
            '1007' => ['userMsg' => '从redis获取token错误', 'sysMsy' => 'get token from error'], //redis获取token错误
        ],
        //尿点模块
        '1004' => [
            '1001' => ['userMsg' => '参数不完整', 'sysMsg' => 'Parameter error'],
            '1002' => ['userMsg' => '不存在的影片尿点', 'sysMsg' => 'error:not movie'],//不存在的影片
            '1003' => ['userMsg' => '添加失败', 'sysMsg' => 'add pee user error'],//添加失败
            '1004' => ['userMsg' => '删除失败', 'sysMsg' => 'delete pee user error'],//删除失败
        ],
        //订单相关模块
        '1005' => [
            '1001' => ['userMsg' => '删除订单失败,请稍后再试!', 'sysMsg' => 'Parameter error orderId'],
            '1002' => ['userMsg' => '删除订单失败,请稍后再试!', 'sysMsg' => 'Parameter error openId'],
            '1003' => ['userMsg' => '删除订单失败,请稍后再试!', 'sysMsg' => 'Redis error delete Order'],
            '1404' => ['userMsg' => '该订单不存在!', 'sysMsg' => 'Not found order'],
        ],
        //电影节
        '1006' => [
            '1001' => ['userMsg' => '添加影片到电影节清单失败!', 'sysMsg' => ''],
            '1002' => ['userMsg' => '删除电影节清单中的影片失败!', 'sysMsg' => ''],
        ],
        //媒资库
        '1007' => [
            '1001' => ['userMsg' => '媒资库数据不合法!', 'sysMsg' => ''],
            '1002' => ['userMsg' => '影片id不能为空', 'sysMsg' => ''],
            '1003' => ['userMsg' => '喜欢影人失败', 'sysMsg' => ''],
            '1004' => ['userMsg' => '取消喜欢影人失败', 'sysMsg' => ''],
            '1005' => ['userMsg' => '修改注水数失败', 'sysMsg' => ''],
            '1006' => ['userMsg' => '更新影人评价失败', 'sysMsg' => ''],
            '1007' => ['userMsg' => '增加影人评价失败', 'sysMsg' => ''],
            '1008' => ['userMsg' => '当前影人不是你的评价影人', 'sysMsg' => ''],
        ],
        //限制（IP、openId等等）
        '1008' => [
            '1001' => ['userMsg' => '系统请求繁忙.', 'sysMsg' => ''],
            '1002' => ['userMsg' => '参数不完整，无法做限制', 'sysMsg' => ''],
            '1003' => ['userMsg' => '系统请求繁忙..', 'sysMsg' => ''],
        ],
        //电影原声音乐
        '1009' => [
            '1001' => ['userMsg' => '参数不完整', 'sysMsg' => 'Parameter error'],
            '1002' => ['userMsg' => '不存在的影片', 'sysMsg' => 'error:not movie'],//不存在的影片
            '1003' => ['userMsg' => '该影片没有原声音乐', 'sysMsg' => 'error:not movie info'],//音乐列表为空
            '1004' => ['userMsg' => '删除失败', 'sysMsg' => 'error:delete error'],//删除失败
            '1005' => ['userMsg' => '写入缓存失败', 'sysMsg' => 'error:set cache failed'],//设置电影音乐缓存失败
        ],
        //滑动验证
        '1010' => [
            '1001' => ['userMsg' => '参数不完整', 'sysMsg' => ''],
            '1002' => ['userMsg' => '操作超时，请重试', 'sysMsg' => ''],
            '1003' => ['userMsg' => '验证码错误,点击这里重试', 'sysMsg' => ''],
            '1004' => ['userMsg' => 'slideId过长', 'sysMsg' => ''],
            '1005' => ['userMsg' => '凭证错误', 'sysMsg' => ''],
        ],
        //CMS
        '1011' => [
            '1001' => ['userMsg' => '参数不完整', 'sysMsg' => ''],
            '1002' => ['userMsg' => '操作超时，请重试', 'sysMsg' => ''],
            '1003' => ['userMsg' => '用户未登录', 'sysMsg' => ''],
            '1004' => ['userMsg' => '评论入库异常，请重新再试', 'sysMsg' => ''],
            '1005' => ['userMsg' => '评论点赞异常，请重新再试', 'sysMsg' => ''],
            '1006' => ['userMsg' => '取消点赞异常，请重新再试', 'sysMsg' => ''],
            '1007' => ['userMsg' => '不存在的评论数据，请重新再试', 'sysMsg' => ''],
            '1008' => ['userMsg' => '当前评论不属于您，请重新再试', 'sysMsg' => ''],
            '1009' => ['userMsg' => '评论删除失败，请重新再试', 'sysMsg' => ''],
        ],
        //报名活动
        '1012' => [
            '1001' => ['userMsg' => '参数不完整', 'sysMsg' => ''],
            '1002' => ['userMsg' => '报名用户信息添加失败', 'sysMsg' => ''],
            '1003' => ['userMsg' => '用户未登录', 'sysMsg' => ''],
            '1004' => ['userMsg' => '用户已报名', 'sysMsg' => ''],
        ],
        //用户id
        '1013' => [
            '1001' => ['userMsg' => 'openid error', 'sysMsg' => ''],
        ],
        //用户中心
        '1014' => [
            '1001' => ['userMsg' => '数据不存在', 'sysMsg' => ''],
            '1002' => ['userMsg' => '更新失败', 'sysMsg' => ''],
            '2000' => ['userMsg' => '该用户不存在', 'sysMsg' => ''],
            '2001' => ['userMsg' => '该第三方数据不存在', 'sysMsg' => ''],
            '2002' => ['userMsg' => '该手机号已经存在', 'sysMsg' => ''],
            '2003' => ['userMsg' => '注册失败', 'sysMsg' => ''],
            '2004' => ['userMsg' => '注册成功，获取信息失败', 'sysMsg' => ''],
            '2005' => ['userMsg' => '该第三方UID已经存在', 'sysMsg' => ''],
            '2006' => ['userMsg' => '用户信息不全', 'sysMsg' => ''],
            '2007' => ['userMsg' => '解绑失败', 'sysMsg' => ''],
            '2008' => ['userMsg' => '用户名或密码错误', 'sysMsg' => ''],
            '2009' => ['userMsg' => '绑定手机号失败', 'sysMsg' => ''],
            '2010' => ['userMsg' => '当前用户手机号不为空', 'sysMsg' => ''],
            '2011' => ['userMsg' => '只能绑定第三方登陆用户手机号', 'sysMsg' => ''],
            '2012' => ['userMsg' => '手机号修改失败', 'sysMsg' => ''],
            #'2013' => ['userMsg' => '手机号已存在', 'sysMsg' => ''],
            '2014' => ['userMsg' => '密码修改失败', 'sysMsg' => ''],
            '2015' => ['userMsg' => '禁止修改第三方用户密码', 'sysMsg' => ''],
            '2016' => ['userMsg' => '原密码错误', 'sysMsg' => ''],
            '2017' => ['userMsg' => '重置密码失败', 'sysMsg' => ''],
            '2018' => ['userMsg' => '该用户已绑定第三方账号', 'sysMsg' => ''],
            '2019' => ['userMsg' => '该Token已存在', 'sysMsg' => ''],
            '2020' => ['userMsg' => '用户Token信息存储失败', 'sysMsg' => ''],
            '2021' => ['userMsg' => '缺少AppKey', 'sysMsg' => ''],
            '2022' => ['userMsg' => '加密失败', 'sysMsg' => ''],
            '2023' => ['userMsg' => '旧手机号码不一致', 'sysMsg' => ''],
            '2024' => ['userMsg' => '手机号不存在', 'sysMsg' => ''],
            '2025' => ['userMsg' => '手机号码已被其他账号占用', 'sysMsg' => ''],
            '2026' => ['userMsg' => '手机号码已被当前账号绑定', 'sysMsg' => ''],
            '2027' => ['userMsg' => '当前账号已被绑定过', 'sysMsg' => ''],
            '2028' => ['userMsg' => '第三方凭证验证失败或过期', 'sysMsg' => ''],
            '2029' => ['userMsg' => '用户密码已存在', 'sysMsg' => ''],
            '2030' => ['userMsg' => '密码设置失败', 'sysMsg' => ''],
            '2031' => ['userMsg' => '换取UcId失败', 'sysMsg' => ''],
        ],
        //短信相关
        '1015' => [
            '1001' => ['userMsg' => '系统异常，验证失败', 'sysMsg' => ''],
            '1002' => ['userMsg' => '查找手机号验证信息失败', 'sysMsg' => ''],
            '1003' => ['userMsg' => '验证码错误', 'sysMsg' => ''],
            '1004' => ['userMsg' => '验证码已过期，请重试', 'sysMsg' => ''],
            '1005' => ['userMsg' => '发送验证码过于频繁,请稍后再试', 'sysMsg' => ''],
            '1006' => ['userMsg' => '验证已失效,请重新获取验证码', 'sysMsg' => ''],
            '1007' => ['userMsg' => '图形验证码错误', 'sysMsg' => ''],
            '1008' => ['userMsg' => '请输入图形验证码', 'sysMsg' => ''],
            '1009' => ['userMsg' => '图形验证码配置文件获取失败', 'sysMsg' => ''],
        ],
        '1016' => [
            '1001' => ['userMsg' => '参数不完整', 'sysMsg' => ''],
            '1002' => ['userMsg' => '删除观影轨迹失败', 'sysMsg' => ''],
        ],
        //微信授权
        '1021' => [
            '1001' => ['userMsg' => 'params error', 'sysMsg' => ''],
        ],
        //降级相关
        '1028' => [
            '1001' => ['userMsg' => '', 'sysMsg' => ''],    //折扣卡降级
        ],
        //token相关
        '1033' => [
            '1001' => ['userMsg' => '', 'sysMsg' => ''],    //手Q无效的token
        ],
        //小程序emoji
        '1038' => [
            '1001' => ['userMsg' => '无效用户', 'sysMsg' => ''],    //无效的用户
            '1002' => ['userMsg' => '内部题目错误', 'sysMsg' => ''],    //这种情况是，获取答案失败
            '1003' => ['userMsg' => '答案错误', 'sysMsg' => ''],    //这种情况是，获取答案失败
            '1004' => ['userMsg' => '答案格式非法', 'sysMsg' => ''],    //这种情况是，获取答案失败
            '1005' => ['userMsg' => 'emoji非法', 'sysMsg' => ''],    //这种情况是，获取答案失败
        ],
    ],
];
