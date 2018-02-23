<?php

/**
 * 小程序相关
 */

namespace sdkService\service;

use sdkService\helper\Net;
use sdkService\helper\Utils;

class serviceSmallRoutine extends serviceBase
{

    /**
     * 由emoji的text生成token所用的密钥
     * @var string
     */
    private $emojiTokenSecret = '19385cd3de55a5c1ad36380d23ca46f4';

    /**
     * 获取随机一个题目
     * @param array $arrInput
     */
    public function getRandomProblemItem($arrInput = [])
    {
        $return = self::getStOut();
        $iChannelId = self::getParam($arrInput, 'channelId');
        $strOpenId = self::getParam($arrInput, 'openId');
        $iRepoId = self::getParam($arrInput, 'repoId');    //题库id
        if (empty($strOpenId)) {
            return $this->getErrorOut(ERRORCODE_XCX_EMOJI_OPENID_ERROR);
        }
        if (empty($iRepoId) || empty($iChannelId)) {
            return $this->getErrorOut(ERRORCODE_COMMON_PARAMS_ERROR);
        }
        /**
         * 随机题目说明
         * 1、24小时内，做随机。24小时后重新随机
         * 2、随机不可以随机到已经回答过的。如果用户全都回答过了，则重新开始随机
         * 3、
         */
        //1、建立用户级别的随机题库：判断用户是否有自己的随机题库，没有的话，重建一个
        $this->model('SmallRoutine')->buildRandRepoItems($iChannelId, $iRepoId, $strOpenId);
        //2、随机一个用户题目
        $iItemId = $this->model('SmallRoutine')->getAUserRandomItem($iChannelId, $iRepoId, $strOpenId);
        if ($iItemId) {
            //3、获取题目数据
            $return = $this->getAProblemItem(['channelId' => $iChannelId, 'openId' => $strOpenId, 'repoId' => $iRepoId, 'itemId' => $iItemId]);
        }

        return $return;
    }

    /**
     * 获取指定题目
     * @param $arrInput
     * @return array
     */
    public function getAProblemItem($arrInput)
    {
        $return = self::getStOut();
        $iChannelId = self::getParam($arrInput, 'channelId');
        $strOpenId = self::getParam($arrInput, 'openId');
        $iRepoId = self::getParam($arrInput, 'repoId');    //题库id
        $iItemId = self::getParam($arrInput, 'itemId');    //题目id
        $iUnsetOther = self::getParam($arrInput, 'unsetOhter', 1);    //是否unset前端不用的字段
        if (empty($iRepoId) || empty($iItemId) || empty($iChannelId)) {
            return $this->getErrorOut(ERRORCODE_COMMON_PARAMS_ERROR);
        }
        $res = $this->model('SmallRoutine')->getAProblemItem($iChannelId, $iRepoId, $iItemId);
        if (!empty($res)) {
            $data = $res;
            if ($iUnsetOther) {
                $data = [];
                $data['id'] = $res['id'];
                $data['repo_id'] = $res['repo_id'];
                $data['emojis'] = $res['emoji'];
                $data['tip'] = $res['tip'];
                $data['text'] = $res['text'];
                $data['answer_len'] = mb_strlen($res['text'], 'UTF-8');
                $data['crack_count'] = $res['crack_count'];
                $data['reward_type'] = $res['reward_type'];
            }
            $return['data'] = $data;
        }

        return $return;
    }

    /**
     * 获取题库名数据
     */
    public function getProblemRepos($arrInput = [])
    {
        $return = self::getStOut();
        $return['data']['list'] = [];
        $iChannelId = self::getParam($arrInput, 'channelId');
        $strOpenId = self::getParam($arrInput, 'openId');
        if (empty($iChannelId)) {
            return $this->getErrorOut(ERRORCODE_COMMON_PARAMS_ERROR);
        }
        $res = $this->model('SmallRoutine')->getRepos($iChannelId);
        if (!empty($res) && is_array($res)) {
            unset($res['9999']);
            $res = array_values($res);
            //只保留前端需要字段
            foreach ($res as &$repoInfo) {
                $data = [
                    'repo_id' => $repoInfo['repo_id'],
                    'repo_name' => $repoInfo['repo_name'],
                    //'joined_count' => 0,
                ];
                $repoInfo = $data;
            }
            $return['data']['list'] = $res;
        }

        return $return;
    }

