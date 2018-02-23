<?php

namespace sdkService\service;

/**
 * 媒资库相关接口
 * Class serviceMsdb
 * @package sdkService\service
 */
class serviceMsdb extends serviceBase
{

    /**
     * 获取即将上映的影片信息--已废弃
     * @param array $arrInput
     * @param int channelId 渠道id，【必须]
     * @param int page 页数，【必须]，默认从第一页开始
     * @param int num 渠道id，【必须]，每页条数，默认为20条
     * @param int movieInfo 是否需要影片信息，1：需要，0：不需要。【默认为0】
     * @param int actorInfo 是否需要影人信息，1：需要，0：不需要，【默认为0】，本字段只有在movieInfo=1的时候生效
     */
    public function getWillMovie(array $arrInput = [])
    {
        $arrReturn = self::getStOut();
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iPage = intval(self::getParam($arrInput, 'page'));
        $iNum = intval(self::getParam($arrInput, 'num'));
        $iMovieInfo = self::getParam($arrInput, 'movieInfo');
        $iActorInfo = self::getParam($arrInput, 'actorInfo');
        $iPage = empty($iPage) || $iPage < 1 ? 1 : $iPage;
        $iNum = empty($iNum) ? 20 : $iNum;
        if (empty($iChannelId)) {
            self::getErrorInfo(ERRORCODE_MSDB_PARAM_ERROR);
        }
        $arrWillMovie['data'] = $arrMovieId = $this->model('msdb')->getWillMovieIdList($iChannelId, ($iPage - 1) * $iNum, $iPage * $iNum - 1);
        $arrWillMovie['total'] = $this->model('msdb')->getWillMovieCount($iChannelId);
        if (!empty($iMovieInfo) && !empty($arrWillMovie['data'])) {//取影片数据
            $arrWillMovie['data'] = $this->model('msdb')->getMovieInfoMore($iChannelId, $arrWillMovie['data']);
            if (!empty($iActorInfo) && !empty($arrWillMovie['data'])) {
                $objActorData = $this->model('msdb')->getMovieActorMore($iChannelId, $arrMovieId);//读取影人信息
                foreach ($arrWillMovie['data'] as $key => &$val) {//循环匹配数据
                    $val->actorInfo = !empty($objActorData[$key]) ? $objActorData[$key] : [];
                }
            }
        }
        $arrReturn['data'] = $arrWillMovie;
        return $arrReturn;
    }

    /**
     * 获取指定的影片信息---影人已迁移，等待影片迁移
     * @param array $arrInput
     * @param int channelId 渠道id，【必须]
     * @param int|| array  movieId 影片ID，，【必须]，可以是一个id，也可以是一个数组，数组格式[6066,6032]，
     * @param int actorInfo 是否需要影人信息，1：需要，0：不需要，【默认为0】，本字段只有在movieInfo=1的时候生效
     */
    public function getMovieInfo(array $arrInput = [])
    {
        $arrReturn = self::getStOut();
        $iChannelId = self::getParam($arrInput, 'channelId');
        $minMovieId = self::getParam($arrInput, 'movieId');
        $iActorInfo = intval(self::getParam($arrInput, 'actorInfo'));
        if (empty($iChannelId) || empty($minMovieId)) {
            self::getErrorInfo(ERRORCODE_MSDB_PARAM_ERROR);
        }
        if (is_array($minMovieId)) {//批量获取
            $arrData = $this->model('msdb')->getMovieInfoMore($iChannelId, $minMovieId);
            if (!empty($iActorInfo) && !empty($arrData)) {
                $objActorData = $this->model('msdb')->getMovieActorMore($iChannelId, $minMovieId);//读取影人信息
                foreach ($arrData as $key => &$val) {//循环匹配数据
                    $val->actorInfo = !empty($objActorData[$key]) ? $objActorData[$key] : [];
                }
            }
        } else {
            $arrData = $this->model('msdb')->getMovieInfoOne($iChannelId, $minMovieId);
            if (!empty($arrData) && !empty($iActorInfo)) {
                $arrData->actorInfo = $this->model('msdb')->getMovieActorOne($iChannelId, $minMovieId);//读取影人信息
            }
        }
        $arrReturn['data'] = $arrData;
        return $arrReturn;
    }

