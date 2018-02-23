<?php

namespace sdkService\service;


use sdkService\helper\Utils;

class serviceUser extends serviceBase
{
    /**
     * 手机号注册
     * @param  int mobileNo 手机号
     * @param  string password 密码
     * @param  string nickname 昵称
     * @param  string avatar 头像地址
     * @return array  {"ret":"0","sub":"0","msg":"SUCCESS"}
     */
    public function register($arrInput)
    {
        $url = JAVA_API_REGISTER;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'mobileNo' => self::getParam($arrInput, 'mobileNo'),
            'password' => strtolower(self::getParam($arrInput, 'password')),
            'nickname' => self::getParam($arrInput, 'nickname'),
            'photoUrl' => self::getParam($arrInput, 'avatar'),
        ];
        $data = $this->http($url, $params);
        return $data;
    }

    /**
     * 第三方帐号注册
     * @param  string openId 第三方UID
     * @param  string otherId 第三方平台编号
     * @param  string nickname 昵称
     * @param  string avatar 头像地址
     * @return array  {"ret":"0","sub":"0","msg":"SUCCESS"}
     */
    public function registerByOpenid($arrInput)
    {
        $url = JAVA_API_OPENIDREGISTER;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'OpenID' => self::getParam($arrInput, 'openId'),
            'OtherID' => self::getParam($arrInput, 'otherId'),
            'unionId' => self::getParam($arrInput, 'unionId'),
            'nickName' => self::getParam($arrInput, 'nickname'),
            'photo' => self::getParam($arrInput, 'avatar'),
            'channelId' => self::getParam($arrInput, 'channelId'),
            'subOtherId' => self::getParam($arrInput, 'subOtherId'),
        ];
        $data = $this->http($url, $params);
        return $this->convPhotoUrl($data);
    }

    /**
     * 用户登录
     * @param  string mobileNo 手机号
     * @param  string password 密码
     * @return array  {"ret":"0","sub":"0","msg":"SUCCESS"}
     */
    public function login($arrInput)
    {
        $url = JAVA_API_LOGIN;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'mobileNo' => self::getParam($arrInput, 'mobileNo'),
            'password' => strtolower(self::getParam($arrInput, 'password')),
        ];
        $data = $this->http($url, $params);
        return $this->convPhotoUrl($data);
    }

    /**
     * 修改用户信息
     * @param string $memberId 用户UID，支持传入openId,uid,memberId,unionId等参数
     * @param string $city 城市
     * @param string $nickname 昵称
     * @param string $name 真实姓名
     * @param int $sex 性别
     * @param string $email 邮箱
     * @param string $avatar 头像地址
     * @param string $userKey 用户身份证号
     * @param string $signature 用户签名
     * @param int $maritalStat 婚恋状态，具体数值由前端定义
     * @param int $carrer 职业，具体数值由前端定义
     * @param string $enrollmentYear 入学年份
     * @param string $highestEdu 最高学历，具体数值由前端定义
     * @param string $school 学校
     * @param string $birthday 生日
     * @param string $watchCPNum 共同观影人数
     * @return array
     */
    public function updateUserinfo($arrInput)
    {
        $return = self::getStOut();
        //取id值，从uid、memberId、userId、id、openId、unionId中依次取。
        $arrIdRelat['id'] = self::getParamMemberid($arrInput);
        if (empty($arrIdRelat['id'])) {
            $arrIdRelat['id'] = !empty($arrInput['id']) ? $arrInput['id'] :
                (!empty($arrInput['openId']) ? $arrInput['openId'] :
                    (!empty($arrInput['unionId']) ? $arrInput['unionId'] : ''));
        }
        $resIdRelat = $this->getIdRelation($arrIdRelat);
        if (!empty($resIdRelat['data']['idRelation']['id'])) {
            $relationId = $resIdRelat['data']['idRelation']['id'];
        } else {
            return self::getErrorOut(ERRORCODE_UCENTER_NO_USER);
        }
        $url = JAVA_API_UPDATEUSERINFO;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'UID' => $relationId, //用户UID
            'city' => self::getParam($arrInput, 'city', null), //城市
            'nickname' => self::getParam($arrInput, 'nickname', null), //昵称
            'name' => self::getParam($arrInput, 'name', null), //真实姓名
            'sex' => self::getParam($arrInput, 'sex', null), //性别
            'email' => self::getParam($arrInput, 'email', null), //邮箱
            'photoUrl' => self::getParam($arrInput, 'avatar', null), //头像地址
            'userKey' => self::getParam($arrInput, 'userKey', null), //用户身份证号
            'signature' => self::getParam($arrInput, 'signature', null), //用户签名
            'maritalStat' => self::getParam($arrInput, 'maritalStat', null), //婚恋状态
            'carrer' => self::getParam($arrInput, 'carrer', null), //职业
            'enrollmentYear' => self::getParam($arrInput, 'enrollmentYear', null), //入学年份
            'highestEdu' => self::getParam($arrInput, 'highestEdu', null), //最高学历
            'school' => self::getParam($arrInput, 'school', null), //学校
            'birthday' => self::getParam($arrInput, 'birthday', null), //生日
            'watchCPNum' => self::getParam($arrInput, 'watchCPNum', null), //共同观影人数
            'hobbies' => self::getParam($arrInput, 'hobbies', null), //用户兴趣爱好
        ];
        //将值为空的字段删除，避免覆盖(可配置允许空的字段)
        foreach ($params['arrData'] as $k => $v) {
            if (empty($v) && $v === null) {
                unset($params['arrData'][$k]);
            }
        }
        $data = $this->http($url, $params);
        if ($data['ret'] == 0 && $data['sub'] == 0) {
            if (isset($data['data']['extUid'])) {
                unset($data['data']['extUid']);
            }
            $return['data'] = $data['data'];
            return $this->convPhotoUrl($return);
        } else {
            return self::getErrorOut(ERRORCODE_UCENTER_UPDATE_FAIL);
        }
    }

    /**
     * 绑定手机号
     * @param  string $openId 第三方UID
     * @param  string $otherId 第三方平台编号
     * @param  string $unionId 微信唯一用户标识
     * @param  int $mobileNo 手机号
     * @return array
     */
    public function bindMobile($arrInput)
    {
        $url = JAVA_API_BINDMOBILENO;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'OpenID' => self::getParam($arrInput, 'openId'),
            'OtherID' => self::getParam($arrInput, 'otherId'),
            'unionId' => self::getParam($arrInput, 'unionId'),
            'mobileNo' => self::getParam($arrInput, 'mobileNo'),
            'channelId' => self::getParam($arrInput, 'channelId')
        ];
        $data = $this->http($url, $params);
        return $data;
    }

    /**
     * 修改手机号
     * @param  string $userId UID
     * @param  string $mobileNo 新手机号
     * @param  string $mobileNoOld 旧手机号
     * @return array
     */
    public function updateMobile($arrInput)
    {
        $url = JAVA_API_UPDATEMOBILENO;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'UID' => self::getParamMemberid($arrInput),
            'newmobileno' => self::getParam($arrInput, 'mobileNo'),
            'oldmobileno' => self::getParam($arrInput, 'mobileNoOld'),
        ];
        $data = $this->http($url, $params);
        return $data;
    }

    /**
     * 解绑第三方用户
     * @param  string $openId 第三方UID
     * @param  string $otherId 第三方平台编号
     * @return array
     */
    public function unbindOpenid($arrInput)
    {
        $url = JAVA_API_UNBINDOPENID;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'OpenID' => self::getParam($arrInput, 'openId'),
            'OtherID' => self::getParam($arrInput, 'otherId'),
        ];
        $data = $this->http($url, $params);
        return $data;
    }

    /**
     * 用户密码修改
     * @param string $userId 用户UID
     * @param string $newpassword 新密码
     * @param string $mobileNo 手机号，防黄牛加的验证
     * @return array
     */
    public function updatePassword($arrInput)
    {
        $url = JAVA_API_UPDATEPASSWORD;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'UID' => self::getParamMemberid($arrInput),
            'newpassword' => strtolower(self::getParam($arrInput, 'newpassword')),
            'mobileNo' => self::getParam($arrInput, 'mobileNo'),
            'opType' => self::getParam($arrInput, 'opType'), //0修改密码，1重置密码
        ];
        //前端不管是修改密码还是重置密码对于java端都是用的这一个接口，
        //入参有passwordOld时才进行旧密码验证，没有passwordOld就直接修改。
        if (isset($arrInput['oldpassword'])) {
            $params['arrData']['oldpassword'] = strtolower($arrInput['oldpassword']);
        }
        $data = $this->http($url, $params);
        return $data;
    }

    /**
     * 通过UID查询用户
     * @param  string $memberId 用户UID
     * @return array
     */
    public function getUserinfoByUid($arrInput)
    {
        $return = self::getStOut();
        $url = JAVA_API_GETUSERINFOBYUID;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'UID' => self::getParamMemberid($arrInput),
        ];
        $data = $this->http($url, $params);
        if ($data['ret'] == 0 && $data['sub'] == 0) {
            if (isset($data['data']['extUid'])) {
                unset($data['data']['extUid']);
            }
            $return['data'] = $data['data'];
            return $this->convPhotoUrl($return);
        } else {
            return self::getErrorOut(ERRORCODE_UCENTER_NO_USER);
        }
    }

    /**
     * 通过OpenID查询用户
     * @param  string $openId 第三方UID
     * @return array
     */
    public function getUserinfoByOpenid($arrInput)
    {
        $return = self::getStOut();
        $url = JAVA_API_GETUSERINFOBYOPENID;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'OpenID' => self::getParam($arrInput, 'openId'),
            #'OtherID' => self::getParam($arrInput, 'otherId'),
        ];
        $data = $this->http($url, $params);
        if ($data['ret'] == 0 && $data['sub'] == 0) {
            if (isset($data['data']['extUid'])) {
                unset($data['data']['extUid']);
            }
            $return['data'] = $data['data'];
            return $this->convPhotoUrl($return);
        } else {
            return self::getErrorOut(ERRORCODE_UCENTER_NO_USER);
        }
    }

    /**
     * 通过手机号查询用户
     * @param string $mobileNo 手机号
     * @return array
     */
    public function getUserinfoByMobile($arrInput)
    {
        $return = self::getStOut();
        $url = JAVA_API_GETUSERINFOBYMOBILE;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'mobileNo' => self::getParam($arrInput, 'mobileNo'),
        ];
        $data = $this->http($url, $params);
        if ($data['ret'] == 0 && $data['sub'] == 0) {
            if (isset($data['data']['extUid'])) {
                unset($data['data']['extUid']);
            }
            $return['data'] = $data['data'];
            return $this->convPhotoUrl($return);
        } else {
            return self::getErrorOut(ERRORCODE_UCENTER_NO_USER);
        }
    }

    /**
     * 通过UID查询用户个人资料
     * @param string $memberId 用户UID
     * @return array
     */
    public function getUserProfileByUid($arrInput)
    {
        $return = self::getStOut();
        $url = JAVA_API_GETUSERPROFILEBYUID;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'UID' => self::getParamMemberid($arrInput),
        ];
        $data = $this->http($url, $params);
        if ($data['ret'] == 0 && $data['sub'] == 0) {
            if (isset($data['data']['extUid'])) {
                unset($data['data']['extUid']);
            }
            $return['data'] = $data['data'];
            return $this->convPhotoUrl($return);
        } else {
            return self::getErrorOut(ERRORCODE_UCENTER_NO_USER);
        }
    }

    /**
     * 通过手机号查询用户openid的集合
     * @param string $mobileNo 手机号
     * @return array
     */
    public function getOpenidListByMobile($arrInput)
    {
        $return = self::getStOut();
        $url = JAVA_API_GETOPENIDLISTBYMOBILE;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'mobileNo' => self::getParam($arrInput, 'mobileNo'),
        ];
        $data = $this->http($url, $params);
        if ($data['ret'] == 0 && $data['sub'] == 0) {
            if (isset($data['data']['extUid'])) {
                unset($data['data']['extUid']);
            }
            $return['data'] = $data['data'];
            return $this->convPhotoUrl($return);
        } else {
            return self::getErrorOut(ERRORCODE_UCENTER_NO_DATA);
        }
    }

    /**
     * 通过任一id查询关系树
     * @param string $arrInput ['id'] 系统里除手机号之外的任一标示用户的id
     * @return array
     */
    public function getIdRelation($arrInput)
    {
        $url = JAVA_API_GETIDRELATION;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'id'           => self::getParam($arrInput, 'id'),
            'showMemberId' => true,
        ];
        $data = $this->http($url, $params);
        return $data;
    }

    /**
     * add by bbq 从commonCGI中拷贝
     * 获取用户所有的openID
     * @param $openId
     * @return array
     */
    public function getAllOpenIds($openId)
    {
        $returnIds = [];
        $url = JAVA_API_GETIDRELATION;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = ['id' => $openId];
        $response = $this->http($url, $params);
        if ($response['ret'] == 0 && $response['sub'] == 0) {
            $idList = isset($response['data']['idRelation']['idUnderBound']) ? $response['data']['idRelation']['idUnderBound'] : [];
            foreach ($idList as $account) {
                $returnIds[] = $account['id'];
                if ($account['idUnderBound']) {
                    foreach ($account['idUnderBound'] as $wxAccount) {
                        $returnIds[] = $wxAccount['id'];
                    }
                }
            }
        }
        if (empty($returnIds)) {
            $returnIds[] = $openId;
        }

        return $returnIds;

    }

    /**
     * 判断新老用户，是否是新用户
     * （老用户：在微票系统中发生过订单、选坐券以及集采、众筹、包场购买行为的用户为老用户，包含支付后又退款的用户）
     * @param $openId string 用户第三方账户
     * @param $idType string id来源渠道，目前为第三方平台的编号id
     * （0->对应所有第三方平台(与20区分);10->新浪微博;11->微信;12->QQ;13->手机号;20->UID;30->UnionID）
     * @return array
     */
    public function checkNewUser($arrInput)
    {
        $url = JAVA_API_CHECKNEWUSER;
        $channelId = isset($arrInput['channelId']) ? $arrInput['channelId'] : '';
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'id' => $arrInput['openId'],
            'idType' => self::getParam($arrInput, 'idType', self::convChannelId($channelId)),
            'tagList' => ['0'], //0代表电影票，1代表演出票
        ];
        $response = $this->http($url, $params);

        //添加isNew字段，如果是新用户则为1，老用户为0
        if (isset($response['data']['result']) && $response['ret'] == 0 && $response['sub'] == 0) {
            $tagVal = $response['data']['result'][0]['tagVal'];
            $response['data'] = [
                'isNew' => empty($tagVal) ? 1 : 0,
            ];
        }
        if (isset($response['X-Request-Id'])) {
            unset($response['X-Request-Id']);
        }
        return $response;
    }

    /**
     * 通过Java接口,获取用户手机号
     * @param   intval channelId            公众号缩写: 3微信电影票, 8 IOS, 9 安卓, 等
     * @param   intval salePlatformType     售卖平台, 一般情况下为2
     * @param   string openId               用户openId
     * @param   intval userId               用户userId
     * @param   intval appId                登录方式，认证体系APPID：0：通用，1为微信公众号，2为手机号...
     * @param   array {"data":{"mobileNo":"15652673019"},"ret":"0","sub":"0","msg":"SUCCESS"}
     */
    public function getMobile(array $arrInput = [])
    {
        //定义参数
        $arrSendParams = [];
        $arrSendParams['publicSignalShort'] = self::getParam($arrInput, 'channelId');
        $arrSendParams['salePlatformType'] = self::getParam($arrInput, 'salePlatformType');
        $arrSendParams['openId'] = self::getParam($arrInput, 'openId');
        $arrSendParams['userId'] = self::getParamMemberid($arrInput);
        $arrSendParams['appId'] = self::getParam($arrInput, 'appId');
        //调用接口
        $httpParams = [
            'arrData' => $arrSendParams,
            'sMethod' => 'POST',
        ];
        $arrReturn = $this->http(JAVA_API_GET_USER_MOBILE, $httpParams);
        return $arrReturn;
    }

    /**
     * 添加一条用户收货地址
     * @param array $arrInput
     * @param string userId 微票用户唯一标示，此参数必须为已存在的UID,(注意此id对应原演出票的passport_user_id)
     * @param int provinceId 省id
     * @param int cityId 城市id
     * @param int districtId 区id
     * @param string postcode 邮编
     * @param string receiveDeliveryPerson 收件人姓名
     * @param string receiveDeliveryMobile 收件人手机号
     * @param boolean isDefault 是否默认地址
     * @param boolean isVisible 是否可见
     * @param string deliveryAddress 详细街道地址
     * @return array
     */
    public function addPda($arrInput = [])
    {
        $url = JAVA_API_ADDPDA;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'UID' => self::getParamMemberid($arrInput),
            'province_id' => self::getParam($arrInput, 'provinceId'),
            'city_id' => self::getParam($arrInput, 'cityId'),
            'district_id' => self::getParam($arrInput, 'districtId'),
            'post_code' => self::getParam($arrInput, 'postcode'),
            'receive_delivery_person' => self::getParam($arrInput, 'receiveDeliveryPerson'),
            'receive_delivery_mobile' => self::getParam($arrInput, 'receiveDeliveryMobile'),
            'is_default' => self::getParam($arrInput, 'isDefault'),
            'is_visible' => self::getParam($arrInput, 'isVisible'),
            'delivery_address' => self::getParam($arrInput, 'deliveryAddress'),
        ];
        $data = $this->http($url, $params);
        return $data;
    }

    /**
     * 删除一条用户收货地址
     * @param array $arrInput
     * @param string userId 微票用户唯一标示，此参数必须为已存在的UID,(注意此id对应原演出票的passport_user_id)
     * @param string id 用户收货地址的id（数据库的主键）
     * @return array
     */
    public function removePda($arrInput = [])
    {
        $url = JAVA_API_REMOVEPDA;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'UID' => self::getParamMemberid($arrInput),
            'id' => self::getParam($arrInput, 'id'),
        ];
        $data = $this->http($url, $params);
        return $data;
    }

    /**
     * 更改用户收货地址
     * @param array $arrInput
     * @param string userId 微票用户唯一标示，此参数必须为已存在的UID,(注意此id对应原演出票的passport_user_id)
     * @param string id 用户收货地址的id（数据库的主键）
     * @param int provinceId 省id
     * @param int cityId 城市id
     * @param int districtId 区id
     * @param string postcode 邮编
     * @param string receiveDeliveryPerson 收件人姓名
     * @param string receiveDeliveryMobile 收件人手机号
     * @param boolean isDefault 是否默认地址
     * @param boolean isVisible 是否可见
     * @param string deliveryAddress 详细街道地址
     * @return array
     */
    public function updataPda($arrInput = [])
    {
        $url = JAVA_API_UPDATEPDA;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'UID' => self::getParamMemberid($arrInput),
            'id' => self::getParam($arrInput, 'id'),
            'province_id' => self::getParam($arrInput, 'provinceId'),
            'city_id' => self::getParam($arrInput, 'cityId'),
            'district_id' => self::getParam($arrInput, 'districtId'),
            'post_code' => self::getParam($arrInput, 'postcode'),
            'receive_delivery_person' => self::getParam($arrInput, 'receiveDeliveryPerson'),
            'receive_delivery_mobile' => self::getParam($arrInput, 'receiveDeliveryMobile'),
            'is_default' => self::getParam($arrInput, 'isDefault'),
            'is_visible' => self::getParam($arrInput, 'isVisible'),
            'delivery_address' => self::getParam($arrInput, 'deliveryAddress'),
        ];
        $data = $this->http($url, $params);
        return $data;
    }

    /**
     * 获取某一用户的收货地址列表
     * @param array $arrInput
     * @param string userId 微票用户唯一标示，此参数必须为已存在的UID,(注意此id对应原演出票的passport_user_id)
     * @param boolean isVisible 如果有此参数，则按照该字段的值返回对应的用户收货地址列表；否则，返回全量用户收货地址列表
     * @return array
     */
    public function getPdaByUid($arrInput = [])
    {
        $url = JAVA_API_GETPDABYUID;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'UID' => self::getParamMemberid($arrInput),
            'is_visible' => self::getParam($arrInput, 'isVisible'),
        ];
        $data = $this->http($url, $params);
        return $data;
    }

    /**
     * 获取某一用户的收货地址数量
     * @param array $arrInput
     * @param string userId 微票用户唯一标示，此参数必须为已存在的UID,(注意此id对应原演出票的passport_user_id)
     * @return array
     */
    public function getPdaCountByUid($arrInput = [])
    {
        $url = JAVA_API_GETPDACOUNTBYUID;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'UID' => self::getParamMemberid($arrInput),
        ];
        $data = $this->http($url, $params);
        return $data;
    }

    /**
     * 获取某一用户的默认收货地址
     * @param array $arrInput
     * @param string userId 微票用户唯一标示，此参数必须为已存在的UID,(注意此id对应原演出票的passport_user_id)
     * @return array
     */
    public function getDefaultPdaByUid($arrInput = [])
    {
        $url = JAVA_API_GETDEFAULTPDABYUID;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'UID' => self::getParamMemberid($arrInput),
        ];
        $data = $this->http($url, $params);
        return $data;
    }

    /**
     * 获取某一用户的某一条收货地址
     * @param array $arrInput
     * @param string userId 微票用户唯一标示，此参数必须为已存在的UID,(注意此id对应原演出票的passport_user_id)
     * @param string id 用户收货地址的id（数据库的主键）
     * @return array
     */
    public function getPdaById($arrInput = [])
    {
        $url = JAVA_API_GETPDABYID;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'UID' => self::getParamMemberid($arrInput),
            'id' => self::getParam($arrInput, 'id'),
        ];
        $data = $this->http($url, $params);
        return $data;
    }

    /**
     *获取用户的用户中心唯一标识
     * @param array $arrInput
     * @param string openId 可以传入openId和unionId用户中心返回那边唯一的标识
     * @return array
     */
    public function getUcid($arrInput = [])
    {
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = ['openId' => $arrInput['openId']];
        return $this->http(JAVA_API_GETUCIDBYOPENID, $params);
    }

    /**
     *拉取订单生成用户的观影轨迹
     * @param array $arrInput
     * @param string ucId 用户中心兑换完的用户的ucId
     * @return array
     */
    public function generateTrace($arrInput = [])
    {
        $ucId = $arrInput['ucId'];
        $url = "http://commoncgi.intra.wepiao.com/wepiao/app/trace";
        $response = $this->http($url, [
            'arrData' => ['openId' => $ucId, 'method' => 'service.passive'],
            'iTimeout' => 3,
        ]);
        return $response;
    }

    /**
     * 通过OpenID查询用户最早注册时间
     * 查询openid关联的uid下面所有节点的注册时间，返回的格式是unix时间戳
     * @return array
     */
    public function getRegisterTime($arrInput = [])
    {
        $url = JAVA_API_GETREGISTERTIME;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'OpenID' => self::getParam($arrInput, 'openId'),
        ];
        $data = $this->http($url, $params);
        return $data;
    }

    /**
     * 获取用户观影轨迹 [ 对APP 使用openId方法内会自动兑换ucId ]
     * @param String $channel 渠道号
     * @param String $page 页数
     * @param String $limit 每页条数
     * @return object
     */
    public function getTracePath($arrInput = [])
    {
        $page = self::getParam($arrInput, 'page', 1);
        $limit = self::getParam($arrInput, 'num', 10);
        $channelId = self::getParam($arrInput, 'channelId', 9);
        $token = self::getParam($arrInput, 'token', '');
        $ucId=self::getParam($arrInput, 'ucId', '');
        if(!empty($page)){
            $page=intval($page);
        }
        if(empty($ucId)){
            //换取ucId
            $arrRet = $this->getUcid($arrInput);
            if ($arrRet['ret'] == 0 and $arrRet['sub'] == 0) {
                $ucId = $arrRet['data']['uniqueId'];
            } else {
                //查询不到ucId时返回空对象
                $return = $this->getStOut();
                $return['data'] = new \stdClass();
                return $return;
            }
        }
        //判断是否用户生成过观影轨迹,如果没有生成过即第一次被动生成其余情况等待脚本刷新生成
        $updateTime = $this->model("Trace")->getUpdateTime($ucId);
        //因为测试强制生成一次观影轨迹如果观影轨迹更新时间大于7天则强制更新一次
        if ($updateTime == 0) {
            $this->generateTrace(['channelId' => $channelId, 'ucId' => $ucId]);
        } elseif ((time() - $updateTime) >= 604800) {
            $this->generateTrace(['channelId' => $channelId, 'ucId' => $ucId]);
        }
        //获取当前用户观影轨迹的总数
        $count = $this->model("Trace")->getUserTraceCount($ucId);
        $minPage = 1;
        $maxPage = ceil($count / $limit);
        //如果越界直接返回空而不返回最近的
        if ($page > $maxPage || $page < $minPage) {
            $return['data']['total'] = $count;
            $return['data']['totalCount'] = $count;
            $return['data']['curPage'] = $page;
            $return['data']['nextPage'] = ($page+1>$maxPage)?$maxPage:$page+1;
            $return['data']['totalPage'] = $maxPage;
            $return['data']['updated_at'] = $updateTime;
            $return['data']['updated_at'] = $updateTime;
            $return['data']['trace'] = [];
            return $return;
        }
        $limit = $limit < 1 ? 1 : $limit;
        $start = ($page - 1) * $limit;
        $end = $start + $limit - 1;
        $arrRet = $this->model("Trace")->getTrace($ucId, $start, $end);
        $return = $this->getStOut();
        $return['data']['total'] = $count;
        $return['data']['totalCount'] = $count;
        $return['data']['totalPage'] = $maxPage;
        $return['data']['curPage'] = $page;
        $return['data']['nextPage'] = ($page+1>$maxPage)?$maxPage:$page+1;
        $return['data']['updated_at'] = $updateTime;
        if ($arrRet) {
            //格式化影片信息
            $thisPageMovieId = [];
            foreach ($arrRet as $key => $value) {
                $traceItem = json_decode($value, true);
                $arrRet[$key] = $traceItem;
                $movieArr = $this->service("movie")->readMovieInfo([
                    'channelId' => $channelId,
                    'movieId' => $traceItem['movie_id'],
                    'cityId' => 10
                ]);
                $movie = $movieArr['data'];
                //处理影片信息获取不到等异常情况则不显示这条轨迹
                if (empty($movie)) {
                    unset($arrRet[$key]);
                } else {
                    $arrRet[$key]['name'] = $movie['name'];
                    $arrRet[$key]['id'] = $movie['id'];
                    $arrRet[$key]['en_name'] = $movie['en_name'];
                    $arrRet[$key]['director'] = $movie['director'];
                    $arrRet[$key]['actor'] = $movie['actor'];
                    $arrRet[$key]['date'] = strtotime($movie['date']);
                    $arrRet[$key]['date_stamp'] = strtotime($movie['date']);
                    $arrRet[$key]['date_des'] = $movie['date_des'];
                    $arrRet[$key]['country'] = $movie['country'];
                    $arrRet[$key]['remark'] = $movie['remark'];
                    $arrRet[$key]['tags'] = $movie['tags'];
                    $arrRet[$key]['longs'] = $movie['longs'];
                    $arrRet[$key]['score'] = $movie['score'];
                    $arrRet[$key]['initscore'] = $movie['score'];
                    $arrRet[$key]['scoreCount'] = $movie['scoreCount'];
                    $arrRet[$key]['wantcount'] = isset($movie['wantCount']) ? $movie['wantCount'] : 0;
                    $arrRet[$key]['seencount'] = isset($movie['seenCount']) ? $movie['seenCount'] : 0;
                    $arrRet[$key]['poster_url'] = $movie['poster_url'];
                    $arrRet[$key]['poster_url_size3'] = $movie['poster_url_size3'];
                    $arrRet[$key]['poster_url_size21'] = $movie['poster_url_size21'];
                    $thisPageMovieId[] = $movie['id'];
                }
                //处理转换评论和订单相关记录如果为空则转成空对象
                if (isset($arrRet[$key]['order']) AND empty($arrRet[$key]['order'])) {
                    $arrRet[$key]['order'] = new \stdClass();
                }
                //统一处理观影轨迹评论
                $arrRet[$key]['commentAndScore'] = [];
                $arrRet[$key]['commentAndScore']['movie_id'] = $movie['id'];
                $arrRet[$key]['commentAndScore']['comment'] = new \stdClass();
                $arrRet[$key]['commentAndScore']['score'] = new \stdClass();
            }

            if (($ucId!='' || $token!='') && !empty($thisPageMovieId)) {
                $ids = implode(",", array_unique($thisPageMovieId));

                $params = [
                    'arrData' => [
                        'channelId' => $channelId,
                        'token' => $token,
                        'ucid'=>$ucId,
                        'movieIds' => $ids,
                    ],
                    'iTimeout' => 1,
                ];
                $url = COMMENT_CENTER_URL . "/v1/movies/multi-comment-score";
                $response = $this->http($url, $params);
                if ($response['ret'] == 0 && $response['sub'] == 0) {
                    foreach ($arrRet as $key => $value) {
                        if (!empty($response['data'][$value['id']]['comment'])) {
                            $arrRet[$key]['commentAndScore']['comment'] = $response['data'][$value['id']]['comment'];
                        }
                        if (!empty($response['data'][$value['id']]['score'])) {
                            $arrRet[$key]['commentAndScore']['score'] = $response['data'][$value['id']]['score'];
                        }
                    }
                }
            }

            $arrRet = array_values($arrRet);
            $return['data']['trace'] = $arrRet;
        } else {
            $return['data']['trace'] = [];
        }
        return $return;
    }


    /**
     * 删除用户订单观影轨迹
     * @param String $channel 渠道号
     * @param String $page 页数
     * @param String $limit 每页条数
     * @return object
     */
    public function deleteTracePath($arrInput = [])
    {
        $return = $this->getStOut();
        //换取ucId
        $arrRet = $this->getUcid($arrInput);
        if ($arrRet['ret'] == 0 and $arrRet['sub'] == 0) {
            $arrInput['ucId'] = $arrRet['data']['uniqueId'];
        }
        if(isset($arrInput['ucId'])){
            $res=$this->model("trace")->deleteTracePath($arrInput);
            if(!$res){
                $return = self::getErrorOut(ERRORCODE_DELETE_USER_TRACE_PATH);
            }
        }
        else{
            $return = self::getErrorOut(ERRORCODE_UCENTER_GET_UCID_ERROR);
        }
        return $return;
    }

    /**
     * 获取已观影轨迹中已购买影片的Id
     * @param String $channel 渠道号
     * @param String $ucId 用户唯一编号 [提前用openId兑换]
     */
    public function getTraceMovies($arrInput)
    {
        $channelId = self::getParam($arrInput, 'channelId', 9);
        $return = $this->getStOut();
        $ucId = self::getParam($arrInput, 'ucId');
        //判断是否用户生成过观影轨迹,如果没有生成过即第一次被动生成其余情况等待刷新生成
        $updateTime = $this->model("Trace")->getUpdateTime($ucId);
        if ($updateTime == 0) {
            $this->generateTrace(['channelId' => $channelId, 'ucId' => $ucId]);
        }
        $strMovieIds = $this->model("Trace")->getTraceMovies($ucId);
        if ($strMovieIds) {
            $return['data'] = json_decode($strMovieIds, true);
        } else {
            $return['data'] = [];
        }
        return $return;
    }

    /*
     * 根据手机号判断是否是新老用户
     */
    public function checkPhone($arrInput = [])
    {
        $phone = $arrInput['phone'];
        $iChannelId = $arrInput['channelId'];
        return $this->model('user')->checkPhone($phone, $iChannelId);
    }

    public function checkBonusNew($arrInput = [])
    {
        $phone = !empty($arrInput['phone']) ? $arrInput['phone'] : '';
        $iChannelId = $arrInput['channelId'];
        $openId = $arrInput['openId'];


        $tag = $this->service('tag')->getTag([
            'openId' => $openId,
            'channelId' => $iChannelId,
            'tags' => 'old_user,tmp_old_user'
        ]);
        if ($tag['ret'] == 0 & (!empty($tag['data']['old_user']) || !empty($tag['data']['tmp_old_user']))) {
            $isNew = 0;
        } else {
            $isNew = 1;
        }

        if (!empty($phone) && ($isNew == 1)) {
            $flag = $this->checkPhone(['phone' => $phone, 'channelId' => $iChannelId]);
            if ($flag) {
                $isNew = 0;
            } else {
                $isNew = 1;
            }
        }

        $return = self::getStOut();
        $return['data']['isNew'] = $isNew;
        return $return;
    }

    /**
     * 检查手机号和设备号码是否一致
     * @param string mobileNo 手机号
     * @return array
     */
    public function verifyMobileDevice($arrInput)
    {
        $url = JAVA_API_MOBILENODEVICE;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'mobileNo' => self::getParam($arrInput, 'mobileNo'),
        ];
        $data = $this->http($url, $params);
        return $data;
    }

    /**
     * 给手机添加一个可信设备号
     * @param string mobileNo 手机号
     * @return array
     */
    public function addMobileDevice($arrInput)
    {
        $url = JAVA_API_MOBILENOADDDEVICE;
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = [
            'mobileNo' => self::getParam($arrInput, 'mobileNo'),
        ];
        $data = $this->http($url, $params);
        return $data;
    }


    /**
     * @param array $arrInput
     * @return array
     * openId,channelId
     * 查询用户是否关注了Movie公众号。如果关注了返回头像昵称，如果没有关注返回ret=-1
     */
    public function checkUserIsFolloMovie($arrInput = [])
    {
        $return = self::getStOut();
        $strOpenId = $arrInput['openId'];
        $strAccessToken = $this->service('wechat')->getAccessToken();
        $result = $this->service('wechat')->getUserInfo(['openid' => $strOpenId, 'access_token' => $strAccessToken]);
        if (isset($result['subscribe']) && $result['subscribe'] == 1) {
            $return['data']['nickname'] = $result['nickname'];
            $return['data']['headimgurl'] = $result['headimgurl'];
        } else {
            $return['ret'] = $return['sub'] = -1;
            $return['msg'] = 'get userinfo error!';
        }
        return $return;
    }

