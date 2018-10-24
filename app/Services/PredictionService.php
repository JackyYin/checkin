<?php

namespace App\Services;

use Carbon\Carbon;
use GuzzleHttp\Client;

class PredictionService
{
    protected $url;
    protected $client;

    public function __construct()
    {
        $this->url = "http://".config('prediction.host')."/predict";
        $this->client = new Client;
    }

    public function predict($staff)
    {
        try {
            $response = $this->client->post($this->url, [
                'json' => [
                    'staff_id'  => $staff->id,
                    'weekday'   => Carbon::now()->dayOfWeek - 1,
                    'yesterday' => $staff->checks()->yesterday()->isLeave()->get()->isNotEmpty() ? true : false,
                    'before_yesterday' => $staff->checks()->daysAgo(2)->isLeave()->get()->isNotEmpty() ? true : false,
                    'three_days_ago' => $staff->checks()->daysAgo(3)->isLeave()->get()->isNotEmpty() ? true : false
                ],
            ]);
        } catch (\Exception $e) {
            return false;
        }

        return json_decode((string) $response->getBody());
    }
}
