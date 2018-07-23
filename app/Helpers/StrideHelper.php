<?php

namespace App\Helpers;

use App\Models\Check;
use GuzzleHttp\Client;
use Carbon\Carbon;

class StrideHelper
{
    private static function CHECK_TYPE($type)
    {
        $mapping = [
                1  => "事假",
                2  => "特休",
                3  => "出差",
                4  => '病假',
                5  => 'Online',
                6  => '晚到',
                7  => '喪假',
                8  => '產假',
                9  => '陪產假',
                10 => '婚假',
            ];

        return $mapping[$type];
    }
 
    private static function WEEK_DAY($day)
    {
        $mapping = [
            "Sunday"    => "日",
            "Monday"    => "一",
            "Tuesday"   => "二",
            "Wednesday" => "三",
            "Thursday"  => "四",
            "Friday"    => "五",
            "Saturday"  => "六",
        ];

        return $mapping[$day];
    }

    public static function roomNotification(Check $check, $action)
    {
        if ($action == 'Create') {
            self::createNotification($check);
        }
        elseif ($action == 'Edit') {
            self::editNotification($check);
        }
    }

    private static function createNotification(Check $check)
    {
        $http = new Client([
            'headers' => [
                'Content-Type'  => 'application/json',
            ]
        ]);

        $checkin_at = Carbon::createFromFormat('Y-m-d H:i:s', $check->checkin_at);
        $checkout_at = Carbon::createFromFormat('Y-m-d H:i:s', $check->checkout_at);

        if ($checkin_at->isSameDay($checkout_at)) {
            $body = "時間: ".$checkin_at->toDateString()." (".self::WEEK_DAY($checkin_at->format('l')).") ".$checkin_at->format('H:i')." ~ ".$checkout_at->format('H:i')."\n";
        }
        else {
            $body = "時間: ".$checkin_at->toDateString()." (".self::WEEK_DAY($checkin_at->format('l')).") ".$checkin_at->format('H:i')." ~ ".$checkout_at->toDateString()." (".self::WEEK_DAY($checkout_at->format('l')).") ".$checkout_at->format('H:i')."\n";
        }

        $body .= "姓名: ".$check->staff->name."\n";

        if ($check->type == Check::TYPE_ONLINE || $check->type == Check::TYPE_OFFICIAL_LEAVE) {
            $body .= "假別: ".self::CHECK_TYPE($check->type)."\n"
                ."原因: ".$check->leave_reason->reason."\n";
        }

        $response = $http->request('POST', config('stride.bot.url')."/checkin/leave/notify", [
            'json' => [
                'action' => 'Leave Create Notification',
                'reply_message' => $body,
            ]
        ]);

        return $response->getBody();
    }

    private static function editNotification(Check $check)
    {
        $http = new Client([
            'headers' => [
                'Content-Type'  => 'text/plain',
            ]
        ]);

        $checkin_at = Carbon::createFromFormat('Y-m-d H:i:s', $check->checkin_at);
        $checkout_at = Carbon::createFromFormat('Y-m-d H:i:s', $check->checkout_at);

        $body = "編號: ".$check->id." 編輯成功\n";

        if ($checkin_at->isSameDay($checkout_at)) {
            $body .= "時間: ".$checkin_at->toDateString()." (".self::WEEK_DAY($checkin_at->format('l')).") ".$checkin_at->format('H:i')." ~ ".$checkout_at->format('H:i')."\n";
        }
        else {
            $body .= "時間: ".$checkin_at->toDateString()." (".self::WEEK_DAY($checkin_at->format('l')).") ".$checkin_at->format('H:i')." ~ ".$checkout_at->toDateString()." (".self::WEEK_DAY($checkout_at->format('l')).") ".$checkout_at->format('H:i')."\n";
        }
 
        $body .= "姓名: ".$check->staff->name."\n";

        if ($check->type == Check::TYPE_ONLINE || $check->type == Check::TYPE_OFFICIAL_LEAVE) {
            $body .= "假別: ".self::CHECK_TYPE($check->type)."\n"
                ."原因: ".$check->leave_reason->reason."\n";
        }
        $response = $http->request('POST', config('stride.bot.url')."/checkin/leave/notify", [
            'json' => [
                'action' => 'Leave Edit Notification',
                'reply_message' => $body,
            ]
        ]);

        return $response->getBody();
    }

    public static function personalNotification(Check $check, $action)
    {
        $http = new Client([
            'headers' => [
                'Content-Type'  => 'text/plain',
            ]
        ]);

        if ($action == "Edit") {
            $body = "編號: ".$check->id." 編輯成功\n";
        }
        if($action == "Create") {
            $body = "編號: ".$check->id." 新增成功\n";
        }

        $checkin_at = Carbon::createFromFormat('Y-m-d H:i:s', $check->checkin_at);
        $checkout_at = Carbon::createFromFormat('Y-m-d H:i:s', $check->checkout_at);

        if ($checkin_at->isSameDay($checkout_at)) {
            $body .= "時間: ".$checkin_at->toDateString()." (".self::WEEK_DAY($checkin_at->format('l')).") ".$checkin_at->format('H:i')." ~ ".$checkout_at->format('H:i')."\n";
        }
        else {
            $body .= "時間: ".$checkin_at->toDateString()." (".self::WEEK_DAY($checkin_at->format('l')).") ".$checkin_at->format('H:i')." ~ ".$checkout_at->toDateString()." (".self::WEEK_DAY($checkout_at->format('l')).") ".$checkout_at->format('H:i')."\n";
        }

        $body .= "姓名: ".$check->staff->name."\n"
            ."假別: ".self::CHECK_TYPE($check->type)."\n"
            ."原因: ".$check->leave_reason->reason."\n";

        $response = $http->request('POST', config('stride.bot.url')."/checkin/leave/notify", [
            'json' => [
                'action' => 'Personal Leave Notification',
                'reply_message' => $body,
                'email'         => $check->staff->email,
            ]
        ]);

        return $response->getBody();
    }
}