    /**
     * 获取指定的影片的影人信息信息
     * @param array $arrInput
     * @param int channelId 渠道id，【必须]
     * @param int|| array  movieId 影片ID，，【必须]，可以是一个id，也可以是一个数组，数组格式[6066,6032]，
     */
    public function getMovieActor(array $arrInput = [])
    {
        $arrReturn = self::getStOut();
        $iChannelId = self::getParam($arrInput, 'channelId');
        $minMovieId = self::getParam($arrInput, 'movieId');
        if (empty($iChannelId) || empty($minMovieId)) {
            self::getErrorInfo(ERRORCODE_MSDB_PARAM_ERROR);
        }
        if (is_array($minMovieId)) {
            $arrData = $this->model('msdb')->getMovieActorMore($iChannelId, $minMovieId);//读取影人信息
        } else {
            $arrData = $this->model('msdb')->getMovieActorOne($iChannelId, $minMovieId);//读取影人信息
        }
        $arrReturn['data'] = $arrData;
        return $arrReturn;
    }


    /**
     * 获取影人信息--包含影人图片
     * @param array $arrInput
     * @return array
     */
    public function getActorInfo(array $arrInput = [])
    {
        $arrReturn = self::getStOut();
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iActorId = self::getParam($arrInput, 'actorId');
        $iMovie = self::getParam($arrInput, 'movieInfo');
        $strOpenId = self::getParam($arrInput, 'openId', '');
        $iCityId = self::getParam($arrInput, 'cityId', '10');
        $iMovie = $iMovie == 1 ? 1 : 0;
        if (empty($iChannelId) || empty($iActorId)) {
            self::getErrorInfo(ERRORCODE_MSDB_PARAM_ERROR);
        }
        $arrData = $this->model('msdb')->getActorOneInfo($iChannelId, $iActorId);//读取影人信息
        if (!empty($arrData)) {
            $arrData = $arrData + $this->getActorLike($iActorId, $strOpenId);
            if (!empty($iMovie)) {
                $arrData['movieInfo'] = $this->model('msdb')->getActorMovie($iChannelId, $iActorId);//读取影人参与的影片信息
                if (!empty($arrData['movieInfo'])) {
                    foreach ($arrData['movieInfo'] as &$val) {
                        if (!empty($val['omMovieId'])) {
                            $val['buy_flag'] = $this->model("Movie")->getScheFlagOfMovieCityNewStatic($iChannelId, $val['movieId'], $iCityId);
                            if (!empty($val['buy_flag'])) {
                                //获取评分
                                $data = $this->model('movie')->readMovieInfo($iChannelId, $val['movieId']);
                                if (isset($data['score']))
                                    $val['score'] = $data['score'];
                                else $val['score'] = 0;
                                if(isset($data['scoreCount']))
                                    $val['scoreCount'] = $data['scoreCount'];
                                else
                                    $val['scoreCount'] = 0;
                            }
                        } else {
                            $val['buy_flag'] = 0;
                        }
                    }
                }
            }
        }
//        if(!empty($arrData) && !empty($iMovie)){
//            $arrData['movieInfo'] = $this->model('msdb')->getActorMovie($iChannelId,$iActorId);//读取影人参与的影片信息
//        }
        $arrReturn['data'] = $arrData;
        return $arrReturn;
    }
    /**
     * 获取影人图片
     * @param array $arrInput
     * @return array
     */

    public function getActorPhoto(array $arrInput = [])
    {
        $arrReturn = self::getStOut();
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iActorId = self::getParam($arrInput, 'actorId');
        $strPosterType = self::getParam($arrInput, 'posterType');
        if (empty($iChannelId) || empty($iActorId)) {
            self::getErrorInfo(ERRORCODE_MSDB_PARAM_ERROR);
        }
        $arrData = $this->model('msdb')->getActorPhoto($iChannelId, $iActorId);//读取影片图片列表信息
        if (!empty($arrData) && !empty($strPosterType)) {
            $arrData = isset($arrData[$strPosterType]) ? $arrData[$strPosterType] : [];
        }
        $arrReturn['data'] = $arrData;
        return $arrReturn;
    }

