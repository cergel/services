<?php

namespace sdkService\service;

class serviceTest extends serviceBase
{

    public function t()
    {
        $a = $this->model('test')->get();
        print_r($a);
    }

}