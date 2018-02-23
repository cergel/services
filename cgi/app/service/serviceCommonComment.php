<?php

namespace sdkService\service;

/**
 * 所有黑名单、屏蔽词等评论公共用到的都写在这里
 * Class serviceCommentNew
 * @package sdkService\service
 */
class serviceCommonComment extends serviceBase
{
    /**
     * @tutorial 用户是否在黑名单
     * @param string $uid (openId or unionId)
     * @return bool T or F
     */
    public function isBalckList($ucid)
    {
        $arrReturn = self::getStOut();
        $arrBlackList = $this->model('BlackList')->getBlackList();
        if (!empty($arrBlackList) && !empty($ucid)) {
            if (in_array($ucid, $arrBlackList))
                $arrReturn = self::getErrorOut(ERRORCODE_BLACK_USER_COMMENT);
        }
        return $arrReturn;
    }

    /**
     * 腾讯云的关键字接口
     * @param $strContent
     */
    public function isTengXunKeyWord($strContent)
    {
        $arrReturn = self::getStOut();
        $url = "http://10.3.40.23/kw.php";
        $params['sMethod'] = 'GET';
        $params['sendType'] = 'json';
        $params['iTimeout'] = 1000;
        $params['arrData'] = [
            'content' => $strContent
        ];
        $retData = $this->http($url, $params);
        if (!empty($retData['level']) && $retData['level'] > 2) {
            $arrReturn = self::getErrorOut(ERRORCODE_SHIELDING_4_WORD);
        }
        return $arrReturn;

    }
    /**
     * @tutorial 屏蔽词判断，是否包含屏蔽词
     * @param string $strContent
     * @return Ambigous <boolean, unknown>
     */
    public function isShieldingWords($strContent)
    {
        $arrShieldingWordsList = $this->model("ShieldingWords")->getShieldingWordsList();
        $arrReturn = self::getStOut();
        $minRes = true;
        if (!empty($arrShieldingWordsList)) {
            //屏蔽词校验
            foreach ($arrShieldingWordsList as $shielding) {
                if (!empty($shielding['name']) && strstr($strContent, $shielding['name'])) {
                    $minRes = $shielding['stype'];
                    break;
                }
            }
        }
        if ($minRes !== true) {
            switch ($minRes) {
                case 1:
                    $arrReturn = self::getErrorOut(ERRORCODE_SHIELDING_1_WORD);
                    break;
                case 2:
                    $arrReturn = self::getErrorOut(ERRORCODE_SHIELDING_2_WORD);
                    break;
                case 3:
                    $arrReturn = self::getErrorOut(ERRORCODE_SHIELDING_3_WORD);
                    break;
                case 4:
                    $arrReturn = self::getErrorOut(ERRORCODE_SHIELDING_4_WORD);
                    break;
                case 5:
                    $arrReturn = self::getErrorOut(ERRORCODE_SHIELDING_5_WORD);
                    break;
            }
        }
        return $arrReturn;
    }

    /**
     * @tutorial 敏感词判断，是否包含敏感词
     * @param string $strContent
     * @return bool
     */
    public function isSensitiveWords($strContent)
    {
        $arrReturn = self::getStOut();
        $arrSensitiveWordsList = $this->model("SensitiveWords")->getSensitiveWordsList();
        $arrReturn['data'] = 0;
        if (!empty($arrSensitiveWordsList)) {
            foreach ($arrSensitiveWordsList as $val) { //屏蔽词校验
                if (!empty($val['name']) && strstr($strContent, $val['name'])) {
                    $arrReturn['data'] = 1;
                    break;
                }
            }
        }

        return $arrReturn;
    }

    /**
     * 根据openid或者ucid获取用户中心的头像,
     * @param $strUcid
     * @param string $iUid
     * @param bool|true $start
     * @return array
     */
    public function getUserInfoByUcid($strUcid, $iUid = '',$start=true)
    {
        $arrUserInfo = ['nickName' => '路人甲', 'uid' => $iUid, 'ucid' => $strUcid, 'photo' => CDN_APPNFS . '/dataImage/headphoto.png'];
        if($start)$arrUserInfo['is_star'] = 0;
        if (!empty($strUcid)) {
            if($start){
                $data = self::_getCommentStar($strUcid);
                $data['uid'] = $iUid;
                $data['is_star'] = 1;
                $arrUserInfo = $data;
            }
            if(empty($arrUserInfo['is_star'])){//去用户中心取数据
                $data = self::_getUserContentInfo(JAVA_API_GETUCIDBYINFO, ['openId' => $strUcid]);
                if (!empty($data['photo']) && strpos($data['photo'], "dataImage/"))
                    $data['photo'] = CDN_APPNFS . $data['photo'];
                if (empty($data['nickName'])) {
                    $data['photo'] = $arrUserInfo['photo'];
                }
                $arrUserInfo['nickName'] = !empty($data['nickName']) ? $data['nickName'] : self::getUserName($strUcid);
                $arrUserInfo['photo'] = !empty($data['photo']) ? $data['photo'] : $arrUserInfo['photo'];
            }
        }
        return $arrUserInfo;
    }
    /**
     * 获取明星内容
     * @param $strUcid
     */
    private function _getCommentStar($strUcid)
    {
        return $this->model('NewCommentStar')->getCommentStarByUcid($strUcid);

    }

