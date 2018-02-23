<?php

namespace sdkService\model;

use sdkService\helper\Http;


class Favorite extends BaseNew
{

    const MAX_FAVORITE_COUNT = 30; //最大收藏数

    const MAX_FAVORITE_ERROR = 101; //超出最大收藏数
    const DATA_ERROR = 102; //数据库错误
    const CACHE_ERROR = 103; //缓存错误
    const FAVORITE_STATUS_ERROR = 104; //收藏状态错误

    //const FAVORITE_TABLE_NAME = 'wx_user_favorite_new';


    protected $dbConfig = 'db1';
    protected $tableName = 'wx_user_favorite_new';



    const REDIS_KEY = 'user_favorite_';
    const IS_FAVORITE_KEY = "user_is_favorite_";

    const MAIN_SWITCH = true; //总开关
    const IS_ACCESS_DB = true; //是否访问收藏
    const CACHE_EXPIRE = 604800; // 7 * 24 * 60 * 60 被动缓存时间
    const CACHE_EXPIRE_INITIATIVE = 2592000;// 30 * 24 * 60 * 60 主动缓存时间2592000

//    public function __construct()
//    {
//        $this->pdo('db1', self::FAVORITE_TABLE_NAME);
//    }
    /**
     * @param $iChannelId int 渠道编号,字符串类型的数字
     * @param $strOpenId string 用户openId
     * @param $iCinemaId string 影院id
     * @param $status int  状态,是否收藏, 0为收藏, 1为取消收藏
     * @return bool
     * @throws \Exception
     */
    public function favoriteCinema($iChannelId, $strOpenId, $iCinemaId, $status)
    {
        if(!self::MAIN_SWITCH){
            return true;
        }
        try {
            if( $status === 0){
                $this->_favorite($iChannelId, $strOpenId, $iCinemaId);
            }elseif( $status === 1){
                $this->_unFavorite($iChannelId, $strOpenId, $iCinemaId);
            }else{
                throw new \Exception('参数错误', self::FAVORITE_STATUS_ERROR);
            }
            return $this->_updateUserFavoriteStatus($strOpenId, $iChannelId);

        } catch (\RedisException $e) {
            if(self::IS_ACCESS_DB)
                return $this->_updateFavoriteDB($strOpenId, $iCinemaId, $status, $iChannelId);
            else
                return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param $iChannelId
     * @param $strOpenId
     * @return array|mixed
     */
    public function getFavoriteCinema($iChannelId, $strOpenId)
    {
        if(!self::MAIN_SWITCH) //总开关关闭，停止一切操作，返回成功状态
            return [];
        $openIDs = $this->_getOpenIdsFromUC($strOpenId);
        try {
            $record = [];
            foreach ($openIDs as $value) {
                $isFavorite = $this->_checkUserIsFavorite($iChannelId, $value);
                if ($isFavorite) { //拥有收藏
                    $strKey = self::REDIS_KEY . $value;
                    $objRedis = $this->redis($iChannelId, USER_GENERATED_CONTENT);
                    $isExistsMainKey = $objRedis->WYexists($strKey);
                    if(!$isExistsMainKey && self::IS_ACCESS_DB){ //缓存过期
                        $this->_write2Redis($iChannelId, $value); //写入缓存
                    }
                    $result = $this->_getFavoriteFromRedis($iChannelId, $value);
                    $record = array_merge($result, $record);
                }
            }
            return array_values(array_unique($record));
        } catch (\PDOException $e) {
            return [];
        } catch (\RedisException $e) {
            if(self::IS_ACCESS_DB)
                return $this->_getFavoriteFromDB($strOpenId,$iChannelId);
            else
                return [];
        }
    }

    /**
     * 从redis获取数据
     * @param $iChannelId
     * @param $openId
     * @return array
     */
    private function _getFavoriteFromRedis($iChannelId, $openId)
    {
        $strKey = self::REDIS_KEY . $openId;
        $objRedis = $this->redis($iChannelId, USER_GENERATED_CONTENT);
        $result = $objRedis->WYhGetAll($strKey);
        if (!empty($result)) {
            return array_keys($result);
        } else {
            return [];
        }
    }

    /**
     * 检查用户是否收藏了影院
     * @param $iChannelId
     * @param $openId
     * @return bool|string
     */
    private function _checkUserIsFavorite($iChannelId, $openId)
    {
        $strKey = self::IS_FAVORITE_KEY . $openId;
        $objRedis = $this->redis($iChannelId, USER_GENERATED_CONTENT);
        return $objRedis->WYget($strKey);
    }

    /**
     * @param $iChannelId
     * @param $openId
     * @param $iCinemaId
     * @return bool
     * @throws \Exception
     */
    private function _favorite($iChannelId, $openId, $iCinemaId)
    {
        $strKey = self::REDIS_KEY . $openId;
        $isExistsMainKey = $this->redis($iChannelId, USER_GENERATED_CONTENT)->WYexists($strKey);
        if(!$isExistsMainKey){ //大key不存在
            $this->_write2Redis($iChannelId, $openId); //写入缓存
        }
        $isExistsSubKey = $this->redis($iChannelId, USER_GENERATED_CONTENT)->WYhExists($strKey, $iCinemaId); //域名是否存在
        if(!$isExistsSubKey){ //小key不存在，证明用户没有收藏过
            $favoriteLength = $this->redis($iChannelId, USER_GENERATED_CONTENT)->WYhLen($strKey);
            if($favoriteLength >= self::MAX_FAVORITE_COUNT)
                throw new \Exception('超出最大收藏数', self::MAX_FAVORITE_ERROR);
            $this->redis($iChannelId, USER_GENERATED_CONTENT)->WYhSet($strKey, $iCinemaId, 1);
            $this->_updateFavoriteDB($openId, $iCinemaId, 0, $iChannelId);
        }
        return $this->redis($iChannelId, USER_GENERATED_CONTENT)->WYexpire($strKey, (!self::IS_ACCESS_DB) ? self::CACHE_EXPIRE_INITIATIVE : self::CACHE_EXPIRE);
    }

    /**
     * 取消收藏
     * @param $iChannelId
     * @param $openId
     * @param $iCinemaId
     * @return mixed
     * @throws \Exception
     */
    private function _unFavorite($iChannelId, $openId, $iCinemaId)
    {
        $openIds = $this->_getOpenIdsFromUC($openId);
        try{
            foreach($openIds as $key => $value)
            {
                $this->_updateFavoriteDB($value, $iCinemaId, 1, $iChannelId); //数据库取消收藏
                $strKey = self::REDIS_KEY . $value;
                $objRedis = $this->redis($iChannelId, USER_GENERATED_CONTENT);
                $isExistsMainKey = $objRedis->WYexists($strKey);
                if(!$isExistsMainKey){ //缓存过期
                    $this->_write2Redis($iChannelId, $value); //写入缓存
                }
                $objRedis->WYhDel($strKey, $iCinemaId); //缓存取消收藏
            }
            return true;
        }catch (\Exception $e){
            return false;
        }
    }

    /**
     * 设置用户缓存
     * @param $iChannelId
     * @param $openId
     * @internal param $result
     */
    private function _write2Redis($iChannelId, $openId)
    {
        $result = $this->_getFavoriteFromDB($openId, $iChannelId);
        //转换数据结构，用以适合redis缓存
        $cache = [];
        foreach ($result as $key => $value) {
            $cache[$value] = 1;
        }
        if(!empty($cache)){
            $strKey = self::REDIS_KEY . $openId;
            $objRedis = $this->redis($iChannelId, USER_GENERATED_CONTENT);
            $objRedis->WYhMset($strKey, $cache);
            $objRedis->WYexpire($strKey, (!self::IS_ACCESS_DB) ? self::CACHE_EXPIRE_INITIATIVE : self::CACHE_EXPIRE); //存7天
        }
    }

    /**
     * 更新用户已经收藏影院状态
     * @param $openId
     * @param $iChannelId
     * @return bool
     */
    private function _updateUserFavoriteStatus($openId, $iChannelId)
    {
        $openIds = $this->_getOpenIdsFromUC($openId);
        try{
            foreach($openIds as $key => $value){
                //获取收藏影院个数
                $mainKey = self::REDIS_KEY . $value;
                $objRedis = $this->redis($iChannelId, USER_GENERATED_CONTENT);
                $favoriteNum = $objRedis->WYhLen($mainKey);
                //更新是否收藏过影院
                $strKey = self::IS_FAVORITE_KEY . $value;
                if($favoriteNum){
                    $objRedis->WYset($strKey, 1);
                }else{
                    $objRedis->WYdelete($strKey);
                }
            }
            return true;
        }catch (\Exception $e)
        {
            return false;
        }



    }

    /**
     * 从数据库获取用户收藏数据
     * @param $openId
     * @return array
     */
    private function _getFavoriteFromDB($openId, $channelId)
    {
        $fields = 'cinema_no';
        $params = [
            ':open_id' => $openId,
            ':status' => 0,
        ];
        $rst = $this->getPdoHelper()->fetchArray("open_id = :open_id and status = :status", $params, $fields);
        $result = [];
        foreach ($rst as $key => $value)
        {
            $result[] = (int)$value['cinema_no'];
        }

        return $result;
    }

    /**
     * 获取用户收藏的所有影院
     *
     * @param string $strOpenId
     * @param $iChannelId
     * @return array|mixed
     */
    public function getFavoriteCinemas($iChannelId, $strOpenId)
    {
        $strKey = USER_FAVORITE_CINEMAS . $strOpenId;
        $data = $this->redis($iChannelId, USER_GENERATED_CONTENT)->WYhGetAll($strKey);
        if (!empty($data)) {
            $data = array_keys($data);
        } else {
            $data = [];
        }

        return $data;
    }

    /**
     * 更新影院收藏数据库
     *
     * @param string $strOpenId
     * @param string $cinemaId
     * @param int $status
     * @param $channelId
     * @return bool
     * @throws \Exception
     */
    private function _updateFavoriteDB($strOpenId, $cinemaId, $status, $channelId)
    {
        $params = [
            ':open_id' => $strOpenId,
            ':cinema_no' => $cinemaId,
            ':status' => $status,
            ':update_time' => time(),
        ];
        try {
            $isHave = $this->_checkIsHave($strOpenId, $cinemaId);
            if ($isHave) { //更新
                $fields = ['status', 'update_time'];
                $where = 'open_id =:open_id and cinema_no =:cinema_no';
                $result = $this->getPdoHelper()->update($fields, $params, $where);

            } else { //插入
                if ( $status === 1)
                    return true;
                $fields = ['open_id', 'cinema_no', 'status', 'update_time'];
                $result =$this->getPdoHelper()->Nadd($params, $fields);
            }
        } catch (\PDOException $e) {
            throw new \Exception('数据库错误', self::DATA_ERROR);
        }
        return $result;
    }

    /**
     * 通过任意OpenId 获取用户id树
     * @param $openId
     * @return array
     */
    private function _getOpenIdsFromUC($openId)
    {
        try{
            //$postData = json_encode(['id' => $openId]);
            $postData = ['id' => $openId];
            $http = Http::instance();
            $url = JAVA_ADDR_USERCENTER . DIRECTORY_SEPARATOR . "uc/v1/getidrelation";
            //$response = $http->post($url, $postData);

            $httpParams = [
                'arrData' => $postData,
                'sMethod' => 'POST',
                'sendType'=>'json'
            ];

            $responseData = $this->http($url,$httpParams);
            //$responseData = json_decode($response['response'], true);
            if($responseData['ret'] === 0 && !empty($responseData['data']['idRelation']['idUnderBound'])){
                $openIds[] = $responseData['data']['idRelation']['id'];
                $openIds = array_merge($openIds, $this->_recursiveOpenId($responseData['data']['idRelation']['idUnderBound']));
                return array_unique($openIds);
            }else{
                return [$openId];
            }
        }catch (\Exception $e){
            return [$openId];
        }
    }
    //递归用户树，获取openIDs
    private function _recursiveOpenId($data)
    {
        $openIds = [];
        foreach ($data as $key => $value) {
            $openIds[] = $value['id'];
            if (!empty($value['idUnderBound'])) {
                $result = $this->_recursiveOpenId($value['idUnderBound']);
                $openIds = array_merge($openIds, $result);
            }
        }
        return $openIds;
    }


    /**
     * 检测用户是否收藏过某个影院
     *
     * @param string $strOpenId
     * @param string $cinemaId
     * @return bool
     * @throws \PDOException
     * @throws \Exception
     */
    private function _checkIsHave($strOpenId, $cinemaId)
    {
        try {
            $field = 'count(*) as isHave';
            $params = [
                ':open_id' => $strOpenId,
                ':cinema_no' => $cinemaId,
            ];
            $where = 'open_id =:open_id and cinema_no =:cinema_no';
            $rst = $this->getPdoHelper()->fetchValue($where, $params, $field);
            if ($rst == '1') { //更新
                return true;
            } else { //插入
                return false;
            }
        } catch (\PDOException $e) {
            throw $e;
        }
    }

}