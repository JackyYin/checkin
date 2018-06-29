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

    public static function create_notify(Check $check)
    {
        if ( strtotime(Carbon::today()) <= strtotime($check->checkin_at) && strtotime($check->checkin_at) <= strtotime(Carbon::tomorrow())) {
            $http = new Client([
                'headers' => [
                    'Content-Type'  => 'text/plain',
                    'Authorization' => 'Bearer '.config('stride.token'),
                ]
            ]);

            if ($check->type == Check::TYPE_ONLINE || $check->type == Check::TYPE_OFFICIAL_LEAVE) {
                $body = 
                    "時間: ".date("Y-m-d", strtotime($check->checkin_at))." (".self::WEEK_DAY(date("l", strtotime($check->checkin_at))).") ".date("H:i", strtotime($check->checkin_at))." ~ ".date("H:i", strtotime($check->checkout_at))."\n"
                    ."姓名: ".$check->staff->name."\n"
                    ."假別: ".self::CHECK_TYPE($check->type)."\n"
                    ."原因: ".$check->leave_reason->reason."\n";
            }
            else {
                $body = 
                    "時間: ".date("Y-m-d", strtotime($check->checkin_at))." (".self::WEEK_DAY(date("l", strtotime($check->checkin_at))).") ".date("H:i", strtotime($check->checkin_at))." ~ ".date("H:i", strtotime($check->checkout_at))."\n"
                    ."姓名: ".$check->staff->name."\n";
            }

            $response = $http->request('POST', config('stride.url'), [
                    'body' => $body, 
            ]);
        }
    }

    public static function edit_notify(Check $check)
    {
        if ( strtotime(Carbon::today()) <= strtotime($check->checkin_at) && strtotime($check->checkin_at) <= strtotime(Carbon::tomorrow())) {
            $http = new Client([
                'headers' => [
                    'Content-Type'  => 'text/plain',
                    'Authorization' => 'Bearer '.env('stride.token'),
                ]
            ]);

            if ($check->type == Check::TYPE_ONLINE || $check->type == Check::TYPE_OFFICIAL_LEAVE) {
                $body = 
                    "編號: ".$check->id." 已編輯\n"
                    ."時間: ".date("Y-m-d", strtotime($check->checkin_at))." (".self::WEEK_DAY(date("l", strtotime($check->checkin_at))).") ".date("H:i", strtotime($check->checkin_at))." ~ ".date("H:i", strtotime($check->checkout_at))."\n"
                    ."姓名: ".$check->staff->name."\n"
                    ."假別: ".self::CHECK_TYPE($check->type)."\n"
                    ."原因: ".$check->leave_reason->reason."\n";
            }
            else {
                $body = 
                    "編號: ".$check->id." 已編輯\n"
                    ."時間: ".date("Y-m-d", strtotime($check->checkin_at))." (".self::WEEK_DAY(date("l", strtotime($check->checkin_at))).") ".date("H:i", strtotime($check->checkin_at))." ~ ".date("H:i", strtotime($check->checkout_at))."\n"
                    ."姓名: ".$check->staff->name."\n";
            }

            $response = $http->request('POST', config('stride.url'), [
                    'body' => $body, 
            ]);
        }

    }
}