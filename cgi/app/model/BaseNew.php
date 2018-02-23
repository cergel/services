<?php
namespace sdkService\model;

use redisManager\redisOperator;
use sdkService\drivers;
use sdkService\helper;
use sdkService\base\Base;

/**
 * Created by PhpStorm.
 * User: syj
 * Date: 16/3/10
 * Time: 下午4:00
 */
class BaseNew extends Base
{
    public $pdohelper = null;

    protected $dbConfig = '';
    protected $tableName = '';

    protected $redis;

    public function  __construct()
    {

    }

    /**
     * @param string $TPId
     * @param string $redisConKey
     *
     * @return \redisTools\redisOperator
     */
    public function redis($TPId = '', $redisConKey = STATIC_MOVIE_DATA)
    {
        if (empty($TPId)) {
            return null;
        }

        return drivers\RedisManager::getInstance($TPId, $redisConKey);
    }

    /**
     * @param $strClass
     *
     * @return helper\OutPut
     */
    public function helper($strClass)
    {
        $strClass = ucfirst($strClass);
        $class = 'sdkService\helper\\' . $strClass;
        $objClass = new $class;
        return $objClass;
    }

    /**
     * 获取pdo实例对象
     *
     * @param string $dbConfigKey the key of 'db.php' index
     * @param string $tableName
     *
     * @return
     */
    protected function pdo($dbConfigKey = '', $tableName = '')
    {
        if (empty($dbConfigKey) || empty($tableName)) {
            return null;
        }
        $pdo = drivers\PDOManager::getInstance($dbConfigKey, $tableName);
        //将当前model，设置一个 pdohelper属性，这样model中每个方法，只需要$this->pdohelper即可操作pdo的orm对象
        $this->pdohelper = $pdo;
//var_dump($dbConfigKey, $tableName);die;
        return $pdo;
    }

    //子类继承此方法
    protected function getPdoHelper($dbConfig = null, $table = null)
    {
        $pdoDbConfig = !empty($dbConfig) ? $dbConfig : $this->dbConfig;
        $pdoTable = !empty($table) ? $table : $this->tableName;
        return $this->pdo($pdoDbConfig, $pdoTable);
    }

    /**
     * 计算分页用的,比如用redis的range()等函数
     * @param $totalNum 总数
     * @param $page 页数
     * @param $pageNum 每页显示条数
     * @return array['start'] 要查询的起始位置 （第一个元素为0）
     * @return array['end'] 要查询的结束位置
     */
    public function page($totalNum, $page, $pageNum)
    {
        $return = [
            'start' => 0,
            'end' => 0,
        ];
        $maxPage = ceil($totalNum / $pageNum);
        if ($page > $maxPage) {
            return false;
        }
        if ($page < 1) {
            return false;
        }
        $return['start'] = $pageNum * ($page - 1);
        $return['end'] = $return['start'] + $pageNum - 1;
        return $return;
    }

    /**
     * 将key的模板转换成真正的key
     * @param $input k=>v形式，k是$keyTempplate中的key  v是真正的值
     * @param $keyTemplate key的模板，通常定义在constant中
     * @return mixed
     */
    protected function swtichRedisKey($input, $keyTemplate)
    {
        $keys = array_keys($input);
        $keys = $keys[0];
        $search = '{#' . $keys . '}';
        return str_replace($search, $input[$keys], $keyTemplate);
    }

    /**
     * 格式化输入的数组，去掉不在列表中的字段,并且验证必传字段
     */
    protected function formatInputArray($inputArrData, $arrFields, $mustFields = [])
    {
        $arrData = [];
        $mustNum = 0;
        foreach ($inputArrData as $k => $v) {
            if (in_array($k, $arrFields)) {
                $arrData[$k] = $v;
            }
            if (in_array($k, $mustFields)) {
                $mustNum++;
            }
        }
        if ($mustNum != count($mustFields)) {
            throw new \Exception(__FUNCTION__ . ' must input ' . json_encode($mustFields));
        }
        return $arrData;
    }


