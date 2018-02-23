<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/1
 * Time: 11:14
 */
//验证码model//
namespace sdkService\model;
class VerifyCode extends BaseNew
{
    private $codeExprieDuration;//验证码的超时时间

    private $faileDuration;//记录多少秒内的错误次数

    private $preHashKey;//hashkey 的前缀

    private $preStrKey;//错误次数的key

    private $picHashKey;//图片池中的大key


    public function __construct($iChannelId){
        parent::__construct();
        $objRedis = $this->redis($iChannelId,STATIC_MOVIE_DATA);
        $this->saveObj=$objRedis;
        $this->codeExprieDuration=600;//验证码的超时时间
        $this->faileDuration=60;//记录多少秒内的错误次数
        $this->preHashKey='verifyCode_';
        $this->preStrKey='verifyCodeFaileTimes_';
        $this->picHashKey='verifyCodePool';
    }

    //将生成的验证码与用户标识保存在一起
    public function createVerifyCodeSave($id)
    {
        $arrFiledKey=$this->saveObj->WYhKeys($this->picHashKey);

        if(empty($arrFiledKey)){
            return false;
        }
        $randKey=array_rand($arrFiledKey,1);
        $code=$arrFiledKey[$randKey];//随机获取一个key作为code
        $hashKey=$this->preHashKey.$id;
        $data['code']=$code;
        $data['createtime']=time();

        $strKey=$this->preStrKey.$id;
        $codeResult=$this->saveObj->WYhMset($hashKey,$data);
        $this->saveObj->WYexpire($hashKey,$this->codeExprieDuration);
        //如果没有数据，则初始化失败次数
        if($this->saveObj->WYget($strKey)===false){//如果没有过期时间，则初始化过期时间
            $faileResult=$this->saveObj->WYset($strKey,0);
            $this->saveObj->WYexpire($strKey,$this->faileDuration);
        }

        if($codeResult){
            $picture=$this->saveObj->WYhGet($this->picHashKey,$code);
            if(isset($faileResult)){
                if($faileResult){
                    return $picture;
                }else{
                    return false;
                }
            }else{
                return $picture;
            }
        }else{
            return false;
        }
    }

    //获取图片池是否存在
    public function PoolExists(){
        return $this->saveObj->WYhKeys($this->picHashKey);
    }

    //检测验证码
    public function checkCode($id,$code)
    {
        $arrReturn['ret']=0;
        $arrReturn['sub']=0;
        $code=strtoupper($code);


        $hashKey=$this->preHashKey.$id;
        $strKey=$this->preStrKey.$id;
        $subKey='code';
        $redisCode=$this->saveObj->WYhGet($hashKey,$subKey);

        if(empty($redisCode)){
            $arrReturn['msg']='验证码失效，请重新获取';
            $arrReturn['data']=0;
        }elseif($code == $redisCode){
            $arrReturn['msg']='验证成功';
            $arrReturn['data']=1;
        }else{
            $arrReturn['msg']='验证码错误';
            $arrReturn['data']=0;
            //如果验证码不对，则要进行错误次数的增加
            if($this->saveObj->WYget($strKey)){//如果当前没过期，则进行直接设置
                $this->saveObj->WYincrBy($strKey,1,-1);
            }else{
                $this->saveObj->WYincrBy($strKey,1);
                $this->saveObj->WYexpire($strKey,$this->faileDuration);
            }
        }
        return $arrReturn;
    }

    //获取错误次数
    public function getFailedTimes($id)
    {
        $arrReturn['ret']=0;
        $arrReturn['sub']=0;
        $arrReturn['msg']='';
        $strKey=$this->preStrKey.$id;
        $arrReturn['data']=$this->saveObj->WYget($strKey);
        return $arrReturn;
    }

    //移除验证码信息
    public function removeCodeData($id){
        $hashKey=$this->preHashKey.$id;
        $strKey=$this->preStrKey.$id;
        if($this->saveObj->WYdelete($hashKey) && $this->saveObj->WYdelete($strKey)){
            return true;
        }else{
            return false;
        }
    }

    //将生成的图片存入图片池
    public function hashSet($data){
        $r=$this->saveObj->WYhMset($this->picHashKey,$data);
        return $r;
    }

    //删除图片池的key
    public function delPoolKey(){
       $r=$this->saveObj->WYdelete($this->picHashKey);

        return $r;
    }
}