    /**
     * 获取单个题库信息数据
     */
    public function getAProblemRepos($arrInput = [])
    {
        $return = self::getStOut();
        $return['data'] = [];
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iRepoId = self::getParam($arrInput, 'repoId');
        $strOpenId = self::getParam($arrInput, 'openId');
        if (empty($iChannelId) || empty($iRepoId)) {
            return $this->getErrorOut(ERRORCODE_COMMON_PARAMS_ERROR);
        }
        $res = $this->model('SmallRoutine')->getRepos($iChannelId);
        if (!empty($res) && is_array($res) && !empty($res[$iRepoId])) {
            //只保留前端需要字段
            $repoInfo = $res[$iRepoId];
            $data = [
                'repo_id' => $repoInfo['repo_id'],
                'repo_name' => $repoInfo['repo_name'],
                'joined_count' => 0,
            ];
            //只有9999题库（用户创建emoji的题库），才返回实际参与人数
            if ($data['repo_id'] == 9999) {
                $data['joined_count'] = $repoInfo['joined_count'] + $repoInfo['base_count'];
            }
            $return['data'] = $data;
        }

        return $return;
    }

    /**
     * 提交答案【判断答案是否正确】
     */
    public function checkProblemAnswer($arrInput = [])
    {
        $return = self::getStOut();
        $return['data'] = ['is_right' => 0, 'bonus_value' => 0, 'owner' => '', 'giftcard' => 0, 'user_name' => '', 'user_photourl' => ''];
        $iChannelId = self::getParam($arrInput, 'channelId');
        $strOpenId = self::getParam($arrInput, 'openId');
        $strAnswer = self::getParam($arrInput, 'answer');   //用户答案（中文汉字）
        $iRepoId = self::getParam($arrInput, 'repoId');   //题库id
        $iItemId = self::getParam($arrInput, 'itemId');   //题目id
        $iReward = self::getParam($arrInput, 'reward');   //是否需要奖励回答者
        $subChannelId = self::getParam($arrInput, 'subChannelId');   //子渠道id
        if (empty($iChannelId) || empty($strAnswer) || empty($iRepoId) || empty($iItemId)) {
            return $this->getErrorOut(ERRORCODE_COMMON_PARAMS_ERROR);
        }
        //答案中文验证
        if (!$this->validateAnswer($strAnswer)) {
            return $this->getErrorOut(ERRORCODE_XCX_EMOJI_ANSWER_VALIDATE_ERROR);
        }
        //获取题目信息
        $arrInput['unsetOhter'] = 0;
        $res = $this->getAProblemItem($arrInput);
        $correctAnswer = !empty($res['data']['text']) ? $res['data']['text'] : '';
        if (empty($correctAnswer)) {
            return $this->getErrorOut(ERRORCODE_XCX_EMOJI_INNER_ERROR);
        }
        //回答错误
        if ($correctAnswer != $strAnswer) {
            return $return;
        }
        //回答正确
        if ($correctAnswer == $strAnswer) {
            $return['data']['is_right'] = 1;
            $return['data']['owner'] = !empty($res['data']['problem_username']) ? $res['data']['problem_username'] : '';
            /**
             * 更新数据
             */
            $fromUser = !empty($res['data']['problem_openid']) ? $res['data']['problem_openid'] : '';   //出题人
            //1、更新Redis中破解次数
            $addRes = $this->model('SmallRoutine')->addUserCrackToRedis($strOpenId, $iRepoId, $iItemId, $iChannelId);
            $crackCount = 0;
            if ($addRes == 0) { //如果用户已经破解过此题目了

            } else {
                $crackCount = $this->model('SmallRoutine')->updateCrackCountRedis($strOpenId, $iRepoId, $iItemId, $iChannelId);
                //2、更新DB中破解次数
                $this->model('SmallRoutine')->updateCrackCountDB($strOpenId, $iRepoId, $iItemId, $iChannelId);
                //3、添加（或更新）回答记录
                $this->model('SmallRoutine')->addUserCrackToDB($strOpenId, $iRepoId, $iItemId, $iChannelId, $fromUser);
            }
            /**
             * 发奖励
             */
            if ($iReward) {
                $awardType = !empty($res['data']['reward_type']) ? intval($res['data']['reward_type']) : 0;
                $awardRes = $this->awardUser($crackCount, $awardType, $strOpenId, $iRepoId, $iItemId, $iChannelId, $fromUser, $subChannelId);
                $return['data']['bonus_value'] = !empty($awardRes['bonus']['value']) ? intval($awardRes['bonus']['value']) : 0;
                $return['data']['giftcard'] = !empty($awardRes['giftcard']['send']) ? intval($awardRes['giftcard']['send']) : 0;
                $return['data']['user_name'] = !empty($awardRes['user_info']['user_name']) ? $awardRes['user_info']['user_name'] : '';
                $return['data']['user_photourl'] = !empty($awardRes['user_info']['user_photourl']) ? $awardRes['user_info']['user_photourl'] : '';
            }
        }

        return $return;
    }

