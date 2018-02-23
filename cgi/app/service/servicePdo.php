<?php
namespace sdkService\service;


class servicePdo extends serviceBase
{

    public function test()
    {
        $arr = [
            'open_id'     => 'dddd',
            'cinema_no'   => 1234,
            'status'      => 1,
            'update_time' => 131,
        ];

        return $this->model('test')->get();
    }
}