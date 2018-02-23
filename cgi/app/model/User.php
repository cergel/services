<?php
namespace sdkService\model;

class User extends BaseNew
{


    public function checkPhone($phone,$iChannelId)
    {
        return $this->redis($iChannelId,BONUS_NEW_USER)->WYsIsMember('mobile_set',$phone);
    }



}