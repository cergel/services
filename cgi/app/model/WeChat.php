<?php
namespace sdkService\model;


class WeChat extends BaseNew
{
    private $wechatRedis;
    //锁的过期时间
    const TIMEOUT = 600000;
    //设置睡眠时间
    const SLEEP = 30000;
    protected static $expire;

    public function __construct()
    {
        $this->wechatRedis = $this->redis(\wepiao::getChannelId(), WEI_XIN_TOKEN);
    }

    /**
     * 获得锁，如果锁被占用，阻塞，直到获得锁或者超时
     * @param  string $key
     * @param  int $timeout 等待获取锁的时间
     * @return boolean    成功，true；失败，false
     */
    public function lock($key, $timeout = 0)
    {
        if (!$key) {
            return false;
        }
        $start = time();
        do {
            self::$expire = self::timeout();
            if ($acquired = $this->wechatRedis->WYsetnx("Lock:" . $key, self::$expire)) {
                break;
            }
            usleep(self::SLEEP);
        } while (time() < $start + $timeout);
        if (!$acquired) {
            //超时
            return false;
        }
        return true;
    }

    /**
     *
     * 释放锁
     */
    public function release($key)
    {
        if (!$key) {
            return false;
        }
        // Only release the lock if it hasn't expired
        if (self::$expire > time()) {
            $this->wechatRedis->WYdelete("Lock:" . $key);
        }
    }

    /**
     * 基于当前时间生成过期时间
     * @return int    timeout
     */
    protected static function timeout()
    {
        return (int)(time() + self::TIMEOUT + 1);
    }

    public function getWechatRedis($redisKey)
    {
        return $this->redis(\wepiao::getChannelId(), WEI_XIN_TOKEN)->WYget($redisKey);
    }

    public function setWechatRedis($strKey, $strValue, $expiretime)
    {
        return $this->redis(\wepiao::getChannelId(), WEI_XIN_TOKEN)->WYset($strKey, $strValue, $expiretime);
    }

    /*
     * 微信登录，保存“用户”access_token，默认一个半小时（token本身两小时过期）
     */
    public function saveWxUserToken($openId, $accessToken, $expiretime = 5400)
    {
        $strKey = WX_USER_ACCESS_TOKEN . $openId;
        return $this->redis(\wepiao::getChannelId(), STATIC_MOVIE_DATA)->WYset($strKey, $accessToken, $expiretime);
    }
    
    /*
     * 微信小程序登录，保存“用户”access_token，默认一个半小时（token本身两小时过期）
     */
    public function saveWxappUserToken($openId, $accessToken, $expiretime = 5400)
    {
        $strKey = WXAPP_USER_ACCESS_TOKEN . $openId;
        return $this->redis(STATIC_MOVIE_DATA)->WYset($strKey, $accessToken, $expiretime);
    }

    /**
     * 微信登录，获取“用户”access_token
     */
    public function getWxUserToken($openId)
    {
        $strKey = WX_USER_ACCESS_TOKEN . $openId;
        return $this->redis(\wepiao::getChannelId(), STATIC_MOVIE_DATA)->WYget($strKey);
    }
    
    /**
     * 微信小程序登录，获取“用户”access_token
     */
    public function getWxappUserToken($openId)
    {
        $strKey = WXAPP_USER_ACCESS_TOKEN . $openId;
        return $this->redis(STATIC_MOVIE_DATA)->WYget($strKey);
    }
}

?>