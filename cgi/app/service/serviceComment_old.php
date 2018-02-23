<?php

namespace sdkService\service;


/**
 * 所有基于评分评论体系的操作都放在这里面
 * Class serviceComment
 * @package sdkService\service
 */
class serviceComment extends serviceBase
{
    const ERROR_CODE_STATUS = true;//错误编码状态

    static $levelDown = false;//评论降级

    //降级函数
    public function levelDown($params){
        return self::getStOut();
    }

    /**
     * @tutorial 添加、修改评论
     * 说明：由于之前版本都是使用的uid，数据库中存储的也是用户的uid，故此，这里也是使用uid，望使用者确认
     * @param  int   uid     用户uid
     * @param  int   movieId 影片的id（数字的那个）
     * @param  int   channelId  渠道
     * @param  int   from    子渠道
     * @param  int   score   评分
     * @param  int   type    表情类型
     * @param string content 内容
     * @param string openId openId
     * @param string unionId unionId
     * OK
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
        ];
        $arrInstallData = self::getParamOpenId($arrInstallData);
        $arrInstallData['checkstatus'] = 0;//默认值
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['ucid']) || empty($arrInstallData['movieId']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        }
        //todo 黑名单还未确定是否封禁uid还是ucid
        //if (empty($arrReturn['ret']) && self::isBalckList($arrInstallData['uid'])){//黑名单
        //    $arrReturn = self::getErrorOut(ERRORCODE_BLACK_USER_COMMENT);
        //}
        if (empty($arrReturn['ret'])) //屏蔽词
            $arrInput = self::isShieldingWords($arrInstallData['content']);
        if (empty($arrReturn['ret']) && self::isSensitiveWords($arrInstallData['content']))//敏感词
            $arrInstallData['checkstatus'] = 3;
        if (empty($arrReturn['ret']) && !self::isLastComment($arrInstallData['ucid'], $arrInstallData['movieId'])) {//是否能发或改当前评论
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_UPDATE_TIME_WORE);
        }
        //判断是更新还是新增
        if (empty($arrReturn['ret']))
            $arrReturn = self::saveComment($arrInstallData);
        return $arrReturn;
    }

    /**
     * @tutorial 添加回复--现在不支持修改回复，
     * 说明：由于之前版本都是使用的uid，数据库中存储的也是用户的uid，故此，这里也是使用uid，望使用者确认
     * @param  int   uid     用户uid
     * @param  int   commentId 评论id（数字的那个）
     * @param  int   channelId  渠道
     * @param  int   from    子渠道
     * @param string content 内容
     * @param string openId openId
     * @param string unionId unionId
     * OK
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
        $arrInstallData = self::getParamOpenId($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['ucid']) || empty($arrInstallData['commentId']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        }
        //黑名单临时去掉，需要修改baymanx
        //if (empty($arrReturn['ret']) && self::isBalckList($arrInstallData['ucid'])){//黑名单
        //    $arrReturn = self::getErrorOut(ERRORCODE_BLACK_USER_COMMENT);
        //}
        if (empty($arrReturn['ret'])) //屏蔽词
            $arrInput = self::isShieldingWords($arrInstallData['content']);
        if (empty($arrReturn['ret']) && self::isSensitiveWords($arrInstallData['content']))//敏感词
            $arrInstallData['checkstatus'] = 3;
        if (empty($arrReturn['ret']) && !self::isLastReply($arrInstallData['ucid'])) {//是否能发当前回复【回复不能修改，只能发】
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_UPDATE_TIME_WORE);
        }
        if (empty($arrReturn['ret'])) {//发回复
            if ($this->model("CommentReply")->insert($arrInstallData)) {//插入
                if (!$this->model("Comment")->saveParamNum(['replyCount' => 1], $arrInstallData['commentId']))//回复数+1
                    $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_REPLY_COMMENT_ERROR);
            } else $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_REPLY_ADD_ERROR);
        }

        return $arrReturn;
    }

    /**
     * @tutorial 给影片评分(或者修改评分)
     * 说明：由于之前版本都是使用的uid，数据库中存储的也是用户的uid，故此，这里也是使用uid，望使用者确认
     * @param  int   uid     用户uid
     * @param  int   movieId 影片id（数字的那个）
     * @param  int   score  分数（0|100）
     * @param  int   type    表情类型
     * @param  int   channelId  渠道
     * @param  int   from    子渠道
     * @param string openId openId
     * @param string unionId unionId
     * OK
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
        $arrInstallData = self::getParamOpenId($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['ucid']) || empty($arrInstallData['movieId']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        } else $arrReturn = self::_saveScore($arrInstallData);
        return $arrReturn;
    }

    /**
     * @tutorial 想看&取消想看接口
     * 说明：由于之前版本都是使用的uid，数据库中存储的也是用户的uid，故此，这里也是使用uid，望使用者确认
     * @param  int   uid     用户uid
     * @param  int   movieId 影片id（数字的那个）
     * @param  int   want    是否想看，1：想看，0：取消想看。
     * @param  int   channelId  渠道
     * @param  int   from    子渠道
     * @param string openId openId
     * @param string unionId unionId
     * OK
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
        $arrInstallData = self::getParamOpenId($arrInstallData);
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
     * OK
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
        $arrInstallData = self::getParamOpenId($arrInstallData);
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
     * 说明：由于之前版本都是使用的uid，数据库中存储的也是用户的uid，故此，这里也是使用uid，望使用者确认
     * @param  int   uid     用户uid
     * @param  int   commentId 评论id（数字的那个）
     * @param  int   channelId  渠道
     * @param  int   from    子渠道
     * @param  int   favor   点赞、去掉点赞，1：点赞，0：取消点赞.
     * @param string openId openId
     * @param string unionId unionId
     * OK
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
        $arrInstallData = self::getParamOpenId($arrInstallData);
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
     * 说明：由于之前版本都是使用的uid，数据库中存储的也是用户的uid，故此，这里也是使用uid，望使用者确认
     * @param  int   uid     用户uid
     * @param  int   movieId 影片id（数字的那个）
     * @param  int   want    是否想看，1：想看，0：看过。
     * @param  int   channelId  渠道
     * @param  int   from    子渠道
     * @param string openId openId
     * @param string unionId unionId
     * OK
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
        $arrInstallData = self::getParamOpenId($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['ucid']) || empty($arrInstallData['movieId']) || empty($arrInstallData['channelId'])) { //参数空校验

            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        } else {
            $arrComment = $this->model('Comment')->getUserCommentArray($arrInstallData['ucid'], $arrInstallData['movieId']);
            if ($arrComment) {
                $arrComment['favorCount'] = $this->model('CommentFavor')->getCommentFavorNums($arrComment['id']);
                $arrComment['favorCount'] += $arrComment['baseFavorCount'];
                $arrComment['favor'] = self::_getUserReplyComment($arrInstallData['ucid'], $arrComment['id']);
            }
            $arrScore = $this->model('Score')->getUserScoreArray($arrInstallData['ucid'], $arrInstallData['movieId']);
            $arrWant = $this->model('Want')->isUsertWantMovie($arrInstallData['ucid'], $arrInstallData['movieId']);
            $arrSeen = $this->model('Seen')->isSeen($arrInstallData['ucid'], $arrInstallData['movieId']);
            $data = [
                'comment' => !empty($arrComment) ? $arrComment : (object)[],
                'score' => !empty($arrScore) ? $arrScore : (object)[],
                'want' => $arrWant ? 1 : 0,
                'seen' => $arrSeen ? 1 : 0,
            ];
            $arrReturn['data'] = $data;
        }
        return $arrReturn;
    }

    /**
     * @tutorial 获取某个影片的评论列表- 最新、热评等
     * 说明：由于之前版本都是使用的uid，数据库中存储的也是用户的uid，故此，这里也是使用uid，望使用者确认
     * @param  int   uid     用户uid 非必填
     * @param  int   movieId 影片id（数字的那个）
     * @param  int   channelId  渠道
     * @param  int   from    子渠道
     * @param  int   page   页数
     * @param  int   num    条数（每页多少条）
     * @param  string  sortBy 排序方式（new:最新评论，reply：回复数，favor：喜欢数，time:时间维度） 都是DESC排序
     * @param string openId openId
     * @param string unionId unionId
     * OK
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
        $arrInstallData = self::getParamOpenId($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['movieId']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        } else {
            $arrComment = [];
            switch ($arrInstallData['sortBy']) {
                case 'new':
                    $arrComment = $this->model('Comment')->getCommentNewList($arrInstallData['movieId'], $arrInstallData['page'], $arrInstallData['num']);//列表
                    break;
                case 'reply':
                    $arrComment = $this->model('Comment')->getCommentReplyList($arrInstallData['movieId'], $arrInstallData['page'], $arrInstallData['num']);//列表
                    break;
                case 'favor':
                    $arrComment = $this->model('Comment')->getCommentFavorList($arrInstallData['movieId'], $arrInstallData['page'], $arrInstallData['num']);//列表
                    break;
                case 'time':
                    $arrComment = $this->model('Comment')->getCommentTimeList($arrInstallData['movieId'], $arrInstallData['page'], $arrInstallData['num']);//列表
                    break;
                default://默认按照new排序
                    $arrComment = $this->model('Comment')->getCommentNewList($arrInstallData['movieId'], $arrInstallData['page'], $arrInstallData['num']);//列表
            }
            if (!empty($arrComment['comments']))
                $arrComment['comments'] = $this->getCommentListUserFavorData($arrComment['comments'], $arrInstallData['ucid'], true);
            $arrReturn['data'] = $arrComment;
        }
        return $arrReturn;
    }

    /**
     * @tutorial 获取指定评论的回复列表
     * 说明：由于之前版本都是使用的uid，数据库中存储的也是用户的uid，故此，这里也是使用uid，望使用者确认
     * @param  int   uid     用户uid 非必填
     * @param  int   commentId 评论ID（数字的那个）
     * @param  int   channelId  渠道
     * @param  int   from    子渠道
     * @param  int   page   页数
     * @param  int   num    条数（每页多少条）
     * @param string openId openId
     * @param string unionId unionId`
     * @todo
     * OK
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
        $arrInstallData = self::getParamOpenId($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['commentId']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        } else {
            $arrData = $this->model('CommentReply')->getCommentReplyList($arrInstallData['commentId'], $arrInstallData['page'], $arrInstallData['num']);//列表
            if (!empty($arrData['replies']))
                $arrData['replies'] = $this->getReplyListUser($arrData['replies']);
            $arrData['comment'] = self::getCommentInfoById($arrInstallData['commentId']);
            $arrReturn['data'] = $arrData;
        }
        return $arrReturn;
    }

    /**
     * @tutorial 获取看过指定电影的用户
     * 说明：由于之前版本都是使用的uid，数据库中存储的也是用户的uid，故此，这里也是使用uid，望使用者确认
     * @param  int   movieId     电影Id（数字）
     * @param  int   channelId  渠道
     * @param  int   from    子渠道
     * @param  int   page   页数
     * @param  int   num    条数（每页多少条）
     * @param string openId openId
     * @param string unionId unionId
     * OK
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
            $arrComment = $this->model('Seen')->getMovieSeenUserList($arrInstallData['movieId'], $arrInstallData['page'], $arrInstallData['num']);//列表

            if (!empty($arrComment['users']))
                $arrComment['users'] = self::getUserInfoByArray($arrComment['users']);
            $arrReturn['data'] = $arrComment;
        }
        return $arrReturn;
    }

    /**
     * @tutorial 获取我想看的电影
     * 说明：由于之前版本都是使用的uid，数据库中存储的也是用户的uid，故此，这里也是使用uid，望使用者确认
     * @param  int   uid     用户uid 非必填
     * @param  int   channelId  渠道
     * @param  int   from    子渠道
     * @param  int   page   页数
     * @param  int   num    条数（每页多少条）
     * @param string openId openId
     * @param string unionId unionId
     * OK
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
        $arrInstallData = self::getParamOpenId($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['ucid']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        } else {
            $arrData = $this->model('Want')->getUserWantMovieList($arrInstallData['ucid'], $arrInstallData['page'], $arrInstallData['num']);//列表
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
     * OK
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
            'openId' => intval(self::getParam($arrInput, 'openId')), //用户openId
            'unionId' => intval(self::getParam($arrInput, 'unionId')), //用户unionId
        ];
        $arrInstallData = self::getParamOpenId($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['ucid']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        } else {
            $arrComment = $this->model('Seen')->getUserSeenMovieList($arrInstallData['ucid'], $arrInstallData['page'], $arrInstallData['num']);//列表
            $arrReturn['data'] = $arrComment;
        }
        return $arrReturn;
    }


    /**
     * @tutorial 指定用户是否评论过当前影片
     * 说明：由于之前版本都是使用的uid，数据库中存储的也是用户的uid，故此，这里也是使用uid，望使用者确认
     * @param  int   uid     用户uid 非必填
     * @param  int   movieId     电影Id（数字）
     * @param  int   channelId  渠道
     * @param  int   from    子渠道
     * @param string openId openId
     * @param string unionId unionId
     *
     */