#####################################################
#########   重构版，新加用户中心接口 START   ##########
#####################################################
#这些接口调用上面的原始接口，因此和上面的接口是平行关系。
#上面现有的接口由于app已经在使用，修改时需要慎重

    /**
     * 判断手机号状态（1 未注册、2 已使用手机号注册、3 已注册但无密码）
     * 注：2与3都表示已注册，但想判断是否绑定过需要看platforms有无对应平台
     * @param string $mobileNo 手机号
     * @param string $otherId 第三方平台编号，传入时返回是否绑定过binded
     * @return array
     */
    public function checkMobileStatus($arrInput = [])
    {
        $arrRes = self::getStOut();
        $bind = 0; //是否绑定过
        $response = $this->getUserinfoByMobile($arrInput);
        if ($response['ret'] == 0 && isset($response['data']['UID'])) {
            $uid = $response['data']['UID'];
            $hasPasswd = $response['data']['hasCredential'];

            //根据手机号请求openid集合
            $response = $this->getOpenidListByMobile($arrInput);
            if ($response['ret'] == 0 && isset($response['data']['openIdList'])) {
                $openidList = $response['data']['openIdList'];
                $otherids = [];
                foreach ($openidList as $idlists) {
                    $otherids[] = $idlists['otherId'];
                }
                if (!empty($arrInput['otherId'])) {
                    $bind = in_array($arrInput['otherId'], $otherids) ? 1 : 0;
                }
            } else {
                return self::getErrorOut(ERRORCODE_UCENTER_NO_DATA);
            }
            //2 该手机号有通过手机号注册过, 3 该手机已与第三方帐号绑定（且没有密码）。最后都返回openid集合
            $arrRes['data'] = [
                'status' => ($hasPasswd == true) ? '2' : '3',
                'UID' => $uid,
                'memberId' => $uid,
                'platforms' => $otherids,
                'binded' => $bind,
            ];
            return $arrRes;
        } else {
            //1 该手机号未注册
            $arrRes['data'] = ['status' => '1', 'binded' => $bind];
            return $arrRes;
        }
    }

    /**
     * 判断openid状态（1 未绑定、2 已绑定手机号、3 已绑定但无密码）
     * @param string $OpenID 第三方帐号
     * @return array
     */
    public function checkOpenidStatus($arrInput = [])
    {
        $arrRes = self::getStOut();
        $bind = 0; //是否绑定过
        $response = $this->getUserinfoByOpenid($arrInput);
        if ($response['ret'] == 0 && !empty($response['data']['UID'])) {
            $uid = $response['data']['UID'];
            $hasPasswd = $response['data']['hasCredential'];

            //根据手机号请求openid集合
            $arrInput['mobileNo'] = $response['data']['mobileNo']; //此处手机号放入入参
            $response = $this->getOpenidListByMobile($arrInput);
            if ($response['ret'] == 0 && isset($response['data']['openIdList'])) {
                $openidList = $response['data']['openIdList'];
                $otherids = [];
                foreach ($openidList as $idlists) {
                    $otherids[] = $idlists['otherId'];
                }
                if (!empty($arrInput['otherId'])) {
                    $bind = in_array($arrInput['otherId'], $otherids) ? 1 : 0;
                }
            } else {
                return self::getErrorOut(ERRORCODE_UCENTER_NO_DATA);
            }
            //2 该openid已与手机号绑定, 3 该openid已与手机号绑定但无密码。最后都返回openid集合
            $arrRes['data'] = [
                'status' => ($hasPasswd == true) ? '2' : '3',
                'UID' => $uid,
                'memberId' => $uid,
                'platforms' => $otherids,
                'binded' => $bind,
                'mobileNo' => $arrInput['mobileNo'],
            ];
            return $arrRes;
        } else {
            //1 该手机号未注册
            $arrRes['data'] = ['status' => '1', 'binded' => $bind, 'mobileNo' => ''];
            return $arrRes;
        }
    }

    /**
     * 根据手机号、密码进行注册 用户信息
     * @param string $mobileNo 手机号
     * @param string $password 密码
     * @param string $nickname 昵称，选传，从cookie中解出
     * @param string $headimgurl 头像，选传，从cookie中解出
     * @return array
     */
    public function MobileRegister($arrInput = [])
    {
        //检查手机号是否已经存在
        $resMobile = [
            'mobileNo' => $arrInput['mobileNo'],
        ];
        $resUserinfo = $this->getUserinfoByMobile($resMobile);
        if (empty($resUserinfo['data'])) {
            $data = [
                'mobileNo' => $arrInput['mobileNo'],
                'password' => $arrInput['password'],
                'platForm' => self::getParam($arrInput, 'platForm', 1), //应用平台（1：电影票，2：演出票）
                'nickname' => self::getParam($arrInput, 'nickname', '手机用户_' . substr($arrInput['mobileNo'], -4, 4)),
                'avatar' => self::getParam($arrInput, 'headimgurl'), //头像
                'channelId' => self::getParam($arrInput, 'channelId'),
            ];
            $response = $this->register($data);
            if ($response['ret'] == 0 && $response['sub'] == 0) {
                //根据手机号查询用户信息并返回
                $data = [
                    'mobileNo' => $arrInput['mobileNo'],
                ];
                $resUserinfo = $this->getUserinfoByMobile($data);
                if ($resUserinfo['ret'] == 0) {
                    $resUserinfo['data']['openId'] = $response['data']['openId'];
                    return $resUserinfo;
                } else {
                    return self::getErrorOut(ERRORCODE_UCENTER_REGISTER_SUCCESS_NO_INFO);
                }
            } else {
                if ($response['sub'] == -20002) {
                    return self::getErrorOut(ERRORCODE_UCENTER_MOBILE_EXIST);
                } else {
                    return self::getErrorOut(ERRORCODE_UCENTER_REGISTER_FAIL);
                }
            }
        } else {
            return self::getErrorOut(ERRORCODE_UCENTER_MOBILE_EXIST);
        }
    }

    /**
     * 根据第三方openId 进行注册 用户信息
     * 注：并不对外使用。目前仅在评论App/Api/User/getUserInfo中使用
     * @param string $openId 第三方UID
     * @param int $otherId 第三方平台的编号
     * @param string $unionId 微信用户唯一编号
     * @param string $nickName 昵称
     * @return array
     */
    public function OpenRegister($arrInput = [])
    {
        //请求第三方，验证openid正确性
        #$checkOpenid = [
        #    'token' => $arrInput['accessToken'],
        #    'otherId' => $arrInput['otherId'],
        #    'openId' => $arrInput['openId'],
        #    'oauth_consumer_key' => self::getParam($arrInput, 'oauthConsumerKey'),
        #];
        #if (!self::isRealUser($checkOpenid)) {
        #    $arrRes = array('status' => false, 'ret' => -11, 'sub' => -2028);//第三方凭证验证失败或过期
        #    return $this->userReturnData($arrRes['ret'], $arrRes['sub'], '');
        #}

        $data = [
            'openId' => $arrInput['openId'],
            'otherId' => $arrInput['otherId'],
            'platForm' => self::getParam($arrInput, 'platForm', '1'),    //1电影票，2演出。默认1
            'nickname' => self::getParam($arrInput, 'nickName'),
            'unionId' => self::getParam($arrInput, 'unionId'),
            'avatar' => self::getParam($arrInput, 'photo'),
            #'mobileNo' => self::getParam($arrInput,'mobileNo'),
            'sex' => self::getParam($arrInput, 'sex'),
            'channelId' => self::getParam($arrInput, 'channelId'),
            'subOtherId' => self::getParam($arrInput, 'subOtherId', 0), //SubotherID
        ];
        $response = $this->registerByOpenid($data);
        if ($response['ret'] == 0 && $response['sub'] == 0) {
            $data = [
                'openId' => $arrInput['openId'],
            ];
            $resUserinfo = $this->getUserinfoByOpenid($data);
            if ($resUserinfo['ret'] == 0) {
                $resUserinfo['data']['hasMobile'] = (empty($resUserinfo['data']['mobileNo'])) ? false : true; //判断有无手机号
                #//$userinfo['hasCredential'] = (empty($userinfo['hasCredential'])) ? false : true; //判断有无密码
                return $resUserinfo;
            } else {
                return self::getErrorOut(ERRORCODE_UCENTER_REGISTER_SUCCESS_NO_INFO);
            }
        } else {
            return self::getErrorOut(ERRORCODE_UCENTER_REGISTER_FAIL);
        }
    }

    /**
     * 手机号密码登陆接口
     * @param string $password 密码
     * @param string $mobileNo 手机号
     * @param string $otherId 可选，第三方平台编号，登陆并绑定时传入
     * @param string $openId 可选，第三方帐号，登陆并绑定时需要，从cookie中获取，不需传入
     * @param string $unionId 可选，微信唯一ID，otherId为11时需要，从cookie中获取，不需传入
     * @return string array
     */
    public function LoginAndBind($arrInput = [])
    {
        $return = self::getStOut();
        $data = [
            'mobileNo' => $arrInput['mobileNo'],
            'password' => $arrInput['password'],
        ];
        $response = $this->login($data);
        if ($response['ret'] == 0 && $response['sub'] == 0) {
            //如果确认注册成功并且传入了otherId，则认为是“登录并自动绑定第三方”。
            if (!empty($arrInput['otherId'])) {
                //全新的openid必须先到openregister记录一次，否则绑定时会报错数据不存在
                $dataOpRegister = [
                    'openId' => $arrInput['openId'],
                    'otherId' => $arrInput['otherId'],
                    'platForm' => self::getParam($arrInput, 'platForm', '1'),    //1电影票，2演出。默认1
                    'nickname' => self::getParam($arrInput, 'nickname'), //昵称，应从cookie中取
                    'avatar' => self::getParam($arrInput, 'headimgurl'), //头像，应从cookie中取
                    'unionId' => self::getParam($arrInput, 'unionid'), //unionid，otherId为11即微信时应传入
                    'channelId' => self::getParam($arrInput, 'channelId'),
                ];
                $resOpRegister = $this->registerByOpenid($dataOpRegister);
                if (!isset($resOpRegister['ret']) || $resOpRegister['ret'] != 0) {
                    return $this->getErrorOut(ERRORCODE_UCENTER_NO_THIRD_DATA);
                }

                $dataBind = [
                    'mobileNo' => $arrInput['mobileNo'],
                    'openId' => $arrInput['openId'],
                    'otherId' => $arrInput['otherId'],
                    'unionId' => self::getParam($arrInput, 'unionid'),
                    'channelId' => self::getParam($arrInput, 'channelId')
                ];
                $resBind = $this->bindMobile($dataBind);
                if (!($resBind['ret'] == 0 && $resBind['sub'] == 0)) {
                    switch ($resBind['sub']) {
                        case -20009:
                            return $this->getErrorOut(ERRORCODE_UCENTER_MOBILE_BINDED);
                        case -20008:
                            return $this->getErrorOut(ERRORCODE_UCENTER_NO_MOBILE);
                        case -20004:
                            return $this->getErrorOut(ERRORCODE_UCENTER_MOBILE_USED);
                        case -20003:
                            return $this->getErrorOut(ERRORCODE_UCENTER_MOBILE_SELFBINDED);
                        default :
                            return $this->getErrorOut(ERRORCODE_UCENTER_BIND_FAIL);
                    }
                }
            }
            $return['data'] = $response['data'];
            return $return;
        } else {
            if ($response['sub'] == -20008) {
                return $this->getErrorOut(ERRORCODE_UCENTER_NO_MOBILE);
            } else {
                return $this->getErrorOut(ERRORCODE_UCENTER_LOGIN_FAIL);
            }
        }
    }

    /**
     * 绑定手机号
     * @param string $mobileNo 绑定手机号
     * @param string $code 手机验证码
     * @param string $openId 第三方唯一ID，从cookie中解出
     * @param int $otherId 第三方平台的编号id 10：新浪微博，11：微信，12:QQ
     * @param string $unionId 微信授权成功唯一ID
     * @return array
     */
    public function Bind($arrInput = [])
    {
        //先根据手机号校验短信
        $arrSmsRet = $this->service('Sms')->verifySmsCode([
            'phone_number' => $arrInput['mobileNo'],
            'code' => $arrInput['code'],
            'channelId' => $arrInput['channelId'],
        ]);
        if (!empty($arrSmsRet['errorcode'])) {
            return $this->getErrorOut(ERRORCODE_SMS_CODE_WRONG);
        }
        $data = [
            'mobileNo' => $arrInput['mobileNo'],
            'openId' => $arrInput['openId'],
            'otherId' => $arrInput['otherId'],
            'unionId' => self::getParam($arrInput, 'unionId'),
            'channelId' => self::getParam($arrInput, 'channelId'),
        ];
        $this->registerByOpenid($data);
        $response = $this->bindMobile($data);
        if (($response['ret'] == 0 && $response['sub'] == 0)) {
            //请求用户信息并返回
            $userinfo = $this->getUserinfoByOpenid($arrInput);
            if ($userinfo['ret'] == 0) {
                return $userinfo;
            } else {
                return $this->getErrorOut(ERRORCODE_UCENTER_NO_DATA);
            }
        } else {
            switch ($response['sub']) {
                case -20009:
                    return $this->getErrorOut(ERRORCODE_UCENTER_MOBILE_BINDED);
                case -20008:
                    return $this->getErrorOut(ERRORCODE_UCENTER_NO_MOBILE);
                case -20004:
                    return $this->getErrorOut(ERRORCODE_UCENTER_MOBILE_USED);
                case -20003:
                    return $this->getErrorOut(ERRORCODE_UCENTER_MOBILE_SELFBINDED);
                default :
                    return $this->getErrorOut(ERRORCODE_UCENTER_BIND_FAIL);
            }
        }
    }

    /**
     * 绑定手机号领取红包
     * @param array $arrInput
     * @return array
     */
    public function getBindBouns($arrInput = []){
        $params = [];
        $params['suitId'] = self::getParam($arrInput, 'suitId');;//绑定红包的资源Id
        $params['openId'] = self::getParam($arrInput, 'openId');
        $params['userId'] = self::getParam($arrInput, 'userId');
        $params['channelId'] = self::getParam($arrInput, 'channelId');
        $params['phone'] = self::getParam($arrInput, 'phone');
        $params['clientIp'] = \sdkService\helper\Net::getRemoteIp();
        $params['mobile_chk_flg'] = 1;
        $params['encrypt_flg'] = 0;
        $params['deviceId'] = self::getParam($arrInput, 'deviceId');
        $params['imei'] = self::getParam($arrInput, 'imei');
        $params['appver']=self::getParam($arrInput, 'appver');
        $params['value'] = self::getParam($arrInput, 'value');
        $params['subChannelId'] = self::getParam($arrInput, 'subChannelId');
        //查询红包活动信息
        $ret=$this->Service("Bonus")->suitBonusInfo($params['suitId']);
        $time=time();
        if($ret['ret']==0){
            if($ret['data']['lefCnt']<=0){
                //剩余库存不足
                $arrRes=array('status' => -1,'msg' => '红包库存不足');
            }
            elseif($ret['data']['stTm']>=$time){
                //活动还未开始
                $arrRes=array( 'status' => -2,'msg' => '活动还未开始',);
            }
            elseif($ret['data']['endTm']>=$time){
                //活动已经结束
                $arrRes=array('status' => -3,'msg' => '活动已结束',);
            }
            else{
                //领取红包
                $ret=$this->service('Bonus')->getSuitBonus($params);
                if($ret['ret']!=0){
                    $arrRes=array('status' => -4,'msg' => '领取失败',);
                }
                else{
                    $arrRes=array('status' => 0,'msg' => '领取成功',);
                }
            }
        }
        else{
            //查询数据失败
            $arrRes=array('status' => -5,'msg' => '网络错误',);
        }
        return $arrRes;
    }

    /**
     * 手机号修改
     * @param string $memberId 唯一标识，支持传入openId,uid,memberId,unionId等参数
     * @param string $mobileNoOld 原手机号
     * @param string $mobileNo 新手机
     * @param string $code 新手机的短信验证码
     */
    public function EditMobile($arrInput = [])
    {
        $arrRes = $this->getStOut();
        //先根据手机号校验短信，因页面关系，只验证新手机号
        $arrSmsRet = $this->service('Sms')->verifySmsCode([
            'phone_number' => $arrInput['mobileNo'],
            'code' => $arrInput['code'],
            'channelId' => $arrInput['channelId'],
        ]);
        if (!empty($arrSmsRet['errorcode'])) {
            return $this->getErrorOut(ERRORCODE_SMS_CODE_WRONG);
        }
        //取id值，从uid、memberId、userId、id、openId、unionId中依次取。
        $arrIdRelat['id'] = self::getParamMemberid($arrInput);
        if (empty($arrIdRelat['id'])) {
            $arrIdRelat['id'] = !empty($arrInput['id']) ? $arrInput['id'] :
                (!empty($arrInput['openId']) ? $arrInput['openId'] :
                    (!empty($arrInput['unionId']) ? $arrInput['unionId'] : ''));
        }
        $resIdRelat = $this->getIdRelation($arrIdRelat);
        if (!empty($resIdRelat['data']['idRelation']['id'])) {
            $relationId = $resIdRelat['data']['idRelation']['id'];
        } else {
            return self::getErrorOut(ERRORCODE_UCENTER_NO_USER);
        }
        $data = [
            'userId' => $relationId,
            'mobileNoOld' => $arrInput['mobileNoOld'],
            'mobileNo' => $arrInput['mobileNo'],
        ];
        $response = $this->updateMobile($data);
        if ($response['ret'] == 0 && $response['sub'] == 0) {
            return $arrRes;
        } else {
            switch ($response['sub']) {
                case -20003:
                    return $this->getErrorOut(ERRORCODE_UCENTER_MOBILE_SELFBINDED);
                case -20004:
                    return $this->getErrorOut(ERRORCODE_UCENTER_MOBILE_USED);
                case -20010:
                    return $this->getErrorOut(ERRORCODE_UCENTER_OLDMOBILE_WRONG);
                default :
                    return $this->getErrorOut(ERRORCODE_UCENTER_MOBILE_CHANGE_FAIL);
            }
        }
    }

    /**
     * 修改密码
     * @param string $memberId 用户唯一标识，支持传入openId,uid,memberId,unionId等参数
     * @param string $passwordOld 原密码
     * @param string $password 新密码
     * @param string $mobileNo 手机号，防黄牛加的验证
     * @return array
     */
    public function EditPassword($arrInput = [])
    {
        $arrRes = $this->getStOut();
        //入参的原密码为空时报错。这一点对于后面java端来说是判断此次请求到底是修改密码还是重置密码的依据。
        //因为java端用的是同一个接口，入参有passwordOld时才进行旧密码验证，没有passwordOld就直接修改。
        if (empty($arrInput['passwordOld'])) {
            return $this->getErrorOut(ERRORCODE_UCENTER_OLDPASSWD_ERROR);
        }
        //取id值，从uid、memberId、userId、id、openId、unionId中依次取。
        $arrIdRelat['id'] = self::getParamMemberid($arrInput);
        if (empty($arrIdRelat['id'])) {
            $arrIdRelat['id'] = !empty($arrInput['id']) ? $arrInput['id'] :
                (!empty($arrInput['openId']) ? $arrInput['openId'] :
                    (!empty($arrInput['unionId']) ? $arrInput['unionId'] : ''));
        }
        $resIdRelat = $this->getIdRelation($arrIdRelat);
        if (!empty($resIdRelat['data']['idRelation']['id'])) {
            $relationId = $resIdRelat['data']['idRelation']['id'];
        } else {
            return self::getErrorOut(ERRORCODE_UCENTER_NO_USER);
        }
        $data = [
            'userId' => $relationId,
            'oldpassword' => $arrInput['passwordOld'],
            'newpassword' => $arrInput['password'],
            'mobileNo' => self::getParam($arrInput, 'mobileNo'),
            'opType' => '0',   //新增，标记为修改密码还是重置密码还是设置密码，修改0，重置1，设置2
        ];
        $response = $this->updatePassword($data);
        if ($response['ret'] == 0 && $response['sub'] == 0) {
            $arrRes['data'] = 1;
            return $arrRes;
        } else {
            switch ($response['sub']) {
                case -20012:
                    return $this->getErrorOut(ERRORCODE_UCENTER_OLDPASSWD_ERROR);
                default :
                    return $this->getErrorOut(ERRORCODE_UCENTER_PASSWD_CHANGE_FAIL);
            }
        }
    }

    /**
     * 密码重置
     * @param string $memberId 用户唯一标识，支持传入openId,uid,memberId,unionId等参数
     * @param string $mobileNo 手机号
     * @param string $code 手机验证码
     * @param string $password 新密码
     * @return array;
     */
    public function EditReset($arrInput = [])
    {
        //先根据手机号校验短信
        $arrSmsRet = $this->service('Sms')->verifySmsCode([
            'phone_number' => $arrInput['mobileNo'],
            'code' => $arrInput['code'],
            'channelId' => $arrInput['channelId'],
        ]);
        if (!empty($arrSmsRet['errorcode'])) {
            return $this->getErrorOut(ERRORCODE_SMS_CODE_WRONG);
        }

        $arrRes = $this->getStOut();
        //取id值，从uid、memberId、userId、id、openId、unionId中依次取。
        $arrIdRelat['id'] = self::getParamMemberid($arrInput);
        if (empty($arrIdRelat['id'])) {
            $arrIdRelat['id'] = !empty($arrInput['id']) ? $arrInput['id'] :
                (!empty($arrInput['openId']) ? $arrInput['openId'] :
                    (!empty($arrInput['unionId']) ? $arrInput['unionId'] : ''));
        }
        $resIdRelat = $this->getIdRelation($arrIdRelat);
        if (!empty($resIdRelat['data']['idRelation']['id'])) {
            $relationId = $resIdRelat['data']['idRelation']['id'];
        } else {
            return self::getErrorOut(ERRORCODE_UCENTER_NO_USER);
        }
        $data = [
            'userId' => $relationId,
            'newpassword' => $arrInput['password'],
            'mobileNo' => $arrInput['mobileNo'],
            'opType' => '1',   //新增，标记为修改密码还是重置密码还是设置密码，修改0，重置1，设置2
        ];
        $response = $this->updatePassword($data);
        if ($response['ret'] == 0 && $response['sub'] == 0) {
            $arrRes['data'] = 1;
            return $arrRes;
        } else {
            return $this->getErrorOut(ERRORCODE_UCENTER_RESET_PASSWD_FAIL);
        }
    }

    /**
     * 设置密码（仅对无密码用户有用）
     * @param string $memberId 用户唯一标识，支持传入openId,uid,memberId,unionId等参数
     * @param string $mobileNo 手机号
     * @param string $password 密码
     * @return array
     */
    public function EditSetPassword($arrInput = [])
    {
        $arrRes = $this->getStOut();
        //取id值，从uid、memberId、userId、id、openId、unionId中依次取。
        $arrIdRelat['id'] = self::getParamMemberid($arrInput);
        if (empty($arrIdRelat['id'])) {
            $arrIdRelat['id'] = !empty($arrInput['id']) ? $arrInput['id'] :
                (!empty($arrInput['openId']) ? $arrInput['openId'] :
                    (!empty($arrInput['unionId']) ? $arrInput['unionId'] : ''));
        }
        $resIdRelat = $this->getIdRelation($arrIdRelat);
        if (!empty($resIdRelat['data']['idRelation']['id'])) {
            $relationId = $resIdRelat['data']['idRelation']['id'];
        } else {
            return self::getErrorOut(ERRORCODE_UCENTER_NO_USER);
        }
        //获取用户信息，根据hasCredential字段判断现密码是否为空。
        $data = [
            'userId' => $relationId,
        ];
        $response = $this->getUserinfoByUid($data);
        if ($response['ret'] == 0 && $response['sub'] == 0) {
            //hasCredential为true的不通过，直接返回
            if (!empty($response['data']['hasCredential'])) {
                return $this->getErrorOut(ERRORCODE_UCENTER_PASSWD_EXIST);
            }
            $data = [
                'userId' => $relationId,
                'newpassword' => $arrInput['password'],
                'mobileNo' => $arrInput['mobileNo'],
                'opType' => '2',   //新增，标记为修改密码还是重置密码还是设置密码，修改0，重置1，设置2
            ];
            $response = $this->updatePassword($data);
            if ($response['ret'] == 0 && $response['sub'] == 0) {
                $arrRes['data'] = 1;
                return $arrRes;
            }
        }
        return $this->getErrorOut(ERRORCODE_UCENTER_SET_PASSWD_FAIL);
    }

    /**
     * 转换图片地址
     * @param array $data 兼容与ret同级的数组data和最终返回的完整数组
     * @return array
     */
    private function convPhotoUrl($arrData)
    {
        $dataType = (isset($arrData['ret']) && isset($arrData['data'])) ? 1 : 0;
        $data = $dataType ? $arrData['data'] : $arrData;
        //对输出结果中的头像字段（photo,photoUrl）进行处理，没有域名的添加域名，没有头像的使用默认地址
        $PHOTO_DEFAULT = CDN_APPNFS . '/dataImage/photo.png';
        $AVATAR_NAMES = ['photo', 'photoUrl'];
        foreach ($AVATAR_NAMES as $k) {
            if (!empty($data[$k])) {
                if (!stristr($data[$k], 'http')) {
                    $data[$k] = CDN_APPNFS . $data[$k];
                }
            } elseif (isset($data[$k]) && is_array($data)) {
                $data[$k] = $PHOTO_DEFAULT;
            }
        }
        if ($dataType) {
            $arrData['data'] = $data;
            return $arrData;
        } else {
            return $data;
        }
    }

    /**
     * 取memberId入参时兼容memberId与uid及userId
     * @param $arrInput
     * @return string $memberId
     */
    public static function getParamMemberid($arrInput)
    {
        $memberId = !empty($arrInput['memberId']) ? $arrInput['memberId'] : self::getParam($arrInput, 'uid');
        if (empty($memberId)) {
            $memberId = !empty($arrInput['userId']) ? $arrInput['userId'] : '';
        }
        return $memberId;
    }

    /**
     * 从cookie中解出openid，目前未使用
     * @param array $otherId 第三方平台号
     * @return string
     */
    public function getOpenIdFromCookie($arrInput = [])
    {
        $otherId = !empty($arrInput['otherId']) ? $arrInput['otherId'] : '';
        if (empty($otherId)) {
            //若没传入，则从全局channelId中取，并进行转换
            switch (\wepiao::$channelId) {
                case 3:
                    $otherId = 11;
                    break;
                case 28:
                    $otherId = 12;
                    break;
            }
        }
        switch ($otherId) {
            case '11': //微信
                $openidData = $this->service('WechatLogin')->getOpenIdFromCookie();
                $openid = self::getParam($openidData['data'], 'openid');
                #$unionid = self::getParam($openidData['data'], 'unionid');
                #if (empty($unionid)) {
                #    return $this->getErrorOut(ERRORCODE_UCENTER_LACK_USERDATA);
                #}
                break;
            case '12': //手Q
                $openidData = $this->service('Mqq')->getOpenIdFromCookie();
                $openid = self::getParam($openidData['data'], 'openid');
                break;
            default:
                $openid = '';
        }
        return $openid;
    }

    /**
     * 查询用户个人资料，支持多种查询id
     * @param string $openId openId
     * @param string $unionId unionId
     * @param string $memberId memberId
     * @param string $uid uid
     * @return array
     */
    public function getUserProfile($arrInput)
    {
        $return = self::getStOut();
        if(isset($arrInput['openId']) || isset($arrInput['unionId'])){
            $openId=(isset($arrInput['openId']) && !empty($arrInput['openId']))?$arrInput['openId']:$arrInput['unionId'];
            $url = JAVA_API_GETUSERPROFILEBYOPENID;
            $params['sMethod'] = 'post';
            $params['sendType'] = 'json';
            $params['arrData'] = [
                'openId' => $openId,
            ];
        }
        else{
            //取id值，从uid、memberId、userId、id中依次取。
            $arrIdRelat['id'] = self::getParamMemberid($arrInput);
            if (empty($arrIdRelat['id'])) {
                $arrIdRelat['id'] = !empty($arrInput['id']) ? $arrInput['id'] :'';
            }
            if(empty($arrIdRelat['id'])){
                return self::getErrorOut(ERRORCODE_UCENTER_NO_USER);
            }
            $resIdRelat = $this->getIdRelation($arrIdRelat);
            if (!empty($resIdRelat['data']['idRelation']['id'])) {
                $relationId = $resIdRelat['data']['idRelation']['id'];
            } else {
                return self::getErrorOut(ERRORCODE_UCENTER_NO_USER);
            }
            $url = JAVA_API_GETUSERPROFILEBYUID;
            $params['sMethod'] = 'post';
            $params['sendType'] = 'json';
            $params['arrData'] = [
                'UID' => $relationId,
            ];
        }
        $data = $this->http($url, $params);
        if ($data['ret'] == 0 && $data['sub'] == 0) {
            if (isset($data['data']['extUid'])) {
                unset($data['data']['extUid']);
            }
            $return['data'] = $data['data'];
            return $this->convPhotoUrl($return);
        } else {
            return self::getErrorOut(ERRORCODE_UCENTER_NO_USER);
        }
    }

    /**
     * 查询获取用户标签
     * @param $arrInput
     * openId 用户的openId
     * channelId  渠道id
     */
    public function getUserTag(array $arrInput)
    {
        //参数整理
        $arrInstallData = [
            'openId' => self::getParam($arrInput, 'openId'), //用户uid
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
        ];
        $return = self::getStOut();
        if (empty($arrInstallData['openId']) || empty($arrInstallData['channelId'])) {
            $return = self::getErrorOut(ERRORCODE_UCENTER_NO_USER);
        } else {
            $url = JAVA_USERCENTER_TAG_GET;
            $params['sMethod'] = 'post';
            $params['sendType'] = 'json';
            $params['arrData'] = [
                'id' => $arrInstallData['openId'],
                'idType' => 0,
                'tagList' => ['tag4dragon']
            ];
            $data = $this->http($url, $params);
            if (empty($data['sub']) && !empty($data['data']['result'][0]['tagVal'])) {
                $data = json_decode($data['data']['result'][0]['tagVal'], true);
            } else {
                $data = [];
            }
            $return['data'] = $data;
        }
        return $return;
    }

    /**
     * 将渠道ID转换为用户中心对应平台编号
     * 3 -> 11, 28 -> 12
     * @param $channelId
     */
    public function convChannelId($channelId)
    {
        switch ($channelId) {
            case 3: //微信
                $res = 11;
                break;
            case 28: //手Q
                $res = 12;
                break;
            default:
                $res = '';
        }
        return $res;
    }
