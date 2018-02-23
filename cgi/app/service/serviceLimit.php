<?php

namespace sdkService\service;


/**
 * 限制通用类
 * Class serviceLimit
 *
 * @package sdkService\service
 */
class serviceLimit extends serviceBase
{
    
    /**
     * 记录限制的次数
     *
     * @param   int       channelId   渠道编号
     * @param   string    limitKey    唯一表示，比如限制用户的请求次数，那么这个id就是用户id相关的key，如果要限制ip，那么这个id，就是IP地址相关的key
     * @param   int       time        有效时间
     *
     * @return  array  ['ret'=>0,'sub'=>0,'data'=>['count'=>20]]
     */
    public function recordLimit($arrInput)
    {
        $return = static::getStOut();
        $iChannelId = $this->getParam($arrInput, 'channelId');
        $iTimeLimit = $this->getParam($arrInput, 'time');
        $strLimitKey = $this->getParam($arrInput, 'limitKey');
        $return['data']['count'] = $this->model('Limit')->recordLimit($iChannelId, $strLimitKey, $iTimeLimit);
        
        return $return;
    }
    
    
    /**
     * 获取IP调用次数
     *
     * @param   string  limitKey    唯一表示，比如限制用户的请求次数，那么这个id就是用户id相关的key，如果要限制ip，那么这个id，就是IP地址相关的key
     * @param   int     channelId   渠道编号
     *
     * @return  array   ['ret'=>0,'sub'=>0,'data'=>['count'=>231]]
     */
    public function getLimit($arrInput)
    {
        $return = static::getStOut();
        $iChannelId = $this->getParam($arrInput, 'channelId');
        $strLimitKey = $this->getParam($arrInput, 'limitKey');
        $return['data']['count'] = $this->model('Limit')->getLimit($iChannelId, $strLimitKey);
        
        return $return;
    }
    
    /**
     * 修改限制时间
     * 用途，例如：当IP的请求触发限制，在超过限制的那一次调用，将key的过期时间延长（默认30分钟），之间此ip访问受限
     *
     * @param   string  limitKey        唯一表示，比如限制用户的请求次数，那么这个id就是用户id相关的key，如果要限制ip，那么这个id，就是IP地址相关的key
     * @param   int     channelId       渠道编号
     * @param   int     extendLimitTime 限制的秒数，默认30分钟
     *
     * @return array
     */
    public function extendLimitTime($arrInput)
    {
        $return = static::getStOut();
        $iChannelId = $this->getParam($arrInput, 'channelId');
        $strLimitKey = $this->getParam($arrInput, 'limitKey');
        $iExtendLimitTime = $this->getParam($arrInput, 'extendLimitTime');
        $changeExpire = $this->model('Limit')->extendLimitTime($iChannelId, $strLimitKey, $iExtendLimitTime);
        if ( !$changeExpire) {
            $return = ['ret' => -1, 'sub' => -1, 'msg' => 'change expire time fail'];
        }
        
        return $return;
    }
    
