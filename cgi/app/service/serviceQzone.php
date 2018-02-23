<?php
namespace sdkService\service;

use sdkService\helper\Utils;


class serviceQzone extends serviceBase
{

    /**
     * @param array $arrInput
     * cid->评论Id
     * content->评论内容
     * attitude->是否喜欢 1->喜欢 2->失望
     * movieId->影片Id
     * openId
     *
     */
    public function sendComment2Qzone($arrInput = [])
    {

        $arrReturn = self::getStOut();
        $iMovieId = $arrInput['movieId'];
        $strOpenId = $arrInput['openId'];
        $iCid = $arrInput['cid'];
        $strContent = $arrInput['content'];
        $iAttitude = $arrInput['attitude'];
        $iChannelId = $arrInput['channelId'];

        $arrSendCookie = [];
        $arrSendCookie['authtype'] = 1;
        $arrSendCookie['appid'] = '101233410';
        $arrSendCookie['openid'] = $strOpenId;
        $arrSendCookie['openkey'] = $this->service('Mqq')->getMqqUserToken([
                'channelId' => $iChannelId,
                'openId' => $strOpenId
            ]);
        if (empty($arrSendCookie['openkey'])) {
            $arrReturn['ret'] = $arrReturn['sub'] = -1;
            $arrReturn['msg'] = 'get openkey error!';
            return $arrReturn;
        }

        $arrSendParams = [];
        $arrSendParams['seq'] = rand(1000, 9999);
        $arrSendParams['cmd'] = 'WEPIAO';
        $arrSendParams['subcmd'] = 'wepiao';
        $arrSendParams['format'] = 'json';

        $arrBizData = [];
        $arrBizData['cmd'] = 'wepiao';
        $arrBizData['format'] = 'json';
        $arrBizData['subcmd'] = 'addugc';
        $arrBizData['mode'] = 1;
        $arrBizData['movie_id'] = $iMovieId;
        $arrBizData['cid'] = $iCid;
        $arrBizData['content'] = $strContent;
        $arrBizData['attitude'] = $iAttitude;


        $arrSendParams['biz_data'] = urlencode(json_encode($arrBizData));

        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
            //'sendType'=>'json',
            'arrCookies' => $arrSendCookie
        ];


        $result = $this->http('http://h5.qzone.qq.com/p/openhydra/cgi-bin/open_hydra?uin=2403618756', $httpParams);
        if (isset($result['code']) && isset($result['subcode'])) {
            $arrReturn['ret'] = $result['code'];
            $arrReturn['sub'] = $result['subcode'];
            $arrReturn['msg'] = $result['message'];
        } else {
            $arrReturn = $result;
        }

        return $arrReturn;

    }


}