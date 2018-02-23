<?php

namespace redisTools\route\defaultRouter;

/**
 * 一致性哈希分布式算法
 * 由于crc32函数，在64位操作系统中，为无符号整型，在32位系统中可能为有符号的，所以建议使用在64位系统中
 * 实现原理：①：模拟哈希环，②：在哈希环上，根据数组配置，落上很多虚拟节点，③：将key转换为整型，寻找合适的落点
 */
class hashDes
{
    public $config = [];
    static protected $instance = [];

    //单个主机虚拟节点数,解决节点分布不均的问题
    private $_replicas = 128;
    //所有节点，包含虚拟节点
    private $_nodes = [];
    //所有节点的key值(CRC后得到的的数值)
    private $_nodeKeys = [];
    //是否缓存一致性哈希的节点，开关：1表示开启缓存一致性哈希节点，0表示关闭，默认开启
    private $cacheNodesSwitch = 1;
    //缓存目录
    private $cacheDir = '/tmp/';
    //缓存文件前缀
    private $cacheFilePrefix = 'service_hash_des_v3_';
    private $isYac=false;
    private $yacLifeTime=300;


    /**
     * 实例化方法
     *
     * @param array $arrConfig
     *
     * @return mixed
     * @throws Exception
     */
    public static function instance(array $arrConfig = [])
    {
        //config配置合法性判断，配置必须为二维数组
        if ( !empty( $arrConfig )) {
            $res = static::_getConfigUniqueKey($arrConfig);
            $srtMKey = $res['key'];
            $arrConfig = $res['config'];
            //如果实例已存在，直接返回
            if ( !isset( static::$instance[$srtMKey] )) {
                $instance = new static;
                $instance->config = $arrConfig;
                $instance->DistributeNode();
                static::$instance[$srtMKey] = $instance;
            }
            return static::$instance[$srtMKey];
        }
        else {
            throw new \Exception("config error", 1);
        }
    }

    /**
     * 私有化构造方法，构成真正的单利模式
     */
    private function __construct()
    {
        if ( !isset( $this->cacheDir )) {
            $this->cacheDir = '/tmp/';
        }
        if (extension_loaded("yac")) {
            $this->isYac=true;
        }
    }

    /**
     * 根据配置，获取当前配置对应的md5 key，该方法，主要是为了支持统一脚本中，不同配置，多个instance的场景
     * 将['host'=>'192.168.200.85','port'=>6379]转换为['key'=>'dsajgpjadjsfdjdsljdls23j23j23j2','config'=>['192.168.200.85_6379']]这种形式
     *
     * @param array $arrConfig
     *
     * @return string
     * @throws Exception
     * @access private
     */
    private static function _getConfigUniqueKey(array $arrConfig = [])
    {
        $return = ['key' => '', 'config' => []];
        if ( !empty( $arrConfig )) {
            $strKey = '';
            //遍历数组，拼接子数组的key和val，组成一个字符串，用于hash
            $newArrConfig = [];
            foreach ($arrConfig as $key => $config) {
                if (is_array($config) && !empty( $config )) {
                    foreach ($config['write'] as $subConfigKey => $subConfigVal) {
                        $strKey .= $subConfigKey . '_' . $subConfigVal;
                    }
                }
                else {
                    throw new \Exception("sub config error", 1);
                }
                $strNewKey = $config['write']['host'] . '_' . $config['write']['port'];
                $newArrConfig[$strNewKey] = $config;
            }
        }
        else {
            throw new \Exception("config error", 1);
        }
        $strMKey = md5($strKey);
        $return['key'] = $strMKey;
        $return['config'] = $newArrConfig;
        return $return;
    }

