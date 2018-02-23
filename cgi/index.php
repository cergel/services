<?php
use vendor\logger\Logger;
define('CGI_APP_PATH', dirname(__FILE__) . '/');

class autoLoad
{
    public function loadClass($className)
    {
        if(strpos($className,'sdkService') !== false){
            $className = str_replace('sdkService','app',$className);
        }
        $baseClasspath = str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
        $classPath = CGI_APP_PATH.$baseClasspath;
        if(file_exists($classPath))
        {
            require_once $classPath;
        }else{
            $classPath = CGI_APP_PATH.'vendor/'.$baseClasspath;
            require_once $classPath;
        }
    }
}

$autoLoad = new autoLoad();
spl_autoload_register(array($autoLoad,'loadClass'));



class wepiao
{

    public static $input = [];
    public static $channelId;
    public  static $config = null;

    public static $request;

    /**
     * 依据服务名,运行cgi
     *
     * @param string $strServiceName 服务名
     * @param array  $arrParams      参数数组
     * @param string $strAction
     *
     * @return mixed
     */
    public static function run($strServiceName = '', $strAction = '', array $arrParams = [])
    {

        $info['logId'] = !empty($arrParams['logId']) ? $arrParams['logId'] : md5($strServiceName.$strAction);
        $info['requestId'] = sdkService\helper\Utils::getRequestId();
        $info['requestParams'] = $arrParams;
        $info['errorLog'] = 'no error'; 
        Logger::getInstance()->initLogWatch($info);

        self::$request = new \stdClass();
        self::$request->serviceClass = $strServiceName;
        self::$request->action = $strAction;

        $logPath = "/data" . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'service' . DIRECTORY_SEPARATOR . date('Ymd') . DIRECTORY_SEPARATOR . $strServiceName.'_'.$strAction. '.log';
        Logger::getInstance()->setLogPath($logPath);


        static::$input = $arrParams;
        //检测参数中,是否有 channelId 参数
        static::checkParams($arrParams);
        if(empty(static::$config)) {
            static::$config = require_once CGI_APP_PATH . 'app/config/web.php';
        }
        $class = 'sdkService\service\service'.$strServiceName;
        $objClass = new $class($arrParams);
        if(isset($objClass::$levelDown) && $objClass::$levelDown==true && !in_array(lcfirst($strAction),$objClass->arrBanOfLevelDownFun)){
            return $objClass->levelDown($arrParams);
        }else{
            //先做limit检测，如果发现有限制，直接返回数据
            $initRes = $objClass->limitCheck($strServiceName, $strAction, $arrParams);
            if ( !empty($initRes)) {
                return $initRes;
            }
            return $objClass->$strAction($arrParams);
        }
        
    }

    /**
     * 检测必须参数, 如果没有,则抛出异常
     *
     * @param array $params
     *
     * @throws \Exception
     */
    public static function checkParams(array $params = [])
    {
        if (empty( $params['channelId'] )) {
            throw new \Exception(' The channelId is required !');
        }
        else {
            self::setChannelId($params['channelId']);
        }
    }

    public static function setChannelId($iChannelId)
    {
        self::$channelId = $iChannelId;
    }

    public static function getChannelId()
    {
        return self::$channelId;
    }
}
?>