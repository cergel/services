<?php
/**
 * Created by PhpStorm.
 * User: panyuanxin
 * Date: 2016/12/23
 * Time: 下午3:38
 */

namespace sdkService\service;


class serviceFilmList extends serviceBase
{
    /**
     * 获取某个片单以及包含
     * @param array $arrInput
     * @return array
     */
    public function getFilmList($arrInput = [])
    {
        $return = $this->getStOut();
        $channelId = $arrInput['channelId'];
        $page = isset($arrInput['page']) ? $arrInput['page'] : 1;
        $listId = isset($arrInput['listId']) ? $arrInput['listId'] : "";
        $num = isset($arrInput['num']) ? $arrInput['num'] : 5;
        $filter = isset($arrInput['filter']) ? $arrInput['filter'] : "";
        $cityId = isset($arrInput['cityId']) ? $arrInput['cityId'] : 10;
        $ucId = empty($arrInput['unionId']) ? $arrInput['openId'] : $arrInput['unionId'];
        $unionId = isset($arrInput['unionId']) ? $arrInput['unionId'] : "";
        if (!empty($unionId)) {
            $ucId = $unionId;
        }

        //如果阅读过第一页则阅读数+1
        if ($page == 1) {
            $this->model("FilmList")->filmListCount($channelId, $listId);
        }

        $data = $this->model("FilmList")->getFilmList($channelId, $listId, $page, $num, $cityId, $filter);
        $movies_origin = [];
        $svipmovie_num = $buy_flag_num = 0;
        //获取影片信息各种过滤分页
        if ($data) {
            foreach ($data['list'] as $key => $value) {
                $params = [];
                $params['movieId'] = $value['movie_id'];
                $params['cityId'] = $cityId;
                $params['channelId'] = $channelId;
                $movieInfo = $this->service("movie")->getMovieInfoMsdb($params);
                if (!empty($movieInfo['data']['id'])) {
                    $movies_origin[$key]['id'] = $movieInfo['data']['id'];
                    $movies_origin[$key]['name'] = $movieInfo['data']['name'];
                    $movies_origin[$key]['actor'] = $movieInfo['data']['actor'];
                    $movies_origin[$key]['date'] = $movieInfo['data']['date'];
                    $movies_origin[$key]['date_des'] = $movieInfo['data']['date_des'];
                    $movies_origin[$key]['year'] = $movieInfo['data']['year'];
                    $movies_origin[$key]['month'] = $movieInfo['data']['month'];
                    $movies_origin[$key]['date_status'] = $movieInfo['data']['date_status'];
                    $movies_origin[$key]['buy_flag'] = $movieInfo['data']['buy_flag'];
                    $movies_origin[$key]['initScore'] = isset($movieInfo['data']['initScore']) ? $movieInfo['data']['initScore'] : 0;
                    $movies_origin[$key]['wantCount'] = isset($movieInfo['data']['wantCount']) ? $movieInfo['data']['wantCount'] : 0;
                    $movies_origin[$key]['seenCount'] = isset($movieInfo['data']['seenCount']) ? $movieInfo['data']['seenCount'] : 0;
                    $movies_origin[$key]['scoreCount'] = isset($movieInfo['data']['scoreCount']) ? $movieInfo['data']['scoreCount'] : 0;
                    $movies_origin[$key]['will_flag'] = $movieInfo['data']['will_flag'];
                    $movies_origin[$key]['poster_url'] = $movieInfo['data']['poster_url'];
                    $movies_origin[$key]['poster_url_size3'] = $movieInfo['data']['poster_url_size3'];
                    $movies_origin[$key]['svipmovie'] = $movieInfo['data']['svipmovie'];
                    $movies_origin[$key]['desc'] = $value['movie_desc'];
                    //判断是否包含手机电影
                    if ($movieInfo['data']['svipmovie']) {
                        $svipmovie_num++;
                    }
                    if ($movieInfo['data']['buy_flag']) {
                        $buy_flag_num++;
                    }
                }
            }
        } else {
            $movies_origin = [];
        }

        $totalAll = count($movies_origin);
        $movies = [];
        if ($filter == "svipmovie") {
            foreach ($movies_origin as $key => $value) {
                if ($value['svipmovie']) {
                    $movies[$key] = $value;
                }
            }
        } elseif ($filter == "buy_flag") {
            foreach ($movies_origin as $key => $value) {
                if ($value['buy_flag']) {
                    $movies[$key] = $value;
                }
            }
        } else {
            $movies = $movies_origin;
        }

        ksort($movies);
        reset($movies);
        $movies = array_values($movies);
        //进行内存分页处理
        $total = count($movies);
        $offset = ($page - 1) * $num;
        $response = array(array_slice($movies, $offset, $num));
        if ($offset < 0 || $offset > $total) {
            $response = [];
        }
        unset($data['list']);

        if (!empty($response[0])) {
            $data['list'] = $response[0];
        } else {
            $data['list'] = [];
        }
        $data['total'] = $totalAll;
        $data['svipmovie_num'] = $svipmovie_num;
        $data['buy_flag_num'] = $buy_flag_num;
        if ($ucId) {
            $data['info']['favourite'] = (int)$this->model("FilmList")->getFavouriteStatus($channelId, $ucId, $listId);
        } else {
            $data['info']['favourite'] = 0;
        }
        $return['data'] = $data;
        return $return;
    }


