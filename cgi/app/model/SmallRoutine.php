<?php

namespace sdkService\model;

class SmallRoutine extends BaseNew
{

    protected $dbConfig = 'dbApp';
    protected $tableName = 't_problem_item';

    /**
     * 获取指定题目
     * @param string $iChannelId 渠道id
     * @param string $repoId 题库id
     * @param string $problemItemId 题目id
     * @return array
     */
    public function getAProblemItem($iChannelId = '', $repoId = '', $problemItemId)
    {
        $return = [];
        if (empty($iChannelId) || empty($repoId) || empty($problemItemId)) {
            return $return;
        }
        $strKey = XIAOCHENGXU_PROBLEM_ITEM . $iChannelId . '_' . strval($repoId) . '_' . $problemItemId;
        $data = $this->redis($iChannelId, EMOJI_XIAOCHENGXU)->WYhGetAll($strKey);
        if (!empty($data) && is_array($data)) {
            $data['emoji'] = !empty($data['emoji']) ? json_decode($data['emoji']) : [];
            $return = $data;
        }

        return $return;
    }

    /**
     * 获取题库名数据
     * @param string $iChannelId
     * @return array|mixed
     */
    public function getRepos($iChannelId = '', $iRepoId = '')
    {
        $return = [];
        if (!empty($iChannelId)) {
            $strKey = XIAOCHENGXU_PROBLEM_REPO_NAME . $iChannelId;
            if (!empty($iRepoId)) {
                $data = $this->redis($iChannelId, EMOJI_XIAOCHENGXU)->WYhGet($strKey, $iRepoId);
                if (!empty($data)) {
                    $return = json_decode($data, true);
                }
                $return[$iRepoId] = $return;
            } else {
                $data = $this->redis($iChannelId, EMOJI_XIAOCHENGXU)->WYhGetAll($strKey);
                if (!empty($data)) {
                    $data = array_filter($data);
                    if (!empty($data)) {
                        foreach ($data as &$v) {
                            $v = json_decode($v, true);
                        }
                        $return = $data;
                    }
                }
            }
        }
        return $return;
    }

    /**
     * 建立用户某个题库的答题基数
     * @param string $iChannelId
     * @param string $iRepoId
     * @param string $strOpenId
     * @return bool|int
     */
    public function buildRandRepoItems($iChannelId = '', $iRepoId = '', $strOpenId = '')
    {
        $return = true;
        if (empty($iChannelId) || empty($iRepoId) || empty($strOpenId)) {
            return false;
        }
        $userRandRepoItemKey = XIAOCHENGXU_USER_RANDOM_REPO_ITEM . $iChannelId . '_' . $strOpenId . '_' . $iRepoId;
        $strRepoItemKey = XIAOCHENGXU_PROBLEM_REPO_ITEM . $iChannelId . '_' . strval($iRepoId);
        //如果用户key不存在，创建
        if (!$this->redis($iChannelId, EMOJI_XIAOCHENGXU)->WYexists($userRandRepoItemKey)) {
            $return = $this->redis($iChannelId, EMOJI_XIAOCHENGXU)->WYsDiffStore($userRandRepoItemKey, $strRepoItemKey, $userRandRepoItemKey);
            if ($return) {
                //设置24小时过期
                $this->redis($iChannelId, EMOJI_XIAOCHENGXU)->WYexpire($userRandRepoItemKey, 60 * 60 * 24);
            }
        }

        return $return;
    }

    /**
     * 建立用户某个题库的答题基数
     * @param string $iChannelId
     * @param string $iRepoId
     * @param string $strOpenId
     * @return bool|int
     */
    public function getAUserRandomItem($iChannelId = '', $iRepoId = '', $strOpenId = '')
    {
        $return = null;
        if (empty($iChannelId) || empty($iRepoId) || empty($strOpenId)) {
            return $return;
        }
        $userRandRepoItemKey = XIAOCHENGXU_USER_RANDOM_REPO_ITEM . $iChannelId . '_' . $strOpenId . '_' . $iRepoId;
        if (!empty($iChannelId) && !empty($iRepoId)) {
            $return = $this->redis($iChannelId, EMOJI_XIAOCHENGXU)->WYsPop($userRandRepoItemKey);

        }

        return $return;
    }