    /**
     * 检测渠道Id，并获取配置了Redis的渠道
     * 比如说，传递渠道id为500，那么，如果redis配置文件中没有500这个渠道，那么返回一个默认的渠道
     *
     * @param int $iChannelId
     *
     * @return int
     */
    public static function getChannelIdChecked($iChannelId = '')
    {
        $arrConf = \wepiao::$config;
        if (empty($arrConf['params']['redis'][STATIC_MOVIE_DATA][$iChannelId])) {
            $iChannelId = DEFAULT_REDIS_CHANNEL_ID;
        }

        return $iChannelId;
    }

    /**
     * 判断订单号是否需要调用新版Crontask数据
     * @param string $iChannelId
     * @return bool
     */
    public function isRestructChannel($iChannelId = '')
    {
        return in_array($this->getChannelIdChecked($iChannelId), \wepiao::$config['params']['channelRestruct']);
    }


    //string类型被动缓存函数
    /**
     * @param $inpuKey      redis中的key
     * @param $keyTemplate  redis的key的模板
     * @param $dbConfigKey  pdo()函数中用到的$dbConfigKey
     * @param $tableName    pdo()函数中用到的$tableName
     * @param $cacheTime    缓存时间
     * @param $where        数据库查询时的条件
     * @param $whereParams  查询条件的params
     * @return mixed
     */
    protected function queryStringCache($inpuKey, $keyTemplate, $dbConfigKey, $tableName, $cacheTime, $where, $whereParams)
    {
        $redisKey = $this->swtichRedisKey($inpuKey, $keyTemplate);
        $redisRe = $this->redis->WYget($redisKey);
        if ($redisRe !== false) {
            $return = $redisRe;
        } else {
            $dbRe = $this->pdo($dbConfigKey, $tableName)->fetchOne($where, $whereParams);
            if ($dbRe) {
                $return = json_encode($dbRe);
                $this->redis->WYset($redisKey, $return);
            } else {
                $return = '';
                $this->redis->WYset($redisKey, '');
            }
        }
        $this->redis->WYexpire($redisKey, $cacheTime);
        return $return;
    }


    //hash类型被动缓存函数
    /**
     * @param $inpuKey      redis中的key
     * @param  $hashKey     hashKey
     * @param $keyTemplate  redis的key的模板
     * @param $dbConfigKey  pdo()函数中用到的$dbConfigKey
     * @param $tableName    pdo()函数中用到的$tableName
     * @param $cacheTime    缓存时间
     * @param $where        数据库查询时的条件
     * @param $whereParams  查询条件的params
     * @return mixed
     */
    protected function queryHashCache($inpuKey, $hashKey, $keyTemplate, $dbConfigKey, $tableName, $cacheTime, $where, $whereParams, $field = '*')
    {
        $redisKey = $this->swtichRedisKey($inpuKey, $keyTemplate);
        $redisRe = $this->redis->WYhGet($redisKey, $hashKey);
        if ($redisRe !== false) {
            $return = $redisRe;
        } else {
            $dbRe = $this->pdo($dbConfigKey, $tableName)->fetchOne($where, $whereParams, $field);
            if ($dbRe) {
                if ($field != '*') {
                    $return = $dbRe[$field];
                } else {
                    $return = json_encode($dbRe);
                }
                $this->redis->WYhSet($redisKey, $hashKey, $return);
            } else {
                $return = '';
                $this->redis->WYhSet($redisKey, $hashKey, '');
            }
        }
        $this->redis->WYexpire($redisKey, $cacheTime);
        return $return;
    }


    /**
     * @param $arrInputKey 大K数组
     * @param $hashKey 小K
     * @param $keyTemplate 大K模板
     * @param $value 值
     * @param int $expire 过期时间
     * @return mixed
     */
    protected function addHashCache($arrInputKey, $hashKey, $keyTemplate, $value, $expire = 86400)
    {
        $redisKey = $this->swtichRedisKey($arrInputKey, $keyTemplate);
        $return = $this->redis->WYhSet($redisKey, $hashKey, $value);
        if ($return) {
            $this->redis->WYexpire($redisKey, $expire);
        }
        return $return;
    }

