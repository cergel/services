<?php
namespace sdkService\model;

class Mqq extends BaseNew
{
    /*
     * 获取手Q公众号access_token
     */
    public function getMptoken()
    {
        $redisKey = MQQ_MP_ACCESS_TOKEN;
        return $this->redis(\wepiao::getChannelId(), GROUP_SHARE_FREQUENT)->WYget($redisKey);
    }

    /**
     * 保存手Q公众号access_token至redis
     */
    public function setMptoken($value, $expireTime = 0)
    {
        $redisKey = MQQ_MP_ACCESS_TOKEN;
        return $this->redis(\wepiao::getChannelId(), GROUP_SHARE_FREQUENT)->WYset($redisKey, $value, $expireTime);
    }

    /*
     * 手Q登录，保存accessToken，默认保存一个月
     */
    public function saveMqqUserToken($openId, $accessToken, $expiretime = 2592000)
    {
        $strKey = MQQ_USER_ACCESS_TOKEN . $openId;
        return $this->redis(\wepiao::getChannelId(), STATIC_MOVIE_DATA)->WYset($strKey, $accessToken, $expiretime);
    }

    /**
     * 获取手Q用户accessToken
     */
    public function getMqqUserToken($iChannelId, $openId)
    {
        $strKey = MQQ_USER_ACCESS_TOKEN . $openId;
        return $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYget($strKey);
    }

    /**
     * 手Q个人资料页，即将上映影片推荐
     */
    public function getMovieWillRecommend()
    {
        $strKey = MQQ_RECOMMEND_WILL;
        return $this->redis(\wepiao::getChannelId(), GROUP_SHARE_FREQUENT)->WYzRange($strKey, 0, -1);
    }

    /**
     * 手Q发现页，文章3、4之间推荐
     */
    public function getDiscoveryRecommend()
    {
        $strKey = MQQ_RECOMMEND_FIND;
        return $this->redis(\wepiao::getChannelId(), GROUP_SHARE_FREQUENT)->WYhGetAll($strKey);
    }

    /**
     * 获取用户手Q公众号推送开关
     */
    public function getMqqPushSwitch($openId)
    {
        $prefix = 'mqqpush_';
        $strKey = $prefix . $openId;
        return $this->redis(\wepiao::getChannelId(), GROUP_SHARE_FREQUENT)->WYget($strKey);
    }

    /**
     * 保存用户手Q公众号推送开关
     */
    public function setMqqPushSwitch($openId, $value)
    {
        $prefix = 'mqqpush_';
        $strKey = $prefix . $openId;
        return $this->redis(\wepiao::getChannelId(), GROUP_SHARE_FREQUENT)->WYset($strKey, $value);
    }
}

?>