    /**
     * 更新DB破解次数
     * @param string $strOpenId
     * @param string $iRepoId
     * @param string $iItemId
     * @param string $iChannelId
     * @return bool|string
     * @throws \Exception
     */
    public function updateCrackCountDB($strOpenId = '', $iRepoId = '', $iItemId = '', $iChannelId = '')
    {
        $this->tableName = 't_problem_item';
        $fields = ['crack_count' => 1];
        //更新DB
        try {
            $result = $this->getPdoHelper()->newupdate($fields, 'id=' . $iItemId);
        } catch (\PDOException $e) {
            throw new \Exception('数据库错误', $e->getCode());
        }

        //更新Redis
        return $result;
    }

    /**
     * 添加用户回答正确的记录
     * @param string $strOpenId
     * @param string $iRepoId
     * @param string $iItemId
     * @param string $iChannelId
     * @return bool|string
     * @throws \Exception
     */
    public function addUserCrackToDB($strOpenId = '', $iRepoId = '', $iItemId = '', $iChannelId = '', $strFromUser = '')
    {
        $this->tableName = 't_problem_user';
        $return = false;
        if (empty($strOpenId) || empty($iRepoId) || empty($iItemId) || empty($iChannelId)) {
            return $return;
        }
        $params = [
            'answer_openid' => $strOpenId,
            'question_openid' => $strFromUser,
            'channel_id' => $iChannelId,
            'update_time' => time(),
            'create_time' => time(),
            'answer_status' => 1,
            'item_id' => intval($iItemId),
            'repo_id' => intval($iRepoId),
        ];
        //更新DB
        try {
            $onUpdate = '';
            foreach ($params as $k => $v) {
                if ($k == 'create_time') {
                    continue;
                }
                if (is_string($v)) {
                    $v = "'{$v}'";
                }
                $onUpdate .= "$k=$v,";
            }
            $onUpdate = rtrim($onUpdate, ',');
            $return = $this->getPdoHelper()->Nadd($params, array_keys($params), $onUpdate);
        } catch (\PDOException $e) {
            throw new \Exception('数据库错误', $e->getCode());
        }

        return $return;
    }

    /**
     * 更新Redis破解次数
     * @param string $strOpenId
     * @param string $iRepoId
     * @param string $iItemId
     * @param string $iChannelId
     * @return null|string
     */
    public function updateCrackCountRedis($strOpenId = '', $iRepoId = '', $iItemId = '', $iChannelId = '')
    {
        $return = null;
        if (empty($iChannelId) || empty($iRepoId) || empty($iItemId) || empty($strOpenId)) {
            return $return;
        }
        $strKey = XIAOCHENGXU_PROBLEM_ITEM . $iChannelId . '_' . strval($iRepoId) . '_' . $iItemId;
        $return = $this->redis($iChannelId, EMOJI_XIAOCHENGXU)->WYhIncrBy($strKey, 'crack_count', 1);

        return $return;
    }

    /**
     * 添加用户回答对的题目
     * @param string $strOpenId
     * @param string $iRepoId
     * @param string $iItemId
     * @param string $iChannelId
     * @return null|string
     */
    public function addUserCrackToRedis($strOpenId = '', $iRepoId = '', $iItemId = '', $iChannelId = '')
    {
        $return = null;
        if (empty($iChannelId) || empty($iRepoId) || empty($iItemId) || empty($strOpenId)) {
            return $return;
        }
        $strKey = XIAOCHENGXU_USER_CRACK_ITEM . $iChannelId . '_' . $strOpenId;
        $setKey = $iRepoId . '_' . $iItemId;
        $return = $this->redis($iChannelId, EMOJI_XIAOCHENGXU)->WYsAdd($strKey, $setKey);

        return $return;
    }

