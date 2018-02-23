<?php
/**
 * 用户观影轨迹相关Model
 * User: Asus
 * Date: 2016/3/28
 * Time: 14:20
 */
namespace sdkService\model;

/**
 * 场次余票紧张相关model
 * Class TicketLeftOver
 * @package sdkService\model
 */
class TicketLeftOver extends BaseNew
{
    private $ticketLeftRedis;

    public function __construct()
    {
        $this->ticketLeftRedis = $this->redis(\wepiao::getChannelId(), TICKET_LEFT_OVER);
    }

    /**
     * 查找该影院下 所有排期对应的座位数
     * @param $cinemaId
     * @param $arrMpIds
     * @return array
     */
    public function getRoomSeatNumByMpIds($cinemaId, $arrMpIds)
    {
        $arrRoomSeats = [];
        if (!empty($arrMpIds)) {
            $strKey = 'room_seat_mpid_' . $cinemaId;
            try {

                $arrSeats = $this->ticketLeftRedis->WYhMGet($strKey, $arrMpIds);
                if (!empty($arrSeats)) {
                    foreach ($arrSeats as $key => $strSeat) {
                        if (!empty($strSeat)) {
                            $arrSeats[$key] = json_decode($strSeat, 1);
                        } else {
                            unset($arrSeats[$key]);
                        }
                    }
                } else {
                    $arrSeats = [];
                }
                $arrRoomSeats = $arrSeats;
            } catch (Exception $ex) {
                $arrRoomSeats = [];
            }
        }
        return $arrRoomSeats;
    }

    /**
     * 通过影院ID活期该影院下所有的场次信息[读取本渠道下的静态数据]
     * @param $cinemaId
     * @return array|mixed
     */
    public function getMpIdsByCinemaId($cinemaId)
    {
        $arrMpIds = [];
        $strKey = 'cinema_mpids_' . $cinemaId;
        try {
            $strMpId = $this->redis(\wepiao::getChannelId(), STATIC_MOVIE_DATA)->WYget($strKey);
            if ($strMpId) {
                $arrMpIds = json_decode($strMpId, 1);
            }
        } catch (Exception $ex) {
            $arrMpIds = [];
        }
        return $arrMpIds;
    }

}