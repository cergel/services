<?php
/**
 * 用户标签
 */
namespace sdkService\model;


class Tag extends BaseNew
{
    /**
     * 初始化redis
     * @param $channelId
     * @return null|\redisManager\redisOperator
     */
    public function initRedis($channelId)
    {
        try {
            $objRedis = $this->redis($channelId, USER_TAG);
            return $objRedis;
        } catch (\Exception $e) {
            //日志记录
            //wepiao::info('[redis error] ' . $e);
            return null;
        }
    }

    /**
     * 向redis导入标签数据
     * @param $openid
     * @param $tags
     * @param $channelId
     * @return bool|int
     */
    public function ImportIntoRedis($openid, $tags, $channelId)
    {
        $objRedis = $this->initRedis($channelId);
        return $objRedis->WYhMset($openid, $tags);
    }

    /**
     * 取指定tag的数据
     * @param $openid
     * @param $arrTags ['tag1',tag2',...]
     * @param $channelId
     * @return array
     */
    public function getTags($openid, $arrTags, $channelId)
    {
        $objRedis = $this->initRedis($channelId);
        return $objRedis->WYhMGet($openid, $arrTags);
    }

    /**
     * 取所有tag的数据
     * @param $openid
     * @param $channelId
     * @return array
     */
    public function getTagsAll($openid, $channelId)
    {
        $objRedis = $this->initRedis($channelId);
        return $objRedis->WYhGetAll($openid);
    }

}
