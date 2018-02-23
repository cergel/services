<?php
namespace sdkService\model;
/**
 *报名用户信息
 * 接收前端的参数包括  用户名 电话 备注 等存入数据表   存入成功 在报名用户集合中插入该用户openid
 */
class ApplyRecord extends BaseNew{
    const CACHE_EXPIRE = ''; //现在不用缓存时间

    const DB_CONFIG_KEY = 'dbActive';

    const APPLY_USER_TABLE_NAME = 't_apply_record';

    const APPLY_USER_INFO_CACHE_EXPIRE = 864000;//活动信息缓存时间,10天

    public function __construct()
    {
        $this->redis = $this->redis(\wepiao::getChannelId(), MOVIE_SHOW_DB);
    }
    //子类继承此方法
    protected function getPdoHelper(){
        return $this->pdo(self::DB_CONFIG_KEY, self::APPLY_USER_TABLE_NAME);
    }
    /*
     * @tutorial增加报名用户信息:只是新增
     * @param array $arrData
     * @return boolean
     */
    public function insertUserInfo($inputArrData)
    {
        $arrFields = ['a_id', 'create_time', 'open_id', 'user_name', 'channel_id', 'phone', 'remark_content','from'];
        $arrData=[
            ':a_id'=>$inputArrData['a_id'],
            ':create_time'=>time(),
            ':open_id'=>$inputArrData['open_id'],
            ':user_name'=>$inputArrData['user_name'],
            ':phone'=>$inputArrData['phone'],
            ':remark_content'=>$inputArrData['remark_content'],
            ':channel_id'=>$inputArrData['channel_id'],
            ':from'=>$inputArrData['from'],
        ];
        $userId =  $this->getPdoHelper()->Nadd($arrData, $arrFields);
        if ($userId) {
            //评论过影片的用户
            $this->addApplyActiveUserCache($inputArrData['a_id'],$inputArrData['open_id']);
            $return = true;
        } else {
            $return = false;
        }
        return $return;
    }
    //增加报名活动的用户
    protected function addApplyActiveUserCache($activeId,$openId)
    {
        $redisKey=KEY_APPLY_USER_SET.$activeId;
        $redisRe = $this->redis->WYsAdd($redisKey,$openId);
        return $redisRe;
    }
    //获取集合中的元素
    public function isExistUser($activeId,$openId){
        $redisKey=KEY_APPLY_USER_SET.$activeId;
        $redisRe = $this->redis->WYsIsMember($redisKey,$openId);
        return $redisRe;
    }
    //获取报名活动的用户数
    public function applyUserNum($activeId)
    {
        $redisKey=KEY_APPLY_USER_SET.$activeId;
        $redisRe = $this->redis->WYsCard($redisKey);
        return $redisRe;
    }
}