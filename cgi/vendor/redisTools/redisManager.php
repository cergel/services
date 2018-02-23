<?php
namespace redisTools;

use redisTools\route;
use vendor\logger\Logger;

//定义自己的异常类
class redisManagerException extends \Exception{

}

/**
 * 核心类，所有要使用redisManager的地方都要使用getInstarce方法来获取实例
 * Class redisManager
 * @package redisManager
 */
class redisManager
{
    private static $selfObj = [];
    private $router;//router对象
    /**
     * 此函数本身应该是单例的，对象也可以调用静态方法
     * @param array $arrConfig
     * @return \redisManager\redisManager
     */
    public static function getInstance($arrConfig = array())
    {
        if(empty($arrConfig)){
            throw new redisManagerException("redis config is empty");
        }
        $objKey = md5(json_encode($arrConfig));
        $type = $arrConfig['type'];
        $routerName = $type.'Router';
        $className = "\\redisTools\\route\\$routerName\\$routerName";
        if (empty(self::$selfObj[$objKey])) {
            self::$selfObj[$objKey] = new self($arrConfig);
            self::$selfObj[$objKey]->router=$className::getInstance($arrConfig);
        }
        return self::$selfObj[$objKey];
    }

    /**
     * 获得router的方法，通过此方法拿到的router可以执行一些router的操作
     * @return mixed
     */
    public function getRouter(){
        return $this->router;
    }

    /**
     * 功能：执行操作redis的方法，去加载redisOperator类
     * @param $func
     * @param $params
     * @return null
     * @throws \Exception
     * @throws redisManagerException
     */
    public function __call($func, $params)
    {
        $redisConf = $this->router->getFinalConf($func, $params);
        $redisOperator = redisOperator::getInstance($redisConf);
        //调用redis方法，如果调用的过程中发现Redis异常，则认为是redis连接丢失，尝试重新连接
        //因为service的调用方可能是常驻进程，对于单例来说，无法确认单例中的连接是否已经lost connection，所以需要重新发起连接尝试
        try{
            $return = $this->runFun($redisOperator, $func, $params);
        }
        catch (\Exception $e1) {
            //catch里边还有try catch，是因为上面的try是为了取数据，异常之后，认为连接丢失，这个时候重新尝试连接，但是假如redis真的挂了，下面的重连还是会报异常
            //因此，需要再次捕获这个异常，不能因为未捕获异常，造成业务方500错误
            try {
                $redisOperator->redisConnect($redisConf);
                //windows下的redis扩展，当远程redis服务器主动关闭连接是会报一个notice，所以加上@来抑制错误
                $return = @$this->runFun($redisOperator, $func, $params);
            }
            catch (\Exception $e2) {
                $logInfo = [
                    'errorCode' => $e2->getCode(),
                    'errMsg'    => $e2->getMessage(),
                    'config'    => $redisConf,
                ];
                Logger::getInstance()->addNode('redisConnectError', $logInfo);
                $return = null;
                throw new redisManagerException("Redis server can not connect!");
            }
        }

        return $return;
    }

    //执行函数
    protected function runFun($redisOperator,$func, $params){
        $paramsNums = count($params);
        switch ($paramsNums) {
            case 1:
                $return = $redisOperator->$func($params[0]);
                break;
            case 2:
                $return = $redisOperator->$func($params[0], $params[1]);
                break;
            case 3:
                $return = $redisOperator->$func($params[0], $params[1], $params[2]);
                break;
            case 4:
                $return = $redisOperator->$func($params[0], $params[1], $params[2], $params[3]);
                break;
            case 5:
                $return = $redisOperator->$func($params[0], $params[1], $params[2], $params[3], $params[4]);
                break;
            default :
                throw new redisManagerException('redis client params error');
        }
        return $return;
    }

    //关闭全部连接
    public function closeAllConnect(){
        $arrConf=$this->router->getRedisConfToArray();
        //遍历这个配置数组，逐个进行关闭
        foreach($arrConf as $v){
            redisOperator::unsetInstance($v);
        }
    }

}