###################################################
#########   重构版，新加用户中心接口 END   ##########
###################################################
    
    /**
     * 检测用户是否在黑名单中
     *
     * @param array $arrInput
     *        string $salePlatformType   售卖平台, 一般情况下为2
     *        string $openId             用户openId
     *        string $channelId          channelId
     *
     * @return array
     */
    public function checkBlack(array $arrInput)
    {
        //参数整理
        $strOpenId = self::getParam($arrInput, 'openId'); //用户uid
        $iChannelId = self::getParam($arrInput, 'channelId');//渠道（来源）
        $salePlatformType = self::getParam($arrInput, 'salePlatformType');//渠道（来源）
        
        $return = self::getStOut();
        $return['data']['inblacklist'] = false;
        //参数校验
        if (empty($strOpenId) || empty($iChannelId) || ($salePlatformType === '')) {
            return $return;
        }
        //获取用户之前的购票手机号
        $orderMobile = '';
        {
            $arrSendParams = [];
            $arrSendParams['channelId'] = $iChannelId;
            $arrSendParams['salePlatformType'] = $salePlatformType;
            $arrSendParams['openId'] = $strOpenId;
            $arrSendParams['userId'] = self::getParamMemberid($arrInput);
            $arrSendParams['appId'] = self::getParam($arrInput, 'appId');
            $res = $this->getMobile($arrSendParams);
            $orderMobile = !empty($res['data']['mobileNo']) ? $res['data']['mobileNo'] : '';
        }
        //获取用户中心手机号
        $userCenterMobile = '';
        {
            $arrSendParams = [];
            $arrSendParams['channelId'] = $iChannelId;
            $arrSendParams['openId'] = $strOpenId;
            $res = $this->getUserinfoByOpenid($arrSendParams);
            $userCenterMobile = !empty($res['data']['mobileNo']) ? $res['data']['mobileNo'] : '';
        }
        //调用大数据防作弊，判断用户是否是在黑名单中
        {
            $inblacklist = false;
            //融合参数
            $arrSendParams = [];
            $arrSendParams['openId'] = $strOpenId;
            $arrSendParams['channelId'] = $iChannelId;
            $arrSendParams['mobiles'] = array_values(array_unique(array_filter([$orderMobile, $userCenterMobile])));
            $res = $this->checkBlackInner($arrSendParams);
            $inblacklist = !empty($res['data']['inblacklist']) ? true : false;
            $return['data']['inblacklist'] = $inblacklist;
        }
        
        return $return;
    }
    
    /**
     * 验证黑名单，内部API，此API完成HTTP调用，此方法由checkBlack完成调用
     *
     * @param array $arrInput
     *
     * @return array
     */
    private function checkBlackInner(array $arrInput)
    {
        $return = self::getStOut();
        $return['data']['inblacklist'] = false;
        //所有的校验参数，比如openid、手机号，手机号可以为多个
        $strOpenId = self::getParam($arrInput, 'openId');
        $arrMobiles = self::getParam($arrInput, 'mobiles', []);
        //防作弊那边，不支持传多个手机号，只能分2次调用
        if ( !empty($arrMobiles) && is_array($arrMobiles)) {
            $params['sMethod'] = 'GET';
            $params['arrData'] = [
                'openid'   => $strOpenId,
                'mobile'   => @$arrMobiles[0],
                'querypos' => 4,
                'distype'  => 1,
            ];
            $res = $this->http(JAVA_BIG_DATA_ANTI_CHEATING_API, $params);
            if (isset($res['ret']) && ($res['ret'] == 1) && isset($res['data']['cannotUse']) && ($res['data']['cannotUse'] !== '')) {
                $return['data']['inblacklist'] = true;
            }
            //判断是否还需要调用一次（因为手机号可能有2个）
            if ( !$return['data']['inblacklist'] && (count($arrMobiles) == 2)) {
                $params['sMethod'] = 'GET';
                $params['arrData'] = [
                    'mobile'   => @$arrMobiles[1],
                    'querypos' => 4,
                    'distype'  => 1,
                ];
                $res = $this->http(JAVA_BIG_DATA_ANTI_CHEATING_API, $params);
                if (isset($res['ret']) && ($res['ret'] == 1) && isset($res['data']['cannotUse']) && ($res['data']['cannotUse'] !== '')) {
                    $return['data']['inblacklist'] = true;
                }
            }
        }
        
        return $return;
    }

    /**
     * 支付页面 给用户推荐的会员卡
     * @param $arrInput
     * openId 用户的openId
     * channelId  渠道id
     */
    public function payVipCard(array $arrInput)
    {
        $return = self::getStOut();
        //参数整理
        $arrData = [
            'openId' => self::getParam($arrInput, 'openId'), //用户uid
            'channelId' => intval(self::getParam($arrInput, 'channelId')),//渠道（来源）
        ];
        if (empty($arrData['openId']) || empty($arrData['channelId'])) {
            $return = self::getErrorOut(ERRORCODE_UCENTER_NO_USER);
        } else {
            $url = SYH_PHP_BONUS_URL;
            $params['sMethod'] = 'GET';
            $params['arrData'] = [
                'suin' => $arrData['openId'],
                'channel_id' => $arrData['channelId'],
            ];
            $data = $this->http($url, $params);
            if (!empty($data) && $data['apicode'] != 10000) {
                $return['ret'] = $return['sub'] = $data['apicode'];
            }
            $return['data'] = $data['data'];
        }
        return $return;
    }

    /*
     * 我跟其他人共同看过的影片
     * @param array $arrInput
     * @return array
     */
    public function watchSameMovies(array $arrInput){
        $return = self::getStOut();
        $myOpenId = self::getParam($arrInput, 'myOpenId','');
        $otherOpenId = self::getParam($arrInput, 'otherOpenId', '');
        $channelId = self::getParam($arrInput, 'channelId', '');
        $httpParams = [
            'arrData' => [
                'openIdList'   => [$myOpenId,$otherOpenId],
                'needSubOtherId' => true,
            ],
            'sMethod' => 'POST',
            'sendType' => 'json',
        ];
        $openids1='';
        $openids2='';
        $movieIds='';
        //先去用户中心查询openId关系树
        $res = $this->http(JAVA_API_BATCHGETIDRELATION, $httpParams);
        if (isset($res['ret']) && ($res['ret'] == 0)) {
            if(isset($res['data']['result'][0])){
                $openids1=Utils::array_column($res['data']['result'][0]['idRelation'],'openId');
                $openids1=implode(',',$openids1);
            }
            if(isset($res['data']['result'][1])){
                $openids2=Utils::array_column($res['data']['result'][1]['idRelation'],'openId');
                $openids2=implode(',',$openids2);
            }
        }
        if(!empty($openids1) && !empty($openids2)){
            $httpParams = [
                'arrData' => [
                    'sourceOpenIds'   => $openids1,
                    'targetOpenIds' => $openids2,
                ],
                'sMethod' => 'POST',
                'sendType' => 'json',
            ];
            $res = $this->http(JAVA_WATCH_SAME_MOVIES, $httpParams);
            if (isset($res['ret']) && ($res['ret'] == 0)) {
                $movieIds=$res['data']['movieIds'];
            }
            if(!empty($movieIds)){
                $params=[
                    'channelId'=>$channelId,
                    'movieIds'=>$movieIds,
                    'actorInfo'=>0
                ];
                $res=$this->service('Movie')->readMovieInfos($params);
                if (isset($res['ret']) && ($res['ret'] == 0) && is_array($res['data'])) {
                    $return['data']=array_values($res['data']);
                }
            }
        }
        return $return;
    }
}
