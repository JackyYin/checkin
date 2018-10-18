<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use Carbon\Carbon;
use App\Models\Bot;
use App\Models\Check;
use App\Models\Staff;

class LineHelper
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

        $this->bot = 'Line';
    }

    public function personalNotification(Check $check, $action)
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

    public function fortuneNotification($staff)
    {
        $service = new \App\Services\Shenjian\ConstellationService();

        $fortune_result = $service->today($staff->constellation);

        if (!$fortune_result) {
            $reply = "今日運勢分析: 找不到您的運勢...QQ";
        } else {
            $reply = "今日運勢分析: ".$fortune_result->data->analysis;
        }

            $response = $this->client->request('POST', Bot::where('name', $this->bot)->first()->notify_hook_url, [
                'json' => [
                    'subscribers' => [$staff->email],
                    'reply_message' => $reply
                ]
            ]);
    }

    private function createNotification(Check $check)
    {
        $checkin_at = Carbon::createFromFormat('Y-m-d H:i:s', $check->checkin_at);
        $checkout_at = Carbon::createFromFormat('Y-m-d H:i:s', $check->checkout_at);

        $body = $check->staff->name." 請假囉!\n"
            ."開始 : ".$checkin_at->format('Y/m/d')." ".$checkin_at->format('H:i')."\n"
            ."結束 : ".$checkout_at->format('Y/m/d')." ".$checkout_at->format('H:i')."\n"
            ."假別 : ".self::CHECK_TYPE($check->type)."\n"
            ."事由 : ".$check->leave_reason->reason."\n"
            ."編號 : ".$check->id;

        $response = $this->client->request('POST', Bot::where('name', $this->bot)->first()->notify_hook_url, [
            'json' => [
                'subscribers' => $this->getSubscribersExcept($check->staff->id),
                'reply_message' => $body,
            ]
        ]);
    }

    private function editNotification(Check $check)
    {
        $checkin_at = Carbon::createFromFormat('Y-m-d H:i:s', $check->checkin_at);
        $checkout_at = Carbon::createFromFormat('Y-m-d H:i:s', $check->checkout_at);

        $body = $check->staff->name." 修改假單囉!\n"
            ."開始 : ".$checkin_at->format('Y/m/d')." ".$checkin_at->format('H:i')."\n"
            ."結束 : ".$checkout_at->format('Y/m/d')." ".$checkout_at->format('H:i')."\n"
            ."假別 : ".self::CHECK_TYPE($check->type)."\n"
            ."事由 : ".$check->leave_reason->reason."\n"
            ."編號 : ".$check->id;

        $response = $this->client->request('POST', Bot::where('name', $this->bot)->first()->notify_hook_url , [
            'json' => [
                'subscribers' => $this->getSubscribersExcept($check->staff->id),
                'reply_message' => $body,
            ]
        ]);
    }

    private function deleteNotification(Check $check)
    {
        $checkin_at = Carbon::createFromFormat('Y-m-d H:i:s', $check->checkin_at);
        $checkout_at = Carbon::createFromFormat('Y-m-d H:i:s', $check->checkout_at);

        $body = $check->staff->name." 偷偷刪除假單囉!\n"
            ."開始 : ".$checkin_at->format('Y/m/d')." ".$checkin_at->format('H:i')."\n"
            ."結束 : ".$checkout_at->format('Y/m/d')." ".$checkout_at->format('H:i')."\n"
            ."假別 : ".self::CHECK_TYPE($check->type)."\n"
            ."事由 : ".$check->leave_reason->reason."\n"
            ."編號 : ".$check->id;

        $response = $this->client->request('POST', Bot::where('name', $this->bot)->first()->notify_hook_url , [
            'json' => [
                'subscribers' => $this->getSubscribersExcept($check->staff->id), 
                'reply_message' => $body,
            ]
        ]);
    }

    private function getSubscribersExcept($staff_id)
    {
        return Staff::where('id', '!=', $staff_id)
            ->subscribed()
            ->active()
            ->get()->pluck('email');
    }
}
