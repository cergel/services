<?php
namespace sdkService\drivers;

use sdkService\helper;

class PDOManager
{

    /**
     * PDO链接实例
     *
     * @var \PDO
     */
    private static $pdoInstance = [];

    /**
     * 操作PDO的ORM实例
     *
     * @var array
     */
    private static $ormInstance = [];

    /**
     *
     * 获取PDO配置
     */
    public static function getConfigration($strDBConfigKey)
    {
        $arrDbConfig = isset( \wepiao::$config['params']['db'][$strDBConfigKey] ) ? \wepiao::$config['params']['db'][$strDBConfigKey] : [];
        if (empty( $arrDbConfig )) {
            return null;
        }

        return $arrDbConfig;
    }

    /**
     * 获取PDO操作实例
     *
     * @param string $dbConfigKey
     * @param string $tableName
     *
     * @return
     */
    public static function getInstance($dbConfigKey = '', $tableName = '')
    {
        //获取PDO操作对象
        $ormObj = static::getPDOOrm($dbConfigKey, $tableName);

        return ( !empty( $ormObj ) && is_object($ormObj) ) ? $ormObj : null;
    }

    /**
     * 获取PDO链接的对象
     *
     * @param string $dbConfigKey
     *
     * @return \PDO
     *
     * 此单例好处： 同库配置不用每次new pdo本身
     */
    private static function getPDO($dbConfigKey = '')
    {
        if (empty( $dbConfigKey )) {
            throw  new \InvalidArgumentException('param error');
        }
        if ( !isset( static::$pdoInstance[$dbConfigKey] )) {
            $config = static::getConfigration($dbConfigKey);
            $obj = new \PDO(
                $config['dsn'],
                $config['user'],
                $config['passwd'],
                [
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . $config['charset'] . ";",
                    \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                ]
            );
            static::$pdoInstance[$dbConfigKey] = $obj;
        }

        return static::$pdoInstance[$dbConfigKey];
    }

    /**
     * 获取操作PDO的ORM对象实例
     *
     * @return helper\PDO
     *
     * 此单例好处：同一个方法多次操作同库同表 不用多次new helper/pdo
     */
    private static function getPDOOrm($dbConfigKey = '', $tableName = '')
    {
        if (empty( $dbConfigKey ) || empty( $tableName )) {
            throw  new \InvalidArgumentException('param error');
        }
        try {
            $instanceKey = $dbConfigKey . '_' . $tableName;
            //如果orm实例不存在
            if ( !isset( static::$ormInstance[$instanceKey] )) {
                //获取pdo链接对象
                $pdo = static::getPDO($dbConfigKey);
                //实例化orm对象
                $ormObj = new helper\PDO($tableName);
                //设置orm对象操作的pdo对象
                $ormObj->setPdo($pdo);
                //将当前orm对象放入已实例化的数组中
                static::$ormInstance[$instanceKey] = $ormObj;
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
        return static::$ormInstance[$instanceKey];
    }

}