    /**
     * 获取用户所有答过的题目
     * @param string $strOpenId
     * @param string $iChannelId
     * @return int
     */
    public function getUserCrackCount($strOpenId = '', $iChannelId = '')
    {
        $return = 0;
        if (empty($iChannelId) || empty($strOpenId)) {
            return $return;
        }
        $strKey = XIAOCHENGXU_USER_CRACK_ITEM . $iChannelId . '_' . $strOpenId;
        $res = $this->redis($iChannelId, EMOJI_XIAOCHENGXU)->WYsCard($strKey);
        if (!empty($res)) {
            $return = $res;
        }

        return $return;
    }

    /**
     * 获取用户所有答过的题目
     * @param string $strOpenId
     * @param string $iChannelId
     * @return int
     */
    public function getProblemCrackCount($iRepoId = '', $iItemId = '', $iChannelId = '')
    {
        $return = 0;
        if (empty($iChannelId) || empty($iRepoId) || empty($iItemId)) {
            return $return;
        }
        $strKey = XIAOCHENGXU_PROBLEM_ITEM . $iChannelId . '_' . strval($iRepoId) . '_' . $iItemId;
        $res = $this->redis($iChannelId, EMOJI_XIAOCHENGXU)->WYhGet($strKey, 'crack_count');
        if (!empty($res)) {
            $return = $res;
        }

        return $return;
    }

    /**
     * 用户题目入数据库
     * @return int 插入的题目id
     */
    public function insertUserProblemToDb($strOpenId = '', $strUserName = '', $text = '', $arrEmojies = [], $iRewardType = 0, $tip = '', $formId = '')
    {
        $return = 0;
        if (!empty($strOpenId) && !empty($text) && !empty($arrEmojies) && !empty($strUserName)) {
            $params = [
                'repo_id' => 9999,
                'problem_text' => $text,
                'problem_emoji' => implode(',', $arrEmojies),
                'problem_tip' => $tip,
                'create_time' => time(),
                'update_time' => time(),
                'problem_status' => 1,
                'problem_openid' => $strOpenId,
                'problem_username' => $strUserName,
                'crack_count' => 0,
                'reward_type' => $iRewardType,
                'form_id' => $formId,
            ];
            //更新DB
            try {
                $return = $this->getPdoHelper()->Nadd($params, array_keys($params));
            } catch (\PDOException $e) {
                throw new \Exception('数据库错误', $e->getCode());
            }
        }

        return $return;
    }

    /**
     * 用户题目入redis
     * @param string $iChannelId
     * @param string $iItemId
     * @param string $strOpenId
     * @param string $strUserName
     * @param string $text
     * @param array $arrEmojies
     * @param int $iRewardType
     * @return bool
     */
    public function insertUserProblemToRedis($iChannelId = '', $iItemId = '', $strOpenId = '', $strUserName = '', $text = '', $arrEmojies = [], $iRewardType = 0, $tip = '', $formId = '')
    {
        $return = false;
        if (empty($iChannelId) || empty($iItemId) || empty($strOpenId) || empty($strUserName) || empty($text) || empty($arrEmojies) || empty($tip)) {
            return $return;
        }
        $data = [
            'id' => $iItemId,
            'repo_id' => 9999,
            'text' => $text,
            'emoji' => json_encode($arrEmojies),
            'tip' => $tip,
            'crack_count' => 0,
            'reward_type' => $iRewardType,
            'problem_username' => $strUserName,
            'problem_openid' => $strOpenId,
            'form_id' => $formId,
        ];
        $strKey = XIAOCHENGXU_PROBLEM_ITEM . $iChannelId . '_9999_' . $iItemId;
        $return = $this->redis(strval($iChannelId), EMOJI_XIAOCHENGXU)->WYhMset($strKey, $data);

        return $return;
    }

}