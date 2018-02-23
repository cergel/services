<?php

namespace sdkService\model;
/**
 * @tutorial 屏蔽词
 * @author liulong
 */
class ShieldingWords extends BaseNew
{
    /**
     * @tutorial 获取屏蔽词列表（数组）
     * @return array
     */
    public function getShieldingWordsList()
    {
        //先读redis
        $arrShieldingWordsList = $this->redis(\wepiao::getChannelId(),USER_COMMENT_CACHE)->WYget(COMMENT_SHIELDING_DATA);
        if($arrShieldingWordsList){
            $return = json_decode($arrShieldingWordsList,true);
        }else{
            $return = [];
        }
        return $return;
    }

}