    /**调用用户中心
     * @param $strUrl
     * @param $arrData
     * @return string
     */
    private function _getUserContentInfo($strUrl, $arrData)
    {
        $params['sMethod'] = 'post';
        $params['sendType'] = 'json';
        $params['arrData'] = $arrData;
        $data = $this->http($strUrl, $params);
        return !empty($data['data']) ? $data['data'] : '';
    }
    /**
     * 取模生成没有昵称的用户的昵称
     * @param $ucid
     * @return mixed
     */
    private function getUserName($ucid)
    {
        $arrData = [
            '捉影联','陷阱密码','张敌手','安迪0987','大平原7174520','收了的人','william要','小朵0206','墨qingqingqing','jingling只要',
            'roma1999','上课68573020','tingting178','cry啥0215','Elohim忽然','some打印','i投了','venic372','vampirersdf419','帥Wong330',
            '我567','看是可以891013','我基本波波','温柔2300','算法14837','巷尾猫的马甲','额样品902678451','赶上的饭12','warden超','罗东waste',
            '王一飞553','360収16367','角搜哈哈','温度i917468470','爱V刹1772970','劢高峰27','孙嘉敏你好么','甫翻翻','大小袍子','unrelatedl03',
            '过八一645','峰哥奕云','V字3804','瑟兰young','琅琊山250','矮小00yu','人磷矿00','苟一巴17','EK乐然8621','pangpang395',
            '可爱song49','台风天2404','坦克1119','硬币hyi','rioero23','李睿mumumu','steveneightsixcn','小马驹','屋子里的房间','夹芯板j123',
            'zh胡长三','不走389','指摘11','jesscial咯咯','破译密码120','拉兹20009','杨凯ikmb','顶风673','模块化14','你说话啊295',
            '塑唐哦哦','偶数蛋232','银英449','游泳圈2018','hqj168','hiking52','V字票2','邬倩倩0612','大大加菲','IDL177',
            '张赛80900','smile汗i2007','么么茶北泉','张_小小','五天905413','行走的暴力姐511','透明贴后面','盐城人机器','邓婕英337','wakayi大',
            '夏花泛滥','大斌82','风中脓啦啦','狂魔xul','裤裤1063','哦翻白眼of','心人940181','skeed444','王文斌9006','冰漪2004',
            'only19901212','苏俩次','精灵王7374','I派1129','错彩镂金123','拉车20','熊快快','牛皮面813152217','卡努002854','小雁南燕',
            '少羽281','李博爱萌萌哒','如水26','童童975','00sail00','本咯vb','Wong3波波','半夏等风吹','吹跑阿婆2011','qqqq6285',
            '最简单_346175710','凌龙成0101','网好礼394','连晨宇','vGZ888946941','Pom艾斯卡','奔奔250','COC1391457587','望月1211','费列罗001',
            '加百媚','搁在531500','猜卿卿','皇后nNNN','sky透明片','十19儿82','Love白豆腐','冶机械','吃东西的小萝莉','请假条3730',
            '黄点123','ar_春','via1500XXX283','很骄傲tc2000','b124人都是','看见哦712','风吹大柳树','往前飞21868346','没_onroe_我','探险票快递',
            'alex0018','胡桃夹子998','全欧洲347761','吃00121110','打天下947611561','好人475','i没人r5632','路易威斯GUI','聪明人429056399','满是0452',
            '哟哟芳草香','小钢887','亲民宝宝806','TUB9808081','star是','儿啊才274','Dori说','反攻为受的一品','热宝3496','winner孙0822',
            '事2622','围围巾7','卿丹师','玛特24259','win呢857','天黑250','360U361','撒让say','呵护70','devil小小',
            '梨花liye','璎珞倩倩','敞篷E6004','v1345372','华华890','欧洲2014','不哈皮774','火星人088','冰窟2004','妮妮身高差',
            '不吃fish的人','Irish樱花','劳伦斯106564','限制1089','啊hi家','砂砾_901','周末婚礼进行时','彩虹他爹586','卡通00088','蓝波织梦',
            '螺积分','森的生活','天然萌萌哒','补下56','蘑菇也跌哟','招聘1024','赵琳ia','QQ秀323','藏宝湾19920808','凯1466',
            '罗叶如今','螺蛳粉90808','风筝遇强敌','蜂蜜1402','对爱情钱','南餐厅Lulu','梨花668','烤鱼店小伙计','车裂30269','拥有人格的鬼混',
            '小海08080909','zj_田坤','3快1502','Clemente0610','吃吃不不','妮妮53021','卡通画一06061','一于承妍','阿玛软开关','量规鸣',
            '静儿candy','没啥大事','一天了231','幻想家27','八卦格ion','波违反2010','讨好木易','妖妖20021122','木枋27','唐宇焖鸡',
            '小一届','燯814','鬼混爱木马','电压993','刺激我233','君莫狂look','我叫莫小莫','无样品336','扬言0714','掌控的的79',
            '绿u1694','杨彦4','我呸1','深Vvv','干脆面浣熊家','别闹寳貝','V型天体','Doray123','拳拳之心快','幺儿不起',
            '齐齐在本片','正能量小霸王','差纷纷00','谢谢184','匡匡0101','芐331','飘呀飘81','费恒基','羽毛bird灰起来','皇家血统16',
            '泸天化94','阿姐24','瑟拉尼娜','龙猫君在大帝都','关遵10','转弯速度241','勤杂工000','秘密特工008','画皮9009','两厢车024',
            '旪37','嗐星舞盟','了教授32','诗意化浓','博士生77','唔卉鬓56','无留言板','银星太保','辣椒姊','美瞳1110',
            '辅导员68','葱8491','木屐哟哟','Francisco寳寳','兔子噗524','咖喱皽222','双眼睛的见','鸭头87','天秤座82','向前冲4123',
            '沟通瑟尔','周训卓咯咯','vrr62','李俊梅37','校服507','婕拉47','囜9083','许西村10','孙瑾147','依亲n0678',
            '龙志子','go块66','数控imati','长沙碎碎','陪我飞306','柒沛78','A站小火电','温泉乡05','Caza41','炮兵团19',
            '馥香9170','涩琪686','邵丕丞相','滑板车看着','热死啦1715','珉溪寳寳','杨彦0714','雯雯_long','甑155455','提哦323',
            '蓝光卡科普','牧羊女29','乌苏里江头','飞飞风铃','唐宇要你从了','毛虾233','答题卡99','貝貝宁03','在欧22','未名湖8087',
            'grace茵','To_Viola11','马霞455','抽屉075','樱花mating','兰兰书','李小婧Mdi','天空ly','刘林欣02','ogham49',
            '鄂喏多姿','里欧上学呢','沛儿280','凌芳QQ','wod0024','虎牙蝉蜕','微微盗来','火星人编号10103','纷纷喵喵','淘气鬼one个',
            '李小xing','晨铎天','敞篷车875','吐槽诺820','藍染bleach','仙人掌0408','赳秦人未老','应雨露水','乌诺凡','棉手套3851',
            '黄小豆556','关遵000','约翰Tupac','资格1202','阳光小汪','angle爱beauty','鞥0980','包梗ion210','蔡锷81','幽灵公主22',
            '廿四味2507','天天向上糖糖','小小诖','小园子GG','缓了缓','粉粉5566','chris章','善恶r9','微波炉小饼','陈怡0601',
            '文旦柚68','YY桃源','晓晓芳玲玲','过谎言','NBA83','紫百合699','恋人恋爱880','不开森11','陈于鏊307','光滑滑露露',
            '圣母马力丫','我家ctrl坏了','奈奈喝奶奶','聊聊李无锋','黄必箐','五段五角','lee.王','岁岁氨','威图4033','耷拉22',
            '螺母9809','happyE叉叉','咯阿春1515','liyina笑笑','何处怜尘埃','关泽楠54','爱爱djq','吃肉的小白牙','妖精1100','孙博酥饼',
            '搜温柔93','几维鸟798','审完在对I13','Peterlalala','UFO没看见','格格屋家碗','看剧city','阿谀12098','乱七八糟88225','kiki咧了咧723',
            '如来福大帐','破魔锤11','车牌架7977','弹幕刷屏','哥斯拉小伙伴','阿拉蕾小帽子','fassbendersha','木鱼家木有余粮','猩猩狒狒2929','遥遥小冰冰439',
            '吴xx呜呜','小小米喜欢吃肉','发奋学习好好奋斗','吴咪咪88','小池做实验','青花瓷557','老罗家的苦逼','余名飞嚄','双人士称','主播ILOVEYOU',
        ];
        return $arrData[abs(crc32($ucid))%450];
    }


}