<?php
namespace sdkService\model;

/**
 * 报名活动被动缓存
 * @tutorial
 * @author
 *#
 */
class ApplyActive extends BaseNew{
    const CACHE_EXPIRE = ''; //现在不用缓存时间

    const DB_CONFIG_KEY = 'dbActive';

    const APPLY_ACTIVE_TABLE_NAME = 't_apply_active';

    const APPLY_ACTIVE_INFO_CACHE_EXPIRE = 864000;//活动信息缓存时间,10天

    public function __construct()
    {
        $this->redis = $this->redis(\wepiao::getChannelId(), MOVIE_SHOW_DB);
    }

    //子类继承此方法
    protected function getPdoHelper(){
        return $this->pdo(self::DB_CONFIG_KEY, self::APPLY_ACTIVE_TABLE_NAME);
    }
    /**获取活动信息，redis为空时被动缓存
     * @param $active_id
     * @return bool|string
     */
    public function findApplyActive($active_id)
    {
        $redisKey=KEY_APPLY_ACTIVE_DATA.$active_id;
        $cacheTime=self::APPLY_ACTIVE_INFO_CACHE_EXPIRE;
        $redisRe=$this->redis->WYget($redisKey);
        if(!empty($redisRe)){
            return $redisRe;
        }else{
            $where='id = ' . $active_id;
            $fields='id,start_apply,end_apply,end_display,support,is_form,is_remark,remark';
            $params=[];
            $dbRe = $this->getPdoHelper()->fetchOne($where,$params,$fields);
            $where ='a_id ='.$active_id;
            $fields='platform';
            $dbRe1 = $this->pdo(self::DB_CONFIG_KEY, 't_apply_active_platform')->fetchAll($where,$params,$fields);
            $platforms=[];
            foreach($dbRe1 as $val){
                $platforms[]=$val->platform;
            }
            $dbRe['platform']=$platforms;
            if($dbRe){
                $return = json_encode($dbRe);
                $this->redis->WYset($redisKey,$return);
            }else{//返回为空时给redis存入空
                $return = '';
                $this->redis->WYset($redisKey,$return);
            }
        }
        $this->redis->WYexpire($redisKey,$cacheTime);
        return $return;
    }
}