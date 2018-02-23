<?php
/**
 * Created by PhpStorm.
 * User: panyuanxin
 * Date: 16/6/15
 * Time: 下午6:25
 */

namespace sdkService\model;

class MovieGuide extends BaseNew
{

    /**
     * 获取观影秘籍的信息
     *
     * @param $channelId
     * @param $movieId
     * @return array
     */
    public function getMovieGuideDetail($channelId, $movieId, $buy_flag)
    {
        $return = [];
        $redis = $this->redis($channelId, MOVIE_GUIDE);
        $ret = $redis->WYget(MOVIE_GUIDE_ITEM . $movieId);
        $arrItem = json_decode($ret, true);
        //获取实时领取数
        $taken_num = (int)$redis->WYsCard(MOVIE_GUIDE_TAKEN_USER . $movieId);
        //获取实时PV
        $pv_num = (int)$redis->WYhIncrBy(MOVIE_GUIDE_PV, $movieId, 1);

        //处理观影秘籍的对象
        if ($ret && $arrItem) {
            switch ($channelId) {
                //处理电影相关链接购票链接
                case 3:
                    if ($buy_flag) {
                        $ticket_url = "https://wx.wepiao.com/cinema_list_movie.html?showwxpaytitle=1&channel=wxmovie&movie_id={$arrItem['movieId']}&from={$arrItem['buy_channelId']}";
                    } else {
                        $ticket_url = "https://wx.wepiao.com/movie_detail.html?movie_id={$arrItem['movieId']}&from={$arrItem['buy_channelId']}";
                    }

                    $btn_url = $arrItem['general_link_wx'];
                    break;
                //处理手Q相关链接
                case 28:
                    if ($buy_flag) {
                        $ticket_url = "https://mqq.wepiao.com/cinema_list.html?movie_id={$arrItem['movieId']}";
                    } else {
                        $ticket_url = "https://mqq.wepiao.com/#/movies/{$arrItem['movieId']}";
                    }
                    $btn_url = $arrItem['general_link_mqq'];
                    break;
                //默认为app
                default :
                    if ($buy_flag) {
                        $ticket_url = "wxmovie://movie/cinemalist?movieid={$arrItem['movieId']}&from={$arrItem['buy_channelId']}";
                    } else {
                        $ticket_url = "wxmovie://filmdetail?movieid={$arrItem['movieId']}&from={$arrItem['buy_channelId']}";

                    }
                    $btn_url = $arrItem['general_link_app'];
                    break;
            }

            //判断是否为电影节的虚拟电影如果为电影节的电影强行有排期和跳转购票链接
            if ($movieId == "1000001") {
                if (in_array($channelId, ["8", "9"])) {
                    $ticket_url = "wxmovie://browser?url=http%3a%2f%2fff.wepiao.com%2fbjdyj2017%2fhome";
                } else {
                    $ticket_url = "http://ff.wepiao.com/bjdyj2017/home";
                }
                $buy_flag = 1;
            }

            //按照不同的渠道格式化每页幻灯片里的链接
            self::formatPage($arrItem['page'], $ticket_url, $btn_url);
            //格式化处理PV以及领取人数
            $arrItem['taken_count'] = $arrItem['baseGetCount'] + $taken_num;
            $arrItem['pv_count'] = $arrItem['basePvCount'] + $pv_num;
            $arrItem['sche_flag'] = $buy_flag;
            //判断环境和地址
            if (SERVICE_ENV == "master") {
                $arrItem['shareLink'] = "https://promotion.wepiao.com/guide/index.html?movieId={$arrItem['movieId']}";
            } else {
                $arrItem['shareLink'] = "https://promotion-pre.wepiao.com/guide/index.html?movieId={$arrItem['movieId']}";
            }

            unset($arrItem['baseGetCount']);
            unset($arrItem['basePvCount']);
            unset($arrItem['general_link_mqq']);
            unset($arrItem['general_link_wx']);
            unset($arrItem['general_link_app']);
            unset($arrItem['general_btn']);
            unset($arrItem['buy_button']);
            unset($arrItem['buy_channelId']);
            $return = $arrItem;
        }
        return $return;
    }

