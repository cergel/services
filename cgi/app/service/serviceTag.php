<?php
/**
 * 用户标签
 */
namespace sdkService\service;

class serviceTag extends serviceBase
{
    /**
     * 向redis中导入数据
     * @param array $data 用户数据，json格式
     *  {"openid333":{"tag111":"sadsad","tag222":"sadsad","tag333":"sadsad"}}
     * @param array $channelId
     * @return array
     */
    public function Import(array $arrInput = [])
    {
        $rst = ['ret' => 0, 'sub' => 0, 'msg' => 'success'];

        //参数处理
        $data = self::getParam($arrInput, 'data');
        $channelId = self::getParam($arrInput, 'channelId');

        $userinfo = json_decode($data, true);
        if (!empty($userinfo)) {
            foreach ($userinfo as $openid => $tags) {
                //标签为数组时特殊处理
                foreach ($tags as $k => $v) {
                    $tags[$k] = is_array($v) ? json_encode($v) : $v;
                }
                $this->model('tag')->ImportIntoRedis($openid, $tags, $channelId);
            }
            return $rst;
        } else {
            return ['ret' => -1, 'sub' => -1, 'msg' => 'input params error'];
        }
    }

    /**
     * 查询用户标签
     * @param array $openId
     * @param array $tags 要查询的标签，多个时用逗号分隔，如 tag1,tag2..
     * @param array $channelId
     * @return array
     */
    public function getTag(array $arrInput = [])
    {
        $rst = ['ret' => 0, 'sub' => 0, 'msg' => 'success'];

        //参数处理
        $openid = self::getParam($arrInput, 'openId');
        $tags = self::getParam($arrInput, 'tags');
        $channelId = self::getParam($arrInput, 'channelId');

        if (empty($tags)) {
            $response = $this->model('tag')->getTagsAll($openid, $channelId); //默认查全部tag
        } else {
            $arrTags = explode(',', $tags);
            $response = $this->model('tag')->getTags($openid, $arrTags, $channelId);
        }
        if (!empty($response)) {
            //标签为数组时特殊处理
            foreach ($response as $k => $v) {
                $vDecode = json_decode($v, true);
                $response[$k] = !empty($vDecode) ? $vDecode : $v;
            }
            $rst['data'] = $response;
            return $rst;
        } else {
            return ['ret' => -2, 'sub' => -2, 'msg' => 'data not found'];
        }
    }
}

