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
    protected $baseUrl;
    protected $appId;
    protected $client;
    protected $translationService;

    public function __construct()
    {
        $this->baseUrl = "https://api.shenjian.io/constellation";
        $this->appId = config('shenjian.appid');
        $this->client = new Client;
        $this->translationService = new \App\Services\Google\TranslationService();
    }

    public function today($engConstellation)
    {
        $redisKey = Carbon::today()->toDateString().":".$engConstellation;

        if (Cache::has($redisKey)) {
            return Cache::get($redisKey);
        }

        try {
            App::setLocale('zh_CN');

            $response = $this->client->get($this->baseUrl.'/today', [
                'query' => [
                    'appid'         => $this->appId,
                    'constellation' => __('constellation.'.$engConstellation)
                ],
            ]);
        } catch (\Exception $e) {
            return false;
        }

        $result = json_decode((string) $response->getBody());

        if ($result->error_code != 0) {
            return false;
        }

        Cache::put($redisKey, $result, Carbon::tomorrow());

        return $result;
    }

    public function analysisToday($engConstellation)
    {
        $redisKey = Carbon::today()->toDateString().":translated_analysis:".$engConstellation;

        if (Cache::has($redisKey)) {
            return Cache::get($redisKey);
        }

        $result = $this->today($engConstellation);

        if (!$result) {
            return false;
        }

        $analysis = $this->translationService->translate($result->data->analysis);

        Cache::put($redisKey, $analysis, Carbon::tomorrow());

        return $analysis;
    }

    public function starsToday($engConstellation)
    {
        $result = $this->today($engConstellation);

        if (!$result) {
            return false;
        }

        $stars = [];

        foreach ($result->data->fate_data as $fate) {
            $stars[] = $fate->value[0];
        }

        return $stars;
    }
}
