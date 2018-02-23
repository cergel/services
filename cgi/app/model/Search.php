<?php

namespace sdkService\model;

class Search extends BaseNew
{
    
    /**
     * 获取搜索推荐
     *
     * @return array
     */
    public function getSearchRecommend($iChannelId)
    {
        $data = $this->redis($iChannelId, USER_MOVIE_PEE)->WYget(SEARCH_RECOMMEND);
        $return = !empty($data) ? json_decode($data, true) : [];
        
        return $return;
    }
    
}