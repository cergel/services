<?php
/**
 * 观影秘籍service
 * Created by PhpStorm.
 * User: panyuanxin
 * Date: 16/6/15
 * Time: 下午6:17
 */

namespace sdkService\service;


class serviceMovieGuide extends serviceBase
{
    /**
     * 观影秘籍用户领取列表
     * @param string channelId 用户的渠道号
     * @param string openId 用户的openId
     * @param string page 用户当前页数
     * @return mixed
     */
    public function getMovieGuideList($arrInput = [])
    {
        $return = $this->getStOut();
        $openId = $arrInput['openId'];
        $channelId = $arrInput['channelId'];
        $page = isset($arrInput['page']) ? $arrInput['page'] : 1;
        $num = isset($arrInput['num']) ? $arrInput['num'] : 10;
        $return['data'] = $this->model("MovieGuide")->getUserMovieGuideList($channelId, $openId, $page,$num);
        return $return;
    }

    /**
     * 观影秘籍用户领取总数
     * @param string channelId 用户的渠道号
     * @param string openId 用户的openId
     * @param string page 用户当前页数
     * @return mixed
     */
    public function getMovieGuideCount($arrInput = [])
    {
        $return = $this->getStOut();
        $return['data']['totalCount']=0;
        $openId = $arrInput['openId'];
        $channelId = $arrInput['channelId'];
        $data = $this->model("MovieGuide")->getUserMovieGuideCount($channelId, $openId);
        if(isset($data['totalCount'])){
            $return['data']['totalCount']=$data['totalCount'];
        }
        return $return;
    }

    /**
     * 查询某条观影秘籍并获取用户的领取信息
     *
     * @param array $arrInput
     * @return array
     */
    public function getMovieGuide($arrInput = [])
    {
        $return = $this->getStOut();
        $openId = $arrInput['openId'];
        $channelId = $arrInput['channelId'];
        $movieId = $arrInput['movieId'];
        //先获取观影秘籍的详情
        $ret = $this->model("MovieGuide")->getMovieGuideInfo($channelId, $movieId);
        //判断用户是否领取过本观影秘籍
        if (empty($openId) && !empty($ret)) {
            $ret['taken'] = "0";
        } elseif (!empty($ret)) {
            $ret['taken'] = $this->model("MovieGuide")->getMovieGuideForUser($channelId, $movieId, $openId);
        }

        if (empty($ret)) {
            $return['data'] = new \stdClass();
        } else {
            $return['data'] = $ret;
        }

        return $return;
    }

    public function takeMovieGuide($arrInput = [])
    {
        $return = $this->getStOut();
        $openId = $arrInput['openId'];
        $channelId = $arrInput['channelId'];
        $movieId = $arrInput['movieId'];
        //先获取观影秘籍的详情
        $ret = (bool)$this->model("MovieGuide")->takeMovieGuideForUser($channelId, $movieId, $openId);
        $return['data'] = $ret;
        return $return;
    }

    public function removeMovieGuide($arrInput = [])
    {
        $return = $this->getStOut();
        $openId = $arrInput['openId'];
        $channelId = $arrInput['channelId'];
        $movieId = $arrInput['movieId'];
        //先获取观影秘籍的详情
        $ret = (bool)$this->model("MovieGuide")->removeMovieGuideForUser($channelId, $movieId, $openId);
        $return['data'] = $ret;
        return $return;
    }

    /**
     * 查询某条观影秘籍并获取用户的领取信息
     *
     * @param array $arrInput
     * @param string openId 用户openId
     * @param string chennelId 渠道Id
     * @param string movieId 影片Id
     * @param string cityId 城市ID
     * @return array
     */
    public function GetMovieGuideDetail($arrInput = [])
    {
        $return = $this->getStOut();
        $openId = $arrInput['openId'];
        $channelId = $arrInput['channelId'];
        $movieId = $arrInput['movieId'];
        $cityId = isset($arrInput['cityId']) ? $arrInput['cityId'] : "0";

        $buy_flag = "0";
        //获取当前城市是否有排期

        $param = [
            'channelId' => $channelId,
            'movieId' => $movieId,
            'cityId' => $cityId,
        ];
        $data_buy_flag = $this->service('movie')->getScheFlagOfMovieCity($param);
        if ($data_buy_flag['ret'] == 0 && $data_buy_flag['sub'] == 0) {
            $buy_flag = $data_buy_flag['data']['scheFlag'];
        } else {
            $buy_flag = 0;
        }
        //先获取观影秘籍的详情
        $ret = $this->model("MovieGuide")->getMovieGuideDetail($channelId, $movieId, $buy_flag);

        //判断用户是否领取过本观影秘籍
        if (empty($openId) && !empty($ret)) {
            $ret['taken'] = "0";
        } elseif (!empty($ret)) {
            $ret['taken'] = $this->model("MovieGuide")->getMovieGuideForUser($channelId, $movieId, $openId);
        }

        if (empty($ret)) {
            $return['data'] = new \stdClass();
        } else {
            $return['data'] = $ret;
        }

        return $return;
    }

    /**
     * 查询出所有的观影秘笈
     * @param string openId 用户openId
     * @param string chennelId 渠道Id
     * @return array
     */
    public function getAllGuides($arrInput = [])
    {
        $return = $this->getStOut();
        $openId = $arrInput['openId'];
        $channelId = $arrInput['channelId'];
        $page = isset($arrInput['page']) ? $arrInput['page'] : 1;
        $num = isset($arrInput['num']) ? $arrInput['num'] : 10;
        $return['data'] = $this->model("MovieGuide")->getAllGuides($channelId, $openId, $page, $num);
        return $return;
    }
}