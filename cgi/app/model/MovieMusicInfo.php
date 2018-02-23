<?php

namespace sdkService\model;


class MovieMusicInfo extends BaseNew
{
    public function __construct()
    {
        $this->pdo('dbAdmin', 't_movie_music_info');
    }

    /**
     * 读取某个影片的音乐列表
     * @param $iMovieId
     * @return array|\stdClass
     */
    public function readMovieMusicInfo($iMovieId)
    {
        $fields = '*';
        $where = "m_id = :m_id and status=1";//得到获取到音乐的列表
        $params = [
            ':m_id' => $iMovieId,
        ];
        $data = $this->pdohelper->fetchArray($where, $params,$fields);
        return $data;
    }
}