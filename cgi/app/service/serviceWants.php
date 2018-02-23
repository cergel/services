<?php
/**
 * Created by PhpStorm.
 * User: xiangli
 * Date: 2016/11/29
 * Time: 15:21
 */

namespace sdkService\service;


class serviceWants extends serviceBase
{
    /**
     * 获取用户想看电影清单
     * @param array $arrInput
     * @return array
     */
    public function getUserWantMovieList($arrInput = []){
        $return = self::getStOut();
        $return['data'] = new \stdClass();
        $channelId = self::getParam($arrInput, 'channelId');
        $page= self::getParam($arrInput, 'page', 1);
        $num= self::getParam($arrInput, 'num', 10);
        $cityId=self::getParam($arrInput, 'cityId');
        $ucid= self::getParam($arrInput, 'ucid');
        $sort= self::getParam($arrInput,"sort", 0);
        $method= self::getParam($arrInput,"method", 'desc');
        if(!empty($ucid)){
            if (!$sort) {
                $return['data'] = $this->wantWithCreated($channelId, $page, $num, $ucid,$cityId);
            } else {
                $return['data'] = $this->wantWithMovieDate($channelId, $page, $num, $ucid, $method,$cityId);
            }
        }
        return $return;
    }


    /**
     * 获取用户想看电影总数
     * @param array $arrInput
     * @return array
     */
    public function getUserWantMovieCount($arrInput = []){
        $return = self::getStOut();
        $return['data']['totalCount'] = 0;
        $channelId = self::getParam($arrInput, 'channelId');
        $ucid= self::getParam($arrInput, 'ucid');
        if(!empty($ucid)){
            //获取用户的想看清单(标记时间)
            $res=$this->_getUserWant($ucid,$channelId);//movies=>[],totalCount=>5
            if (!empty($res)) {
                $return['data']['totalCount'] = $res['totalCount'];
            }
        }
        return $return;
    }

    /**
     * 根据想看时间获取想看电影列表
     * @param $channelId
     * @param $page
     * @param $num
     * @param $ucid
     * @return array
     */
    protected function wantWithCreated($channelId, $page, $num, $ucid,$cityId)
    {
        $return = ['movies' => [], 'totalCount' => "0",'total' => "0",'totalPage'=>0,'curPage'=>0,'nextPage'=>0];
        //获取用户的想看清单(标记时间)
        $res=$this->_getUserWant($ucid,$channelId,$page,$num);//movies=>[],totalCount=>5
        if (!empty($res)) {
            $MovieIDs = $res['movies'];
            $totalPage=ceil($res['totalCount']/$num);
            $return['totalCount'] = $res['totalCount'];
            $return['total'] = $res['totalCount'];
            $return['totalPage'] = ceil($res['totalCount']/$num);
            $return['curPage'] = $page;
            $return['nextPage'] = ($page+1>$totalPage)?$totalPage:$page+1;
            //格式化影片信息
            $return['movies'] = self::_formatUserWantMovies($MovieIDs, $channelId,$cityId);
        }
        return $return;
    }