    /**
     * 设置限制次数，但是，倘若已经超出了限制，则直接返回超出限制的信息，不再设置限制
     *
     * @param   int     channelId       渠道编号，如果多个渠道要共享限制，那channelId传一个公共值
     * @param   array   config          限制配置，如果是限定ip每分钟5000此和openId每分钟30次，则可以这样写：
     *                  [['127.0.0.1'=>['limit'=>5000,'time'=>'60','businessId'=>'lockSeat',],'o0aT-d2sxxxxHYzsraVXcW7e2gdU'=>['limit'=>30,'time'=>'60','businessId'=>'lockSeat',]]
     *                  说明：
     *                  limit：次数。time：时效频率，单位秒。businessId：业务方id，适用于不同接口想用不同限制的场景
     *                  注意：数组中key就是限制维度，但这个key不要太长，因为要存入redis，redis对key的要求是不超过64个字节
     *
     * @return array    ret为0，表示正常。ret非0，有可能是超过了限制，也有可能是参数错误
     */
    public function commonLimit($arrInput = [])
    {
        $return = static::getStOut();
        $iChannelId = $this->getParam($arrInput, 'channelId');
        $arrConfig = $this->getParam($arrInput, 'config');
        
        if ( !empty($iChannelId) && is_array($arrConfig) && !empty($arrConfig)) {
            foreach ($arrConfig as $strLimitId => $arrLimitInfo) {
                //是否有业务标识，有的话，limitKey要加上业务标识
                $strBusinessId = !empty($arrLimitInfo['businessId']) ? $arrLimitInfo['businessId'] : '';
                //获取限制次数
                $iLimit = !empty($arrLimitInfo['limit']) ? $arrLimitInfo['limit'] : '';
                //限制频率
                $iTime = !empty($arrLimitInfo['time']) ? $arrLimitInfo['time'] : '';
                //参数判断，只要不合法，就直接中断，返回
                if (empty($strLimitId) || empty($iLimit) || empty($iTime)) {
                    $return = $this->getErrorOut(ERRORCODE_LIMIT_PARAM_ERROR);
                    break;
                }
                $strLimitKey = !empty($strLimitId) ? $iChannelId . '_' . $strLimitId . '_' . $strBusinessId : '';
                //判断key已经超了限制
                //$arrGetLimitInfo = $this->getLimit(['channelId' => $iChannelId, 'limitKey' => $strLimitKey]);
                //增加限制次数
                $arrGetLimitInfo = $this->recordLimit(['channelId' => $iChannelId, 'time' => $iTime, 'limitKey' => $strLimitKey]);
                if (isset($arrGetLimitInfo['ret']) && ($arrGetLimitInfo['ret'] == 0) && !empty($arrGetLimitInfo['data']['count']) && ($arrGetLimitInfo['data']['count'] >= $iLimit)) {
                    $return = $this->getErrorOut(ERRORCODE_LIMIT_OVER_ERROR);
                    $return['data'] = [
                        'channelId'  => $iChannelId,    //渠道编号
                        'config'     => $arrConfig,     //配置
                        'limitCount' => $iLimit,        //限制次数
                        'limitId'    => $strLimitId,    //达到次数的key
                        'dimension'  => !empty($arrLimitInfo['dimension']) ? $arrLimitInfo['dimension'] : '',   //维度，其实就是说，被限制住的Key，是什么，如：ip、openId
                    ];
                    
                    return $return;
                }
                //增加限制次数
                //$this->recordLimit(['channelId' => $iChannelId, 'time' => $iTime, 'limitKey' => $strLimitKey]);
            }
        } else {
            $return = $this->getErrorOut(ERRORCODE_LIMIT_PARAM_ERROR);
        }
        
        return $return;
    }
    
    /**
     * 查询某个IP，是否在IP黑名单
     */
    public function checkIfInIpBlack($arrInput = [])
    {
        $return = static::getStOut();
        $return['data'] = false;
        $iChannelId = $this->getParam($arrInput, 'channelId');
        $strIp = $this->getParam($arrInput, 'ip');
        if (empty($strIp)) {
            return $return;
        }
        //解析IP为IP段
        $arrIpSegment = explode('.', $strIp);
        $ipNet = implode('.', array_slice($arrIpSegment, 0, 3));    //IP的网络号
        $ipHost = implode('', array_slice($arrIpSegment, -1, 1));    //IP的主机号
        //从redis中，获取限制规则
        $strKey = 'ipblack_' . $ipNet;
        $arrLimitRules = $this->model('Limit')->getIpLimitRule($iChannelId, $strKey);
        if (empty($arrLimitRules) || !isset($arrLimitRules['limitNet']) || !isset($arrLimitRules['ipHost'])) {
            return $return;
        }
        //判断是否限制IP段，如果限制的是IP段，则直接返回true（因为我们已经根据IP段查询出内容了）
        $iLimitIpNet = $arrLimitRules['limitNet'];
        if ($iLimitIpNet == 1) {
            $return['data'] = true;
        } elseif ($iLimitIpNet == 0) {
            $arrIpHost = !empty($arrLimitRules['ipHost']) ? json_decode($arrLimitRules['ipHost']) : [];
            $return['data'] = in_array($ipHost, $arrIpHost);
        }
        
        return $return;
    }
    
}