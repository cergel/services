<?php
/**
 * Created by PhpStorm.
 * User: syj
 * Date: 16/3/10
 * Time: 下午2:26
 */
class sdk
{
    private static $sdkInstance = null;

    public static function Instance()
    {
        if (empty(self::$sdkInstance)) {
            self::$sdkInstance = new self();
        }
        return self::$sdkInstance;
    }

    public function call($strControllerAction,$arrParams=[])
    {
        if (empty($strControllerAction) || strpos($strControllerAction, '/') == false) {
            throw new \Exception('action params error!');
        }
        $arrClass = explode('/', $strControllerAction);
        $strClassName = ucfirst($arrClass[0]);
        $strAction = $arrClass[1];
        $strClassName = $this->__formatClassName($strClassName);
        $strAction = $this->__formatClassName($strAction);

        require_once dirname(__FILE__).'/../cgi/index.php';
        return wepiao::run($strClassName,$strAction,$arrParams);
    }

    private function __formatClassName($strClassName)
    {
        if(strpos($strClassName,"-") !== false){
            $arr = explode("-",$strClassName);
            foreach($arr as &$value){
                $value = ucfirst($value);
            }
            $strClassName = implode('',$arr);
        }
        return $strClassName;
    }



}
?>