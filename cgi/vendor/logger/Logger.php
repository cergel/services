<?php
namespace vendor\logger;
/**
 * Created by PhpStorm.
 * User: dongzj
 * Date: 16/3/7
 * Time: 15:50
 */
class Logger
{

    private static $logger = null; //实例
    private $content = []; //日志内容
    private $logFile = null;    //日志文件
    private $shutdownFuncMark = 0;    //是否已经注册了shutdown function 来刷日志信息到文件

    public function __construct()
    {
    }

    /**
     * 获取实例，单实例
     * @return Logger
     */
    public static function getInstance($logId='')
    {
        if (static::$logger[$logId] === null) {
            static::$logger[$logId] = new self();
        }
        return static::$logger[$logId];
    }

    /**
     * @param $logPath
     */
    public function setLogPath($logPath)
    {
        $this->logFile = $logPath;
        $logPath = dirname($logPath);
        if (!is_dir($logPath)) {
            mkdir($logPath, 0777, true);
        }
        //判断是否已经注册了flushLog的shutdown function
        if (empty( $this->shutdownFuncMark )) {
            register_shutdown_function([$this, 'flushLog']);
            $this->shutdownFuncMark = 1;
        }
    }

    /**
     * 获取logId
     * @return mixed
     * @throws Exception
     */
    public function getLogId()
    {
        if (isset($this->content['logId'])) {
            return $this->content['logId'];
        } else {
            return '';
        }
    }

    /**
     * 写入日志记录
     * @param $toDo
     * @param $data string|array
     */
    public function addNode($toDo, $data)
    {
        if ($toDo == 'requestOther') {
            $this->content['requestOther'][] = $data;
        } else {
            $this->content[$toDo] = $data;
        }
    }

    /**
     * 一次性写入多个信息，初始化时使用
     * @param $data
     */
    public function initLogWatch($data)
    {
        //刷新service日志组件的信息，将之前的记录刷到file中,相当于每次调用initLogWatch的时候,会将之前的日志信息刷到日志中。
        if ( !empty( $this->content ) && !empty( $this->logFile )) {
            $this->flushLog();
        }
        //重新初始化
        $this->content['serverIP'] = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : gethostname();
        $this->content['beginTime'] = microtime(true);
        $this->content['requestTime'] = date("Y-m-d H:i:s", $this->content['beginTime']);
        $this->content = array_merge($this->content, $data);
    }

    public function resetLogInfo()
    {
        $this->content = [];
        $this->logFile = null;
    }

    /**
     * 记录第三方日志
     * @param $data
     */
    public function addRequestOtherNode($data)
    {
        $this->content['requestOther'][] = $data;
    }

    public function errorLog($data)
    {
        $this->content['error'] = $data;
    }

    public function flushLog()
    {
        if (empty($this->content['response'])) {
            $this->content['errorLog'] = error_get_last();
        }
        if (!isset($this->content['logId'])) {
            $this->content['logId'] = md5('test' . microtime(true));
        }
        if (empty($this->logFile)) {
            throw new \Exception("log path not set", 404);
        }
        $this->content['endTime'] = microtime(true);
        $this->content['totalTime'] = floor((microtime(true) - $this->content['beginTime']) * 10000) / 10000;
        $content = json_encode($this->content) . PHP_EOL;
        error_log($content, 3, $this->logFile);
        //重置日志信息
        $this->resetLogInfo();
    }
}