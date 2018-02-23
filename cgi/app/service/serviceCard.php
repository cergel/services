<?php
namespace sdkService\service;

/**
 * 点卡
 * Class serviceSnack
 *
 * @package sdkService\service
 */
class serviceCard extends serviceBase
{
    
    /**
     * 查询点卡出错的文案字典,字典之外的文案,应该使用 : "点卡不可用，请更换其他点卡"
     *
     * @var array
     */
    protected $pointCardQueryErrorMap = [
        '-1_-110001' => '该点卡已过期，请更换其它点卡',
        '-1_-110002' => '点卡可用点数为0，请更换点卡',
        '-1_-110009' => '点卡不可用，请更换其他点卡',
    ];
    
    protected $pointCardQueryErrorDefaultMsg = "点卡不可用，请更换其他点卡";
    
    /**
     * 获取点卡信息
     *
     * @param Y string cardPass         点卡密码
     * @param Y string subChannelId     子渠道
     * @param Y string orderId          订单号(这个字段理论上是非必须的,但是Java要做日志记录,所以还是必传参数)
     * @param Y string mobile           用户手机号
     * @param Y string openId           用户唯一标识
     * @param Y string channelId        用户渠道ID
     */
    public function getPointCardInfo($arrInput = [])
    {
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        //获取影院原本的小吃列表
        $arrSendParams = [];
        $arrSendParams['suin'] = self::getParam($arrInput, 'openId');
        $arrSendParams['mobile'] = self::getParam($arrInput, 'mobile');
        $arrSendParams['cardPass'] = self::getParam($arrInput, 'cardPass');
        $arrSendParams['chanId'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['subChanId'] = self::getParam($arrInput, 'subChannelId');
        $arrSendParams['ordId'] = self::getParam($arrInput, 'orderId');
        $paramValiad = true;
        foreach ($arrSendParams as $paramKey => $paramVal) {
            //除了mobile参数外,其他都必须有
            if (empty( $paramVal ) && ( $paramKey != 'mobile' )) {
                $paramValiad = false;
                break;
            }
        }
        if ($paramValiad == false) {
            return $return;
        }
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'GET',
        ];
        $arrCardInfo = $this->http(JAVA_API_POINT_CARD, $httpParams);
        if (( $arrCardInfo['ret'] == 0 ) && !empty( $arrCardInfo['data'] )) {
            $return['data'] = $arrCardInfo['data'];
        }
        else {
            $mapKey = $arrCardInfo['ret'] . '_' . $arrCardInfo['sub'];
            $errMsg = $this->pointCardQueryErrorDefaultMsg;
            if (array_key_exists($mapKey, $this->pointCardQueryErrorMap)) {
                $errMsg = $this->pointCardQueryErrorMap[$mapKey];
            }
            $return['ret'] = $arrCardInfo['ret'];
            $return['sub'] = $arrCardInfo['sub'];
            $return['msg'] = $errMsg;
        }
        
        return $return;
    }
    
}