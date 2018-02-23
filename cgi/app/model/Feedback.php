<?php
/**
 * Created by PhpStorm.
 * User:
 * Date:
 * Time:
 */

namespace sdkService\model;

class Feedback extends BaseNew
{
    public function addFeedback($params)
    {
        $pdo = $this->getPdo(USER_DB_APP, "t_feedback");
        $arrFields = ['uid', 'mobileNo', 'content', 'version', 'channelId', 'fromId', 'device', 'os', 'created', 'updated', 'network'];
        $mustFields = ['channelId', 'version', 'content'];
        $arrData = $this->formatInputArray($params, $arrFields, $mustFields);
        $time = time();
        $arrData['created'] = $time;
        $arrData['updated'] = $time;
        return $pdo->Nadd($arrData, array_keys($arrData));
    }

}