<?php
namespace sdkService\service;
use sdkService\model\ApplyActive;

/**
 *报名活动
 */
class serviceApply extends serviceBase{
    //错误编码状态
    const ERROR_CODE_STATUS = true;

    /**获取活动动态信息
     * @param $active_id  int    活动id 【必须】
     * @param $token      string token  【非必须】
     * @param $channelId  int  渠道id   【必须】
     * @return array
     */
    public function getApplyActive(array $arrInput){
        //参数整理
        $arrReturn = self::getStOut();
        $active_id = self::getParam($arrInput, 'active_id');
        $token = self::getParam($arrInput, 'token');
        $channelId = self::getParam($arrInput, 'channelId');
        if(empty($active_id) || empty($channelId)){
            $arrReturn=self::getErrorOut(ERRORCODE_APPLY_ACTIVE_ERROR);
            return $arrReturn;
        }
        $arrData=$this->model('ApplyActive')->findApplyActive($active_id);
        $arrData=json_decode($arrData,true);//assoc=true 该返回值为array而非object
        $time=time();
        if($time<=$arrData['start_apply']){//当前时间小于活动开始报名时间返回该数组
            $arrReturn['data']=['viewCount'=>0, 'activeStatus'=>0, 'is_user'=>0];
            return $arrReturn;
        }
        //获取当前活动报名用户数加上后台注水数
        $viewCount=$this->model('ApplyRecord')->applyUserNum($active_id);
        $viewCount=intval($viewCount+$arrData['support']);
        //获取该用户是否已报名
        $arrRes=$this->isApplyUser($token,$active_id,$channelId);
        if($arrRes['ret']==0){
            $arrReturn['data']['viewCount'] = $viewCount;
            if($time>$arrData['end_apply']){
                //当前时间大于报名结束时间返回该数组
                $arrReturn['data']['activeStatus'] = 1;
            }else{
                $arrReturn['data']['activeStatus'] = 2;
            }
            if($arrRes['data']['is_user']==1){
                $arrReturn['data']['is_user'] = 1;
            }else{
                $arrReturn['data']['is_user'] = 0;
            }
        }else{
            $arrReturn=self::getErrorOut(ERRORCODE_DECRYPT_TOKEN_ERROR);
            return $arrReturn;
        }
        $arrReturn['data']['platform']=$arrData['platform'];
        return $arrReturn;
    }

    /**接收表单中用户信息
     * @param $active_id  int    活动id 【必须】
     * @param $token      string token  【必须】
     * @param $channelId  int  渠道id   【必须】
     * @param $openId     string  openId【非必须】
     * @param $user_name  string  用户名【非必须】
     * @param $phone      string  电话【非必须】
     * @param $remark_content   string  备注【非必须】
     * @return array|string
     */
    public function getApplyUser(array $arrInput){
        //参数整理
        $arrReturn = self::getStOut();
        $active_id = self::getParam($arrInput, 'active_id');
        $token = self::getParam($arrInput, 'token');
        $channelId = self::getParam($arrInput, 'channelId');
        $openId = self::getParam($arrInput, 'openId');
        $user_name = self::getParam($arrInput, 'user_name');
        $phone = self::getParam($arrInput, 'phone');
        $remark_content = self::getParam($arrInput, 'remark_content');
        $from = self::getParam($arrInput, 'from');
        if(empty($active_id) || empty($token) || empty($channelId)){
            $arrReturn=self::getErrorOut(ERRORCODE_APPLY_ACTIVE_ERROR);
            return $arrReturn;
        }
        $arrResult=$this->isApplyUser($token,$active_id,$channelId);
        if(isset($arrResult['data']['is_user']) && !empty($arrResult['data']['is_user'])){
            $arrReturn=self::getErrorOut(ERRORCODE_IS_APPLY_USER);
            return $arrReturn;
        }else{
            if($arrResult['ret']==0){
                $openId=$arrResult['data']['openId'];
                $inputArrData=[
                    'a_id'=>$active_id,
                    'open_id'=>$openId,
                    'user_name'=>$user_name,
                    'phone'=>$phone,
                    'channel_id'=>$channelId,
                    'from'=>$from
                ];
                $arrData=$this->model('ApplyActive')->findApplyActive($active_id);
                $arrData=json_decode($arrData,true);//assoc=true 该返回值为array而非object
                if($arrData['is_form'] && $arrData['is_remark']){//是否需要填写表单
                    //是否需要填写备注
                    $inputArrData['remark_content']=$remark_content;
                }else{
                    $inputArrData['remark_content']='';
                }
                $arrRes=$this->model('ApplyRecord')->insertUserInfo($inputArrData);
                if($arrRes){
                    return $arrReturn;
                }else{
                    $arrReturn=self::getErrorOut(ERRORCODE_APPLY_USER_ADD);
                    return $arrReturn;
                }
            }else{
                $arrReturn=self::getErrorOut(ERRORCODE_DECRYPT_TOKEN_ERROR);
                return $arrReturn;
            }
        }
    }

    public function getApplyShare(array $arrInput){
        //参数整理
        $arrReturn = self::getStOut();
        $active_id = self::getParam($arrInput, 'active_id');
        $channelId = self::getParam($arrInput, 'channelId');
        if(empty($active_id) || empty($channelId)){
            $arrReturn=self::getErrorOut(ERRORCODE_APPLY_ACTIVE_ERROR);
            return $arrReturn;
        }
        $arrData=$this->model('ApplyActive')->findApplyActive($active_id);
        $arrData=json_decode($arrData,true);//assoc=true 该返回值为array而非object
        $arrReturn['data']=$arrData['platform'];
        return $arrReturn;
    }

    /**
     * @param $token
     * @param $active_id
     * @param $channelId
     * @return bool
     */
    public function isApplyUser($token,$active_id,$channelId){
        $arrReturn=self::getStOut();
        $arrReturn['data']=['openId'=>'','is_user'=>''];
        if(empty($token)){
            $arrReturn['data']['is_user']=0;
            return $arrReturn;
        }else {
            $arrInput = ['str' => $token, 'channelId' => $channelId];
            if($channelId == 8 || $channelId == 9){
                $arrInput['str']=urldecode($arrInput['str']);
            }
            $arrRes = $this->service('common')->decrypt($arrInput);
            if ($arrRes['ret'] == 0) {
                if ($channelId == 28 || $channelId == 3) {
                    $arrReturn['data']['openId'] = $arrRes['data']['decryptStr'];
                } else {
                    $arrId = json_decode($arrRes['data']['decryptStr']);
                    $arrReturn['data']['openId'] = $arrId->openId;
                }
            } else {
                $arrReturn = $arrRes;
                return $arrReturn;
            }
            $isUser = $this->model('ApplyRecord')->isExistUser($active_id, $arrReturn['data']['openId']);
            if ($isUser) {//该用户openId存在该集合中则用户已报名
                $arrReturn['data']['is_user'] = 1;
            } else {
                $arrReturn['data']['is_user'] = 0;
            }
        }
        return $arrReturn;
    }
}