    /**
     * 按照上映日期排序
     * @param $channelId
     * @param $page
     * @param $num
     * @param $ucid
     * @param string $sort
     * @return array
     */
    protected function wantWithMovieDate($channelId, $page, $num, $ucid, $sort = "desc",$cityId)
    {
        $return = ['movies' => [], 'totalCount' => "0", 'total' => "0",'totalPage'=>0,'curPage'=>0,'nextPage'=>0];
        //方法内的函数
        $PageFun = function ($totalNum, $page, $pageNum) {
            $return = [
                'start' => 0,
                'limit' => 10,
            ];
            $maxPage = ceil($totalNum / $pageNum);
            if ($page > $maxPage) {
                return false;
            }
            if ($page < 1) {
                return false;
            }
            $return['start'] = $pageNum * ($page - 1);
            if ($maxPage == $page) {
                $return['limit'] = $totalNum - ($page - 1) * $pageNum;
            } else {
                $return['limit'] = $pageNum;
            }
            return $return;
        };
        //获取用户的想看清单(标记时间)
        $wantList=$this->_getUserWant($ucid,$channelId,1,10000);
        if(!empty($wantList['movies'])){
            //格式化影片信息
            $MovieInfo = $wantList['movies'];
            //格式化影片信息
            $MovieInfo=self::_formatUserWantMovies($MovieInfo, $channelId,$cityId);
            $MovieSort = [];
            $Date = [];
            $WantPaging = [];
            //按照上映日期排序,因为日期类型为str不好直接用array_multisort直接排序所以转换时间戳先
            foreach ($MovieInfo as $key => $item) {
                $Date[$key] = strtotime($item['date']);
            }
            //判断排序方式是啥
            if ($sort == "desc") {
                arsort($Date);
            } else {
                asort($Date);
            }
            $sortKey = array_keys($Date);
            foreach ($sortKey as $value) {
                $MovieSort[] = $MovieInfo[$value];
            }
            //开始进行假分页获取开始和结束的项目号
            $totalNum = count($MovieSort);
            $item = $PageFun($totalNum, $page, $num);
            if ($item) {
                $WantPaging = array_slice($MovieSort, $item['start'], $item['limit']);
            }
            $return['totalCount']=$wantList['totalCount'];
            $totalPage=ceil($wantList['totalCount']/$num);
            $return['total'] = $wantList['totalCount'];
            $return['totalPage'] = $totalPage;
            $return['curPage'] = $page;
            $return['nextPage'] = ($page+1>$totalPage)?$totalPage:$page+1;
            $return['movies']=$WantPaging;
        }
        return $return;
    }

    /**
     * 根据影片id获取影片信息
     * @param $MovieIDs
     * @param $iChannelId
     * @return array
     */
    private function _formatUserWantMovies($MovieIDs, $iChannelId,$cityId)
    {
        $arrInput['channelId'] = $iChannelId;
        $returnData = [];
        foreach ($MovieIDs as $iMovieId) {
            $arrInput['movieId'] = $iMovieId;
            $arrInput['cityId'] = $cityId;
            $arrRes = $this->service('movie')->readMovieInfo($arrInput);
            if (!empty($arrRes['data']['id'])) {
                $data = $arrRes['data'];
                if ($data) {
                    if (isset($data['prevue']) AND empty($data['prevue'])) {
                        $data['prevue'] = new \stdClass();
                    }
                    unset($data['still_list']);
                    unset($data['videos']);
                    $data['created'] = time();
                    $returnData[] = $data;
                }
            }
        }
        return $returnData;
    }

    /**
     * 加密ucid得到token
     * @param $ucid
     * @param $channelId
     * @return string
     */
    private function _getToken($ucid,$channelId){
        $return ='';
        $params = [];
        //客户端加密的是openId数组
        if($channelId==8 || $channelId==9){
            $params['str'] = json_encode(['openId' => $ucid]);
        }
        //手Q跟微信加密的ucid字符串
        if($channelId==28 || $channelId==3){
            $params['str'] = $ucid;
        }
        //格瓦拉渠道
        if ($channelId == 80 || $channelId == 84) {
            $params['str'] = json_encode(['gewaraid' => $ucid]);;
        }
        $params['t'] = time() + 3600;
        $params['channelId'] = $channelId;
        $response = $this->service('Common')->encrypt($params);
        if (!empty($response['data']['encryptStr'])) {
            $return = $response['data']['encryptStr'];
        }
        return $return;
    }

    /**
     * 获取用户的想看列表 本方法有分页酌情调取
     * @param $channelId
     * @param int $page
     * @param int $num
     * @return array
     */
    private function _getUserWant($ucid,$channelId, $page = 1, $num = 10)
    {
        $return = [];
        $token=$this->_getToken($ucid,$channelId);
        $url = COMMENT_CENTER_URL . "/v1/users/want-movies";
        $param = [];
        $param['sMethod'] = "GET";
        $param['iTimeout'] = 2;
        $param['arrData'] = [
            'token'=>$token,
            'ucid'=>$ucid,
            'channelId' => $channelId,
            'page' => $page,
            'num' => $num,
        ];
        $response = $this->http($url, $param);
        if ($response['ret'] == 0 && $response['sub'] == 0) {
            $return = $response['data'];
        }
        return $return;
    }
}