    /**
     * @param $arrInputKey 大K数组
     * @param $hashKey 小K
     * @param $keyTemplate 大K模板
     * @param $value 值
     * @param int $expire 过期时间
     * @return mixed
     */
    protected function delHashCache($arrInputKey, $hashKey, $keyTemplate)
    {
        $redisKey = $this->swtichRedisKey($arrInputKey, $keyTemplate);
        $return = $this->redis->WYhDel($redisKey, $hashKey);
        return $return;
    }

    //有序集合通用被动缓存查询方法
    /*
        $arrRedisInfo = [
            'redisKey'=>要查找的redisKey,
            'redisKeyTemplate'=>要查找的redisKey的template,
            'redisCountKey'=>计算总数的redisKey,
            'redisCountKeyTemplate'=>计算总数的redisKey的template,
            'scoreField'=>查询出的哪个字段用于保存score,
            'valueField'=>查询出的哪个字段用于保存value,
            'start'=>查询的起始位置,
            'end'=>查询的结束位置,
            'expire'=>缓存的过期时间,
        ];
        $arrSqlInfo=[
            'table'=>要查询的数据表名,
            'where'=>查询的条件,
            'params'=>预处理,
            'orderBy'=>排序字段,
            'step'=> 步长值,
        ];
        $orderDesc true:使用zrevrange false:使用zrange
        $withScores true:返回值为[value1=>score1,value2=>score2,....] false:返回值为[value1,value2,....]
     */
    protected function queryZsetCache($arrRedisInfo, $arrSqlInfo, $orderDesc = true, $withScores = false)
    {
        if (empty($this->redis)) {
            throw new \Exception('redis is empty !!! ');
        }
        $return = false;
        if ($arrRedisInfo['start'] > $arrRedisInfo['end']) {
            return $return;
        }

        //确定排序函数
        $fun = $orderDesc ? 'WYzRevRange' : 'WYzRange';
        //集合key
        $zsetKey = $this->swtichRedisKey($arrRedisInfo['redisKey'], $arrRedisInfo['redisKeyTemplate']);
        //总数key
        $countKey = $this->swtichRedisKey($arrRedisInfo['redisCountKey'], $arrRedisInfo['redisCountKeyTemplate']);
        $countRedisExists = $this->redis->WYexists($countKey);
        //计算总数
        if (empty($countRedisExists)) {
            $countSql = 'select count(*) from ' . $arrSqlInfo['table'] . ' where ' . $arrSqlInfo['where'];
            $dbRe = $this->getPdoHelper()->fetchOneBySql($countSql, $arrSqlInfo['params']);
            $count = $dbRe['count(*)'] ? $dbRe['count(*)'] : 0;
            $setRe = $this->redis->WYset($countKey, $count);
        } else {
            $count = $this->redis->WYget($countKey);
        }
        $this->redis->WYexpire($countKey, $arrRedisInfo['expire']);
        //读key
        $redisExists = $this->redis->WYexists($zsetKey);
        $scoreField = isset($arrRedisInfo['selectScoreField']) ? $arrRedisInfo['selectScoreField'] : $arrRedisInfo['scoreField'];
        $valueField = isset($arrRedisInfo['selectValueField']) ? $arrRedisInfo['selectValueField'] : $arrRedisInfo['valueField'];
        if ($redisExists) {//如果有key
            $redisRe = $this->redis->$fun($zsetKey, 0, 0);//正序取第一个，判断是否是空
            if (isset($redisRe) && $redisRe[0] == '') {//如果是，返回false
                $return = false;
            } else {
                //取出缓存中的数目
                $keyNums = $this->redis->WYzCard($zsetKey);
                if ($keyNums > 0) {
                    if ($arrRedisInfo['start'] <= $keyNums && $arrRedisInfo['end'] <= $keyNums) {
                        //已经缓存的区间之内，只需要查询redis,不用查询db
                        $return = $this->redis->$fun($zsetKey, $arrRedisInfo['start'], $arrRedisInfo['end'], $withScores);
                    } elseif ($arrRedisInfo['start'] <= $keyNums && $arrRedisInfo['end'] > $keyNums) {
                        //1.根据步长值在db中生成数据
                        if ($keyNums < $count) {//如果缓存的key的数，小于db中的总数，那么按照步长查询一批数据塞进缓存
                            $limitStart = $keyNums - floor($arrSqlInfo['step'] / 10);//起始的查询值略小于key的总数，防止由于查询时并发插入遗漏一部分
                            $limitStart = $limitStart > 0 ? $limitStart : 0;
                            $sql = 'select ' . $scoreField . ',' . $valueField . ' from ' . $arrSqlInfo['table'] . ' where ' . $arrSqlInfo['where'] . ' order by ' . $arrSqlInfo['orderBy'] . ' limit ' . $limitStart . ',' . $arrSqlInfo['step'];
                            $dbRe = $this->getPdoHelper()->fetchArrayBySql($sql, $arrSqlInfo['params']);
                            if ($dbRe) {
                                foreach ($dbRe as $info) {
                                    $this->redis->WYzAdd($zsetKey, $info[$arrRedisInfo['scoreField']], $info[$arrRedisInfo['valueField']]);
                                }
                            }
                            if (count($dbRe) < $arrSqlInfo['step']) {
                                $newCount = $this->redis->WYzCard($zsetKey);
                                $this->redis->WYset($countKey, $newCount);
                                $this->redis->WYexpire($countKey, $arrRedisInfo['expire']);
                            }
                        }
                        //2.通过缓存拿到要返回的数据
                        $return = $this->redis->$fun($zsetKey, $arrRedisInfo['start'], $arrRedisInfo['end'], $withScores);
                    } else {//如果超出了缓存范围，有可能要使用db查询
                        if ($arrRedisInfo['start'] > $count) {
                            $return = false;
                        } elseif ($keyNums < $count) {//如果缓存的key的数，小于db中的总数，那么按照步长查询一批数据塞进缓存
                            $limitStart = $keyNums - floor($arrSqlInfo['step'] / 10);//起始的查询值略小于key的总数，防止由于插入太多在查询的时候遗漏一部分
                            $limitStart = $limitStart > 0 ? $limitStart : 0;
                            $sql = 'select ' . $scoreField . ',' . $valueField . ' from ' . $arrSqlInfo['table'] . ' where ' . $arrSqlInfo['where'] . ' order by ' . $arrSqlInfo['orderBy'] . ' limit ' . $limitStart . ',' . $arrSqlInfo['step'];
                            $dbRe = $this->getPdoHelper()->fetchArrayBySql($sql, $arrSqlInfo['params']);
                            if ($dbRe) {
                                foreach ($dbRe as $info) {
                                    $this->redis->WYzAdd($zsetKey, $info[$arrRedisInfo['scoreField']], $info[$arrRedisInfo['valueField']]);
                                }
                                $return = $this->redis->$fun($zsetKey, $arrRedisInfo['start'], $arrRedisInfo['end'], $withScores);
                            } else {
                                $return = false;
                            }
                            if (count($dbRe) < $arrSqlInfo['step']) {
                                $newCount = $this->redis->WYzCard($zsetKey);
                                $this->redis->WYset($countKey, $newCount);
                                $this->redis->WYexpire($countKey, $arrRedisInfo['expire']);
                            }
                        } else {
                            $return = false;
                        }
                    }
                } else {
                    $return = false;
                }
            }
        } else {//如果没有key
            $sql = 'select ' . $scoreField . ',' . $valueField . ' from ' . $arrSqlInfo['table'] . ' where ' . $arrSqlInfo['where'] . ' order by ' . $arrSqlInfo['orderBy'] . ' limit 0 ,' . $arrSqlInfo['step'];
            $dbRe = $this->getPdoHelper()->fetchArrayBySql($sql, $arrSqlInfo['params']);
            if ($dbRe) {//如果有数据，存入缓存，并设置超时时间
                $i = 0;
                foreach ($dbRe as $info) {
                    $this->redis->WYzAdd($zsetKey, $info[$arrRedisInfo['scoreField']], $info[$arrRedisInfo['valueField']]);
                    //todo
                    if ($i >= $arrRedisInfo['start'] && $i <= $arrRedisInfo['end']) {
                        $return[] = $info[$arrRedisInfo['valueField']];
                    }
                    $i++;
                }
                $return = array_unique($return);
            } else {//没有数据缓存空值
                $this->redis->WYzAdd($zsetKey, 0, '');
            }
        }
        $this->redis->WYexpire($zsetKey, $arrRedisInfo['expire']);
        return $return;
    }

