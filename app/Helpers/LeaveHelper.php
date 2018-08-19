<?php

namespace App\Helpers;

use App\Models\Check;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LeaveHelper
{
    public static function getUsedHours($staff, $added_months)
    {
        $checks = $staff->checks
            ->whereIn('type', [
                Check::TYPE_ANNUAL_LEAVE, Check::TYPE_PERSONAL_LEAVE, Check::TYPE_LATE, Check::TYPE_SICK_LEAVE
            ])
            ->where('checkin_at', ">=", $staff->profile->on_board_date->addMonths($added_months));

        return $checks->sum(function ($item) {
            $checkin = $item->checkin_at;
            $checkout = $item->checkout_at;

            //扣掉午休時間
            if ($checkin->lte($item->noon_start) && $checkout->gte($item->noon_end)) {
                $minutes = $checkin->diffInMinutes($checkout) - 60 ;
            }
            else {
                $minutes = $checkin->diffInMinutes($checkout);
            }
            return round($minutes / 60, 1);
        });
    }

    public static function CheckRepeat($staff_id, $from, $to, $without = null)
    {
        $date = explode(" ", $from)[0];
        $date_start = $date." 00:00:00";
        $date_end   = $date." 23:59:59";

        $past_checks = Check::where('staff_id', $staff_id)
            ->isLeave()
            ->where('checkin_at', '>=', $date_start)
            ->where('checkin_at', '<=', $date_end)
            ->where('checkout_at', '<=', $date_end)
            ->where('id', '!=', $without)
            ->get();

        foreach ($past_checks as $check) {
            $check_start = strtotime($check->checkin_at);
            $check_end   = strtotime($check->checkout_at);
            if (strtotime($from) <= $check_start) {
                if (strtotime($to) <= $check_start) {
                    continue;
                }
                elseif ($check_start < strtotime($to) && strtotime($to) < $check_end) {
                    return false;
                }
                elseif ($check_end <= strtotime($to)) {
                    $check->delete();
                }
            }
            elseif ($check_start < strtotime($from) && strtotime($from) < $check_end) {
                return false;
            }
            elseif ($check_end <= strtotime($from)) {
                continue;
            }
        }

        return true;
    }
}
