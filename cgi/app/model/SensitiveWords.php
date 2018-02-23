<?php

namespace sdkService\model;
/**
 * @tutorial 敏感词
 * @author liulong
 */
class SensitiveWords extends BaseNew
{
    /**
     * @tutorial 获列表（数组）redis还未拆分出来
     * @return array
     */
     public function getSensitiveWordsList()
     {
         //先读redis
         $arrSensitiveWordsList = $this->redis(\wepiao::getChannelId(),USER_COMMENT_CACHE)->WYget(COMMENT_SENSITIVEWORDS_DATA);
         if($arrSensitiveWordsList){
             $return = json_decode($arrSensitiveWordsList,true);
         }else{
             $return = [];
         }
         return $return;
     }

}
