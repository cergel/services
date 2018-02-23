<?php

namespace sdkService\service;

/**
 * 资讯相关的
 * Class serviceCms
 * @package sdkService\service
 */
class serviceNews extends serviceBase
{
    /**
     * 获取资讯列表
     * @param array $arrInput
     * @return array
     * @throws \Exception
     */
    public function getMovieNews(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'page' => intval(self::getParam($arrInput, 'page')) ? intval(self::getParam($arrInput, 'page')) : 1,
            'num' => intval(self::getParam($arrInput, 'num')) ? intval(self::getParam($arrInput, 'num')) : 5,//
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'from' => self::getParam($arrInput, 'from'),    //子来源
            'movie_id' => intval(self::getParam($arrInput, 'movieId')),    //子来源
            'token' => self::getParam($arrInput, 'token'), //用户unionId
            'openId' => self::getParam($arrInput, 'openId'), //用户的openid
        ];
        $this->service('Cms')->getOpenIdByToken($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['channelId']) || empty($arrInstallData['movie_id'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_CMS_NO_DATA);
        }else{
            $arrInstallData['num'] = $arrInstallData['num'] < 100?$arrInstallData['num']:100;//最多每次一百条
            $page = $arrInstallData['page']>0 ? $arrInstallData['page']: 1;
            $iStart = ($page -1) * $arrInstallData['num'];
            $iEnd = $page * $arrInstallData['num'] - 1;
            //获取列表
            $arrList['totalCount'] = $this->model('CmsNews')->getMovieNewsCount($arrInstallData['channelId'],$arrInstallData['movie_id']);
            $arrList['list'] = $this->model('CmsNews')->getMovieNewsList($arrInstallData['channelId'],$arrInstallData['movie_id'],$iStart,$iEnd);
            //增加返回总页数，和当前页，方便前端判断是否需要继续请求下一页数据
            $arrList['total_page'] = ( !empty( $arrList['totalCount'] ) ) ? ceil($arrList['totalCount'] / $arrInstallData['num']) : 1;
            $arrList['current_page'] = $page;
            //获取内容详情
            $arrList['list'] = self::getNewsInfo($arrList['list'],$arrInstallData['openId']);
            $arrReturn['data'] = $arrList;
        }
        return $arrReturn;
    }
    public function getActorNews(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'page' => intval(self::getParam($arrInput, 'page')) ? intval(self::getParam($arrInput, 'page')) : 1,
            'num' => intval(self::getParam($arrInput, 'num')) ? intval(self::getParam($arrInput, 'num')) : 5,//
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'from' => self::getParam($arrInput, 'from'),    //子来源
            'actor_id' => intval(self::getParam($arrInput, 'actorId')),    //子来源
            'token' => self::getParam($arrInput, 'token'), //用户unionId
            'openId' => self::getParam($arrInput, 'openId'), //用户的openid
        ];
        $this->service('Cms')->getOpenIdByToken($arrInstallData);
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['channelId']) || empty($arrInstallData['actor_id'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_CMS_NO_DATA);
        }else{
            $arrInstallData['num'] = $arrInstallData['num'] < 100?$arrInstallData['num']:100;//最多每次一百条
            $page = $arrInstallData['page']>0 ? $arrInstallData['page']: 1;
            $iStart = ($page -1) * $arrInstallData['num'];
            $iEnd = $page * $arrInstallData['num'] - 1;
            //获取列表
            $arrList['totalCount'] = $this->model('CmsNews')->getActorNewsCount($arrInstallData['channelId'],$arrInstallData['actor_id']);
            $arrList['list'] = $this->model('CmsNews')->getActorNewsList($arrInstallData['channelId'],$arrInstallData['actor_id'],$iStart,$iEnd);
            //增加返回总页数，和当前页，方便前端判断是否需要继续请求下一页数据
            $arrList['total_page'] = ( !empty( $arrList['totalCount'] ) ) ? ceil($arrList['totalCount'] / $arrInstallData['num']) : 1;
            $arrList['current_page'] = $page;
            //获取内容详情
            $arrList['list'] = self::getNewsInfo($arrList['list'],$arrInstallData['openId']);
            $arrReturn['data'] = $arrList;
        }
        return $arrReturn;
    }

    /**
     * 获取每条资讯内容的信息
     * @param $arrData
     */
    private function getNewsInfo($arrData,$openId='')
    {
        $arrResData = [];
        if(!empty($arrData) && is_array($arrData)){
            foreach($arrData as $val){
                if(empty($val))continue;
                $arrInfo = $this->model('Cms')->getNewsInfoById($val);
                if(empty($arrInfo))continue;
                $arrInfo['reads'] = $arrInfo['base_read'] + $this->model('Cms')->getCmsReadNumById($val);
                $arrInfo['likes'] = $arrInfo['base_fill'] + $this->model('CmsLike')->getCmsLikeNum($val);
                if(!empty($openId))
                    $arrInfo['is_like'] = $this->model('CmsLike')->isCmsLikeUser($val,$openId);
                else $arrInfo['is_like'] = 0;
                $arrResData[] = $arrInfo;
            }
        }
        return $arrResData;
    }

}