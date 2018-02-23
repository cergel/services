<?php

namespace sdkService\model;
/**
 * @tutorial 黑名单
 * @author   liulong
 */
class BlackList extends BaseNew
{

    /**
     * @tutorial 获取黑名单列表（数组）
     * @return array
     */
    public function getBlackList()
    {
        $blackList = $this->redis(\wepiao::getChannelId(),USER_COMMENT_CACHE)->WYsMembers(COMMENT_BLACKLIST_DATA);
        if($blackList){
            $return = $blackList;
        }else{
            $return = [];
        }
        return $return;
    }
}
