<?php
namespace sdkService\resources;

/**
 * 资源获取base
 *
 * @author CIYE
 * @version 2017-05-12
 */
use DirectoryIterator;

class staticBase
{
    //静态数据路径
    private static $staticPath;
    //静态数据名称
    private static $staticName;
    //数据存放路径
    private static $staticFilePath;

    public function __construct($staticName, $staticPath = '')
    {
        $this->setStaticPath($staticPath);
        $this->setStaticName($staticName);
        $this->setStaticFilePath();
    }


    public function setStaticName($staticName)
    {
        if (!empty($staticName)) {
            self::$staticName = $staticName;
        }
    }

    public function setStaticPath($staticPath)
    {
        if (!empty($staticPath)) {
            self::$staticPath = $staticPath;
        }
    }

    public function setStaticFilePath()
    {
        self::$staticFilePath = $this->getStaticPath() . $this->getStaticName() . '/';
    }

    public function getStaticPath()
    {
        return self::$staticPath;
    }

    public function getStaticName()
    {
        return self::$staticName;
    }

    public function getStaticFilePath()
    {
        return self::$staticFilePath;
    }

    /**
     * 获取简单分片
     */
    protected function getSlotArr()
    {
        $filePath = $this->getStaticFilePath();
        if (!is_dir($filePath)) {
            return [];
        }
        $DirIterator = new DirectoryIterator($filePath);
        $arr = [];
        foreach ($DirIterator as $file) {
            if (!$DirIterator->isDot()) {
                $arr[] = (int)$file->getBasename();
            }
        }
        sort($arr);
        return $arr;
    }

    /**
     * 二分法(获取区间)
     * @param $array
     * @param $k
     * @param int $low
     * @param int $high
     * @return int|null
     */
    protected function search($array, $k, $low = 0, $high = 0)
    {
        if (count($array) != 0 && $high == 0) {
            $high = count($array);
        }
        if ($low <= $high) {
            $mid = intval(($low + $high) / 2);
            $max = $array[$mid];
            $min = $mid > 0 ? $array[$mid - 1] : 0;
            if ($min < $k && $k <= $max) {
                return $mid;
            } elseif ($min > $k && $k < $max) {
                return $this->search($array, $k, $low, $mid - 1);
            } else {
                return $this->search($array, $k, $mid + 1, $high);
            }
        }
        return null;
    }

    /**
     * 简单获取文件
     */
    protected function getSimpleStaticFile()
    {
        $fileName = $this->getStaticPath() . $this->getStaticName() . '.php';
        if (!file_exists($fileName)) {
            return [];
        }
        return include $fileName;
    }
}