    /**
     * 根据已有配置，做节点分布
     * 最终分布的结果是，所有节点在一个数组中，key为节点对应的整型，val为具体配置的key，如：key为1094239918，值为：192.168.200.85_6379
     */
    public function DistributeNode()
    {
        $res = $this->createNodesByCache($this->config);
        if ( !$res) {
            $firstStrConfig = '';
            if ( !empty( $this->config )) {
                foreach ($this->config as $arrConfig) {
                    $host = $arrConfig['write']['host'];     //IP
                    $port = $arrConfig['write']['port'];     //端口
                    //设置第一组配置
                    if (empty( $firstStrConfig )) {
                        $firstStrConfig = $host . '_' . $port;
                    }
                    //权重，如果未设置，则使用默认单台虚拟节点数量
                    $weight = isset( $arrConfig['write']['weight'] ) ? $arrConfig['write']['weight'] : $this->_replicas;
                    //根据权重，生成节点，生成方式为：将节点字符串，转换为无符号整型
                    for ($i = 0; $i < $weight; $i++) {
                        $crcKey = $host . '_' . $port . '_' . $i;
                        $intKey = static::_getIntvalKey($crcKey);
                        //$intKey = strval($intKey);
                        //将节点对应的redis主机配置，添加到总节点信息中，值为主机IP和端口链接的字符串
                        $this->_nodes[$intKey] = $host . '_' . $port;
                    }
                }
            }
            else {
                throw new \Exception("config empty", 1);
            }
            //判断0节点，有没有占位，没有的话，用第一个占位
            if ( !isset( $this->_nodes[0] )) {
                $this->_nodes[0] = $firstStrConfig;
            }
            //按照Key的大小排序
            krsort($this->_nodes);
            $this->_nodeKeys = array_keys($this->_nodes);
            //print_r($this->_nodes);
            //print_r($this->_nodeKeys);
            $this->cacheNodes($this->config, $this->_nodes, $this->_nodeKeys);
        }
    }

    /**
     * 根据key，在众节点中，寻找一个合适的节点，并获取到该节点对应的配置
     * 获取节点的方式为：将所有节点的key，降序排序，然后循环，用key的整型值intKey比较，当获取到比intKey小的值（节点的key）的时候，将这个节点选取
     *
     * @param string $strKey  存储数据到redis时，需要的key
     * @param string $getType 获取方式，pro表示生产环节，仅仅获取config，debug表示获取调试所有内容
     *
     * @return string|array
     */
    public function lookupConfig($strKey = '', $getType = 'pro')
    {
        $return = ['intKey' => 0, 'strKey' => $strKey, 'selectIntKey' => 0, 'config' => []];
        $intKey = static::_getIntvalKey($strKey);
        //从节点中选择一个，处理方式为：选择比intKey小的值作为基点（小有下限，大无上限）
        $nodesKey = $this->_nodeKeys;
        $selectIntKey = 0;
        $count = count($nodesKey);
        //从node节点中，选择比intKey小的node节点
        for ($i = 0; $i < $count; $i++) {
            if ($intKey > $nodesKey[$i]) {
                $selectIntKey = $nodesKey[$i];
                break;
            }
        }
        $strConfig = $this->_nodes[$selectIntKey];
        $arrConfig = $this->config[$strConfig];
        //拼装返回结果
        $return['intKey'] = $intKey;
        $return['selectIntKey'] = $selectIntKey;
        $return['config'] = $arrConfig;
        //返回内容，如果定制了返回内容，则按定制内容返回，否则返回整个数组
        if ($getType === 'debug') {
            return $return;
        }
        return $return['config'];
    }

    /**
     * 将字符串key，转换为对应的无符号整型
     *
     * @param string $strKey
     *
     * @return int
     */
    protected static function _getIntvalKey($strKey = '')
    {
        $intKey = crc32($strKey);
        //如果IntKey为负值，转换为正数
        if ($intKey < 0) {
            //$intKey =4294967295 + $intKey + 1;
            $intKey = 0 - $intKey;
        }
        return $intKey;
    }

    /**
     * 获取所有的节点
     *
     * @return array
     */
    public function getAllNodes()
    {
        return $this->_nodes;
    }

    /**
     * 添加节点
     * 该方法主要用于动态测试添加节点，对于PHP这种一次性生命周期的脚本来说，生产环节并不需要
     *
     * @param array $config
     */
    public function addNode(array $config = [])
    {

    }

    /**
     * 删除节点
     * 该方法主要用于动态测试删除节点，对于PHP这种一次性生命周期的脚本来说，生产环节并不需要
     */
    public function delNode()
    {

    }