    /**
     * 用户点喜欢、不喜欢影人
     * @param array $arrInput
     */
    public function actorLike(array $arrInput = [])
    {
        $arrReturn = self::getStOut();
        $iChannelId = self::getParam($arrInput, 'channelId');
        $actorId = self::getParam($arrInput, 'actorId');
        $openId = self::getParam($arrInput, 'openId', '');
        $status = self::getParam($arrInput, 'status', '0');
        if (empty($iChannelId) || empty($actorId) || empty($openId)) {
            return self::getErrorInfo(ERRORCODE_MSDB_PARAM_ERROR);
        }
        if ($status == '1') {
            if (!$this->model('ActorUser')->isActiveUser($actorId, $openId)) {
                if (!$this->model('ActorUser')->addActiveUser($actorId, $openId)) {
                    self::getErrorInfo(ERRORCODE_MSDB_PARAM_ERROR);
                }
            }
        } else {
            if ($this->model('ActorUser')->isActiveUser($actorId, $openId)) {
                if (!$this->model('ActorUser')->delActiveUser($actorId, $openId)) {
                    self::getErrorInfo(ERRORCODE_MSDB_ACTOR_DEL_LIKE_ERROR);
                }
            }
        }
        return $arrReturn;
    }
    /**
     * 获取用户喜欢的影人列表
     * @param array $arrInput
     */
    public function actorLikeList(array $arrInput = [])
    {
        $arrReturn = self::getStOut();
        $iChannelId = self::getParam($arrInput, 'channelId');
        $openId = self::getParam($arrInput, 'openId', '');
        $page = intval(self::getParam($arrInput, 'page', 1));
        $num = intval(self::getParam($arrInput, 'num', 5));
        if (empty($iChannelId) || empty($openId)) {
            return self::getErrorInfo(ERRORCODE_MSDB_PARAM_ERROR);
        }
        $page = $page < 1?1:$page;
        $star = ($page - 1) * $num;
        $end = $page * $num - 1;
        $arrData = $this->model('ActorUser')->getActiveUserList($openId,$star,$end);
        $arrActor = [];
        if(!empty($arrData)){
            foreach ($arrData as $val){
                $actor = $this->model('msdb')->getActorOneInfo($iChannelId, $val);//读取影人信息
                if(!empty($actor)){
                    if(isset($actor['biography']))
                        unset($actor['biography']);
                    $arrActor[] = $actor;
                }
            }
        }
        $totalCount= $this->model('ActorUser')->getActiveUserListCount($openId);
        $maxPage=ceil($totalCount/$num);
        $arrReturn['data']['curPage'] = $page;
        $arrReturn['data']['nextPage'] = ($page+1>$maxPage)?$maxPage:$page+1;
        $arrReturn['data']['totalPage'] = $maxPage;
        $arrReturn['data']['totalCount'] = $totalCount;
        $arrReturn['data']['list'] = $arrActor;
        return $arrReturn;
    }


    /**
     * 获取用户喜欢的影人总数
     * @param array $arrInput
     */
    public function actorLikeCount(array $arrInput = [])
    {
        $arrReturn = self::getStOut();
        $openId = self::getParam($arrInput, 'openId', '');
        $arrReturn['data']['totalCount'] = $this->model('ActorUser')->getActiveUserListCount($openId);
        return $arrReturn;
    }

    /**
     * 注水点赞数
     * @param array $arrInput
     * @return array
     */
    public function manageActorBaseLike(array $arrInput = [])
    {
        $arrReturn = self::getStOut();
        $iChannelId = self::getParam($arrInput, 'channelId');
        $actorId = self::getParam($arrInput, 'actorId');
        $manageLike = self::getParam($arrInput, 'base_like', '0');
        if (empty($iChannelId) || empty($actorId) || empty($openId)) {
            self::getErrorInfo(ERRORCODE_MSDB_PARAM_ERROR);
        }
        $baseLike = $this->model('Actor')->getActorBaseLike($actorId);
        if ($baseLike != $manageLike) {
            if (!$this->model('Actor')->saveActorBaseLike($actorId, $manageLike)) {
                self::getErrorInfo(ERRORCODE_MSDB_ACTOR_BASE_LIKE_ERROR);
            }
        }
        return $arrReturn;
    }

    /**
     * 获取影人图片
     * @param array $arrInput
     * @return array
     */

    public function getMoviePoster(array $arrInput = [])
    {
        $arrReturn = self::getStOut();
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iMovieId = self::getParam($arrInput, 'movieId');
        $strPosterType = self::getParam($arrInput, 'posterType');
        if (empty($iChannelId) || empty($iMovieId)) {
            self::getErrorInfo(ERRORCODE_MSDB_PARAM_ERROR);
        }
        $arrData = $this->model('msdb')->getMoviePosterOne($iChannelId, $iMovieId);//读取影片图片信息
        if (!empty($arrData) && !empty($strPosterType)) {
            $arrData = isset($arrData[$strPosterType]) ? $arrData[$strPosterType] : [];
//            foreach($arrData as &$val){
//                if($val['poster_type'] != $strPosterType ){
//                    $val ='';
//                }
//            }
//            $arrData = array_values(array_filter($arrData));
        }
        $arrReturn['data'] = $arrData;
        return $arrReturn;
    }