    /**
     * 获取观影秘籍封面信息
     * @param $channelId
     * @param $movieId
     * @return array
     */
    public function getMovieGuideInfo($channelId, $movieId)
    {
        $return = [];
        $redis = $this->redis($channelId, MOVIE_GUIDE);
        $ret = $redis->WYget(MOVIE_GUIDE_ITEM . $movieId);
        $arrItem = json_decode($ret, true);
        //获取实时领取数
        $taken_num = (int)$redis->WYsCard(MOVIE_GUIDE_TAKEN_USER . $movieId);
        $pv_num = (int)$redis->WYhGet(MOVIE_GUIDE_PV, $movieId);

        //处理观影秘籍的对象由于未领取时只有部分所以只取部分信息
        if ($ret && $arrItem) {
            $return = [];
            $return['movieId'] = $arrItem['movieId'];
            $return['title'] = $arrItem['title'];
            $return['subtitle'] = $arrItem['subtitle'];
            $return['cover_img'] = $arrItem['cover_img'];
            $return['share_img'] = !empty($arrItem['share_img']) ? $arrItem['share_img'] : '';
            if (isset($arrItem['cover_img_large'])) {
                $return['cover_img_large'] = $arrItem['cover_img_large'];
            }
            $return['taken_count'] = $arrItem['baseGetCount'] + $taken_num;
            $return['pv_count'] = $arrItem['basePvCount'] + $pv_num;
            return $return;
        }
    }

    /**
     * 获取用户对某观影秘籍的领取状态
     * @param $channelId
     * @param $movieId
     * @param $openId
     * @return string
     */
    public function getMovieGuideForUser($channelId, $movieId, $openId)
    {
        $redis = $this->redis($channelId, MOVIE_GUIDE);
        $taken_flag = $redis->WYsIsMember(MOVIE_GUIDE_TAKEN_USER . $movieId, $openId);
        if ($taken_flag) {
            return "1";
        } else {
            return "0";
        }
    }

    /**
     * 删除观影秘籍
     * @param $channelId
     * @param $movieId
     * @param $openId
     * @return mixed
     */
    public function removeMovieGuideForUser($channelId, $movieId, $openId)
    {
        $this->pdo('dbApp', 't_movieguideinfo');
        $redis = $this->redis($channelId, MOVIE_GUIDE);
        $redis->WYhDel(MOVIE_GUIDE_USER_LIST . $openId, $movieId);
        $redis->WYexpire(MOVIE_GUIDE_USER_LIST . $openId, MOVIE_GUIDE_USER_LIST_EXPIRE);
        $redis->WYsRem(MOVIE_GUIDE_TAKEN_USER . $movieId, $openId);
        return (bool)$this->pdohelper->remove("openId='{$openId}' AND movieId='{$movieId}'");
    }

    /**
     * 领取观影秘籍
     * @param $channelId
     * @param $movieId
     * @param $openId
     * @return mixed
     */
    public function takeMovieGuideForUser($channelId, $movieId, $openId)
    {
        $redis = $this->redis($channelId, MOVIE_GUIDE);
        //先判断是否可以领取如果没有这个影片信息直接返回false
        $guide_exist = $this->getMovieGuideInfo($channelId, $movieId);
        if (!$guide_exist) {
            return false;
        }


        $exist_redis = $redis->WYsIsMember(MOVIE_GUIDE_TAKEN_USER . $movieId, $openId);
        //判断已经领取的列表中是否有用户信息
        if ($exist_redis) {
            return false;
        }
        //判断数据库中有没有用户已经领取的信息如果有则补写回REDIS之中
        $this->pdo('dbApp', 't_movieguideinfo');
        $exist_db = $this->pdohelper->fetchOne("openId='{$openId}' AND movieId='{$movieId}'");
        if ($exist_db) {
            $redis->WYsAdd(MOVIE_GUIDE_TAKEN_USER . $movieId, $openId);
            return true;
        }
        $now = date("Y-m-d H:i:s");
        $this->pdo('dbApp', 't_movieguideinfo');
        $this->pdohelper->excuteBySql("INSERT INTO `t_movieguideinfo` (`openId`, `movieId`, `created_at`) VALUES ('{$openId}', '{$movieId}', '{$now}')");
        $redis->WYhSet(MOVIE_GUIDE_USER_LIST . $openId, $movieId, time());
        $redis->WYexpire(MOVIE_GUIDE_USER_LIST . $openId, MOVIE_GUIDE_USER_LIST_EXPIRE);
        $redis->WYsAdd(MOVIE_GUIDE_TAKEN_USER . $movieId, $openId);
        return true;
    }


    /**
     * 获取用户已领列表
     * @param $channelId
     * @param string $openId
     * @param int $current_page
     * @param int $num
     * @return array
     */
    public function getUserMovieGuideList($channelId, $openId = "", $current_page = 1, $num = 10)
    {
        $user_taken_list=$this->_getUserGuideIds($channelId,$openId);
        krsort($user_taken_list);
        //批量获取观影秘籍信息如果观影秘籍不存在则从用户的列表中取消
        $user_list = [];
        foreach ($user_taken_list as $movidId => $created_at) {
            $item = $this->getMovieGuideInfo($channelId, $movidId);
            if ($item) {
                $item['created_at'] = $created_at;
                $user_list[] = $item;
            }
        }
        $total = count($user_list);
        $page = ceil($total / $num);
        $data = [];
        if ($current_page >= 1 && $current_page <= $page) {
            $start = ($current_page - 1) * $num;
            if (count($user_list) > 0) {
                $data = array_slice($user_list, $start, $num);
            }
        }

        $return = [];
        $return['total'] = $total;
        $return['totalCount'] = $total;
        $return['limt'] = $num;
        $return['page'] = $page;
        $return['current'] = $current_page;
        $return['totalPage'] = $page;
        $return['curPage'] = $current_page;
        $return['nextPage'] = ($current_page+1>$page)?$page:$current_page+1;
        $return['list'] = $data;
        return $return;
    }


