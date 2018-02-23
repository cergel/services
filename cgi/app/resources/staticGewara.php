<?php
namespace sdkService\resources;
/**
 * 获取Gewara静态数据
 *
 * @author CIYE
 * @version 2017-05-12
 */

class staticGewara extends staticBase
{
    private $fileNameTag = 'gewara_';

    public function __construct($staticName, $staticPath = '')
    {
        $staticPath = !empty($staticPath) ? $staticPath : CGI_APP_PATH . 'app/resources/gewara/';
        parent::__construct($staticName, $staticPath);
    }

    public function getGewaraId($id)
    {
        $array = $this->getSlotArr();
        $slot = $this->search($array, $id);
        if ($slot === null) {
            return '';
        }
        $fileName = $slot . $this->fileNameTag . '.php';
        if (!file_exists($fileName)) {
            return '';
        }
        $GewaraIds = include $fileName;
        return isset($GewaraIds[$slot]) ? $GewaraIds[$slot] : '';
    }

    public function gewaraInArray($gewaraId)
    {
        $staticArr = $this->getSimpleStaticFile();
        return in_array($gewaraId, $staticArr) ? true : false;
    }
}