<?php

namespace sdkService\service;


use sdkService\extend\slideVerify\slideVerify;

class serviceSlideVerify extends serviceBase
{
    const poolNum = 50;

    static $arrStandard = ["small"=>480,"medium"=>720,"big"=>1080];

    //生成图验到redis
    public function create($arrInput){
        $width=self::getParam($arrInput, 'width');
        if($width<=600){
            $standard = "small";
        }elseif($width>600 && $width<=900){
            $standard = "medium";
        }else{
            $standard = "big";
        }
        $hashKey = rand(1,self::poolNum);
        if(!$this->model('SlideVerifyCode')->poolExists($standard)){
            $this->createPool();
        }
        $slideInfo = $this->model("SlideVerifyCode")->getPoolInfo($standard,$hashKey);
        $slideInfo = json_decode($slideInfo,1);
        $id=md5("cc".rand(1,399999).time()."p6q35n");
        $outPutInfo=[
            'slideId'=>$id,
            'colorStrip'=>$slideInfo['colorStrip'],
            'wordPic'=>$slideInfo['wordPic'],
        ];
        $cacheInfo=$slideInfo['trueColorArea'];
        $this->model("SlideVerifyCode")->setIdInfo($id,json_encode($cacheInfo));
        $returnData=self::getStOut();
        $returnData['data']=$outPutInfo;
        return $returnData;
    }

    //生成图片池
    public function createPool(){
        $arrStandard = self::$arrStandard;
        foreach($arrStandard as $key=>$width){
            for($i=1;$i<=self::poolNum;$i++){
                $slide=new slideVerify($width);
                $slideInfo=$slide->getRealStrip();
                $slideInfo['wordPic'] = base64_encode($slideInfo['wordPic']);
                $hashKey = $i;
                $hashValue = json_encode($slideInfo);
                $this->model("SlideVerifyCode")->setPoolInfo($key,$hashKey,$hashValue);
            }
            $this->model("SlideVerifyCode")->setPoolExpire($key);
        }
    }

    //验证滑动图验的区域
    public function verify($arrInput){
        $offset=self::getParam($arrInput,'offset');
        $id=self::getParam($arrInput,'slideId');

        //参数校验
        if($offset==='' || $id===''){
            return self::getErrorOut(ERRORCODE_SLIDE_VERIFY_PARAMS_ERROR);
        }
        $cacheRe=$this->model('SlideVerifyCode')->getIdInfo($id);
        //如果为false证明已经过期了
        if($cacheRe===false){
            return self::getErrorOut(ERRORCODE_SLIDE_VERIFY_TIMEOUT_ERROR);
        }
        $arrCacheInfo=json_decode($cacheRe,1);
        if($offset>=$arrCacheInfo['left'] && $offset<=$arrCacheInfo['right']){
            $credential = $this->setCredential($id);
            $return = self::getStOut();
            $return['data']=['slideCredential'=>$credential];
            $return['msg']="验证成功";
        }else{
            $return = self::getErrorOut(ERRORCODE_SLIDE_VERIFY_VERIFY_ERROR);
        }
        //删掉正确区域信息，用户下次申请时再生成
        $this->model('SlideVerifyCode')->delIdInfo($id);
        return $return;
    }

    //生成加密凭据，并存储
    protected function setCredential($id){
        $credential = md5(time().'uglllidr4gf9'.rand(1,999999).$id);
        $this->model('SlideVerifyCode')->saveCredential($id,$credential);
        return $credential;
    }

    //检测加密串
    public function checkCredential($arrInput){
        $id = self::getParam($arrInput,'slideId');
        $credential = self::getParam($arrInput,'slideCredential');
        if(empty($id)||empty($credential)){
            return  self::getErrorOut(ERRORCODE_SLIDE_VERIFY_CREDENTIAL_ERROR);
        }
        $redisRe = $this->model('SlideVerifyCode')->getCredential($id);
        if($redisRe===false){
            $return = self::getErrorOut(ERRORCODE_SLIDE_VERIFY_CREDENTIAL_ERROR);
        }else{
            if($credential==$redisRe){
                $this->model('SlideVerifyCode')->delCredential($id);
                $return = self::getStOut();
            }else{
                $return = self::getErrorOut(ERRORCODE_SLIDE_VERIFY_CREDENTIAL_ERROR);
            }
        }
        return $return;
    }


    //检测id测错误次数
    protected function checkSlideIdFaikNum(){

    }

    //获取h5页面的url
    public function getH5Url(){
        $return = self::getStOut();
        $return['data']['url']=SLIDE_H5_URL;
        return $return;
    }


}