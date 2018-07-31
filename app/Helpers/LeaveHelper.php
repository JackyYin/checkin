<?php

namespace App\Helpers;

use App\Models\Check;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class LeaveHelper
{
    public static function countHours(Collection $checks)
    { 
        return $checks
        ->sum(function ($item) {
            $checkin = Carbon::createFromFormat('Y-m-d H:i:s', $item->checkin_at);
            $checkout = Carbon::createFromFormat('Y-m-d H:i:s', $item->checkout_at);

            $noon_start = Carbon::createFromFormat('Y-m-d H:i:s', $checkin->toDateString()." ".Check::NOON_START);
            $noon_end   = Carbon::createFromFormat('Y-m-d H:i:s', $checkin->toDateString()." ".Check::NOON_END);

            //扣掉午休時間
            if ($checkin->lte($noon_start) && $checkout->gte($noon_end)) {
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
