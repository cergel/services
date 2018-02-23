<?php
/**
 * 用户观影轨迹相关Model
 * User: Asus
 * Date: 2016/3/28
 * Time: 14:20
 */
namespace sdkService\model;


class Trace extends BaseNew
{
    const TRACE_EXPIRE = 5184000;
    private $traceRedis;

    public function __construct()
    {
        $this->traceRedis = $this->redis(\wepiao::getChannelId(), USER_TRACE);
    }

    /**
     * @param array $tracePath 观影轨迹的全量数组
     * @param array $orderBuyMovies 用户已购影片ID合集
     * @param string $ucId 用户中心兑换的ucId
     * @return int
     */
    public function saveTrace($tracePath = [], $orderBuyMovies = [], $ucId)
    {
        //存储观影轨迹
        if (is_array($tracePath)) {
            $pathKey = $ucId . "_path";
            //删除之前的观影轨迹避免存入重复数据
            $this->traceRedis->WYdelete($pathKey);
            foreach ($tracePath as $series => $item) {
                $this->traceRedis->WYzAdd($pathKey, $series, json_encode($item));
            }

            if ($this->traceRedis->WYexists($pathKey)) {
                $this->traceRedis->WYexpire($pathKey, self::TRACE_EXPIRE);
            }
            $tracePath = null;
        }

        //存储订单已购影片列表
        if (is_array($orderBuyMovies)) {
            $this->traceRedis->WYhSet(USER_TRACE_ORDER_BUY_MOVIES, $ucId, json_encode($orderBuyMovies));
            $orderBuyMovies = null;
        }
        //更新用户最新轨迹生成时间
        return $this->traceRedis->WYhSet(USER_TRACE_UPDATE, $ucId, time());
    }

    /**
     * 删除订单观影轨迹
     * @param array $arrInput
     */
    public function deleteTracePath(array $arrInput = []){
        $iMovieId = self::getParam($arrInput, 'movieId');
        $traceId = self::getParam($arrInput, 'traceId');
        $orderId = self::getParam($arrInput, 'orderId');
        $pathKey = $arrInput['ucId'] . "_path";
        $deleTraceKey=$arrInput['ucId'] . "_del_path";
        $traceList=$this->traceRedis->WYzRevRange($pathKey, 0, -1,true);
        $del=false;
        foreach($traceList as $key=>$val){
            $data=json_decode($key,true);
            if($data['trace_id']==$traceId && !empty($data['order']) && $data['order']['order_id']==$orderId && $data['order']['movie_id']==$iMovieId){
                $this->traceRedis->WYzRem($pathKey,$key);
                $value=$iMovieId.'-'.$orderId.'-'.$traceId;//用于记录已经删除的观影轨迹
                $this->traceRedis->WYsAdd($deleTraceKey,$value);
                $del=true;
                break;
            }
        }
        return $del;
    }

    /**
     * 获取观影轨迹
     * @param $ucID
     * @param $start
     * @param $end
     * @return mixed
     */
    public function getTrace($ucID,$start,$end)
    {
        $pathKey = $ucID . "_path";
        return $this->traceRedis->WYzRevRange($pathKey,$start,$end);
    }

    /**
     * 获取观影轨迹的数量
     * @param $ucId
     * @return int
     */
    public function getUserTraceCount($ucId)
    {
        $pathKey = $ucId . "_path";
        return (int) $this->traceRedis->WYzCard($pathKey);

    }

    /**
     * 获取用户已购影片的列表
     * @param $ucId
     * @return mixed
     */
    public function getTraceMovies($ucId)
    {
       return $this->traceRedis->WYhGet(USER_TRACE_ORDER_BUY_MOVIES, $ucId);
    }

    /**
     * 获取某个ucId观影轨迹生成的最后时间戳
     * @param $ucId
     * @return int
     */
    public function getUpdateTime($ucId)
    {
        return (int) $this->traceRedis->WYhGet(USER_TRACE_UPDATE, $ucId);
    }
}