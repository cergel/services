<?php

namespace sdkService\model;

use \sdkService\helper\RedisManager;
use wepiao\wepiao;
use DI\Definition\ArrayDefinition;

/**
 * @tutorial 发现相关内容
 * @author liulong
 *
 */
class CmsFind extends BaseNew
{
    const CMS_FIND_INFO_TIME = -1;//缓存一个月
    const DB_NAME = 'dbActive';
    const DB_TABLE_NAME = 't_active_find';
    public function __construct()
    {
        $this->redis = $this->redis(\wepiao::getChannelId(), MOVIE_SHOW_DB);
    }

    /**
     * 获取一条cms内容--列表用的
     * @param $id
     * @return array|bool|mixed|string
     */
    public function getOneCmsFindInfo($id)
    {
        $arrData = $this->redis->WYget(CMS_FIND_INFO.$id);
        if(empty($arrData)){//不存在，则被动
            $this->pdo(self::DB_NAME, self::DB_TABLE_NAME);
            $where = "id = :id AND `status`='1'";
            $paramData = [':id'=>$id];
            $dbRe = $this->pdohelper->fetchOne($where,$paramData);
            if($dbRe){//存在，则查询出所有的渠道链接
                //$arrChannel = $this->model('CmsFindChannel')->getChannelUrlByFid($id);
                $sql = "SELECT * FROM t_active_find_channel WHERE f_id='$id'";
                $arrChannel = $this->pdohelper->fetchArrayBySql($sql);
                $arrData = self::getFieldByFind($dbRe,$arrChannel);
                $this->redis->WYset(CMS_FIND_INFO.$id,json_encode($arrData));
            }else{
                $this->redis->WYset(CMS_FIND_INFO.$id,json_encode([]));
            }
        }else{
            $arrData = json_decode($arrData,true);
        }
        $this->redis->WYexpire(CMS_FIND_INFO.$id,self::CMS_FIND_INFO_TIME);
        return $arrData;
    }

    /**
     * 整合需要的字段
     * @param $dbRe
     * @param array $arrChannel
     * @return array
     */
    private function getFieldByFind($dbRe,$arrChannel=[])
    {
        if(empty($dbRe))return [];
        $arrData = [];
        $arrData['id'] = $dbRe['id'];
        $arrData['a_id'] = $dbRe['a_id'];
        $arrData['f_type'] = $dbRe['f_type'];
        $arrData['f_title'] = $dbRe['f_title'];
        $arrData['f_cover'] = CDN_APPNFS . $dbRe['f_cover'];
        $arrData['up_time'] = $dbRe['up_time'];
        if (is_array($arrChannel)){
            foreach($arrChannel as $result) {
                $arrData['channel_url_'.$result['channel_id']] =$result['f_url'];
            }
        }
        return $arrData;
    }
}
