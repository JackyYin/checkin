<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use Carbon\Carbon;
use App\Models\Check;
use App\Models\Bot;

class StrideHelper
{
    protected $client;
    protected $bot;

    private static function CHECK_TYPE($type)
    {
        return Check::getEnum('type')[$type];
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

    public function __construct()
    {
        $this->client = new Client([
            'headers' => [
                'Content-Type'  => 'application/json',
            ]
        ]);

        $this->bot = 'Stride';
    }

    public function roomNotification(Check $check, $action)
    {
        if ($action == 'Create') {
            $this->createNotification($check);
        }
        elseif ($action == 'Edit') {
            $this->editNotification($check);
        }
        elseif ($action == 'Delete') {
            $this->deleteNotification($check);
        }
    }

    private function createNotification(Check $check)
    {
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

        $response = $this->client->request('POST', Bot::where('name', $this->bot)->first()->notify_hook_url, [
            'json' => [
                'action' => 'Leave Create Notification',
                'reply_message' => $body,
            ]
        ]);
    }

    private function editNotification(Check $check)
    {
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
        $response = $this->client->request('POST', Bot::where('name', $this->bot)->first()->notify_hook_url , [
            'json' => [
                'action' => 'Leave Edit Notification',
                'reply_message' => $body,
            ]
        ]);
    }

    private function deleteNotification(Check $check)
    {
        $checkin_at = Carbon::createFromFormat('Y-m-d H:i:s', $check->checkin_at);
        $checkout_at = Carbon::createFromFormat('Y-m-d H:i:s', $check->checkout_at);

        $body = $check->staff->name." 偷偷刪除假單囉！\n";

        if ($checkin_at->isSameDay($checkout_at)) {
            $body .= "時間: ".$checkin_at->toDateString()." (".self::WEEK_DAY($checkin_at->format('l')).") ".$checkin_at->format('H:i')." ~ ".$checkout_at->format('H:i')."\n";
        }
        else {
            $body .= "時間: ".$checkin_at->toDateString()." (".self::WEEK_DAY($checkin_at->format('l')).") ".$checkin_at->format('H:i')." ~ ".$checkout_at->toDateString()." (".self::WEEK_DAY($checkout_at->format('l')).") ".$checkout_at->format('H:i')."\n";
        }

        if ($check->type == Check::TYPE_ONLINE || $check->type == Check::TYPE_OFFICIAL_LEAVE) {
            $body .= "假別: ".self::CHECK_TYPE($check->type)."\n"
                ."原因: ".$check->leave_reason->reason."\n";
        }
        $response = $this->client->request('POST', Bot::where('name', $this->bot)->first()->notify_hook_url , [
            'json' => [
                'action' => 'Leave Delete Notification',
                'reply_message' => $body,
            ]
        ]);
    }

    public function personalNotification(Check $check, $action)
    {
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

        $response = $this->client->request('POST', Bot::where('name', $this->bot)->first()->notify_hook_url, [
            'json' => [
                'action' => 'Personal Leave Notification',
                'reply_message' => $body,
                'email'         => $check->staff->email,
            ]
        ]);
    }

    public function sendPanel()
    {
        $today = Carbon::today();
        $body = $today->toDateString()." (".self::WEEK_DAY($today->format('l')).") 出缺勤狀況";

        $response = $this->client->request('POST', Bot::where('name', $this->bot)->first()->notify_hook_url, [
            'json' => [
                'action' => 'Panel',
                'reply_message' => $body,
            ]
        ]);
    }
}
