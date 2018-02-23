<?php

namespace sdkService\service;
use vendor\logger\Logger;
/**
 * 所有基于评分评论体系的操作都放在这里面
 * Class serviceCommentNew
 * @package sdkService\service
 */
class serviceComment extends serviceBase
{
    const ERROR_CODE_STATUS = true;//错误编码状态

    static $levelDown = false;//评论降级

    //禁止降级的方法
    public $arrBanOfLevelDownFun = ['getUserinfoByUcid'];

    //降级函数
    public function levelDown($params)
    {
        $return = self::getErrorOut(ERRORCODE_COMMENT_LEVEL_DOWN);
        return $return;
    }

    /**
     * @tutorial 添加、修改评论接口
     * @param  int   uid     用户uid 【非必须】
     * @param  int   movieId 影片的id（数字的那个）【必须】
     * @param  int   channelId  渠道 【必须】
     * @param  int   from    子渠道 【非必须】
     * @param  int   score   评分    【非必须】
     * @param  int   type    表情类型 【非必须】
     * @param string content 内容    【必须】
     * @param string openId openId   【必须】
     * @param string unionId unionId   【非必须，微信必须】
     *
     */
    public function save(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'uid' => intval(self::getParam($arrInput, 'uid')), //用户uid
            'movieId' => intval(self::getParam($arrInput, 'movieId')),  //影片id
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
            'content' => self::getParam($arrInput, 'content'), //评论内容
            'score' => self::getParam($arrInput, 'score'),  //评分
            'type' => intval(self::getParam($arrInput, 'type')),   //表情类型
            'openId' => self::getParam($arrInput, 'openId'), //用户openId
            'unionId' => self::getParam($arrInput, 'unionId'), //用户unionId
            'isQzone' => self::getParam($arrInput, 'isQzone'), //用户unionId
        ];
        self::getParamOpenId($arrInstallData);  //获取用户信息
        $arrInstallData['checkstatus'] = 0;//默认值
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['ucid']) || empty($arrInstallData['movieId']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        }
        if (empty($arrReturn['ret']) && self::isBalckList($arrInstallData['ucid'])) {//黑名单
            $arrReturn = self::getErrorOut(ERRORCODE_BLACK_USER_COMMENT);
        }
        if (empty($arrReturn['ret'])) //腾讯屏蔽词
            $arrReturn = self::isTengXunKeyWord($arrInstallData['content']);
        if (empty($arrReturn['ret'])) //屏蔽词
            $arrReturn = self::isShieldingWords($arrInstallData['content']);
        if (empty($arrReturn['ret']) && self::isSensitiveWords($arrInstallData['content']))//敏感词
            $arrInstallData['checkstatus'] = 3;
        if (empty($arrReturn['ret']) && !self::isLastComment($arrInstallData['ucid'])) {//是否能发或改当前评论
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_UPDATE_TIME_WORE);
        }
        //判断是更新还是新增
        if (empty($arrReturn['ret']))
            $arrReturn = self::saveComment($arrInstallData);
        if(PUSH_COMMENT_TO_QQ && $arrInstallData['channelId'] == 28 && $arrInstallData['isQzone'] ==1 && !empty($arrReturn['data']['commentId'])){
            $arrInstallData['cid'] = $arrReturn['data']['commentId'];
            self::sendComment2Qzone($arrInstallData);
        }
        return $arrReturn;
    }

    /**
     * 发往qq空间
     * @param $arrInput
     */
    private function sendComment2Qzone($arrInput)
    {
        if(self::_getScore($arrInput) === true){//如果带了评分，就直接用
            $arrInput['attitude'] = $arrInput['score'] >= 60?1:0;
        }else{//如果评论的时候没带评分则去取
            $minData = $this->model('NewScore')->queryMovieUserScore($arrInput['movieId'], $arrInput['ucid']);
            if ($minData) {
                $minData = json_decode($minData, 1);
                $minData = isset($minData['score'])?$minData['score']:100;
            } else {
                $minData = 100;
            }
            $arrInput['attitude'] = $minData >= 60?1:0;
        }
        $res = $this->service('qzone')->sendComment2Qzone($arrInput);

//        $logger = new Logger();
//        $logger->setLogPath('/data/logs/qzoneqq.log');
//        $logger->initLogWatch(['data'=>json_encode($res)]);
    }

    /**
     * @tutorial 删除评论接口
     * @param  int   uid     用户uid 【非必须】
     * @param  int   commentId 评论的id（数字的那个）【必须】
     * @param  int   channelId  渠道 【必须】
     * @param  int   from    子渠道 【非必须】
     * @param string openId openId   【必须】
     * @param string unionId unionId   【非必须，微信必须】
     *
     */
    public function delComment(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'uid' => intval(self::getParam($arrInput, 'uid')), //用户uid
            'commentId' => intval(self::getParam($arrInput, 'commentId')),  //
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
            'openId' => self::getParam($arrInput, 'openId'), //用户openId
            'unionId' => self::getParam($arrInput, 'unionId'), //用户unionId
        ];
        self::getParamOpenId($arrInstallData);  //获取用户信息
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['ucid']) || empty($arrInstallData['commentId']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        }
        $arrComment = $this->model('NewComment')->queryComment($arrInstallData['commentId']);
        $arrComment = json_decode($arrComment, true);
        if (empty($arrComment['ucid']) || $arrComment['ucid'] != $arrInstallData['ucid']) {
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_DEL_NO_USER_ERROR);
        }

        $arrInstallData['movieId'] = $arrComment['movieId'];
        if (empty($arrReturn['ret'])){
            if($this->model('NewComment')->deleteComment($arrInstallData['commentId'])){
                //删除评分
                $this->model('NewScore')->delScore($arrInstallData);
                //删除差评
                $this->model('NewComment')->remBadCommentList($arrInstallData['movieId'],$arrInstallData['commentId']);
                //删除好评
                $this->model('NewComment')->remGoodCommentList($arrInstallData['movieId'],$arrInstallData['commentId']);
                //删除已购票
                $this->model('NewComment')->remBuyCommentList($arrInstallData['movieId'],$arrInstallData['commentId']);
                //删除看过
                self::_delSeen($arrInstallData);

            }else{
                $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_DEL_ERROR);
            }
        }
        return $arrReturn;
    }


    /**
     * @tutorial 添加回复--现在不支持修改回复，
     * @param  int   uid     用户uid  [非必须]
     * @param  int   commentId 评论id（数字的那个） [必须]
     * @param  int   channelId  渠道  [必须]
     * @param  int   from    子渠道     [非必须]
     * @param string content 内容     [必须]
     * @param string openId openId    [必须]
     * @param string unionId unionId   [非必须，微信必须]
     * @todo 还差黑名单、屏蔽词、敏感词 其他OK
     */
    public function saveReply(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'uid' => intval(self::getParam($arrInput, 'uid')), //用户uid
            'commentId' => intval(self::getParam($arrInput, 'commentId')),  //评论id
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
            'content' => self::getParam($arrInput, 'content'), //评论内容
            'openId' => self::getParam($arrInput, 'openId'), //用户openId
            'unionId' => self::getParam($arrInput, 'unionId'), //用户unionId
        ];
        self::getParamOpenId($arrInstallData);
        $arrReturn = self::getStOut();
        $arrInstallData['checkstatus'] = 0;//默认值
        if (empty($arrInstallData['ucid']) || empty($arrInstallData['commentId']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        }
        if (empty($arrReturn['ret']) && self::isBalckList($arrInstallData['ucid'])) {//黑名单
            $arrReturn = self::getErrorOut(ERRORCODE_BLACK_USER_COMMENT);
        }
        if (empty($arrReturn['ret'])) //腾讯屏蔽词
            $arrReturn = self::isTengXunKeyWord($arrInstallData['content']);
        if (empty($arrReturn['ret'])) //屏蔽词
            $arrReturn = self::isShieldingWords($arrInstallData['content']);
        if (empty($arrReturn['ret']) && self::isSensitiveWords($arrInstallData['content']))//敏感词
            $arrInstallData['checkstatus'] = 3;
        if (empty($arrReturn['ret']) && !self::isLastReply($arrInstallData['ucid'])) {//是否能发当前回复【回复不能修改，只能发】
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_UPDATE_TIME_WORE);
        }
        if (empty($arrReturn['ret'])) {//发回复
            $arrInstallData['created'] = $arrInstallData['updated'] = time();
            if (!$this->model("NewCommentReply")->insertReply($arrInstallData)) //插入
                $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_REPLY_ADD_ERROR);
        }
        return $arrReturn;
    }

    /**
     * @tutorial 给影片评分(或者修改评分)
     * @param  int   uid     用户uid      【非必须】
     * @param  int   movieId 影片id（数字的那个） 【必须】
     * @param  int   score  分数（0|100） 【必须】
     * @param  int   type    表情类型
     * @param  int   channelId  渠道       【必须】
     * @param  int   from    子渠道        【非必须】
     * @param string openId openId        【必须】
     * @param string unionId unionId      【非必须，微信必须】
     * @todo OK
     */
    public function score(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'uid' => intval(self::getParam($arrInput, 'uid')), //用户uid
            'movieId' => intval(self::getParam($arrInput, 'movieId')),  //影片id
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
            'score' => intval(self::getParam($arrInput, 'score')),  //评分
            'type' => intval(self::getParam($arrInput, 'type')),   //表情类型
            'openId' => self::getParam($arrInput, 'openId'), //用户openId
            'unionId' => self::getParam($arrInput, 'unionId'), //用户unionId
        ];
        self::getParamOpenId($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['ucid']) || empty($arrInstallData['movieId']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        } else {
            $arrReturn = self::_saveScore($arrInstallData);
        }

        //好评差评
        return $arrReturn;
    }

    /**
     * @tutorial 想看&取消想看接口
     * @param  int   uid     用户uid      【非必须】
     * @param  int   movieId 影片id（数字的那个）        【必须】
     * @param  int   want    是否想看，1：想看，0：取消想看。 【必须】
     * @param  int   channelId  渠道     【必须】
     * @param  int   from    子渠道            【非必须】
     * @param string openId openId          【必须】
     * @param string unionId unionId        【非必须】
     * @todo 缓存有问题
     */
    public function want(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'uid' => intval(self::getParam($arrInput, 'uid')), //用户uid
            'movieId' => intval(self::getParam($arrInput, 'movieId')),  //影片id
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
            'want' => intval(self::getParam($arrInput, 'want')),   //表情类型
            'openId' => self::getParam($arrInput, 'openId'), //用户openId
            'unionId' => self::getParam($arrInput, 'unionId'), //用户unionId
        ];
        self::getParamOpenId($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['ucid']) || empty($arrInstallData['movieId']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        } elseif ($arrInstallData['want'] == 1) {//想看
            $arrReturn = self::insertWant($arrInstallData);//想看和看过、评分互斥,所以要删除
        } elseif ($arrInstallData['want'] == 0) {//取消想看[删除想看]
            $arrReturn = self::delWant($arrInstallData);
        }
        return $arrReturn;
    }

    /**
     * @tutorial 看过 & 取消看过
     * 说明：由于之前版本都是使用的uid，数据库中存储的也是用户的uid，故此，这里也是使用uid，望使用者确认
     * @param  int   uid     用户uid
     * @param  int   movieId 影片id（数字的那个）
     * @param  int   seen    是否想看，1：想看，0：取消想看。
     * @param  int   channelId  渠道
     * @param  int   from    子渠道
     * @param string openId openId
     * @param string unionId unionId
     * todo 缓存有问题
     */
    public function seen(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'uid' => intval(self::getParam($arrInput, 'uid')), //用户uid
            'movieId' => intval(self::getParam($arrInput, 'movieId')),  //影片id
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
            'seen' => intval(self::getParam($arrInput, 'seen')),   //表情类型
            'openId' => self::getParam($arrInput, 'openId'), //用户openId
            'unionId' => self::getParam($arrInput, 'unionId'), //用户unionId
        ];
        self::getParamOpenId($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['ucid']) || empty($arrInstallData['movieId']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        } elseif ($arrInstallData['seen'] == 1) {//看过
            $arrReturn = self::insertSeen($arrInstallData);//看过和想看互斥，所以要删除想看
        } elseif ($arrInstallData['seen'] == 0) {//取消想看[删除想看]
            $arrReturn = self::_delSeen($arrInstallData);
        }
        return $arrReturn;
    }


    /**
     * @tutorial 对评论进行点赞、取消点赞，
     * @param  int   uid     用户uid   【非必须】
     * @param  int   commentId 评论id（数字的那个） 【必须】
     * @param  int   channelId  渠道  【必须】
     * @param  int   from    子渠道   【非必须】
     * @param  int   favor   点赞、去掉点赞，1：点赞，0：取消点赞.   【必须】
     * @param string openId openId  【必须】
     * @param string unionId unionId   【非必须】
     * @todo 已完成，未详细测试
     */
    public function favor(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'uid' => intval(self::getParam($arrInput, 'uid')), //用户uid
            'commentId' => intval(self::getParam($arrInput, 'commentId')),  //评论id
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
            'favor' => intval(self::getParam($arrInput, 'favor')), //评论内容
            'openId' => self::getParam($arrInput, 'openId'), //用户openId
            'unionId' => self::getParam($arrInput, 'unionId'), //用户unionId
        ];
        self::getParamOpenId($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['ucid']) || empty($arrInstallData['commentId']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        } elseif ($arrInstallData['favor'] == 1) { //点赞
            $arrReturn = self::addCommentFavor($arrInstallData);//点赞，并且点赞数+1
        } elseif ($arrInstallData['favor'] == 0) {//取消点赞
            $arrReturn = self::delCommentFavor($arrInstallData);//取消点赞，并且点赞数-1
        }
        return $arrReturn;
    }


    /**
     * @tutorial 获取用户对某个影片的评分、评论信息
     * @param  int   uid     用户uid  【非必须】
     * @param  int   movieId 影片id（数字的那个） 【必须】
     * @param  int   channelId  渠道  【必须】
     * @param  int   from    子渠道    【非必须】
     * @param string openId openId  【必须】
     * @param string unionId unionId    【非必须，微信必须】
     * @todo 完成，没有进行严格测试
     */
    public function getScoreAndComment(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'uid' => intval(self::getParam($arrInput, 'uid')), //用户uid
            'movieId' => intval(self::getParam($arrInput, 'movieId')),  //影片id
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
            'openId' => self::getParam($arrInput, 'openId'), //用户openId
            'unionId' => self::getParam($arrInput, 'unionId'), //用户unionId
        ];
        self::getParamOpenId($arrInstallData);
        $arrReturn = self::getStOut();

        if ( empty($arrInstallData['movieId']) || empty($arrInstallData['channelId'])) { //参数空校验
            return self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        }

        $scoreNumbers = $this->model('NewScore')->getMovieScoreNumbers($arrInstallData['movieId']);
        $commentSortByCount = [
            'new' => $this->model('NewComment')->queryMovieNewCommentCount($arrInstallData['movieId']),
            'time' => $this->model('NewComment')->queryMovieHotCommentCount($arrInstallData['movieId']),
            'good' => $this->model('NewComment')->queryMovieGoodCommentCount($arrInstallData['movieId']),
            'bad' => $this->model('NewComment')->queryMovieBadCommentCount($arrInstallData['movieId']),
            'buy' => $this->model('NewComment')->queryMovieBuyCommentCount($arrInstallData['movieId']),
        ];

        if (empty($arrInstallData['ucid'])) { //参数空校验
            $data = [
                'comment' =>  (object)[],
                'score' => (object)[],
                'want' =>  0,
                'seen' =>  0,
                'scoreNumber' => $scoreNumbers,//各个评分的人数
                'commentSortByCount' => $commentSortByCount,//每项评论分类的总数
            ];
            $arrReturn['data'] = $data;
        } else {
            $arrComment = $this->model('NewComment')->queryCommentByUcidMovieId($arrInstallData['ucid'], $arrInstallData['movieId']);
            if ($arrComment) {
                $arrComment = json_decode($arrComment, 1);
                $arrComment['favorCount'] = $this->model('NewCommentFavor')->getCommentNumber($arrComment['id']);
                $arrComment['favorCount'] += $arrComment['baseFavorCount'];
                $arrComment['favor'] = self::isUserFavor($arrInstallData['ucid'], $arrComment['id']) ? 1 : 0; //当前评论自己是否点过赞
                $replyCount = (int)$this->model('NewCommentReply')->getReplyCount($arrComment['id']);
                $arrComment['replyCount'] = $replyCount ? $replyCount : 0;//直接读取缓存
            }
            $arrScore = $this->model('NewScore')->queryMovieUserScore($arrInstallData['movieId'], $arrInstallData['ucid']);
            $arrScore = json_decode($arrScore, 1);
            $arrWant = $this->model('NewWant')->queryUserIsWantMovie($arrInstallData['ucid'], $arrInstallData['movieId']) ? 1 : 0;
            $arrSeen = $this->model('NewSeen')->queryUserIsSeenMovie($arrInstallData['ucid'], $arrInstallData['movieId']) ? 1 : 0;

            $data = [
                'comment' => !empty($arrComment) ? $arrComment : (object)[],
                'score' => !empty($arrScore) ? $arrScore : (object)[],
                'want' => $arrWant ? 1 : 0,
                'seen' => $arrSeen ? 1 : 0,
                'scoreNumber' => $scoreNumbers,//各个评分的人数
                'commentSortByCount' => $commentSortByCount,//每项评论分类的总数
            ];
            $arrReturn['data'] = $data;
        }
        return $arrReturn;
    }

    /**
     * @tutorial 获取某个影片的5条推荐评论及其他类型评论，只有第一页有效，目前仅用于手机QQ
     * 说明：由于之前版本都是使用的uid，数据库中存储的也是用户的uid，故此，这里也是使用uid，望使用者确认
     * @param  int   uid     用户uid 【非必须】
     * @param  int   movieId 影片id（数字的那个） 【必须】
     * @param  int   channelId  渠道 【必须】
     * @param  int   from    子渠道 【非必须】
     * @param  int   page   页数 【必须】
     * @param  int   num    条数（每页多少条）【必须】
     * @param  string  sortBy 排序方式（new:最新评论，reply：回复数，favor：喜欢数，time:时间维度） 都是DESC排序 【必须】
     * @param string openId openId 【非必须】
     * @param string unionId unionId 【非必须】
     * @param string recommend 是否需要推荐评论，默认是不获取，需要获取传1【非必须】
     * @todo 完成，未进行完全测试
     */
    public function getMovieCommentV2(array $arrInput){
        //参数整理
        $arrInstallData = [
            'uid' => intval(self::getParam($arrInput, 'uid')), //用户uid
            'movieId' => intval(self::getParam($arrInput, 'movieId')),  //影片id
            'page' => intval(self::getParam($arrInput, 'page')) ? intval(self::getParam($arrInput, 'page')) : 1,
            'num' => intval(self::getParam($arrInput, 'num')) ? intval(self::getParam($arrInput, 'num')) : 5,//
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
            'sortBy' => self::getParam($arrInput, 'sortBy'),
            'openId' => self::getParam($arrInput, 'openId'), //用户openId
            'unionId' => self::getParam($arrInput, 'unionId'), //用户unionId
            'recommend'=>intval(self::getParam($arrInput, 'recommend')) ? intval(self::getParam($arrInput, 'recommend')) : 0,
        ];
        self::getParamOpenId($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['movieId']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        } else {
            $iStart = ($arrInstallData['page'] - 1) * $arrInstallData['num'];
            $iEnd = $arrInstallData['page'] * $arrInstallData['num'] - 1;
            $arrComment = ['totalCount' => 0, 'comments' => ''];
            $arrRecommendComment = ['totalCount' => 0, 'comments' => ''];
            if($arrInstallData['recommend']){
                //获取5条推荐评论
                $arrRecommendComment['comments']  = $this->model('NewComment')->queryMovieRecommendComment($arrInstallData['movieId'], 0, 4);//列表
                $arrRecommendComment['totalCount'] = count($arrRecommendComment['comments']);
            }
            switch ($arrInstallData['sortBy']) {
                case 'new':
                    $arrComment['comments'] = $this->model('NewComment')->queryMovieNewComment($arrInstallData['movieId'], $iStart, $iEnd);//列表
                    $arrComment['totalCount'] = $this->model('NewComment')->queryMovieNewCommentCount($arrInstallData['movieId']);
                    break;
                case 'time':
                    $arrComment['comments'] = $this->model('NewComment')->queryMovieHotComment($arrInstallData['movieId'], $iStart, $iEnd);//列表
                    $arrComment['totalCount'] = $this->model('NewComment')->queryMovieHotCommentCount($arrInstallData['movieId']);
                    break;
                case 'good'://按照好评排序
                    $arrComment['comments'] = $this->model('NewComment')->queryMovieGoodComment($arrInstallData['movieId'], $iStart, $iEnd);//列表
                    $arrComment['totalCount'] = $this->model('NewComment')->queryMovieGoodCommentCount($arrInstallData['movieId']);
                    break;
                case 'bad'://按照差评排序
                    $arrComment['comments'] = $this->model('NewComment')->queryMovieBadComment($arrInstallData['movieId'], $iStart, $iEnd);//列表
                    $arrComment['totalCount'] = $this->model('NewComment')->queryMovieBadCommentCount($arrInstallData['movieId']);
                    break;
                case 'buy'://按照已购票
                    $arrComment['comments'] = $this->model('NewComment')->queryMovieBuyComment($arrInstallData['movieId'], $iStart, $iEnd);//列表
                    $arrComment['totalCount'] = $this->model('NewComment')->queryMovieBuyCommentCount($arrInstallData['movieId']);
                    break;
                case 'recommend'://获取推荐的评论
                    $arrComment['comments']  = $this->model('NewComment')->queryMovieRecommendComment($arrInstallData['movieId'], 0, 4);//列表
                    $arrComment['totalCount'] = count($arrComment['comments']);
                    //$arrComment['totalCount'] = $this->model('NewComment')->queryMovieRecommendCommentCount($arrInstallData['movieId']);
                    break;
                default://默认按照new排序
                    $arrComment['comments'] = $this->model('NewComment')->queryMovieNewComment($arrInstallData['movieId'], $iStart, $iEnd);//列表
                    $arrComment['totalCount'] = $this->model('NewComment')->queryMovieNewCommentCount($arrInstallData['movieId']);
            }
            if (!empty($arrComment['comments']))
                $arrComment['comments'] = $this->getCommentListUserFavorData($arrComment['comments'], $arrInstallData['ucid'], true);
            else $arrComment['comments'] = [];
            if (!empty($arrRecommendComment['comments']))
                $arrRecommendComment['comments'] = $this->getCommentListUserFavorData($arrRecommendComment['comments'], $arrInstallData['ucid'], true);
            else $arrRecommendComment['comments'] = [];
            $arrReturn['data'] = $arrComment;
            $arrReturn['data']['recommend'] = $arrRecommendComment;
        }
        return $arrReturn;
    }


    /**
     * @tutorial 获取某个影片的评论列表- 最新、热评等
     * 说明：由于之前版本都是使用的uid，数据库中存储的也是用户的uid，故此，这里也是使用uid，望使用者确认
     * @param  int   uid     用户uid 【非必须】
     * @param  int   movieId 影片id（数字的那个） 【必须】
     * @param  int   channelId  渠道 【必须】
     * @param  int   from    子渠道 【非必须】
     * @param  int   page   页数 【必须】
     * @param  int   num    条数（每页多少条）【必须】
     * @param  string  sortBy 排序方式（new:最新评论，reply：回复数，favor：喜欢数，time:时间维度） 都是DESC排序 【必须】
     * @param string openId openId 【非必须】
     * @param string unionId unionId 【非必须】
     * @todo 完成，未进行完全测试
     */
    public function getMovieComment(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'uid' => intval(self::getParam($arrInput, 'uid')), //用户uid
            'movieId' => intval(self::getParam($arrInput, 'movieId')),  //影片id
            'page' => intval(self::getParam($arrInput, 'page')) ? intval(self::getParam($arrInput, 'page')) : 1,
            'num' => intval(self::getParam($arrInput, 'num')) ? intval(self::getParam($arrInput, 'num')) : 5,//
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
            'sortBy' => self::getParam($arrInput, 'sortBy'),
            'openId' => self::getParam($arrInput, 'openId'), //用户openId
            'unionId' => self::getParam($arrInput, 'unionId'), //用户unionId
        ];
        self::getParamOpenId($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['movieId']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        } else {
            $iStart = ($arrInstallData['page'] - 1) * $arrInstallData['num'];
            $iEnd = $arrInstallData['page'] * $arrInstallData['num'] - 1;
            $arrComment = ['totalCount' => 0, 'comments' => ''];
            switch ($arrInstallData['sortBy']) {
                case 'new':
                    $arrComment['comments'] = $this->model('NewComment')->queryMovieNewComment($arrInstallData['movieId'], $iStart, $iEnd);//列表
                    $arrComment['totalCount'] = $this->model('NewComment')->queryMovieNewCommentCount($arrInstallData['movieId']);
                    break;
                case 'time':
                    $arrComment['comments'] = $this->model('NewComment')->queryMovieHotComment($arrInstallData['movieId'], $iStart, $iEnd);//列表
                    $arrComment['totalCount'] = $this->model('NewComment')->queryMovieHotCommentCount($arrInstallData['movieId']);
                    break;
                case 'good'://按照好评排序
                    $arrComment['comments'] = $this->model('NewComment')->queryMovieGoodComment($arrInstallData['movieId'], $iStart, $iEnd);//列表
                    $arrComment['totalCount'] = $this->model('NewComment')->queryMovieGoodCommentCount($arrInstallData['movieId']);
                    break;
                case 'bad'://按照差评排序
                    $arrComment['comments'] = $this->model('NewComment')->queryMovieBadComment($arrInstallData['movieId'], $iStart, $iEnd);//列表
                    $arrComment['totalCount'] = $this->model('NewComment')->queryMovieBadCommentCount($arrInstallData['movieId']);
                    break;
                case 'buy'://按照已购票
                    $arrComment['comments'] = $this->model('NewComment')->queryMovieBuyComment($arrInstallData['movieId'], $iStart, $iEnd);//列表
                    $arrComment['totalCount'] = $this->model('NewComment')->queryMovieBuyCommentCount($arrInstallData['movieId']);
                    break;
                case 'recommend'://获取推荐的评论
                    $arrComment['comments']  = $this->model('NewComment')->queryMovieRecommendComment($arrInstallData['movieId'], 0, 4);//列表
                    $arrComment['totalCount'] = count($arrComment['comments']);
//                    $arrComment['totalCount'] = $this->model('NewComment')->queryMovieRecommendCommentCount($arrInstallData['movieId']);
                    break;
                default://默认按照new排序
                    $arrComment['comments'] = $this->model('NewComment')->queryMovieNewComment($arrInstallData['movieId'], $iStart, $iEnd);//列表
                    $arrComment['totalCount'] = $this->model('NewComment')->queryMovieNewCommentCount($arrInstallData['movieId']);
            }
            if (!empty($arrComment['comments']))
                $arrComment['comments'] = $this->getCommentListUserFavorData($arrComment['comments'], $arrInstallData['ucid'], true);
            else $arrComment['comments'] = [];
            $arrReturn['data'] = $arrComment;
        }
        return $arrReturn;
    }


    /**
     * @tutorial 获取指定评论的回复列表
     * 说明：由于之前版本都是使用的uid，数据库中存储的也是用户的uid，故此，这里也是使用uid，望使用者确认
     * @param  int   uid     用户uid  【非必须】
     * @param  int   commentId 评论ID（数字的那个）  【必须】
     * @param  int   channelId  渠道  【必须】
     * @param  int   from    子渠道 【非必须】
     * @param  int   page   页数  【必须】
     * @param  int   num    条数（每页多少条）   【必须】
     * @param string openId openId  【非必须】
     * @param string unionId unionId`    【非必须】
     * @todo 完成，未进行完全测试
     *
     */
    public function getMovieCommentReply(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'uid' => intval(self::getParam($arrInput, 'uid')), //用户uid
            'commentId' => intval(self::getParam($arrInput, 'commentId')),  //影片id
            'page' => intval(self::getParam($arrInput, 'page')) ? intval(self::getParam($arrInput, 'page')) : 1,
            'num' => intval(self::getParam($arrInput, 'num')) ? intval(self::getParam($arrInput, 'num')) : 5,//
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
            'openId' => self::getParam($arrInput, 'openId'), //用户openId
            'unionId' => self::getParam($arrInput, 'unionId'), //用户unionId
        ];
        self::getParamOpenId($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['commentId']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        }else {
            $arrInstallData['num'] = $arrInstallData['num']<=100 ? $arrInstallData['num'] : 100;//防止传入的数量过多
            $arrInstallData['num'] = $arrInstallData['num']<0 ? 5 : $arrInstallData['num'];
            $page = $arrInstallData['page']>0 ? $arrInstallData['page']: 1;
            $iStart = ($page -1) * $arrInstallData['num'];
            $iEnd = $page * $arrInstallData['num'] - 1;
            $arrData = ['totalCount'=>0,'replies'=>[]];
            $arrData['replies'] = $this->model('NewCommentReply')->getReplyList($arrInstallData['commentId'], $iStart, $iEnd);//列表
            $arrData['totalCount'] = $this->model('NewCommentReply')->getReplyCount($arrInstallData['commentId']);
            if (!$arrData) {
                $arrData['replies'] = [];
            }
            if (!empty($arrData['replies']))
                $arrData['replies'] = self::getReplyListUser($arrData['replies']);
            else $arrData['replies'] = [];
            $arrData['comment'] = (object)self::getCommentInfoById($arrInstallData['commentId'], $arrInstallData['ucid']);
            $arrReturn['data'] = $arrData;
        }
        return $arrReturn;
    }


    /**
     * @tutorial 获取看过指定电影的用户
     * @param  int   movieId     电影Id（数字）【必须】
     * @param  int   channelId  渠道  【必须】
     * @param  int   from    子渠道    【非必须】
     * @param  int   page   页数      【必须】
     * @param  int   num    条数（每页多少条）  【必须】
     * @param string openId openId  【非必须】
     * @param string unionId unionId  【非必须】
     * @todo 完成，未进行完全测试
     */
    public function getMovieSeenUser(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'movieId' => intval(self::getParam($arrInput, 'movieId')),
            'page' => intval(self::getParam($arrInput, 'page')) ? intval(self::getParam($arrInput, 'page')) : 1,
            'num' => intval(self::getParam($arrInput, 'num')) ? intval(self::getParam($arrInput, 'num')) : 5,//
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
        ];
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['movieId']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        } else {
            $iStart = ($arrInstallData['page'] - 1) * $arrInstallData['num'];
            $iEnd = $arrInstallData['page'] * $arrInstallData['num'] - 1;
            $arrData = ['totalCount' => 0, 'users' => []];
            $arrData['users'] = $this->model('NewSeen')->queryMovieSeenList($arrInstallData['movieId'], $iStart, $iEnd);//列
            $arrData['totalCount'] = $this->model('NewSeen')->queryMovieSeenCount($arrInstallData['movieId']);
            if (!empty($arrData['users'])) {
                $arrData['users'] = self::getUserInfoByArray($arrData['users']);
            } else {
                $arrData['users'] = [];
            }
            $arrReturn['data'] = $arrData;
        }
        return $arrReturn;
    }

    /**
     * @tutorial 获取我想看的电影
     * 说明：由于之前版本都是使用的uid，数据库中存储的也是用户的uid，故此，这里也是使用uid，望使用者确认
     * @param  int   uid     用户uid 【非必须】
     * @param  int   channelId  渠道  【必须】
     * @param  int   from    子渠道    【非必须】
     * @param  int   page   页数  【必须】
     * @param  int   num    条数（每页多少条）   【必须】
     * @param string openId openId  【必须】
     * @param string unionId unionId    【非必须，微信必须】
     * @todo 完成，未进行完全测试
     */
    public function getUserWantMovie(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'uid' => intval(self::getParam($arrInput, 'uid')), //用户uid
            'page' => intval(self::getParam($arrInput, 'page')) ? intval(self::getParam($arrInput, 'page')) : 1,
            'num' => intval(self::getParam($arrInput, 'num')) ? intval(self::getParam($arrInput, 'num')) : 5,//
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
            'openId' => self::getParam($arrInput, 'openId'), //用户openId
            'unionId' => self::getParam($arrInput, 'unionId'), //用户unionId
        ];
        self::getParamOpenId($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['ucid']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        } else {
            $iStart = ($arrInstallData['page'] - 1) * $arrInstallData['num'];
            $iEnd = $arrInstallData['page'] * $arrInstallData['num'] - 1;
            $arrData = $return = ['movies' => [], 'totalCount' => 0,];
            $arrData['movies'] = $this->model('NewWant')->queryUserWantList($arrInstallData['ucid'], $iStart, $iEnd);//列表
            $arrData['totalCount'] = $this->model('NewWant')->queryUserWantCount($arrInstallData['ucid']);//列表
            if (empty($arrData['movies']))
                $arrData['movies'] = [];
            $arrReturn['data'] = $arrData;
        }
        return $arrReturn;
    }


    /**
     * @tutorial 获取我看过的电影
     * 说明：由于之前版本都是使用的uid，数据库中存储的也是用户的uid，故此，这里也是使用uid，望使用者确认
     * @param  int   uid     用户uid 非必填
     * @param  int   channelId  渠道
     * @param  int   from    子渠道
     * @param  int   page   页数
     * @param  int   num    条数（每页多少条）
     * @param string openId openId
     * @param string unionId unionId
     * @todo 完成，未进行完全测试
     */
    public function getUserSeenMovie(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'uid' => intval(self::getParam($arrInput, 'uid')), //用户uid
            'page' => intval(self::getParam($arrInput, 'page')) ? intval(self::getParam($arrInput, 'page')) : 1,
            'num' => intval(self::getParam($arrInput, 'num')) ? intval(self::getParam($arrInput, 'num')) : 5,//
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
            'openId' => self::getParam($arrInput, 'openId'), //用户openId
            'unionId' => self::getParam($arrInput, 'unionId'), //用户unionId
            'withTime'=>self::getParam($arrInput, 'withTime'), //是否将时间也显示出来[非必传]
        ];
        self::getParamOpenId($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['ucid']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        } else {
            $iStart = ($arrInstallData['page'] - 1) * $arrInstallData['num'];
            $iEnd = $arrInstallData['page'] * $arrInstallData['num'] - 1;
            $arrData = ['movies' => [], 'totalCount' => 0,];
            $withScores = isset($arrInstallData['withTime']) ? $arrInstallData['withTime'] : false;
            $arrData['movies'] = $this->model('NewSeen')->queryUserSeenList($arrInstallData['ucid'], $iStart, $iEnd,$withScores);//列表
            $arrData['totalCount'] = $this->model('NewSeen')->queryUserSeenCount($arrInstallData['ucid']);
            if (empty($arrData['movies']))
                $arrData['movies'] = [];
            $arrReturn['data'] = $arrData;
        }
        return $arrReturn;
    }


    /**
     * @tutorial 指定用户是否评论过当前影片
     * @param  int   uid     用户uid 非必填  【非必须】
     * @param  int   movieId     电影Id（数字） 【必须】
     * @param  int   channelId  渠道 【必须】
     * @param  int   from    子渠道    【非必须】
     * @param string openId openId  【必须】
     * @param string unionId unionId    【非必须，微信必须】
     * @todo 完成，未进行完全测试
     * 此函数有问题，返回值一会是json数组，一会是json对象
     */

    public function isUserCommentMovie(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'movieId' => intval(self::getParam($arrInput, 'movieId')),
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
            'uid' => intval(self::getParam($arrInput, 'uid')), //用户uid
            'openId' => self::getParam($arrInput, 'openId'), //用户openId
            'unionId' => self::getParam($arrInput, 'unionId'), //用户unionId
        ];
        self::getParamOpenId($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['movieId']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        } else {
            $minData = $this->model('NewComment')->queryCommentByUcidMovieId($arrInstallData['ucid'], $arrInstallData['movieId']);
            if (!empty($minData)) {
                $minData = json_decode($minData, 1);
            }
            $arrReturn['data'] = empty($minData) ? [] : $minData;
        }
        return $arrReturn;
    }


    /**
     * 获取用户评论过的影片列表
     * @param  int   uid     用户uid 非必填  【非必须】
     * @param  array   movieList     电影Id（数字）  【必须】
     * @param  int   channelId  渠道 【必须】
     * @param  int   from    子渠道    【非必须】
     * @param string openId openId  【必须】
     * @param string unionId unionId    【非必须，微信必须】
     * @return array
     * @todo 完成，未进行完全测试
     */
    public function getCommentList($arrInput)
    {
        //参数整理
        $arrInput = [
            'uid' => intval(self::getParam($arrInput, 'uid')), //用户uid
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'openId' => self::getParam($arrInput, 'openId'), //用户openId
            'unionId' => self::getParam($arrInput, 'unionId'), //用户unionId
            'movieList' => self::getParam($arrInput, 'movieList'),
        ];
        self::getParamOpenId($arrInput);
        if (empty($arrInput['ucid']) || empty($arrInput['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        } else {
            $movieList = $arrInput['movieList'];
            $arrReturn = self::getStOut();
            $arrData = $this->model('NewComment')->queryUserAllCommentMovieId($arrInput['ucid']);
            if ($arrData) {
                if (!empty($movieList)) {
                    $arrReturn['data'] = array_intersect($arrData, $movieList);
                } else {
                    $arrReturn['data'] = $arrData;
                }
            } else {
                $arrReturn['data'] = [];
            }
        }
        return $arrReturn;
    }

    //
    /**
     * 指定影片的评论相关信息 谁写的？谁在使用？注释貌似不对,这个函数未进行修改，还是原来的，
     * @todo 看上面
     * @param $arrInput
     * @return array
     * @throws \Exception
     */
    public function getMovieCommentInfo($arrInput)
    {
        $iMovieId = $arrInput['movieId'];

        $r = $this->model('MovieDBApp')->getOneMovieObject($iMovieId);
        if ($r) {
            $arrMovie = [];
            foreach ($r as $k => $v) {
                $arrMovie[$k] = $v;
            }
            $arrReturn = self::getStOut();
            $arrReturn['data'] = $arrMovie;
        } else {
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_MOVIE_COMMENT_INFO_ERROR);
        }

        return $arrReturn;
    }


    /**
     * 整理评论列表的格式
     * @param array $arrComment
     *
     */
    private function getCommentListUserFavorData($arrComment, $strUcid, $favor = false)
    {
        if (!empty($arrComment)) {
            foreach ($arrComment as &$val) {
                if (empty($val)) {
                    unset($val);
                    #$val = false;
                    continue;
                }
                $val = $this->model('NewComment')->queryComment($val);
                if (empty($val)) {
                    unset($val);
                    #$val = false;
                    continue;
                }
                $val = json_decode($val, 1);
                $val['favorCount'] = $this->model('NewCommentFavor')->getCommentNumber($val['id']); //现在是读取的redis的
                $val['favorCount'] += $val['baseFavorCount'];
                $replyCount = $this->model('NewCommentReply')->getReplyCount($val['id']);
                $val['replyCount'] = $replyCount ? $replyCount : 0;//直接读取缓存
                unset($val['baseFavorCount']);
                unset($val['movieName']);
                $val['user'] = self::_getUserInfoByUcid($val['ucid'], $val['uid']);//获取用户信息
                $val['favor'] = self::isUserFavor($strUcid, $val['id']) ? 1 : 0;
                $val['score'] = self::_getUcid2MovieIdScore($val['ucid'], $val['movieId']);
            }
            $arrComment = array_values($arrComment);
            // $arrComment = array_filter($arrComment);
        } else $arrComment = [];

        return (array)$arrComment;
    }

    /**
     * 根据commentId获取评论的信息
     * @param int $iCommentId
     * @return array
     */
    private function getCommentInfoById($iCommentId, $ucid)
    {
        $arrComment = [];
        if (!empty($iCommentId)) {
            $arrComment = $this->model('NewComment')->queryComment($iCommentId);
            if (!empty($arrComment)) {
                $arrComment = json_decode($arrComment, true);
                if (empty($arrComment['id'])) return [];
                $arrComment['favorCount'] = $this->model('NewCommentFavor')->getCommentNumber($arrComment['id']); //现在是读取的redis的
                $arrComment['favorCount'] += $arrComment['baseFavorCount'];
                $replyCount = $this->model('NewCommentReply')->getReplyCount($iCommentId);
                $arrComment['replyCount'] = $replyCount ? $replyCount : 0;
                unset($arrComment['baseFavorCount']);
                $arrComment['favor'] = $this->isUserFavor($ucid, $iCommentId) ? 1 : 0;
                $scoreResult = $this->model('NewScore')->queryMovieUserScore($arrComment['movieId'], $arrComment['ucid']);
                if ($scoreResult) {
                    $scoreObj = json_decode($scoreResult);
                    $arrComment['score'] = (int)($scoreObj->score);
                } else {
                    $arrComment['score'] = -1;//-1代表未评分
                }
                $arrComment['user'] = self::_getUserInfoByUcid($arrComment['ucid'], $arrComment['uid']);//获取用户信息
            }
        }
        return $arrComment;
    }

    /**
     * 整理回复的列表格式
     * @param array $arrRepiles
     *
     */
    private function getReplyListUser($arrReply)
    {
        if (!empty($arrReply) && is_array($arrReply)) {
            foreach ($arrReply as &$val) {
                if (empty($val)) {
                    $val = false;
                    continue;
                }
                $val = $this->model('NewCommentReply')->getReplyInfo($val);
                if (empty($val)) {
                    $val = false;
                    continue;
                }
                $val = json_decode($val, 1);
                $val['user'] = self::_getUserInfoByUcid($val['ucid'], $val['uid']);//获取用户信息
            }
            $arrReply = array_filter($arrReply);
        } else $arrReply = [];
        return $arrReply;
    }

    /**
     * 获取评分
     * @param $strUcid
     * @param $iMovieId
     * @return int
     */
    private function _getUcid2MovieIdScore($strUcid, $iMovieId)
    {
        $iScore = -1;
        if (empty($strUcid) || empty($iMovieId))
            return $iScore;
        $arrScore = $this->model('NewScore')->queryMovieUserScore($iMovieId, $strUcid);
        if ($arrScore) {
            $arrScore = json_decode($arrScore, 1);
            $iScore = $arrScore['score'];
        }
        $iScore = (int)$iScore;
        return $iScore;
        //$arrScore = json_decode($arrScore,1);
        //$arrayData = $this->model('Score')->getUserScoreArray($strUcid, $iMovieId);
        //return isset($arrayData['score']) ? $arrayData['score'] : -1;
    }


    /**
     * @tutorial 对评论点赞
     * @param unknown $arrData
     * @return Ambigous <multitype:, multitype:number string multitype: , multitype:string NULL unknown >
     */
    private function addCommentFavor($arrData)
    {
        $arrReturn = self::getStOut();
        if (!self::isUserFavor($arrData['ucid'], $arrData['commentId'])) {
            $arrData['created'] = time();
            if (!$this->model('NewCommentFavor')->insertFavor($arrData)) //入库
                $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_FAVOR_ADD_ERROR);//赞添加失败
        }
        return $arrReturn;
    }

    /**
     * @tutorial 取消点赞
     * @param unknown $arrData
     * @return Ambigous <multitype:, multitype:number string multitype: , multitype:string NULL unknown >
     */
    private function delCommentFavor($arrData)
    {
        $arrReturn = self::getStOut();
        if (self::isUserFavor($arrData['ucid'], $arrData['commentId'])) {//不存在赞，则删除
            if (!$this->model('NewCommentFavor')->deleteFavor($arrData)) //删除
                $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_FAVOR_DEL_ERROR);//赞添加失败
        }
        return $arrReturn;
    }

    /**
     * 当前用户是否点赞过该评论
     * @param $strUcid
     * @param $iCommentId
     * @return bool
     */
    private function isUserFavor($strUcid, $iCommentId)
    {
        $boolRes = false;
        if(!empty($strUcid)){
            $boolRes = $this->model('NewCommentFavor')->isFavor($iCommentId, $strUcid);
        }
        return $boolRes;
    }


    /**
     * @tutorial 插入看过
     * @param array $arrData
     */
    private function insertSeen($arrData)
    {
        $arrReturn = self::getStOut();
        self::_isMovieData($arrData['movieId']);
        if (!$this->model('NewSeen')->queryUserIsSeenMovie($arrData['ucid'], $arrData['movieId'])) {//没有看过
            $arrData['created'] = time();
            if ($this->model('NewSeen')->insertUserSeen($arrData)) {//插入数据
                $arrReturn = self::delWant($arrData);//删除想看:想看和看过互斥。
            } else $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_WANT_ADD_ERROR);
        }//已经是想看了，就不用处理了
        return $arrReturn;
    }

    /**
     * @tutorial 插入想看，想看和看过互斥,所以要删除看过
     * @param array $arrData
     */
    private function insertWant($arrData)
    {
        $arrReturn = self::getStOut();
        self::_isMovieData($arrData['movieId']);
        if (!$this->model('NewWant')->queryUserIsWantMovie($arrData['ucid'], $arrData['movieId'])) {//没有想看数据，则进行增加想看
            $arrData['created'] = time();
            if ($this->model('NewWant')->insertUserWant($arrData)) {//插入想看数据
                $arrReturn = self::_delSeen($arrData);//删除看过:想看和看过互斥。
            } else $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_WANT_ADD_ERROR);
        }//已经是想看了，就不用处理了
        return $arrReturn;
    }

    /**
     * @tutorial 删除想看，想看数-1
     * @param array $arrData
     */
    private function delWant($arrData)
    {
        $arrReturn = self::getStOut();
        if ($this->model('NewWant')->queryUserIsWantMovie($arrData['ucid'], $arrData['movieId'])) {//有想看数据，进行删除
            if (!$this->model('NewWant')->delUserWant($arrData['ucid'], $arrData['movieId'])) //删除想看数据
                $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_WANT_DELETE_ERROR);
        }//没有想看数据，就不用处理了
        return $arrReturn;
    }

    /**
     *  删除看过,同时看过数减一
     * @param array $arrData
     * @return array
     */
    private function _delSeen($arrData)
    {
        $arrReturn = self::getStOut();
        if ($this->model('NewSeen')->queryUserIsSeenMovie($arrData['ucid'], $arrData['movieId'])) {//存在看过才会进行删除
            if (!$this->model('NewSeen')->delUserSeen($arrData['ucid'], $arrData['movieId'])) //删除看过
                $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_SEEN_DEL_ERROR);//删除看过失败
        }
        return $arrReturn;
    }


    /**
     * @tutorial 是否能发当前评论：基于时间判断
     * @param int $iUid
     * @param int $iMovieId
     * @return boolean
     */
    private function isLastComment($strUcid)
    {
        $boolRes = true;
        $iLastTime = $this->model('NewComment')->queryUserLastCommentTime($strUcid);
        if (!empty($iLastTime) && is_array($iLastTime)) {
            $iLastTime = intval(current($iLastTime));
            if (($iLastTime + COMMENT_UPDATE_TIME) > time())
                $boolRes = false;
        }
        return $boolRes;
    }

    /**
     * @tutorial 是否能发当前回复：基于时间判断
     * @param int $iUid
     * @return boolean
     */
    private function isLastReply($strUcid)
    {
        $boolRes = true;
        $iLastTime = $this->model('NewCommentReply')->getUserLastReplyTime($strUcid);
        if ($iLastTime) {
            if (($iLastTime + COMMENT_REPLY_UPDATE_TIME) > time())
                $boolRes = false;
        }
        return $boolRes;
    }

    /**
     * @tutorial 更新或者插入评论。
     * @param array $arrInstallData
     */
    private function saveComment($arrInstallData)
    {
        $arrReturn = self::getStOut();
        $minData = $this->model('NewComment')->queryCommentByUcidMovieId($arrInstallData['ucid'], $arrInstallData['movieId']);
        self::_isMovieData($arrInstallData['movieId']);//判断影片是否存在
        $commentId = 0;
        $commentCreated = '';
        if ($minData) {//更新
            $objData = json_decode($minData);
            $arrInstallData['updated'] = time();
            if ($objData->status == 0) { // 已被删除的评论
                $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_DELETE);
            } else { //更新
                $commentId = $objData->id;
                $boolUpdate = $this->model('NewComment')->updateComment($objData->id, $arrInstallData);
                if (!$boolUpdate) {//更新失败：
                    $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_EDIT);
                }
            }
        } else {//新增
            $arrInstallData['created'] = $arrInstallData['updated'] = $commentCreated = time();
            $boolUpdate = $this->model('NewComment')->insertComment($arrInstallData);
            if ($boolUpdate) {//插入成功，评论数+1，各个集合修改成功
                $commentId = $boolUpdate;
                //用户是否想看改影片,如果既没有想看也没有看过，则默认是看过
                if (!$this->model('NewWant')->queryUserIsWantMovie($arrInstallData['ucid'], $arrInstallData['movieId']) && !$this->model('NewSeen')->queryUserIsSeenMovie($arrInstallData['ucid'], $arrInstallData['movieId'])) {
                    if (!$this->model('NewSeen')->insertUserSeen($arrInstallData)) {
                    }
                }
            } else
                $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_ADD);// 新增失败
        }
        if (empty($arrReturn['ret']) && isset($arrInstallData['score']) && $arrInstallData['score'] !== '') {//有评分的情况下计算评分
            $arrReturn = self::_saveScore($arrInstallData);
        }elseif(empty($arrReturn['ret'])){//无评分的情况下进行清洗数据
            $minData = $this->model('NewScore')->queryMovieUserScore($arrInstallData['movieId'], $arrInstallData['ucid']);
            $minData = json_decode($minData, 1);
            $score = isset($minData['score'])?$minData['score']:'';
            self::_saveCommentScoreList($arrInstallData['movieId'],$score,$commentId,0);
        }

        if (!empty($commentId)) {
            $arrReturn['data'] = ['commentId' => $commentId];
        }
        return $arrReturn;
    }

    /**
     * @tutorial 用户是否在黑名单
     * @param unknown $uid
     * @return bool T or F
     * @todo 还未完成黑名单
     */
    private function isBalckList($ucid)
    {
        $arrBlackList = $this->model('BlackList')->getBlackList();
        $boolRes = false;
        if (!empty($arrBlackList) && !empty($ucid)) {
            if (in_array($ucid, $arrBlackList))
                $boolRes = true;
        }
        return $boolRes;
    }

    /**
     * 腾讯云的关键字接口
     * @param $strContent
     */
    private function isTengXunKeyWord($strContent)
    {
        $arrReturn = self::getStOut();
        $url = "http://10.3.40.23/kw.php";
        $params['sMethod'] = 'GET';
        $params['sendType'] = 'json';
        $params['iTimeout'] = 1000;
        $params['arrData'] = [
            'content' => $strContent
        ];
        $retData = $this->http($url, $params);
        if (!empty($retData['level']) && $retData['level'] > 2) {
            $arrReturn = self::getErrorOut(ERRORCODE_SHIELDING_4_WORD);
        }
        //写入日志
        if (!empty($retData['level']) && $retData['level'] == 2) {
            $logger = new Logger();
            $logger->setLogPath('/data/logs/key_word.log');
            $logger->initLogWatch(['data'=>$retData['beatTips']]);
        }
        return $arrReturn;

    }

    /**
     * @tutorial 屏蔽词判断，是否包含屏蔽词
     * @param string $strContent
     * @return Ambigous <boolean, unknown>
     * @todo 还未完成屏蔽词
     */
    private function isShieldingWords($strContent)
    {
        $arrShieldingWordsList = $this->model("ShieldingWords")->getShieldingWordsList();
        $arrReturn = self::getStOut();
        $minRes = true;
        if (!empty($arrShieldingWordsList)) {
            //屏蔽词校验
            foreach ($arrShieldingWordsList as $shielding) {
                if (!empty($shielding['name']) && strstr($strContent, $shielding['name'])) {
                    $minRes = $shielding['stype'];
                    break;
                }
            }
        }
        if ($minRes !== true) {
            switch ($minRes) {
                case 1:
                    $arrReturn = self::getErrorOut(ERRORCODE_SHIELDING_1_WORD);
                    break;
                case 2:
                    $arrReturn = self::getErrorOut(ERRORCODE_SHIELDING_2_WORD);
                    break;
                case 3:
                    $arrReturn = self::getErrorOut(ERRORCODE_SHIELDING_3_WORD);
                    break;
                case 4:
                    $arrReturn = self::getErrorOut(ERRORCODE_SHIELDING_4_WORD);
                    break;
                case 5:
                    $arrReturn = self::getErrorOut(ERRORCODE_SHIELDING_5_WORD);
                    break;
            }
        }
        return $arrReturn;
    }

    /**
     * @tutorial 敏感词判断，是否包含敏感词
     * @param string $strContent
     * @return bool
     * @todo 还未完成敏感词
     */
    private function isSensitiveWords($strContent)
    {
        $arrSensitiveWordsList = $this->model("SensitiveWords")->getSensitiveWordsList();
        $boolRes = false;
        if (!empty($arrSensitiveWordsList)) {
            foreach ($arrSensitiveWordsList as $val) { //屏蔽词校验
                if (!empty($val['name']) && strstr($strContent, $val['name'])) {
                    $boolRes = true;
                    break;
                }
            }
        }
        return $boolRes;
    }

    /**
     * 评论、回复的字数判断
     * @param $strData
     * @param int $iMin
     * @param int $iMax
     * @param string $type
     * @return array
     * @throws \Exception
     *
     */
    private function getStrlen($strData, $iMin = 0, $iMax = 10000, $type = 'comment')
    {
        $arrReturn = self::getStOut();
        if (mb_strlen($strData, 'UTF8') < $iMin) {
            $arrReturn = self::getErrorOut($type == 'comment' ? ERRORCODE_COMMENT_LENGTH_MIN_ERROR : ERRORCODE_COMMENT_REPLY_LENGTH_MIN_ERROR);
        } elseif (mb_strlen($strData, 'UTF8') < $iMax) {
            $arrReturn = self::getErrorOut($type == 'comment' ? ERRORCODE_COMMENT_LENGTH_MAX_ERROR : ERRORCODE_COMMENT_REPLY_LENGTH_MAX_ERROR);
        }
        return $arrReturn;
    }


    /**
     * @tutorial 判断影片是否存在，若不存在，则插入一条
     * @param int $iMovieId
     * @return boolean
     * @todo 这个还没有进行缓存
     */
    private function _isMovieData($iMovieId)
    {
        $boolRes = true;
        if (empty($iMovieId))
            $boolRes = false;
        else {
            $objData = $this->model('MovieDBApp')->getOneMovieObject($iMovieId);
            if (!$objData) {//若不存在，则插入数据
                $arrInstallData = [
                    'id' => $iMovieId,
                    'baseScore' => MOVIE_BASE_SCORE_NUM,
                    'score' => MOVIE_BASE_SCORE_NUM,
                    'baseScoreCount' => MOVIE_BASE_SCORE_COUNT_NUM,
                    'baseWantCount' => MOVIE_BASE_WANT_COUNT_NUM,
                ];
                if (!$this->model('MovieDBApp')->insert($arrInstallData))
                    $boolRes = false;
            }
        }
        return $boolRes;
    }

    /**
     * @tutorial 新增、修改评分(评分人数也要+1)[对影片的评分]
     * @param array $arrData
     * @param array $commentInfo 评论信息，用于做评论筛选中的好评和差评
     * @return array
     */
    private function _saveScore($arrData)
    {
        $arrReturn = self::getStOut();
        self::_isMovieData($arrData['movieId']);
        $boolRes = self::_getScore($arrData);//格式化评分

        if ($boolRes) { //若不是数字或者评分小于0或者不在范围内，则放弃修改
            $arrData['type'] = (isset($arrData['type']) && $arrData['type'] != "") ? $arrData['type'] : 4;
            //获取当前用户对当前影片的评分（其实是用于判断有没有评分过）
            $minData = $this->model('NewScore')->queryMovieUserScore($arrData['movieId'], $arrData['ucid']);
            if ($minData) {
                $tmp = json_decode($minData, 1);
                $score = (int)$tmp['score'];
            } else {
                $score = -1;//-1其实无意义，我们只是在有分数的时候才会用到
            }
            //拿取评论信息操作好评/差评列表
            $arrCommentInfo = $this->model('NewComment')->queryCommentByUcidMovieId($arrData['ucid'], $arrData['movieId']);
            $commentInfo = $arrCommentInfo ? json_decode($arrCommentInfo, 1) : false;
            $movieId = empty($commentInfo['movieId'])?'':$commentInfo['movieId'];
            $commentId = empty($commentInfo['id'])?'':$commentInfo['id'];
            $zscore = (int)($commentInfo['baseFavorCount']+$commentInfo['favorCount']);
            if ($minData && $boolRes === true) {//更新评分表
                $arrData['updated'] = time();
                if ($this->model('NewScore')->updateScore($arrData['ucid'], $arrData['movieId'], $arrData)) {
                    self::_saveCommentScoreList($movieId,$arrData['score'],$commentId,$zscore);
                } else {
                    $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_SCORE_EDIT);
                }
            } elseif ($boolRes === true) { //新增
                $arrData['created'] = $arrData['updated'] = time();
                if ($this->model('NewScore')->insertScore($arrData)) {
                    self::_saveCommentScoreList($movieId,$arrData['score'],$commentId,$zscore);
                } else {
                    $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_SCORE_ADD);
                }
            } elseif ($minData && $boolRes == '-1') {//删除
                if ($this->model('NewScore')->delScore($arrData)) {
                    if ($commentInfo) {
                        $movieId = $commentInfo['movieId'];
                        $commentId = $commentInfo['id'];
                        $this->model('NewComment')->remBadCommentList($movieId, $commentId);
                        $this->model('NewComment')->remGoodCommentList($movieId, $commentId);
                    }
                } else {
                    $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_SCORE_ADD);//删除失败
                }
            }
        }
        return $arrReturn;
    }

    /**
     * 修改好评列表和差评列表
     * @param $movieId
     * @param $score
     * @param $commentId
     * @param favorCount
     * @return bool
     */
    private function _saveCommentScoreList($movieId,$score,$commentId,$favorCount)
    {
        if(empty($movieId) || empty($commentId) || $favorCount<0 || $score === ''){
            return false;
        }

        if ($score == 0 || $score == 20) {
            $this->model('NewComment')->remGoodCommentList($movieId, $commentId);
            $this->model('NewComment')->addBadCommentList($movieId, $favorCount, $commentId);
        }elseif ($score == 80 || $score == 100) {
            $this->model('NewComment')->remBadCommentList($movieId, $commentId);
            $this->model('NewComment')->addGoodCommentList($movieId, $favorCount, $commentId);
        }else{
            $this->model('NewComment')->remGoodCommentList($movieId, $commentId);
            $this->model('NewComment')->remBadCommentList($movieId, $commentId);
        }
    }

    /**
     * 整理评分格式，
     * @param unknown $arrData
     */
    private function _getScore(&$arrData)
    {
        $boolRes = false;
        $arrScore = ['0', '20', '40', '60', '80', '100'];
        if (is_numeric($arrData['score']) && $arrData['score'] >= 0) {
            $arrData['score'] = intval($arrData['score']);
            if (in_array($arrData['score'], $arrScore))
                $boolRes = true;
        } elseif ($arrData['score'] == '-1') {
            $boolRes = '-1';
        }
        return $boolRes;
    }


    /**
     * 获取用户信息（调用靳松的接口）
     * 说明：如果存在unionId，则证明是微信，否则的话就去靳松那兑换一下看是否是微信
     * @return Ambigous <string, array>
     */
    private function getParamOpenId(&$arrInstallData)
    {
        //微信电影票unionId为空或者其他渠道的openid和unionId都未空，则需要根据uid来获取unionId或者openid
        $arrInstallData['ucid'] = '';
        if (!empty($arrInstallData['unionId'])) {
            $arrInstallData['ucid'] = $arrInstallData['unionId'];
        } elseif (!empty($arrInstallData['openId'])) {
            $data = self::_getUserContentInfo(JAVA_API_GETUCIDBYOPENID, ['openId' => $arrInstallData['openId']]);
            $arrInstallData['ucid'] = !empty($data['uniqueId']) ? $data['uniqueId'] : $arrInstallData['openId'];
        } elseif (!empty($arrInstallData['uid'])) {
            $data = self::_getUserContentInfo(JAVA_API_GETUCIDBYUID, ['uid' => $arrInstallData['uid']]);
            $arrInstallData['ucid'] = !empty($data['openId']) ? $data['openId'] : '';
        }
    }

    /**
     * 批量获取用户信息
     * @param unknown $arrUid
     * @return multitype:\sdkService\service\array:
     */
    private function getUserInfoByArray($arrUid)
    {
        $arrData = [];
        if (!empty($arrUid) && is_array($arrUid))
            foreach ($arrUid as $val)
                $arrData[] = self::_getUserInfoByUcid($val);
        return $arrData;

    }
    /**
     * 根据用户ucid获取用户详细信息
     * @param int $iUid
     * @return array:
     */
    /*    private function _getUserInfoByUcid($strUcid, $iUid = '')
        {
            $arrUserInfo = ['nickName' => '路人甲', 'uid' => $iUid, 'ucid' => $strUcid, 'photo' => 'https://appnfs.wepiao.com/dataImage/photo.png'];
            if (!empty($strUcid)) {
                $data = self::_getUserContentInfo(JAVA_API_GETUCIDBYINFO, ['openId' => $strUcid]);
                if (!empty($data['photo']) && strpos($data['photo'], "dataImage/"))
                    $data['photo'] = 'https://appnfs.wepiao.com' . $data['photo'];
                $arrUserInfo['nickName'] = !empty($data['nickName']) ? $data['nickName'] : $arrUserInfo['nickName'];
                $arrUserInfo['photo'] = !empty($data['photo']) ? $data['photo'] : $arrUserInfo['photo'];
            }
            return $arrUserInfo;
        }*/
    /**
     * 根据用户ucid获取用户详细信息
     * @param int $iUid
     * @return array:
     */
    private function _getUserInfoByUcid($strUcid, $iUid = '')
    {
        $arrUserInfo = ['nickName' => '路人甲', 'uid' => $iUid, 'ucid' => $strUcid, 'photo' => CDN_APPNFS . '/dataImage/headphoto.png','is_star'=>0];
        if (!empty($strUcid)) {
            $data = self::_getCommentStar($strUcid);
            if(!empty($data)){
                $data['uid'] = $iUid;
                $data['is_star'] = 1;
                $arrUserInfo = $data;
            }else{
                $data = self::_getUserContentInfo(JAVA_API_GETUCIDBYINFO, ['openId' => $strUcid]);
                if (!empty($data['photo']) && strpos($data['photo'], "dataImage/") && (substr($data['photo'],0,4) != 'http'))
                    $data['photo'] = CDN_APPNFS . $data['photo'];
                if (empty($data['nickName'])) {
                    $data['photo'] = $arrUserInfo['photo'];
                }
                $arrUserInfo['nickName'] = !empty($data['nickName']) ? $data['nickName'] : self::getUserName($strUcid);
                $arrUserInfo['photo'] = !empty($data['photo']) ? $data['photo'] : $arrUserInfo['photo'];
            }
        }
        return $arrUserInfo;
    }

    /**
     * 获取明星内容
     * @param $strUcid
     */
    private function _getCommentStar($strUcid)
    {
        return $this->model('NewCommentStar')->getCommentStarByUcid($strUcid);

    }

    /**
     * 通过ucid获取用户信息
     * 入参判断顺序，unionId、openId、uid
     * 注意：此用户信息是先判断用户中心uid上是否有信息，有则返回，没有则返回对应unionid/openid的信息
     * @param $arrInput
     * @return array
     */
    public function getUserinfoByUcid($arrInput)
    {
        $resData = ['ret' => 0, 'sub' => 0];
        //参数整理
        $arrInstallData = [
            'uid' => intval(self::getParam($arrInput, 'uid')), //用户uid
            'openId' => self::getParam($arrInput, 'openId'), //用户openId
            'unionId' => self::getParam($arrInput, 'unionId'), //用户unionId
        ];
        self::getParamOpenId($arrInstallData); //获取ucid
        $ucid = !empty($arrInstallData['ucid']) ? $arrInstallData['ucid'] : '';

        $resData['data'] = self::_getUserInfoByUcid($ucid, $arrInstallData['uid']);//获取用户信息
        return $resData;
    }

    /**
     * 请求接口
     * @param $strUrl
     * @param $arrData
     * @return string
     */
    private function _getUserContentInfo($strUrl, $arrData)
    {
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = $arrData;
        $data = $this->http($strUrl, $params);
        return !empty($data['data']) ? $data['data'] : '';
    }


    /**
     * 通过commentId查询评论信息和对应的用户信息
     * @param $commentId
     */
    public function queryCommentInfoById(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'commentId' => intval(self::getParam($arrInput, 'commentId')), //用户uid
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
        ];

        $return = self::getStOut();
        $commentResult = $this->model('NewComment')->queryComment($arrInstallData['commentId']);
        if ($commentResult) {
            $arrInfo = json_decode($commentResult, 1);
            $ucid = $arrInfo['ucid'];
            $movieId = $arrInfo['movieId'];
            $userInfo = $this->_getUserInfoByUcid($ucid);
            $want = $this->model('NewWant')->queryUserIsWantMovie($ucid, $movieId) ? 1 : 0;
            $seen = $this->model('NewSeen')->queryUserIsSeenMovie($ucid, $movieId) ? 1 : 0;
            $score = $this->_getUcid2MovieIdScore($ucid, $movieId);

            $favorCount = $this->model('NewCommentFavor')->getCommentNumber($arrInstallData['commentId']); //现在是读取的redis的
            $favorCount += $arrInfo['baseFavorCount'];

            $replyCount = $this->model('NewCommentReply')->getReplyCount($arrInstallData['commentId']);
            $replyCount = $replyCount ? $replyCount : 0;//直接读取缓存

            $return['data'] = [
                'comment' => $arrInfo['content'],
                'userName' => $userInfo['nickName'],
                'photo' => $userInfo['photo'],
                'want' => $want,
                'seen' => $seen,
                'score' => $score,
                'favorCount' => $favorCount,
                'replyCount' => $replyCount,
            ];
        } else {
            $return = self::getErrorOut(ERRORCODE_COMMENT_NOT_EXISTS_ERROR);//评论信息不存在
        }
        return $return;
    }



    ###############################################################  以下为脚本为baymax调用的   ########################################################


    /**
     * 管理员屏蔽评论
     * @param commentId int 必须
     * @param channelId int 必须
     *
     */
    public function managerDelComment($arrInput)
    {
        //参数整理
        $arrInstallData = [
            'commentId' => intval(self::getParam($arrInput, 'commentId')), //
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
        ];
        $arrReturn = self::getStOut();
        if (!empty($arrInstallData['commentId']) && !empty($arrInstallData['channelId'])) {
            if (!$this->model('NewComment')->managerScreen($arrInstallData['commentId']))
                $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_EDIT);
        } else $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_EDIT);
        return $arrReturn;
    }

    /**
     * 管理员修改评论(内容和注水数)
     * @param commentId int 必须
     * @param channelId int 必须
     *
     */
    public function managerEditComment($arrInput)
    {
        //参数整理
        $arrInstallData = [
            'commentId' => intval(self::getParam($arrInput, 'commentId')), //
            'content' => self::getParam($arrInput, 'content'), //
            'baseFavorCount' => self::getParam($arrInput, 'baseFavorCount'), //
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
        ];
        if (isset($arrInstallData['content']) && $arrInstallData['content'] == "") {
            unset($arrInstallData['content']);
        }
        if (isset($arrInstallData['baseFavorCount']) && $arrInstallData['baseFavorCount'] == "") {
            unset($arrInstallData['baseFavorCount']);
        }
        $arrReturn = self::getStOut();
        if (!empty($arrInstallData['commentId']) && !empty($arrInstallData['channelId'])) {
            if (!$this->model('NewComment')->managerUpdateComment($arrInstallData['commentId']))
                $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_EDIT);
            //增加一个改变后的注水喜欢数
            if(isset($arrInstallData['baseFavorCount'])){
                $this->model("NewCommentFavor")->addChangeSet($arrInstallData['commentId']);
            }
        } else $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_EDIT);
        return $arrReturn;
    }

    /**
     * 管理员屏蔽回复
     * @param commentId int 必须
     * @param channelId int 必须
     *
     */
    public function managerDelReply($arrInput)
    {
        //参数整理
        $arrInstallData = [
            'replyId' => intval(self::getParam($arrInput, 'replyId')),
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
        ];
        $arrReturn = self::getStOut();
        if (!empty($arrInstallData['replyId']) && !empty($arrInstallData['channelId'])) {
            if (!$this->model('NewCommentReply')->managerScreen($arrInstallData['replyId']))
                $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_REPLY_EDIT_ERROR);
        } else $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_REPLY_EDIT_ERROR);
        return $arrReturn;
    }

    //设置热门评论的值
    public function setHotCommentOrder($arrInput)
    {
        $movieId = $arrInput['movieId'];
        $score = $arrInput['score'];
        $commentId = $arrInput['commentId'];
        $this->model('NewComment')->setHotCommentOrder($movieId, $score, $commentId);//此方法返回值如果是修改，都是0
        return self::getStOut();
    }

    /**
     * 明星评论相关
     * @param $arrInput
     * @return array
     * @throws \Exception
     */
    public function saveCommentStar($arrInput)
    {
        $ucid = self::getParam($arrInput, 'ucid');
        $status = self::getParam($arrInput, 'status');
        $content = self::getParam($arrInput, 'content');
        $arrReturn = self::getStOut();
        if(empty($ucid) || ($status == 1 && empty($content))){
            //@todo 错误记录
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_REPLY_EDIT_ERROR);
        }else{
            if($status == 1){
                //save
               if(!$this->model('NewCommentStar')->saveRedis($ucid,$content)){
                   $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_REPLY_EDIT_ERROR);
               }
            }else{
                //del
                if(!$this->model('NewCommentStar')->delRedis($ucid)){
                    $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_REPLY_EDIT_ERROR);
                }
            }
        }
        return $arrReturn;
    }

    /**
     * 影片注水
     * @param $arrInput
     * @return array
     * @throws \Exception
     */
    public function saveMovieBaseScore($arrInput)
    {
        $movieId = intval(self::getParam($arrInput, 'movieId'));
        $content = self::getParam($arrInput, 'content');
        $content = json_decode($content,true);
        $arrReturn = self::getStOut();
        if(empty($content) || empty($movieId)){
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_REPLY_EDIT_ERROR);
        }else{
            if(! $this->model('MovieDBApp')->saveMovieBaseScore($movieId,$content)){
                $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_REPLY_EDIT_ERROR);
            }
        }
        return $arrReturn;
    }


    /**
     * 取模生成没有昵称的用户的昵称
     * @param $ucid
     * @return mixed
     */
    private function getUserName($ucid)
    {
        $arrData = [
            '捉影联','陷阱密码','张敌手','安迪0987','大平原7174520','收了的人','william要','小朵0206','墨qingqingqing','jingling只要',
            'roma1999','上课68573020','tingting178','cry啥0215','Elohim忽然','some打印','i投了','venic372','vampirersdf419','帥Wong330',
            '我567','看是可以891013','我基本波波','温柔2300','算法14837','巷尾猫的马甲','额样品902678451','赶上的饭12','warden超','罗东waste',
            '王一飞553','360収16367','角搜哈哈','温度i917468470','爱V刹1772970','劢高峰27','孙嘉敏你好么','甫翻翻','大小袍子','unrelatedl03',
            '过八一645','峰哥奕云','V字3804','瑟兰young','琅琊山250','矮小00yu','人磷矿00','苟一巴17','EK乐然8621','pangpang395',
            '可爱song49','台风天2404','坦克1119','硬币hyi','rioero23','李睿mumumu','steveneightsixcn','小马驹','屋子里的房间','夹芯板j123',
            'zh胡长三','不走389','指摘11','jesscial咯咯','破译密码120','拉兹20009','杨凯ikmb','顶风673','模块化14','你说话啊295',
            '塑唐哦哦','偶数蛋232','银英449','游泳圈2018','hqj168','hiking52','V字票2','邬倩倩0612','大大加菲','IDL177',
            '张赛80900','smile汗i2007','么么茶北泉','张_小小','五天905413','行走的暴力姐511','透明贴后面','盐城人机器','邓婕英337','wakayi大',
            '夏花泛滥','大斌82','风中脓啦啦','狂魔xul','裤裤1063','哦翻白眼of','心人940181','skeed444','王文斌9006','冰漪2004',
            'only19901212','苏俩次','精灵王7374','I派1129','错彩镂金123','拉车20','熊快快','牛皮面813152217','卡努002854','小雁南燕',
            '少羽281','李博爱萌萌哒','如水26','童童975','00sail00','本咯vb','Wong3波波','半夏等风吹','吹跑阿婆2011','qqqq6285',
            '最简单_346175710','凌龙成0101','网好礼394','连晨宇','vGZ888946941','Pom艾斯卡','奔奔250','COC1391457587','望月1211','费列罗001',
            '加百媚','搁在531500','猜卿卿','皇后nNNN','sky透明片','十19儿82','Love白豆腐','冶机械','吃东西的小萝莉','请假条3730',
            '黄点123','ar_春','via1500XXX283','很骄傲tc2000','b124人都是','看见哦712','风吹大柳树','往前飞21868346','没_onroe_我','探险票快递',
            'alex0018','胡桃夹子998','全欧洲347761','吃00121110','打天下947611561','好人475','i没人r5632','路易威斯GUI','聪明人429056399','满是0452',
            '哟哟芳草香','小钢887','亲民宝宝806','TUB9808081','star是','儿啊才274','Dori说','反攻为受的一品','热宝3496','winner孙0822',
            '事2622','围围巾7','卿丹师','玛特24259','win呢857','天黑250','360U361','撒让say','呵护70','devil小小',
            '梨花liye','璎珞倩倩','敞篷E6004','v1345372','华华890','欧洲2014','不哈皮774','火星人088','冰窟2004','妮妮身高差',
            '不吃fish的人','Irish樱花','劳伦斯106564','限制1089','啊hi家','砂砾_901','周末婚礼进行时','彩虹他爹586','卡通00088','蓝波织梦',
            '螺积分','森的生活','天然萌萌哒','补下56','蘑菇也跌哟','招聘1024','赵琳ia','QQ秀323','藏宝湾19920808','凯1466',
            '罗叶如今','螺蛳粉90808','风筝遇强敌','蜂蜜1402','对爱情钱','南餐厅Lulu','梨花668','烤鱼店小伙计','车裂30269','拥有人格的鬼混',
            '小海08080909','zj_田坤','3快1502','Clemente0610','吃吃不不','妮妮53021','卡通画一06061','一于承妍','阿玛软开关','量规鸣',
            '静儿candy','没啥大事','一天了231','幻想家27','八卦格ion','波违反2010','讨好木易','妖妖20021122','木枋27','唐宇焖鸡',
            '小一届','燯814','鬼混爱木马','电压993','刺激我233','君莫狂look','我叫莫小莫','无样品336','扬言0714','掌控的的79',
            '绿u1694','杨彦4','我呸1','深Vvv','干脆面浣熊家','别闹寳貝','V型天体','Doray123','拳拳之心快','幺儿不起',
            '齐齐在本片','正能量小霸王','差纷纷00','谢谢184','匡匡0101','芐331','飘呀飘81','费恒基','羽毛bird灰起来','皇家血统16',
            '泸天化94','阿姐24','瑟拉尼娜','龙猫君在大帝都','关遵10','转弯速度241','勤杂工000','秘密特工008','画皮9009','两厢车024',
            '旪37','嗐星舞盟','了教授32','诗意化浓','博士生77','唔卉鬓56','无留言板','银星太保','辣椒姊','美瞳1110',
            '辅导员68','葱8491','木屐哟哟','Francisco寳寳','兔子噗524','咖喱皽222','双眼睛的见','鸭头87','天秤座82','向前冲4123',
            '沟通瑟尔','周训卓咯咯','vrr62','李俊梅37','校服507','婕拉47','囜9083','许西村10','孙瑾147','依亲n0678',
            '龙志子','go块66','数控imati','长沙碎碎','陪我飞306','柒沛78','A站小火电','温泉乡05','Caza41','炮兵团19',
            '馥香9170','涩琪686','邵丕丞相','滑板车看着','热死啦1715','珉溪寳寳','杨彦0714','雯雯_long','甑155455','提哦323',
            '蓝光卡科普','牧羊女29','乌苏里江头','飞飞风铃','唐宇要你从了','毛虾233','答题卡99','貝貝宁03','在欧22','未名湖8087',
            'grace茵','To_Viola11','马霞455','抽屉075','樱花mating','兰兰书','李小婧Mdi','天空ly','刘林欣02','ogham49',
            '鄂喏多姿','里欧上学呢','沛儿280','凌芳QQ','wod0024','虎牙蝉蜕','微微盗来','火星人编号10103','纷纷喵喵','淘气鬼one个',
            '李小xing','晨铎天','敞篷车875','吐槽诺820','藍染bleach','仙人掌0408','赳秦人未老','应雨露水','乌诺凡','棉手套3851',
            '黄小豆556','关遵000','约翰Tupac','资格1202','阳光小汪','angle爱beauty','鞥0980','包梗ion210','蔡锷81','幽灵公主22',
            '廿四味2507','天天向上糖糖','小小诖','小园子GG','缓了缓','粉粉5566','chris章','善恶r9','微波炉小饼','陈怡0601',
            '文旦柚68','YY桃源','晓晓芳玲玲','过谎言','NBA83','紫百合699','恋人恋爱880','不开森11','陈于鏊307','光滑滑露露',
            '圣母马力丫','我家ctrl坏了','奈奈喝奶奶','聊聊李无锋','黄必箐','五段五角','lee.王','岁岁氨','威图4033','耷拉22',
            '螺母9809','happyE叉叉','咯阿春1515','liyina笑笑','何处怜尘埃','关泽楠54','爱爱djq','吃肉的小白牙','妖精1100','孙博酥饼',
            '搜温柔93','几维鸟798','审完在对I13','Peterlalala','UFO没看见','格格屋家碗','看剧city','阿谀12098','乱七八糟88225','kiki咧了咧723',
            '如来福大帐','破魔锤11','车牌架7977','弹幕刷屏','哥斯拉小伙伴','阿拉蕾小帽子','fassbendersha','木鱼家木有余粮','猩猩狒狒2929','遥遥小冰冰439',
            '吴xx呜呜','小小米喜欢吃肉','发奋学习好好奋斗','吴咪咪88','小池做实验','青花瓷557','老罗家的苦逼','余名飞嚄','双人士称','主播ILOVEYOU',
        ];
        return $arrData[abs(crc32($ucid))%450];
    }
}