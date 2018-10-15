<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use Carbon\Carbon;
use App\Models\Check;
use App\Models\Bot;

class DiscordHelper
{
    protected $client;
    protected $url;

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

        $this->url = config('discord.url');
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

        if ($check->type == Check::TYPE_ONLINE || $check->type == Check::TYPE_OFFICIAL_LEAVE) {
            $body .= "假別: ".self::CHECK_TYPE($check->type)."\n"
                ."原因: ".$check->leave_reason->reason."\n";
        }

        $response = $this->client->request('POST', $this->url, [
            'json' => [
                'username' => config('mail.from.name'),
                'embeds' => [[
                    'title' => $check->staff->name." 請假囉！",
                    'description' => $body 
                ]]
            ]
        ]);
    }

    private function editNotification(Check $check)
    {
        $checkin_at = Carbon::createFromFormat('Y-m-d H:i:s', $check->checkin_at);
        $checkout_at = Carbon::createFromFormat('Y-m-d H:i:s', $check->checkout_at);

        if ($checkin_at->isSameDay($checkout_at)) {
            $body = "時間: ".$checkin_at->toDateString()." (".self::WEEK_DAY($checkin_at->format('l')).") ".$checkin_at->format('H:i')." ~ ".$checkout_at->format('H:i')."\n";
        }
        else {
            $body = "時間: ".$checkin_at->toDateString()." (".self::WEEK_DAY($checkin_at->format('l')).") ".$checkin_at->format('H:i')." ~ ".$checkout_at->toDateString()." (".self::WEEK_DAY($checkout_at->format('l')).") ".$checkout_at->format('H:i')."\n";
        }
 
        if ($check->type == Check::TYPE_ONLINE || $check->type == Check::TYPE_OFFICIAL_LEAVE) {
            $body .= "假別: ".self::CHECK_TYPE($check->type)."\n"
                ."原因: ".$check->leave_reason->reason."\n";
        }

        $response = $this->client->request('POST', $this->url, [
            'json' => [
                'username' => config('mail.from.name'),
                'embeds' => [[
                    'title' => $check->staff->name." 編輯假單囉！",
                    'description' => $body 
                ]]
            ]
        ]);
    }

    private function deleteNotification(Check $check)
    {
        $checkin_at = Carbon::createFromFormat('Y-m-d H:i:s', $check->checkin_at);
        $checkout_at = Carbon::createFromFormat('Y-m-d H:i:s', $check->checkout_at);

        if ($checkin_at->isSameDay($checkout_at)) {
            $body = "時間: ".$checkin_at->toDateString()." (".self::WEEK_DAY($checkin_at->format('l')).") ".$checkin_at->format('H:i')." ~ ".$checkout_at->format('H:i')."\n";
        }
        else {
            $body = "時間: ".$checkin_at->toDateString()." (".self::WEEK_DAY($checkin_at->format('l')).") ".$checkin_at->format('H:i')." ~ ".$checkout_at->toDateString()." (".self::WEEK_DAY($checkout_at->format('l')).") ".$checkout_at->format('H:i')."\n";
        }

        if ($check->type == Check::TYPE_ONLINE || $check->type == Check::TYPE_OFFICIAL_LEAVE) {
            $body .= "假別: ".self::CHECK_TYPE($check->type)."\n"
                ."原因: ".$check->leave_reason->reason."\n";
        }

        $response = $this->client->request('POST', $this->url, [
            'json' => [
                'username' => config('mail.from.name'),
                'embeds' => [[
                    'title' => $check->staff->name." 偷偷刪除假單囉！",
                    'description' => $body
                ]]
            ]
        ]);
    }
}
