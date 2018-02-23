<?php

namespace sdkService\service;

/**
 * 获取影片的原声音乐列表
 * Class serviceMusic
 * @package sdkService\service
 */
class serviceMusic extends serviceBase
{
    /**
     * 读取某个电影的原声音乐
     * @param array $arrInput
     * @return array
     * @throws \Exception
     */
    public function getMusicInfo(array $arrInput=[])
    {
        //参数整理
        $arrInstallData = [
            'movieId' => self::getParam($arrInput, 'movieId'),  //影片id
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
            'openId' => self::getParam($arrInput, 'openId'), //用户openId
        ];
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['movieId']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_MOVIE_MUSIC_NO_DATA);
        }
        if ($arrReturn['ret'] == 0 && $arrReturn['sub'] == 0){
            $arrData = $this->model('MovieMusic')->getMovieMusic($arrInstallData['movieId']);
            $arrReturn['data'] = $arrData;
        }
        return $arrReturn;
    }

    /**
     * 设置某个电影的原声音乐redis
     * @param array $arrInput
     * @return array
     * @throws \Exception
     */
    public function setMusicInfo(array $arrInput=[])
    {
        //参数整理
        $arrInstallData = [
            'movieId' => self::getParam($arrInput, 'movieId'),  //影片id
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
            'openId' => self::getParam($arrInput, 'openId'), //用户openId
        ];
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['movieId']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_MOVIE_MUSIC_NO_DATA);
        }
        if ($arrReturn['ret'] == 0 && $arrReturn['sub'] == 0){
            //从数据库读取
            $arrData = $this->model('MovieMusic')->setMovieMusic($arrInstallData['movieId']);
            if($arrData===true){
                $arrReturn['data'] = $arrData;
            }
            else{
                if($arrData===-1){
                    $arrReturn = self::getErrorOut(ERRORCODE_MOVIE_MUSIC_NO_MOVIE);
                }
                elseif($arrData===-2 || $arrData===-3){
                    $arrReturn = self::getErrorOut(ERRORCODE_MOVIE_MUSIC_NO_MOVIEINFO);
                }
                else{
                    $arrReturn = self::getErrorOut(ERRORCODE_MOVIE_MUSIC_SET_ERROR);
                }
            }
        }
        return $arrReturn;
    }

    /**
     * 删除某个电影的原声音乐redis
     * @param array $arrInput
     * @return array
     * @throws \Exception
     */
    public function delMusicInfo(array $arrInput=[])
    {
        //参数整理
        $arrInstallData = [
            'movieId' => self::getParam($arrInput, 'movieId'),  //影片id
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
            'fromId' => self::getParam($arrInput, 'from'),    //子来源
            'openId' => self::getParam($arrInput, 'openId'), //用户openId
        ];
        $arrReturn = self::getStOut();
        if (empty($arrInstallData['movieId']) || empty($arrInstallData['channelId'])) { //参数空校验
            $arrReturn = self::getErrorOut(ERRORCODE_MOVIE_MUSIC_NO_DATA);
        }
        if (empty($arrReturn['ret'])){
            $arrData = $this->model('MovieMusic')->delMovieMusic($arrInstallData['movieId']);
            if(!$arrData){
                $arrReturn = self::getErrorOut(ERRORCODE_MOVIE_MUSIC_DEL_ERROR);
            }
            else{
                $arrReturn['data'] = $arrData;
            }
        }
        return $arrReturn;
    }
}