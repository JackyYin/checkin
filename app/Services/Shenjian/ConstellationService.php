<?php

namespace App\Services\Shenjian;

use App;
use Cache;
use Carbon\Carbon;
use DB;
use GuzzleHttp\Client;
use App\Models\Staff;
use App\Models\Bot;

class ConstellationService
{
    protected $base_url;
    protected $app_id;
    protected $client;

    public function __construct()
    {
        $this->base_url = "https://api.shenjian.io/constellation";
        $this->app_id = config('shenjian.appid');
        $this->client = new Client;
    }

    public function today($en_constellation)
    {
        $redis_key = Carbon::today()->toDateString().":".$en_constellation;

        if (Cache::has($redis_key)) {
            return Cache::get($redis_key);
        }

        try {
            App::setLocale('zh_CN');

            $response = $this->client->get($this->base_url.'/today', [
                'query' => [
                    'appid'         => $this->app_id,
                    'constellation' => __('constellation.'.$en_constellation)
                ],
            ]);
        } catch (\Exception $e) {
            return false;
        }

        $result = json_decode((string) $response->getBody());

        if ($result->error_code != 0) {
            return false;
        }

        Cache::put($redis_key, $result, Carbon::tomorrow());

        return $result;
    }
}