    /**
     * 通过影片ID查询出包含此影片的片单信息
     * @param array $arrInput
     * @return array
     */
    public function getFilmListByMovieId($arrInput = [])
    {
        $return = $this->getStOut();
        $channelId = $arrInput['channelId'];
        $movieId = isset($arrInput['movieId']) ? $arrInput['movieId'] : '';
        $data = $this->model("FilmList")->getFilmListByMovieId($channelId, $movieId);
        if ($data) {
            foreach ($data as &$listItem) {
                foreach ($listItem['images'] as $key => &$movieId) {
                    $params = ['channelId' => $channelId, "movieId" => $movieId];
                    $movieInfo = $this->service("movie")->getMovieInfoMsdb($params);
                    if (!empty($movieInfo['data']['id'])) {
                        $movieId = $movieInfo['data']['poster_url'];
                    }
                }
            }
        } else {
            $data = [];
        }
        //对数据进行排序
        $sortkey = [];
        foreach ($data as $key => $item) {
            $sortkey[$key] = $item['collect_num'];
        }
        array_multisort($sortkey, SORT_DESC, $data);
        $return['data'] = $data;
        return $return;
    }

    /**
     * 收藏取消收藏片单
     * @param array $arrInput
     * @return array
     */
    public function favourite($arrInput = [])
    {
        $return = $this->getStOut();
        $channelId = $arrInput['channelId'];
        $listId = isset($arrInput['listId']) ? $arrInput['listId'] : '';
        $ucId = empty($arrInput['unionId']) ? $arrInput['openId'] : $arrInput['unionId'];
        $uid = isset($arrInput['uid']) ? $arrInput['uid'] : "";
        $action = isset($arrInput['action']) ? $arrInput['action'] : "1";
        //兑换出uid给用户默认填写上uid方便将来数据打通
        $return['data'] = $this->model("FilmList")->favourite($channelId, $action, $listId, $ucId, $uid);
        return $return;
    }

    /**
     * 我的片单
     * @param array $arrInput
     * @return array
     */
    public function getFavouriteList($arrInput = [])
    {
        $return = $this->getStOut();
        $channelId = $arrInput['channelId'];
        $ucId = empty($arrInput['unionId']) ? $arrInput['openId'] : $arrInput['unionId'];
        $page = isset($arrInput['page']) ? $arrInput['page'] : 1;
        $num = isset($arrInput['num']) ? $arrInput['num'] : 10;
        $data = $this->model("FilmList")->myFavourite($channelId, $ucId, $page, $num);

        //对片单进行格式化
        $listInfo = $this->model("FilmList")->mGetFilmList($channelId, $data['list']);
        if ($listInfo) {
            foreach ($listInfo as &$listItem) {
                foreach ($listItem['images'] as $key => &$movieId) {
                    $params = ['channelId' => $channelId, "movieId" => $movieId];
                    $movieInfo = $this->service("movie")->getMovieInfoMsdb($params);
                    $movieId = $movieInfo['data']['poster_url'];
                }
            }
        } else {
            $listInfo = [];
        }
        //推荐片单
        $recommend = $this->model("FilmList")->getRecommend($channelId, 3);
        if ($recommend) {
            foreach ($recommend as &$listItem) {
                foreach ($listItem['images'] as $key => &$movieId) {
                    $params = ['channelId' => $channelId, "movieId" => $movieId];
                    $movieInfo = $this->service("movie")->getMovieInfoMsdb($params);
                    $movieId = isset($movieInfo['data']['poster_url']) ? $movieInfo['data']['poster_url'] : '';
                }
            }
        }
        $data['recommend'] = $recommend;
        $data['list'] = $listInfo;
        $return['data'] = $data;
        return $return;
    }

    /***
     * 获取feed流中的片单
     * @param array $arrInput
     * @return array
     */
    public function feedFilmList($arrInput = [])
    {
        $return = $this->getStOut();
        $channelId = $arrInput['channelId'];
        $filmListId = $arrInput['listId'];
        //获取片单信息
        $listInfo = $this->model("FilmList")->mGetFilmList($channelId, [$filmListId]);
        if ($listInfo) {
            foreach ($listInfo as &$listItem) {
                foreach ($listItem['images'] as $key => &$movieId) {
                    $params = ['channelId' => $channelId, "movieId" => $movieId];
                    $movieInfo = $this->service("movie")->getMovieInfoMsdb($params);
                    $movieId = $movieInfo['data']['poster_url'];
                }
            }
            $return['data'] = !empty($listInfo[0]) ? $listInfo[0] : new \stdClass();
        } else {
            $return['data'] = new \stdClass();
        }
        return $return;
    }


    public function removeMovieFromList($arrInput = [])
    {
        $return = $this->getStOut();
        $channelId = $arrInput['channelId'];
        $filmListId = $arrInput['listId'];
        $movieId = $arrInput['movieId'];
        $response = $this->model("FilmList")->removeMovieFromList($channelId, $filmListId, $movieId);
        if (!$response) {
            $return['ret'] = -1;
            $return['msg'] = 删除失败;
        }
        return $return;
    }
}