    /**
     * 奖励用户（发红包、发模板通知等）
     * @param int $crackCount 已破解次数（发礼品卡通知用）
     * @param string $strOpenId
     * @param string $iRepoId
     * @param string $iItemId
     * @param string $strFromUser
     * @param string $subChannelId 发红包用
     * @return []
     */
    private function awardUser($crackCount = 0, $awardType = '', $strOpenId = '', $iRepoId = '', $iItemId = '', $iChannelId = '', $strFromUser = '', $subChannelId = '')
    {
        $return = ['bonus' => ['send' => 0, 'value' => 0], 'giftcard' => ['send' => 0], 'user_info' => ['user_name' => '', 'user_photourl' => '']];
        if (empty($awardType) || empty($strOpenId) || empty($iRepoId) || empty($iItemId) || empty($iChannelId)) {
            return $return;
        }
        //4、判断这个答案，应该发红包，还是发礼品卡模板通知
        switch ($awardType) {
            case 0: //默认不发
                break;
            case 1: //发礼品卡通知（只有第一个答对的用户才发通知）
                if ($crackCount == 1) {
                    /**
                     * TODO
                     * 发模板通知
                     */
                    $return['giftcard']['send'] = 1;
                    /**
                     * 答题人的昵称和头像
                     */
                    $userInfo = $this->service('User')->getUserinfoByOpenid(['channelId' => $iChannelId, 'openId' => $strOpenId]);
                    $return['user_info']['user_name'] = !empty($userInfo['data']['nickName']) ? $userInfo['data']['nickName'] : '';
                    $return['user_info']['user_photourl'] = !empty($userInfo['data']['photoUrl']) ? $userInfo['data']['photoUrl'] : '';
                }
                break;
            case 2: //发红包
                //概率发红包（10%）
                if (1 == rand(0, 9)) {
                    $params = [
                        'bonusId' => '0',
                        'openId' => $strOpenId,
                        'rcv_cnt' => 1,
                        'channelId' => $iChannelId,
                        'subChannelId' => $subChannelId,
                        'clientIp' => Net::getRemoteIp(),
                    ];
                    $res = $this->service('Bonus')->getBonus($params);
                    if (isset($res['ret']) && ($res['ret'] == 0) && !empty($res['data'])) {
                        $return['bonus']['send'] = 1;
                        $return['bonus']['value'] = !empty($res['data']['bonuslist'][0]['bns_val']) ? $res['data']['bonuslist'][0]['bns_val'] : 0;
                    }
                }
                break;
            default:
                break;
        }

        return $return;
    }


    /**
     * 获取用户答对的总题目数
     */
    public function getUserCrackCount($arrInput = [])
    {
        $return = self::getStOut();
        $return['data']['crack_count'] = 0;
        $iChannelId = self::getParam($arrInput, 'channelId');
        $strOpenId = self::getParam($arrInput, 'openId');
        $res = $this->model('SmallRoutine')->getUserCrackCount($strOpenId, $iChannelId);
        if (!empty($res)) {
            $return['data']['crack_count'] = $res;
        }

        return $return;
    }

    /**
     * 获取某个题目被破解次数
     */
    public function getProblemCrackCount($arrInput = [])
    {
        $return = self::getStOut();
        $return['data']['crack_count'] = 0;
        $iChannelId = self::getParam($arrInput, 'channelId');
        $iRepoId = self::getParam($arrInput, 'repoId');   //题库id
        $iItemId = self::getParam($arrInput, 'itemId');   //题目id
        $res = $this->model('SmallRoutine')->getProblemCrackCount($iRepoId, $iItemId, $iChannelId);
        if (!empty($res)) {
            $return['data']['crack_count'] = $res;
        }

        return $return;
    }

