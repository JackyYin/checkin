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
 
    public static function create_notify(Check $check)
    {
        if ( strtotime(Carbon::today()) <= strtotime($check->checkin_at) && strtotime($check->checkin_at) <= strtotime(Carbon::tomorrow())) {
            $http = new Client([
                'headers' => [
                    'Content-Type'  => 'text/plain',
                    'Authorization' => 'Bearer '.env('STRIDE_TOKEN'),
                ]
            ]);

            $body = 
                "姓名: ".$check->staff->name."\n"
                .date("H 點 i 分", strtotime($check->checkin_at))." 至 ".date("H 點 i 分", strtotime($check->checkout_at))." 請假\n"
                ."假別: ".self::CHECK_TYPE($check->type)."\n"
                ."原因: ".$check->leave_reason->reason."\n";

            $response = $http->request('POST', env('STRIDE_URI'), [
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
                    'Authorization' => 'Bearer '.env('STRIDE_TOKEN'),
                ]
            ]);

            $body = 
                "編號: ".$check->id." 已編輯\n"
                ."姓名: ".$check->staff->name."\n"
                .date("H 點 i 分", strtotime($check->checkin_at))." 至 ".date("H 點 i 分", strtotime($check->checkout_at))." 請假\n"
                ."假別: ".self::CHECK_TYPE($check->type)."\n"
                ."原因: ".$check->leave_reason->reason."\n";

            $response = $http->request('POST', env('STRIDE_URI'), [
                    'body' => $body, 
            ]);
        }

    }
}
