<?php

namespace sdkService\service;
use sdkService\extend\verify;


class serviceVerifyCode extends serviceBase
{
    //生成验证码
    //1.输出图片
    //2.存储验证码信息
    /**
     * @param id 用户唯一标识
     * @param channelId 渠道id
     * @throws \Exception
     */
    public function createVerifyCode($arrInput){
        $id=$arrInput['id'];
        $chinnelId=$arrInput['channelId'];
        if(!$this->model('verifyCode',$chinnelId)->PoolExists()){//走的是85
            $this->createCodeBack($arrInput);//如果图片池不存在，那么要先创建个图片池
        }
        $saveResult = $this->model('verifyCode',$chinnelId)->createVerifyCodeSave($id);

        if ($saveResult) {
            header('Cache-Control: private, max-age=0, no-store, no-cache, must-revalidate');
            header('Cache-Control: post-check=0, pre-check=0', false);
            header('Pragma: no-cache');
            header("content-type: image/png");
            // 输出图像
            echo $saveResult;
        } else {
            echo '验证码生成失败';
        }
    }

    //对验证码检验
    /**
     * @param id 用户唯一标识
     * @param channelId 渠道id
     * @return mixed
     * @throws \Exception
     */
    public function checkCode($arrInput){
        $id=$arrInput['id'];
        $code=$arrInput['code'];
        $chinnelId=$arrInput['channelId'];
        return $this->model('verifyCode',$chinnelId)->checkCode($id,$code);
    }

    /**
     * 检验验证码(验证成功后会使验证码失效)
     * @param string $id 用户唯一标识
     * @param string $code 验证码
     * @param string $channelId 渠道id
     * @return['data']  1 验证成功 ，0 验证失败
     */
    public function checkCodeForm($arrInput = [])
    {
        $data = [
            'id' => $arrInput['id'],
            'code' => $arrInput['code'],
            'channelId' => $arrInput['channelId'],
        ];
        $response = $this->checkCode($data);
        if ($response['data'] == 1) {
            //如果验证通过，移除验证码数据
            $this->removeCodeData($data);
            $arrRes = [
                'ret' => 0,
                'sub' => 0,
                'msg' => '验证成功',
                'data' => 1,
            ];
            return $arrRes;
        } else {
            $arrRes = [
                'ret' => -1,
                'sub' => -1,
                'msg' => '验证失败',
                'data' => 0,
            ];
            return $arrRes;
        }
    }

    //获取验证码输入次数
    /**
     * @param id 用户唯一标识
     * @param channelId 渠道id
     * @return mixed
     * @throws \Exception
     */
    public function getFailedTimes($arrInput){
        $id=$arrInput['id'];
        $chinnelId=$arrInput['channelId'];
        return $this->model('verifyCode',$chinnelId)->getFailedTimes($id);
    }

    //移除验证码资料
    /**
     * @param id 用户唯一标识
     * @param channelId 渠道id
     * @return mixed
     * @throws \Exception
     */
    public function removeCodeData($arrInput){
        $id=$arrInput['id'];
        $chinnelId=$arrInput['channelId'];
        return $this->model('verifyCode',$chinnelId)->removeCodeData($id);
    }

    //跑脚本生成多张验证码图片
    //后台脚本专用，【前台禁止】调用
    /**
     * @param nums 生成的数量
     * @param filePath 生成图片在服务器上存放的地址
     * @return mixed
     * @throws \Exception
     */
    public function createCodeBack($arrInput=[]){
        $nums=isset($arrInput['nums'])?$arrInput['nums']:300;//默认300
        $filePath=isset($arrInput['filePath'])?$arrInput['filePath']:'/tmp/pic_pool_'.md5(rand(1,999));//默认一个乱七八糟的名字
        $channelId=isset($arrInput['channelId'])?$arrInput['channelId']:DEFAULT_REDIS_CHANNEL_ID;//如果不指定,默认用DEFAULT_REDIS_CHANNEL_ID这个channelId
        //$verify=wepiao::di('veryfy',['class'=>"\\sdkService\\extend\\verify\\verify"],$channelId);
        $verify  = new  verify\verify($channelId);
        $model=$this->model('verifyCode',$channelId);

       //todo 待测试
        $flag=true;
        $model->delPoolKey();
        for($j=1;$j<=$nums;$j++){
            $data=$verify->createCodePic($filePath);
            $r=$model->hashSet($data);
            if($r==false){
                $flag=false;
            }
        }
        return $flag;
    }
    
}