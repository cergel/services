<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/1
 * Time: 11:14
 */
//验证码model//
namespace sdkService\model;
class SlideVerifyCode extends BaseNew
{

    const CODE_EXPIRE=180;//验证码有效时间
    const CREDENTIAL_EXPIRE=180;//加密串的有效时间
    const POOL_EXPIRE = 86400;//缓存池时间是1天

    protected $redis;

    public function __construct(){
        $this->redis=$this->redis(\wepiao::getChannelId(),GROUP_SHARE_FREQUENT);
    }

    protected function switchVerifyKey($id){
        return $this->swtichRedisKey(['slideId'=>$id],SLIDE_ID);
    }

    //将某个id对应的图验信息保存起来
    public function setIdInfo($id,$jsonInfo){
        $redisKey=$this->switchVerifyKey($id);
        return $this->redis->WYset($redisKey,$jsonInfo,self::CODE_EXPIRE);
    }

    //获取id对应的验证信息
    public function getIdInfo($id){
        $redisKey=$this->switchVerifyKey($id);
        return $this->redis->WYget($redisKey);
    }

    //删除缓存的信息
    public function delIdInfo($id){
        $redisKey=$this->switchVerifyKey($id);
        return $this->redis->WYdelete($redisKey);
    }

    //保存加密串
    public function saveCredential($id,$credential){
        $input = ['slideId'=>$id];
        $redisKey = $this->swtichRedisKey($input,SLIDE_CREDENTIAL);
        return $this->redis->WYset($redisKey,$credential,self::CREDENTIAL_EXPIRE);
    }

    //获取加密串
    public function getCredential($id){
        $input = ['slideId'=>$id];
        $redisKey = $this->swtichRedisKey($input,SLIDE_CREDENTIAL);
        return $this->redis->WYget($redisKey);
    }

    //删除加密串
    public function delCredential($id){
        $input = ['slideId'=>$id];
        $redisKey = $this->swtichRedisKey($input,SLIDE_CREDENTIAL);
        return $this->redis->WYdelete($redisKey);
    }

    //保存图片池
    public function setPoolInfo($standard,$hashKey,$info){
        $key = 'slide_pic_'.$standard;
        return $this->redis->WYhSet($key,$hashKey,$info);
    }

    public function getPoolInfo($standard,$hashKey){
        $key = 'slide_pic_'.$standard;
        return $this->redis->WYhGet($key,$hashKey);
    }

    public function poolExists($standard){
        $key = 'slide_pic_'.$standard;
        return $this->redis->WYexists($key);
    }

    public function setPoolExpire($standard){
        $key = 'slide_pic_'.$standard;
        return $this->redis->WYexpire($key,self::POOL_EXPIRE);
    }
}