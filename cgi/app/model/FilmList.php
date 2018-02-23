<?php
/**
 * Created by PhpStorm.
 * User: panyuanxin
 * Date: 2016/12/23
 * Time: 下午3:59
 */

namespace sdkService\model;


class FilmList extends BaseNew
{
    /***
     * 获取片单信息
     * @param $channelId
     * @param $listId
     * @return mixed|string
     */
    public function getFilmList($channelId, $listId)
    {
        $redis = $this->redis($channelId, FILM_LIST);
        $item = $redis->WYhGet(KEY_FILM_LIST_ITEM, $listId);
        $item = json_decode($item, true);
        return $item;
    }

    /***
     * 已经阅读数计数器
     * @param $channelId
     * @param $listId
     */
    public function filmListCount($channelId, $listId)
    {
        $redis = $this->redis($channelId, FILM_LIST);
        $redis->WYhIncrBy(KEY_FILM_LIST_COUNT, $listId, 1);
    }

    /**
     * 通过影片ID获取包含此ID的片单
     * @param $channelId
     * @param $movieId
     * @return array
     */
    public function getFilmListByMovieId($channelId, $movieId)
    {
        $return = [];
        $redis = $this->redis($channelId, FILM_LIST);
        $res = $redis->WYsMembers(KEY_FILM_LIST_MOVIE . $movieId);
        if (is_array($res)) {
            $list = $redis->WYhMGet(KEY_FILM_LIST_ITEM, $res);
        } else {
            $list = [];
        }
        if (!$list) {
            $list = [];
        }
        foreach ($list as $key => $value) {
            //拼装所需要的片单信息
            $item = json_decode($value, true);
            $return[$key]['listId'] = $key;
            $return[$key]['title'] = $item['info']['title'];
            $return[$key]['count'] = count($item['list']);
            $return[$key]['collect_num'] = (int)$item['info']['collect_num'];
            $return[$key]['images'] = [];
            $i = 0;
            foreach ($item['list'] as $movieId) {
                if ($i <= 2) {
                    $return[$key]['images'][] = $movieId['movie_id'];
                    $i++;
                }
            }
        }
        $return = array_values($return);
        return $return;
    }

    /**
     * 片单收藏
     * @param $channelId
     * @param $action
     * @param $listId
     * @param $ucId
     * @param $uid
     * @return bool
     */
    public function favourite($channelId, $action, $listId, $ucId, $uid)
    {
        $redis = $this->redis($channelId, FILM_LIST);
        if ($action == 1) {
            //检查一下影片维度用户是否收藏了该片单
            $check_exist = $redis->WYsIsMember(KEY_FILM_LIST_USERS . $listId, $ucId);
            if ($check_exist) {
                return false;
            }
            //检查一下用户维度是否收藏过该片单
            $check_exist = $redis->WYzRank(KEY_FILM_LIST_USER . $ucId, $listId);
            if ($check_exist) {
                return false;
            }
            //查询db里是否收藏了片单如果收藏了写入缓存
            $this->pdo('dbApp', 't_favor_filmlist');
            $exist_db = $this->pdohelper->fetchOne("ucId='{$ucId}' AND listId='{$listId}'");
            if ($exist_db) {
                $redis->WYsAdd(KEY_FILM_LIST_USERS . $listId, $ucId);
                $redis->WYzAdd(KEY_FILM_LIST_USER . $ucId, time(), $listId);
                $redis->WYexpire(KEY_FILM_LIST_USER . $ucId, 3600 * 24 * 7);
                return true;
            }
            //没有收藏添加一条相关的记录
            $this->pdo('dbApp', 't_favor_filmlist');
            $now = time();
            $this->pdohelper->excuteBySql("INSERT INTO `t_favor_filmlist` (`ucId`, `listId`, `created_at`, `updated_at`,`channelId`,`memberId`) VALUES ('{$ucId}', '{$listId}', '{$now}','{$now}','{$channelId}','{$uid}')");
            $redis->WYsAdd(KEY_FILM_LIST_USERS . $listId, $ucId);
            $redis->WYzAdd(KEY_FILM_LIST_USER . $ucId, time(), $listId);
            $redis->WYexpire(KEY_FILM_LIST_USER . $ucId, 3600 * 24 * 7);
            return true;
        } else {
            //移除用户以及影片片单
            $redis->WYsRem(KEY_FILM_LIST_USERS . $listId, $ucId);
            $redis->WYzRem(KEY_FILM_LIST_USER . $ucId, $listId);
            $this->pdo('dbApp', 't_favor_filmlist');
            return (bool)$this->pdohelper->remove("ucId='{$ucId}' AND listId='{$listId}'");
        }
    }

