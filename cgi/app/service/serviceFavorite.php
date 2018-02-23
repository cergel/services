<?php

namespace sdkService\service;

class serviceFavorite extends serviceBase
{

    /**
     * 获取用户收藏的电影院列表
     *
     * @param string openId     用户openId
     * @param string channelId  渠道编号
     * @return array
     */
    public function getFavoriteCinema(array $arrInput = [])
    {
        //参数整理
        $strOpenId = self::getParam($arrInput, 'openId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        $return = self::getStOut();
        $return['data']['cinemaList'] = $this->model('favorite')->getFavoriteCinema($iChannelId, $strOpenId);
        //日志记录
        //wepiao::info(__METHOD__ . ': input: ' . json_encode($arrInput) . ' return: ' . json_encode($return));

        return $return;
    }

    /**
     * 用户收藏，添加取消接口
     *
     * @param   array $arrInput
     * @param   string openId       用户openId
     * @param   string channelId    渠道编号
     * @param   string action       是否收藏, favorite 表示收藏, 其他表示不收藏
     * @param   string cinemaId     影院编号
     * @return  array
     */
    function favoriteCinema(array $arrInput = [])
    {
        //参数整理
        $strOpenId = self::getParam($arrInput, 'openId');
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iCinemaId = self::getParam($arrInput, 'cinemaId');
        $strAction = self::getParam($arrInput, 'action');
        $return = self::getStOut();
        if (empty($strOpenId) || empty($iChannelId) || empty($iCinemaId) || empty($strAction)) {
            $return['ret'] = $return['sub'] = ERROR_RET_PARAM_EMPTY;
            $return['msg'] = ERROR_MSG_PARAM_EMPTY;
        } else {
            $status = ($strAction === 'favorite') ? 0 : 1; //0:添加收藏; 1:删除收藏;
            try {
                if ($this->model('favorite')->favoriteCinema($iChannelId, $strOpenId, $iCinemaId, $status)) {
                    $return['ret'] = 0;
                    $return['sub'] = 0;
                    $return['msg'] = 'success';
                    $return['data'] = true;
                }
            } catch (\Exception $e) {
                //收藏失败的时候, sub码为抛出异常的code
                $return['ret'] = ERROR_RET_CINEMA_FAVORITE_ERROR;
                $return['sub'] = $e->getCode();
                $return['msg'] = ERROR_MSG_CINEMA_FAVORITE_ERROR . ': ' . $e->getMessage();
                $return['data'] = false;
            }

        }
        //日志记录
        //wepiao::info(__METHOD__ . ': input: ' . json_encode($arrInput) . ' return: ' . json_encode($return));

        return $return;
    }
}