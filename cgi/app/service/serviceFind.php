<?php

namespace sdkService\service;

/**
 * 仅限新版发、内容相关现接口
 * Class serviceCms
 * @package sdkService\service
 */
class serviceFind extends serviceBase
{
    public function getFindOther(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'cityId' => intval(self::getParam($arrInput, 'cityId',10)),//城市（来源）
            'type' => self::getParam($arrInput, 'type',''),//类型
        ];
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_CMS_NO_DATA);
        }elseif(!empty($arrInstallData['type'])){
            $arrReturn['data'] =  $this->_getFindOtherInfo($arrInstallData);
        }else{
            $arrReturn['data'] = $this->_getFindOther($arrInstallData['channelId'],$arrInstallData['cityId']);
        }
        return $arrReturn;

    }

    /**
     * 获取发现列表--各种列表
     * @param array $arrInput
     * @return array
     */
    public function getFindList(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'page' => intval(self::getParam($arrInput, 'page')) ? intval(self::getParam($arrInput, 'page')) : 1,
            'num' => intval(self::getParam($arrInput, 'num')) ? intval(self::getParam($arrInput, 'num')) : 5,//
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'type' => self::getParam($arrInput, 'type'), //类型：find：发现列表，author：作者列表，recommend：推荐列表
            'typeId' => self::getParam($arrInput, 'typeId'), //id，不填为全部，如果是推荐请传入1
        ];
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['channelId']) || empty($arrInstallData['type']) || empty($arrInstallData['typeId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_CMS_NO_DATA);
        }else{
            $arrData = $this->_getFindListHerd($arrInstallData);
            $arrData['list'] = $this->_getFindListInfo($arrData['list'],$arrInstallData['channelId']);
            $arrReturn['data'] = $arrData;
        }
        return $arrReturn;
    }

    /**
     * @param array $arrInput
     * @return array
     */
    public function getInfomation(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'id' => self::getParam($arrInput, 'id'),
        ];
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['channelId']) || empty($arrInstallData['id'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_CMS_NO_DATA);
        }else{
            $arrData = [];
            if(FIND_INFOMATION_CACHE_STATUS){
                $arrData = $this->model('Find')->getInfomationInfoCache($arrInstallData['channelId'],$arrInstallData['id']);//获取全量缓存
            }
            if(empty($arrData)){
                $arrData = $this->model('Find')->getInfomationInfo($arrInstallData['channelId'],$arrInstallData['id']);//获取全量缓存
                if(!empty($arrData['topic_mailto']) && is_array($arrData['topic_mailto'])){
                    foreach ($arrData['topic_mailto'] as &$val){
                        $val['find_info'] = $this->_getFindListInfo($val['find_id'],$arrInstallData['channelId']);
                    }
                }
                if(FIND_INFOMATION_CACHE_STATUS){
                    $this->model('Find')->setInfomationInfoCache($arrInstallData['channelId'],$arrInstallData['id'],$arrData);//获取全量缓存
                }
            }
            $arrReturn['data'] = $arrData;
        }
        return $arrReturn;
    }

    private function _getFindOtherInfo($arrInstallData)
    {
        $arrData = [];
        if (!empty($arrInstallData['channelId']) && !empty($arrInstallData['type'])) { //参数空校验
            $arrData = [];
            switch ($arrInstallData['type']){
                case 'tagList':
                    $arrData = $this->model('Find')->getTagList($arrInstallData['channelId']);//标签分类
                    break;
                case 'banner':
                    $arrData = $this->model('Find')->getBannerCache($arrInstallData['channelId'],$arrInstallData['cityId']);
                    break;
                case 'horizontal':
                    $arrData = $this->model('Find')->getHorizontalCache($arrInstallData['channelId']);
                    break;
                case 'movieReview':
                    $arrData = $this->model('Find')->getMovieReviewCache($arrInstallData['channelId']);
                    break;
                case 'topic':
                    $arrData = $this->model('Find')->getTopicInfoCache($arrInstallData['channelId']);
                    break;
            }
        }
        return $arrData;
    }

    /**
     * 获取发现头部数据
     * @param $channelId
     */
    private function _getFindOther($channelId,$cityId)
    {
        $arrData = [];
        if(FIND_NEW_CACHE_STATUS){
            $arrData = $this->model('Find')->getFindHeadCache($channelId,$cityId);//获取全量缓存
        }
        if (empty($arrData)){
            $arrData = [];
            $arrData['tagList'] = $this->model('Find')->getTagList($channelId);//标签分类
            $arrData['banner'] = $this->model('Find')->getBannerCache($channelId,$cityId);//banner
            $arrData['horizontal'] = $this->model('Find')->getHorizontalCache($channelId);//发现横栏
            $arrData['movieReview'] = $this->model('Find')->getMovieReviewCache($channelId);//发现观影指南g
            $arrData['topic'] = $this->model('Find')->getTopicInfoCache($channelId);//发现热门话题
            if(FIND_NEW_CACHE_STATUS){
                 $this->model('Find')->setFindHeadCache($channelId,$cityId,$arrData);//获取全量缓存
            }
        }
        return $arrData;
    }

    /**
     * 根据分类获取数据集合
     * @param $arrInstallData
     * @return array
     */
    private function _getFindListHerd($arrInstallData)
    {
        $arrInstallData['num'] = $arrInstallData['num'] < 100?$arrInstallData['num']:100;//最多每次一百条
        $page = $arrInstallData['page']>0 ? $arrInstallData['page']: 1;
        $iStart = ($page -1) * $arrInstallData['num'];
        $iEnd = $page * $arrInstallData['num'] - 1;
        $arrData = ['totalCount' => 0, 'list' => ''];
        switch ($arrInstallData['type']) {
            case 'find'://发现
                $arrData['list'] = $this->model('Find')->getFindList($arrInstallData['channelId'],$arrInstallData['typeId'], $iStart, $iEnd);//列表
                $arrData['totalCount'] = $this->model('Find')->getFindListCount($arrInstallData['channelId'],$arrInstallData['typeId']);
                break;
            case 'author'://作者
                $arrData['list'] = $this->model('Find')->getFindAuthorList($arrInstallData['channelId'],$arrInstallData['typeId'], $iStart, $iEnd);;//列表
                $arrData['totalCount'] = $this->model('Find')->getFindAuthorCount($arrInstallData['channelId'],$arrInstallData['typeId']);
                break;
            case 'recommend'://推荐
                $arrData['list'] = $this->model('Find')->getFindRecommendList($arrInstallData['channelId'], $iStart, $iEnd);;//列表
                $arrData['totalCount'] = $this->model('Find')->getFindRecommendListCount($arrInstallData['channelId']);
                break;
            case 'topic'://热门话题
                $arrTopic =$this->model('Find')->getTopicList($arrInstallData['channelId'],$arrInstallData['typeId']);
                $arrData['list'] = array_slice($arrTopic,$iStart,$arrInstallData['num']);
                $arrData['totalCount'] = count($arrTopic);
                break;
            default://默认按照find去取数据
                $arrData['list'] = $this->model('Find')->queryMovieNewComment($arrInstallData['channelId'],$arrInstallData['typeId'], $iStart, $iEnd);;//列表
                $arrData['totalCount'] = $this->model('Find')->queryMovieNewCommentCount($arrInstallData['channelId'],$arrInstallData['typeId']);
        }
        $arrData['list'] = empty($arrData['list'])?[]:$arrData['list'];
        $arrData['totalCount'] = empty($arrData['totalCount'])?0:$arrData['totalCount'];
        return $arrData;
    }

    /**
     * 获取发现详情
     * @param $rrData
     */
    public function _getFindListInfo($arrData,$channelId)
    {
        $arrFindList = [];
        if(!empty($arrData) && is_array($arrData)){
            if(FIND_NEW_CACHE_STATUS){
                $arrFindList =$this->model('Find')->getFindListCache($channelId,$arrData);
            }
            if(empty($arrFindList)){
                $arrTagInfo = $this->model('Find')->getTagInfo($channelId);
                foreach ($arrData as $val){
                    if(empty($val))
                        continue;
                    $findInfo = $this->model('Find')->getFindInfo($channelId,$val);//获取内容
                    if(empty($findInfo))
                        continue;
                    $findInfo['read']  = $this->model('Cms')->getCmsReadNum($findInfo['a_id']);//阅读数
                    $findInfo['like']  = $this->model('Cms')->getBaseLikeNum($findInfo['a_id']) + $this->model('CmsLike')->getCmsLikeNum($findInfo['a_id']);//点赞数
                    $findInfo['tag_one_name'] = isset($arrTagInfo[$findInfo['tag_one']])?$arrTagInfo[$findInfo['tag_one']]:'';
                    $findInfo['tag_two_name'] = isset($arrTagInfo[$findInfo['tag_two']])?$arrTagInfo[$findInfo['tag_two']]:'';
                    $arrFindList[] = $findInfo;
                }
                if(FIND_NEW_CACHE_STATUS){//缓存
                    $this->model('Find')->setFindListCache($channelId,$arrData,$arrFindList);
                }
            }
        }
        return $arrFindList;
    }

    /**
     * 获取发现导流
     */
    public function getFindGuide($arrInput)
    {
        return $this->model("Find")->getFindGuide($arrInput['channelId']);
    }

}