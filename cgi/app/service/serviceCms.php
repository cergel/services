<?php

namespace sdkService\service;

/**
 * 所有CMS相关的
 * Class serviceCms
 * @package sdkService\service
 */
class serviceCms extends serviceBase
{

    /**
     * @param array $arrInput
     * @return array
     * @throws \Exception
     */
    public function getCmsFindList(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'page' => intval(self::getParam($arrInput, 'page')) ? intval(self::getParam($arrInput, 'page')) : 1,
            'num' => intval(self::getParam($arrInput, 'num')) ? intval(self::getParam($arrInput, 'num')) : 5,//
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'from' => self::getParam($arrInput, 'from'),    //子来源
            'token' => self::getParam($arrInput, 'token'), //用户加密的openid
            'openId' => self::getParam($arrInput, 'openId'), //用户的openid
            'typeId' => self::getParam($arrInput, 'typeId'), //类型id，不填为全部
        ];
        self::getOpenIdByToken($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_CMS_NO_DATA);
        }else{
            $arrInstallData['num'] = $arrInstallData['num'] < 100?$arrInstallData['num']:100;//最多每次一百条
            $page = $arrInstallData['page']>0 ? $arrInstallData['page']: 1;
            $iStart = ($page -1) * $arrInstallData['num'];
            $iEnd = $page * $arrInstallData['num'] - 1;
            //获取列表
            if(!empty($arrInstallData['typeId'])){
                $arrList = $this->model('CmsFindChannel')->getChannelFindTypeList($arrInstallData['channelId'],$arrInstallData['typeId'],$iStart,$iEnd);
            }else{
                $arrList = $this->model('CmsFindChannel')->getChannelFindList($arrInstallData['channelId'],$iStart,$iEnd);
            }

            $arrList = self::getCmsFindInfo($arrList,$arrInstallData['openId']);
            $arrReturn['data'] = $arrList;
        }
        return $arrReturn;
    }
    /**
     * 获取资讯列表
     * @param array $arrInput
     * @return array
     * @throws \Exception
     */
    public function getCmsNewsList(array $arrInput)
    {
        return $this->service('News')->getMovieNews($arrInput);
//        //参数整理
//        $arrInstallData = [
//            'page' => intval(self::getParam($arrInput, 'page')) ? intval(self::getParam($arrInput, 'page')) : 1,
//            'num' => intval(self::getParam($arrInput, 'num')) ? intval(self::getParam($arrInput, 'num')) : 5,//
//            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
//            'from' => self::getParam($arrInput, 'from'),    //子来源
//            'movie_id' => intval(self::getParam($arrInput, 'movieId')),    //子来源
//            'token' => self::getParam($arrInput, 'token'), //用户unionId
//            'openId' => self::getParam($arrInput, 'openId'), //用户的openid
//        ];
//        self::getOpenIdByToken($arrInstallData);
//        $arrReturn = self::getStOut();
//        if (empty($arrInstallData['channelId']) || empty($arrInstallData['movie_id'])) { //参数空校验
//            $arrReturn = self::getErrorOut(ERRORCODE_CMS_NO_DATA);
//        }else{
//            $arrInstallData['num'] = $arrInstallData['num'] < 100?$arrInstallData['num']:100;//最多每次一百条
//            $page = $arrInstallData['page']>0 ? $arrInstallData['page']: 1;
//            $iStart = ($page -1) * $arrInstallData['num'];
//            $iEnd = $page * $arrInstallData['num'] - 1;
//            //获取列表
//            //获取列表
//            $arrList['totalCount'] = $this->model('CmsNews')->getMovieNewsCount($arrInstallData['channelId'],$arrInstallData['movie_id']);
//            $arrList['list'] = $this->model('CmsNews')->getMovieNewsList($arrInstallData['channelId'],$arrInstallData['movie_id'],$iStart,$iEnd);
////            $arrList = $this->model('CmsNews')->getCmsNewsList($arrInstallData['movie_id'],$iStart,$iEnd);
//            //增加返回总页数，和当前页，方便前端判断是否需要继续请求下一页数据
//            $arrList['total_page'] = ( !empty( $arrList['totalCount'] ) ) ? ceil($arrList['totalCount'] / $arrInstallData['num']) : 1;
//            $arrList['current_page'] = $page;
//            //获取内容详情
//            $arrList['list'] = self::getCmsInfo($arrList['list'],$arrInstallData['openId']);
//            $arrReturn['data'] = $arrList;
//        }
//        return $arrReturn;
    }

    /**
     * 获取cms内容的阅读数，并且+1
     * @param array $arrInput
     * @return array
     * @throws \Exception
     */
    public function getCmsReadStatus(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'a_id' => intval(self::getParam($arrInput, 'id')),//cms id
            'from' => self::getParam($arrInput, 'from'),    //子来源
            'token' => self::getParam($arrInput, 'token'), //用户unionId
            'openId' => self::getParam($arrInput, 'openId'), //用户的openid
        ];
        self::getOpenIdByToken($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['channelId']) || empty($arrInstallData['a_id'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_CMS_NO_DATA);
        }else{ //获取阅读数等
            //阅读数+1
            $this->model('Cms')->addCmsReadNum($arrInstallData['a_id']);
            $arrData = self::getCmsReadLikeStatus($arrInstallData['a_id'],$arrInstallData['openId']);
            $arrReturn['data'] = $arrData;
        }
        return $arrReturn;
    }

    /**
     * 点赞cms
     * @param array $arrInput
     * @return array
     * @throws \Exception
     */
    public function likeCms(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'a_id' => intval(self::getParam($arrInput, 'id')),//cms id
            'from' => htmlspecialchars(self::getParam($arrInput, 'from')),    //子来源
            'status' => intval(self::getParam($arrInput, 'status')),    //1点赞、0取消点赞
            'token' => self::getParam($arrInput, 'token'), //用户unionId
            'openId' => self::getParam($arrInput, 'openId'), //用户的openid
        ];
        self::getOpenIdByToken($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['channelId']) || empty($arrInstallData['a_id'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_CMS_NO_DATA);
        }elseif(empty($arrInstallData['openId'])){
            $arrReturn = self::getErrorOut(ERRORCODE_CMS_NO_USER);
        }else{
            if($arrInstallData['status'] =='1'){//点赞
                $arrReturn = self::addCmsLike($arrInstallData);
            }else{//取消点赞
                $arrReturn = self::delCmsLike($arrInstallData);
            }
        }
        return $arrReturn;
    }

    /**
     * 推荐频道-发现文章
     * @param array $arrInput
     * @return array
     * @throws \Exception
     */
    public function getFindOtherCms(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'a_id' => intval(self::getParam($arrInput, 'id')),//cms id
            'from' => htmlspecialchars(self::getParam($arrInput, 'from')),    //子来源
            't_id' => intval(self::getParam($arrInput, 't_id')),    //1点赞、0取消点赞
            'token' => self::getParam($arrInput, 'token'), //用户unionId
            'openId' => self::getParam($arrInput, 'openId'), //用户的openid
        ];
        self::getOpenIdByToken($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['channelId']) || empty($arrInstallData['a_id']) || empty($arrInstallData['t_id'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_CMS_NO_DATA);
        }else{
            $arrData = $this->model('cmsFindChannel')->getCmsFindOtherCmsId($arrInstallData['channelId'],$arrInstallData['t_id']);
            if(!empty($arrData)){
//                $arrData = array_splice(array_merge(array_diff($arrData, array($arrInstallData['a_id']))),0,4);
//                $arrData = self::getCmsInfo($arrData,$arrInstallData['openId']);
                $arrData = $this->getArrayDataToOtherCms($arrData,$arrInstallData['a_id'],$arrInstallData['openId']);
            }else{
                $arrData = [];
            }
            $arrReturn['data'] = $arrData;
        }
        return $arrReturn;
    }

    /**
     * 发现文章关联推荐文章
     * @param $arrData
     * @param $aid
     * @param $openId
     * @return array
     */
    private function getArrayDataToOtherCms($arrData,$aid,$openId)
    {
        $arrFind = [];
        if(!empty($arrData)){
            foreach($arrData as $val){
                if($val['a_id'] == $aid){//除去重复的数据
                    continue;
                }
                $val =  $val + $this->getCmsReadLikeStatus($val['a_id'],$openId);
                $arrFind[] = $val;
            }
        }
        return $arrFind;
    }




    /**
     * 点赞
     * @param $arrInstallData
     */
    private function addCmsLike($arrInstallData)
    {
        $arrReturn = self::getStOut();
        if(!$this->model('CmsLike')->isCmsLikeUser($arrInstallData['a_id'],$arrInstallData['openId'])){
            $this->model('CmsLike')->addLike($arrInstallData);
        }
        return $arrReturn;
    }
    /**
     * 取消点赞
     * @param $arrInstallData
     */
    private function delCmsLike($arrInstallData)
    {
        $arrReturn = self::getStOut();
        if($this->model('CmsLike')->isCmsLikeUser($arrInstallData['a_id'],$arrInstallData['openId'])){
            $this->model('CmsLike')->delLike($arrInstallData['a_id'],$arrInstallData['openId']);
        }
        return $arrReturn;
    }


    /**
     * 获取每条内容的信息
     * @param $arrData
     */
    private function getCmsInfo($arrData,$openId='')
    {
        $arrResData = [];
        if(!empty($arrData) && is_array($arrData)){
            foreach($arrData as $val){
                if(empty($val))continue;
                $arrInfo = $this->model('Cms')->getOneCmsInfo($val);
                if(empty($arrInfo))continue;
                $arrInfo = array_merge($arrInfo,self::getCmsReadLikeStatus($arrInfo['a_id'],$openId));
                $arrInfo['url'] = PEOMOTION_CDN_URL.'/cms_h5/'.$arrInfo['a_id'].'/index.html';
                $arrResData[] = $arrInfo;
            }
        }
        return $arrResData;
    }

    /**
     * 获取每条发现的信息
     * @param $arrData
     */
    private function getCmsFindInfo($arrData,$openId='')
    {
        $arrResData = [];
        if(!empty($arrData) && is_array($arrData)){
            foreach($arrData as $val){
                if(empty($val))continue;
                $arrFindInfo = $this->model('CmsFind')->getOneCmsFindInfo($val);
                if(empty($arrFindInfo))continue;
                $arrFindInfo = array_merge($arrFindInfo,self::getCmsReadLikeStatus($arrFindInfo['a_id'],$openId));
                $arrResData[] = $arrFindInfo;
            }
        }
        return $arrResData;
    }


    /**
     * 获取cms的点赞等状态
     * @param $aid
     */
    private function getCmsReadLikeStatus($aid,$openId='')
    {
        $arrData['reads'] = self::getCmsReadNum($aid);//阅读数
        $arrData['likes'] = self::getCmsLikeNum($aid);//喜欢数
        $arrData['is_like'] = self::isCmsLikeUser($aid,$openId);//点赞状态
        return $arrData;
    }
    /**
     * 获取每条CMS的点赞状态
     */
    private function isCmsLikeUser($aid,$openId)
    {
        $is_like = 0;
        if(!empty($aid) && !empty($openId))
            $is_like = $this->model('CmsLike')->isCmsLikeUser($aid,$openId);
        return $is_like;
    }
    /**
     * 获取每条CMS的阅读总数
     * 注水数+真实阅读数
     */
    private function getCmsReadNum($aid)
    {
        $read = 0;
        if(!empty($aid))
            $read = $this->model('Cms')->getCmsReadNum($aid);
        return $read;
    }

    /**
     * 获取cms喜欢总数
     * @param $aid
     */
    private function getCmsLikeNum($aid)
    {
        $like = 0;
        if(!empty($aid)){
            $like += $this->model('Cms')->getBaseLikeNum($aid);//注水数
            $like += $this->model('CmsLike')->getCmsLikeNum($aid);
        }
        return $like;
    }

    /**
     * 获取用户openId信息
     * @param $arrInstallData
     */
    public function getOpenIdByToken(&$arrInstallData)
    {
        //$arrInstallData['openId'] = !empty($arrInstallData['openId'])?$arrInstallData['openId']:'';
        if(!empty($arrInstallData['token']) && !empty($arrInstallData['channelId']) && empty($arrInstallData['openId'])){
            //兑换openId
            $data = $this->service('common')->decrypt(['channelId'=>$arrInstallData['channelId'],'str'=>$arrInstallData['token']]);
            if(!empty($data['data']['decryptStr'])){
                if($arrInstallData['channelId'] ==8 || $arrInstallData['channelId'] == 9){
                    $arr = json_decode($data['data']['decryptStr'],true);
                    $arrInstallData['openId'] = !empty($arr['openId'])?$arr['openId']:'';
                }elseif($arrInstallData['channelId'] ==3 || $arrInstallData['channelId'] ==28){
                    $arrInstallData['openId'] = $data['data']['decryptStr'];
                }elseif($arrInstallData['channelId'] ==80 || $arrInstallData['channelId'] == 84){
                    $arr = json_decode($data['data']['decryptStr'],true);
                    $arrInstallData['openId'] = !empty($arr['gewaraid'])?$arr['gewaraid']:'';
                }

            }
        }
    }











}