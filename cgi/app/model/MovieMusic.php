<?php

namespace sdkService\model;


class MovieMusic extends BaseNew
{
    private $keyTemplate=MOVIE_MUSIC_LIST;
    const CACHE_EXPIRE = 2592000;// 30 * 24 * 60 * 60 缓存一个月
    public function __construct()
    {
        $this->pdo('dbAdmin', 't_movie_music');
        $this->redis = $this->redis(\wepiao::getChannelId(), USER_GENERATED_CONTENT);
    }

    /**
     * 读取影片音乐列表
     * @param $iChannelId
     * @param $iMovieId
     * @return array|\stdClass
     */
    public function getMovieMusic($iMovieId)
    {
        $input = ['movieId'=>$iMovieId];
        $redisKey = $this->swtichRedisKey($input,$this->keyTemplate);
        $isExists = $this->redis->WYexists($redisKey);
        if(!$isExists){
            $data = '';
        }
        else{
            $data = $this->redis->WYget($redisKey);
        }
        return $this->helper('OutPut')->jsonConvert($data);
    }

    /**
     * 读取数据库设置redis
     * @param $iChannelId
     * @param $iMovieId
     */
    public function setMovieMusic($iMovieId){
        $where = 'id = :id';
        $params = [
            ':id' => $iMovieId,
        ];
        $input = ['movieId'=>$iMovieId];
        $redisKey = $this->swtichRedisKey($input,$this->keyTemplate);
        $music = $this->pdohelper->fetchOne($where,$params);
        if(empty($music)){
            return -1;
        }
        $musicInfoModel = new MovieMusicInfo();
        //设置Redis
        $return=array();
        $return['movie_id']=$iMovieId;
        $return['movie_name']=$music['movie_name'];
        $return['movie_cover']= CDN_APPNFS . '/uploads/MovieMusic'.$music['cover'];
        $musicData=$musicInfoModel->readMovieMusicInfo($iMovieId);
        $musicList=array();
        if(count($musicData)==0){
            $this->redis->WYdelete($redisKey);//如果音乐列表为空，也删除该Redis
            return -2;
        }
        foreach($musicData as $val){
            if($val['ws_play_url']=='' && $val['cc_play_url']==''){
                continue;
            }
            $arr=array();
            $arr['song_id']=$val['song_id'];
            $arr['song_name']=$val['song_name'];
            $arr['singer_pic']=$val['singer_pic'];
            $arr['singer_name']=$val['singer_name'];
            $arr['album_id']=$val['album_id'];
            $arr['album_name']=$val['album_name'];
            $arr['ws_play_url']=$val['ws_play_url'];
            $arr['cc_play_url']=$val['cc_play_url'];
            array_push($musicList,$arr);
        }
        //如果返回的音乐列表为空，则删除该Redis
        if(empty($musicList)){
            $this->redis->WYdelete($redisKey);
            return -3;
        }
        $return['music_list']=$musicList;
        $return=json_encode($return);
        $redisRe = $this->redis->WYset($redisKey,$return);
        $this->redis->WYexpire($redisKey, self::CACHE_EXPIRE);
        return $redisRe;
    }

    /**
     * 删除某个电影的rediskey
     * @param $iChannelId
     * @param $iMovieId
     */
    public function delMovieMusic($iMovieId){
        $input = ['movieId'=>$iMovieId];
        $redisKey = $this->swtichRedisKey($input,$this->keyTemplate);
        $redisRe = $this->redis->WYdelete($redisKey);
        return $redisRe;
    }
}