    /**
     * 获取影人列表--带评价数和评价状态
     * @param array $arrInput
     */
    public function getMovieActorListAndAppraise(array $arrInput = [])
    {
        $arrReturn = self::getStOut();
        $channelId = self::getParam($arrInput, 'channelId');
        $movieId = self::getParam($arrInput, 'movieId');
        $openId = self::getParam($arrInput, 'openId');
        if (empty($channelId) || empty($movieId)) {
            self::getErrorInfo(ERRORCODE_MSDB_PARAM_ERROR);
        }
        $arrReturn['data']['list'] = $this->_getMovieActorListCount($movieId, $channelId);
        if (!empty($arrReturn['data']['list'])) {
            $arrReturn['data']['user_actor'] = $this->model('ActorAppraise')->getMovieActiveByUser($movieId, $openId);//获取用户维度的信息：评价了哪个影人
        }
        return $arrReturn;
    }

    /**
     * 更新评价状态【添加、修改、删除】评价
     * @param array $arrInput
     */
    public function saveAppraise(array $arrInput = [])
    {
        $channelId = self::getParam($arrInput, 'channelId');
        $movieId = self::getParam($arrInput, 'movieId');
        $actorId = self::getParam($arrInput, 'actorId');
        $openId = self::getParam($arrInput, 'openId');
        $status = self::getParam($arrInput, 'status');
        $status = $status == 1 ? 1 : 0;
        if (empty($channelId) || empty($movieId) || empty($actorId) || empty($openId)) {
            self::getErrorInfo(ERRORCODE_MSDB_PARAM_ERROR);
        }
        if ($status == 1) {//添加或修改
            $arrReturn = $this->_addMovieActorAppraise($movieId, $actorId, $openId);
        } else {//删除
            $arrReturn = $this->_delMovieActorAppraise($movieId, $actorId, $openId);
        }
        return $arrReturn;
    }

    /**
     * 获取手机电影信息
     * @param array $arrInput
     * @return array
     */
    public function getSvipMovie(array $arrInput = [])
    {
        $channelId = self::getParam($arrInput, 'channelId');
        $movieId = self::getParam($arrInput, 'movieId');
        if (empty($channelId) || empty($movieId)) {
            self::getErrorInfo(ERRORCODE_MSDB_PARAM_ERROR);
        }
        $arrReturn = self::getStOut();
        $arrData = $this->model('msdb')->getSvipMovieUrl($channelId, $movieId);
        $arrReturn['data'] = $arrData;
        return $arrReturn;
    }

    /**
     * 获取明星配对列表
     * @param array $arrInput
     */
    public function getStarPair(array $arrInput = [])
    {
        $arrReturn = self::getStOut();
        $birthday = self::getParam($arrInput, 'birthday'); //生日，格式:YYYY-mm-dd  2017-01-20
        $gender = self::getParam($arrInput, 'gender');  //性别，男、女
        $count = self::getParam($arrInput, 'count',5);//条数，默认为5条
        $openId = self::getParam($arrInput, 'openId');
        if(empty($birthday) || empty($gender) || empty($openId)){
            $arrReturn = self::getErrorInfo(ERRORCODE_MSDB_PARAM_ERROR);
        }else{
            $this->service('user')->updateUserinfo(['openId'=>$openId,'birthday'=>$birthday,'sex'=>$gender=='男'?1:2]);//更新用户信息
            $url = JAVA_ADDR_BIG_DATA_STAR_PAIR;
            $params['sMethod'] = 'post';
            $params['arrData'] = [
                'birthday' => $birthday,
                'gender' => $gender,
                'count' => $count,
            ];
            $data = $this->http($url, $params);
            $arrReturn['data'] = isset($data['data'])?$data['data']:[];
        }
        return $arrReturn;
    }

    /**
     * 判断用户是否购过某电影的票，调大数据接口
     * @param array $arrInput
     * @return array
     */
    public function isBuyTicket(array $arrInput = []){
        $arrReturn = self::getStOut();
        $movieId = self::getParam($arrInput, 'movieId');
        $openId = self::getParam($arrInput, 'openId', '');
        $arrReturn['data'] = ['status'=>0];
        if(!empty($movieId) && !empty($openId)){
            //参数处理
            $arrSendParams = ['movieId'=>$movieId,'openId'=>$openId];
            $httpParams = [
                'arrData' => $arrSendParams,
                'sMethod' => 'POST',
                'iTryTimes' => 1,
                'sendType' => 'json',
            ];
            $arrRet = $this->http(JAVA_ADDR_BIG_DATA_MOVIE_TICKET, $httpParams);
            if(!empty($arrRet) && $arrRet['ret']==0){
                $arrReturn['data'] = $arrRet['data'];
            }
        }
        return $arrReturn;
    }

