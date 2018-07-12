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
}
