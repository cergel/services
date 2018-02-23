<?php
/**
 * Memcache 操作类
 * 需要注意的是，这个类不会造成处理业务失败，牵连到其他业务（比如添加数据的操作，连不上Memcached等）
 * demo:
 * $cacheObj = new Data_Mc_Memcache();
 * $cacheObj -> set('keyName','this is value');
 * $cacheObj -> get('keyName');
 * exit;
 */

namespace sdkService\helper;

class Memcache
{

    private $local_cache = array();
    private $m;
    private $client_type;
    protected $errors = array();
    protected $addedServers = array();

    //优先使用Memcache拓展
    public function __construct()
    {
        $this->client_type = class_exists('Memcache') ? "Memcache" : (class_exists('Memcached') ? "Memcached" : FALSE);

        if ($this->client_type) {
            // 判断引入类型
            switch ($this->client_type) {
                case 'Memcached':
                    $this->m = new \Memcached();
                    //设置Memcached的分布式算法策略为一致性哈希分布式算法
                    $this->m->setOption(\Memcached::OPT_DISTRIBUTION, \Memcached::DISTRIBUTION_CONSISTENT);
                    break;
                case 'Memcache':
                    $this->m = new \memcache();
                    // if (auto_compress_tresh){
                    // $this->setcompressthreshold(auto_compress_tresh, auto_compress_savings);
                    // }
                    break;
            }
        } else {
            echo 'ERROR: Failed to load Memcached or Memcache Class (∩_∩)';
            exit;
        }
    }

    /**
     * 添加server。注意：Memcache这个拓展的分布式算法，只能通过设置php.ini，具体设置内容，参考PHP手册
     * @param $server
     * @return bool
     */
    public function add_server($server)
    {
        $host = '';
        $port = '';
        $weight = '';
        extract($server, EXTR_OVERWRITE);
        return $this->m->addServer($host, intval($port), intval($weight));
    }

    /**
     * 批量添加server，强烈建议添加server，用add_servers的方式，哪怕仅仅只有一台，这样后期拓展非常方便
     * @param $servers
     * @return bool
     */
    public function add_servers($servers)
    {
        //Memcache拓展无法通过代码设置分布式算法，智能通过php.ini，而且默认是取余算法，对于取余算法，权重是无效的
        $addStatus = false;
        if ($this->client_type == 'Memcache') {
            foreach ($servers as $key => $server) {
                $host = '';
                $port = '';
                $weight = '';
                extract($server, EXTR_OVERWRITE);
                //判断这个server是否已经添加过了，这里的判断依据是host和port的组合，无关weight权重
                //注意：如果不做是否添加过这个处理，那么做单例模式的时候，不同的方法可以再次添加server，造成了假的memcached集群
                //这样本来取一台或2台机器的情况，就成了哈希一致性分布式算法，取集群。
                $strHostKey = $host . '_' . $port;
                if (in_array($strHostKey, $this->addedServers)) {
                    continue;
                } else {
                    $this->addedServers[] = $strHostKey;
                }
                $addStatus = $this->m->addServer($host, $port, $weight);
            }
        } //Memcached的分布式算法，默认也是取余算法，但是可以修改，而且，分布式的话，最好使用addServers来操作。
        elseif ($this->client_type == 'Memcached') {
            $addStatus = $this->m->addServers($servers);
        }
        return $addStatus;
        //return $this->m->addServers($servers);
    }

    /**
     * 添加key和对应的值。默认过期时间为30天
     * 对于Memcache拓展，添加值，不启用压缩，也最好不要压缩，因为影响increment，同理set操作
     * @param null $key
     * @param null $value
     * @param int $expiration
     * @return array|bool
     */
    public function add($key = NULL, $value = NULL, $expiration = 2592000)
    {
        $add_status = false;
        if (is_array($key)) {
            foreach ($key as $multi) {
                if (!isset($multi['expiration']) || $multi['expiration'] == '') {
                    $multi['expiration'] = $expiration;
                }
                //对应循环添加数据，add_status没有意义
                $add_status = $this->add($this->key_name($multi['key']), $multi['value'], $multi['expiration']);
            }
        } else {
            //$this->local_cache[$this->key_name($key)] = $value;
            switch ($this->client_type) {
                case 'Memcache':
                    $add_status = $this->m->add($this->key_name($key), $value, false, $expiration);
                    break;

                default:
                case 'Memcached':
                    $add_status = $this->m->add($this->key_name($key), $value, $expiration);
                    break;
            }

        }
        return $add_status;
    }

    /**
     * 设置key的自增
     * 对于Memcache拓展，如果设置了压缩，则使用increment后，会造成无法get到你希望得到的值，切记切记。
     **/
    public function increment($key = NULL, $offset = 1)
    {
        $incre_status = false;
        if (is_null($key)) return false;
        if (is_array($key)) {
            foreach ($key as $multi) {
                $incre_status = $this->add($this->key_name($multi['key']), $multi['offset']);
            }
        } else {
            $incre_status = $this->m->increment($this->key_name($key), $offset);
        }
        return $incre_status;
    }