    /**
     * 获取明星配对列表
     * @param array $arrInput
     */
    public function getStarPairPv(array $arrInput = [])
    {
        $arrReturn = self::getStOut();
        $url = JAVA_ADDR_BIG_DATA_STAR_PAIR_PV;
        $params['sMethod'] = 'get';
        $params['arrData'] = [
        ];
        $data = $this->http($url, $params);
        $arrReturn['data']['page_pv'] = isset($data['data'][0]['page_pv'])?$data['data'][0]['page_pv']:0;
        return $arrReturn;
    }

    private static function getErrorInfo($errType)
    {
        $arrReturn = self::getErrorOut($errType);
        return $arrReturn;
    }

    /**
     * 获取影人的喜欢数、和当前用户是否喜欢该明星
     * @param $actorId
     * @param $openId
     */
    private function getActorLike($actorId, $openId)
    {
        $arrData['likeCount'] = $this->model('Actor')->getActorLikeCount($actorId);
        if (empty($openId)) {
            $arrData['is_like'] = 0;
        } else {
            $isLike = $this->model('ActorUser')->isActiveUser($actorId, $openId);
            $arrData['is_like'] = $isLike === false ? 0 : 1;
        }
        return $arrData;
    }

    /**
     * 读取数据-基于缓存的缓存
     * @param $movieId
     * @param $channelId
     * @return array
     */
    private function _getMovieActorListCount($movieId, $channelId)
    {
        //优先读取缓存
        //$arrData = $this->model('ActorAppraise')->getActorCache($movieId);
        $arrData = [];
        if (empty($arrData)) {
            $arrData = $this->model('msdb')->getMovieActorOne($channelId, $movieId);//读取影人信息
            if (!empty($arrData)) {
                $movieActorCount = $this->model('ActorAppraise')->getMovieAllActorCount($movieId);
                foreach ($arrData as &$val) {
                    if ($val->movie_actor_type == 'ORDINARY_ACTOR') {
                        $val->appraise_count = isset($movieActorCount[$val->actor_id]) ? $movieActorCount[$val->actor_id] : 0;
//                        $val->appraise_count = $this->model('ActorAppraise')->getMovieOneActorCount($movieId,$val->actor_id);
                    }
                }
                $this->model('ActorAppraise')->saveActorCache($arrData, $movieId);
            }
        }
        return $arrData;
    }

    /**
     * 插入、修改评价
     * @param $movieId
     * @param $actorId
     * @param $openId
     * @return array
     * @throws \Exception
     */
    private function _addMovieActorAppraise($movieId, $actorId, $openId)
    {
        $arrReturn = self::getStOut();
        $baseActorId = $this->model('ActorAppraise')->getMovieActiveByUser($movieId, $openId);
        if (!empty($baseActorId) && $baseActorId != $actorId) {
            if (!$this->model('ActorAppraise')->delMovieActiveByUser($movieId, $baseActorId, $openId)) {
                $arrReturn = $this->getErrorOut(ERRORCODE_MSDB_ACTOR_DEL_APPRAISE_ERROR);
            }
        } elseif ($baseActorId == $actorId) {
            return $arrReturn;
        }
        if (!$this->model('ActorAppraise')->addMovieActiveByUser($movieId, $actorId, $openId)) {
            $arrReturn = $this->getErrorOut(ERRORCODE_MSDB_ACTOR_ADD_APPRAISE_ERROR);
        }
        return $arrReturn;
    }

    /*
     * 删除评价
     */
    private function _delMovieActorAppraise($movieId, $actorId, $openId)
    {
        $arrReturn = self::getStOut();
        $baseActorId = $this->model('ActorAppraise')->getMovieActiveByUser($movieId, $openId);
        if (!empty($baseActorId) && $baseActorId == $actorId) {
            if (!$this->model('ActorAppraise')->delMovieActiveByUser($movieId, $baseActorId, $openId)) {
                $arrReturn = $this->getErrorOut(ERRORCODE_MSDB_ACTOR_DEL_APPRAISE_ERROR);
            }
        } elseif (!empty($baseActorId)) {
            $arrReturn = $this->getErrorOut(ERRORCODE_MSDB_ACTOR_NO_USER_APPRAISE_ERROR);
        }
        return $arrReturn;
    }


}