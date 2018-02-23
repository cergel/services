<?php

namespace sdkService\service;

/**
 * 所有CMS评论相关的
 * Class serviceCmsComment
 * @package sdkService\service
 */
class serviceCmsComment extends serviceBase
{
    /**
     * 增加评论
     * @param array $arrInput
     * @return array
     * @throws \Exception
     * @todo OK
     */
    public function addComment(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'channel_id' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'from' => htmlspecialchars(self::getParam($arrInput, 'from')),    //子来源
            'content' => htmlspecialchars(self::getParam($arrInput, 'content')), //评论内容
            'a_id' =>intval(self::getParam($arrInput, 'id')),  //内容id
            'token' => self::getParam($arrInput, 'token'), //用户unionId
            'open_id' => self::getParam($arrInput, 'openId'), //用户的openid
            'checkstatus'=>0,
            'status'=>1,
            'created'=>time(),
            'favor_count'=>0,
        ];
        self::getOpenIdByToken($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['channel_id']) || empty($arrInstallData['a_id'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_CMS_NO_DATA);
        }elseif(empty($arrInstallData['open_id'])){//登录状态
            $arrReturn = self::getErrorOut(ERRORCODE_CMS_NO_USER);
        }else{
            if (empty($arrReturn['ret'])) {//黑名单
                $arrReturn = $this->service('commonComment')->isBalckList($arrInstallData['open_id']);
            }
            if (empty($arrReturn['ret'])) //腾讯屏蔽词
                $arrReturn = $this->service('commonComment')->isTengXunKeyWord($arrInstallData['content']);
            if (empty($arrReturn['ret'])) //屏蔽词
                $arrReturn = $this->service('commonComment')->isShieldingWords($arrInstallData['content']);
            if (empty($arrReturn['ret'])){//敏感词
                $arrReturn = $this->service('commonComment')->isSensitiveWords($arrInstallData['content']);
                $arrInstallData['checkstatus'] = empty($arrReturn['data'])?0:3;
            }
            if (empty($arrReturn['ret'])) {//是否能发或改当前评论
                $arrReturn = self::isLastComment($arrInstallData['open_id']);
            }
            if(empty($arrReturn['ret'])){//插入数据库
                $minRes =$this->model('CmsComment')->insertContent($arrInstallData,COMMENT_UPDATE_TIME);
                if(!empty($minRes))$arrReturn['data'] = $minRes;
                else $arrReturn = self::getErrorOut(ERRORCODE_CMS_NO_USER);
            }
        }
        return $arrReturn;
    }

    /**
     * 获取指定内容的评论列表,包含用户信息（统一获取）
     * @param array $arrInput
     * @return array
     * @throws \Exception
     * @todo hot OK，new异常
     */
    public function getCmsCommentList(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'page' => intval(self::getParam($arrInput, 'page')) ? intval(self::getParam($arrInput, 'page')) : 1,
            'num' => intval(self::getParam($arrInput, 'num')) ? intval(self::getParam($arrInput, 'num')) : 5,//
            'channel_id' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'sortBy'=>self::getParam($arrInput, 'sortBy'),
            'from' => htmlspecialchars(self::getParam($arrInput, 'from')),    //子来源
            'a_id' =>intval(self::getParam($arrInput, 'id')),  //内容id
            'token' => self::getParam($arrInput, 'token'), //用户unionId
            'open_id' => self::getParam($arrInput, 'openId'), //用户的openid
        ];
        self::getOpenIdByToken($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['channel_id']) || empty($arrInstallData['a_id'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_CMS_NO_DATA);
        }else{
            $arrInstallData['num'] = $arrInstallData['num'] < 100?$arrInstallData['num']:100;//最多每次一百条
            $arrInstallData['page'] = $arrInstallData['page']>0 ? $arrInstallData['page']: 1;
            $iStart = ($arrInstallData['page'] -1) * $arrInstallData['num'];
            $iEnd = $arrInstallData['page']  * $arrInstallData['num'] - 1;
            switch ($arrInstallData['sortBy']) {
                case 'new':
                    $arrComment['comments'] = $this->model('CmsComment')->queryNewCommentList($arrInstallData['a_id'], $iStart, $iEnd);//列表
                    break;
                case 'hot':
                    $arrComment['comments'] = $this->model('CmsComment')->queryHotCommentList($arrInstallData['a_id'], $iStart, $iEnd);//列表
                    break;
                default://默认按照new排序
                    $arrComment['comments'] = $this->model('CmsComment')->queryNewCommentList($arrInstallData['a_id'], $iStart, $iEnd);//列表
            }
            $arrComment['totalCount'] = $this->model('CmsComment')->queryCommentNum($arrInstallData['a_id']);
            $arrComment['comments'] =self::getCommentListInfoFavor($arrComment['comments'],$arrInstallData['open_id']);//格式化评论
            $arrReturn['data'] = $arrComment;
        }
        return $arrReturn;
    }
    /**
     * 点赞、取消点赞评论
     * @param array $arrInput
     * @return array
     * @throws \Exception
     * @todo OK
     */
    public function favor(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'comment_id' => intval(self::getParam($arrInput, 'commentId')),  //评论id
            'channel_id' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'from' => htmlspecialchars(self::getParam($arrInput, 'from')),    //子来源
            'status' => intval(self::getParam($arrInput, 'status')), //点赞状态
            'token' => self::getParam($arrInput, 'token'), //用户openId
            'open_id' => self::getParam($arrInput, 'openId'), //用户的openid
            'created'=>time(),
        ];
        self::getOpenIdByToken($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['channel_id']) || empty($arrInstallData['comment_id'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_CMS_NO_DATA);
        }elseif(empty($arrInstallData['open_id'])){
            $arrReturn = self::getErrorOut(ERRORCODE_CMS_NO_USER);
        }else{
            if($arrInstallData['status'] == 1){//点赞
                $arrReturn = self::_addFavor($arrInstallData);
            }else{//取消点赞
                $arrReturn = self::_delFavor($arrInstallData);
            }
        }
        return $arrReturn;
    }

    /**
     * 用户删除评论
     * @param array $arrInput
     * @return array
     * @throws \Exception
     * @todo OK
     */
    public function delComment(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'comment_id' => intval(self::getParam($arrInput, 'commentId')),  //评论id
            'channel_id' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'from' => self::getParam($arrInput, 'from'),    //子来源
            'token' => self::getParam($arrInput, 'token'), //用户openId
            'open_id' => self::getParam($arrInput, 'openId'), //用户的openid
        ];
        self::getOpenIdByToken($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['channel_id']) || empty($arrInstallData['comment_id'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_CMS_NO_DATA);
        }elseif(empty($arrInstallData['open_id'])){//是否登录
            $arrReturn = self::getErrorOut(ERRORCODE_CMS_NO_USER);
        }else{
            $arrCommentInfo = $this->model('CmsComment')->queryContent($arrInstallData['comment_id']);//获取内容
            if(empty($arrCommentInfo)){//是否存在的评论
                $arrReturn = self::getErrorOut(ERRORCODE_CMS_NO_COMMENT);
            }elseif($arrCommentInfo['open_id'] != $arrInstallData['open_id']){//是否是当前用户的评论
                $arrReturn = self::getErrorOut(ERRORCODE_CMS_NO_COMMENT_USER);
            }else{
                if(!$this->model('CmsComment')->delContent($arrInstallData['comment_id'])){//删除
                    $arrReturn = self::getErrorOut(ERRORCODE_CMS_DEL_COMMENT);
                }
            }
        }
        return $arrReturn;
    }

    /**
     * 获取内容等信息
     * @param $arrComment
     * @param string $openId
     * @return array
     */
    private function getCommentListInfoFavor($arrComment,$openId=''){
        $arrData = [];
        if(!empty($arrComment) && is_array($arrComment))
        foreach($arrComment as $val){
            if(empty($val))continue;//空值判断
            $arrInfo = $this->model('CmsComment')->queryContent($val);//获取内容
            if(empty($arrInfo))continue;
            if(!empty($openId)){
                $arrInfo['is_user'] =($openId == $arrInfo['open_id'])?1:0;  //判断是否属于当前用户
                $arrInfo['is_favor'] = $this->model('CmsCommentFavor')->isFavor($arrInfo['open_id'],$val)?1:0;
            }else {
                $arrInfo['is_user'] = 0;
                $arrInfo['is_favor'] = 0;
            }
            $arrInfo['favor_count'] = $arrInfo['base_favor_count'] + $this->model('CmsCommentFavor')->getFavorNum($val);
            //获取头像
            $arrInfo['user'] = $this->service('commonComment')->getUserInfoByUcid($arrInfo['open_id'],'',false);
            self::_delField($arrInfo);
            $arrData[] = $arrInfo;
        }
        return $arrData;
    }
    private function _delField(&$arrInfo)
    {
        if(isset($arrInfo['open_id']))
            unset($arrInfo['open_id']);
        if(isset($arrInfo['base_favor_count']))
            unset($arrInfo['base_favor_count']);
        if(isset($arrInfo['checkstatus']))
            unset($arrInfo['checkstatus']);
        if(isset($arrInfo['from']))
            unset($arrInfo['from']);
        if(isset($arrInfo['channel_id']))
            unset($arrInfo['channel_id']);
    }


    /**
     * 点赞
     * @param $arrInstallData
     * @return array
     * @throws \Exception
     */
    private function _addFavor($arrInstallData){
        $arrReturn = self::getStOut();
        if(!$this->model('CmsCommentFavor')->isFavor($arrInstallData['open_id'],$arrInstallData['comment_id'])){
            if(!$this->model('CmsCommentFavor')->favor($arrInstallData)){
                $arrReturn = self::getErrorOut(ERRORCODE_CMS_ADD_FAVOR);  //点赞失败
            }
        }
        return $arrReturn;
    }
    /**
     * 取消点赞
     * @param $arrInstallData
     * @return array
     * @throws \Exception
     */
    private function _delFavor($arrInstallData){
        $arrReturn = self::getStOut();
        if($this->model('CmsCommentFavor')->isFavor($arrInstallData['open_id'],$arrInstallData['comment_id'])){
            if(!$this->model('CmsCommentFavor')->unFavor($arrInstallData['comment_id'],$arrInstallData['open_id'])){
                $arrReturn = self::getErrorOut(ERRORCODE_CMS_DEL_FAVOR);  //取消点赞失败
            }
        }
        return $arrReturn;
    }


    /**
     * 管理员修改评论
     * @param array $arrInput
     * @return array
     * @throws \Exception
     */
    public function manageSaveComment(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'comment_id' => intval(self::getParam($arrInput, 'commentId')),  //评论id
            'checkstatus' => intval(self::getParam($arrInput, 'checkstatus')),  //
            'base_favor_count' => intval(self::getParam($arrInput, 'baseFavorCount')),  //评论id
            'content' => self::getParam($arrInput, 'content'),  //评论id
            'channel_id' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
        ];
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['comment_id'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_CMS_NO_DATA);
        }else{
            if(!$this->model('CmsComment')->managerUpdateComment($arrInstallData['comment_id'],$arrInstallData))
                $arrReturn = self::getErrorOut(ERRORCODE_CMS_DEL_COMMENT);
        }
        return $arrReturn;
    }

    /**
     * 管理员删除评论
     * @param array $arrInput
     * @return array
     * @throws \Exception
     */
    public function manageDelComment(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'comment_id' => intval(self::getParam($arrInput, 'commentId')),  //评论id
            'channel_id' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
        ];
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['comment_id'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_CMS_NO_DATA);
        }else{
            if(!$this->model('CmsComment')->managerScreenComment($arrInstallData['comment_id']))
                $arrReturn = self::getErrorOut(ERRORCODE_CMS_DEL_COMMENT);
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
        $arrReturn = self::getStOut();
        if($this->model('CmsComment')->commentSpacing($strUcid))
            $arrReturn = self::getErrorOut(ERRORCODE_COMMENT_UPDATE_TIME_WORE);
        return $arrReturn;
    }


    /**
     * 获取用户openId信息
     * @param $arrInstallData
     */
    private function getOpenIdByToken(&$arrInstallData,$openId='open_id',$channelId ='channel_id')
    {
        //$arrInstallData[$openId] = !empty($arrInstallData[$openId])?$arrInstallData[$openId]:'';
        if(!empty($arrInstallData['token']) && !empty($arrInstallData[$channelId]) && empty($arrInstallData[$openId])){
            //兑换openId
            $data = $this->service('common')->decrypt(['channelId'=>$arrInstallData[$channelId],'str'=>$arrInstallData['token']]);
            if(!empty($data['data']['decryptStr'])){
                if($arrInstallData[$channelId] ==8 || $arrInstallData[$channelId] == 9){
                    $arr = json_decode($data['data']['decryptStr'],true);
                    $arrInstallData[$openId] = !empty($arr['openId'])?$arr['openId']:'';
                }elseif($arrInstallData[$channelId] ==3 || $arrInstallData[$channelId] ==28){
                    $arrInstallData[$openId] = $data['data']['decryptStr'];
                }

            }
        }
    }











}