    //有序集合通用插入方法
    protected function addZsetCache($arrKey, $keyTemplate, $countKey, $countKeyTemplate, $score, $value, $expire = 86400)
    {
        $key = $this->swtichRedisKey($arrKey, $keyTemplate);
        $countKey = $this->swtichRedisKey($countKey, $countKeyTemplate);
        $redisRe = $this->redis->WYzRange($key, 0, 0);
        if (!empty($redisRe) && $redisRe[0] == '') {
            $this->redis->WYdelete($key);
        }
        $addRe = $this->redis->WYzAdd($key, $score, $value);
        if ($expire > 0) {
            $this->redis->WYexpire($key, $expire);
        }
        if ($addRe) {
            if ($countKey) {
                $this->redis->WYincr($countKey);//计数的key +1
                if ($expire > 0) {
                    $this->redis->WYexpire($countKey, $expire);
                }
            }
        }
        return $addRe;
    }

    //向存在的有序集合通用插入方法--仅当有序集合存在的时候插入
    protected function addExistZsetCache($arrKey, $keyTemplate, $countKey, $countKeyTemplate, $score, $value, $expire = 86400)
    {
        $key = $this->swtichRedisKey($arrKey, $keyTemplate);
        $countKey = $this->swtichRedisKey($countKey, $countKeyTemplate);
        $exists = $this->redis->WYexists($key);
        if ($exists) {
            $redisRe = $this->redis->WYzRange($key, 0, 0);
            if (!empty($redisRe) && $redisRe[0] == '') {
                $this->redis->WYdelete($key);
            }
            $addRe = $this->redis->WYzAdd($key, $score, $value);
            if ($expire > 0) {
                $this->redis->WYexpire($key, $expire);
            }
            if ($addRe) {
                if ($countKey) {
                    $this->redis->WYincr($countKey);//计数的key +1
                    if ($expire > 0) {
                        $this->redis->WYexpire($countKey, $expire);
                    }
                }
            }
            $return = $addRe;
        } else {
            $return = false;
        }
        return $return;
    }

