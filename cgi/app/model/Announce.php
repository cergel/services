<?php
namespace sdkService\model;

class Announce extends BaseNew
{
    //历史订单接口，数据库表
    const CACHE_KEY_ANNOUNCE = 'static_new_notification_data_';

    /**
     * 查询可用公告
     * @param string $iChannelId
     * @param string $PrimaryKey
     * @param array $OrderIds
     * @return array
     */
    public function getAnnounce($iChannelId = '', $position, $cinemaId, $movieId = 0)
    {
        $data = $this->redis($iChannelId, STATIC_MOVIE_DATA)->WYhMGet(self::CACHE_KEY_ANNOUNCE . round(1, 20), [$position]);
        $JsonAnnounce = $Announce = $AnnounceChannel = $AnnounceCinema = $nowAnnounce = $returnArr = [];
        if ($data) {
            $JsonAnnounce = json_decode(current($data), true);
            if (!$JsonAnnounce) {
                $JsonAnnounce = [];
            }
            //筛选出本渠道的公告
            foreach ($JsonAnnounce as $value) {
                if (array_key_exists('channelIds', $value) AND in_array($iChannelId, $value['channelIds'])) {
                    $AnnounceChannel[$value['iNotificationID']] = $value;
                }
            }
            //通过movieId和影院确定是否有公告
            foreach ($AnnounceChannel as $value) {
                //判断是否需要movieId过滤
                $movieIdCheck = in_array($position, [22, 2]) && isset($value['movieId']) && !empty($value['movieId']);
                if ($movieIdCheck && (empty($movieId) || !in_array($movieId, $value['movieId']))) {
                    continue;
                }
                if (array_key_exists('cinemaIds', $value) && (in_array($cinemaId, $value['cinemaIds']) || (array_key_exists('allChannel', $value) && $value['allChannel'] == 1))) {
                    $Announce[$value['iNotificationID']] = $value;
                } else if (!$cinemaId) {
                    $Announce[$value['iNotificationID']] = $value;
                }
            }
            //判断当前时刻有哪些恰当可展示的公告
            $now = time();
            foreach ($Announce as $value) {
                if ($now >= $value['iStartAt'] AND $now < $value['iEndAt']) {
                    $nowAnnounce[$value['iNotificationID']] = $value;
                }
            }
            krsort($nowAnnounce);
            //有公告
            if ($nowAnnounce) {
                $item = current($nowAnnounce);
                $returnArr = [];
                $returnArr['sName'] = $item['sName'];
                $returnArr['sContent'] = $item['sContent'];
                $returnArr['sInfo'] = $item['sInfo'];
            } else {
                $returnArr = new \stdclass();
            }
        } else {
            $returnArr = new \stdclass();
        }
        return $returnArr;
    }
}