    public function getFavouriteStatus($channelId, $ucId, $listId)
    {
        $redis = $this->redis($channelId, FILM_LIST);
        //检查一下影片维度用户是否收藏了该片单
        $check_exist = $redis->WYsIsMember(KEY_FILM_LIST_USERS . $listId, $ucId);
        if ($check_exist) {
            return true;
        }
        //检查一下用户维度是否收藏过该片单
        $check_exist = $redis->WYzRank(KEY_FILM_LIST_USER . $ucId, $listId);
        if ($check_exist) {
            return true;
        }
        //查询db里是否收藏了片单如果收藏了写入缓存
        $this->pdo('dbApp', 't_favor_filmlist');
        $exist_db = $this->pdohelper->fetchOne("ucId='{$ucId}' AND listId='{$listId}'");
        if ($exist_db) {
            $redis->WYsAdd(KEY_FILM_LIST_USERS . $listId, $ucId);
            $redis->WYzAdd(KEY_FILM_LIST_USER . $ucId, time(), $listId);
            $redis->WYexpire(KEY_FILM_LIST_USER . $ucId, 3600 * 24 * 7);
            return true;
        }
        return false;

    }

    public function myFavourite($channelId, $ucId, $page, $num)
    {

        $return = [];
        $redis = $this->redis($channelId, FILM_LIST);
        //获取用户的收藏列表
        $key_exist = $redis->WYexists(KEY_FILM_LIST_USER . $ucId);
        if (!$key_exist) {
            $this->pdo('dbApp', 't_favor_filmlist');
            $sql = "select listId,created_at from t_favor_filmlist where  ucId = '{$ucId}'";
            $ret_db = $this->pdohelper->fetchArrayBySql($sql);
            foreach ($ret_db as $value) {
                $redis->WYzAdd(KEY_FILM_LIST_USER . $ucId, $value['created_at'], $value['listId']);
            }
            $redis->WYexpire(KEY_FILM_LIST_USER . $ucId, 3600 * 24 * 7);
        }

        $total = (int)$redis->WYzCard(KEY_FILM_LIST_USER . $ucId);
        $limit = $num < 1 ? 1 : $num;
        $start = ($page - 1) * $num;
        $end = $start + $limit - 1;
        $return['list'] = $redis->WYzRevRange(KEY_FILM_LIST_USER . $ucId, $start, $end);
        $return['total'] = $total;
        $return['page'] = $page;
        $return['num'] = $num;
        return $return;
    }

    //批量获取片单信息
    public function mGetFilmList($channelId, $listIds = [])
    {
        if (empty($listIds)) {
            return [];
        }
        $redis = $this->redis($channelId, FILM_LIST);
        $list = $redis->WYhMGet(KEY_FILM_LIST_ITEM, $listIds);
        if (!$list) {
            $list = [];
        }
        $return = [];
        foreach ($list as $key => $value) {
            //拼装所需要的片单信息
            $item = json_decode($value, true);
            //过滤已经下线的项目
            if (!$item) {
                continue;
            }

            $return[$key]['listId'] = $key;
            $return[$key]['title'] = $item['info']['title'];
            $return[$key]['count'] = count($item['list']);
            $return[$key]['collect_num'] = (int)$item['info']['collect_num'];
            $return[$key]['images'] = [];
            $i = 0;
            foreach ($item['list'] as $movieId) {
                if ($i <= 2) {
                    $return[$key]['images'][] = $movieId['movie_id'];
                    $i++;
                }
            }
        }
        $return = array_values($return);
        return $return;
    }

    //获取推荐片单
    public function getRecommend($channelId, $num)
    {
        $list = [];
        $redis = $this->redis($channelId, FILM_LIST);
        $list = $redis->WYsMembers(KEY_FILM_LIST_TOP20);
        if ($list) {
            $max = count($list);
            if ($num > $max) {
                $num = 1;
            }
            $randlist = array_rand($list, $num);
            if ($randlist === false) {
                $randlist = [];
            }
            $filmIds = [];
            if (is_array($randlist)) {
                foreach ($randlist as $value) {
                    $filmIds[] = $list[$value];
                }
            } else {
                $filmIds[] = $list[$randlist];
            }
            $list = $this->mGetFilmList($channelId, $filmIds);
        }
        return $list;
    }

    //移除某个片单下的某部电影
    public function removeMovieFromList($channelId, $filmListId, $movieId)
    {
        $redis = $this->redis($channelId, FILM_LIST);
        return $redis->WYsRem(KEY_FILM_LIST_MOVIE . $movieId, $filmListId);
    }


}