    //有序集合通用删除方法
    protected function delZsetCache($arrKey, $keyTemplate, $countKey, $countKeyTemplate, $value)
    {
        $key = $this->swtichRedisKey($arrKey, $keyTemplate);
        $countKey = $this->swtichRedisKey($countKey, $countKeyTemplate);
        $remRe = $this->redis->WYzRem($key, $value);
        if ($remRe) {
            if ($countKey) {
                $nums = $this->redis->WYget($countKey);
                if ($nums > 0) {
                    $this->redis->WYdecr($countKey);//计数的key -1
                }
            }
        }
        return $remRe;
    }


    //查询有序列表的count总数，通用被动缓存
    protected function queryZsetCacheCount($arrInputKey, $keyTemplate, $table, $where, $params, $expire = 86400)
    {
        //总数key
        $countKey = $this->swtichRedisKey($arrInputKey, $keyTemplate);
        $countRedisExists = $this->redis->WYexists($countKey);
        //计算总数
        if (empty($countRedisExists)) {
            $countSql = 'select count(*) from ' . $table . ' where ' . $where;
            $dbRe = $this->getPdoHelper()->fetchOneBySql($countSql, $params);
            $count = $dbRe['count(*)'] ? $dbRe['count(*)'] : 0;
            $setRe = $this->redis->WYset($countKey, $count);
        } else {
            $count = $this->redis->WYget($countKey);
        }
        $this->redis->WYexpire($countKey, $expire);
        return $count;
    }
    
    
    /**
     * 判断是第三方渠道
     * 目前，影片详情和预告片的数据，微信+手Q+APP+小程序，是共用的，渠道的影片详情和预告片也是共用的，只不过是存储在另一份共用redis中，所以
     * 此方法就是用来决定，影片和预告片存在哪个公共池的
     *
     * @param string $channelId
     *
     * @return bool
     */
    public function checkThirdChannel($channelId = '')
    {
        return in_array($channelId, [11, 6, 45, 30, 18, 2, 27, 14, 60, 47]) ? true : false;
    }

}