<?php

namespace sdkService\service;


/**
 * 所有基于尿点的操作都放在这里面
 * Class servicePee
 * @package sdkService\service
 */
class servicePee extends serviceBase
{
    const ERROR_CODE_STATUS = true;//错误编码状态

    static $levelDown = false;//尿点降级

    //降级函数
    public function levelDown($params){
        return self::getStOut();
    }

    /**
     * @tutorial 获取影片的尿点报告信息
     * @param  int   movieId 影片的id（数字的那个，可以是数组，也可以是单个影片） 【必填】
     * @param  int   channelId  渠道  【必填】
     * @param  int   from    子渠道    【非必填】
     * @param string openId openId    【非必填】
     *
     */
    public function getMoviePeeInfo(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'movieId' => self::getParam($arrInput, 'movieId'),  //影片id，可以是数组
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
            'openId' => self::getParam($arrInput, 'openId'), //用户openId
        ];
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['movieId']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_PEE_NO_DATA);
        }
        if (empty($arrReturn['ret'])){
            if(is_array($arrInstallData['movieId'])){
                $arrData = $this->model('Pee')->getMoreMoviePee($arrInstallData['movieId']);
                $arrData = self::_getPeeCountMovieArray($arrData,$arrInstallData['openId']);
            }else{
                $arrData = $this->model('Pee')->getOneMoviePee($arrInstallData['movieId']);
                $arrData = self::_getPeeCountMovieOne($arrData,$arrInstallData['openId'],$arrInstallData['movieId']);
            }
            $arrReturn['data'] = $arrData;
        }
        return $arrReturn;
    }

    /**
     * @tutorial 用户点击尿了、取消尿了
     * @param  int   movieId 影片的id（数字的那个，单个影片） 【必填】
     * @param  int   p_id 尿点的id   【必填】
     * @param  int   status  状态  【必填】，0：取消，1：点击尿了
     * @param  int   channelId  渠道  【必填】
     * @param  int   from    子渠道    【非必填】
     * @param string openId openId    【必填】
     * @todo OK
     */
    public function clickPee(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'movieId' => intval(self::getParam($arrInput, 'movieId')),  //影片id，
            'p_id' => intval(self::getParam($arrInput, 'p_id')),  //尿点Id
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'status' => self::getParam($arrInput, 'status'),    //1 点尿，0：取消点尿
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
            'openId' => self::getParam($arrInput, 'openId'), //用户openId
        ];
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['movieId']) || empty($arrInstallData['channelId']) || empty($arrInstallData['p_id'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_PEE_NO_DATA);
        }
        if(empty($arrReturn['ret']) && $arrInstallData['status'] == 1){
            $arrReturn = self::_addPeeUserInfo($arrInstallData);
        }elseif(empty($arrReturn['ret']) && $arrInstallData['status'] == 0){
            $arrReturn = self::_delPeeUserInfo($arrInstallData);
        }
        return $arrReturn;
    }
    /**
     * 删除-用户点尿
     * @param $arrInstallData
     * @return array
     * @throws \Exception
     * @todo OK
     */
    private function _delPeeUserInfo($arrInstallData)
    {
        $arrReturn = self::getStOut();
        if($this->model('PeeInfo')->queryPeeOpenId($arrInstallData['p_id'],$arrInstallData['openId'],$arrInstallData['movieId'])){//如果用户没点过尿了，就不用处理了
            if(!$this->model('PeeInfo')->deletePeeInfo($arrInstallData['p_id'],$arrInstallData['openId'],$arrInstallData['movieId']))
                $arrReturn = self::getErrorOut(ERRORCODE_PEE_DEL_ERROR);
            }
        return $arrReturn;
    }

    /**
     * 插入-用户点尿
     * @param $arrInstallData
     * @return array
     * @throws \Exception
     * @todo OK
     */
    private function _addPeeUserInfo($arrInstallData)
    {
        $arrReturn = self::getStOut();
        if(!$this->model('PeeInfo')->queryPeeOpenId($arrInstallData['p_id'],$arrInstallData['openId'],$arrInstallData['movieId'])){//如果已经点过尿了，就不用处理了
            if($this->model('Pee')->isMoviePee($arrInstallData['p_id'],$arrInstallData['movieId'])){//是否是指定影片的尿点
                $inserRe = $this->model('PeeInfo')->insertPeeInfo($arrInstallData);
                if(empty($inserRe))
                    $arrReturn = self::getErrorOut(ERRORCODE_PEE_ADD_ERROR);
            }else $arrReturn = self::getErrorOut(ERRORCODE_PEE_NO_MOVIE);
        }
        return $arrReturn;
    }


    /**
     * 获取peeinfo的附加内容:一条
     * @param $arrData
     * @param string $openId
     * @param $movieId
     * @return mixed
     * @todo OK
     */
    private function _getPeeCount(&$arrData,$openId='',$movieId)
    {
        if(!empty($openId)){
            $arrDataKey = $this->model('PeeInfo')->getAllUserPeeMovie($openId,$movieId);
            foreach($arrData as &$val){
                $val = (array)$val;
                $val['pee_user'] = in_array($val['p_id'],$arrDataKey)?1:0;
                $peeCount = $this->model('PeeInfo')->queryPeeCount($val['p_id']);
                $val['pee_count'] =$peeCount + $val['base_pee_count'];
                self::_unsetField($val);
            }
        }else{
            foreach($arrData as &$val){
                $val = (array)$val;
                $val['pee_user'] = 0;
                $peeCount = $this->model('PeeInfo')->queryPeeCount($val['p_id']);
                $val['pee_count'] =$peeCount + $val['base_pee_count'];
                self::_unsetField($val);
            }
        }
    }

    /**
     * 清理不必要的字段
     * @param $arrData
     * @todo OK
     */
    private function _unsetField(&$arrData)
    {
        if(isset($arrData['base_pee_count']))
            unset($arrData['base_pee_count']);
//        if(isset($arrData['created']))
//            unset($arrData['created']);
//        if(isset($arrData['update']))
//            unset($arrData['update']);
    }
    /**
     * 获取peeinfo的附加内容多个影片
     * @param $arrData
     * @param string $openId
     * @param $movieId
     * @return mixed
     */
    private function _getPeeCountMovieOne($arrData,$openId='',$movieId)
    {
        if(!empty($arrData)){
            if(!empty($arrData->peeInfo)){
                self::_getPeeCount($arrData->peeInfo,$openId,$movieId);
            }
        }
        return $arrData;
    }
    /**
     * 获取peeinfo的附加内容多个影片
     * @param $arrData
     * @param string $openId
     * @param $movieId
     * @return mixed
     */
    private function _getPeeCountMovieArray($arrData,$openId='')
    {
        if(!empty($arrData)){
            foreach($arrData as $key=>&$movie){
                if(!empty($movie->peeInfo)){
                    self::_getPeeCount($movie->peeInfo,$openId,$key);
                }
            }
        }
        return $arrData;
    }

    /**
     * 获取尿点类型
     * @param $arrData
     * @param string $openId
     * @param string $type
     * @return mixed
     * @todo  废弃
     */
    private function getPeeCount($arrData,$openId='',$type='')
    {
        if(empty($arrData)) return $arrData;
        if($type == 'array'){
            foreach($arrData as &$movie){
                if(!empty($movie->peeInfo)){
                    foreach($movie->peeInfo as &$val){
                        self::_getPeeInfo($val,$openId);
                    }
                }
            }
        }else{
            if(!empty($arrData->peeInfo)){
                foreach($arrData->peeInfo as &$val){
                    self::_getPeeInfo($val,$openId);
                }
            }
        }
        return $arrData;
    }

    /**
     * 获取尿点数据详情（总数和当前用户）
     * @param $arrPeeInfo
     * @param string $openId
     */
    private function _getPeeInfo(&$arrPeeInfo,$openId='')
    {
        $arrPeeInfo = (array)$arrPeeInfo;
        $arrPeeInfo['pee_count'] = $this->model('PeeInfo')->queryPeeCount($arrPeeInfo['p_id']) + $arrPeeInfo['base_pee_count'];
        unset($arrPeeInfo['base_pee_count']);
        if(!empty($openId)){
            $pee_user = $this->model('PeeInfo')->queryPeeOpenId($arrPeeInfo['p_id'],$openId) ;
            $arrPeeInfo['pee_user'] = !empty($pee_user)?1:0;
        }else  $arrPeeInfo['pee_user'] = 0;

    }


}