    public function isUserCommentMovie(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'movieId' => intval(self::getParam($arrInput, 'movieId')),
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
            'uid' => intval(self::getParam($arrInput, 'uid')), //用户uid
            'openId' => intval(self::getParam($arrInput, 'openId')), //用户openId
            'unionId' => intval(self::getParam($arrInput, 'unionId')), //用户unionId
        ];
        $arrInstallData = self::getParamOpenId($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['movieId']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        } else {
            $arrComment = $this->model('Comment')->getUserCommentArray($arrInstallData['ucid'], $arrInstallData['movieId']);
            $arrReturn['data'] = empty($arrComment) ? [] : $arrComment;
        }
        return $arrReturn;
    }


    /**
     * 根据commentId获取评论的信息
     * @param int $iCommentId
     * @return array
     */
    private function getCommentInfoById($iCommentId)
    {
        $arrComment = [];
        if (!empty($iCommentId)) {
            $arrComment = $this->model('Comment')->getCommentInfoByIdArray($iCommentId);
            if (!empty($arrComment)) {
                $arrComment['favorCount'] += $arrComment['baseFavorCount'];
                unset($arrComment['baseFavorCount']);
            }
        }
        return $arrComment;
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
                    $val = false;
                    continue;
                }
                $val = $this->model('Comment')->getCommentInfoByIdArray($val);
                if (empty($val)) {
                    $val = false;
                    continue;
                }
                $val['favorCount'] += $val['baseFavorCount'];
                unset($val['baseFavorCount']);
                unset($val['movieName']);
                $val['user'] = self::_getUserInfoByUcid($val['ucid'], $val['uid']);//获取用户信息
                $val['favor'] = self::_getUserReplyComment($strUcid, $val['id']);
                $val['score'] = self::_getUcid2MovieIdScore($val['ucid'], $val['movieId']);
            }
            $arrComment = array_filter($arrComment);
        } else $arrComment = [];
        return $arrComment;
    }

    private function _getUcid2MovieIdScore($strUcid, $iMovieId)
    {
        if (empty($strUcid) || empty($iMovieId))
            return -1;
        $arrayData = $this->model('Score')->getUserScoreArray($strUcid, $iMovieId);
        return isset($arrayData['score']) ? $arrayData['score'] : -1;
    }

    /**
     * 整理回复的列表格式
     * @param array $arrRepiles
     *
     */
    private function getReplyListUser($arrReply)
    {
        if (!empty($arrReply)) {
            foreach ($arrReply as &$val) {
                if (empty($val)) {
                    $val = false;
                    continue;
                }
                $val = $this->model('CommentReply')->getOneReplyArray($val);
                if (empty($val) || empty($val['status'])) {
                    $val = false;
                    continue;
                }
                $val['user'] = self::_getUserInfoByUcid($val['ucid'], $val['uid']);//获取用户信息
            }
            $arrReply = array_filter($arrReply);
        } else $arrReply = [];
        return $arrReply;
    }

    /**
     *  判断指定用户是否赞过指定评论
     * @param unknown $iUid
     * @param unknown $iCommentId
     * @return number
     */
    private function _getUserReplyComment($strUcid, $iCommentId)
    {
        $iStatus = 0;
        if (!empty($strUcid) && !empty($iCommentId)) {
            if ($this->model('CommentFavor')->isFavor($strUcid, $iCommentId))
                $iStatus = 1;
        }
        return $iStatus;
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
    private function _getUserInfoByUcid($strUcid, $iUid = '')
    {
        $arrUserInfo = ['nickName' => '路人甲', 'uid' => $iUid, 'ucid' => $strUcid, 'photo' => CDN_APPNFS . '/dataImage/photo.png'];
        if (!empty($strUcid)) {
            $data = self::_getUserContentInfo(JAVA_API_GETUCIDBYINFO, ['openId' => $strUcid]);
            if (!empty($data['photo']) && strpos($data['photo'], "dataImage/"))
                $data['photo'] = CDN_APPNFS . $data['photo'];
            $arrUserInfo['nickName'] = !empty($data['nickName']) ? $data['nickName'] : $arrUserInfo['nickName'];
            $arrUserInfo['photo'] = !empty($data['photo']) ? $data['photo'] : $arrUserInfo['photo'];
        }
        return $arrUserInfo;
    }

    /**
     * @tutorial 用户是否在黑名单
     * @param unknown $uid
     * @return bool T or F
     */
    private function isBalckList($uid)
    {
        $arrBlackList = $this->model('BlackList')->getBlackList();
        $boolRes = false;
        if (!empty($arrBlackList) && !empty($uid)) {
            if (in_array($uid, $arrBlackList))
                $boolRes = true;
        }
        return $boolRes;
    }

    /**
     * @tutorial 屏蔽词判断，是否包含屏蔽词
     * @param string $strContent
     * @return Ambigous <boolean, unknown>
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
     * @tutorial 是否能发当前评论：基于时间判断
     * @param int $iUid
     * @param int $iMovieId
     * @return boolean
     */
    private function isLastComment($strUcid, $iMovieId)
    {
        $boolRes = true;
        return true;
        //todo 临时去掉发评论的时间限制
        $objObject = $this->model('Comment')->getOneCommentObject("ucid = '$strUcid' AND movieId ='$iMovieId'", null, '*', 'updated DESC');
        if ($objObject) {
            if (($objObject->updated + COMMENT_UPDATE_TIME) <= time())
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
        return $boolRes;
        //todo 暂时去掉时间限制
        $objObject = $this->model('CommentReply')->getOneReplyObject("ucid = '$strUcid'", null, '*', "updated DESC");
        if ($objObject) {
            if (($objObject->updated + COMMENT_REPLY_UPDATE_TIME) <= time())
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
        $objData = $this->model('Comment')->getUserMovieCommentObject($arrInstallData['ucid'], $arrInstallData['movieId']);
        self::_isMovieData($arrInstallData['movieId']);
        $commentId = 0;
        if ($objData) {//
            if ($objData->status == 0) { // 已被删除的评论
                $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_DELETE);
            } else { //更新
                $commentId = $objData->id;
                $boolUpdate = $this->model('Comment')->update($arrInstallData, $objData->id);
                if (!$boolUpdate) {//更新失败：
                    $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_EDIT);
                }
            }
        } else {//新增
            $boolUpdate = $this->model('Comment')->insert($arrInstallData);
            if ($boolUpdate) {//插入成功，评论数+1
                $commentId = $boolUpdate;
                $boolInsert = $this->model('MovieDBApp')->saveParamNum(['commentCount' => 1], $arrInstallData['movieId']);
                if (!$boolInsert)
                    $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_EDIT_PARAM_COMMENT); // +1更新失败
                if (!$this->model('Want')->isUsertWantMovie($arrInstallData['ucid'], $arrInstallData['movieId'])) //如果没有想看数据
                    $arrReturn = self::insertSeen($arrInstallData);//默认插入看过，如果以前是想看，则不处理
            } else
                $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_ADD);// 新增失败
        }
        if (empty($arrReturn['ret']) && isset($arrInstallData['score'])) {//计算评分
            $arrReturn = self::_saveScore($arrInstallData);
        }
        if (!empty($commentId)) {
            $arrReturn['data'] = ['commentId' => $commentId];
        }
        return $arrReturn;
    }

    /**
     * @tutorial 新增、修改评分(评分人数也要+1)[对影片的评分]
     * @param array $arrData
     * @return array
     */
    private function _saveScore($arrData)
    {
        $arrReturn = self::getStOut();
        self::_isMovieData($arrData['movieId']);
        $arrData = self::_getScore($arrData);//格式化评分
        if ($arrData) { //若不是数字或者评分小于0，则放弃修改
            $arrData['score'] = intval($arrData['score']);
            $objData = $this->model('Score')->getUserScoreMovieObject($arrData['ucid'], $arrData['movieId']);
            if ($objData) {//更新评分表
                if (!$this->model('Score')->update($arrData))
                    $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_SCORE_EDIT);
            } else { //新增
                if (!$this->model('Score')->insert($arrData))
                    $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_SCORE_ADD);
                elseif (!$this->model('MovieDBApp')->saveParamNum(['scoreCount' => 1], $arrData['movieId']))//评分人数+1
                    $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_SCORE_ADD_ERROR);
            }
            if (empty($arrReturn['ret']))//计算评分
                $arrReturn = self::_getMovieScore($arrData['movieId']);//计算评分
        }
        return $arrReturn;
    }

    /**
     * 整理评分格式，
     * @param unknown $arrData
     */
    private function _getScore($arrData)
    {
        $minRes = false;
        $arrScore = ['0', '20', '40', '60', '80', '100'];
        if (is_numeric($arrData['score']) && $arrData['score'] >= 0) {
            $arrData['score'] = intval($arrData['score']);
            //if ($arrData['score'] > 10)
            //    $arrData['score'] = intval($arrData['score']/10);
            if (in_array($arrData['score'], $arrScore))
                $minRes = $arrData;
        }
        return $minRes;
    }

    /**
     * @tutorial 删除评分  -- 新功能不能删除评分了，继续保留 --该功能临时废弃，但是不要删除，说不定什么时候又要了
     * @param array $arrData
     * @return array
     */
    private function _delScore($arrData)
    {
        $arrReturn = self::getStOut();
        $objData = $this->model('Score')->getUserScoreMovieObject($arrData['uid'], $arrData['movieId']);
        if ($objData) {//删除
            if ($this->model('Score')->deleteScore($arrData['uid'], $arrData['movieId'])) {//删除评分
                if (!$this->model('MovieDBApp')->saveParamNum(['scoreCount' => -1], $arrData['movieId']))//评分人数-1
                    $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_SCORE_ADD_ERROR);
            } else $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_SCORE_DELTTE_ERROR);
            if (empty($arrReturn['ret']))//计算评分
                $arrReturn = self::_getMovieScore($arrData['movieId']);//计算评分
        }
        return $arrReturn;
    }

    /**
     * @tutorial计算评分
     * @param int $iMovieId
     * @return array
     */
    private function _getMovieScore($iMovieId)
    {
        $arrReturn = self::getStOut();
        $objData = $this->model('MovieDBApp')->getOneMovieObject($iMovieId);
        if ($objData) {
            $scoreCount = $this->model('Score')->getMovieScoreCount($iMovieId);
            $scoreSum = $this->model('Score')->getMovieScoreSum($iMovieId);
            if ($objData->baseScoreCount + $scoreCount != 0) {//更新
                $iScore = intval(($scoreSum + $objData->baseScoreCount * $objData->baseScore) / ($scoreCount + $objData->baseScoreCount));//分数计算
                $this->model('MovieDBApp')->update(['score' => $iScore], $iMovieId);
                //todo 临时这么修改
                //if (!$this->model('MovieDBApp')->update(['score'=>$iScore],$iMovieId))
                //    $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_SCORE_ALL);
            }
        }
        return $arrReturn;
    }

    /**
     * @tutorial 判断影片是否存在，若不存在，则插入一条
     * @param int $iMovieId
     * @return boolean
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
     * @tutorial 插入想看，想看和看过互斥,所以要删除看过
     * @param array $arrData
     */
    private function insertWant($arrData)
    {
        $arrReturn = self::getStOut();
        self::_isMovieData($arrData['movieId']);
        if (!$this->model('Want')->isUsertWantMovie($arrData['ucid'], $arrData['movieId'])) {//没有想看数据，则进行增加想看
            if ($this->model('Want')->insert($arrData)) {//插入想看数据
                if ($this->model("MovieDBApp")->saveParamNum(['wantCount' => 1], $arrData['movieId'])) {//想看+1
                    $arrReturn = self::_delSeen($arrData);//删除看过:想看和看过互斥。
                } else {//取消所有的看过
                    $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_WANT_COUNT_ERROR);
                }
            } else $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_WANT_ADD_ERROR);
        }//已经是想看了，就不用处理了
        return $arrReturn;
    }

    /**
     * @tutorial 插入看过
     * @param array $arrData
     */
    private function insertSeen($arrData)
    {
        $arrReturn = self::getStOut();
        self::_isMovieData($arrData['movieId']);
        if (!$this->model('Seen')->isSeen($arrData['ucid'], $arrData['movieId'])) {//没有看过
            if ($this->model('Seen')->insert($arrData)) {//插入想看数据
                if ($this->model("MovieDBApp")->saveParamNum(['seenCount' => 1], $arrData['movieId'])) {//想看+1
                    $arrReturn = self::delWant($arrData);//删除想看:想看和看过互斥。
                } else {//取消所有的看过
                    $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_WANT_COUNT_ERROR);
                }
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
        if ($this->model('Want')->isUsertWantMovie($arrData['ucid'], $arrData['movieId'])) {//有想看数据，进行删除
            if ($this->model('Want')->delete($arrData['ucid'], $arrData['movieId'])) {//删除想看数据
                if (!$this->model("MovieDBApp")->saveParamNum(['wantCount' => -1], $arrData['movieId'])) {//想看-1
                    $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_WANT_COUNT_ERROR);//想看-1失败
                }
            } else $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_WANT_DELETE_ERROR);
        }//已经是想看了，就不用处理了
        return $arrReturn;
    }

    /**
     *  删除看过，同时看过数-1
     * @param array $arrData
     * @return array
     */
    private function _delSeen($arrData)
    {
        $arrReturn = self::getStOut();
        if ($this->model('Seen')->isSeen($arrData['ucid'], $arrData['movieId'])) {//存在看过
            if ($this->model('Seen')->delete($arrData['ucid'], $arrData['movieId'])) {//删除看过
                if (!$this->model("MovieDBApp")->saveParamNum(['seenCount' => -1], $arrData['movieId'])) {//看过-1
                    $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_SEEN_COUNT_ERROR);//删除看过-1失败
                }
            } else $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_SEEN_DEL_ERROR);//删除看过失败
        }
        return $arrReturn;
    }

    /**
     * @tutorial 对评论点赞
     * @param unknown $arrData
     * @return Ambigous <multitype:, multitype:number string multitype: , multitype:string NULL unknown >
     */
    private function addCommentFavor($arrData)
    {
        $arrReturn = self::getStOut();
        if (!$this->model('CommentFavor')->isUserFavorComment($arrData['ucid'], $arrData['commentId'])) {//不存在赞，则添加赞
            if ($this->model('CommentFavor')->insert($arrData)) {//入库
                if (!$this->model("Comment")->saveParamNum(['favorCount' => 1], $arrData['commentId'])) {//赞+1
                    $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_FAVOR_COUNT_ERROR);//赞+1失败
                }
            } else $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_FAVOR_ADD_ERROR);//赞添加失败
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
        if ($this->model('CommentFavor')->isUserFavorComment($arrData['ucid'], $arrData['commentId'])) {//不存在赞，则删除
            if ($this->model('CommentFavor')->delete($arrData['ucid'], $arrData['commentId'])) {//删除
                if (!$this->model("Comment")->saveParamNum(['favorCount' => -1], $arrData['commentId'])) {//赞+1
                    $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_FAVOR_COUNT_ERROR);//赞-1失败
                }
            } else $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_FAVOR_DEL_ERROR);//赞添加失败
        }
        return $arrReturn;
    }

    /**
     * 获取用户信息
     * @return Ambigous <string, array>
     */
    private function getParamOpenId($arrInstallData)
    {
        //微信电影票unionId为空或者其他渠道的openid和unionId都未空，则需要根据uid来获取unionId或者openid
        if (empty($arrInstallData['openId']) && empty($arrInstallData['unionId'])) {
            if (!empty($arrInstallData['uid'])) {
                $data = self::_getUserContentInfo(JAVA_API_GETUCIDBYUID, ['uid' => $arrInstallData['uid']]);
                $arrInstallData['ucid'] = !empty($data['openId']) ? $data['openId'] : '';
            } else//如果连uid都没传入，那就不用管了，直接置空
                $arrInstallData['ucid'] = '';
        } elseif (empty($arrInstallData['unionId']) && !empty($arrInstallData['openId'])) {//只有openid的时候，这个是为了兼容已发版的客户端，他们现在只传了openid，用于token校验，但不知道是不是微信
            $data = self::_getUserContentInfo(JAVA_API_GETUCIDBYOPENID, ['openId' => $arrInstallData['openId']]);
            $arrInstallData['ucid'] = !empty($data['uniqueId']) ? $data['uniqueId'] : $arrInstallData['openId'];
        } else {
            $arrInstallData['ucid'] = $arrInstallData['unionId'];
        }
        return $arrInstallData;
    }

    private function _getUserContentInfo($strUrl, $arrData)
    {
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = $arrData;
        $data = $this->http($strUrl, $params);
        return !empty($data['data']) ? $data['data'] : '';
    }

    //获取用户评论过的影片列表
    public function getCommentList($arrInput)
    {
        //参数整理
        $arrInput = [
            'uid' => intval(self::getParam($arrInput, 'uid')), //用户uid
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'openId' => self::getParam($arrInput, 'openId'), //用户openId
            'unionId' => self::getParam($arrInput, 'unionId'), //用户unionId
            'movieList' => self::getParam($arrInput, 'movieList'), //用户unionId
        ];
        $arrInput = $this->getParamOpenId($arrInput);
        if (empty($arrInput['ucid']) || empty($arrInput['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_NO_DATA);
        } else {
            $movieList = $arrInput['movieList'];
            $arrReturn = self::getStOut();
            $arrData = $this->model('Comment')->getCommentList($arrInput['ucid']);
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

    //指定影片的评论相关信息
    public function getMovieCommentInfo($arrInput){
        $iMovieId = $arrInput['movieId'];
        $r = $this->model('MovieDBApp')->getOneMovieObject($iMovieId);
        if($r){
            $arrMovie = [];
            foreach($r as $k=>$v){
                $arrMovie[$k]=$v;
            }
            $arrReturn = self::getStOut();
            $arrReturn['data'] = $arrMovie;
        }else{
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_MOVIE_COMMENT_INFO_ERROR);
        }

        return $arrReturn;
    }
}