    /**
     * 发送模板消息, 告知出题人，已经有人答对题了
     */
    private function sendMsgTPL()
    {

    }

    /**
     * 答案合法性验证
     * @param string $text
     * @return bool
     */
    private function validateAnswer($text = '')
    {
        $return = true;
        if (empty($text)) {
            return false;
        }
        //中文验证
        if (!(preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $text) > 0)) {
            return false;
        }
        //长度验证
        $mbStrLen = mb_strlen($text, 'UTF-8');
        if (($mbStrLen < 2) || ($mbStrLen > 6)) {
            return false;
        }

        return $return;
    }

    /**
     * 根据文字，获取emoji标识符
     * @param string $text
     * @param string $strOpenId
     * @return array
     */
    public function getEmojiesByText($arrInput = [])
    {
        $return = static::getStOut();
        $return['data']['emoji_translation'] = [];
        $iChannelId = self::getParam($arrInput, 'channelId');
        $text = self::getParam($arrInput, 'text');
        $strOpenId = self::getParam($arrInput, 'openId');
        if (!$this->validateAnswer($text)) {
            return $this->getErrorOut(ERRORCODE_XCX_EMOJI_ANSWER_VALIDATE_ERROR);
        }
        $res = $this->requestEmojiXML($text, $strOpenId);
        if (isset($res['errorno']) && ($res['errorno'] == 0) && !empty($res['data']['emoji_translation'])) {
            $return['data']['emoji_translation'] = $res['data']['emoji_translation'];
            $return['ret'] = $return['sub'] = $res['data']['ret'];  //用微信的错误码
        }

        return $return;
    }

    private function requestEmojiXML($text = '', $strOpenId = '')
    {
        $return = ['data' => [], 'errorno' => 0];
        $time = time();
        $queryInJson = '{"raw_query": "' . $text . '", "emoji_translation": "" }';
        $svrInfo = '{"uin_id":"[' . $strOpenId . ']","pid":"1700004884"}';
        $xml_data = "<xml>
<ToUserName><![CDATA[smartcs]]></ToUserName>
<FromUserName><![CDATA[fromUser]]></FromUserName>
<CreateTime>$time</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[$queryInJson]]></Content>
<MsgId>1234567890123456</MsgId>
<SvrInfo>$svrInfo</SvrInfo>
</xml>";
        $url = EMOJI_TEXT_TO_NO;
        $ch = curl_init();
        $header[] = "Content-type: text/xml;charset=UTF-8";//定义content-type为xml
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_data);
        $response = curl_exec($ch);
        if ($errorno = curl_errno($ch)) {
            $return['errorno'] = $errorno;
        } else {
            /**
             * 微信说，用UTF-8，结果返回值用的GKB，我还得自己探索，他们的编码格式，坑爹
             */
            $response = mb_convert_encoding($response, 'UTF-8', 'GBK');
            if (!empty($response) && is_string($response) && ($json = json_decode($response, true)) && !empty($json)) {
                if (!empty($json['emoji_translation'])) {
                    foreach ($json['emoji_translation'] as &$arrEmojiIds) {
                        $data = ['ids' => $arrEmojiIds, 'token' => $this->createEmojiToken($text, $arrEmojiIds)];
                        $arrEmojiIds = $data;
                    }
                }
                $return['data'] = $json;
            }
        }
        curl_close($ch);

        return $return;
    }

    /**
     * 创建用户级别的emoji题目
     * @param array $arrInput
     */
    public function createEmojiesProblem($arrInput = [])
    {
        $return = static::getStOut();
        $return['data'] = ['created' => 0, 'giftcard' => 0];
        $iChannelId = self::getParam($arrInput, 'channelId');
        $strOpenId = self::getParam($arrInput, 'openId');
        $text = self::getParam($arrInput, 'text');
        $emojiIds = self::getParam($arrInput, 'emojiIds');  //字符串类型，如：A_70,B_2
        $emojiIds = !empty($emojiIds) ? explode(',', $emojiIds) : [];
        $iRewardType = self::getParam($arrInput, 'awardType', 0);
        $iRewardType = (!empty($iRewardType) && ($iRewardType == 1)) ? 1 : 0;  //用户级别的奖励，只能最多是礼品卡通知，rewardType为1
        $emojiToken = self::getParam($arrInput, 'emojiToken');
        $formId = self::getParam($arrInput, 'formId');
        //1、参数校验
        if (empty($iChannelId) || empty($emojiIds) || empty($strOpenId) || empty($text) || empty($emojiToken)) {
            return $this->getErrorOut(ERRORCODE_COMMON_PARAMS_ERROR);
        }
        /**
         * 2、emoji token校验
         * 其实是为了验证，这个text和emojis是否对应，而不是用户随便篡改的（用户篡改的话，将显示不了图片，也相当于是脏数据）
         */
        if (!$this->validateEmojiToken($text, $emojiIds, $emojiToken)) {
            return $this->getErrorOut(ERRORCODE_XCX_EMOJI_EMOJI_VALIDATE_ERROR);
        }
        //3、入库
        //3.1：插入数据库
        $userInfo = $this->service('User')->getUserinfoByOpenid(['channelId' => $iChannelId, 'openId' => $strOpenId]);
        $strUserName = !empty($userInfo['data']['nickName']) ? $userInfo['data']['nickName'] : '';
        $tip = $this->generateProblemTip($text);
        $insertId = $this->model('SmallRoutine')->insertUserProblemToDb($strOpenId, $strUserName, $text, $emojiIds, $iRewardType, $tip, $formId);
        if ($insertId) {
            //3.1：插入redis
            $res = $this->model('SmallRoutine')->insertUserProblemToRedis($iChannelId, $insertId, $strOpenId, $strUserName, $text, $emojiIds, $iRewardType, $tip, $formId);
            if ($res) {
                $return['data']['created'] = 1;
                //$return['data']['giftcard'] = $iRewardType;
                $return['data']['id'] = intval($insertId);
                $return['data']['repo_id'] = 9999;
            }
        }
        return $return;
    }

    /**
     * 验证emoji token
     * @param string $text
     * @param array $emojiIds
     * @param string $emojiToken
     * @return bool
     */
    private function validateEmojiToken($text = '', $emojiIds = [], $emojiToken = '')
    {
        $return = false;
        if (!empty($text) && !empty($emojiIds) && !empty($emojiToken)) {
            $arrData = ['text' => $text, 'emoji' => json_encode($emojiIds)];
            $sign = Utils::makeSign($arrData, $this->emojiTokenSecret);
            if ($emojiToken == $sign) {
                $return = true;
            }
        }
        return $return;
    }

    /**
     * 生成emoji token
     * @param string $text
     * @param array $emojiIds
     * @param string $emojiToken
     * @return string
     */
    private function createEmojiToken($text = '', $emojiIds = [])
    {
        $return = '';
        if (!empty($text) && !empty($emojiIds)) {
            $arrData = ['text' => $text, 'emoji' => json_encode($emojiIds)];
            $return = Utils::makeSign($arrData, $this->emojiTokenSecret);
        }

        return $return;
    }

    /**
     * 根据一个文字串，生成题目提示文字（一个字）
     * @param $text
     * @return string
     */
    private function generateProblemTip($text)
    {
        $return = '';
        if (!empty($text)) {
            $strLen = mb_strlen($text);
            //取第一个文字
            $firstChar = mb_substr($text, 0, 1);
            //将第一个文字，哈希
            $crcnum = crc32($firstChar);
            //取余
            $yu = $crcnum % $strLen;
            //获取提示字
            $tip = mb_substr($text, $yu, 1);
            if (!empty($tip)) {
                $return = $tip;
            }
        }

        return $return;
    }

    /**
     * 创建用户级别的emoji题目
     * @param array $arrInput
     */
    public function getEmojiJoinedCount($arrInput = [])
    {
        $return = static::getStOut();
        $return['data'] = ['joined_count' => 1];
        $iChannelId = self::getParam($arrInput, 'channelId');
        //1、参数校验
        if (empty($iChannelId)) {
            return $this->getErrorOut(ERRORCODE_COMMON_PARAMS_ERROR);
        }
        $joinedCount = $this->model('SmallRoutine')->insertUserProblemToDb($iChannelId);

        return $return;
    }

}