    /**
     * 缓存一致性哈希节点
     *
     * @param array $config    当前cluster的配置
     * @param array $nodes     所有的排序好的节点（+虚拟几点）
     * @param array $nodesKeys 所有节点的key，此值不一定需要
     *
     * @throws \Exception
     */
    public function cacheNodes(array $config = [], array $nodes = [], array $nodesKeys = [])
    {
        //缓存开关开启、且配置有效
        if ($this->cacheNodesSwitch && !empty( $config ) && !empty( $nodes )) {
            $arr = ['nodeKeys' => $nodesKeys, 'nodes' => $nodes];
            if($this->isYac){
                $yac = new \Yac();
                $key=md5(serialize($config));
                $yac->set($key,$arr,$this->yacLifeTime);
            }
            else{
                $str = json_encode($arr);
                if (!file_exists($this->cacheDir)) {
                    @mkdir($this->cacheDir, 0755);
                }
                $file = $this->cacheDir . $this->cacheFilePrefix . md5(serialize($config));
                self::writeCacheFile($file, $str);
            }
        }
    }

    /**
     * 写缓存文件，独占式，非阻塞
     * @param $filename
     * @param $data
     */
    private function writeCacheFile($filename, $data)
    {
        $fp = fopen($filename, 'w');
        //尝试获取独占锁，非阻塞。获取不到则什么都不做。（Windows上只能阻塞）
        if (flock($fp, LOCK_EX | LOCK_NB)) {
            fwrite($fp, $data);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }

    /**
     * 根据缓存的文件，还原节点
     * 读取缓存的一致性哈希结果
     * @param array $config 当前cluster的配置
     *
     * @return bool
     */
    public function createNodesByCache(array $config = [])
    {
        $return = false;
        //判断是否开启一致性哈希缓存节点的开关及配置是否有效
        if ( !$this->cacheNodesSwitch || empty( $config )) {
            return $return;
        }
        if($this->isYac){
            $yac = new \Yac();
            $key=md5(serialize($config));
            $content=$yac->get($key);
            if(is_array($content)){
                $this->_nodes = $content['nodes'];
                $this->_nodeKeys = $content['nodeKeys'];
                $return = true;
            }
        }
        else{
            //缓存文件名
            $strFileName = $this->cacheFilePrefix . md5(serialize($config));
            //缓存文件路径
            $file = $this->cacheDir . $strFileName;
            //如果缓存文件存在，则通过缓存文件内容，还原一致性哈希节点
            if (is_file($file)) {
                $content = file_get_contents($file);
                if(!empty($content)){
                    $content=json_decode($content,true);
                    if ($content && is_array($content)) {
                        $this->_nodes = $content['nodes'];
                        $this->_nodeKeys = $content['nodeKeys'];
                        $return = true;
                    }
                }
            }
        }
        return $return;
    }

    /**
     * 根据文件路径，创建此文件
     *
     * @param string $file
     *
     * @throws \Exception
     */
    protected function makeFile($file = '')
    {
        try {
            if ( !empty( $file ) && !is_file($file)) {
                //create dir and file
                if ( !is_dir($logDir = dirname($file))) {
                    mkdir($logDir, 777, true);
                    touch($file);
                }
                if ( !is_file($file)) {
                    touch($file);
                }
            }
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 设置缓存目录
     *
     * @param string $path
     *
     * @return $this
     */
    public function setCacheDir($path = '')
    {
        if ( !empty( $path ) && is_string($path)) {
            $this->cacheDir = $path;
        }

        return $this;
    }

    /**
     * 设置节点缓存文件的前缀
     *
     * @param string $prefix
     *
     * @return $this
     */
    public function setCacheFilePrifix($prefix = '')
    {
        if ( !empty( $prefix ) && is_string($prefix)) {
            $this->cacheFilePrefix = $prefix;
        }

        return $this;
    }

    /**
     * 设置节点缓存的开关
     *
     * @param int $status 1表示开启，0表示关闭
     *
     * @return $this
     */
    public function setCacheSwitch($status = 1)
    {
        if (in_array($status, [0, 1])) {
            $this->cacheNodesSwitch = $status;
        }

        return $this;
    }

}


?>