    private function _getUserGuideIds($channelId,$openId){
        //先从REDIS中获取是否存在用户缓存
        $redis = $this->redis($channelId, MOVIE_GUIDE);
        $user_list_exist = $redis->WYexists(MOVIE_GUIDE_USER_LIST . $openId);
        if (!$user_list_exist) {
            $this->pdo('dbApp', 't_movieguideinfo');
            $sql = "select movieId,created_at from t_movieguideinfo where  openId = '{$openId}'";
            $ret_db = $this->pdohelper->fetchArrayBySql($sql);
            $user_taken_list = [];
            foreach ($ret_db as $value) {
                $user_taken_list[$value['movieId']] = strtotime($value['created_at']);
            }
            $redis->WYhMset(MOVIE_GUIDE_USER_LIST . $openId, $user_taken_list);
        } else {
            $user_taken_list = $redis->WYhGetAll(MOVIE_GUIDE_USER_LIST . $openId);
        }
        return $user_taken_list;
    }



    /**
     * 获取用户已领总数
     * @param $channelId
     * @param string $openId
     * @param int $current_page
     * @param int $num
     * @return array
     */
    public function getUserMovieGuideCount($channelId, $openId = "")
    {
        //先从REDIS中获取是否存在用户缓存
        $user_taken_list=$this->_getUserGuideIds($channelId,$openId);
        //批量获取观影秘籍信息如果观影秘籍不存在则从用户的列表中取消
        $user_list = [];
        foreach ($user_taken_list as $movidId => $created_at) {
            $item = $this->getMovieGuideInfo($channelId, $movidId);
            if ($item) {
                $user_list[] = $item;
            }
        }
        $total = count($user_list);
        $return['totalCount'] = $total;
        return $return;
    }

    /**
     * 格式化每页之中的两个按钮与对应的链接
     * @param $pages
     * @param $ticket_url
     * @param $btn_url
     */
    private static function formatPage(&$pages, $ticket_url, $btn_url)
    {
        foreach ($pages as &$page) {
            //购票渠道unset
            if (isset($page['buy_channelId'])) {
                unset($page['buy_channelId']);
            }
            //购票链接
            if ($page['buy_flag'] == 1) {
                $page['buy_url'] = $ticket_url;
            } else {
                $page['buy_url'] = "";
            }

            if ($page['btn_flag'] != 0) {
                $page['btn_url'] = $btn_url;

            } else {
                $page['btn_url'] = "";
                $page['btn_type'] = "0";
            }

            if (isset($page['app_url'])) {
                unset($page['app_url']);
            }
            if (isset($page['wx_url'])) {
                unset($page['wx_url']);
            }
            if (isset($page['mqq_url'])) {
                unset($page['mqq_url']);
            }
        }
    }

    /**
     * 获取所有的观影秘笈
     * @param $channelId
     * @param $openId
     * @param $current_page
     * @param $num
     * @return array
     */
    public function getAllGuides($channelId, $openId, $current_page, $num)
    {
        $return = [];
        $return['total'] = 0;
        $return['page'] = (int)$current_page;
        $return['num'] = (int)$num;
        $return['data'] = [];
        $redis = $this->redis($channelId, MOVIE_GUIDE);
        //获取该用户所有的领取的观影秘笈列表
        $takenList = [];
        if (!empty($openId)) {
            $getRet = $this->getUserMovieGuideList($channelId, $openId, 1, 1);
            if ($getRet['total'] > 0) {
                $getTaken = $this->getUserMovieGuideList($channelId, $openId, 1, $getRet['total']);
                $takenList = [];
                array_walk($getTaken['list'], function ($item) use (&$takenList) {
                    $takenList[] = $item['movieId'];
                });
            }
        }

        //获取Zset的长度计算分页
        $total = $redis->WYzCard(KEY_MOVIE_GUIDE_LIST);
        $return['total'] = (int)$total;
        $page = ceil($total / $num);
        $start = ($current_page - 1) * $num;
        $data = $redis->WYzRevRange(KEY_MOVIE_GUIDE_LIST, $start, $start + $num - 1);
        if ($data) {
            //已经领取的观影秘笈增加状态
            array_walk($data, function (&$item) use ($data, $takenList) {
                $item = json_decode($item, 1);
                if (in_array($item['movieId'], $takenList)) {
                    $item['taken'] = 1;
                }
            });
            $return['data'] = $data;
        }
        return $return;
    }


}