<?php

namespace sdkService\model;

class Test extends BaseNew
{
    public function __construct()
    {
        $this->pdo('db1', 'bonus_dev');
    }


    public function save($arrParams = [])
    {

        return $this->pdohelper->Nadd($arrParams, array_keys($arrParams));
    }

    public function get()
    {
        return $this->pdohelper->fetchArray("devId=:devId",[':devId'=>'ffffffff-ed29-b2f6-9f29-d6ef13a86b79']);
    }
}