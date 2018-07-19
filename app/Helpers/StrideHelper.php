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

    private static function GetConversationRoster()
    {
        $http = new Client([
            'headers' => [
                'accept'  => 'application/json',
                'Authorization' => 'Bearer '.config('stride.token'),
            ]
        ]);
        $url = str_replace("message", "roster", config('stride.url'));
        $response = $http->request('GET', $url);

        $content = array();
        foreach(json_decode($response->getBody())->values as $user_id) {
            $content[] = array(
                'type'  => "mention",
                'attrs' => array(
                    'id' => $user_id,
                )
            );
        }
        return $content;
    }

    public static function createNotification(Check $check)
    {
        if ( strtotime(Carbon::today()) <= strtotime($check->checkin_at) && strtotime($check->checkin_at) <= strtotime(Carbon::tomorrow())) {
            $http = new Client([
                'headers' => [
                    'Content-Type'  => 'application/json',
                ]
            ]);

            if ($check->type == Check::TYPE_ONLINE || $check->type == Check::TYPE_OFFICIAL_LEAVE) {
                $body = 
                    "\n時間: ".date("Y-m-d", strtotime($check->checkin_at))." (".self::WEEK_DAY(date("l", strtotime($check->checkin_at))).") ".date("H:i", strtotime($check->checkin_at))." ~ ".date("H:i", strtotime($check->checkout_at))."\n"
                    ."姓名: ".$check->staff->name."\n"
                    ."假別: ".self::CHECK_TYPE($check->type)."\n"
                    ."原因: ".$check->leave_reason->reason."\n";
            }
            else {
                $body = 
                    "\n時間: ".date("Y-m-d", strtotime($check->checkin_at))." (".self::WEEK_DAY(date("l", strtotime($check->checkin_at))).") ".date("H:i", strtotime($check->checkin_at))." ~ ".date("H:i", strtotime($check->checkout_at))."\n"
                    ."姓名: ".$check->staff->name."\n";
            }

            $response = $http->request('POST', config('stride.bot.url')."/checkin/leave/notify", [
                'json' => [
                    'action' => 'Leave Create Notification',
                    'reply_message' => $body,
                ]
            ]);

            return $response->getBody();
        }
    }

    public static function editNotification(Check $check)
    {
        if ( strtotime(Carbon::today()) <= strtotime($check->checkin_at) && strtotime($check->checkin_at) <= strtotime(Carbon::tomorrow())) {
            $http = new Client([
                'headers' => [
                    'Content-Type'  => 'text/plain',
                ]
            ]);

            if ($check->type == Check::TYPE_ONLINE || $check->type == Check::TYPE_OFFICIAL_LEAVE) {
                $body = 
                    "編號: ".$check->id." 編輯成功\n"
                    ."時間: ".date("Y-m-d", strtotime($check->checkin_at))." (".self::WEEK_DAY(date("l", strtotime($check->checkin_at))).") ".date("H:i", strtotime($check->checkin_at))." ~ ".date("H:i", strtotime($check->checkout_at))."\n"
                    ."姓名: ".$check->staff->name."\n"
                    ."假別: ".self::CHECK_TYPE($check->type)."\n"
                    ."原因: ".$check->leave_reason->reason."\n";
            }
            else {
                $body = 
                    "編號: ".$check->id." 編輯成功\n"
                    ."時間: ".date("Y-m-d", strtotime($check->checkin_at))." (".self::WEEK_DAY(date("l", strtotime($check->checkin_at))).") ".date("H:i", strtotime($check->checkin_at))." ~ ".date("H:i", strtotime($check->checkout_at))."\n"
                    ."姓名: ".$check->staff->name."\n";
            }

            $response = $http->request('POST', config('stride.bot.url')."/checkin/leave/notify", [
                'json' => [
                    'action' => 'Leave Edit Notification',
                    'reply_message' => $body,
                ]
            ]);

            return $response->getBody();
        }
    }

    public static function personalNotification(Check $check, $source)
    {
        $http = new Client([
            'headers' => [
                'Content-Type'  => 'text/plain',
            ]
        ]);

        if ($source == "edit") {
            $body = "編號: ".$check->id." 編輯成功\n";
        }
        if($source == "create") {
            $body = "編號: ".$check->id."新增成功\n";
        }

        $body = 
            $body."時間: ".date("Y-m-d", strtotime($check->checkin_at))." (".self::WEEK_DAY(date("l", strtotime($check->checkin_at))).") ".date("H:i", strtotime($check->checkin_at))." ~ ".date("H:i", strtotime($check->checkout_at))."\n"
            ."姓名: ".$check->staff->name."\n"
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
