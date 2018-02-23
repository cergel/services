<?php
/**
 * Created by PhpStorm.
 * User: dongzj
 * Date: 16/3/8
 * Time: 16:42
 */
class Logger
{

    private static $logger = null; //实例
    private $content = []; //日志内容
    private $logFile = null;

    public function __construct()
    {
        $this->content['serverIP'] = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';
        $this->content['beginTime'] = microtime(true);
        $this->content['requestTime'] = date("Y-m-d H:i:s", $this->content['beginTime']);
    }

    /**
     * 获取实例，单实例
     * @return Logger
     */
    public static function getInstance()
    {
        if (static::$logger === null) {
            static::$logger = new self();
        }
        return static::$logger;
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
        register_shutdown_function(array($this, 'flushLog'));
    }

    /**
     * 获取logId
     * @return mixed
     * @throws Exception
     */
    public function getLogId()
    {
        if(isset($this->content['logId']))
        {
            return $this->content['logId'];
        }else{
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
        if($toDo == 'requestOther'){
            $this->content['requestOther'][] = $data;
        }else{
            $this->content[$toDo] = $data;
        }
    }

    /**
     * 一次性写入多个信息，初始化时使用
     * @param $data
     */
    public function initLogWatch($data)
    {
        $this->content = $this->content + $data;
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
        $this->content['errorLog'] = error_get_last();
        if(!isset($this->content['logId']))
        {
            $this->content['logId'] = md5('test' . microtime(true));
        }
        if(empty($this->logFile))
        {
            throw new Exception("log path not set");
        }
        $this->content['endTime'] = microtime(true);
        $this->content['totalTime'] = floor((microtime(true) - $this->content['beginTime']) *10000)/10000;
        $content = json_encode($this->content) . PHP_EOL;
        error_log($content, 3, $this->logFile);
    }
}