    /**
     * 与add类似,但服务器有此键值时仍可写入替换
     **/
    public function set($key = NULL, $value = NULL, $expiration = 2592000)
    {
        if (is_array($key)) {
            foreach ($key as $multi) {
                $this->set($this->key_name($multi['key']), $multi['value'], $multi['expiration']);
            }
        } else {
            //$this->local_cache[$this->key_name($key)] = $value;
            switch ($this->client_type) {
                case 'Memcache':
                    $add_status = $this->m->set($this->key_name($key), $value, false, $expiration);
                    break;
                case 'Memcached':
                    $add_status = $this->m->set($this->key_name($key), $value, $expiration);
                    break;
            }
            return $add_status;
        }
    }

    /**
     * get 根据键名获取值
     **/
    public function get($key = NULL)
    {
        if ($this->m) {
            /*if(isset($this->local_cache[$this->key_name($key)]))
            {
                return $this->local_cache[$this->key_name($key)];
            }*/
            if (is_null($key)) {
                $this->errors[] = 'The key value cannot be NULL';
                return FALSE;
            }

            if (is_array($key)) {
                foreach ($key as $n => $k) {
                    $key[$n] = $this->key_name($k);
                }
                return $this->m->getMulti($key);
            } else {
                return $this->m->get($this->key_name($key));
            }
        } else {
            return FALSE;
        }
    }

    /**
     * @Name   delete
     * @param  $key key
     * @param  $expiration 服务端等待删除该元素的总时间
     * @return true OR false
     **/
    public function delete($key, $expiration = 0)
    {
        if (is_null($key)) {
            $this->errors[] = 'The key value cannot be NULL';
            return FALSE;
        }

        if (is_array($key)) {
            foreach ($key as $multi) {
                $this->delete($multi, $expiration);
            }
        } else {
            //unset($this->local_cache[$this->key_name($key)]);
            return $this->m->delete($this->key_name($key), $expiration);
        }
    }

    /**
     * @Name   replace
     * @param  $key 要替换的key
     * @param  $value 要替换的value
     * @param  $expiration 到期时间
     * @return none
     **/
    public function replace($key = NULL, $value = NULL, $expiration = 2592000)
    {
        if (is_array($key)) {
            foreach ($key as $multi) {
                /*if(!isset($multi['expiration']) || $multi['expiration'] == ''){
                    $multi['expiration'] = $this->config['config']['expiration'];
                }*/
                $replace_status = $this->replace($multi['key'], $multi['value'], $multi['expiration']);
            }
        } else {
            //$this->local_cache[$this->key_name($key)] = $value;

            switch ($this->client_type) {
                case 'Memcache':
                    $replace_status = $this->m->replace($this->key_name($key), $value, false, $expiration);
                    break;
                case 'Memcached':
                    $replace_status = $this->m->replace($this->key_name($key), $value, $expiration);
                    break;
            }

        }
        return $replace_status;
    }

    /**
     * @Name   replace 清空所有缓存
     * @return none
     **/
    public function flush()
    {
        return $this->m->flush();
    }

    /**
     * @Name   获取服务器池中所有服务器的版本信息
     **/
    public function getversion()
    {
        return $this->m->getVersion();
    }


    /**
     * @Name   获取服务器池的统计信息
     **/
    public function getstats($type = "items")
    {
        switch ($this->client_type) {
            case 'Memcache':
                $stats = $this->m->getStats($type);
                break;

            default:
            case 'Memcached':
                $stats = $this->m->getStats();
                break;
        }
        return $stats;
    }

    /**
     * @Name: 开启大值自动压缩
     * @param:$tresh 控制多大值进行自动压缩的阈值。
     * @param:$savings 指定经过压缩实际存储的值的压缩率，值必须在0和1之间。默认值0.2表示20%压缩率。
     * @return : true OR false
     * add by cheng.yafei
     **/
    public function setcompressthreshold($tresh, $savings = 0.2)
    {
        switch ($this->client_type) {
            case 'Memcache':
                $setcompressthreshold_status = $this->m->setCompressThreshold($tresh, $savings = 0.2);
                break;

            default:
                $setcompressthreshold_status = TRUE;
                break;
        }
        return $setcompressthreshold_status;
    }

    /**
     * @Name: 生成md5加密后的唯一键值
     * @param:$key key
     * @return : md5 string
     * add by cheng.yafei
     **/
    private function key_name($key, $type = 'orginal')
    {
        if ($type == 'orginal') {
            return $key;
        }
    }

    /**
     * @Name: 向已存在元素后追加数据
     * @param:$key key
     * @param:$value value
     * @return : true OR false
     **/
    public function append($key = NULL, $value = NULL)
    {


//      if(is_array($key))
//      {
//          foreach($key as $multi)
//          {
//
//              $this->append($multi['key'], $multi['value']);
//          }
//      }
//      else
//      {

        switch ($this->client_type) {
            case 'Memcache':
                $append_status = $this->m->append($this->key_name($key), $value);
                break;

            default:
            case 'Memcached':
                $append_status = $this->m->append($this->key_name($key), $value);
                break;
        }

        return $append_status;
//      }
    }

    /**
     * 关闭连接
     */
    public function close()
    {
        switch ($this->client_type) {
            case 'Memcache':
                $this->m->close();
                break;
            default:
            case 'Memcached':
                $this->m->quit();
                break;
        }
    }

}